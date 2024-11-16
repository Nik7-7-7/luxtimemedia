<?php

class berqNotifications {
    // public $notices = [];

    function __construct() {
        add_action('admin_notices', [$this, 'notification']);
        add_action('init', [$this, 'session']);
    }

    function session() {
        if (is_admin()) {
            session_start();
        }
    }

    function notification() {

        if (empty($_SESSION['berqwp_user_notice'])) {
            return;
        }

        $notices = $_SESSION['berqwp_user_notice'];

        
        if (!empty($notices)) {
            foreach($notices as $notice) {
                $msg = $notice[1];
                $class = $notice[0];

				$notice = '<div class="notice notice-'.$class.' is-dismissible">';
				$notice .= '<p>';
				$notice .= __($msg, 'searchpro');
				$notice .= '</p>';
				$notice .= '</div>';
				echo wp_kses_post($notice);
			
            }

            $_SESSION['berqwp_user_notice'] = [];
        }

    }

    function notice($text) {
        $_SESSION['berqwp_user_notice'][] = ['info', $text];
    }

    function error($text) {
        $_SESSION['berqwp_user_notice'][] = ['error', $text];
    }

    function warning($text) {
        $_SESSION['berqwp_user_notice'][] = ['warning', $text];
    }

    function success($text) {
        $_SESSION['berqwp_user_notice'][] = ['success', $text];
    }
}

global $berqNotifications;
$berqNotifications = new berqNotifications();