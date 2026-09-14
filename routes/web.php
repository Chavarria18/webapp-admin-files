<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth; 


Route::get('/', function () {
    return view('welcome');
});


Route::resource("auth",Auth::class);