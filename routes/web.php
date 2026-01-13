<?php

use Illuminate\Support\Facades\Route;

// Home redirect
Route::get('/', function () {
    return redirect()->route('email.draft');
});

// Email routes - return views directly
Route::get('/email/draft', function () {
    return view('emails.draft');
})->name('email.draft');

Route::get('/email/response', function () {
    return view('emails.response');
})->name('email.response');

Route::get('/email/analyze', function () {
    return view('emails.analyze');
})->name('email.analyze');

Route::get('/email/summarize', function () {
    return view('emails.summarize');
})->name('email.summarize');

// Template routes
Route::get('/templates', function () {
    return view('templates.index');
})->name('template.index');

Route::get('/templates/create', function () {
    return view('templates.create');
})->name('template.create');

// Settings
Route::get('/settings', function () {
    return view('settings');
})->name('settings');