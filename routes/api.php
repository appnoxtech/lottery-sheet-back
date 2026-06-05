<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LotteryRequestController;
use App\Http\Controllers\LotteryTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerification']);

Route::get('/admin/approve/{id}/{hash}', [AuthController::class, 'approveAdmin'])->middleware(['signed'])->name('admin.approve');

Route::post('/lottery-requests', [LotteryRequestController::class, 'store']);
Route::get('/lottery-types', [LotteryTypeController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/admin/requests', [LotteryRequestController::class, 'index']);
    Route::patch('/admin/requests/bulk-status', [LotteryRequestController::class, 'bulkUpdateStatus']);
    Route::patch('/admin/requests/{id}/process', [LotteryRequestController::class, 'updateStatus']);
    Route::post('/admin/requests/share-email', [LotteryRequestController::class, 'shareViaEmail']);

    Route::apiResource('/admin/lottery-types', LotteryTypeController::class);
    Route::post('/admin/lottery-types/reorder', [LotteryTypeController::class, 'reorder']);

    // Admin User Management
    Route::get('/admin/pending-users', [AuthController::class, 'getPendingUsers']);
    Route::post('/admin/users/{id}/approve', [AuthController::class, 'approveUser']);

    // Admin Profile (WhatsApp number, name)
    Route::patch('/admin/profile', [AuthController::class, 'updateProfile']);
});
