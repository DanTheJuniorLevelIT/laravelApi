<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Models\Subject;
use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Discussion_Reply;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Learner;
use App\Models\Question;
use App\Models\Option;
use App\Models\Answer;
use App\Models\Assessment_Answer;
use App\Models\Roster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ExecuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        // Get the currently logged-in teacher's ID (assuming the teacher is logged in)
        // $teacherId = auth('admin')->user()->adminID;  // Adjust this according to how you're handling authentication
    
        // Get today's day name (e.g., 'Monday', 'Tuesday', etc.)
        $today = date('l');


        $dayOfWeek = date('N'); // Get the day of the week (1 = Monday, 7 = Sunday)

        $program = '';

        // Determine the program based on the current day
        if ($dayOfWeek == 1) {
            $program = 'blp';
        } elseif (in_array($dayOfWeek, [2, 3])) {
            $program = 'alsElem';
        } elseif (in_array($dayOfWeek, [4, 5])) {
            $program = 'alsJhs';
        }

        // Retrieve the subjects based on the program
        $subject = DB::table('classes')
                ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID') // left join
                ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid') // left join
                ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.schedule', 'rooms.location')
                ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
                ->get();

        return $subject;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function createDiscussion(Request $request)
    {
        $validatedData = $request->validate([
            'lesson_id' => 'required|integer',
            'discussion_topic' => 'required|string|max:255'
        ]);

        $discuss = Discussion::create($validatedData);
        return response()->json($discuss, 201);
    }

    public function createAssessment(Request $request)
    {
        //
        $validatedData = $request->validate([
            'lesson_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'instruction' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'due_date' => 'date',
        ]);

        $assess = Assessment::create($validatedData);
        return response()->json($assess, 201);
    }

    public function createQuestion(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required',
            'question' => 'required|string',
            'type' => 'required|string',
            'key_answer' => 'nullable|string',
            'points' => 'required|integer',
            'options' => 'array', // Validate that options is an array (for multiple-choice)
        ]);

        // Create question
        $question = Question::create([
            'assessment_id' => $request->assessment_id,
            'question' => $request->question,
            'type' => $request->type,
            'key_answer' => $request->key_answer,
            'points' => $request->points,
        ]);

        $options = $request->options ?? [];

        if ($request->type === 'multiple-choice' && !empty($options)) {
            foreach ($options as $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                ]);
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'question' => [
                'question_id' => $question->id,
                'assessment_id' => $question->assessment_id,
                'question' => $question->question,
                'type' => $question->type,
                'key_answer' => $question->key_answer,
                'points' => $question->points,
                'options' => $options,  // Return the options
            ]
        ], 201);
    }

    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'subjectID' => 'required',
            'title' => 'required|string',
            'instruction' => 'required|string'
        ]);

        // Check if an announcement for the subjectID exists
        $announcement = Announcement::where('subjectid', $request->subjectID)->first();

        if ($announcement) {
            // Update existing announcement
            Announcement::where('subjectid', $request->subjectID)->update([
                'title' => $request->title,
                'instruction' => $request->instruction
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'announcement' => [
                    'subjectID' => $announcement->subjectid,
                    'title' => $announcement->title,
                    'instruction' => $announcement->instruction
                ]
            ], 200);
        } else {
            // Create new announcement
            $announce = Announcement::create([
                'subjectid' => $request->subjectID,
                'title' => $request->title,
                'instruction' => $request->instruction
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'announcement' => [
                    'subjectID' => $announce->subjectid,
                    'title' => $announce->title,
                    'instruction' => $announce->instruction
                ]
            ], 201);
        }
    }



    /**
     * Display the specified resource.
     */

    public function showAnnouncement($id)
    {
        $announce = DB::table('subjects')
                        ->rightJoin('announcements', 'subjects.subjectid', '=', 'announcements.subjectid')
                        ->where('announcements.subjectid', $id)
                        ->select('announcements.announceid', 'announcements.title', 'announcements.instruction')
                        ->first();

        // return $announce;
        return response()->json($announce);
    }
    // public function show(Execute $id)
    public function show($id)
    {
        //
        $subject = DB::table('classes')
                ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
                ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
                ->where('classes.classid', '=', $id)
                ->first();

        if ($subject) {
            return response()->json($subject);
        } else {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    public function teacherSubjects($id)
    {
        //
        // Get today's day name (e.g., 'Monday', 'Tuesday', etc.)
        $today = date('l');

        // Retrieve the subjects based on the program
        $subject = DB::table('classes')
            ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID') // left join
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid') // left join
            ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.schedule', 'rooms.school')
            ->where('classes.adminid', $id) 
            ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
            ->get();

        $school = DB::table('classes')
            ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID') // left join
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid') // left join
            ->select('rooms.school')
            ->where('classes.adminid', $id) 
            ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
            ->first();

        if ($subject) {
            return [
                'subject' => $subject,
                'school' => $school
            ];
        } else {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    public function showSubModules($id)
    {
        $modules = DB::table('classes')
            ->rightJoin('modules', 'classes.classid', '=', 'modules.classid')
            ->select('modules.modules_id', 'modules.title')
            ->where('classes.classid', $id)
            ->get();

        if ($modules) {
            return [
                'modules' => $modules
            ];
        } else {
            return response()->json(['message' => 'Modules not found'], 404);
        }
    }

    public function showAssessmentDetails($id)
    {
        $assess = DB::table('lessons')
            ->rightJoin('assessments', 'lessons.lesson_id', '=', 'assessments.lesson_id')
            ->select('assessments.assessmentid', 'assessments.title', 'assessments.instruction', 'assessments.description')
            ->where('assessments.assessmentid', $id)
            ->first();

        return $assess;
    }

    public function showQuestions($id)
    {
        // Fetch questions and include options if the type is 'multiple-choice'
        $questions = DB::table('questions')
            ->leftJoin('options', 'questions.question_id', '=', 'options.question_id')
            ->select(
                'questions.question_id',
                'questions.assessment_id',
                'questions.question',
                'questions.type',
                'questions.key_answer',
                'questions.points',
                'questions.created_at',
                'questions.updated_at',
                'options.option_text'
            )
            ->where('questions.assessment_id', $id)
            ->orderBy('questions.created_at', 'asc')
            ->get();

        // Group the questions and their associated options together
        $groupedQuestions = $questions->groupBy('question_id')->map(function ($questionGroup) {
            $question = $questionGroup->first(); // Get the first record for each group

            return [
                'question_id' => $question->question_id,
                'assessment_id' => $question->assessment_id,
                'question' => $question->question,
                'type' => $question->type,
                'key_answer' => $question->key_answer,
                'points' => $question->points,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
                // Collect options if it's a multiple-choice question
                'options' => $question->type === 'multiple-choice' ? $questionGroup->pluck('option_text')->filter()->all() : null,
            ];
        })->values(); // Ensure it's a flat array, not an associative collection

        // Return a structured JSON response
        return response()->json([
            'status' => 'success',
            'data' => $groupedQuestions
        ]);
    }

    public function getCompletionStats($id)
    {
        // Get total students in the assessment
        $totalStudents = Learner::count();

        // Get students who have submitted their answers
        $completedStudents = Answer::whereIn('question_id', function($query) use ($id) {
            $query->select('question_id')
                ->from('questions')
                ->where('assessment_id', $id);
        })->distinct('lrn')->count('lrn'); // Count distinct learners who answered

        // Return the result
        return response()->json([
            'completed' => $completedStudents,
            'total' => $totalStudents
        ], 200);
    }

    public function showStudents($classid, $assessment_id)
    {
        // Get the total number of questions for the assessment
        $totalQuestions = Question::where('assessment_id', $assessment_id)->count();

        // Calculate the total points for the assessment by summing up the points of each question
        $totalPoints = Question::where('assessment_id', $assessment_id)->sum('points');

        // Get the learners from the roster of a class
        $learners = Roster::where('classid', $classid)
            ->join('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->select('learners.lrn', 'learners.firstname', 'learners.lastname')
            ->get();

        $learnersScores = Roster::where('classid', $classid)
            ->leftJoin('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->leftJoin('assessment_answers', 'rosters.lrn', '=', 'assessment_answers.lrn')
            ->select('learners.lrn', 'learners.firstname', 'learners.lastname', 'assessment_answers.score')
            ->get();

        // Check completion status for each student
        $learnersWithCompletionStatus = $learnersScores->map(function ($learner) use ($assessment_id, $totalQuestions) {
            // Count how many answers the student has submitted for this assessment
            $answersCount = Answer::where('lrn', $learner->lrn)
                ->whereIn('question_id', function($query) use ($assessment_id) {
                    $query->select('question_id')
                        ->from('questions')
                        ->where('assessment_id', $assessment_id);
                })
                ->count();

            // Check if the student has completed the assessment
            $learner->completed = ($answersCount == $totalQuestions);
            
            return $learner;
        });

        // Return the list of learners with their completion status and total points
        return [
            'status' => $learnersWithCompletionStatus,
            'score' => $learnersScores,
            'total_points' => $totalPoints // Include total points for the assessment
        ];
    }

    //Working 2
    public function showStudentAnswers($assessmentId, $lrn)
    {
        // Fetch the questions associated with the assessment
        $questions = Question::where('assessment_id', $assessmentId)
                    ->with(['options']) // Include the answer options
                    ->get();

        // Fetch the student's answers for the corresponding questions
        $studentAnswers = Answer::whereIn('question_id', $questions->pluck('question_id'))
                        ->where('lrn', $lrn)
                        ->get();

        // Fetch the total score directly from the Assessment_Answer table
        $studentScore = Assessment_Answer::where('lrn', $lrn)
                        ->select('score')
                        ->first();

        // Variables to keep track of the total score and possible maximum score
        $totalScore = 0;
        $maxScore = 0;

        // Merge the questions and answers, and display scores
        $response = [];
        foreach ($questions as $question) {
            $answer = $studentAnswers->firstWhere('question_id', $question->question_id);

            // Use the score from the answer if available, otherwise default to 0
            $score = $answer ? $answer->score : 0;

            // Increment total possible score
            $maxScore += $question->points;

            // Add to the total score
            $totalScore += $score;

            // Add question, student's answer, and score to the response
            $response[] = [
                'question_id' => $question->question_id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => $question->options, // If applicable
                'key_answer' => $question->key_answer, // Correct answer
                'student_answer' => $answer ? $answer->answer : null,
                'score' => $score, // Use the stored score from the database
                'points' => $question->points,
                'max_points' => $question->points // Maximum points for the question
            ];
        }

        // Return the response with individual questions, student's answers, and total score
        return [
            'status' => 'success',
            'data' => $response,
            'studentScore' => $studentScore, // Total score from Assessment_Answer table
            'total_score' => $totalScore, // Calculated total score
            'max_score' => $maxScore, // Total maximum score for the assessment
        ];
    }

    public function autoCheck($classid, $assessment_id)
    {
        // Get the total number of questions for the assessment
        $questions = Question::where('assessment_id', $assessment_id)->get();
        $totalQuestions = Question::where('assessment_id', $assessment_id)->count();

        // Get learners from the roster of a class
        $learners = Roster::where('classid', $classid)
            ->join('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->select('learners.lrn', 'learners.firstname', 'learners.lastname')
            ->get();

        // Check completion status for each student
        $learnersWithCompletionStatus = $learners->map(function ($learner) use ($assessment_id, $totalQuestions) {
            // Count how many answers the student has submitted for this assessment
            $answersCount = Answer::where('lrn', $learner->lrn)
                ->whereIn('question_id', function($query) use ($assessment_id) {
                    $query->select('question_id')
                        ->from('questions')
                        ->where('assessment_id', $assessment_id);
                })
                ->count();

            // Check if the student has completed the assessment
            $learner->completed = ($answersCount == $totalQuestions);
            
            return $learner;
        });

        foreach ($learners as $learner) {
            $totalScore = 0;
            $completed = true;

                foreach ($questions as $question) {
                    // Find the student's answer to the question
                    $answer = Answer::where('lrn', $learner->lrn)
                        ->where('question_id', $question->question_id)
                        ->first();
            
                    if ($answer) {
                        // Check if the answer matches the key answer and assign points
                        if ($answer->answer == $question->key_answer) {
                            $answer->score = $question->points; // Save the points to the answer
                            // $answer->save(); // Save the updated answer with the score
                            $answer->update(['score' => $question->points]);
                            // $totalScore += $question->points;
                        }
                    } else {
                        // Mark as incomplete if an answer is missing
                        $completed = false;
                    }
                }

            $totalScore = Answer::where('lrn', $learner->lrn)
                ->whereIn('question_id', function($query) use ($assessment_id) {
                    $query->select('question_id')
                        ->from('questions')
                        ->where('assessment_id', $assessment_id);
                })
                ->sum('score');

            // Save the score in the Assessment_Answer table
            Assessment_Answer::updateOrCreate(
                ['lrn' => $learner->lrn, 'assessmentid' => $assessment_id],
                ['score' => $totalScore, 'date_submission' => now()]
            );

            // Attach the score to the learner object
            $learner->score = $totalScore;
        }

        // Return learners with their scores
        return [
            'score' => $learners,
            'status' => $learnersWithCompletionStatus
        ];
    }

    public function submitScore(Request $request) 
    {
        // Validate the incoming data
        $validated = $request->validate([
            'assessment_id' => 'required|integer',
            'learner_id' => 'required|string',
            'question_id' => 'required|integer',
            'score' => 'required|numeric|min:0',
        ]);

        // Find the existing answer record for the specific question
        $answer = Answer::where('question_id', $request->question_id)
                        ->where('lrn', $request->learner_id)
                        ->first();

        // Find the total score record for the assessment and learner
        $assessmentAnswer = Assessment_Answer::where('assessmentid', $request->assessment_id)
                                            ->where('lrn', $request->learner_id)
                                            ->first();

        if ($answer && $assessmentAnswer) {
            // Track the previous score
            $previousScore = $answer->score;

            // Update the answer's score with the new score from the request
            $answer->update(['score' => $request->score]);

            // Update the total score by adjusting it with the difference between old and new scores
            $scoreDifference = $request->score - $previousScore;
            $assessmentAnswer->score += $scoreDifference;
            $assessmentAnswer->save();

            return response()->json([
                'status' => 'success', 
                'message' => 'Score updated successfully',
                'total_score' => $assessmentAnswer->score
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Answer or assessment record not found'], 404);
        }
    }

    // Fetch discussion replies based on discussionID
    public function viewDiscussionReplies($discussionid)
    {
        $replies = Discussion_Reply::where('discussionid', $discussionid)
            ->leftJoin('admins', 'discussion_replies.adminID', '=', 'admins.adminID') // Join with the Admins table
            ->leftJoin('learners', 'discussion_replies.lrn', '=', 'learners.lrn') // Join with the Learners table
            ->select(
                'discussion_replies.*',
                'admins.firstname as teacher_firstname', 
                'admins.lastname as teacher_lastname',
                'learners.firstname as student_firstname',
                'learners.lastname as student_lastname'
            )
            ->orderBy('discussion_replies.created_at', 'asc')
            ->get();

        return response()->json($replies);
    }




    // Store a new discussion reply
    public function sendDiscussionReplies(Request $request)
    {
        $validated = $request->validate([
            'discussionid' => 'required|integer',
            'lrn' => 'nullable|string', // Only for students
            'adminID' => 'nullable|integer', // Only for teachers
            'reply' => 'required|string'
        ]);

        $reply = Discussion_Reply::create([
            'discussionid' => $validated['discussionid'],
            'lrn' => $validated['lrn'] ?? null,
            'adminID' => $validated['adminID'] ?? null,
            'reply' => $validated['reply']
        ]);

        return response()->json(['message' => 'Reply sent successfully', 'reply' => $reply]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Execute $execute)
    {
        //
    }

    public function editQuestion(Request $request, $id)
    {
        // Find the question
        // $question = Question::find($id);
        $question = DB::table('questions')
            ->where('question_id', $id)
            ->first();

        // return $question;

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        // Validate the request
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|string',
            'key_answer' => 'required|string',
            'points' => 'required|integer',
            'options' => 'array', // Only required if multiple-choice
        ]);

        // Update the question
        DB::table('questions')
        ->where('question_id', $id)
        ->update([
            'question' => $request->question,
            'type' => $request->type,
            'key_answer' => $request->key_answer,
            'points' => $request->points,
        ]);

        // Handle options for multiple-choice questions
        if ($request->type === 'multiple-choice') {
            // Delete existing options
            DB::table('options')->where('question_id', $id)->delete();

            // Add new options
            if (!empty($request->options)) {
                foreach ($request->options as $optionText) {
                    DB::table('options')->insert([
                        'question_id' => $id,
                        'option_text' => $optionText,
                    ]);
                }
            }
        } else {
            // Delete options if not multiple-choice
            DB::table('options')->where('question_id', $id)->delete();
        }

        // Fetch updated options
        $options = $request->options ?? [];

        if ($request->type === 'multiple-choice' && !empty($options)) {
            foreach ($options as $optionText) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                ]);
            }
        }


        // Return the updated question
        return response()->json([
            'message' => 'Question updated successfully',
            'question' => [
                'question_id' => $question->question_id,
                'assessment_id' => $question->assessment_id,
                'question' => $question->question,
                'type' => $question->type,
                'key_answer' => $question->key_answer,
                'points' => $question->points,
                'options' => $options,
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Execute $execute)
    {
        //
    }

    public function deleteQuestion($id)
    {
        $question = Question::where('question_id', $id)
                            ->delete();
        
        return response()->json(['status' => 'Deleted successfully', 'response' => $question]);
    }

    public function showAll()
    {
        //
        $subject = DB::table('classes')
            ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
            ->get();
        return $subject;
        
    }

    public function teacherAllSubjects($id)
    {
        //
        $subject = DB::table('classes')
            ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid')
            ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule', 'rooms.school')
            ->where('classes.adminid', $id) 
            ->get();

        return $subject;
    }

    public function showDiscussion()
    {
        //
        $discuss = Discussion::select(
            'discussionid',
            'lesson_id',
            'discussion_topic',
            DB::raw('DATE_FORMAT(created_at, "%M %d, %Y") as created')
            )
            ->get();

        return $discuss;
    }

    public function showAssessment()
    {
        //
        $assess = DB::table('assessments')
        ->select(
            'assessments.assessmentID',
            'assessments.Title',
            'assessments.Instruction',
            'assessments.Description',
            DB::raw('DATE_FORMAT(assessments.Due_date, "%M %d, %Y") as formatted_due_date')
        )
        ->get();

        return $assess;
    }

    public function registerAdmin(Request $request){
        $formField = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'address' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        Admin::create($formField);

        return 'registered';
    }

    public function registerLearner(Request $request){
        $formField = $request->validate([
            'lrn' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'middlename' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:255',
            'birthdate' => 'required|date',
            'placeofbirth' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'gender' => 'required|string',
            'civil_status' => 'required|string',
            'email' => 'required|email|unique:learners',
            'password' => 'required|string|min:8|confirmed'
        ]);

        Learner::create($formField);

        return 'registered';
    }

    public function loginAdmin(Request $request){
        $request->validate([
            'email' => 'required|email|exists:admins',
            'password' => 'required'
        ]);

        $user = Admin::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return [
                'message' => 'The Provided Credentials are incorrect'
            ];
        };
        $token = $user->createToken($user->lastname);
        $adminid = $user->adminID;

        return [
            'adminid' => $adminid,
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function loginLearner(Request $request){
        $request->validate([
            'email' => 'required|email|exists:learners',
            'password' => 'required'
        ]);

        $user = Learner::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return [
                'message' => 'The Provided Credentials are incorrect'
            ];
        };
        $token = $user->createToken($user->lastname);

        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function logoutAdmin(Request $request){
        $request->user()->tokens()->delete();
        return [
            'message' => 'You are Logged out'
        ];
    }
}
