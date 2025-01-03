<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/burndown', function () {
    return view('burndown');
})->middleware('auth');

Route::get('/token', function () {
    $user = Auth::user();
    $user->tokens()->delete();
    $token = $user->createToken('burndown-api');

    return $token->plainTextToken;
})->middleware('auth');
