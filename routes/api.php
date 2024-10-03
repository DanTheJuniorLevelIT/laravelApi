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
Route::post('/registerLearner', [ExecuteController::class, 'registerLearner']);
Route::post('/loginLearner', [ExecuteController::class, 'loginLearner']);
Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::middleware(['teacher'])->group(function(){
        Route::get('/teacherSub/{id}', [ExecuteController::class, 'teacherSubjects']);
        Route::post('/subjects/createDiscuss', [ExecuteController::class, 'createDiscussion']);
        Route::post('/subjects/create', [ExecuteController::class, 'createAssessment']);
        Route::post('/subjects/createQuestion', [ExecuteController::class, 'createQuestion']);
        Route::post('/subjects/createAnnouncement', [ExecuteController::class, 'createAnnouncement']);
        Route::put('/subjects/editQuestion/{id}', [ExecuteController::class, 'editQuestion']);
        Route::delete('/subjects/deleteQuestion/{id}', [ExecuteController::class, 'deleteQuestion']);
        Route::get('/subjects/showAnnouncement/{id}', [ExecuteController::class, 'showAnnouncement']);
        Route::get('/subjects/showQuestion/{id}', [ExecuteController::class, 'showQuestions']);
        Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
        Route::get('/subjects/allSubjects/{id}', [ExecuteController::class, 'teacherAllSubjects']);
        Route::get('/subjects/discussion', [ExecuteController::class, 'showDiscussion']);
        Route::get('/subjects/discussion/replies/{discussionid}', [ExecuteController::class, 'viewDiscussionReplies']);
        Route::post('/subjects/discussion/reply', [ExecuteController::class, 'sendDiscussionReplies']);
        Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
        Route::get('/subjects/showAssessment/{id}', [ExecuteController::class, 'showAssessmentDetails']);
        route::get('/subjects/getCompleted/{id}', [ExecuteController::class, 'getCompletionStats']);
        Route::get('/subjects/students/{id}/{assid}', [ExecuteController::class, 'showStudents']);
        Route::post('/subjects/autocheck/{id}/{assid}', [ExecuteController::class, 'autoCheck']);
        Route::post('/subjects/submitScore', [ExecuteController::class, 'submitScore']);
        Route::get('/subjects/checking/{id}/{lrnid}', [ExecuteController::class, 'showStudentAnswers']);
        Route::get('/modules/{id}', [ExecuteController::class, 'showSubModules']);
        Route::get('/subjects/{id}', [ExecuteController::class, 'show']);
    });

    Route::middleware(['admin'])->group(function(){
        
    });

    Route::middleware(['student'])->group(function(){
        
    });
    Route::post('/logoutAdmin', [ExecuteController::class, 'logoutAdmin'])->middleware('auth:sanctum');
});




































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