<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use Exception;
use App\Models\Subject;
use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Discussion_Reply;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Announcement;
use App\Models\Learner;
use App\Models\Question;
use App\Models\Option;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Message;
use App\Models\Module;
use App\Models\Answer;
use App\Models\Assessment_Answer;
use App\Models\Roster;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class ExecuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    public function index2()
    {
        //
        $dayOfWeek = date('N'); // Get the day of the week (1 = Monday, 7 = Sunday)

        $program = '';

        // Determine the program based on the current day
        if ($dayOfWeek == 1) {
            $program = 'blp';
        } elseif (in_array($dayOfWeek, [2, 3])) {
            $program = 'alsElem';
        } elseif (in_array($dayOfWeek, [4, 5])) {
            $program = 'aleJhs';
        }

        // Retrieve the subjects based on the program
        $subject = DB::table('classes')
            ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            ->select('subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.Schedule')
            ->where('subjects.Program', '=', $program)
            ->get();

        return $subject;
    }

    // Dan WORKS
    /**
     * CREATE DATA
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
        // Validate the request data
        $validatedData = $request->validate([
            'lesson_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'instruction' => 'required|string',
            'description' => 'required|string',
            'due_date' => [
                'required', 
                'date', 
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(date('Y-m-d'))) {
                        $fail('The due date cannot be earlier than today.');
                    }
                },
            ],
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
            'classid' => 'required',
            'title' => 'required|string',
            'instruction' => 'required|string'
        ]);

        // Check if an announcement for the subjectID exists
        $announcement = Announcement::where('classid', $request->classid)->first();

        if ($announcement) {
            // Update existing announcement
            Announcement::where('classid', $request->classid)->update([
                'title' => $request->title,
                'instruction' => $request->instruction
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'announcement' => [
                    'classid' => $announcement->classid,
                    'title' => $announcement->title,
                    'instruction' => $announcement->instruction
                ]
            ], 200);
        } else {
            // Create new announcement
            $announce = Announcement::create([
                'classid' => $request->classid,
                'title' => $request->title,
                'instruction' => $request->instruction
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'announcement' => [
                    'classid' => $announce->classid,
                    'title' => $announce->title,
                    'instruction' => $announce->instruction
                ]
            ], 201);
        }
    }



    /**
     * DISPLAY SPECIFIC DATA BY ID
     */

    public function showAnnouncement($id)
    {
        $announce = DB::table('classes')
                        ->rightJoin('announcements', 'classes.classid', '=', 'announcements.classid')
                        ->where('announcements.classid', $id)
                        ->select('announcements.announceid', 'announcements.title', 'announcements.instruction')
                        ->first();

        $announceid = DB::table('classes')
                        ->rightJoin('announcements', 'classes.classid', '=', 'announcements.classid')
                        ->where('announcements.classid', $id)
                        ->select('announcements.classid')
                        ->value('classid');

        return [
            'announce' => $announce,
            'announceid' => (int)$announceid,
        ];

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
            ->leftJoin('subjects', 'classes.subjectid', '=', 'subjects.subjectid') // left join
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

    public function getStudentsByClass($cid)
    {
        // Fetch all students in the class
        $students = Learner::join('rosters', 'learners.lrn', '=', 'rosters.lrn')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->where('classes.classid', $cid)
            ->select('learners.lrn', 'learners.firstname', 'learners.lastname', 'learners.gender', 'learners.birthdate', 'learners.contact_numbers', 'classes.classid')
            ->get();

        // Fetch all assessments for the class
        $totalAssessments = DB::table('assessments')
            ->join('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
            ->whereIn('lessons.module_id', function ($query) use ($cid) {
                $query->select('modules_id')
                    ->from('modules')
                    ->where('classid', $cid);
            })
            ->count();

        // Add completed assessments data for each student
        $studentsData = $students->map(function ($student) use ($totalAssessments) {
            $completedAssessments = DB::table('assessment_answers')
                    ->where('lrn', $student->lrn)
                    ->whereIn('assessmentid', function ($query) use ($student) {
                        $query->select('assessmentid')
                            ->from('assessments')
                            ->join('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
                            ->whereIn('lessons.module_id', function ($subquery) use ($student) {
                                $subquery->select('modules_id')
                                    ->from('modules')
                                    ->where('classid', $student->classid);
                            });
                    })
                    ->distinct()
                    ->count();

            return [
                'lrn' => $student->lrn,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'gender' => $student->gender,
                'birthdate' => $student->birthdate,
                'contact_numbers' => $student->contact_numbers,
                'completed_assessments' => $completedAssessments,
                'total_assessments' => $totalAssessments,
            ];
        });

        // Get the total number of students
        $totalStudents = $students->count();

        return response()->json([
            'allStudents' => $studentsData,
            'total_students' => $totalStudents,
            'total_assessments' => $totalAssessments,
        ], 200);
    }


    public function assessmentTotalPoints($aid){
        $totalPoints = Question::where('assessment_id', $aid)->sum('points');

        return $totalPoints;
    }

    public function getAssessmentsByClass($cid)
    {
        // Retrieve modules associated with the given class ID
        $modules = Module::where('classid', $cid)
        ->with([
            'lessons.assessments' => function ($query) {
                $query->select('assessmentid', 'lesson_id', 'title', 'instruction', 'description', 'due_date', 'created_at')
                ->orderBy('created_at', 'desc'); // Order by created_at in descending order;
            }
        ])
        ->get();

        // Transform the data for easier frontend usage
        $result = $modules->map(function ($module) {
            return [
                'module_id' => $module->modules_id,
                'module_title' => $module->title,
                'lessons' => $module->lessons->map(function ($lesson) {
                    return [
                        'lesson_id' => $lesson->lesson_id,
                        'lesson_title' => $lesson->topic_title,
                        'assessments' => $lesson->assessments->map(function ($assessment) {
                            return [
                                'assessment_id' => $assessment->assessmentid,
                                'title' => $assessment->title,
                                'instruction' => $assessment->instruction,
                                'description' => $assessment->description,
                                'due_date' => $assessment->due_date,
                                'formatted_due_date' => \Carbon\Carbon::parse($assessment->due_date)->format('F j, Y'),
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json($result);
    }

    public function getCompletionStats($id, $cid)
    {
        // Get the total number of students in the assessment (for the class or course)
        $totalStudents = Learner::join('rosters', 'learners.lrn', '=', 'rosters.lrn')
                            ->join('classes', 'rosters.classid', '=', 'classes.classid')
                            ->where('classes.classid', $cid) // Replace $id with the specific class ID
                            ->count();

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

    public function getLearnerAssessments($lrn, $cid)
    {
        // Fetch learner details
        $learner = DB::table('learners')
            ->where('lrn', $lrn)
            ->select('firstname', 'middlename', 'lastname')
            ->first();

        if (!$learner) {
            return ['message' => 'Learner not found.'];
        }

        // Fetch the classes the learner is enrolled in
        $classes = DB::table('rosters')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->where('rosters.lrn', $lrn)
            ->where('rosters.classid', $cid)
            ->select('classes.classid', 'classes.schedule')
            ->get();

        // Prepare data for each class
        $assessmentsData = [];

        foreach ($classes as $class) {
            // Fetch modules linked to the class
            $modules = DB::table('modules')
                ->where('classid', $class->classid)
                ->pluck('modules_id');

            // Fetch assessments linked to the lessons
            $assessments = DB::table('assessments')
                ->join('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
                ->whereIn('lessons.module_id', $modules)
                ->select('assessments.assessmentid', 'assessments.title', 'assessments.due_date', 'lessons.topic_title', 'lessons.module_id')
                ->orderBy('assessments.due_date', 'asc')
                ->get();

            foreach ($assessments as $assessment) {
                // Fetch the number of questions in the assessment
                $totalQuestions = DB::table('questions')
                    ->where('assessment_id', $assessment->assessmentid)
                    ->count();

                // Fetch learner's answers
                $learnerAnswers = DB::table('answers')
                    ->where('lrn', $lrn)
                    ->whereIn('question_id', function ($query) use ($assessment) {
                        $query->select('question_id')
                            ->from('questions')
                            ->where('assessment_id', $assessment->assessmentid);
                    })
                    ->count();

                // Check if all questions are answered
                $allQuestionsAnswered = $learnerAnswers === $totalQuestions;

                // Fetch learner's score
                $score = DB::table('assessment_answers')
                    ->where('lrn', $lrn)
                    ->where('assessmentid', $assessment->assessmentid)
                    ->value('score');

                // Determine status
                $status = ($allQuestionsAnswered && $score > 0) ? 'Finish' : 'Not Finish';

                $assessmentsData[] = [
                    'class_schedule' => $class->schedule,
                    'assessment_title' => $assessment->title,
                    'lesson_title' => $assessment->topic_title,
                    'assessID' => $assessment->assessmentid,
                    'due_date' => $assessment->due_date,
                    'status' => $status,
                    'module_id' => $assessment->module_id,
                    'score' => $score,
                    'allQuestionsAnswered' => $allQuestionsAnswered,
                ];
            }
        }

        return [
            'learner' => "{$learner->firstname} {$learner->middlename} {$learner->lastname}",
            'assessments' => $assessmentsData,
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

            $studentAnswer = $answer ? strip_tags($answer->answer) : null;

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
                'student_answer' => $studentAnswer,
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
        $learners = Roster::where('rosters.classid', $classid)
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

        // FIRST
        // foreach ($learners as $learner) {
        //     // If the learner has uploaded a file, skip score calculation
        //     if (!is_null($learner->file)) {
        //         continue;  // Skip to the next learner
        //     }

        //     $totalScore = null;
        //     $completed = true;

        //         foreach ($questions as $question) {
        //             // Find the student's answer to the question
        //             $answer = Answer::where('lrn', $learner->lrn)
        //                 ->where('question_id', $question->question_id)
        //                 ->first();
            
        //             if ($answer) {
        //                 // Check if the answer matches the key answer and assign points
        //                 if ($answer->answer == $question->key_answer) {
        //                     $answer->score = $question->points; // Save the points to the answer
        //                     // $answer->save(); // Save the updated answer with the score
        //                     $answer->update(['score' => $question->points]);
        //                     // $totalScore += $question->points;
        //                 }
        //             } else {
        //                 // Mark as incomplete if an answer is missing
        //                 $completed = false;
        //             }
        //         }

        //     $totalScore = Answer::where('lrn', $learner->lrn)
        //         ->whereIn('question_id', function($query) use ($assessment_id) {
        //             $query->select('question_id')
        //                 ->from('questions')
        //                 ->where('assessment_id', $assessment_id);
        //         })
        //         ->sum('score');

        //     // Save the score in the Assessment_Answer table
        //     Assessment_Answer::updateOrCreate(
        //         ['lrn' => $learner->lrn, 'assessmentid' => $assessment_id],
        //         ['score' => $totalScore, 'date_submission' => now()]
        //     );

        //     // Attach the score to the learner object
        //     $learner->score = $totalScore;
        // }

        //SECOND
        foreach ($learners as $learner) {
            // Check if the learner has uploaded a file or has incomplete answers
            $answersCount = Answer::where('lrn', $learner->lrn)
                ->whereIn('question_id', function($query) use ($assessment_id) {
                    $query->select('question_id')
                        ->from('questions')
                        ->where('assessment_id', $assessment_id);
                })
                ->count();
        
            $totalQuestions = Question::where('assessment_id', $assessment_id)->count();
        
            // Skip if the learner has not completed all answers or uploaded a file
            if ($answersCount < $totalQuestions && is_null($learner->file)) {
                continue; // Skip to the next learner
            } else if (!is_null($learner->file)){
                continue;
            }
        
            // Initialize total score
            $totalScore = 0;
        
            // Calculate the score for the completed answers
            foreach ($questions as $question) {
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
        
            // Attach the score to the learner object for further use
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
    // OLD
    public function viewDiscussionReplies($discussionid)
    {
        $replies = Discussion_Reply::where('discussionid', $discussionid)
            ->leftJoin('admins', 'discussion_replies.adminID', '=', 'admins.adminID') // Join with the Admins table
            ->leftJoin('learners', 'discussion_replies.lrn', '=', 'learners.lrn') // Join with the Learners table
            ->select(
                'discussion_replies.reply',
                'discussion_replies.lrn',
                // 'discussion_replies.created_at',
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
     * UPDATE FUNCTION
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

    public function updateAvailability(Request $request, $assessmentID)
    {
        // Validate new due date
        $request->validate([
            'available' => 'required|boolean'
        ]);

        // Update the due date of the assessment
        DB::table('assessments')
            ->where('assessmentID', $assessmentID)
            ->update(['available' => $request->available]);

        return response()->json(['message' => 'Due date updated successfully']);
    }

    /**
     * DELETE FUNCTION
     */
    public function destroy(Execute $execute)
    {
        //
    }

    public function deleteAnnouncement($classid) {
        $announcement = Announcement::where('classid', $classid)->first();
    
        if ($announcement) {
            $announcement->delete();
            return response()->json(['message' => 'Announcement deleted successfully.'], 200);
        }
    
        return response()->json(['message' => 'Announcement not found.'], 404);
    }    

    public function deleteQuestion($id)
    {
        $question = Question::where('question_id', $id)
                            ->delete();
        
        return response()->json(['status' => 'Deleted successfully', 'response' => $question]);
    }

    public function deleteAssessment($id)
    {
        $assess = Assessment::where('assessmentid', $id);

        if ($assess) {
            $assess->delete();
            return response()->json(['message' => 'Lesson deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Lesson not found'], 404);
        }
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
            ->leftJoin('subjects', 'classes.subjectid', '=', 'subjects.subjectid')
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid')
            ->select('classes.classid', 'subjects.subjectid', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule', 'rooms.school')
            ->where('classes.adminid', $id) 
            ->get();

        return $subject;
    }

    public function showDiscussion($id)
    {
        //
        $discuss = Discussion::select(
            'discussionid',
            'lesson_id',
            'discussion_topic',
            DB::raw('DATE_FORMAT(created_at, "%M %d, %Y") as created')
            )
            ->where('lesson_id', $id)
            ->get();

        return $discuss;
    }

    public function countDiscussion($id)
    {
        //
        $countDiscussion = Discussion::where('lesson_id', $id)
            ->count();

        return $countDiscussion;
    }

    public function showAssessment()
    {
        // Fetch assessments with their current availability status
        $assess = DB::table('assessments')
            ->leftJoin('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
            ->select(
                'assessments.assessmentID',
                'assessments.Title',
                'assessments.Instruction',
                'assessments.Description',
                'assessments.lesson_id',
                'assessments.Due_date',
                'assessments.available', // Include availability status
                DB::raw('DATE_FORMAT(assessments.Due_date, "%M %d, %Y") as formatted_due_date')
            )
            ->get();

        return $assess;
    }

    // Login Function

    public function resetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if the email exists in the database
        $user = Admin::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        // Generate reset token
        $resetCode = rand(100000, 999999); // Example: Generate a 6-digit code

        // Save code to the user record (or a separate table)
        $user->update(['reset_code' => $resetCode]);

        // Send email
        Mail::to($request->email)->send(new ResetPasswordMail($resetCode));

        return response()->json(['message' => 'Reset code sent successfully']);
    }

    public function registerAdmin(Request $request)
    {
        $formField = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
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

    public function registerLearner(Request $request)
    {
        $formField = $request->validate([
            'lrn' => 'nullable|string|max:255',
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:255',
            'birthdate' => 'required|date',
            'placeofbirth' => 'nullable|string|max:255',
            'last_education' => 'nullable|string|max:255',
            'gender' => 'required|string',
            'civil_status' => 'required|string',
            'email' => 'required|email|unique:learners',
            'password' => 'required|string|min:8|confirmed'
        ]);

        Learner::create($formField);

        return 'registered';
    }

    public function loginAdmin(Request $request)
    {
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
            'details' => [
                'adminID' => $user->adminID,
                'firstname' => $user->firstname,
                'middlename' => $user->middlename,
                'email' => $user->email,
                'lastname' => $user->lastname,
            ],
            'profile_picture' => "http://localhost:8000/storage/profile_pictures/$user->profile_picture",
            'token' => $token->plainTextToken
        ];
    }

    public function loginLearner(Request $request)
    {
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

    //elzaina works
    //create module
    public function createModule(Request $request)
    {
        $validatedData = $request->validate([
            'classid' => 'required|integer',
            'title' => 'required|string',
            'description' => 'required|string',
            'date' => [
                'required', 
                'date', 
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(date('Y-m-d'))) {
                        $fail('The date cannot be earlier than today.');
                    }
                },
            ],
        ]);

        $modules = Module::create($validatedData);
        // $allModules = Module::all();
        return response()->json($modules);

    }

    public function showModulesDetails($id)
    {
        $mods = Module::where('classid', $id)
                        ->orderBy('date', 'asc')
                        ->get(); // Fetches all matching modules

        return response()->json($mods);
    }

    public function updateModuleDate(Request $request, $id) {
        // Log the incoming data
        Log::info('Request Data:', $request->all());
        
        $validatedData = $request->validate([
            'title' => 'required', // Validate that 'date' is a valid date
            'description' => 'required', // Validate that 'date' is a valid date
            'date' => 'required|date', // Validate that 'date' is a valid date
        ]);

        
    
        // Attempt to update the module's date
        $affectedRows = DB::table('modules')
            ->where('modules_id', $id)
            ->update([
                'title' => $request->title, 
                'description' => $request->description, 
                'date' => $request->date
            ]);
    
        // Check if any rows were affected
        if ($affectedRows === 0) {
            return response()->json(['success' => false, 'message' => 'Module not found or no changes made'], 404);
        }
    
        return response()->json(['success' => true, 'message' => 'Module date updated successfully']);
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
            $lessons = DB::table('lessons')
            ->leftJoin('media', 'lessons.lesson_id', '=', 'media.lesson_id')
            ->leftJoin('discussions', 'lessons.lesson_id', '=', 'discussions.lesson_id')
            ->select(
                'lessons.lesson_id',
                'lessons.module_id',
                'lessons.topic_title',
                'lessons.lesson',
                'lessons.handout',
                'lessons.file',
                'lessons.created_at',
                DB::raw('GROUP_CONCAT(media.filename) as media_files'), // Concatenate media filenames
                DB::raw('GROUP_CONCAT(media.media_id) as media_ids'), // Concatenate media IDs
                DB::raw('COUNT(discussionid) as discussion_count') // Count discussions
            )
            ->where('lessons.module_id', $id)
            ->groupBy(
                'lessons.lesson_id',
                'lessons.module_id',
                'lessons.topic_title',
                'lessons.lesson',
                'lessons.handout',
                'lessons.file',
                'lessons.created_at'
            )  // Add all selected columns to GROUP BY
            ->get();

            $lessonid = DB::table('lessons')->select('lessons.lesson_id')->where('lessons.module_id', $id)->value('lesson_id');

            $mediaIds = $lessons->pluck('media_ids')->toArray(); // Get all media IDs
            error_log('Media IDs: ' . implode(', ', $mediaIds)); // Log to console

        // return $lessons;
        return [
            'lessons' => $lessons,
            'lessonid' => $lessonid
        ];
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

    public function getSingleAssessment($id)
    {
        $assessment = DB::table('assessments')->where('assessmentID', $id)->first();
        return $assessment;
    }

    public function updateAssessment(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'instruction' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ]);

        DB::table('assessments')
            ->where('assessmentID', $id)
            ->update([
                'title' => $validatedData['title'],
                'instruction' => $validatedData['instruction'],
                'description' => $validatedData['description'],
                'due_date' => $validatedData['due_date'],
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Assessment updated successfully']);
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
            $media->delete();

            return response()->json(['message' => 'File deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Lesson not found'], 404);
        }
    }

    //Mark Workx

    public function getUnreadMessages($id)
    {
        $unreadMessages = DB::table('messages')
            ->join('admins', 'messages.adminID', '=', 'admins.adminID')
            ->where('admins.adminID', $id)
            ->whereRaw('messages.sender_name != CONCAT(admins.firstname, " ", admins.lastname)')
            ->where('messages.status', 0)
            ->select('messages.*')
            ->get();

            $count = $unreadMessages->count();

            return $count;
    }

    public function markAllMessagesAsRead(Request $request)
    {
        $adminID = $request->adminID;
        $lrn = $request->lrn;

        try {
            DB::table('messages')
                ->where('adminID', $adminID)
                ->where('lrn', $lrn)
                ->update(['status' => 1]);

            return response()->json(['message' => 'All messages marked as read'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update messages status'], 500);
        }
    }  

    public function showMessages($id)
    {
        $messages = DB::table('messages')
            ->select(
                DB::raw('messages.messageid'),
                'messages.adminID',
                'messages.lrn',
                'messages.sender_name',
                'messages.messages',
                'messages.created_at',
                'learners.firstname',
                'learners.lastname',
                DB::raw('(SELECT COUNT(*) 
                        FROM messages AS sub_messages 
                        WHERE sub_messages.lrn = messages.lrn 
                            AND sub_messages.status = 0 
                            AND sub_messages.sender_name != CONCAT(admins.firstname, " ", admins.lastname)) AS unread_count')
            )
            ->join('rosters', 'messages.lrn', '=', 'rosters.lrn')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->join('learners', 'learners.lrn', '=', 'messages.lrn')
            ->join('admins', 'classes.adminid', '=', 'admins.adminID') // Ensure admin data is available
            ->where('classes.adminid', $id)
            ->whereRaw('messages.created_at = (SELECT MAX(sub_messages.created_at) 
                                            FROM messages AS sub_messages 
                                            WHERE sub_messages.lrn = messages.lrn)')
            ->orderBy('messages.created_at', 'DESC') // Ensure latest messages appear first
            ->distinct()
            ->get();

            return response()->json($messages);
    }



    public function viewConvo($lrn)
    {
        $messages = DB::table('messages')
        ->select(
            'messages.messageid',
            'messages.adminID',
            'messages.lrn',
            'messages.sender_name',
            'messages.messages',
            'messages.created_at',
            'learners.firstname',
            'learners.lastname'
        )
        ->join('learners', 'learners.lrn', '=', 'messages.lrn')
        ->where('messages.lrn', $lrn)
        ->orderBy('messages.created_at', 'ASC') // Order by oldest first for conversation flow
        ->get();

        return response()->json($messages);
    }

    public function getStudents($id)
    {
        $students = Learner::join('rosters', 'learners.lrn', '=', 'rosters.lrn')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->where('classes.adminid', $id)
            ->select('learners.studentid', 'learners.firstname', 'learners.lastname', 'learners.lrn')
            ->distinct()
            ->get();     
        return response()->json($students);
    }

    public function sendReply(Request $request)
    {
        $validatedData = $request->validate([
            'lrn' => 'required|exists:learners,lrn',
            'messages' => 'required|string',
            'adminID' => 'required|exists:admins,adminid',
            'mid' => 'required',
        ]);

        $admin = Admin::find($validatedData['adminID']);
        
        $message = new Message();
        $message->lrn = $validatedData['lrn'];
        $message->adminID = $validatedData['adminID'];
        $message->messages = $validatedData['messages'];
        $message->sender_name = $admin->firstname . ' '. $admin->lastname;
        $message->status = 0; // The new reply should remain unread
        $message->save();

        $adminID = $request->adminID;
        $lrn = $request->lrn;

        DB::table('messages')
                ->where('adminID', $adminID)
                ->where('lrn', $lrn)
                ->where('messageid', '!=', $message->id)
                ->update(['status' => 1]);

        return response()->json(['message' => 'Reply sent successfully!'], 200);
    }

    public function sendMessage(Request $request)
    {
        $validatedData = $request->validate([
            'lrn' => 'required|exists:learners,lrn',
            'messages' => 'required|string',
            'adminID' => 'required|exists:admins,adminid',
        ]);

        $admin = Admin::find($validatedData['adminID']);

        $message = new Message();
        $message->lrn = $validatedData['lrn'];
        $message->adminID = $validatedData['adminID'];
        $message->messages = $validatedData['messages'];
        $message->sender_name = $admin->firstname . ' ' . $admin->lastname; // Store sender's name
        $message->status = 0; // The new reply should remain unread
        $message->save();

        $adminID = $request->adminID;
        $lrn = $request->lrn;

        DB::table('messages')
                ->where('adminID', $adminID)
                ->where('lrn', $lrn)
                ->where('messageid', '!=', $message->messageid)
                ->update(['status' => 1]);


        return response()->json(['message' => 'Message sent successfully!'], 200);
    }

    public function uploadProfilePicture(Request $request, $id)
    {
        $request->validate([
            // 'adminID' => 'required',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // $id = $request->input('id');

        if($request->hasFile('profile_picture')){
            $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');

            $fileName = basename($filePath);

            $destinationPath = public_path('storage/profile_pictures');
            $request->file('profile_picture')->move($destinationPath, $fileName);

            DB::table('admins')->updateOrInsert(
                ['adminID' => $id],
                ['profile_picture' => $fileName]
            );

            return  response()->json(['message' => 'Profile picture updated successfully', 'image_name' => $fileName], 200);
        } else {
            return response()->json(['error' => 'No File Uploaded'], 400);
        }
    }

    public function updateAdminPassword(Request $request, $id)
    {
        $request->validate([
            'oldpassword' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::where('adminID', $id)->first();

        if(!$admin){
            return response()->json(['message' => 'Admin Not Found'], 404);
        }

        if(!Hash::check($request->oldpassword, $admin->password)){
            return response()->json(['message' => 'Old password does not match'], 400);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    //Student API
    public function getSubjects($lrn)
    {
        $subjects = DB::table('rosters')
            ->join('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->join('rooms', 'classes.roomid', '=', 'rooms.roomid')
            ->join('admins', 'classes.adminid', '=', 'admins.adminID')
            ->join('subjects', 'classes.subjectid', '=', 'subjects.subjectid')
            ->where('rosters.lrn', $lrn)
            ->select(
                'classes.*',
                'subjects.*',
                'admins.*',
                'rooms.school',
                DB::raw("CONCAT(admins.firstname, ' ', admins.middlename, ' ', admins.lastname) AS admin_name")
            )
            ->distinct()
            ->get();


        return response()->json($subjects);
    }

    public function getSubjectsToday(Request $request)
    {
        $today = date('l');
        $lrn = $request->input('lrn');

        $subjects = DB::table('rosters')
            ->join('learners', 'rosters.lrn', '=', 'learners.lrn')
            ->join('classes', 'rosters.classid', '=', 'classes.classid')
            ->join('admins', 'classes.adminid', '=', 'admins.adminID')
            ->join('subjects', 'classes.subjectid', '=', 'subjects.subjectid')
            ->where('rosters.lrn', $lrn)
            ->where('classes.schedule', 'LIKE', '%' . $today . '%')
            ->select(
                'learners.*',       // Select all columns from the learners table
                'rosters.*',        // Select all columns from the rosters table
                'classes.*',        // Select all columns from the classes table
                'subjects.*',       // Select all columns from the subjects table
                DB::raw("CONCAT(admins.firstname, ' ', admins.middlename, ' ', admins.lastname) AS admin_name")
            )
            ->get();

        return response()->json($subjects);
    }

    public function getModules(Request $request)
    {
        $classid = $request->input('classid');

        $modules = DB::table('modules')
            ->select('modules.*', DB::raw("DATE_FORMAT(modules.date, '%M %d, %Y') as formatted_date"))
            ->where('modules.classid', $classid)
            // ->orderBy('modules.date', 'desc')
            ->get();


        return response()->json($modules);
    }

    public function getLessonID2(Request $request)
    {
        $mid = $request->input('mid');

        $lessons = DB::table('lessons')
            ->select('lessons.*')
            ->where('module_id', $mid)
            ->get();


        return response()->json($lessons);
    }

    public function getLessons(Request $request)
    {
        $module_id = $request->input('moduleID');

        // Get all lessons for the specified module
        $lessons = DB::table('lessons')
        ->select('lessons.*', DB::raw('(SELECT COUNT(*) FROM assessments WHERE assessments.lesson_id = lessons.lesson_id) as total_assessments'))
        ->where('lessons.module_id', $module_id)
        ->get();

        // For each lesson, fetch related media data
        foreach ($lessons as $lesson) {
            $lesson->media = DB::table('media')
                ->where('lesson_id', $lesson->lesson_id)
                ->get();
        }

        return response()->json($lessons);
    }

    public function getQuestions(Request $request)
    {
        $assessmentID = $request->input('assessmentID');
        $lrn = $request->input('lrn');

        $questions = DB::table('questions')
            ->leftJoin('answers', function ($join) use ($lrn) {
                $join->on('answers.question_id', '=', 'questions.question_id')
                    ->where('answers.lrn', '=', $lrn);
            })
            ->select('questions.*', 'answers.answer as user_answer')
            ->where('questions.assessment_id', $assessmentID)
            ->get();

        foreach ($questions as $q) {
            if ($q->type == "multiple-choice") {
                $q->options = DB::table('options')
                    ->where('question_id', $q->question_id)
                    ->get();
            }
        }

        return response()->json($questions);
    }

    public function getAssessments(Request $request)
    {
        $lesson_id = $request->input('lessonID');
        $lrn = $request->input('lrn'); // Assuming the LRN is passed from the request
        $currentDate = now()->format('Y-m-d');

        $assessments = DB::table('assessments')
            ->select(
                'assessments.*',
                'assessment_answers.assessmentid as aid',  // Aliasing assessmentid to aid
                'assessment_answers.lrn as slrn',         // Aliasing lrn to slrn  
                'assessment_answers.score as scores',               // Fetching the score 
                DB::raw("DATE_FORMAT(assessment_answers.date_submission, '%M %d, %Y') as formatted_date"),
                DB::raw("DATE_FORMAT(assessments.due_date, '%M %d, %Y') as due_date"),
                DB::raw("IF(assessments.available, 1, 0) as isOpen") // Keep it open on due date or later
            )
            ->leftJoin('assessment_answers', function ($join) use ($lrn) {
                $join->on('assessments.assessmentid', '=', 'assessment_answers.assessmentid')
                    ->where('assessment_answers.lrn', '=', $lrn);
            })
            ->where('assessments.lesson_id', $lesson_id)
            ->get();


        return response()->json($assessments);
    }

    public function saveAnswers(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'qid' => 'required',
            'slrn' => 'required',
            'answerValue' => 'required|string',
        ]);

        // Extract values
        $lrn = $validated['slrn'];
        $qid = $validated['qid'];
        $answer = $validated['answerValue'];

        // Upsert query
        DB::table('answers')->updateOrInsert(
            [
                'question_id' => $qid, // Unique constraint
                'lrn' => $lrn,
            ],
            [
                'answer' => $answer, // Values to insert or update
            ]
        );


        // Return a success response
        return response()->json('Answer saved successfully');
    }

    public function getAssessmentProgress(Request $request)
    {
        $lrn = $request->input('lrn');

        $progress = DB::table('assessment_answers')
            ->select('assessment_answers.*')
            ->where('assessment_answers.lrn', $lrn)
            ->get();

        return response()->json($progress);
    }

    public function getLearnerByToken(Request $request)
    {
        //Retrieve the currently authenticated student
        $learner = $request->user();

        if ($learner) {
            //Return all the student's information
            return response()->json($learner);
        } else {
            return response()->json(['message' => 'Student not found'], 404);
        }
    }

    public function getLearner($lrn)
    {
        $learner = DB::table('learners')->where('lrn', $lrn)->first();

        if (!$learner) {
            return response()->json(['message' => 'Learner not found'], 404);
        }

        // return response()->json($learner, 200);
        return [
            'learner' => $learner,
            'image' => $learner->image
        ];
    }

    public function getAnswerFile(Request $request)
    {
        $aid = $request->input('aid');
        $lrn = $request->input('lrn');
        $fileInfo = DB::table('assessment_answers')
            ->where('lrn', $lrn)
            ->where('assessmentid', $aid)
            ->first();

        if (!$fileInfo) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // return response()->json($learner, 200);
        return [
            'assessment' => $fileInfo,
            'file' => $fileInfo->file
        ];
    }

    public function saveAssessmentsAnswer(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'assessmentID' => 'required',
            'lrn' => 'required'

        ]);
        // Extract values
        $lrn = $validated['lrn'];
        $assessmentID = $validated['assessmentID'];

        // Get the current date and time
        $today = now()->format('Y-m-d H:i:s');

        // Upsert query
        DB::table('assessment_answers')->updateOrInsert(
            [
                'assessmentid' => $assessmentID, // Unique constraint
                'lrn' => $lrn,
            ],
            [
                'date_submission' => $today, // Add the current date and time
            ]
        );


        // Return a success response
        return response()->json('Answer saved successfully');
    }

    public function updateLearnerPassword(Request $request, $lrn)
    {
        // Validate the incoming request
        $request->validate([
            'oldpassword' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the learner by LRN
        $learner = Learner::where('lrn', $lrn)->first();

        // Check if the learner exists
        if (!$learner) {
            return response()->json(['message' => 'Learner not found'], 404);
        }

        // Check if the old password matches the stored password
        if (!Hash::check($request->oldpassword, $learner->password)) {
            return response()->json(['message' => 'Old password does not match'], 400);
        }

        // Update to the new password
        $learner->password = Hash::make($request->password);
        $learner->save();

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function getPendingAssessments(Request $request)
    {
        $lrn = $request->input('lrn');
        $currentDate = now()->format('Y-m-d');

        $pendingAssessments = DB::table('assessments')
            ->leftJoin('assessment_answers', function ($join) use ($lrn) {
                $join->on('assessment_answers.assessmentid', '=', 'assessments.assessmentid')
                    ->where('assessment_answers.lrn', '=', $lrn);
            })
            ->leftJoin('lessons', 'assessments.lesson_id', '=', 'lessons.lesson_id')
            ->leftJoin('modules', 'lessons.module_id', '=', 'modules.modules_id')
            ->leftJoin('rosters', 'modules.classid', '=', 'rosters.classid')
            ->whereNull('assessment_answers.lrn')
            ->where('rosters.lrn', '=', $lrn)
            ->select(
                'assessments.*',
                'assessment_answers.lrn as slrn', 
                DB::raw("IF(assessments.due_date >= '$currentDate', 1, 0) as isDateDue"),
                DB::raw("IF(assessments.available, 1, 0) as isOpen")
            )
            ->get();

        return response()->json($pendingAssessments);
    }

    public function getDiscussions(Request $request)
    {
        $lessonID = $request->input('lessonid');

        $discussionlist = DB::table('discussions')
            ->select(
                'discussions.*',
                DB::raw("DATE_FORMAT(discussions.created_at, '%M %d, %Y') as date_created")
            ) //fetching date submission
            ->where('discussions.lesson_id', $lessonID)
            ->get();

        return response()->json($discussionlist);
    }

    public function uploadProfilePicture2(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'lrn' => 'required'
        ]);

        // Retrieve the learner using LRN
        $lrn = $request->input('lrn');

        // Check if a file was uploaded
        if ($request->hasFile('profile_picture')) {
            // Store the new image in the 'public/profile_pictures' directory
            $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');

            // Get the file name to save in the database
            $fileName = basename($filePath); // Get just the file name

            // Save the image in the assets folder
            $destinationPath = public_path('assets/profile_pictures');
            $request->file('profile_picture')->move($destinationPath, $fileName);

            // Use query builder to check and update or insert profile picture for the student
            DB::table('learners')->updateOrInsert(
                ['lrn' => $lrn], // Condition to match LRN
                ['image' => $fileName] // Save just the image name in the database
            );

            return response()->json(['message' => 'Profile picture updated successfully', 'image_name' => $fileName], 200);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,png,jpeg|max:2048', // Accept specific file types
            'lrn' => 'required',
            'assessmentid' => 'required' // Validation for assessment ID
        ]);

        // Retrieve the learner and assessment details
        $lrn = $request->input('lrn');
        $assessmentId = $request->input('assessmentid');
        $dateSubmission = now();

        // Check if a file was uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName(); // Get original file name
            $destinationPath = public_path('assets/files'); // Directory to store the file

            // Move the file to the destination path with its original name
            $file->move($destinationPath, $originalFileName);

            // Check if a record already exists
            $existingRecord = DB::table('assessment_answers')
                ->where('lrn', $lrn)
                ->where('assessmentid', $assessmentId)
                ->first();

            if ($existingRecord) {
                // Update the existing record
                DB::table('assessment_answers')
                    ->where('answerid', $existingRecord->answerid)
                    ->update(['file' => $originalFileName, 'date_submission' => $dateSubmission]);
            } else {
                // Insert a new record
                DB::table('assessment_answers')->insert([
                    'lrn' => $lrn,
                    'file' => $originalFileName,
                    'assessmentid' => $assessmentId,
                    'date_submission' => $dateSubmission
                ]);
            }

            return response()->json(['message' => 'File uploaded successfully', 'file' => $originalFileName], 200);
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    public function checkProgress(Request $request)
    {
        $classID = $request->input('cid'); //Classes ID
        $lrn = $request->input('lrn'); //Learner's LRN
        $currentDate = now()->format('Y-m-d');

        $subjectprogress = DB::table('classes')
            ->join('modules', 'classes.classid', '=', 'modules.classid')
            ->join('lessons', 'modules.modules_id', '=', 'lessons.module_id')
            ->join('assessments', 'lessons.lesson_id', '=', 'assessments.lesson_id')
            ->leftJoin('assessment_answers', function ($join) use ($lrn) {
                $join->on('assessment_answers.assessmentid', '=', 'assessments.assessmentid')
                    ->where('assessment_answers.lrn', '=', $lrn);
            })
            // ->whereNull('assessment_answers.lrn')
            ->where('classes.classid', $classID) // Added condition for classID
            ->select(
                'assessments.*',
                'assessment_answers.lrn as slrn',
                DB::raw("IF(assessments.due_date >= '$currentDate', 1, 0) as isDateDue"), // Keep it open on due date or later
                DB::raw("IF(assessments.available, 1, 0) as isOpen")
            )
            ->get();

        return response()->json($subjectprogress); // Return the subject progress
    }

    public function getScore(Request $request)
    {
        $aid = $request->input('aid');
        $lrn = $request->input('lrn');
        // Query to get the score
        $score = DB::table('assessment_answers') // Replace 'your_table_name' with the actual table name
            ->where('lrn', $lrn)
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json($score);
    }

    public function getFile(Request $request)
    {
        $aid = $request->input('aid');
        $lrn = $request->input('lrn');
        $score = DB::table('assessment_answers') // Replace 'your_table_name' with the actual table name
            ->where('lrn', $lrn)
            ->where('assessmentid', $aid)
            ->get();
        return response()->json($score);

    }

    public function getAnnouncements(Request $request)
    {
        $classid= $request->input('cid');

        $announcements = DB::table('announcements')
        ->select('announcements.*', DB::raw("DATE_FORMAT(announcements.created_at, '%M %d, %Y') as formatted_date"))
        ->where('announcements.classid', $classid)
        ->get();
    

        return response()->json($announcements);
    }

    public function getResultAnalysis(Request $request)
    {
        $aid = $request->query('aid');
        $lrn = $request->query('lrn');

        // Use DB::table() for query builder
        $result = DB::table('answers')
        ->join('questions', 'answers.question_id', '=', 'questions.question_id')
        ->join('assessments', 'questions.assessment_id', '=', 'assessments.assessmentid')
        ->where('questions.assessment_id', $aid)
        ->where('answers.lrn', $lrn)
        ->select(
            'questions.question as question', 
            'questions.type as question_type', 
            'questions.key_answer as key_answer',
            'answers.answer as answer',
            'answers.score as score',
        )
        ->get();    


            // Remove <p> tags from essay answers
        $result = $result->map(function ($item) {
            if ($item->question_type === 'Essay') {
                $item->answer = strip_tags($item->answer);
            }
            return $item;
        });


        return response()->json($result);
    }

    public function getmoduleID(Request $request) {
        $aid = $request->query('aid');

        $result = DB::table('classes')
        ->join('admins', 'classes.adminid', '=', 'admins.adminID')
        ->join('subjects', 'classes.subjectid', '=', 'subjects.subjectid')
        ->join('modules', 'classes.classid', '=', 'modules.classid' )
        ->join('lessons', 'modules.modules_id', '=', 'lessons.module_id')
        ->join('assessments', 'lessons.lesson_id', '=', 'assessments.lesson_id')
        ->select('modules.modules_id as mid', 'modules.description as modesc', 'modules.title as title', DB::raw("CONCAT(admins.firstname, ' ', admins.middlename, ' ', admins.lastname) AS admin_name"), 'subjects.subject_name as subname', 'classes.classid',)
        ->where('assessments.assessmentid', $aid)
        ->get();

        return response()->json($result);
    }

    public function showMessages2($id)
    {
        $messages = DB::table('messages')
                ->join('learners', 'learners.lrn', '=', 'messages.lrn')
                ->join('admins', 'messages.adminID', '=', 'admins.adminID')
                ->where('messages.lrn', $id)
                ->select('messages.*', 'admins.firstname', 'admins.lastname', 'admins.adminID')
                ->orderBy('messages.updated_at', 'asc')
                ->get();
        
        return response()->json($messages);
    }

    public function getAdmin($id) 
    {
        $admin = DB::table('admins')
        ->distinct()
        ->join('classes', 'classes.adminid', '=', 'admins.adminID')
        ->join('rosters', 'rosters.classid', '=', 'classes.classid')
        ->where('rosters.lrn', $id)
        ->select('admins.*')
        ->get();    

        return response()->json($admin);
    }

    public function sendReply2(Request $request)
    {
        $validatedData = $request->validate([
            'lrn' => 'required|exists:learners,lrn',
            'messages' => 'required|string',
            'adminID' => 'required|exists:admins,adminID',
            // 'mid' => 'required'
        ]);

        //Fetch learner details
        $learner = Learner::where('lrn', $validatedData['lrn'])->first();

        //Update or create the message
        // $message = Message::where('messageid', $validatedData['mid'])
            // ->orderBy('created_at', 'desc')
            // ->first();
        
        // if ($message) {
            $message = new Message();
            $message->messages = $validatedData['messages'];
            $message->lrn = $validatedData['lrn'];
            $message->adminID = $validatedData['adminID'];
            $message->sender_name = $learner->firstname . ' ' . $learner->lastname; //Add sender name
            $message->updated_at = now();
            $message->save();
        // }

        return response()->json(['message' => 'Reply sent successfully!'], 200);

    }

    public function sendMessage2(Request $request)
    {
        $validatedData = $request->validate([
            'adminID' => 'required|exists:admins,adminID',
            'messages' => 'required|string',
            'lrn' => 'required|exists:learners,lrn',
        ]);

        //Fetch learner details
        $learner = Learner::find($validatedData['lrn']);

        $message = new Message();
        $message->lrn = $validatedData['lrn'];
        $message->adminID = $validatedData['adminID'];
        $message->messages = $validatedData['messages'];
        $message->sender_name = $learner->firstname . ' ' . $learner->lastname;
        $message->status = 0;
        $message->save();

        $adminID = $request->adminID;
        $lrn = $request->lrn;

        DB::table('messages')
            ->where('adminID', $adminID)
            ->where('lrn', $lrn)
            ->where('messageid', '!=', $message->messageid)
            ->update(['status' => 1]);

        return response()->json(['message' => 'Message sent successfully!'], 200);
    }

    public function getUnreadMessages2($lrn)
    {

        // Fetch unread messages based on the sender_name and join with learners
        $unreadMessages = DB::table('messages')
        ->join('learners', 'messages.lrn', '=', 'learners.lrn')
        ->where('learners.lrn', $lrn)
        ->whereRaw('messages.sender_name != CONCAT(learners.firstname, " ", learners.lastname)')
        ->where('messages.status', 0)
        ->select('messages.*')
        ->get();
        $count = $unreadMessages->count(); // Get total unread messages

        return $count;
    }

    public function clearUnreadMessages(Request $request)
    {
        $lrn = $request->input('lrn'); // Pass LRN dynamically (e.g., logged-in user)

        // Clear logic can vary depending on your implementation
        // Here, just an acknowledgment response
        return response()->json(['message' => 'Messages cleared']);
    }

    public function getAdminDetails($lrn)
    {
        $admin = DB::table('admins')
            ->join('classes', 'admins.adminID', '=', 'classes.adminid')
            ->join('rosters', 'classes.classid', '=', 'rosters.classid')
            ->where('rosters.lrn', $lrn)
            ->distinct()
            ->select('admins.adminID')
            ->first();
    
        // Check if an admin was found and return the adminID as a string
        if ($admin) {
            return (string) $admin->adminID; // Cast to string
        }
    
        return null; // Return null or handle the case where no admin is found
    }

    public function createAssessment2(Request $request)
    {
        //
        $validatedData = $request->validate([
            'Lesson_ID' => 'required|integer',
            'Title' => 'required|string|max:255',
            'Instruction' => 'required|string|max:255',
            'Description' => 'required|string|max:255',
            'Due_date' => 'date',
        ]);

        $assess = Assessment::create($validatedData);
        return response()->json($assess, 201);
    }

    public function showAssessment2()
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

    public function requestChangePassword(Request $request)
    {
        // Validate the incoming email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Find the learner by email
        $learner = Learner::where('email', $request->email)->first();

        if ($learner) {
            // Update the password_change_request column to 2
            $learner->password_change_request = 1;
            $learner->save();

            return response()->json([
                'message' => 'Password change request submitted successfully.'
            ], 200);
        }

        return response()->json([
            'message' => 'Learner not found.'
        ], 404);
    }

    public function getPasswordChangeRequestStatus(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email'
        ]);

        // Find the learner by email
        $learner = Learner::where('email', $request->email)->first();

        if ($learner) {
            // Return the password_change_request value
            return response()->json([
                'password_change_request' => $learner->password_change_request,
            ], 200);
        }

        return response()->json([
            'message' => 'Learner not found.'
        ], 404);
    }

    public function changePassword(Request $request, $email) 
    {
                // Validate the incoming request
                $request->validate([
                    'password' => 'required|string|min:8|confirmed',
                ], [
                    'password.confirmed' => 'The password field confirmation does not match.'
                ]);

                
        
                // Find the learner by email
                $learner = Learner::where('email', $email)->first();
        
                // Check if the learner exists
                if (!$learner) {
                    return response()->json(['message' => 'Learner not found'], 404);
                }
        
                // Check if the old password matches the stored password
                // if (!Hash::check($request->oldpassword, $learner->password)) {
                //     return response()->json(['message' => 'Old password does not match'], 400);
                // }
        
                // Update to the new password
                $learner->password = Hash::make($request->password);
                $learner->save();
        
                return response()->json(['message' => 'Password updated successfully'], 200);
    }
}
