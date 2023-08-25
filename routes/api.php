<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\EventController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/users', [UserController::class, 'register']);
Route::get('/vars', [UserController::class, 'env']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/revenues', [EventController::class, 'revenue']);
Route::get('/followers', [EventController::class, 'followers']);
Route::get('/sales', [EventController::class, 'sales']);
Route::post('/followers', [FollowerController::class, 'add']);
Route::post('/donations', [DonationController::class, 'add']);
Route::post('/subscribers', [SubscriberController::class, 'add']);
Route::post('/sales', [SaleController::class, 'add']);
Route::post('/flags', [EventController::class, 'flag']);