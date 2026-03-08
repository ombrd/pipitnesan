<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PTController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\VisitController;

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

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('branches', [App\Http\Controllers\Api\BranchController::class, 'index']);
Route::get('promotions', [App\Http\Controllers\Api\PromotionController::class, 'index']);

Route::group(['middleware' => 'auth:api'], function () {

    // Auth & Profile
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('me', [AuthController::class, 'updateProfile']);
    
    // QR Attendance Payload Generation
    Route::get('qr/generate', [AuthController::class, 'generateQR']);

    // PT Information
    Route::get('pts', [PTController::class, 'index']);
    Route::get('pts/{id}/schedules', [PTController::class, 'schedules']);

    // Bookings & Visits
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('pts/book', [BookingController::class, 'store']);
    Route::post('pts/book/{id}/cancel', [App\Http\Controllers\Api\BookingController::class, 'cancel']);
    Route::get('visits', [VisitController::class, 'index']);

});
