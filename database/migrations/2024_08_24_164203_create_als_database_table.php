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
            $table->id('studentid');
            $table->string('lrn')->unique()->nullable();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('extension_name')->nullable();
            $table->date('birthdate');
            $table->string('gender');
            $table->string('placeofbirth');
            $table->string('religion')->nullable();
            $table->string('civil_status');
            $table->string('education')->nullable();
            $table->string('contact_numbers')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('enrollees', function (Blueprint $table) {
            $table->id('enrolID');
            $table->string('lrn');
            $table->string('program');
            $table->string('status');
            $table->string('school_year');
            $table->date('enrolldate');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('adminID');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('gender');
            $table->date('birthdate');
            $table->string('address');
            $table->string('mobile_number');
            $table->string('role');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id('messageid');
            $table->unsignedBigInteger('senderid');
            $table->unsignedBigInteger('receiverid');
            $table->text('messages');
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('senderID')->references('adminID')->on('admins')->onDelete('cascade');
            // $table->foreign('receiverID')->references('adminID')->on('admins')->onDelete('cascade');
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id('classid');
            $table->unsignedBigInteger('adminid');
            $table->unsignedBigInteger('subjectid');
            $table->unsignedBigInteger('roomid');
            $table->string('schedule');
            // $table->timestamps();

            // Foreign Keys
            // $table->foreign('adminID')->references('adminID')->on('admins')->onDelete('cascade');
            // $table->foreign('subjectID')->references('subjectID')->on('subjects')->onDelete('cascade');
            // $table->foreign('RoomID')->references('RoomID')->on('rooms')->onDelete('cascade');
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id('roomid');
            $table->string('location');
            $table->string('school');
            $table->integer('capacity');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subjectid');
            $table->string('image');
            $table->string('subject_name');
            $table->text('description')->nullable();
            $table->string('program');
            // $table->timestamps();
        });

        Schema::create('rosters', function (Blueprint $table) {
            $table->id('rosterid');
            $table->unsignedBigInteger('classid');
            $table->unsignedBigInteger('lrn');
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announceid');
            $table->unsignedBigInteger('subjectid');
            $table->string('title');
            $table->text('instruction');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('subjectID')->references('subjectID')->on('subjects')->onDelete('cascade');
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id('modules_id');
            $table->unsignedBigInteger('classid');
            $table->string('title');
            $table->text('description');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('classID')->references('classID')->on('classes')->onDelete('cascade');
        });

        Schema::create('discussions', function (Blueprint $table) {
            $table->id('discussionid');
            $table->unsignedBigInteger('lesson_id');
            $table->text('Discussion_Topic');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('Lesson_ID')->references('Lessons_ID')->on('lessons')->onDelete('cascade');
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id('replyid');
            $table->unsignedBigInteger('discussionid');
            $table->unsignedBigInteger('lrn');
            $table->text('reply');
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('discussionID')->references('discussionID')->on('discussions')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id('lesson_id');
            $table->unsignedBigInteger('module_id');
            $table->string('topic_title');
            $table->text('lesson');
            $table->text('handout')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();

            // Foreign Key
            // $table->foreign('Module_ID')->references('Module_ID')->on('modules')->onDelete('cascade');
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id('assessmentid');
            $table->unsignedBigInteger('lesson-id');
            $table->string('title');
            $table->text('instruction');
            $table->text('description');
            $table->date('due_date');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('Lesson_ID')->references('Lessons_ID')->on('lessons')->onDelete('cascade');
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->unsignedBigInteger('assessment_id');
            $table->string('question');
            $table->string('type');
            $table->string('key_answer');
            $table->integer('points');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('Assessment_ID')->references('assessmentID')->on('assessments')->onDelete('cascade');
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id('option_id');
            $table->unsignedBigInteger('question_id');
            $table->string('option_text');
            $table->timestamps();

            // Foreign Key
            // $table->foreign('Question_ID')->references('Question_ID')->on('questions')->onDelete('cascade');
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->unsignedBigInteger('question_id');
            $table->string('lrn');
            $table->string('answer');
            $table->integer('score');
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('Question_ID')->references('Question_ID')->on('questions')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id('answerid');
            $table->string('lrn');
            $table->unsignedBigInteger('assessmentid');
            $table->string('link')->nullable();
            $table->integer('score')->nullable();
            $table->date('date_submission');
            $table->string('file')->nullable();
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('assessmentID')->references('assessmentID')->on('assessments')->onDelete('cascade');
            // $table->foreign('LRN')->references('LRN')->on('learners')->onDelete('cascade');
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id('media_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('uploader_id');
            $table->string('type');
            $table->string('filename');
            $table->timestamps();

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
