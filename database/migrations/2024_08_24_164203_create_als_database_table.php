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
            $table->string('image')->nullable();
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
            $table->string('profile_picture');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id('messageid');
            $table->unsignedBigInteger('adminID')->nullable();
            $table->unsignedBigInteger('lrn')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('messages');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id('classid');
            $table->unsignedBigInteger('adminid');
            $table->unsignedBigInteger('subjectid');
            $table->unsignedBigInteger('roomid');
            $table->string('schedule');
            $table->timestamps();
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
            $table->timestamps();
        });

        Schema::create('rosters', function (Blueprint $table) {
            $table->id('rosterid');
            $table->unsignedBigInteger('classid');
            $table->unsignedBigInteger('lrn');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announceid');
            $table->unsignedBigInteger('classid');
            $table->string('title');
            $table->text('instruction');
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id('modules_id');
            $table->unsignedBigInteger('classid');
            $table->string('title');
            $table->text('description');
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('discussions', function (Blueprint $table) {
            $table->id('discussionid');
            $table->unsignedBigInteger('lesson_id');
            $table->text('Discussion_Topic');
            $table->timestamps();
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id('replyid');
            $table->unsignedBigInteger('discussionid');
            $table->unsignedBigInteger('lrn')->nullable();
            $table->unsignedBigInteger('adminID')->nullable();
            $table->longText('reply');
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id('lesson_id');
            $table->unsignedBigInteger('module_id');
            $table->string('topic_title');
            $table->longText('lesson');
            $table->text('handout')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id('assessmentid');
            $table->unsignedBigInteger('lesson_id');
            $table->string('title');
            $table->text('instruction');
            $table->text('description');
            $table->date('due_date');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->unsignedBigInteger('assessment_id');
            $table->longText('question');
            $table->string('type');
            $table->string('key_answer')->nullable();
            $table->integer('points');
            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id('option_id');
            $table->unsignedBigInteger('question_id');
            $table->string('option_text');
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->unsignedBigInteger('question_id');
            $table->string('lrn');
            $table->string('answer');
            $table->integer('score')->nullable();
            $table->timestamps();
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
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id('media_id');
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->unsignedBigInteger('uploader_id')->nullable();
            $table->string('type');
            $table->string('filename');
            $table->timestamps();
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
