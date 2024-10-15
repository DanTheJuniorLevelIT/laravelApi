<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Models\Subject;
use App\Models\Assessment;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
use App\Models\Admin;
use App\Models\Learner;
use App\Models\Question;
use App\Models\Option;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Media;
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
        // $subject = DB::table('classes')
        //     ->rightJoin('subjects', 'classes.subjectid', '=', 'subjects.subjectid')
        //     ->rightJoin('rooms', 'classes.roomid', '=', 'rooms.roomid')
        //     ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.schedule', 'rooms.school')
        //     ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
        //     ->get();
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
            'key_answer' => 'required|string',
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

        $options = [];

        // Save the options for multiple-choice questions
        if ($request->type === 'multiple-choice' && !empty($request->options)) {
            foreach ($request->options as $optionText) {
                $option = Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                ]);
                $options[] = $optionText; // Store the options for returning in the response
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


    /**
     * Display the specified resource.
     */
    // public function show(Execute $id)
    public function show($id)
    {
        //
        $subject = DB::table('classes')
                ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
                ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
                // ->where('subjects.subjectID', '=', $id)
                ->where('classes.classid', '=', $id)
                ->get();

        if ($subject) {
            return response()->json($subject);
            // return $subject;
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
        // $subject = DB::table('classes')
        //     ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
        //     ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.schedule')
        //     ->where('classes.adminid', $id) 
        //     ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
        //     ->get();
        $subject = DB::table('classes')
            ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID') // left join
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid') // left join
            ->select('classes.classid', 'subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'classes.schedule', 'rooms.school')
            ->where('classes.adminid', $id) 
            // ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
            ->where('classes.schedule', 'LIKE', '%Thursday%') // Filter based on today's day
            ->get();

        $school = DB::table('classes')
            ->leftJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID') // left join
            ->leftJoin('rooms', 'classes.roomid', '=', 'rooms.roomid') // left join
            ->select('rooms.school')
            ->where('classes.adminid', $id) 
            // ->where('classes.schedule', 'LIKE', '%' . $today . '%') // Filter based on today's day
            ->where('classes.schedule', 'LIKE', '%Thursday%') // Filter based on today's day
            ->first();

        if ($subject) {
            // return response()->json($subject);
            // return $subject;
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
            // return response()->json($subject);
            // return $subject;
            return [
                'modules' => $modules
                // 'school' => $school
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
            // ->get();

        return $assess;
    }

    // public function showQuestions($id)
    // {
    //     $question = DB::table('questions')
    //         ->rightJoin('assessments', 'questions.assessment_id', '=', 'assessments.assessmentid')
    //         ->rightJoin('options', 'questions.question_id', '=', 'options.question_id')
    //         ->select('questions.question', 'questions.type', 'questions.key_answer', 'questions.points', 'options.option_text')
    //         ->where('questions.assessment_id', $id)
    //         ->get();
        
    //     return $question;
    // }

    // public function showQuestions($id)
    // {
    //     // Fetch questions and include options if the type is 'multiple-choice'
    //     $questions = DB::table('questions')
    //         ->leftJoin('options', 'questions.question_id', '=', 'options.question_id')
    //         ->select(
    //             'questions.question_id',
    //             'questions.assessment_id',
    //             'questions.question',
    //             'questions.type',
    //             'questions.key_answer',
    //             'questions.points',
    //             'questions.created_at',
    //             'questions.updated_at',
    //             'options.option_text',
    //             'options.option_id'
    //         )
    //         ->where('questions.assessment_id', $id)
    //         ->orderBy('questions.created_at', 'asc')
    //         ->get();

    //     // Group the questions and their associated options together
    //     $groupedQuestions = $questions->groupBy('question_id')->map(function ($questionGroup) {
    //         $question = $questionGroup->first();

    //         return [
    //             'question_id' => $question->question_id,
    //             'question' => $question->question,
    //             'type' => $question->type,
    //             'key_answer' => $question->key_answer,
    //             'points' => $question->points,
    //             'created_at' => $question->created_at,
    //             'updated_at' => $question->updated_at,
    //             'options' => $question->type === 'multiple-choice' ? $questionGroup->pluck('option_text') : null,
    //         ];
    //     });

    //     return response()->json($groupedQuestions);
    //     // return $groupedQuestions;
    // }

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
        $options = [];
        if ($request->type === 'multiple-choice') {
            $options = Option::where('question_id', $question->question_id)->pluck('option_text');
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

    public function showAll()
    {
        //
        $subject = DB::table('classes')
            ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            // ->select('subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
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

        return $subject;;
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

    //create module
    public function createModule(Request $request)
    {
        $validatedData = $request->validate([
            'classid' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
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
        'modules_id' => 'required|integer',
        'topic_title' => 'required|string|max:255',
        'lesson' => 'required|string|max:255',
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
            'lessons.modules_id',
            'lessons.topic_title',
            'lessons.lesson',
            'lessons.handout',
            'lessons.file',
            DB::raw('GROUP_CONCAT(media.filename) as media_files') // Concatenate media filenames
        )
        ->where('lessons.modules_id', $id)
        ->groupBy(
            'lessons.lesson_id',
            'lessons.modules_id',
            'lessons.topic_title',
            'lessons.lesson',
            'lessons.handout',
            'lessons.file'
        )  // Add all selected columns to GROUP BY
        ->get();

    return $lessons;

}

public function getlessonid($id)
{
    $les = DB::table('lessons')
                    ->select('lessons.*')
                    ->where('lessons.lesson_id',$id)
                    ->first(); // Fetches all matching modules

    return $les;
}

public function updateLessonInfo(Request $request, $id) {
    \Log::info('Received Lesson ID for update: ' . $id);  // Check if the ID is correctly passed

    // Check if the ID is null or invalid
    if (is_null($id)) {
        return response()->json(['message' => 'Lesson ID is missing'], 400);
    }

    // Validate the request
    $validatedData = $request->validate([
        'topic_title' => 'required|string|max:255',
        'lesson' => 'required|string|max:255',
    ]);

    // Fetch the lesson by lesson_id
    $lesson = Lesson::where('lesson_id', $id)->firstOrFail();

    // Update lesson data
    $lesson->fill($validatedData);
    $lesson->save();

    return response()->json(['message' => 'Lesson updated successfully']);
}

public function deleteLesson($id)
{
    $lesson = Lesson::find($id);

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




}
