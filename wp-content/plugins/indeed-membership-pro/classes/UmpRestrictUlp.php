<?php
namespace Indeed\Ihc;

class UmpRestrictUlp
{

    public function __construct()
    {
        add_filter('filter_on_ihc_block_url', array($this, 'checkRestrictionOnCourse'), 99, 4);
    }

    public function checkRestrictionOnCourse($redirectLink='', $url='', $currentUser=0, $postId=0)
    {
        global $current_user;
        if (!$this->isUlpActive()){
            return $redirectLink;
        }
        if (empty($url) || empty($currentUser)){
            return $redirectLink;
        }
        $courseId = $this->getCourseId($url);
        if (!$courseId){
            return $redirectLink;
        }
        $levelsForThisCourse = $this->getLevelsForThisCourse($courseId);
        if (!$levelsForThisCourse){
            return $redirectLink;
        }
        $redirectLink = $this->checkRedirectLink($redirectLink, $postId);

        if ($postId!=$courseId){
            $isPreview = $this->isPreviewLesson($postId);
        }
        if (!empty($isPreview)){
            $redirectLink = '';
            return $redirectLink;
        }

        $userLevels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $current_user->ID );

        if (empty($userLevels)){
            return $redirectLink;
        }

        foreach ($levelsForThisCourse as $lid){
            $isExpired = ihc_is_user_level_expired($current_user->ID, $lid);
            $isOntime = ihc_is_user_level_ontime($lid);
            $userGotLevel = empty($userLevels[$lid]) ? false : true;
            if ($userGotLevel && $isExpired==0 && $isOntime==1){
                $redirectLink = ''; /// remove redirect
            }
        }
        return $redirectLink;
    }

    private function isUlpActive()
    {
        if (!function_exists('is_plugin_active')){
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (is_plugin_active('indeed-learning-pro/indeed-learning-pro.php')){
            return true;
        }
        return false;
    }

    private function getLevelsForThisCourse($courseId=0)
    {
        $array = array();
        if (!$courseId){
            return $array;
        }
        $levelsData = \Indeed\Ihc\Db\Memberships::getAll();
        if (empty($levelsData)){
            return $array;
        }
        foreach ($levelsData as $lid => $levelData){
            if (isset($levelData['ump_ulp_course']) && $levelData['ump_ulp_course']==$courseId){
                $array[] = $lid;
            }
        }
        return $array;
    }

    private function getCourseId($url='')
    {
        $courseId = 0;
        if (empty($url)){
            return $courseId;
        }
        $courseQueryVarName = get_option('ulp_course_custom_query_var');
        if (empty($courseQueryVarName)){
            $courseQueryVarName = 'ulp-course';
        }
        $uriPath = parse_url($url, PHP_URL_PATH);
        if (empty($uriPath)){
            return $courseId;
        }
        $uriSegments = explode('/', $uriPath);
        if (empty($uriSegments)){
            return $courseId;
        }
        $key = false;
        if (in_array($courseQueryVarName, $uriSegments)!==false){
            $key = array_search($courseQueryVarName, $uriSegments);
        }
        if ($key===false){
            return $courseId;
        }
        $key++;
        if (empty($uriSegments[$key])){
            return $courseId;
        }
        $courseSlug = $uriSegments[$key];

  			if ($courseSlug){
  					$courseId = \DbUlp::getPostIdByTypeAndName('ulp_course', $courseSlug);
  			}
        return $courseId;
    }

    private function checkRedirectLink($redirectLink='', $postId=0)
    {
        if (!empty($redirectLink)){
            return $redirectLink;
        }
        $defaultRedirectId = get_option('ihc_general_redirect_default_page');
        if ($defaultRedirectId==$postId){
            $defaultRedirectId = '';
        }
        if (!empty($defaultRedirectId)){
            $redirectLink = get_permalink($defaultRedirectId);
        }
        $redirectLink = home_url();
        return $redirectLink;
    }

    private function isPreviewLesson($postId=0)
    {
        $postType = \DbUlp::getPostTypeById($postId);
        if ($postType!='ulp_lesson'){
            return false;
        }
        $preview = get_post_meta($postId, 'ulp_lesson_preview', TRUE);
        if ($preview){
            return true;
        }
        return false;
    }


}
