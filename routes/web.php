<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistoryController;

## AUTH 
Route::prefix('auth')->name('auth.')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');   

    Route::get('/new-password', [AuthController::class, 'showNewPasswordForm'])
        ->name('new-password');

    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('forgot-password');

    Route::post('/new-password', [AuthController::class, 'updatePassword'])
        ->name('new-password.store');

    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])
        ->name('send-reset-code');
    Route::get(
        '/forgot-password/confirm',
        [AuthController::class, 'confirmPassword']
    )->name('confirm-forgot-password.index');

    Route::post(
        '/forgot-password/confirm',
        [AuthController::class, 'confirmForgotPassword']
    )->name('confirm-forgot-password');

    Route::get('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

##HOME

Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('cognito.auth');

Route::get('/recycle', [FileController::class, 'recycleBin'])
    ->name('recycle')
    ->middleware('cognito.auth');

Route::get('/history', [HistoryController::class, 'index'])
    ->name('history')
    ->middleware(['cognito.auth', 'admin']);



Route::get('/files', [FileController::class, 'showFilesForm'])
    ->name('files')->middleware('cognito.auth');
;

Route::post('/files', [FileController::class, 'storeFiles'])
    ->name('files.store')->middleware('cognito.auth');
Route::delete('/files/{file}/destroy', [FileController::class, 'destroy'])
    ->name('files.destroy')->middleware('cognito.auth');
Route::delete('/files/{file}/fdestroy', [FileController::class, 'forceDelete'])
    ->name('files.fdestroy')->middleware('cognito.auth');


Route::get('/files/{file}/download', [FileController::class, 'downloadFiles'])
    ->name('files.download')->middleware('cognito.auth');
Route::get('/files/check-name', [FileController::class, 'checkName'])
    ->name('files.check-name')->middleware('cognito.auth');
Route::patch('/files/{id}/restore', [FileController::class, 'restoreFile'])
    ->name('files.restore')->middleware('cognito.auth');

##Usuarios

Route::middleware(['cognito.auth', 'admin:admin,gerente,jefe_area'])->prefix('users')->name('users.')->group(function(){
    ##Creacion de usuairos
    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('register');
    Route::post('/register', [AuthController::class, 'storeUser'])
        ->name('register');


    Route::get('/',[UserController::class,'index'])->name('index');
    Route::get('/organigrama', [UserController::class, 'organigrama'])->name('organigrama');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});
