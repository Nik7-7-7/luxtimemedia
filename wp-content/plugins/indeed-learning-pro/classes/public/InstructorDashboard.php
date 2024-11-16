<?php
namespace Indeed\Ulp\PublicSection;

class InstructorDashboard
{
    private $currentTab   = '';
    private $uid          = 0;
    private $currentUri   = '';
    private $baseUri      = '';
    private $tabs         = [];
    private $perPage      = 5;

    public function __construct()
    {
        wp_enqueue_style('ulp_sweet_alert_css', ULP_URL . 'assets/css/sweetalert.css', array(), '3.9' );
        wp_enqueue_script('ulp_sweet_alert', ULP_URL . 'assets/js/sweetalert.js', array('jquery'), '3.9' );
        $this->currentTab = isset($_GET['ulp_tab']) ? sanitize_text_field($_GET['ulp_tab']) : 'overview';
        $this->uid = ulp_get_current_user();
        $this->setUri();
        $this->setNavMenu();
    }

    private function userCanView()
    {
        return \DbUlp::isUserInstructor($this->uid);
    }

    public function getOutput()
    {
        if (!$this->userCanView()){
            return;
        }

        $content = '';
        $postType = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        $this->currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

        switch ($this->currentTab){
            case 'overview':
              $content = $this->overview();
              break;
            case 'add-edit':
              switch ($postType){
                  case 'ulp_course':
                    $content = $this->addEditCourses();
                    break;
                  case 'ulp_quiz':
                    $content = $this->addEditQuizes();
                    break;
                  case 'ulp_question':
                    $content = $this->addEditQuestions();
                    break;
                  case 'ulp_lesson':
                    $content = $this->addEditLessons();
                    break;
                  case 'ulp_announcement':
                    $content = $this->addEditAnnouncements();
                    break;
                  case 'ulp_qanda':
                    $content = $this->addEditQanda();
                    break;
              }
              break;
            case 'manage':
              switch ($postType){
                  case 'ulp_course':
                    $content = $this->manageCourses();
                    break;
                  case 'ulp_quiz':
                    $content = $this->manageQuizes();
                    break;
                  case 'ulp_question':
                    $content = $this->manageQuestions();
                    break;
                  case 'ulp_lesson':
                    $content = $this->manageLessons();
                    break;
                  case 'ulp_announcement':
                    $content = $this->manageAnnouncements();
                    break;
                  case 'ulp_qanda':
                    $content = $this->manageQanda();
                    break;

              }
              break;
            case 'special-settings':
              switch ($postType){
                  case 'ulp_course':
                    $content = $this->coursesSpecialSettings();
                    break;
                  case 'ulp_quiz':
                    $content = $this->quizesSpecialSettings();
                    break;
                  case 'ulp_question':
                    $content = $this->questionsSpecialSettings();
                    break;
                  case 'ulp_lesson':
                    $content = $this->lessonsSpecialSettings();
                    break;
              }
              break;
            case 'settings':
              $content = $this->settings();
              break;
            case 'list-students':
              $content = $this->listStudentsByCourse();
              break;
        }
        $cookie = ulpSetCookieViaJS('ulp_ref_value', $this->currentUri, time()+3600);
        return $cookie . $this->header() . $content . $this->footer();
    }

    private function setUri()
    {
        $this->currentUri = ULP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $this->baseUri = $this->currentUri;
        $removeGetParams = ['tab', 'postId', 'type', 'courseId', 'ulp_page'];
        foreach ($removeGetParams as $key){
          if (!empty($_GET[$key])){
            $this->baseUri = remove_query_arg($key, $this->baseUri);
          }
        }
    }

    private function setNavMenu()
    {
        $this->tabs = [
                          [
                              'base_slug'      => 'overview',
                              'tab'            => 'overview',
                              'type'           => '',
                              'label'          => esc_html__('Overview', 'ulp'),
                          ],
                          [
                              'base_slug'      => 'ulp_course',
                              'tab'            => 'manage',
                              'type'           => 'ulp_course',
                              'label'          => esc_html__('Courses', 'ulp'),
                          ],
                          [
                              'base_slug'      => 'ulp_lesson',
                              'tab'            => 'manage',
                              'type'           => 'ulp_lesson',
                              'label'          => esc_html__('Lessons', 'ulp'),
                          ],
                          [
                              'base_slug'      => 'ulp_quiz',
                              'tab'            => 'manage',
                              'type'           => 'ulp_quiz',
                              'label'          => esc_html__('Quizzes', 'ulp'),
                          ],
                          [
                              'base_slug'      => 'ulp_question',
                              'tab'            => 'manage',
                              'type'           => 'ulp_question',
                              'label'          => esc_html__('Questions', 'ulp'),
                          ],
                          [
                            'base_slug'      => 'settings',
                            'tab'            => 'settings',
                            'type'           => '',
                            'label'          => esc_html__('Settings', 'ulp'),
                          ]
        ];
    }

    private function header()
    {
        wp_enqueue_media();
        wp_enqueue_style('ulp_jquery_ui', ULP_URL . 'assets/css/jquery-ui.min.css', array(), '3.9' );
        wp_enqueue_style('ulp_ui_multiselect_css', ULP_URL . 'assets/css/ui.multiselect.css', array(), '3.9' );

        $location = locate_template('ultimate-learning-pro/instructor_dashboard/header.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/header.php' : $location;
        $roles = \DbUlp::get_user_roles($this->uid);
        $role = esc_html__('User', 'ulp');
        if (isset($roles['ulp_instructor'])){
            $role = esc_html__('Junior Instructor', 'ulp');
        } else if (isset($roles['ulp_instructor_senior'])){
            $role = esc_html__('Senior Instructor', 'ulp');
        } else if (isset($roles['administrator'])){
            $role = esc_html__('Administrator', 'ulp');
        } else if (isset($roles['ulp_instructor-pending'])){
            $role = esc_html__('Instructor Pending', 'ulp');
        }

        $currentTab = $this->currentTab;
        if (isset($_GET['type'])){
            $currentTab = sanitize_text_field($_GET['type']);
        }

        $data = [
            'tabs'          => $this->tabs,
            'baseUri'       => $this->baseUri,
            'userEmail'     => \DbUlp::get_user_col_value($this->uid, 'user_email'),
            'avatar'        => \DbUlp::getAuthorImage($this->uid),
            'fullName'      => \DbUlp::get_full_name($this->uid),
            'role'          => $role,
            'addNewCource'  => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_course'], $this->baseUri ),
            'newStudents'   => \DbUlp::getCountOfNewStudents($this->uid),
            'newQuestions'  => \DbUlp::getCountOfNewQuestions($this->uid),
            'currentTab'    => $currentTab,
        ];

        \DbUlp::updateInstructorHasViewTheDashboard($this->uid);

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function footer()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/footer.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/footer.php' : $location;
        $data = [];
        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function settings()
    {
        $error_mess = $confirm_mess = false;
        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new \UlpInstructor();
        $data = [
             'first_name'               => get_user_meta($this->uid, 'first_name', TRUE),
             'last_name'                => get_user_meta($this->uid, 'last_name', TRUE),
             'avatar'                   => get_user_meta($this->uid, 'ulp_avatar', TRUE),
             'user_email'               => \DbUlp::get_user_col_value($this->uid, 'user_email'),
             'description'              => get_user_meta( $this->uid, 'description', true ),
        ];

        if (isset($_POST['update_user_data']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            if (!empty($_POST['first_name']) && $_POST['first_name']!=$data ['first_name']){
                $_POST['first_name'] = sanitize_text_field( $_POST['first_name'] );
                update_user_meta($this->uid, 'first_name', sanitize_text_field($_POST['first_name']));
            }
            if (!empty($_POST['last_name']) && $_POST['last_name']!=$data ['last_name']){
                $_POST['last_name'] = sanitize_text_field( $_POST['last_name'] );
                update_user_meta($this->uid, 'last_name', sanitize_text_field($_POST['last_name']));
            }
            if (!empty($_POST['user_email']) && $_POST['user_email']!=$data ['user_email']){
                $_POST['user_email'] = sanitize_text_field( $_POST['user_email'] );
                wp_update_user( array( 'ID' => $this->uid, 'user_email' => sanitize_email($_POST['user_email']) ) );
            }
        }

        if (!empty($_POST['update_user_info']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            update_user_meta($this->uid, 'description', sanitize_textarea_field($_POST['description']) );
            $UlpInstructor->setSingleInstructorPageSettings($this->uid, ulp_sanitize_array($_POST) );
        }

        if (isset($_POST['update_user_password']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
          if(!empty($_POST['old_pass']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])){
            $check_pass = false;
            $current_user = wp_get_current_user();
            $_POST['old_pass'] = sanitize_text_field( $_POST['old_pass'] );
            $_POST['pass1'] = sanitize_text_field( $_POST['pass1'] );
            $_POST['pass2'] = sanitize_text_field( $_POST['pass2'] );

            //CHECK OLD PASSWORD
            require_once( ABSPATH . 'wp-includes/class-phpass.php' );
            $wp_hasher = new \PasswordHash( 8, TRUE );
            if ( $wp_hasher->CheckPassword( $_POST['old_pass'], $current_user->data->user_pass ) ) {
              $check_pass = true;
            }

            if(!$check_pass){
               $error_mess = esc_html__( 'Old password incorrect!', 'ulp' );
            }else{
              //CHECK PASSWORD CONFIRMATION
              if ($_POST['pass1'] == $_POST['pass2']){
                 wp_set_password(sanitize_text_field($_POST['pass1']),$this->uid);
                 $confirm_mess = esc_html__( 'Confirmation password incorrect!', 'ulp' );

              }else{
                $error_mess = esc_html__( 'Confirmation password incorrect!', 'ulp' );
              }
            }
          }else{
              $error_mess = esc_html__( 'Please Complete all required fields!', 'ulp' );
          }
         }
        if (isset($_POST['update_user_avatar']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
           if(!empty($_POST['ulp_avatar'])){
              $_POST['ulp_avatar'] = sanitize_text_field( $_POST['ulp_avatar'] );
              update_user_meta($this->uid, 'ulp_avatar', sanitize_textarea_field($_POST['ulp_avatar']));
           }
        }
        if (isset($_POST['update_user_notf']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            $UlpInstructor->setInstructorNotificationSettings($this->uid, ulp_sanitize_array($_POST));
        }

        $data = [
             'first_name'               => get_user_meta($this->uid, 'first_name', TRUE),
             'last_name'                => get_user_meta($this->uid, 'last_name', TRUE),
             'avatar'                   => get_user_meta($this->uid, 'ulp_avatar', TRUE),
             'user_email'               => \DbUlp::get_user_col_value($this->uid, 'user_email'),
             'description'              => get_user_meta( $this->uid, 'description', true ),
             'user_id'                  => $this->uid,
             'username'                 => get_user_meta($this->uid, 'nickname', TRUE),
             'error_mess'               => $error_mess,
             'confirm_mess'             => $confirm_mess,
             'instructorPageSettings'   => $UlpInstructor->getSingleInstructorPageSettings($this->uid),
             'tabs'                     => [
                                    'ulp-profile-basic-section'             => esc_html__('Basic informations', 'ulp'),
                                    'ulp-profile-instructor-info-section'   => esc_html__('Privacy', 'ulp'),
                                    'ulp-profile-password-section'          => esc_html__('Reset password', 'ulp'),
                                    'ulp-profile-avatar-section'            => esc_html__('Profile Photo', 'ulp'),
                                    'ulp-profile-notifications-section'     => esc_html__('Notifications', 'ulp'),
                                    //'ulp-public-profile-section'     => esc_html__('Check Public Profile', 'ulp'),
             ],
             'instructorNotfsettings'   => $UlpInstructor->getInstructorNotificationSettings($this->uid)
        ];

        $location = locate_template('ultimate-learning-pro/instructor_dashboard/settings.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/settings.php' : $location;

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function overview()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/overview.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/overview.php' : $location;
        $data = [
            'addNewAnnouncementLink'      => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_announcement'], $this->baseUri),
            'lastStudents'                => \DbUlp::getLastStudentsForInstructor($this->uid, 5),
            'lastAnnouncementComments'    => \DbUlp::getLastAnnouncementCommentsForInstructor($this->uid, 5),
            'lastQandAEntries'            => \DbUlp::getLastQandAOrQandAComments($this->uid, 5),
            'courses'                     => \DbUlp::get_courses_for_instructor($this->uid, 3),
        ];
        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }


    private function manageCourses()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-courses.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-courses.php' : $location;

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if ( !empty($_POST['submit']) && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
            && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){
            $_POST['post_title'] = sanitize_text_field( $_POST['post_title'] );
            $saveData = [
                    'ID'              => isset($_POST['ID']) ? sanitize_text_field($_POST['ID']) : 0,
                    'post_content'    => sanitize_textarea_field($_POST['post_content']),
                    'post_title'      => sanitize_textarea_field($_POST['post_title']),
                    'post_type'       => 'ulp_course',
                    'post_author'     => $this->uid,
            ];
            if (isset($_POST['post_status'])){
                $_POST['post_status'] = sanitize_text_field( $_POST['post_status'] );
                $saveData['post_status'] = sanitize_text_field($_POST['post_status']);
            }
            $postId = $postsDb->save($saveData);
            \DbUlp::saveCoursesModules($postId, ulp_sanitize_array($_POST));
            if ($saveData['ID']){
                do_action('ulp_public_instructor_has_update_course', $this->uid, $postId);
            } else {
                do_action('ulp_public_instructor_has_create_course', $this->uid, $postId);
            }
            /// initiate special settings if the posts has been created
            $post_got_special_settings = \DbUlp::does_post_meta_exists($postId, 'ulp_course_assessments');
            if ($post_got_special_settings===FALSE){
                $defaults = \DbUlp::getPostMetaGroup($postId, 'course_special_settings', TRUE);
                \DbUlp::update_post_meta_group('course_special_settings', $postId, $defaults );
            }

            /// save categories && tags
            $tags = empty($_POST['tags']) ? [] : $_POST['tags'];
            $tags = ulp_force_array_element_to_int($tags);
            wp_set_post_terms($postId, $tags, 'course_tags', false);
            $cats = empty($_POST['categories']) ? [] : $_POST['categories'];
            $cats = ulp_force_array_element_to_int($cats);
            wp_set_post_terms($postId, $cats, 'ulp_course_categories', false);

            if (!empty($_POST['ulp_feat_image_input'])){
                $_POST['ulp_feat_image_input'] = sanitize_text_field( $_POST['ulp_feat_image_input'] );
                \DbUlp::saveAttachmentToPost($postId, sanitize_textarea_field($_POST['ulp_feat_image_input']) );
            }
        } else if (!empty($_POST['save_special_settings']) && $this->uid && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            $this->saveSpecialSettings('ulp_course', ulp_sanitize_array( $_POST ) );
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $limit = $this->perPage;
        $offset = 0;
        $totalItems = $postsDb->select('ulp_course', $limit, $offset, $this->uid, true);
        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;
        require_once ULP_PATH . 'classes/IndeedPagination.class.php';
        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'addNewLink'            => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_course'], $this->baseUri),
            'items'                 => $postsDb->select('ulp_course', $limit, $offset, $this->uid, false),
            'pagination'            => $pagination->output(),
            'currency'              => ulp_currency(),
            'show_announcements'    => get_option('ulp_announcements_enabled'),
            'show_qanda'            => get_option('ulp_qanda_enabled'),
            'baseUri'               => $this->baseUri,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function manageQuizes()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-quizes.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-quizes.php' : $location;

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if (!empty($_POST['submit']) && $this->uid && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
            && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){
            $_POST['post_title'] = sanitize_text_field( $_POST['post_title'] );
            $saveData = [
                'ID'              => isset($_POST['ID']) ? sanitize_text_field( $_POST['ID'] ) : 0,
                'post_content'    => sanitize_textarea_field($_POST['post_content']),
                'post_type'       => 'ulp_quiz',
                'post_status'     => 'publish',
                'post_author'     => $this->uid,
                'post_title'      => isset($_POST['post_title']) ? sanitize_textarea_field($_POST['post_title']) : '',
            ];
            if (isset($_POST['post_status'])){
                $_POST['post_status'] = sanitize_text_field( $_POST['post_status'] );
                $saveData['post_status'] = sanitize_text_field($_POST['post_status']);
            }
            $postId = $postsDb->save($saveData);
            \DbUlp::saveQuizQuestions($postId, ulp_sanitize_array( $_POST ) );

            /// save categories
            $cats = empty($_POST['categories']) ? [] : $_POST['categories'];
            $cats = ulp_force_array_element_to_int($cats);
            wp_set_post_terms($postId, $cats, 'ulp_quiz_categories', false);

            if (!empty($_POST['ulp_feat_image_input'])){
                $_POST['ulp_feat_image_input'] = sanitize_text_field( $_POST['ulp_feat_image_input'] );
                \DbUlp::saveAttachmentToPost($postId, sanitize_text_field($_POST['ulp_feat_image_input']) );
            }

        } else if (!empty($_POST['save_special_settings']) && $this->uid && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            $this->saveSpecialSettings('ulp_quiz', ulp_sanitize_array( $_POST ) );
        }

        $limit = $this->perPage;
        $offset = 0;
        $totalItems = $postsDb->select('ulp_quiz', $limit, $offset, $this->uid, true);
        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;
        require_once ULP_PATH . 'classes/IndeedPagination.class.php';
        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'addNewLink'            => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_quiz'], $this->baseUri),
            'items'                 => $postsDb->select('ulp_quiz', $limit, $offset, $this->uid, false),
            'pagination'            => $pagination->output(),
            'baseUri'               => $this->baseUri,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function manageLessons()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-lessons.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-lessons.php' : $location;

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if (!empty($_POST['submit']) && $this->uid && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
            && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){
            $_POST['post_title'] = sanitize_text_field( $_POST['post_title'] );
            $saveData = [
              'ID'              => isset($_POST['ID']) ? sanitize_text_field($_POST['ID']) : 0,
              'post_content'    => sanitize_textarea_field($_POST['post_content']),
              'post_type'       => 'ulp_lesson',
              'post_author'     => $this->uid,
              'post_title'      => isset($_POST['post_title']) ? sanitize_textarea_field($_POST['post_title']) : '',
            ];
            if (isset($_POST['post_status'])){
                $saveData['post_status'] = sanitize_text_field($_POST['post_status']);
            }
            $postId = $postsDb->save($saveData);

            \DbUlp::update_post_meta_group('drip_content', $postId, ulp_sanitize_array( $_POST ) );

            /// save categories
            $cats = empty($_POST['categories']) ? [] : $_POST['categories'];
            $cats = ulp_force_array_element_to_int($cats);
            wp_set_post_terms($postId, $cats, 'ulp_lesson_categories', false);

            if (!empty($_POST['ulp_feat_image_input'])){
                \DbUlp::saveAttachmentToPost($postId, sanitize_text_field($_POST['ulp_feat_image_input']) );
            }

            /// save video settings
            \DbUlp::update_post_meta_group( 'video_lesson_settings', $postId, ulp_sanitize_array($_POST) );

        } else if (!empty($_POST['save_special_settings']) && $this->uid && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            $this->saveSpecialSettings('ulp_lesson', ulp_sanitize_array( $_POST ) );
        }
        $limit = $this->perPage;
        $offset = 0;
        $totalItems = $postsDb->select('ulp_lesson', $limit, $offset, $this->uid, true);
        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;

        require_once ULP_PATH . 'classes/IndeedPagination.class.php';

        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'addNewLink'            => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_lesson'], $this->baseUri),
            'items'                 => $postsDb->select('ulp_lesson', $limit, $offset, $this->uid, false),
            'pagination'            => $pagination->output(),
            'baseUri'               => $this->baseUri,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function manageQuestions()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-questions.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-questions.php' : $location;

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if (isset($_POST['submit']) && $this->uid && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
            && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){
          $saveData = [
            'ID'              => isset($_POST['ID']) ? sanitize_text_field($_POST['ID']) : 0,
            'post_content'    => sanitize_textarea_field($_POST['post_content']),
            'post_type'       => 'ulp_question',
            'post_author'     => $this->uid,
          ];
          if (isset($_POST['post_status'])){
              $saveData['post_status'] = sanitize_text_field($_POST['post_status']);
          }
          $postId = $postsDb->save($saveData);

          $meta_keys = \DbUlp::getPostMetaGroup($postId, 'answer_settings', false);
          foreach ($meta_keys as $type=>$value){
              if (isset($_POST[$type])){
                update_post_meta($postId, $type, ulp_sanitize_array($_POST[$type]) );
              }
          }

          /// save categories
          $cats = empty($_POST['categories']) ? [] : $_POST['categories'];
          $cats = ulp_force_array_element_to_int($cats);
          wp_set_post_terms($postId, $cats, 'ulp_question_categories', false);

          /*
          if (!empty($_POST['ulp_feat_image_input'])){
              \DbUlp::saveAttachmentToPost($postId, sanitize_text_field($_POST['ulp_feat_image_input']) );
          }
          */
        } else if (!empty($_POST['save_special_settings']) && $this->uid && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
            $this->saveSpecialSettings('ulp_question', ulp_sanitize_array( $_POST ) );
        }

        $limit = $this->perPage;
        $offset = 0;
        $totalItems = $postsDb->select('ulp_question', $limit, $offset, $this->uid, true);
        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;

        require_once ULP_PATH . 'classes/IndeedPagination.class.php';

        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'addNewLink'            => add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_question'], $this->baseUri),
            'items'                 => $postsDb->select('ulp_question', $limit, $offset, $this->uid, false),
            'pagination'            => $pagination->output(),
            'baseUri'               => $this->baseUri,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function manageAnnouncements()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-announcements.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-announcements.php' : $location;

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if (isset($_POST['submit']) && $this->uid && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
            && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){
          $saveData = [
            'ID'              => isset($_POST['ID']) ? sanitize_text_field( $_POST['ID'] ) : 0,
            'post_content'    => sanitize_textarea_field($_POST['post_content']),
            'post_title'      => sanitize_textarea_field( $_POST['post_title'] ),
            'post_type'       => 'ulp_announcement',
            'post_author'     => $this->uid,
          ];
          if (isset($_POST['post_status'])){
              $saveData['post_status'] = sanitize_textarea_field($_POST['post_status']);
          }
          $postId = $postsDb->save($saveData);
          update_post_meta($postId, 'ulp_course_id', sanitize_text_field($_POST['ulp_course_id']));
        }
        $courseId = isset($_GET['courseId']) ? $_GET['courseId'] : 0;

        $limit = $this->perPage;
        $offset = 0;
        if ($courseId){
            $totalItems = $postsDb->selectByMetaValue('ulp_announcement', $limit, $offset, 'ulp_course_id', $courseId, true, $this->uid);
        } else {
            $totalItems = $postsDb->getByAuthor('ulp_announcement', $limit, $offset, true, $this->uid);
        }

        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;

        require_once ULP_PATH . 'classes/IndeedPagination.class.php';

        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'pagination'            => $pagination->output(),
            'courseId'              => $courseId,
            'announcementObject'    => new \Indeed\Ulp\Db\Announcements(),
            'baseUri'               => $this->baseUri,
        ];

        if ($courseId){
            $data['addNewLink'] = add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_announcement', 'courseId' => $courseId], $this->baseUri);
            $data['items'] = $postsDb->selectByMetaValue('ulp_announcement', $limit, $offset, 'ulp_course_id', $courseId, false, $this->uid);
        } else {
            $data['addNewLink'] = add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_announcement'], $this->baseUri);
            $data['items'] = $postsDb->getByAuthor('ulp_announcement', $limit, $offset, false, $this->uid);
        }

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function manageQanda()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/manage-qanda.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/manage-qanda.php' : $location;
        $qandaDbObject = new \Indeed\Ulp\Db\QandA();

        $postsDb = new \Indeed\Ulp\Db\Posts();
        if (isset($_POST['submit']) && $this->uid && isset($_COOKIE['ulp_ref_value']) && $_COOKIE['ulp_ref_value']!=$this->currentUri
          && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
        ){

            $saveData = [
              'ID'              => isset($_POST['ID']) ? sanitize_text_field( $_POST['ID'] ) : 0,
              'post_content'    => sanitize_textarea_field($_POST['post_content']),
              'post_title'      => sanitize_textarea_field( $_POST['post_title'] ),
              'post_type'       => 'ulp_qanda',
              'post_author'     => $this->uid,
            ];
            if (isset($_POST['post_status'])){
                $saveData['post_status'] = sanitize_text_field( $_POST['post_status'] );
            }
            $postId = $postsDb->save($saveData);

            update_post_meta($postId, 'ulp_qanda_course_id', sanitize_text_field( $_POST['ulp_qanda_course_id'] ) );
        }
        $courseId = isset($_GET['courseId']) ? $_GET['courseId'] : 0;

        $limit = $this->perPage;
        $offset = 0;

        if ($courseId){
            $totalItems = $postsDb->selectByMetaValue('ulp_qanda', $limit, $offset, 'ulp_qanda_course_id', $courseId, true);
        } else {
            $totalItems = $qandaDbObject->countAllByInstructor($this->uid);
        }

        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;

        require_once ULP_PATH . 'classes/IndeedPagination.class.php';

        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'pagination'            => $pagination->output(),
            'courseId'              => $courseId,
            'qandaDbObject'         => $qandaDbObject,
            'baseUri'               => $this->baseUri,
        ];

        if ($courseId){
            $data['addNewLink'] = add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_qanda', 'courseId' => $courseId], $this->baseUri);
            $data['items'] = $postsDb->selectByMetaValue('ulp_qanda', $limit, $offset, 'ulp_qanda_course_id', $courseId, false);
        } else {
            $data['addNewLink'] = add_query_arg(['tab' => 'add-edit', 'type' => 'ulp_qanda' ], $this->baseUri);
            $data['items'] = $qandaDbObject->getByInstructor($this->uid, $limit, $offset);
        }

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function addEditCourses()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_courses.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_courses.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_course')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'     => $postsDb->get($postId),
            'post_id'       => $postId,
            'saveLink'      => add_query_arg(['tab' => 'manage', 'type' => 'ulp_course'], $this->baseUri),
            'is_senior'     => \DbUlp::isInstructorSenior($this->uid),
            'tags'          => get_terms('course_tags'),
            'categories'    => get_terms('ulp_course_categories'),
            'post_tags'     => \DbUlp::getTermsForPost($postId, 'course_tags'),
            'post_cats'     => \DbUlp::getTermsForPost($postId, 'ulp_course_categories'),
            'featureImage'  => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function addEditQuizes()
    {
        wp_register_script('ulp_ui_multiselect_js', ULP_URL . 'assets/js/ui.multiselect.js', array('jquery'), '3.0.1');
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_quizes.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_quizes.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_quiz')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'     => $postsDb->get($postId),
            'post_id'       => $postId,
            'saveLink'      => add_query_arg(['tab' => 'manage', 'type' => 'ulp_quiz'], $this->baseUri),
            'all_questions' => \DbUlp::getAllQuestions(true),
            'is_senior'     => \DbUlp::isInstructorSenior($this->uid),
            'categories'    => get_terms('ulp_quiz_categories'),
            'post_cats'     => \DbUlp::getTermsForPost($postId, 'ulp_quiz_categories'),
            'featureImage'  => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
        ];
        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function addEditQuestions()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_questions.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_questions.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_question')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'     => $postsDb->get($postId),
            'post_id'       => $postId,
            'saveLink'      => add_query_arg(['tab' => 'manage', 'type' => 'ulp_question'], $this->baseUri),
            'is_senior'     => \DbUlp::isInstructorSenior($this->uid),
            'categories'    => get_terms('ulp_question_categories'),
            'post_cats'     => \DbUlp::getTermsForPost($postId, 'ulp_question_categories'),
            'featureImage'  => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
        ];
        $data = $data + \DbUlp::getPostMetaGroup($postId, 'answer_settings', TRUE);

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function addEditLessons()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_lessons.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_lessons.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_lesson')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'                 => $postsDb->get($postId),
            'post_id'                   => $postId,
            'saveLink'                  => add_query_arg(['tab' => 'manage', 'type' => 'ulp_lesson'], $this->baseUri),
            'metas'                     => \DbUlp::getPostMetaGroup($postId, 'drip_content'),
            'is_senior'                 => \DbUlp::isInstructorSenior($this->uid),
            'categories'                => get_terms('ulp_lesson_categories'),
            'post_cats'                 => \DbUlp::getTermsForPost($postId, 'ulp_lesson_categories'),
            'featureImage'              => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
            'video_lesson_settings'     => \DbUlp::getPostMetaGroup( $postId, 'video_lesson_settings' )
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function addEditAnnouncements()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_announcements.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_announcements.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_announcement')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $t1 = \DbUlp::get_courses_for_instructor($this->uid);
        $t2 = \DbUlp::getCoursesForAdditionalInstructor($this->uid);
        if ($t2===false){
          $t2 = [];
        }
        if ($t1===false){
          $t1 = [];
        }
        $courses = array_merge($t1, $t2);

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'         => $postsDb->get($postId, true),
            'post_id'           => $postId,
            'ulp_course_id'     => get_post_meta($postId, 'ulp_course_id', true),
            'courses'           => $courses,
            'commentsBox'	      => $this->listingCommentsBox($postId),
            'is_senior'         => \DbUlp::isInstructorSenior($this->uid),
            'categories'        => get_terms('ulp_announcement_categories'),
            'post_cats'         => \DbUlp::getTermsForPost($postId, 'ulp_announcement_categories'),
            'featureImage'      => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
        ];
        if ($data['ulp_course_id']){
            $data['saveLink'] = add_query_arg(['tab' => 'manage', 'type' => 'ulp_announcement', 'courseId' => $data['ulp_course_id']], $this->baseUri);
        } else {
            $data['saveLink'] = add_query_arg(['tab' => 'manage', 'type' => 'ulp_announcement' ], $this->baseUri);
        }

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function addEditQanda()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/add_edit_qanda.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/add_edit_qanda.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_qanda')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $t1 = \DbUlp::get_courses_for_instructor($this->uid);
        $t2 = \DbUlp::getCoursesForAdditionalInstructor($this->uid);
        if ($t2===false){
          $t2 = [];
        }
        if ($t1===false){
          $t1 = [];
        }
        $courses = array_merge($t1, $t2);

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'post_data'               => $postsDb->get($postId, true),
            'post_id'                 => $postId,
            'ulp_qanda_course_id'     => get_post_meta($postId, 'ulp_qanda_course_id', true),
            'courses'                 => $courses,
            'commentsBox'	            => $this->listingCommentsBox($postId),
            'is_senior'               => \DbUlp::isInstructorSenior($this->uid),
            'categories'              => get_terms('ulp_qanda_categories'),
            'post_cats'               => \DbUlp::getTermsForPost($postId, 'ulp_qanda_categories'),
            'featureImage'            => \DbUlp::getAttachmentUrlById( \DbUlp::getFeatureImageIdForPost($postId) ),
        ];
        $data['saveLink'] = add_query_arg(['tab' => 'manage', 'type' => 'ulp_qanda', 'courseId' => $data['ulp_qanda_course_id']], $this->baseUri);

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data)->getOutput();
    }

    private function coursesSpecialSettings()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/courses_special_settings.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/courses_special_settings.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_course')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'data'              => \DbUlp::getPostMetaGroup($postId, 'course_special_settings'),
            'saveLink'          => add_query_arg(['tab' => 'manage', 'type' => 'ulp_course' ], $this->baseUri),
            'post_title'        => \DbUlp::getPostTitleByPostId($postId),
            'postId'            => $postId,
            'courses'           => \DbUlp::getAllCourses(),
        ];
        $data['data']['coming_soon'] =  get_option('ulp_coming_soon_enabled');
        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function quizesSpecialSettings()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/quizes_special_settings.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/quizes_special_settings.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_quiz')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'data'        => \DbUlp::getPostMetaGroup($postId, 'quiz_special_settings'),
            'saveLink'    => add_query_arg(['tab' => 'manage', 'type' => 'ulp_quiz' ], $this->baseUri),
            'post_title'  => \DbUlp::getPostTitleByPostId($postId),
            'postId'      => $postId,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function questionsSpecialSettings()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/questions_special_settings.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/questions_special_settings.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_question')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'data'         => \DbUlp::getPostMetaGroup($postId, 'questions_special_settings'),
            'saveLink'     => add_query_arg(['tab' => 'manage', 'type' => 'ulp_question' ], $this->baseUri),
            'postContent'  => \DbUlp::getPostContentByPostId($postId),
            'postId'       => $postId,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function lessonsSpecialSettings()
    {
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/lessons_special_settings.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/lessons_special_settings.php' : $location;

        $postId = isset($_GET['postId']) ? sanitize_text_field($_GET['postId']) : 0;

        if ($postId && !$this->canAccessThisPost($postId, 'ulp_lesson')){
            return esc_html__("You don't have the permissions to edit this post!", 'ulp');
        }

        $postsDb = new \Indeed\Ulp\Db\Posts();
        $data = [
            'data'        => \DbUlp::getPostMetaGroup($postId, 'lesson_special_settings'),
            'saveLink'    => add_query_arg(['tab' => 'manage', 'type' => 'ulp_lesson' ], $this->baseUri),
            'post_title'  => \DbUlp::getPostTitleByPostId($postId),
            'postId'      => $postId,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function saveSpecialSettings($type='', $postData=[])
    {
        if (!$type || !isset($postData['ID']) || !$postData){
            return false;
        }
        $postType = \DbUlp::getPostTypeById($postData['ID']);
        switch ($postType){
            case 'ulp_course':
              $options = \DbUlp::getPostMetaGroup($postData['ID'], 'course_special_settings');
              break;
            case 'ulp_lesson':
              $options = \DbUlp::getPostMetaGroup($postData['ID'], 'lesson_special_settings');
              break;
            case 'ulp_quiz':
              $options = \DbUlp::getPostMetaGroup($postData['ID'], 'quiz_special_settings');
              break;
            case 'ulp_question':
              $options = \DbUlp::getPostMetaGroup($postData['ID'], 'questions_special_settings');
              break;
        }
        if (empty($options)){
            return false;
        }
        foreach ($options as $key => $default_value){
            if (!isset($postData[$key])){
                continue;
            }
            update_post_meta($postData['ID'], $key, $postData[$key]);
        }
    }

    private function listingCommentsBox($postId=0)
    {
        if (!$postId){
            return '';
        }
        $location = locate_template('ultimate-learning-pro/instructor_dashboard/listing_comments.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/listing_comments.php' : $location;

        $offset = 0;
        $limit = $this->perPage;
        $comments = new \Indeed\Ulp\Db\Comments();
        $data = [
            'comments'            => $comments->getForPost($postId, $limit, $offset),
            'showLoadMoreBttn'    => $comments->countForPost($postId)<=$this->perPage ? false : true,
            'limit'               => $this->perPage,
            'postId'              => $postId,
        ];

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function listStudentsByCourse()
    {
        $courseId = empty($_GET['courseId']) ? 0 : $_GET['courseId'];
        if (empty($courseId)){
            return '';
        }

        $courseId = isset($_GET['courseId']) ? $_GET['courseId'] : 0;

        $limit = $this->perPage;
        $offset = 0;
        $totalItems = \DbUlp::countStudents($courseId);
        $currentPage = isset($_GET['ulp_page']) ? $_GET['ulp_page'] : 1;

        require_once ULP_PATH . 'classes/IndeedPagination.class.php';

        $pagination = new \IndeedPagination([
            'base_url'        => $this->currentUri,
            'param_name'      => 'ulp_page',
            'total_items'     => $totalItems,
            'items_per_page'  => $this->perPage,
            'current_page'    => $currentPage,
        ]);

        if ($currentPage>1){
          $offset = ( $currentPage - 1 ) * $this->perPage;
        } else {
          $offset = 0;
        }
        if ($offset + $limit>$totalItems){
          $limit = $totalItems - $offset;
        }

        $data = [
            'students'      => \DbUlp::getStudents($courseId, $limit, $offset),
            'pagination'    => $pagination->output(),
            'courseId'      => $courseId,
            'courseName'    => \DbUlp::getPostNameById($courseId),
        ];

        $location = locate_template('ultimate-learning-pro/instructor_dashboard/listing_students.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/listing_students.php' : $location;

        $view = new \ViewUlp();
        return $view->setTemplate($template)->setContentData($data, true)->getOutput();
    }

    private function canAccessThisPost($postId=0, $postType='')
    {
        if ($postType!='ulp_course'){
            $courseId = \DbUlp::getCoursesForQuizId($postId);
        } else {
            $courseId = $postId;
        }
        if (is_array($courseId)){
            foreach ($courseId as $array){
                if (\DbUlp::isInstructorForCourse($this->uid, $array['course_id'])){
                    return true;
                }
            }
        } else {
          if (\DbUlp::isInstructorForCourse($this->uid, $courseId)){
              return true;
          }
        }
        if (\DbUlp::isUserAuthorForPost($this->uid, $postId)){
            return true;
        }

        /// admin can view anything
        $roles = \DbUlp::get_user_roles($this->uid);
        if (isset($roles['administrator'])){
            return true;
        }

        return false;
    }

}
