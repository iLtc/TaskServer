<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\TaskController;

Route::middleware('auth:sanctum')->get('tasks', [TaskController::class, 'index']);
Route::middleware('auth:sanctum')->post('tasks', [TaskController::class, 'store']);
Route::middleware('auth:sanctum')->get('tasks/{id}', [TaskController::class, 'show']);
