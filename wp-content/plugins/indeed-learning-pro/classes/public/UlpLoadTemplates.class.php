<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('UlpLoadTemplates')):
class UlpLoadTemplates{
	/**
	 * @var string
	 */
	private static $post_type = '';
	/**
	 * @var int
	 */
	private static $post_id = 0;
	/**
	 * @var string
	 */
	private static $url = '';
	/**
	 * @param string
	 * @param int
	 * @return none
	 */
	public function __construct($post_type='', $post_id=0){
		self::$url = ULP_CURRENT_URI;
		self::$post_type = $post_type;
		self::$post_id = $post_id;

		if (empty(self::$post_id)){
			self::$post_id = ulp_get_post_id_from_url(self::$url);
		}
		if (empty(self::$post_type)){
			self::setPostType();
		}
		/// for indeed sections
		add_filter('indeed_search_theme_template_for_section', array('UlpLoadTemplates', 'search_dynamic_template_for_section'), 1, 2);
		/// {$type}_template
		/// `$type` include: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'embed', home', 'frontpage',
		/// 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
		add_filter('archive_template', array('UlpLoadTemplates', 'load_template_for_archive'), 1, 1);
		add_filter('single_template', array('UlpLoadTemplates', 'load_template_for_single'), 1, 1);
		add_filter('ulp_filter_shortcodes_template', ['UlpLoadTemplates', 'filterShortcodesTemplate'], 2, 2);
	}
	/**
	 * @param string
	 * @return string
	 */
	public static function load_template_for_single($template=''){
		$default = $template;
		$search = '';
		switch (self::$post_type){
			case 'ulp_course':
				require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
				$DbUserEntitiesRelations = new DbUserEntitiesRelations();
				$uid = ulp_get_current_user();
				if ($DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, self::$post_id)){
						///Enrolled
						$search = 'single-course.php';
						$default = ULP_PATH . 'views/templates/single-course.php';
				} else {
						/// visitor
						$search = 'single-course-visitor.php';
						$default = ULP_PATH . 'views/templates/single-course-visitor.php';
				}

				/// coming soon - rewrite template
				$comingSoon = new \Indeed\Ulp\Db\DbComingSoon();
				if ($comingSoon->isEnabledOnCourse(self::$post_id)){
					$search = 'single-course-coming-soon.php';
					$default = ULP_PATH . 'views/templates/single-course-coming-soon.php';
				}
				break;
			case 'ulp_quiz':
				require_once ULP_PATH . 'classes/public/UlpPublicQuiz.class.php';
				$search = 'single-quiz.php';
				$default = ULP_PATH . 'views/templates/single-quiz.php';
				break;
			case 'ulp_lesson':
				require_once ULP_PATH . 'classes/public/UlpPublicLesson.class.php';
				$isVideo = get_post_meta( self::$post_id, 'ulp_lesson_is_video', true );
				if ( $isVideo ){
						/// video
						$search = 'single-lesson-video.php';
						$default = ULP_PATH . 'views/templates/single-lesson-video.php';
				} else {
						/// default
						$search = 'single-lesson.php';
						$default = ULP_PATH . 'views/templates/single-lesson.php';
				}
				break;
			case 'ulp-instructor':
				require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
				$search = 'single-instructor.php';
				$default = ULP_PATH . 'views/templates/instructors/single-instructor.php';
				break;
			case 'ulp_announcement':
				$search = 'single-announcement.php';
				$default = ULP_PATH . 'views/templates/single-announcement.php';
				break;
			case 'ulp_qanda':
				$search = 'single-qanda.php';
				$default = ULP_PATH . 'views/templates/single-qanda.php';
				break;
		}
		if ($search && $new_location=self::searchTemplateIntoCurrentTheme('ultimate-learning-pro/' . $search)){
					return $new_location;
			}
		if ($search && $new_location=self::searchTemplateIntoCurrentTheme($search)){
			return $new_location;
		}
		return $default;
	}
	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function search_dynamic_template_for_section($template_path='', $filename=''){
		/// search into ultimate-learning-pro theme folder
		if ($location=self::searchTemplateIntoCurrentTheme('ultimate-learning-pro/'.$filename)){
				return $location;
		}
		/// search into theme root
		if ($location=self::searchTemplateIntoCurrentTheme($filename)){
				return $location;
		}
		/// default (plugin template file)
		return $template_path;
	}
	/**
	 * @param string
	 * @return string
	 */
	protected static function searchTemplateIntoCurrentTheme($search=''){
		if ($location=locate_template($search)){
			return $location;
		}
		return '';
	}
	/**
	 * @param string
	 * @return string
	 */
	public static function load_template_for_archive($template=''){
		$search = '';
		$default = '';
		switch (self::$post_type){
			case 'ulp_course':
				$search = 'archive-course.php';
				$default = ULP_PATH . 'views/templates/archive-course.php';
				break;
		}
		if ($search && $new_location=self::searchTemplateIntoCurrentTheme($search)){
			return $new_location;
		}
		if ($default){
			return $default;
		}
		return $template;
	}
	/**
	 * @param none
	 * @return none
	 */
	private static function setPostType(){
		self::$post_type = get_post_type();
		if (empty(self::$post_type)){
			if (!empty(self::$post_id)){
				self::$post_type = DbUlp::getPostTypeById(self::$post_id);
			}
		}
	}

	public static function filterShortcodesTemplate($currentLocation='', $searchFile='')
	{
			/// search into ultimate-learning-pro theme folder
			if ($location=self::searchTemplateIntoCurrentTheme('ultimate-learning-pro/' . $searchFile)){
					return $location;
			}
			/// search into theme root
			if ($location=self::searchTemplateIntoCurrentTheme($searchFile)){
					return $location;
			}
			/// default (plugin template file)
			return $currentLocation;
	}

}
endif;
