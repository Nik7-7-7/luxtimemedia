<?php

namespace FluentBookingPro\App\Hooks\Handlers;


use FluentBooking\App\Hooks\Handlers\AdminMenuHandler;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\App;

class FrontendRenderer
{
    public function register()
    {
        add_action('init', function () {
            $frontAppUrl = $this->getFrontAppUrl();

            if (!$frontAppUrl) {
                return;
            }

            add_filter('fluent_booking/admin_portal_vars', function ($vars) {
                if (is_admin()) {
                    $vars['menuItems'][] = [
                        'key'       => 'frontend',
                        'label'     => __('Frontend Portal', 'fluent-booking-pro'),
                        'permalink' => $this->getFrontAppUrl(),
                    ];
                } else {
                    $siteLogoId = get_theme_mod( 'custom_logo' );

                    if($siteLogoId) {
                        $siteLogo = wp_get_attachment_url($siteLogoId);
                        if($siteLogo) {
                            $vars['logo'] = $siteLogo;
                        }
                    }
                }
                
                return $vars;
            });

            add_filter('fluent_booking/admin_base_url', function ($url) use ($frontAppUrl) {
                return $frontAppUrl;
            }, 100);

            add_action('template_redirect', function () {
                $frontSlug = $this->getFronendSlug();

                if (!$frontSlug) {
                    return;
                }

                global $wp;
                $currentUrl = home_url($wp->request);

                $extension = str_replace(home_url(), '', $currentUrl);

                if (!$extension) {
                    return;
                }

                // trim the /
                $uri = trim($extension, '/');

                if (!$uri) {
                    return;
                }

                $urlParts = array_values(array_filter(explode('/', $uri)));

                if (!count($urlParts) || $urlParts[0] !== $frontSlug) {
                    return;
                }

                $this->renderFullApp();
            }, 1);
        }, 1);

        add_shortcode('fluent_booking_panel', [$this, 'renderFrontendShortcode']);
    }

    public function renderFullApp()
    {
        if (get_current_user_id()) {
            $hasPermission = PermissionManager::currentUserHasAnyPemrmission();
            if ($hasPermission) {
                // disable admin bar
                add_filter('show_admin_bar', '__return_false');
                $content = $this->getAppContent();
                (new AdminMenuHandler())->enqueueAssets();
            } else {
                $content = '<div style="padding: 40px; background: white; text-align: center; margin: 100px auto; max-width: 500px;" class="fbs_no_permission"><p>' . apply_filters('fluent_booking/no_permission_message', __('You do not have permission to view the bookings', 'fluent-boards-pro')) . '</p></div>';
            }
        } else {
            $content = $this->getAuthContent();
        }

        require_once FLUENT_BOOKING_PRO_DIR . 'app/Views/front-app.php';

        exit();
    }

    public function getAppContent()
    {
        ob_start();
        echo '<div class="fluent_boards_frontend fbs_front">';
        (new AdminMenuHandler())->render();
        echo '</div>';
        $content = ob_get_clean();
        return $content;
    }

    public function renderFrontendShortcode()
    {
        add_filter('fluent_booking/skip_no_conflict', '__return_true');

        if (get_current_user_id()) {
            $hasPermission = PermissionManager::currentUserHasAnyPemrmission();
            if ($hasPermission) {
                $content = $this->getAppContent();
                (new AdminMenuHandler())->enqueueAssets();
            } else {
                $content = '<div style="padding: 40px; background: white; text-align: center; margin: 100px auto; max-width: 500px;" class="fbs_no_permission"><p>' . apply_filters('fluent_booking/no_permission_message', __('You do not have permission to view the bookings', 'fluent-boards-pro')) . '</p></div>';
            }
        } else {
            $content = $this->getAuthContent();
        }

        if (is_user_logged_in()) {
            $content = '<div class="fbs_shortcode">' . $content . '</div>';
            ob_start();
            ?>
            <style>
                .fbs_shortcode #fluent-booking-app {
                    margin-left: 0;
                    padding-left: 0;
                    background: transparent;
                }

                .fbs_shortcode #fluent-booking-app .fl_app {
                    margin-right: 0;
                }

                .fbs_shortcode .fframe_main-menu-items {
                    margin-left: 0;
                    margin-right: 0;
                    border-radius: 8px;
                }
            </style>
            <?php
            $content .= ob_get_clean();
        } else {
            ob_start();
            ?>
            <style>
                .fbs_login_form {
                    margin: 0 auto;
                    max-width: 500px !important;
                    background: white;
                    border-radius: 5px;
                }

                .fbs_login_form {
                    .fbs_login_form_heading {
                        padding: 20px;
                        background: #fbfbfb;
                        border-bottom: 1px solid #e5e7eb;
                        font-size: 20px;
                        font-weight: 500;
                        border-top-left-radius: 5px;
                        border-top-right-radius: 5px;
                        text-align: center;
                    }
                }

                .fbs_login_wrap {
                    padding: 20px;
                    border-bottom-left-radius: 5px;
                    border-bottom-right-radius: 5px;
                }

                #loginform p {
                    margin-bottom: 10px;
                    display: block;
                }

                #loginform * {
                    box-sizing: border-box;
                }

                #loginform p > label {
                    font-size: 14px;
                    font-weight: 500;
                    display: block;
                }

                input.input {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #e5e7eb;
                    border-radius: 5px;
                    margin-top: 5px;
                }

                .button-primary {
                    background: #3b82f6;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-top: 10px;
                }
            </style>
            <?php
            $content .= ob_get_clean();
        }

        return $content;
    }


    protected function getFrontAppUrl()
    {
        if (defined('FLUENT_BOOKING_FRONT_SLUG') && FLUENT_BOOKING_FRONT_SLUG) {
            return site_url(FLUENT_BOOKING_FRONT_SLUG) . '#/';
        }

        $settings = Helper::getPrefSettins();

        if ($settings['frontend']['enabled'] !== 'yes') {
            return '';
        }

        // check if by page
        $renderType = empty($settings['frontend']['render_type']) ? 'standalone' : $settings['frontend']['render_type'];

        if ($renderType === 'shortcode') {
            if (empty($settings['frontend']['page_id'])) {
                return '';
            }

            return get_the_permalink($settings['frontend']['page_id']) . '#/';
        }

        if (empty($settings['frontend']['slug'])) {
            return '';
        }

        return site_url($settings['frontend']['slug']) . '#/';
    }

    protected function getFronendSlug()
    {
        if (defined('FLUENT_BOOKING_FRONT_SLUG') && FLUENT_BOOKING_FRONT_SLUG) {
            return FLUENT_BOOKING_FRONT_SLUG;
        }

        static $slug = null;

        if ($slug !== null) {
            return $slug;
        }

        $settings = Helper::getPrefSettins();

        $renderType = empty($settings['frontend']['render_type']) ? 'standalone' : $settings['frontend']['render_type'];

        if ($renderType != 'standalone') {
            $slug = '';
            return $slug;
        }

        $slug = $settings['frontend']['slug'];

        return $slug;

    }

    protected function getAuthContent()
    {
        $loginContent = '';
        if (defined('FLUENT_AUTH_PLUGIN_PATH')) {
            add_filter('fluent_boards/asset_listed_slugs', function ($slugs) {
                $slugs[] = '\/fluent-security\/';
                return $slugs;
            });

            $authHandler = new \FluentAuth\App\Hooks\Handlers\CustomAuthHandler();

            if ($authHandler->isEnabled()) {
                $loginContent = do_shortcode('[fluent_auth_login redirect_to="self"]');
            }
        }

        if (!$loginContent) {
            $loginContent = wp_login_form([
                'echo'     => false,
                'redirect' => Helper::getAppBaseUrl()
            ]);
        }

        return '<div class="fbs_login_form"><div class="fbs_login_form_heading">' . apply_filters('fluent_boards/login_header', __('Please login to view the bookings', 'fluent-booking-pro')) . '</div><div class="fbs_login_wrap">' . $loginContent . '</div></div>';
    }
}
