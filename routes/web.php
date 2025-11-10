<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/recruiter/home', function () {
    return view('recruiter.home');
});

Route::get('/recruiter/candidate/{id}', function () {
    return view('recruiter.candidate-view');
});

Route::get('/recruiter/settings', function () {
    return view('recruiter.settings');
});

