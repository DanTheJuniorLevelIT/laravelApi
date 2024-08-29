<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learners', function (Blueprint $table) {
            $table->id('StudentID');
            $table->string('LRN')->unique();;
            $table->string('Firstname');
            $table->string('Middlename')->nullable();
            $table->string('Lastname');
            $table->string('Extension_name')->nullable();
            $table->date('Birthdate');
            $table->string('Gender');
            $table->string('PlaceOfBirth');
            $table->string('Religion')->nullable();
            $table->string('Civil_status');
            $table->string('Education')->nullable();
            $table->string('Contact_Numbers')->nullable();
            $table->string('Email')->unique();
            $table->string('Password');
            // $table->timestamps();
        });

        Schema::create('enrollees', function (Blueprint $table) {
            $table->id('enrolID');
            $table->string('LRN');
            $table->string('Program');
            $table->string('Status');
            $table->string('School_Year');
            $table->date('Enrolldate');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('adminID');
            $table->string('Firstname');
            $table->string('Middlename')->nullable();
            $table->string('Lastname');
            $table->string('Gender');
            $table->date('Birthdate');
            $table->string('Address');
            $table->string('Mobile_number');
            $table->string('Role');
            $table->string('Email')->unique();
            $table->string('Password');
            // $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id('messageID');
            $table->unsignedBigInteger('senderID');
            $table->unsignedBigInteger('receiverID');
            $table->text('Messages');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('senderID')->references('adminID')->on('admins')->onDelete('cascade');
            // $table->foreign('receiverID')->references('adminID')->on('admins')->onDelete('cascade');
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id('classID');
            $table->unsignedBigInteger('adminID');
            $table->unsignedBigInteger('subjectID');
            $table->unsignedBigInteger('RoomID');
            $table->string('Schedule');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('adminID')->references('adminID')->on('admins')->onDelete('cascade');
            // $table->foreign('subjectID')->references('subjectID')->on('subjects')->onDelete('cascade');
            // $table->foreign('RoomID')->references('RoomID')->on('rooms')->onDelete('cascade');
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id('RoomID');
            $table->string('Location');
            $table->string('Room_No');
            $table->integer('Capacity');
            // $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subjectID');
            $table->string('image');
            $table->string('subject_name');
            $table->text('Description')->nullable();
            $table->string('Program');
            // $table->timestamps();
        });

        Schema::create('rosters', function (Blueprint $table) {
            $table->id('rosterID');
            $table->unsignedBigInteger('classID');
            $table->unsignedBigInteger('LRN');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announceID');
            $table->unsignedBigInteger('subjectID');
            $table->string('Title');
            $table->text('Instruction');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('subjectID')->references('subjectID')->on('subjects')->onDelete('cascade');
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id('Module_ID');
            $table->unsignedBigInteger('classID');
            $table->string('Title');
            $table->text('Description');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
        });

        Schema::create('discussions', function (Blueprint $table) {
            $table->id('discussionID');
            $table->unsignedBigInteger('Lesson_ID');
            $table->text('Discussion_Topic');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('Lesson_ID')->references('Lessons_ID')->on('lessons')->onDelete('cascade');
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id('replyID');
            $table->unsignedBigInteger('discussionID');
            $table->unsignedBigInteger('LRN');
            $table->text('Reply');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('discussionID')->references('discussionID')->on('discussions')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id('Lessons_ID');
            $table->unsignedBigInteger('Module_ID');
            $table->string('Topic_title');
            $table->text('Lesson');
            $table->text('Handout')->nullable();
            $table->string('File')->nullable();
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('Module_ID')->references('Module_ID')->on('modules')->onDelete('cascade');
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id('assessmentID');
            $table->unsignedBigInteger('Lesson_ID');
            $table->string('Title');
            $table->text('Instruction');
            $table->text('Description');
            $table->date('Due_date');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('Lesson_ID')->references('Lessons_ID')->on('lessons')->onDelete('cascade');
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id('Question_ID');
            $table->unsignedBigInteger('Assessment_ID');
            $table->string('Question');
            $table->string('Type');
            $table->string('Key_Answer');
            $table->integer('Points');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('Assessment_ID')->references('assessmentID')->on('assessments')->onDelete('cascade');
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id('Option_ID');
            $table->unsignedBigInteger('Question_ID');
            $table->string('Option_text');
            // $table->timestamps();

            // Foreign Key
            // $table->foreign('Question_ID')->references('Question_ID')->on('questions')->onDelete('cascade');
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id('Answer_ID');
            $table->unsignedBigInteger('Question_ID');
            $table->string('LRN');
            $table->string('Answer');
            $table->integer('Score');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('Question_ID')->references('Question_ID')->on('questions')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id('answerID');
            $table->string('LRN');
            $table->unsignedBigInteger('assessmentID');
            $table->string('Link')->nullable();
            $table->integer('Score')->nullable();
            $table->date('Date_submission');
            $table->string('File')->nullable();
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('assessmentID')->references('assessmentID')->on('assessments')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id('Media_ID');
            $table->unsignedBigInteger('Lesson_ID');
            $table->unsignedBigInteger('Uploader_ID');
            $table->string('Type');
            $table->string('Filename');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('Lesson_ID')->references('Lessons_ID')->on('lessons')->onDelete('cascade');
            // $table->foreign('Uploader_ID')->references('adminID')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learners');
        Schema::dropIfExists('enrollees');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('rosters');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('discussions');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('options');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('media');
    }
};
