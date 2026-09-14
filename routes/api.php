<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CognitoController;

Route::post('/register', [CognitoController::class, 'register']);