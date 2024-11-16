<?php
/*
This class will insert demo content (courses, quizes, questions, lessons)
Do not change the order of functions in __construct, because quizes contains questions and
courses contains lessons and quizes.
*/
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Demo_Content')){
   return;
}
if (!trait_exists('RegisterCourseTags')){
   require_once ULP_PATH . 'classes/traits/RegisterCourseTags.php';
}

class Ulp_Demo_Content{

    use RegisterCourseTags;

    private $_courses = null;
    private $_lessons = null;
    private $_quizes = null;
    private $_questions = null;
    public function __construct(){
        /// prevent multiple insert in case of activate, deactivate, activate plugin
        if (!$this->_posts_exists()){
            $this->_create_lessons();
            $this->_create_questions();
            $this->_create_quizes();
            $this->_create_courses();
        }

        $this->createCourseTags();
    }
    private function _posts_exists(){
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE post_type='ulp_course';";
        $exists = $wpdb->get_var( $query );
        return $exists;
    }
    private function _create_courses(){


        $course_categ = 1;

        $this->_courses = [
            'course_1' => [
                  'post_data' => [
                          'post_title' => 'Ultimate Learning Pro Basics',
                          'post_content' => '
                                Thank you for purchasing Ultimate Learning Pro, this course is here to help you with the basics and also serves as an example of how a course is made.

This section here is the course description, use it to describe your course as best as you can so that your users get an idea if they should buy it or not.

If this is your first time using a LMS (Learning Management System) plugin, you can follow this course to get a basic idea of how it works. A course is a collection of Lessons and Quizzes grouped together in Sections. A quiz is a collection of questions.

This course is made of two sections, the first one has three lessons which will show you how to create a question, quiz and a lesson. The second section will teach you how to group them together and follow up with a short quiz.

Good luck!',
						  'post_excerpt' => 'Thank you for purchasing Ultimate Learning Pro, this course is here to help you with the basics and also serves as an example of how a course is made',
						  'post_category' => array($course_categ),
                          'post_type' => 'ulp_course'
                  ],
			      'feature_image' => 'featured_img_01.jpg',
                  'special_settings' => [
                          'ulp_course_duration' => 4,
                          'ulp_course_duration_type' => 'w',
                          'ulp_course_time_period_duration' => 3,
                          'ulp_course_time_period_duration_type' => 'w',
                          'ulp_course_prerequest_courses' => '',
                          'ulp_course_prerequest_reward_points' => 0,
                          'ulp_course_max_students' => 999,
                          'ulp_course_retake_course' => 3,
                          'ulp_course_featured' => 1,
                          'ulp_modules_order_items_by' => 'default',
                          'ulp_modules_order_items_type' => 'ASC',
                          'ulp_modules_per_page' => 5,
                          'ulp_course_payment' => 0,
                          'ulp_course_price' => 0,
                          'ulp_course_assessments' => 'final_quiz',
                          'ulp_course_assessments_passing_value' => 90,
                          'ulp_course_quizes_min_grade' => 50,
                          'ulp_post_reward_points' => 30,
                          'ulp_course_access_item_only_if_prev' => 0,
                          'ulp_course_difficulty' => '',

                          'ulp_course_coming_soon_enabled' => 1,
                          'ulp_course_coming_soon_message' => '<h2 class="ulp-text-aling-center">This course is Coming Soon</h2>
<h4 class="ulp-text-aling-center">Please save it into your WishList and follow the updates</h4>',
                          'ulp_course_coming_soon_end_time' => time() + 3 * 30 * 24 * 60 * 60,
                          'ulp_course_coming_soon_show_count_down' => 1,
                  ],
                  'modules' => [
                                  'Chapter 1' => [['post' => 'adding-a-new-lesson', 'post_type' => 'ulp_lesson'],
								  				  ['post' => 'creating-a-question', 'post_type' => 'ulp_lesson'],
												  ['post' => 'setting-up-a-quiz', 'post_type' => 'ulp_lesson']
												  ],
                                  'Chapter 2' => [['post' => 'creating-a-course', 'post_type' => 'ulp_lesson'],
								  				  ['post' => 'special-settings-intro', 'post_type' => 'ulp_lesson'],
												  ['post' => 'ulp-basics-quiz', 'post_type' => 'ulp_quiz']
												  ],
                  ],
                  'post_id' => null,
            ], /// end of course_1
          'course_2' => [
              'post_data' => [
                      'post_title' => 'Ultimate Learning Pro Advanced',
                      'post_content' => '<strong>Course Description</strong>

Already conquered the basics of <strong>Ultimate Learning Pro</strong>? Want to learn more and get more done? This course is the follow-up to <strong>"Ultimate Learning Pro Basics"</strong> and it will show you how to further set up your LMS.

If this is your first time using a LMS (Learning Management System) plugin, this course brings additional knowledge to what you have learned from the first course.

<strong>What will you learn?</strong>
<ol>
 	<li>By the end of this course you will have a better understanding of ULP;</li>
 	<li>You will know how to extend the plugin functionality by using magic features;</li>
</ol>
<strong>Target Audience:</strong>
<ol>
 	<li>Users that want to create an online learning system;</li>
 	<li>Developers which want to contribute to ULP;</li>
 	<li>Users that want to become an instructor in a LMS;</li>
</ol>
<strong>Course Breakdown</strong>

This course is made of one section with two lessons which will cover payment services and notifications.

These are followed up by two quizzes which will test your knowledge about ULP.

Good luck!',
					   'post_excerpt' => 'Already conquered the basics of Ultimate Learning Pro? Want to learn more and get more done? This course is the follow-up to "Ultimate Learning Pro Basics" and it will show you how to further set up your LMS.',
					   'post_category' => array($course_categ),
                       'post_type' => 'ulp_course'
              ],
			  'feature_image' => 'featured_img_02.jpg',
              'special_settings' => [
                      'ulp_course_duration' => 6,
                      'ulp_course_duration_type' => 'w',
                      'ulp_course_time_period_duration' => 5,
                      'ulp_course_time_period_duration_type' => 'w',
                      'ulp_course_prerequest_courses' => '',
                      'ulp_course_prerequest_reward_points' => 0,
                      'ulp_course_max_students' => 500,
                      'ulp_course_retake_course' => 1,
                      'ulp_course_featured' => 0,
                      'ulp_modules_order_items_by' => 'default',
                      'ulp_modules_order_items_type' => 'ASC',
                      'ulp_modules_per_page' => 5,
                      'ulp_course_payment' => 1,
                      'ulp_course_price' => 10,
                      'ulp_course_assessments' => 'quizes',
                      'ulp_course_assessments_passing_value' => 90,
                      'ulp_course_quizes_min_grade' => 70,
                      'ulp_post_reward_points' => 50,
                      'ulp_course_access_item_only_if_prev' => 0,
                      'ulp_course_difficulty' => '',
              ],
              'modules' => [
                              'Chapter 1' => [['post' => 'payment-services', 'post_type' => 'ulp_lesson'],
											  ['post' => 'activating-notifications', 'post_type' => 'ulp_lesson'],
											  ['post' => 'ulp-advanced-quiz-1', 'post_type' => 'ulp_quiz'],
											  ['post' => 'ulp-advanced-quiz-1-2', 'post_type' => 'ulp_quiz']
												  ],
              ],
              'post_id' => null,
            ] /// end of course_2
        ]; /// end of $this->_courses
		$this->_insert_posts_with_special_settings($this->_courses);
        /// save modules : courses have quizes and lessons
        require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
    		require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
    		$DbCoursesModulesUlp = new DbCoursesModulesUlp();
    		$DbModuleItems = new DbModuleItems();
        foreach ($this->_courses as $the_key => $array){
            $module_index = 1;
            foreach ($array ['modules'] as $module_name => $module_content){
                $module_id = $DbCoursesModulesUlp->saveModule(-1, $module_name, $array ['post_id'], $module_index, 1);

            	$item_order = 1;
				foreach ($module_content as $key=> $object){
				  if ($object ['post_type']=='ulp_quiz'){
					  $item_id= $this->_quizes [$object ['post']]['post_id'];
				  } else {
					  $item_id = $this->_lessons [$object ['post']]['post_id'];
				  }
                	$DbModuleItems->saveItem($module_id, $array ['post_id'], $item_id, $object ['post_type'], $item_order, 1);
                	$item_order++;
				}
                $module_index++;
            }
        }
    }
    private function _create_lessons(){
      $this->_lessons = [
          'adding-a-new-lesson' => [
                'post_data' => [
                        'post_title' => 'Adding a new Lesson',
                        'post_content' => '
                              In the upper menu, go to the <b>Lessons </b>tab, then click on the <b>Add new Lesson</b> button found here.

In the content area you will type in your lesson and once you are ready hit publish!

An additional section called Lesson Drip Content can be found below the content area <strong>only after activating</strong> the Magic Feature: Lesson Drip Content. This will allow you to release content at regular intervals by creating a release schedule for your content.',
						'post_type' => 'ulp_lesson'
                ],
              'special_settings' => [
                      'ulp_lesson_duration' => 45,
                      'ulp_lesson_duration_type' => 'm',
                      'ulp_lesson_preview' => 1,
                      'ulp_lesson_show_back_to_course_link' => 1,
                      'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_a
          'creating-a-question' => [
                'post_data' => [
                        'post_title' => 'Creating a Question',
                        'post_content' => '
                              In the upper menu, go to the <b>Questions</b> tab, then click on the <b>Add new Question</b> button found here. You can type your message here, then you have the option to select which <b>Type of Question</b> it is, you can select one from the drop-down menu.

Example: If  you select the type to be Multi Choice, then you can add as many answers as you want by clicking <b>Add new Option</b>, then at the bottom you can add the correct answer.

When you are done editing, simply click on the <b>Publish</b> button found on the right hand side menu.

You can also access the <b>Special Settings</b> page where you can edit how many Quiz Points the question is worth, add a Hint Message and a Question Explanation. When creating or editing a Question, on the right hand side menu you have the <strong>Special Settings</strong> button. We will cover this section later in the course.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                          'ulp_lesson_duration' => 30,
                          'ulp_lesson_duration_type' => 'm',
                          'ulp_lesson_preview' => 0,
                          'ulp_lesson_show_back_to_course_link' => 1,
                          'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_b
          'setting-up-a-quiz' => [
                'post_data' => [
                        'post_title' => 'Setting up a Quiz',
                        'post_content' => '
                              In the upper menu, go to the <b>Quizzes</b> tab, then click on the <b>Add new Quiz</b> button found here.

You can add content in the first section, or skip to the <b>Quiz Questions</b> section where you can add existing questions to your quiz. You can click on the + icon, drag and drop and search for specific questions.

When you are done editing, simply click on the <b>Publish</b> button found on the right hand side menu.

You can also access the <b>Special Settings</b> page where you have some useful options you can further tweak. We will cover this section later in the course.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                            'ulp_lesson_duration' => 30,
                            'ulp_lesson_duration_type' => 'm',
                            'ulp_lesson_preview' => 0,
                            'ulp_lesson_show_back_to_course_link' => 1,
                            'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_c
          'creating-a-course' => [
                'post_data' => [
                        'post_title' => 'Creating a Course',
                        'post_content' => '
                              A course is a collection of lessons and quizzes, a quiz is a collection of questions. Before making a course make sure you have everything else created to make things easier for yourself.

In the upper menu, go to the <b>Courses </b>tab, then click on the <b>Add new Course</b> button found here.

In the upper portion of the page you have a menu with useful links to help you customize your course, for example the <strong>Special Settings</strong> button.

After adding content in the first area, you can scroll to the <b>Course Sections</b>, by clicking the + sign, you create a new section. Enter a name for it and add courses or quizzes to it.

When adding elements to a section you can search for them, press +Add all to add the whole list, for a single element you can click the + button, double click or drag and drop to the left.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                              'ulp_lesson_duration' => 40,
                              'ulp_lesson_duration_type' => 'm',
                              'ulp_lesson_preview' => 0,
                              'ulp_lesson_show_back_to_course_link' => 1,
                              'ulp_post_reward_points' => 15,
                ],
                'post_id' => null,
          ], /// end of lesson_d
		  'special-settings-intro' => [
                'post_data' => [
                        'post_title' => 'Special Settings - Intro',
                        'post_content' => '
                              These are <strong>very important</strong> settings which bring a new level of customization to: Questions, Quizzes, Lessons and Courses.

You can access these settings by hovering over an existing item and clicking on <b>Special Settings</b> or when creating / editing one of the above items, at the top of the page and / or on the right hand side menu you have the <b>Special Settings </b>buttons.

They are different for each item, to learn more either access their respective pages or head over to the knowledge base and read about them there.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                              'ulp_lesson_duration' => 30,
                              'ulp_lesson_duration_type' => 'm',
                              'ulp_lesson_preview' => 0,
                              'ulp_lesson_show_back_to_course_link' => 1,
                              'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_d
		  'payment-services' => [
                'post_data' => [
                        'post_title' => 'Payment Services',
                        'post_content' => '
                              You can find a list of all the payment services by going in the upper menu and clicking on the <b>Payment Services </b>tab. Here you can click on which box you want to set up.

If a certain payment is grayed out it means that you must first navigate to the <b>Extensions</b> tab and activate the <b>Payment Integration</b> module from there.

Each payment type requires different settings in order for it to work.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                              'ulp_lesson_duration' => 30,
                              'ulp_lesson_duration_type' => 'm',
                              'ulp_lesson_preview' => 0,
                              'ulp_lesson_show_back_to_course_link' => 1,
                              'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_d
		  'activating-notifications' => [
                'post_data' => [
                        'post_title' => 'Activating Notifications',
                        'post_content' => '
                             In the upper menu, go to the <b>Notifications </b>tab, then click on the <b>Activate New Notification</b> button found here.

There are two main notification types based on destination: <b>Admin</b> and <b>Student </b>notifications. You can also decide if a notification affects all of the courses or just a specific one.

When typing your subject and message, make sure to use the <b>shortcodes</b> found on the right hand side of the page.

Additional settings can be found by navigating to the <strong>General Options -&gt; Notifications</strong> tab.

You can also activate the option for Pushover Notifications by going to the Magic Feature tab and setting this feature up. Afterwards when creating or editing a notification a new section called Pushover Notifications will appear.',
                        'post_type' => 'ulp_lesson'
                ],
                'special_settings' => [
                              'ulp_lesson_duration' => 30,
                              'ulp_lesson_duration_type' => 'm',
                              'ulp_lesson_preview' => 0,
                              'ulp_lesson_show_back_to_course_link' => 1,
                              'ulp_post_reward_points' => 10,
                ],
                'post_id' => null,
          ], /// end of lesson_d
      ]; /// end of $this->_lessons
      $this->_insert_posts_with_special_settings($this->_lessons);
    }
    private function _create_quizes(){
        $this->_quizes = [
            'ulp-basics-quiz' => [
                'post_data' => [
                    'post_title' => 'ULP Basics Quiz',
                    'post_content' => '<p>Hello, good job for making it this far.</p>
<br/>
<p>This quiz will ask you three questions of different types to see if you have attained basic knowledge of ULP.</p>
<br/>
<p><strong>Time</strong>: 30min</p>
<p><strong>Passing Grade</strong>: 50%</p>
<p><strong>Retake attempts</strong>: 10</p><br/>',
                    'post_type' => 'ulp_quiz'
                ],
                'special_settings' => [
                    'retake_limit' => 3,
    								'quiz_time' => 30,/// in minutes
    								'quiz_workflow' => 'default',
    								'enable_back_button' => 1,
    								'ulp_quiz_show_explanation' => 1,
    								'ulp_quiz_show_hint' => 1,
    								'ulp_quiz_grade_type' => 'percentage',
    								'ulp_quiz_grade_value' => 50,
    								'ulp_post_reward_points' => 50,
    								'ulp_quiz_display_questions_random' => 0,
    								'ulp_quiz_display_answers_random' => 0,
									'retake_limit' => 10,
                ],
                'post_id' => null,
                'questions' => [
                    'question_a',
                    'question_b',
                    'question_c',
                ],
            ],
            'ulp-advanced-quiz-1' => [
                'post_data' => [
                    'post_title' => 'ULP Advanced Quiz 1',
                    'post_content' => '<p>Hello, good job for making it this far.</p>
<br/>
<p>This quiz will ask you three questions of different types to see if you have attained basic knowledge of ULP.</p>
<br/>
<p><strong>Time</strong>: 30min</p>
<p><strong>Passing Grade</strong>: 70%</p>
<p><strong>Retake attempts</strong>: 3</p><br/>',
                    'post_type' => 'ulp_quiz'
                ],
                'special_settings' => [
                    'retake_limit' => 3,
                    'quiz_time' => 30,/// in minutes
                    'quiz_workflow' => 'default',
                    'enable_back_button' => 1,
                    'ulp_quiz_show_explanation' => 1,
                    'ulp_quiz_show_hint' => 1,
                    'ulp_quiz_grade_type' => 'percentage',
                    'ulp_quiz_grade_value' => 70,
                    'ulp_post_reward_points' => 20,
                    'ulp_quiz_display_questions_random' => 0,
                    'ulp_quiz_display_answers_random' => 0,
                ],
                'post_id' => null,
                'questions' => [
                    'question_d',
                    'question_f',
                ],
            ],
            'ulp-advanced-quiz-1-2' => [
                'post_data' => [
                    'post_title' => 'ULP Advanced Quiz 2',
                    'post_content' => '<p>Hello, good job for making it this far.</p>
<br/>
<p>This quiz will ask you three questions of different types to see if you have attained basic knowledge of ULP.</p>
<br/>
<p><strong>Time</strong>: 30min</p>
<p><strong>Passing Grade</strong>: 50%</p>
<p><strong>Retake attempts</strong>: 3</p><br/>',
                    'post_type' => 'ulp_quiz'
                ],
                'special_settings' => [
                    'retake_limit' => 3,
                    'quiz_time' => 30,/// in minutes
                    'quiz_workflow' => 'default',
                    'enable_back_button' => 1,
                    'ulp_quiz_show_explanation' => 1,
                    'ulp_quiz_show_hint' => 1,
                    'ulp_quiz_grade_type' => 'percentage',
                    'ulp_quiz_grade_value' => 50,
                    'ulp_post_reward_points' => 30,
                    'ulp_quiz_display_questions_random' => 0,
                    'ulp_quiz_display_answers_random' => 0,
                ],
                'post_id' => null,
                'questions' => [
                    'question_g',
                    'question_h',
                ],
                'post_id' => null,
            ],
        ];
        /// insert posts
        $this->_insert_posts_with_special_settings($this->_quizes);
        /// save quiz questions
        require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
        $DbQuizQuestions = new DbQuizQuestions();
        foreach ($this->_quizes as $the_key => $array){
            $module_index = 1;
            $item_order = 1;
            $quiz_id = $array ['post_id'];
            foreach ($array ['questions'] as $question_key){
                $question_id = $this->_questions [$question_key]['post_id'];
                $DbQuizQuestions->saveQuizQuestion($question_id, $quiz_id, $item_order, 1);
                $item_order++;
            }
        }
    }
    private function _create_questions(){
      $this->_questions = [
          'question_a' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'Which statement is correct about the Special Settings feature?',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 2, /// SingleChoice
                        'answers_single_answer_possible_values' => ['They bring a new level of customization.', 'They are a premium feature not available for free.', 'Every item has this option'],
                        'answers_single_answer_correct_value' => 'They bring a new level of customization.',
                        ///
                        'ulp_question_hint' => 'They bring a new level of customization.',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 2,
                ],
                'post_id' => null,
          ], /// end of _questions_a
          'question_b' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'Is the Lesson Drip Content feature available from the start?',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 4, /// True or False
                        'answer_value_for_bool' => '1',
                        ////
                        'ulp_question_hint' => 'True',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 2,
                ],
                'post_id' => null,
          ], /// end of question_b
          'question_c' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'A course is a collection of lessons and ___.',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 1, /// true or false
                        'answer_value' => 'quizzes',
                        ///
                        'ulp_question_hint' => 'quizzes',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 2,
                ],
                'post_id' => null,
          ], /// end of question_c
          'question_d' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'If a payment service is grayed out it means that?',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 3, /// fill in
                        'answers_multiple_answers_possible_values' => ['You must activate the payment integration module.', 'You must purchase this feature from our premium list.', 'First you navigate to the Extensions tab and locate the module.', 'This payment module is not available in your country.'],
						'answers_multiple_answers_correct_answers' => 'You must activate the payment integration module.,First you navigate to the Extensions tab and locate the module.',
                        ///
                        'ulp_question_hint' => 'You must activate the payment integration module.,First you navigate to the Extensions tab and locate the module.',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 5,
                ],
                'post_id' => null,
          ], /// end of question_d
          'question_f' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'Prove you are a human by sorting these numbers.

a=8',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 6, /// Sorting Answers
                        'answers_sorting_type' => ['-25', '-7', '4', 'a', '35', '42'],
                        ///
                        'ulp_question_hint' => 'No Hint for this question',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 5,
                ],
                'post_id' => null,
          ], /// end of question_f
          'question_g' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'Select the correct statement about notifications.',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 2, /// single choice
                        'answers_single_answer_possible_values' => ['You can activate pushover notifications from the Notifications tab.', 'There are no shortcodes available for notifications.', 'There are two main notification types.', 'Notifications can not affect just one course.'],
                        'answers_single_answer_correct_value' => 'There are two main notification types.',
                        ///
                        'ulp_question_hint' => 'There are two main notification types',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 3,
                ],
                'post_id' => null,
          ], /// end of question_g
          'question_h' => [
                'post_data' => [
                        'post_title' => '',
                        'post_content' => 'Is Ultimate Learning Pro the best LMS plugin?',
                        'post_type' => 'ulp_question'
                ],
                'special_settings' => [
                        'answer_type' => 2, /// single choice
                        'answers_single_answer_possible_values' => ['Yes', 'No'],
						'answers_single_answer_correct_value' => 'Yes',
                        ///
                        'ulp_question_hint' => 'Yes',
                        'ulp_question_explanation' => 'Check the KnowledgeBase',
                        'ulp_question_points' => 3,
                ],
                'post_id' => null,
          ], /// end of question_h
      ]; /// end of $this->_questions
      $this->_insert_posts_with_special_settings($this->_questions);
    }
    private function _insert_posts_with_special_settings(&$var=[]){
        foreach ($var as $the_key => $array){
            /// each post will be public
            $array['post_data']['post_status'] = 'publish';
            /// insert post
            $post_id = wp_insert_post($array ['post_data']);
            $var [$the_key]['post_id'] = $post_id;

      			if($array ['post_data']['post_type'] == 'ulp_course'){
      				$this->set_featured_image(ULP_URL.'assets/images/'.$array ['feature_image'] ,$post_id, '');
      			}

            /// save special settings
            foreach ($array ['special_settings'] as $key => $value){
                update_post_meta($post_id, $key, $value);
            }
        }
    }


  public function createCourseTags()
  {
      if ($this->tagsAlreadyExists()){
         return;
      }

      /// register taxonomy
      $this->registerTags();

      $tags = [
          [
              'slug' => 'new',
              'label' => esc_html__('New', 'ulp'),
              'description' => '',
              'color' => '#0a9fd8',
          ],
          [
              'slug' => 'best-seller',
              'label' => esc_html__('Best Seller', 'ulp'),
              'description' => '',
              'color' => '#f8ba01',
          ],
          [
              'slug' => 'highest-rated',
              'label' => esc_html__('Highest Rated', 'ulp'),
              'description' => '',
              'color' => '#f1505b',
          ],
          [
              'slug' => 'trending',
              'label' => esc_html__('Trending', 'ulp'),
              'description' => '',
              'color' => '#0bb586',
          ],
      ];
      $DbCourseTags = new \Indeed\Ulp\Db\DbCourseTags();

      foreach ($tags as $array){
          $DbCourseTags->save($array);
      }
  }

  private function tagsAlreadyExists()
  {
      global $wpdb;
      $query = "SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy='course_tags' ORDER BY term_id LIMIT 1;";
      $data = $wpdb->get_var($query);
      return $data;
  }

	private function set_featured_image($file, $post_id, $desc){
    if ( !function_exists( 'download_url' ) ){
      require_once ABSPATH . 'wp-admin/includes/admin.php';
    }
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		if ( ! $matches ) {
			 return new WP_Error( 'image_sideload_failed', esc_html__( 'Invalid image URL' ) );
		}

		$file_array = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $file );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}
		return set_post_thumbnail( $post_id, $id );

	}
}
