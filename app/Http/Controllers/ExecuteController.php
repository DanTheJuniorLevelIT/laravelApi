<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Models\Subject;
use App\Models\Assessment;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
use App\Models\Admin;
use App\Models\Learner;
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
            'Lesson_ID' => 'required|integer',
            'Title' => 'required|string|max:255',
            'Instruction' => 'required|string|max:255',
            'Description' => 'required|string|max:255',
            'Due_date' => 'date',
        ]);

        $assess = Assessment::create($validatedData);
        return response()->json($assess, 201);
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
                ->select('subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
                ->where('subjects.subjectID', '=', $id)
                ->get();

        if ($subject) {
            return response()->json($subject);
            // return $subject;
        } else {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Execute $execute)
    {
        //
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
            ->select('subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
            ->get();
        return $subject;
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

        return [
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
}
