<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Student_Profile')){
   return;
}
class Ulp_Student_Profile{
    private $_uid                 = 0;
    private $_current_tab         = '';
    private $_metas               = array();
    private $_tab_content         = '';
    private $_tab_title           = '';
    private $_student_data        = array();
    private $_menu_items          = array();
    private $_uri                 = '';
    private $_current_uri         = '';
    private $_extra_metas         = array();
    private $confirmMessage       = '';
    private $errorMessage         = '';

    public function __construct($uid=0, $extra_metas=array() ){
        $this->_current_tab = isset($_GET['ulp_tab']) ? sanitize_text_field($_GET['ulp_tab']) : 'overview';
        $this->_uid = $uid;
        $this->_set_uri();
        $this->_set_nav_menu();
        $this->_metas = DbUlp::getOptionMetaGroup('showcases_account_page');
        $this->_extra_metas = $extra_metas;
        require_once ULP_PATH . 'classes/Entity/UlpStudent.class.php';
        $this->_student_data = new UlpStudent($this->_uid);
        $this->processing();
    }

    protected function processing()
    {
        switch ($this->_current_tab){
            case 'profile':
              if (isset($_POST['update_user_data']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
                  $firstName = get_user_meta( $this->_uid, 'first_name', true );
                  if (!empty($_POST['first_name']) && $_POST['first_name']!=$firstName ){
                      $_POST['first_name'] = sanitize_text_field( $_POST['first_name'] );
                      update_user_meta($this->_uid, 'first_name', sanitize_text_field($_POST['first_name']));
                  }
                  $lastName = get_user_meta( $this->_uid, 'last_name', true );
                  if (!empty($_POST['last_name']) && $_POST['last_name']!=$lastName ){
                      $_POST['last_name'] = sanitize_text_field( $_POST['last_name'] );
                      update_user_meta($this->_uid, 'last_name', sanitize_text_field($_POST['last_name']));
                  }
                  $userData = get_userdata( $this->_uid );
                  if ( isset($userData->user_email) && !empty($_POST['user_email']) && $_POST['user_email'] != $userData->user_email ){
                      $_POST['user_email'] = sanitize_text_field( $_POST['user_email'] );
                      wp_update_user( array( 'ID' => $this->_uid, 'user_email' => sanitize_email($_POST['user_email']) ) );
                  }
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
                  $wp_hasher = new PasswordHash( 8, TRUE );
                  if ( $wp_hasher->CheckPassword( $_POST['old_pass'], $current_user->data->user_pass ) ) {
                    $check_pass = true;
                  }

                  if(!$check_pass){
                     $this->errorMessage = esc_html__( 'Old password incorrect!', 'ulp' );
                  }else{
                    //CHECK PASSWORD CONFIRMATION
                    if ( sanitize_text_field($_POST['pass1']) == sanitize_text_field($_POST['pass2']) ){
                      wp_set_password(sanitize_text_field($_POST['pass1']),$this->_uid);
                      $this->confirmMessage = esc_html__( 'Confirmation password incorrect!', 'ulp' );
                    }else{
                      $this->errorMessage = esc_html__( 'Confirmation password incorrect!', 'ulp' );
                    }
                  }
                }else{
                    $this->errorMessage = esc_html__( 'Please Complete all required fields!', 'ulp' );
                }
               }
               if (isset($_POST['update_user_avatar'])){
                 if(!empty($_POST['ulp_avatar'])){
                    $_POST['ulp_avatar'] = sanitize_text_field( $_POST['ulp_avatar'] );
                    update_user_meta($this->_uid, 'ulp_avatar', sanitize_textarea_field($_POST['ulp_avatar']));
                    $data ['avatar'] = sanitize_textarea_field($_POST['ulp_avatar']);
                 }
               }
              break;
        }
    }

    private function _set_uri(){
        $account_page = get_option('ulp_default_page_student_profile');
  			if ($account_page && $account_page>-1 && empty($this->_extra_metas['is_buddypress']) ){
  				$this->_current_uri = get_permalink($account_page);
  			} else {
  				$this->_current_uri = ULP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  			}
  			$remove_get_attr = ['ulp_tab'];
  			foreach ($remove_get_attr as $key){
  				if (!empty($_GET[$key])){
  					$this->_uri = remove_query_arg($key, $this->_current_uri);
  				}
  			}
    }

    private function _set_nav_menu(){
        $this->_menu_items = DbUlp::account_page_get_tabs(false, true);
        if ($this->_menu_items){
            foreach ($this->_menu_items as $slug => $array){
                if (empty($this->_menu_items [$slug]['url'])){
                    $this->_menu_items [$slug]['url'] = add_query_arg( 'ulp_tab', $slug, $this->_uri );
                } else {
                    if (strpos($this->_menu_items [$slug]['url'], 'http')!==0){
                        $this->_menu_items [$slug]['url'] = 'http://' . $this->_menu_items [$slug]['url'];
                    }
                }
            }
            if (isset($this->_menu_items ['list_certificates']) && !get_option('ulp_certificates_enable')){
                unset($this->_menu_items ['list_certificates']);
            }
        }
    }

    public function Header(){
        $location = locate_template('ultimate-learning-pro/student_profile/header.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/header.php' : $location;
        $data = [
            'content' => ulp_replace_constants(stripslashes_deep($this->_metas['ulp_ap_welcome_msg']),$this->_uid),
            'metas' => $this->_metas,
            'notices' => $this->_check_for_notice(),
            'ulp_account_page_personal_header' => get_user_meta($this->_uid, 'ulp_account_page_personal_header', true)
        ];
        if ($this->_metas['ulp_ap_edit_show_avatar']){
            $data ['avatar'] = DbUlp::getAuthorImage($this->_uid);
        }

        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    private function _check_for_notice(){
        $array = [];
        if (empty(ULP_LICENSE_SET)){
            $array [] = esc_html__("This is a Trial Version of Ultimate Learning Pro plugin. Please add your purchase code into Licence section to enable the Full Ultimate Learning Pro Version.", 'ulp');
        }
        return $array;
    }

    public function Footer(){
        $location = locate_template('ultimate-learning-pro/student_profile/footer.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/footer.php' : $location;
        $data = ['content' => stripslashes(ulp_replace_constants($this->_metas['ulp_ap_footer_msg'])) ];
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    public function NavBar(){
        $location = locate_template('ultimate-learning-pro/student_profile/nav_bar.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/nav_bar.php' : $location;
        $data = [ 'tabs' => $this->_menu_items ];
		    $data ['metas'] = $this->_metas;
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    public function Content(){
        if (empty($this->_current_tab)){
            $this->_current_tab = 'overview';
        }

        switch ($this->_current_tab){
            //default:
            case 'overview':
              return $this->_overview();
              break;
            case 'profile':
              return $this->_profile();
              break;
            case 'orders':
              return $this->_orders();
              break;
            case 'help':
              return $this->_help();
              break;
            case 'my_courses':
              return $this->_my_courses();
              break;
            case 'reward_points':
              return $this->_reward_points();
              break;
            case 'list_certificates':
              return $this->_list_certificates();
              break;
            default:
              $content = get_option('ulp_ap_' . $this->_current_tab . '_msg');
              $title = get_option('ulp_ap_' . $this->_current_tab . '_title');
              $data ['content'] = stripslashes(ulp_replace_constants($content, $this->_uid));
              $data ['title'] = stripslashes(ulp_replace_constants($title, $this->_uid));
              return $this->_standard_content($data);
              break;
        }
    }

    private function _overview(){
        $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_overview_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_overview_title'], $this->_uid));
        return $this->_standard_content($data);
    }

    private function _help(){
        $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_help_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_help_title'], $this->_uid));
        return $this->_standard_content($data);
    }

    private function _orders(){
        $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_orders_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_orders_title'], $this->_uid));
        $location = locate_template('ultimate-learning-pro/student_profile/standard_content.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/orders.php' : $location;
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    private function _profile(){
        $data = [
                  'first_name'            => get_user_meta($this->_uid, 'first_name', TRUE),
                  'last_name'             => get_user_meta($this->_uid, 'last_name', TRUE),
                  'avatar'                => get_user_meta($this->_uid, 'ulp_avatar', TRUE),
                  'user_email'            => DbUlp::get_user_col_value($this->_uid, 'user_email'),
                  'error_mess'            => $this->errorMessage,
                  'confirm_mess'          => $this->confirmMessage,
                  'user_id'               => $this->_uid,
                  'username'              => get_user_meta($this->_uid, 'nickname', TRUE),
                  'content'               => stripslashes(ulp_replace_constants($this->_metas['ulp_ap_profile_msg'], $this->_uid)),
                  'title'                 => stripslashes(ulp_replace_constants($this->_metas['ulp_ap_profile_title'], $this->_uid)),
                  'avatar_field'          => $this->_avatar_field(),
        ];

        $location = locate_template('ultimate-learning-pro/student_profile/standard_content.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/profile.php' : $location;
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    private function _avatar_field()
    {
      $data = [
          'name'            => 'ulp_avatar',
          'rand'            => rand(1, 10000),
          'imageClass'      => 'ulp-member-photo',
          'user_id'         => $this->_uid,
          'imageUrl'        => '',
          'value'           => get_user_meta($this->_uid, 'ulp_avatar', TRUE)
      ];
      if ( !empty($data['value']) ){
          if (strpos($data['value'], "http")===0){
              $data['imageUrl'] = $data['value'];
          } else {
              $tempData = \DbUlp::getMediaBaseImage($data['value']);
              if (!empty($tempData)){
                $data['imageUrl'] = $tempData;
              }
          }
      }
      $viewObject = new ViewUlp();
      return $viewObject->setTemplate( ULP_PATH . '/views/templates/student_profile/upload_image.php')->setContentData( $data )->getOutput();
    }

    private function _list_certificates(){
        if (!get_option('ulp_certificates_enable')){
           return '';
        }
        $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_list_certificates_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_list_certificates_title'], $this->_uid));
        return $this->_standard_content($data);
    }

    private function _standard_content($data = array()){
        $location = locate_template('ultimate-learning-pro/student_profile/standard_content.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/standard_content.php' : $location;
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

    private function _my_courses(){
		    $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_my_courses_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_my_courses_title'], $this->_uid));
        $location = locate_template('ultimate-learning-pro/student_profile/my_courses.php');
        $template = empty($location) ? ULP_PATH . 'views/templates/student_profile/my_courses.php' : $location;
        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData(['Student' => $this->_student_data, 'data' => $data], true);
        return $view->getOutput();
    }

    private function _reward_points(){
        $data ['content'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_reward_points_msg'], $this->_uid));
        $data ['title'] = stripslashes(ulp_replace_constants($this->_metas['ulp_ap_reward_points_title'], $this->_uid));
        return $this->_standard_content($data);
    }

}
