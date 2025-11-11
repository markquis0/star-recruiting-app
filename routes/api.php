<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\RecruiterController;

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
    Route::get('/questions/{type}', [CandidateController::class, 'getQuestions']);
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


