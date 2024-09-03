<?php

use App\Http\Controllers\ExecuteController;
use App\Models\Execute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "API";
});

// Route::apiResource('execute', ExecuteController::class);
Route::get('/subjects', [ExecuteController::class, 'index']);
Route::post('/subjects/create', [ExecuteController::class, 'createAssessment']);
Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
Route::get('/subjects/{id}', [ExecuteController::class, 'show']);

// Route::get('/subjects', 'ExecuteController@index');
// Route::post('/users', 'ExecuteController@store');

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
