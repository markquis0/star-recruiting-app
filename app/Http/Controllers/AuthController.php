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

            Log::info('Login: Creating token');
            try {
                $token = $user->createToken('StarRecruiting')->accessToken;
                Log::info('Login: Token created successfully');
            } catch (\Exception $tokenException) {
                Log::error('Token creation failed: ' . $tokenException->getMessage(), [
                    'trace' => $tokenException->getTraceAsString(),
                    'user_id' => $user->id,
                    'exception_class' => get_class($tokenException)
                ]);
                
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
