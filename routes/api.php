<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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
Route::get('/events', [EventController::class, 'index']);
Route::get('/revenues', [EventController::class, 'revenue']);
Route::get('/followers', [EventController::class, 'followers']);
Route::get('/sales', [EventController::class, 'sales']);