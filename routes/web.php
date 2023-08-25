<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\DonationsController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/users', [UserController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/followers/add', [FollowerController::class, 'add']);
Route::get('/donations/add', [DonationController::class, 'add']);
Route::get('/subscribers/add', [SubscriberController::class, 'add']);
Route::get('/sales/add', [SaleController::class, 'add']);
