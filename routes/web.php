<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

## AUTH 
Route::prefix('auth')->name('auth.')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::post('/register', [AuthController::class, 'storeUser'])
        ->name('login.register');

    Route::get('/new-password', [AuthController::class, 'showNewPasswordForm'])
        ->name('new-password');

    Route::post('/new-password', [AuthController::class, 'updatePassword'])
        ->name('new-password.store');
});

##HOME

Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('cognito.auth');