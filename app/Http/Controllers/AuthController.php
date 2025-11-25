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

            // Check if Passport personal access client exists and is properly linked
            $personalAccessClient = DB::table('oauth_personal_access_clients')
                ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                ->first();

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
                    return response()->json([
                        'message' => 'Authentication service not properly configured. Please contact support.',
                        'error' => 'passport_client_missing',
                        'debug' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
            }

            $token = $user->createToken('StarRecruiting')->accessToken;

            return response()->json([
                'message' => 'Registration successful',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'An error occurred during registration. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'internal_error'
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
            // Check if Passport personal access client exists and is properly linked
            $personalAccessClient = DB::table('oauth_personal_access_clients')
                ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
                ->where('oauth_clients.name', 'like', '%Personal Access Client%')
                ->first();
            
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

