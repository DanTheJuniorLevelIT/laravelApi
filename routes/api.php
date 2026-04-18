
<?php

use App\Http\Controllers\ExecuteController;
use App\Models\Execute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "API";
});

// Route::get('/subjects', [ExecuteController::class, 'index']);
Route::post('/registerAdmin', [ExecuteController::class, 'registerAdmin']);
// Route::post('/registerLearner', [ExecuteController::class, 'registerLearner']);
// Route::post('/loginLearner', [ExecuteController::class, 'loginLearner']);
Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
Route::post('/sendResetCode', [ExecuteController::class, 'resetCode']);

// Learner Studen API
// Route::post('/registerAdmin', [ExecuteController::class, 'registerAdmin']);
Route::post(uri: '/registerLearner', action: [ExecuteController::class, 'registerLearner']);
// Route::post('/loginAdmin', [ExecuteController::class, 'loginAdmin']);
Route::post('/loginLearner', [ExecuteController::class, 'loginLearner']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::middleware(['teacher'])->group(function(){
        Route::get('/subjects', [ExecuteController::class, 'index']);
        Route::get('/teacherSub/{id}', [ExecuteController::class, 'teacherSubjects']);
        Route::post('/subjects/createDiscuss', [ExecuteController::class, 'createDiscussion']);
        Route::post('/subjects/create', [ExecuteController::class, 'createAssessment']);
        Route::get('/subjects/assessments/{id}', [ExecuteController::class, 'getSingleAssessment']);
        Route::post('/subjects/assessments/{id}', [ExecuteController::class, 'updateAssessment']);
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
        Route::put('/assessment/updateAvailability/{assessmentID}', [ExecuteController::class, 'updateAvailability']);
        Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment']);
        Route::get('/subjects/showAssessment/{id}', [ExecuteController::class, 'showAssessmentDetails']);
        Route::get('/subjects/getStudentsByClass/{cid}', [ExecuteController::class, 'getStudentsByClass']);
        Route::get('/subjects/getAssessmentsByClass/{cid}', [ExecuteController::class, 'getAssessmentsByClass']);
        route::get('/subjects/getCompleted/{id}/{cid}', [ExecuteController::class, 'getCompletionStats']);
        Route::get('/subjects/students/{id}/{assid}', [ExecuteController::class, 'showStudents']);
        Route::get('/subjects/learnerassessments/{lrn}/{cid}', [ExecuteController::class, 'getLearnerAssessments']);
        Route::get('/subjects/assessTotalPoints/{aid}', [ExecuteController::class, 'assessmentTotalPoints']);
        Route::post('/subjects/autocheck/{id}/{assid}', [ExecuteController::class, 'autoCheck']);
        Route::post('/subjects/submitScore', [ExecuteController::class, 'submitScore']);
        Route::post('/subjects/updateScore', [ExecuteController::class, 'updateAssessScore']);
        Route::get('/subjects/checking/{id}/{lrnid}', [ExecuteController::class, 'showStudentAnswers']);
        Route::get('/modules/{id}', [ExecuteController::class, 'showSubModules']);
        Route::get('/subjects/{id}', [ExecuteController::class, 'show']);
        // zaina works
        Route::post('/modules/create', [ExecuteController::class, 'createModule']);
        Route::get('/modules/showModules/{id}', [ExecuteController::class, 'showModulesDetails']);
        Route::post('/modules/updateDate/{id}', [ExecuteController::class, 'updateModuleDate']);
        Route::post('/modules/createLesson', [ExecuteController::class, 'createLesson']);
        Route::get('/modules/showLessons/{id}', [ExecuteController::class, 'showLessonDetails']);
        Route::get('/modules/getlessonid/{id}', [ExecuteController::class, 'getlessonid']);
        Route::patch('/modules/updateLessonInfo/{id}', [ExecuteController::class, 'updateLessonInfo']);
        Route::delete('modules/deleteLesson/{id}', [ExecuteController::class, 'deleteLesson']);
        Route::post('modules/uploadMedia', [ExecuteController::class, 'uploadMedia']);
        Route::delete('/modules/deleteFile/{id}', [ExecuteController::class, 'deleteFile']);
        Route::delete('/modules/deleteMediaFile/{id}', [ExecuteController::class, 'deleteMediaFile']);
        //mark works
        Route::get('/messages/conversation/{id}', [ExecuteController::class, 'viewConvo']);
        Route::get('/messages/unread/{id}', [ExecuteController::class, 'getUnreadMessages']);
        Route::post('/messages/mark-read', [ExecuteController::class, 'markAllMessagesAsRead']);
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
        Route::get('/subjects', [ExecuteController::class, 'index2']);
        Route::get('/getSubjects/{lrn}', [ExecuteController::class, 'getSubjects']);
        Route::get('/getSubjectsToday', [ExecuteController::class, 'getSubjectsToday']);
        Route::get('/getModules', [ExecuteController::class, 'getModules']);
        Route::get('/getLessonID', [ExecuteController::class, 'getLessonID2']);
        Route::get('/getLessons', [ExecuteController::class, 'getLessons']);
        Route::get('/getQuestions', [ExecuteController::class, 'getQuestions']);
        Route::get('/getAssessments', [ExecuteController::class, 'getAssessments']);
        Route::post('/saveAnswers', [ExecuteController::class, 'saveAnswers']);
        Route::get('/getAssessmentProgress', [ExecuteController::class, 'getAssessmentProgress']);
        Route::post('/logoutLearner', [ExecuteController::class, 'logoutLearner'])->middleware('auth:sanctum');
        Route::middleware('auth:sanctum')->get('/getLearnerByToken', [ExecuteController::class, 'getLearnerByToken']);
        Route::get('/getLearner/{lrn}', [ExecuteController::class, 'getLearner']);
        Route::get('/getAnswerFile', [ExecuteController::class, 'getAnswerFile']);
        Route::post('/saveAssessmentsAnswer', [ExecuteController::class, 'saveAssessmentsAnswer']);
        Route::post('/updateLearnerPassword/{lrn}', [ExecuteController::class, 'updateLearnerPassword']);
        Route::get('/getPendingAssessments', [ExecuteController::class, 'getPendingAssessments']);
        Route::get('/getDiscussions', [ExecuteController::class, 'getDiscussions']);
        Route::post('/updateProfilePicture', [ExecuteController::class, 'uploadProfilePicture2']);
        Route::post('/updateFile', [ExecuteController::class, 'uploadFile']);
        Route::get('/discussionReplies/{discussionid}', [ExecuteController::class, 'viewDiscussionReplies']);
        Route::post('/discussionReply', [ExecuteController::class, 'sendDiscussionReplies']);
        Route::get('/checkProgress', [ExecuteController::class, 'checkProgress']);
        Route::get('/getScore', [ExecuteController::class, 'getScore']);
        Route::get('/getFile', [ExecuteController::class, 'getFile']);
        Route::post('/uploadFile', [ExecuteController::class, 'uploadFile']);
        Route::get('/getAnnouncements', [ExecuteController::class, 'getAnnouncements']);
        Route::get('/getResultAnalysis', [ExecuteController::class, 'getResultAnalysis']);
        Route::get('/getmoduleID', [ExecuteController::class, 'getmoduleID']);

        //Message Component
        Route::get('/messages/{id}', [ExecuteController::class, 'showMessages2']);
        Route::get('/admins/{id}', [ExecuteController::class, 'getAdmin']);
        Route::post('/messages/reply', [ExecuteController::class, 'sendReply2']);
        Route::post('/messages/compose', [ExecuteController::class, 'sendMessage2']);
        Route::get('/messages/unread/{lrn}', [ExecuteController::class, 'getUnreadMessages2']);
        Route::post('/messages/clear', [ExecuteController::class, 'clearUnreadMessages']);
        Route::get('/messages/getAdminDetails/{lrn}', [ExecuteController::class, 'getAdminDetails']);

        Route::post('/subjects/create', [ExecuteController::class, 'createAssessment2']);
        Route::get('/subjects/showAll', [ExecuteController::class, 'showAll']);
        Route::get('/subjects/assessment', [ExecuteController::class, 'showAssessment2']);
        Route::get('/subjects/{id}', [ExecuteController::class, 'show']);

        // Change Password Component
        Route::post('/request-change-password', [ExecuteController::class, 'requestChangePassword']);
        Route::post('/get-password-change-status', [ExecuteController::class, 'getPasswordChangeRequestStatus']);
        Route::post('/change-password/{email}', [ExecuteController::class, 'changePassword']);
    });
    Route::post('/logoutAdmin', [ExecuteController::class, 'logoutAdmin'])->middleware('auth:sanctum');
});
