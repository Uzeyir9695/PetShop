<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Auth\UserAuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MainPageController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\FileController;
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
    Route::controller(AdminAuthController::class)->group(function(){
        Route::post('/admin/create', 'register')->name('admin.register');
        Route::post('/admin/login',  'login')->name('admin.login')->middleware(['jwt']);

        Route::middleware(['auth:jwt', 'jwt'])->group(function () {
            Route::post('/admin/logout', 'logout')->name('logout');
        });
    });

    Route::controller(UserAuthController::class)->group(function(){
        Route::post('/user/create', 'register')->name('user.register');
        Route::post('/user/login',  'login')->name('user.login')->middleware(['jwt']);

        Route::middleware(['auth:jwt', 'jwt'])->group(function () {
            Route::post('/user/logout', 'logout')->name('logout');
        });
    });
    Route::controller(PasswordResetController::class)->group(function(){
        Route::post('/user/forgot-password', 'forgot_password');
        Route::post('/user/reset-password-tokens',  'password_reset');
    });

    Route::middleware(['auth:jwt'])->group(function () {
        Route::middleware('jwt')->group(function () {
            Route::controller(AdminController::class)->prefix('/admin')->name('admin.')->group(function () {
                Route::get('/user-listing', 'userList')->name('user-list');
                Route::put('/user-edit/{uuid}', 'userEdit')->name('user-edit');
                Route::delete('/user-delete/{uuid}', 'userDelete')->name('user-delete');
            });

            Route::controller(UserController::class)->prefix('/user')->name('user.')->group(function () {
                Route::get('/', 'get');
                Route::get('/orders', 'orders')->name('orders');
                Route::put('/edit', 'edit')->name('edit');
                Route::delete('/delete', 'delete')->name('delete');
            });
        });

        Route::controller(MainPageController::class)->prefix('/main')->group(function(){
            Route::get('/blog', 'blogList');
            Route::get('/blog/{uuid}', 'blogShow');
            Route::get('/promotions', 'promotions');
        });

        Route::apiResources([
            '/brand' => BrandController::class,
            '/category' => CategoryController::class,
            '/order' => OrderController::class,
            '/order-status' => OrderStatusController::class,
            '/payments' => PaymentController::class,
            '/product' => ProductController::class,
        ]);
        Route::resource('/brands', BrandController::class)->only('index');
        Route::resource('/categories', CategoryController::class)->only('index');
        Route::resource('/orders', OrderController::class)->only('index');
        Route::resource('/order-statuses', OrderStatusController::class)->only('index');
        Route::resource('/products', ProductController::class)->only('index');

        Route::get('/order/{uuid}/download', [OrderController::class, 'orderDownload']);

        Route::controller(OrderController::class)->prefix('/orders')->group(function(){
            Route::get('/dashboard', 'orderDashboard');
            Route::get('/shipment-locator', 'shipmentLocator');
        });

        Route::controller(FileController::class)->prefix('/file')->group(function(){
            Route::post('/upload', 'fileUpload');
            Route::get('/{uuid}', 'getFile');
        });
    });
});
