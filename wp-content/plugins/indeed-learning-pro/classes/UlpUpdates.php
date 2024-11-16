<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpUpdates')){
   return;
}

class UlpUpdates
{
    /**
     * @var string
     */
    protected static $_optionName = 'ulp_plugin_version';

    /**
     * @param none
     * @return none
     */
    public static function run()
    {
        $pluginOldVersion = get_option(self::$_optionName);
        if (version_compare( $pluginOldVersion, '3.0' )==-1){
            self::updateInstructorSeniorCapability();
        }
        if (version_compare($pluginOldVersion, ULP_PLUGIN_VER)==-1){
            self::doAction();
            update_option(self::$_optionName, ULP_PLUGIN_VER);
        }
    }

    /**
     * @param none
     * @return none
     */
    private static function doAction()
    {
        // @since v 1.0.6
        DbUlp::create_plugin_custom_roles();

        // @since v 1.2
        self::instructorsGotCPT();
        self::insertDemoContent();
        self::checkIndexes();
    }

    /**
     * @param none
     * @return none
     */
    public static function instructorsGotCPT()
    {
        $instructors = DbUlp::getAllInstructors(9999, 0);
        if (!$instructors){
            return;
        }
        foreach ($instructors as $object){
            $alreadyExists = DbUlp::getPostIdForInstructor($object->uid);
            if (empty($alreadyExists)){
                DbUlp::insertCustomPostTypeInstructor($object->uid);
            }
        }
    }

    /**
     * made for inserting the courses tags
     * @param none
     * @return none
     */
    public static function insertDemoContent()
    {
        require_once ULP_PATH . 'classes/Ulp_Demo_Content.class.php';
        $Ulp_Demo_Content = new Ulp_Demo_Content();
    }

    /**
     * @param none
     * @return none
     */
    public static function checkIndexes()
    {
        DbUlp::addCoursesCodulesIndex();
        DbUlp::addCoursesModulesMetasIndex();
        DbUlp::addCourseModulesItemsIndex();
        DbUlp::addQuizesQuestionsIndex();
        DbUlp::addUserEntitiesRelationsIndex();
        DbUlp::addUserEntitiesRelationsMetasIndex();
        DbUlp::addActivityIndex();
        DbUlp::addRewardPointsDetailsIndex();
        DbUlp::addOrderMetaIndex();
        DbUlp::addStudentCertificateIndex();
        DbUlp::addNotesIndex();
        DbUlp::addStudentBadgesIndex();
    }

    /**
     * @param none
     * @return none
     */
    public static function updateInstructorSeniorCapability()
    {
        $role = get_role( 'ulp_instructor_senior' );
        if ( $role && empty( $role->capabilities['publish_ulp_courses'] ) ){
            $role->add_cap( 'publish_ulp_courses' );
        }
    }

}
