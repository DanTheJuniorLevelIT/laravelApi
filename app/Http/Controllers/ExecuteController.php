<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Http\Controllers\Log;
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
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Module;
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

        $announceid = DB::table('subjects')
                        ->rightJoin('announcements', 'subjects.subjectid', '=', 'announcements.subjectid')
                        ->where('announcements.subjectid', $id)
                        ->select('announcements.subjectid')
                        ->value('subjectid');

        return [
            'announce' => $announce,
            'announceid' => (int)$announceid,
        ];
        // return response()->json($announce);

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
        // Get the total number of students in the assessment (for the class or course)
        $totalStudents = Learner::count();

        // Get distinct students who submitted answers to the assessment
        $studentsWithAnswers = Answer::whereIn('question_id', function($query) use ($id) {
            $query->select('question_id')
                ->from('questions')
                ->where('assessment_id', $id);
        })->distinct('lrn')->pluck('lrn'); // Get distinct learner IDs (lrn) who submitted answers

        // Get distinct students who uploaded a file in the assessment
        $studentsWithFiles = Assessment_Answer::where('assessmentid', $id)
                                ->whereNotNull('file') // Check if the file column is not null
                                ->distinct('lrn')->pluck('lrn'); // Get distinct learner IDs (lrn) who uploaded files

        // Merge both collections to get all unique learners
        $allCompletedStudents = $studentsWithAnswers->merge($studentsWithFiles)->unique();

        // Count the total number of distinct learners who either submitted answers or uploaded files
        $completedStudentsCount = $allCompletedStudents->count();

        // Return the response with both the number of completed students and total students
        return response()->json([
            'completed' => $completedStudentsCount,
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

        // $learnersScores = Roster::where('classid', $classid)
        //     ->leftJoin('learners', 'rosters.lrn', '=', 'learners.lrn')
        //     ->leftJoin('assessment_answers', 'rosters.lrn', '=', 'assessment_answers.lrn')
        //     ->leftJoin('assessments', 'assessment_answers.assessmentid', '=', 'assessments.assessmentid')
        //     ->select('learners.lrn', 'learners.firstname', 'learners.lastname', 'assessment_answers.score', 'assessment_answers.file')
        //     ->get();
        // Get the learners' scores and completion status for this assessment
        $learnersScores = Roster::where('rosters.classid', $classid)
        ->leftJoin('learners', 'rosters.lrn', '=', 'learners.lrn')
        ->leftJoin('assessment_answers', function($join) use ($assessment_id) {
            $join->on('rosters.lrn', '=', 'assessment_answers.lrn')
                ->where('assessment_answers.assessmentid', '=', $assessment_id);
        })
        ->leftJoin('assessments', 'assessment_answers.assessmentid', '=', 'assessments.assessmentid')
        ->select(
            'learners.lrn', 
            'learners.firstname', 
            'learners.lastname', 
            'assessment_answers.score', 
            'assessment_answers.file'
        )
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

            // Check if the student has uploaded a file
            $fileUploaded = !is_null($learner->file);

            // Check if the student has completed the assessment
            $learner->completed = ($answersCount == $totalQuestions || $fileUploaded);
                
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
                        ->where('assessmentid', $assessmentId)
                        ->select('answerid', 'score', 'file')
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
            'answerid' => $studentScore->answerid,
            'studentFile' => $studentScore->file ? url('storage/Files/' . $studentScore->file) : null, // Generate full URL File from Assessment_Answer table
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
            ->leftJoin('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->leftJoin('assessment_answers', 'rosters.lrn', '=', 'assessment_answers.lrn')
            ->select('learners.lrn', 'learners.firstname', 'learners.lastname', 'assessment_answers.score', 'assessment_answers.file')
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

            // Check if the student has uploaded a file
            $fileUploaded = !is_null($learner->file);

            // Check if the student has completed the assessment
            $learner->completed = ($answersCount == $totalQuestions || $fileUploaded);
            
            return $learner;
        });

        foreach ($learners as $learner) {
            // If the learner has uploaded a file, skip score calculation
            if (!is_null($learner->file)) {
                continue;  // Skip to the next learner
            }

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

    public function updateAssessScore(Request $request) 
    {
        // Validate the incoming data
        $validated = $request->validate([
            'assessment_id' => 'required|integer',
            'learner_id' => 'required|string',
            'answerid' => 'required|integer',
            'score' => 'required|numeric|min:0',
        ]);

        // Find the total score record for the assessment and learner
        $assessmentAnswer = Assessment_Answer::where('assessmentid', $request->assessment_id)
                                            ->where('answerid', $request->answerid)
                                            ->first();

        if ($assessmentAnswer) {
            // Track the previous score
            $previousScore = $assessmentAnswer->score;

            // Update the answer's score with the new score from the request
            $assessmentAnswer->update(['score' => $request->score]);

            // Update the total score by adjusting it with the difference between old and new scores
            // $scoreDifference = $request->score - $previousScore;
            // $assessmentAnswer->score += $scoreDifference;
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
            // Conditionally require 'key_answer' if the type is not 'Essay'
            'key_answer' => $request->type !== 'Essay' ? 'required|string' : 'nullable|string',
            'points' => 'required|integer',
            'option' => 'array', // Only required if multiple-choice
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
        if ($request->type === 'multiple-choice' && !empty($request->options)) {
            $optionIds = [];

            foreach ($request->options as $option) {
                if (is_array($option) && isset($option['option_id'])) {
                    // Update existing options (associative array)
                    DB::table('options')
                        ->where('option_id', $option['option_id'])
                        ->update([
                            'option_text' => $option['option_text']
                        ]);
                    $optionIds[] = $option['option_id'];  // Track existing option IDs
                } elseif (is_string($option)) {
                    // Insert new options if it's just a string (option text)
                    $newOptionId = DB::table('options')->insertGetId([
                        'question_id' => $id,
                        'option_text' => $option,
                    ]);
                    $optionIds[] = $newOptionId;  // Track new option IDs
                }
            }

            // Delete options that were not included in the update
            DB::table('options')
                ->where('question_id', $id)
                ->whereNotIn('option_id', $optionIds)
                ->delete();
        } else {
            // Delete all options if the type is not multiple-choice
            DB::table('options')->where('question_id', $id)->delete();
        }

        // Fetch the updated question with options
        $updatedQuestion = DB::table('questions')
            ->where('question_id', $id)
            ->first();
        
        $updatedOptions = DB::table('options')
            ->where('question_id', $id)
            ->get();


        // Return the updated question
        return response()->json([
            'message' => 'Question updated successfully',
            'question' => [
            'question_id' => $updatedQuestion->question_id,
            'assessment_id' => $updatedQuestion->assessment_id,
            'question' => $updatedQuestion->question,
            'type' => $updatedQuestion->type,
            'key_answer' => $updatedQuestion->key_answer,
            'points' => $updatedQuestion->points,
            'options' => $updatedOptions,
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
        ->leftJoin('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
        ->select(
            'assessments.assessmentID',
            'assessments.Title',
            'assessments.Instruction',
            'assessments.Description',
            'assessments.lesson_id',
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
        $role = $user->role;

        return [
            'adminid' => $adminid,
            'role' => $role,
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

    //create module
    public function createModule(Request $request)
    {
        $validatedData = $request->validate([
            'classid' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|string|max:255',
        ]);

        $modules = Module::create($validatedData);
        // $allModules = Module::all();
        return response()->json($modules);

    }

    public function showModulesDetails($id)
{
    // $mods = DB::table('modules')
    // ->select('modules.*')
    // ->where('modules.classid', $id)
    // ->get(); // Fetches all matching modules
    $mods = Module::where('classid', $id)
                    ->get(); // Fetches all matching modules

    return response()->json($mods);
}

//createLesson
//create module
public function createLesson(Request $request)
{
    $validatedData = $request->validate([
        'module_id' => 'required|integer',
        'topic_title' => 'required|string',
        'lesson' => 'required|string',
        'file' => 'nullable|file|max:2048' 
    ]);

    if ($request->hasFile('file')) {
        $file = $request->file('file');

        $originalFileName = time() . '_' . $file->getClientOriginalName();

        $filePath = $file->storeAs('lesson file', $originalFileName, 'public');
        
        $validatedData['file'] = $filePath;
    }

    $lesson = Lesson::create($validatedData);

    return response()->json($lesson);
}

public function showLessonDetails($id)
{
    // $mods = DB::table('modules')
    // ->select('modules.*')
    // ->where('modules.classid', $id)
    // ->get(); // Fetches all matching modules
    // $les = Lesson::where('modules_id', $id)
                    // ->get(); 
                    
    // $les = DB::table('lessons')
    //         ->leftJoin('media','lessons.lesson_id','=','media.lesson_id')
    //         ->select('lessons.*','media.filename')
    //         ->where('lessons.modules_id',$id)
    //         ->get(); // Fetches all matching modules

    //     return $les;

        $lessons = DB::table('lessons')
        ->leftJoin('media', 'lessons.lesson_id', '=', 'media.lesson_id')
        ->select(
            'lessons.lesson_id',
            'lessons.module_id',
            'lessons.topic_title',
            'lessons.lesson',
            'lessons.handout',
            'lessons.file',
            DB::raw('GROUP_CONCAT(media.filename) as media_files'), // Concatenate media filenames
            DB::raw('GROUP_CONCAT(media.media_id) as media_ids') // Concatenate media IDs
        )
        ->where('lessons.module_id', $id)
        ->groupBy(
            'lessons.lesson_id',
            'lessons.module_id',
            'lessons.topic_title',
            'lessons.lesson',
            'lessons.handout',
            'lessons.file',
        )  // Add all selected columns to GROUP BY
        ->get();

        $mediaIds = $lessons->pluck('media_ids')->toArray(); // Get all media IDs
        error_log('Media IDs: ' . implode(', ', $mediaIds)); // Log to console

    return $lessons;
}

public function getlessonid($id)
{
    $les = DB::table('lessons')
                    ->select('lessons.*')
                    ->where('lessons.lesson_id',$id)
                    ->get(); // Fetches all matching modules

    return $les;
}

public function updateLessonInfo(Request $request, $id) {

    // Check if the ID is null or invalid
    if (is_null($id)) {
        return response()->json(['message' => 'Lesson ID is missing'], 400);
    }

    // Validate the request
    $validatedData = $request->validate([
        'topic_title' => 'required|string',
        'lesson' => 'required|string',
    ]);

    // Fetch the lesson by lesson_id
    $lesson = Lesson::find($id);

    // Update lesson data
    $lesson->fill($validatedData);
    $lesson->save();

    return response()->json(['message' => 'Lesson updated successfully']);
}

public function deleteLesson($id)
{
    $lesson = Lesson::where('lesson_id', $id);

    if ($lesson) {
        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted successfully'], 200);
    } else {
        return response()->json(['message' => 'Lesson not found'], 404);
    }
}

public function uploadMedia(Request $request)
{
    // Validate the request
    $request->validate([
        'lesson_id' => 'required',
        'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'  // Restrict file types and size
    ]);

    // Store the file
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // if ($request->hasFile('file') == 'file'=> )
        $filePath = $file->storeAs('uploads', $filename, 'public');

        // Save file information to the database
        $media = new Media();
        $media->lesson_id = $request->input('lesson_id');
        $media->uploader_id = null;
        $media->type = $file->getClientOriginalExtension();
        $media->filename = $filePath;
        $media->save();

        return response()->json(['message' => 'File uploaded successfully', 'file' => $filePath], 200);
    }

    return response()->json(['message' => 'File not uploaded'], 400);
}

public function deleteFile($id)
{
    $lesson = Lesson::find($id);
    if ($lesson) {
        // Update the file field to null
        $lesson->file = null;
        $lesson->save();

        return response()->json(['message' => 'File deleted successfully'], 200);
    } else {
        return response()->json(['message' => 'Lesson not found'], 404);
    }
}

public function deleteMediaFile($id)
{
    $media = Media::find($id);
    if ($media) {
        // Update the file field to null
        // $media->file = null;
        $media->delete();

        return response()->json(['message' => 'File deleted successfully'], 200);
    } else {
        return response()->json(['message' => 'Lesson not found'], 404);
    }
}
}
