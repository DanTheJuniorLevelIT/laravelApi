<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        // Insert Module 1: Personal Development and Self-Awareness
        $module1Id = DB::table('modules')->insertGetId([
            'classid' => 76, // Unique class ID
            'title' => 'Personal Development and Self-Awareness',
            'description' => 'This module focuses on helping students understand themselves better, recognizing their strengths and weaknesses, and developing personal goals.',
            'date' => '2024-12-12',
        ]);

        // Lessons for Module 1
        $module1Lessons = [
            [
                'module_id' => $module1Id,
                'topic_title' => 'Discovering Your Strengths and Weaknesses',
                'lesson' => 'This lesson encourages students to reflect on their unique qualities, identifying their strengths and areas where they can improve.',
                'handout' => null,
                'file' => null,
            ],
            [
                'module_id' => $module1Id,
                'topic_title' => 'Communicating Effectively',
                'lesson' => 'This lesson introduces students to the different aspects of effective communication, with a focus on verbal and nonverbal communication, active listening, and empathy.',
                'handout' => null,
                'file' => null,
            ],
            [
                'module_id' => $module1Id,
                'topic_title' => 'Working Together',
                'lesson' => 'This lesson introduces students to the principles of teamwork and collaboration, emphasizing how working together can lead to greater success.',
                'handout' => null,
                'file' => null,
            ],
        ];

        // Insert Module 1 Lessons
        foreach ($module1Lessons as $lesson) {
            $lessonId = DB::table('lessons')->insertGetId($lesson);

            // Dynamically add assessments for each lesson
            $this->addAssessments($lessonId, $lesson['topic_title']);
        }

        // Insert Module 2: Basic Life Skills
        $module2Id = DB::table('modules')->insertGetId([
            'classid' => 76, // Unique class ID
            'title' => 'Basic Life Skills',
            'description' => 'This module focuses on essential life skills that students need to navigate daily life effectively.',
            'date' => '2024-12-19',
        ]);

        // Lessons for Module 2
        $module2Lessons = [
            [
                'module_id' => $module2Id,
                'topic_title' => 'Taking Care of Yourself',
                'lesson' => 'This lesson emphasizes the importance of personal hygiene and healthy habits, including proper nutrition, exercise, and sleep.',
                'handout' => null,
                'file' => null,
            ],
            [
                'module_id' => $module2Id,
                'topic_title' => 'Discovering Your Interests and Skills',
                'lesson' => 'This lesson encourages students to reflect on their personal interests, talents, and skills.',
                'handout' => null,
                'file' => null,
            ],
        ];

        // Insert Module 2 Lessons
        foreach ($module2Lessons as $lesson) {
            $lessonId = DB::table('lessons')->insertGetId($lesson);

            // Dynamically add assessments for each lesson
            $this->addAssessments($lessonId, $lesson['topic_title']);
        }
    }

    private function addAssessments($lessonId, $topicTitle)
    {
        $assessmentsData = [
            'Discovering Your Strengths and Weaknesses' => [
                [
                    'title' => 'Self-Reflection Activity',
                    'instruction' => 'Reflect on your strengths and weaknesses.',
                    'description' => 'Assessment for identifying strengths and areas for improvement.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'Self-esteem is about comparing yourself to others.',
                            'type' => 'true-false',
                            'key_answer' => 'false',
                            'points' => 2
                        ],
                        [
                            'question' => 'It is important to focus on your weaknesses to improve yourself.',
                            'type' => 'true-false',
                            'key_answer' => 'false',
                            'points' => 2
                        ],
                        [
                            'question' => 'Setting goals can help you stay motivated and achieve your dreams.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                        [
                            'question' => 'Realistic goals are challenging but achievable.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                        [
                            'question' => 'Achieving your goals requires effort, planning, and perseverance.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                    ],
                ],
                [
                    'title' => 'Self-Reflection Activity pt',
                    'instruction' => 'Reflect on your strengths and weaknesses.',
                    'description' => 'Assessment for identifying strengths and areas for improvement.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'The belief in your own abilities and worth.',
                            'type' => 'identification',
                            'key_answer' => 'Self-esteem',
                            'points' => 2
                        ],
                        [
                            'question' => 'The feeling of being sure of yourself and your abilities.',
                            'type' => 'identification',
                            'key_answer' => 'Self-confidence',
                            'points' => 2
                        ],
                        [
                            'question' => 'A desired outcome or achievement that you aim for.',
                            'type' => 'identification',
                            'key_answer' => 'Goal',
                            'points' => 2
                        ],
                        [
                            'question' => 'A goal that is challenging but achievable.',
                            'type' => 'identification',
                            'key_answer' => 'Realistic goal',
                            'points' => 2
                        ],
                        [
                            'question' => 'The process of working towards achieving a goal.',
                            'type' => 'identification',
                            'key_answer' => 'Goal setting',
                            'points' => 2
                        ],
                    ],
                ],
            ],
            'Communicating Effectively' => [
                [
                    'title' => 'Communication Skills Quiz',
                    'instruction' => 'Answer the following questions.',
                    'description' => 'Assessment for evaluating effective communication skills.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'Effective communication is a one-way process. ',
                            'type' => 'true-false',
                            'key_answer' => 'false',
                            'points' => 2
                        ],
                        [
                            'question' => 'Active listening is a passive activity.',
                            'type' => 'true-false',
                            'key_answer' => 'false',
                            'points' => 2
                        ],
                        [
                            'question' => 'Nonverbal cues can convey important information.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                        [
                            'question' => 'It is important to be aware of your own communication style.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                        [
                            'question' => 'Effective communication is essential for building positive relationships.',
                            'type' => 'true-false',
                            'key_answer' => 'true',
                            'points' => 2
                        ],
                        [
                            'question' => 'Effective communication is essential for building positive relationships.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 10
                        ],
                        [
                            'question' => 'Effective communication is essential for building positive relationships.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 10
                        ],
                    ],
                ],
            ],
            'Working Together' => [
                [
                    'title' => 'Teamwork Skills Assessment',
                    'instruction' => 'Complete the questions below.',
                    'description' => 'Assessment for understanding principles of teamwork.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'The process of working together towards a common goal. ',
                            'type' => 'identification',
                            'key_answer' => 'Teamwork',
                            'points' => 2,
                        ],
                        [
                            'question' => 'The process of sharing ideas, information, and responsibilities?',
                            'type' => 'identification',
                            'key_answer' => 'Collaboration',
                            'points' => 2,
                        ],
                        [
                            'question' => 'A disagreement or clash of opinions. ',
                            'type' => 'identification',
                            'key_answer' => 'Conflict',
                            'points' => 2,
                        ],
                        [
                            'question' => 'A solution that satisfies everyone involved',
                            'type' => 'identification',
                            'key_answer' => 'Compromise',
                            'points' => 2,
                        ],
                        [
                            'question' => 'The ability to work effectively with others. ',
                            'type' => 'identification',
                            'key_answer' => 'Teamwork skills',
                            'points' => 2,
                        ],
                        [
                            'question' => 'Describe three benefits of teamwork.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 15
                        ],
                        [
                            'question' => 'Explain the importance of communication and cooperation in successful teamwork.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 5
                        ],
                    ],
                ],
            ],
            'Taking Care of Yourself' => [
                [
                    'title' => 'Healthy Habits Quiz',
                    'instruction' => 'Answer the following questions.',
                    'description' => 'Assessment for understanding personal hygiene and healthy habits.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'Which of the following is NOT a key aspect of personal hygiene?',
                            'type' => 'multiple-choice',
                            'key_answer' => 'Staying up late',
                            'points' => 2,
                            'options' => ['Bathing regularly', 'Brushing your teeth', 'Washing your hands', 'Staying up late'],
                        ],
                        [
                            'question' => 'What is the importance of a balanced diet?',
                            'type' => 'multiple-choice',
                            'key_answer' => 'All of the above',
                            'points' => 2,
                            'options' => ['It provides your body with the nutrients it needs.', 'It helps you maintain a healthy weight.', 'It boosts your energy levels.', 'All of the above'],
                        ],
                        [
                            'question' => 'How much sleep do adults need each night?',
                            'type' => 'multiple-choice',
                            'key_answer' => '6-8 hours',
                            'points' => 2,
                            'options' => ['4-6 hours', '6-8 hours', '8-10 hours', '10-12 hours'],
                        ],
                        [
                            'question' => 'What are the benefits of regular exercise?',
                            'type' => 'multiple-choice',
                            'key_answer' => 'All of the above',
                            'points' => 2,
                            'options' => ['It improves your physical health.', 'It reduces stress and anxiety.', 'It boosts your mood.', 'All of the above'],
                        ],
                        [
                            'question' => 'How can you make informed choices about your health?',
                            'type' => 'multiple-choice',
                            'key_answer' => 'All of the above',
                            'points' => 2,
                            'options' => ['By talking to your doctor', 'By reading reliable sources of information', 'By listening to your body', 'All of the above'],
                        ],
                    ],
                ],
            ],
            'Discovering Your Interests and Skills' => [
                [
                    'title' => 'Career Interest Survey',
                    'instruction' => 'Complete the survey.',
                    'description' => 'Assessment for identifying interests and career paths.',
                    'due_date' => now()->addDays(7),
                    'available' => true,
                    'questions' => [
                        [
                            'question' => 'Describe three interests or skills that you have and how they could be relevant to a career choice.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 15,
                        ],
                        [
                            'question' => 'Explain the importance of career planning and how it can help you achieve your career goals.',
                            'type' => 'Essay',
                            'key_answer' => null,
                            'points' => 15,
                        ],
                    ],
                ],
            ],
        ];

        if (isset($assessmentsData[$topicTitle])) {
            foreach ($assessmentsData[$topicTitle] as $assessment) {
                $assessmentId = DB::table('assessments')->insertGetId([
                    'lesson_id' => $lessonId,
                    'title' => $assessment['title'],
                    'instruction' => $assessment['instruction'],
                    'description' => $assessment['description'],
                    'due_date' => $assessment['due_date'],
                    'available' => $assessment['available'],
                ]);

                foreach ($assessment['questions'] as $question) {
                    $questionId = DB::table('questions')->insertGetId([
                        'assessment_id' => $assessmentId,
                        'question' => $question['question'],
                        'type' => $question['type'],
                        'key_answer' => $question['key_answer'],
                        'points' => $question['points'],
                    ]);

                    if (isset($question['options'])) {
                        foreach ($question['options'] as $option) {
                            DB::table('options')->insert([
                                'question_id' => $questionId,
                                'option_text' => $option,
                            ]);
                        }
                    }
                }
            }
        }
    }
}