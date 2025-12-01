<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicProfileController;

// Public profile route (no auth required)
Route::get('/profile/{token}', [PublicProfileController::class, 'show'])
    ->name('public.profile.show');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/candidate/home', function () {
    return view('candidate.home');
});

Route::get('/candidate/settings', function () {
    return view('candidate.settings');
});

Route::get('/candidate/assessment/{id}', function () {
    return view('candidate.assessment');
});

Route::get('/candidate/assessment/{id}/view', function () {
    return view('candidate.assessment-view');
});

Route::get('/candidate/form/{id}', function () {
    return view('candidate.form');
});

Route::get('/candidate/form/{id}/view', function () {
    return view('candidate.form-view');
});

Route::get('/candidate/projects', function () {
    return view('candidate.projects.index');
});

Route::get('/candidate/projects/new', function () {
    return view('candidate.projects.form', ['project' => null]);
});

Route::get('/candidate/projects/{id}/edit', function ($id) {
    return view('candidate.projects.form', ['projectId' => $id]);
});

Route::get('/recruiter/home', function () {
    return view('recruiter.home');
});

Route::get('/recruiter/candidate/{id}', function () {
    return view('recruiter.candidate-view');
});

Route::get('/recruiter/settings', function () {
    return view('recruiter.settings');
});

