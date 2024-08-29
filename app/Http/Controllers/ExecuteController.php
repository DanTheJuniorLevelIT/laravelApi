<?php

namespace App\Http\Controllers;

use App\Models\Execute;
use App\Models\Subject;
use App\Http\Requests\StoreExecuteRequest;
use App\Http\Requests\UpdateExecuteRequest;
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
        $subject = DB::table('classes')
            ->rightJoin('subjects', 'classes.subjectID', '=', 'subjects.subjectID')
            ->where("Program", "alsElem")
            ->select('subjects.image', 'subjects.subject_name', 'classes.Schedule')
            ->get();
        return $subject;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExecuteRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Execute $execute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExecuteRequest $request, Execute $execute)
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
}
