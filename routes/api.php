<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
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
Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/admin/create', 'register');
        Route::post('/admin/login',  'login');
        Route::post('/user/create', 'register');
        Route::post('/user/login',  'login');

        Route::middleware(['auth'])->group(function () {
            Route::post('/user/logout', 'logout');
            Route::post('/admin/logout', 'logout');
        });
    });

    Route::controller(PasswordResetController::class)->group(function(){
        Route::post('/user/forgot-password', 'forgot_password');
        Route::post('/user/reset-password-tokens',  'password_reset');
    });

    Route::middleware(['jwt'])->group(function () {
        Route::controller(AdminController::class)->prefix('/admin')->name('admin.')->group(function(){
            Route::get('/user-listing', 'userList')->name('user-list');
            Route::put('/user-edit/{uuid}', 'userEdit')->name('user-edit');
            Route::delete('/user-delete/{uuid}', 'userDelete')->name('user-delete');
        });

        Route::controller(UserController::class)->prefix('/user')->name('user.')->group(function(){
            Route::get('/', 'get');
            Route::get('/orders', 'orders')->name('orders');
            Route::put('/edit', 'edit')->name('edit');
            Route::delete('/delete', 'delete')->name('delete');
        });
    });
});

