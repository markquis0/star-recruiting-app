<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Recruiter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username|max:255',
                'password' => 'required|string|min:6',
                'role' => 'required|in:candidate,recruiter',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required_if:role,recruiter|nullable|email|unique:recruiters,email',
                'role_title' => 'nullable|string|max:255',
                'years_exp' => 'nullable|integer',
                'company_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Wrap everything in a transaction so if any part fails, all changes are rolled back
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                ]);

                if ($request->role === 'candidate') {
                    Candidate::create([
                        'user_id' => $user->id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role_title' => $request->role_title,
                        'years_exp' => $request->years_exp,
                    ]);
                } else {
                    Recruiter::create([
                        'user_id' => $user->id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'company_name' => $request->company_name,
                    ]);
                }

                // Check if Passport tables exist first
                $passportTableExists = DB::getSchemaBuilder()->hasTable('oauth_personal_access_clients');
                
                if (!$passportTableExists) {
                    Log::error('Passport tables do not exist. Running passport:install...');
                    // Run passport:install to create missing tables
                    try {
                        $exitCode = Artisan::call('passport:install', ['--force' => true]);
                        $output = Artisan::output();
                        Log::info('Passport install completed', [
                            'exit_code' => $exitCode,
                            'output' => $output
                        ]);
                        
                        if ($exitCode !== 0) {
                            throw new \Exception('passport:install returned exit code ' . $exitCode . '. Output: ' . $output);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to run passport:install: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                            'exception_class' => get_class($e)
                        ]);
                        throw new \Exception('Failed to set up authentication service: ' . $e->getMessage());
                    }
                }
                
                // Check if Passport personal access client exists and is properly linked
                try {
                    $personalAccessClient = DB::table('oauth_personal_access_clients')
                        ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                        ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                        ->first();
                } catch (\Exception $e) {
                    // If table still doesn't exist or query fails, try passport:install again
                    Log::error('Error querying Passport client: ' . $e->getMessage());
                    try {
                        $exitCode = Artisan::call('passport:install', ['--force' => true]);
                        $output = Artisan::output();
                        Log::info('Passport install completed after query error', [
                            'exit_code' => $exitCode,
                            'output' => $output
                        ]);
                        
                        if ($exitCode !== 0) {
                            throw new \Exception('passport:install returned exit code ' . $exitCode . '. Output: ' . $output);
                        }
                        
                        $personalAccessClient = DB::table('oauth_personal_access_clients')
                            ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                            ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                            ->first();
                    } catch (\Exception $e2) {
                        Log::error('Failed to run passport:install after query error: ' . $e2->getMessage(), [
                            'trace' => $e2->getTraceAsString()
                        ]);
                        throw new \Exception('Failed to set up authentication service: ' . $e2->getMessage());
                    }
                }

                if (!$personalAccessClient) {
                    Log::error('Passport Personal Access Client not found during registration. Attempting to create...');
                    // Try to create the client programmatically
                    try {
                        $clientRepository = app(\Laravel\Passport\ClientRepository::class);
                        $client = $clientRepository->createPersonalAccessClient(
                            null,
                            'Star Recruiting Personal Access Client',
                            'http://localhost'
                        );
                        Log::info('Passport Personal Access Client created successfully during registration with ID: ' . $client->id);
                        
                        // Verify it was created properly
                        $verifyClient = DB::table('oauth_personal_access_clients')
                            ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                            ->where('oauth_clients.id', $client->id)
                            ->first();
                        
                        if (!$verifyClient) {
                            throw new \Exception('Personal access client created but not properly linked');
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to create Passport client during registration: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Throw exception to trigger transaction rollback
                        throw new \Exception('Authentication service not properly configured: ' . $e->getMessage());
                    }
                }

                // Create token - if this fails, the transaction will rollback
                try {
                    $token = $user->createToken('StarRecruiting')->accessToken;
                } catch (\Exception $tokenException) {
                    Log::error('Token creation failed during registration: ' . $tokenException->getMessage(), [
                        'trace' => $tokenException->getTraceAsString(),
                        'user_id' => $user->id
                    ]);
                    // Throw exception to trigger transaction rollback
                    throw new \Exception('Failed to generate authentication token: ' . $tokenException->getMessage());
                }

                return response()->json([
                    'message' => 'Registration successful',
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'role' => $user->role,
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception_class' => get_class($e)
            ]);
            
            // Check if it's a Passport or token creation error
            $errorMessage = 'An error occurred during registration. Please try again.';
            $errorCode = 'registration_failed';
            
            if (str_contains($e->getMessage(), 'Authentication service not properly configured')) {
                $errorMessage = 'Authentication service configuration error. Please contact support.';
                $errorCode = 'passport_client_missing';
            } elseif (str_contains($e->getMessage(), 'Failed to generate authentication token')) {
                $errorMessage = 'Failed to generate authentication token. Please try again.';
                $errorCode = 'token_creation_failed';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE') || str_contains($e->getMessage(), 'database')) {
                $errorMessage = 'Database error during registration. Please try again.';
                $errorCode = 'database_error';
            }
            
            // Always include error details in response for debugging (even in production)
            // This helps diagnose issues without needing to check logs
            return response()->json([
                'message' => $errorMessage,
                'error' => $errorCode,
                'error_details' => [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        Log::info('=== LOGIN START ===', ['username' => $request->username]);
        
        try {
            Log::info('Login: Starting validation');
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::info('Login: Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            Log::info('Login: Querying user from database');
            $user = User::where('username', $request->username)->first();
            Log::info('Login: User query completed', ['user_found' => $user !== null, 'user_id' => $user ? $user->id : null]);

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::info('Login: Invalid credentials');
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            Log::info('Login: Checking Passport client');
            
            // Check if Passport tables exist first
            $passportTableExists = DB::getSchemaBuilder()->hasTable('oauth_personal_access_clients');
            
            if (!$passportTableExists) {
                Log::error('Passport tables do not exist. Running passport:install...');
                try {
                    $exitCode = Artisan::call('passport:install', ['--force' => true]);
                    $output = Artisan::output();
                    Log::info('Passport install completed during login', [
                        'exit_code' => $exitCode,
                        'output' => $output
                    ]);
                    
                    if ($exitCode !== 0) {
                        throw new \Exception('passport:install returned exit code ' . $exitCode . '. Output: ' . $output);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to run passport:install: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Authentication service not properly configured. Please contact support.',
                        'error' => 'passport_setup_failed',
                        'error_details' => [
                            'message' => $e->getMessage(),
                            'type' => get_class($e)
                        ]
                    ], 500);
                }
            }
            
            // Check if Passport personal access client exists and is properly linked
            try {
                $personalAccessClient = DB::table('oauth_personal_access_clients')
                    ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                    ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                    ->first();
            } catch (\Exception $e) {
                // If table still doesn't exist or query fails, try passport:install again
                Log::error('Error querying Passport client: ' . $e->getMessage());
                try {
                    $exitCode = Artisan::call('passport:install', ['--force' => true]);
                    $output = Artisan::output();
                    Log::info('Passport install completed after query error during login', [
                        'exit_code' => $exitCode,
                        'output' => $output
                    ]);
                    
                    if ($exitCode !== 0) {
                        throw new \Exception('passport:install returned exit code ' . $exitCode . '. Output: ' . $output);
                    }
                    
                    $personalAccessClient = DB::table('oauth_personal_access_clients')
                        ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                        ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                        ->first();
                } catch (\Exception $e2) {
                    Log::error('Failed to run passport:install after query error: ' . $e2->getMessage(), [
                        'trace' => $e2->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Authentication service not properly configured. Please contact support.',
                        'error' => 'passport_setup_failed',
                        'error_details' => [
                            'message' => $e2->getMessage(),
                            'type' => get_class($e2)
                        ]
                    ], 500);
                }
            }
            
            Log::info('Login: Passport client check completed', ['client_exists' => $personalAccessClient !== null, 'client_id' => $personalAccessClient ? $personalAccessClient->client_id : null]);

            if (!$personalAccessClient) {
                Log::error('Passport Personal Access Client not found or not properly linked. Attempting to create...');
                // Try to create the client programmatically
                try {
                    $clientRepository = app(\Laravel\Passport\ClientRepository::class);
                    Log::info('Login: Creating Passport client');
                    $client = $clientRepository->createPersonalAccessClient(
                        null,
                        'Star Recruiting Personal Access Client',
                        'http://localhost'
                    );
                    Log::info('Passport Personal Access Client created successfully with ID: ' . $client->id);
                    
                    // Verify it was created properly
                    $verifyClient = DB::table('oauth_personal_access_clients')
                        ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                        ->where('oauth_clients.id', $client->id)
                        ->first();
                    
                    if (!$verifyClient) {
                        throw new \Exception('Personal access client created but not properly linked');
                    }
                    Log::info('Login: Passport client verified successfully');
                } catch (\Exception $e) {
                    Log::error('Failed to create Passport client: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Authentication service not properly configured. Please contact support.',
                        'error' => 'passport_client_missing',
                        'debug' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }

            Log::info('Login: Creating token');
            // Create token with error handling
            try {
                $token = $user->createToken('StarRecruiting')->accessToken;
                Log::info('Login: Token created successfully');
            } catch (\Exception $tokenException) {
                Log::error('Token creation failed: ' . $tokenException->getMessage(), [
                    'trace' => $tokenException->getTraceAsString(),
                    'user_id' => $user->id,
                    'exception_class' => get_class($tokenException)
                ]);
                
                // Check if it's a Passport client issue
                $clientCheck = DB::table('oauth_clients')
                    ->where('name', 'like', '%Personal Access Client%')
                    ->first();
                
                if (!$clientCheck) {
                    Log::error('Login: No Passport client found after token creation failure');
                    return response()->json([
                        'message' => 'Authentication service configuration error. Please contact support.',
                        'error' => 'passport_client_missing',
                        'debug' => config('app.debug') ? 'No Passport Personal Access Client found in database' : null
                    ], 500);
                }
                
                return response()->json([
                    'message' => 'Failed to generate authentication token. Please try again.',
                    'error' => 'token_creation_failed',
                    'debug' => config('app.debug') ? $tokenException->getMessage() : null
                ], 500);
            }

            Log::info('Login: Success, returning response', ['user_id' => $user->id, 'role' => $user->role]);
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception_class' => get_class($e)
            ]);
            return response()->json([
                'message' => 'An error occurred during login. Please try again.',
                'error' => 'internal_error',
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
}

