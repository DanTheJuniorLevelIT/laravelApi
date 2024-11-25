
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
        Route::delete('modules/deleteAssessment/{id}', [ExecuteController::class, 'deleteAssessment']);
        Route::get('/subjects/showAnnouncement/{id}', [ExecuteController::class, 'showAnnouncement']);
        Route::delete('/subjects/deleteAnnouncement/{id}', [ExecuteController::class, 'deleteAnnouncement']);
        Route::get('/subjects/showQuestion/{id}', [ExecuteController::class, 'showQuestions']);
        Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
        Route::get('/subjects/allSubjects/{id}', [ExecuteController::class, 'teacherAllSubjects']);
        Route::get('/subjects/showDiscussion/{id}', [ExecuteController::class, 'showDiscussion']);
        Route::get('/subjects/discussion/{id}', [ExecuteController::class, 'countDiscussion']);
        Route::get('/subjects/discussion/replies/{discussionid}', [ExecuteController::class, 'viewDiscussionReplies']);
        Route::post('/subjects/discussion/reply', [ExecuteController::class, 'sendDiscussionReplies']);
        Route::put('/assessment/update-due-date/{assessmentID}', [ExecuteController::class, 'updateDueDate']);
        Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
        Route::get('/subjects/showAssessment/{id}', [ExecuteController::class, 'showAssessmentDetails']);
        route::get('/subjects/getCompleted/{id}', [ExecuteController::class, 'getCompletionStats']);
        Route::get('/subjects/students/{id}/{assid}', [ExecuteController::class, 'showStudents']);
        Route::post('/subjects/autocheck/{id}/{assid}', [ExecuteController::class, 'autoCheck']);
        Route::post('/subjects/submitScore', [ExecuteController::class, 'submitScore']);
        Route::post('/subjects/updateScore', [ExecuteController::class, 'updateAssessScore']);
        Route::get('/subjects/checking/{id}/{lrnid}', [ExecuteController::class, 'showStudentAnswers']);
        Route::get('/modules/{id}', [ExecuteController::class, 'showSubModules']);
        Route::get('/subjects/{id}', [ExecuteController::class, 'show']);
        // zaina works
        Route::post('/modules/create', [ExecuteController::class, 'createModule']);
        Route::get('/modules/showModules/{id}', [ExecuteController::class, 'showModulesDetails']);
        Route::put('/modules/updateDate/{id}', [ExecuteController::class, 'updateModuleDate']);
        Route::post('/modules/createLesson', [ExecuteController::class, 'createLesson']);
        Route::get('/modules/showLessons/{id}', [ExecuteController::class, 'showLessonDetails']);
        Route::get('/modules/getlessonid/{id}', [ExecuteController::class, 'getlessonid']);
        Route::patch('/modules/updateLessonInfo/{id}', [ExecuteController::class, 'updateLessonInfo']);
        Route::delete('modules/deleteLesson/{id}', [ExecuteController::class, 'deleteLesson']);
        Route::post('modules/uploadMedia', [ExecuteController::class, 'uploadMedia']);
        Route::delete('/modules/deleteFile/{id}', [ExecuteController::class, 'deleteFile']);
        Route::delete('/modules/deleteMediaFile/{id}', [ExecuteController::class, 'deleteMediaFile']);
        //mark works
        Route::get('/messages/{id}', [ExecuteController::class, 'showMessages']);
        Route::get('/students/{id}', [ExecuteController::class, 'getStudents']);
        Route::post('/messages/reply', [ExecuteController::class, 'sendReply']);
        Route::post('/messages/compose', [ExecuteController::class, 'sendMessage']);
        Route::post('/uploadProfilePicture/{id}', [ExecuteController::class, 'uploadProfilePicture']);
        Route::post('/updateAdminPassword/{id}', [ExecuteController::class, 'updateAdminPassword']);
    });

    Route::middleware(['admin'])->group(function(){
        
    });

    Route::middleware(['student'])->group(function(){
        
    });
    Route::post('/logoutAdmin', [ExecuteController::class, 'logoutAdmin'])->middleware('auth:sanctum');
});
