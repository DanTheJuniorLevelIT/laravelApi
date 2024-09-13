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
Route::post('/registerAdmin', [ExecuteController::class, 'registerAdmin']);
Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
Route::post('/logoutAdmin', [ExecuteController::class, 'logoutAdmin'])->middleware('auth:sanctum');
Route::post('/loginLearner', [ExecuteController::class, 'loginLearner']);
Route::post('/subjects/create', [ExecuteController::class, 'createAssessment']);
Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
Route::get('/subjects/allSubjects/{id}', [ExecuteController::class, 'teacherAllSubjects']);
Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
Route::get('/modules/{id}', [ExecuteController::class, 'showSubModules']);
Route::get('/subjects/{id}', [ExecuteController::class, 'show']);
Route::get('/teacherSub/{id}', [ExecuteController::class, 'teacherSubjects']);
// Route::middleware(['auth:admin'])->group(function () {
    // Routes that require admin authentication
    // Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
// });

// Route::get('/subjects', 'ExecuteController@index');
// Route::post('/users', 'ExecuteController@store');

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
