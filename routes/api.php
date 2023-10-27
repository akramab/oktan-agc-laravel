<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('auth.register');
    Route::post('/login', 'login')->name('auth.login');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'index')->name('user.get-list');
        Route::post('/user/verify/{id}', 'verifyPayment')->name('user.verify-payment');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::post('/user/profile', 'updateOrCreate')->name('user.update-or-create-profile');
        Route::get('/user/profile', 'get')->name('user.get-profile');
        Route::get('/user/profile/{id}', 'downloadDocument')->name('user.profile.download-document');
    });
});
