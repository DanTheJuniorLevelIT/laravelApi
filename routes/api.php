<?php

use App\Http\Controllers\ExecuteController;
use App\Models\Execute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "API";
});


Route::get('/subjects', [ExecuteController::class, 'index']);
Route::post('/registerAdmin', [ExecuteController::class, 'registerAdmin']);
Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
Route::post('/logoutAdmin', [ExecuteController::class, 'logoutAdmin'])->middleware('auth:sanctum');
Route::post('/loginLearner', [ExecuteController::class, 'loginLearner']);
Route::post('/subjects/create', [ExecuteController::class, 'createAssessment']);
Route::post('/subjects/createQuestion', [ExecuteController::class, 'createQuestion']);
Route::put('/subjects/editQuestion/{id}', [ExecuteController::class, 'editQuestion']);
// Route::get('/subjects/editQuestion/{id}', [ExecuteController::class, 'editQuestion']);
Route::get('/subjects/showQuestion/{id}', [ExecuteController::class, 'showQuestions']);
Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
Route::get('/subjects/allSubjects/{id}', [ExecuteController::class, 'teacherAllSubjects']);
Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
Route::get('/subjects/showAssessment/{id}', [ExecuteController::class, 'showAssessmentDetails']);
Route::get('/modules/{id}', [ExecuteController::class, 'showSubModules']);
Route::get('/subjects/{id}', [ExecuteController::class, 'show']);
Route::get('/teacherSub/{id}', [ExecuteController::class, 'teacherSubjects']);
// zaina works
Route::post('/modules/create', [ExecuteController::class, 'createModule']);
Route::get('/modules/showModules/{id}', [ExecuteController::class, 'showModulesDetails']);
Route::post('/modules/createLesson', [ExecuteController::class, 'createLesson']);
Route::get('/modules/showLessons/{id}', [ExecuteController::class, 'showLessonDetails']);
Route::get('/modules/getlessonid/{id}', [ExecuteController::class, 'getlessonid']);
Route::patch('/modules/updateLessonInfo/{id}', [ExecuteController::class, 'updateLessonInfo']);
Route::delete('modules/deleteLesson/{id}', [ExecuteController::class, 'deleteLesson']);
Route::post('modules/uploadMedia', [ExecuteController::class, 'uploadMedia']);
Route::delete('/modules/deleteFile/{id}', [ExecuteController::class, 'deleteFile']);



































// Route::apiResource('execute', ExecuteController::class);
// Route::get('/forgetpass', function () {
//     return view('welcome');
// });

// Route::middleware(['auth:admin'])->group(function () {
    // Routes that require admin authentication
    // Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
// });

// Route::get('/subjects', 'ExecuteController@index');
// Route::post('/users', 'ExecuteController@store');

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');