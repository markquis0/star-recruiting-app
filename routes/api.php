<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\RecruiterController;

// Health Check Routes (for debugging)
Route::get('/health-db', function () {
    try {
        \DB::connection()->getPdo();
        return response()->json(['ok' => true, 'db' => 'connected']);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/health-passport', function () {
    try {
        $client = \DB::table('oauth_personal_access_clients')
            ->join('oauth_clients', 'oauth_personal_access_clients.client_id', '=', 'oauth_clients.id')
            ->where('oauth_clients.name', 'like', '%Personal Access Client%')
            ->first();
        
        return response()->json([
            'ok' => true,
            'passport_client_exists' => $client !== null,
            'client_id' => $client ? $client->client_id : null
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Candidate Routes
Route::middleware('auth:api')->prefix('candidate')->group(function () {
    Route::get('/home', [CandidateController::class, 'home']);
    Route::get('/profile', [CandidateController::class, 'getProfile']);
    Route::put('/profile', [CandidateController::class, 'updateProfile']);
    Route::post('/form', [CandidateController::class, 'createForm']);
    Route::get('/form/{id}', [CandidateController::class, 'getForm']);
    Route::post('/form/{id}', [CandidateController::class, 'updateForm']);
    Route::delete('/form/{id}', [CandidateController::class, 'deleteForm']);
    Route::post('/assessment/{form_id}', [CandidateController::class, 'submitAssessment']);
    Route::get('/assessment/{id}', [CandidateController::class, 'getAssessment']);
    Route::get('/questions/{type}', [CandidateController::class, 'getQuestions']);
    
    // Project routes
    Route::get('/projects', [CandidateController::class, 'getProjects']);
    Route::post('/projects', [CandidateController::class, 'createProject']);
    Route::get('/projects/{id}', [CandidateController::class, 'getProject']);
    Route::put('/projects/{id}', [CandidateController::class, 'updateProject']);
    
    // Public profile management
    Route::get('/public-profile', [CandidateController::class, 'getPublicProfile']);
    Route::post('/public-profile/generate', [CandidateController::class, 'generatePublicProfile']);
    Route::post('/public-profile/disable', [CandidateController::class, 'disablePublicProfile']);
});

// Recruiter Routes
Route::middleware('auth:api')->prefix('recruiter')->group(function () {
    Route::get('/home', [RecruiterController::class, 'home']);
    Route::get('/profile', [RecruiterController::class, 'getProfile']);
    Route::put('/profile', [RecruiterController::class, 'updateProfile']);
    Route::get('/search', [RecruiterController::class, 'search']);
    Route::post('/save', [RecruiterController::class, 'saveCandidate']);
    Route::delete('/save/{id}', [RecruiterController::class, 'unsaveCandidate']);
    Route::get('/candidate/{id}', [RecruiterController::class, 'viewCandidate']);
});


