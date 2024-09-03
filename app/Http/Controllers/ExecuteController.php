<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Models\Subject;
use App\Models\Assessment;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExecuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // $subject = Subject::all();
        // $subject = Subject::where("Program", "alsElem")->get();
        // $subject = DB::table('classes')
        //     ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
        //     ->where("Program", "alsElem")
        //     ->select('subjects.image', 'subjects.subject_name', 'classes.Schedule')
        //     ->get();
        // return $subject;

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
            'Title' => 'required|string|max:255',
            'Title' => 'required|string|max:255',
            'Title' => 'required|string|max:255',
            'Due_date' => 'required|date',
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
        // $subject = Subject::find($id);
        // $subject = Subject::where('subjectID', $id)->first();
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

        // Find the subject by ID
        // $subject = Subject::find($id);

        // if ($subject) {
        //     return response()->json($subject);
        // } else {
        //     return response()->json(['message' => 'Subject not found'], 404);
        // }
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
        // $subject = Subject::all();
        // $subject = Subject::where("Program", "alsElem")->get();
        // $subject = DB::table('classes')
        //     ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
        //     ->where("Program", "blp")
        //     ->select('subjects.image', 'subjects.subject_name', 'classes.Schedule')
        //     ->get();
        $subject = DB::table('classes')
            ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            ->select('subjects.subjectID', 'subjects.image', 'subjects.subject_name', 'subjects.Program', 'classes.Schedule')
            ->get();
        return $subject;
    }
}
