<?php
if (!defined('ABSPATH'))
    exit;

if (class_exists('HMWP_Models_Compatibility_Abstract')) {
    class HideMyWPGhost extends HMWP_Models_Compatibility_Abstract
    {
        function __construct()
        {
            add_filter('template_redirect', [$this, 'disable_plugin']);
            add_filter('berqwp_cache_buffer', [$this, 'findReplaceCache'], PHP_INT_MAX);
        }

        function disable_plugin()
        {
            if (
                isset($_GET['generating_critical_css'])
                || isset($_GET['nocache'])
                || isset($_GET['creating_cache'])
            ) {
                add_filter('hmwp_process_buffer', '__return_false');
				add_filter('hmwp_process_find_replace', '__return_false');
            }

        }

        function findReplaceCache($content) {
            return HMWP_Classes_ObjController::getClass('HMWP_Models_Rewrite')->getBuffer($content);;
        }


    }
    new HideMyWPGhost();
}