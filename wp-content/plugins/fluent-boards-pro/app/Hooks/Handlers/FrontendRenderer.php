<?php

namespace FluentBoardsPro\App\Hooks\Handlers;


use FluentBoards\App\Hooks\Handlers\AdminMenuHandler;
use FluentBoards\App\Services\PermissionManager;
use FluentBoardsPro\App\Core\App;

class FrontendRenderer
{
    public function register()
    {

        add_action('init', function () {
            $frontAppUrl = \FluentBoardsPro\App\Services\ProHelper::getFrontAppUrl();

            if (!$frontAppUrl) {
                return;
            }

            add_filter('fluent_boards/app_url', function ($url) use ($frontAppUrl) {
                return $frontAppUrl;
            }, 100);

            add_action('template_redirect', function () {
                $frontSlug = \FluentBoardsPro\App\Services\ProHelper::getFrontEndSlug();

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

        add_shortcode('fluent_boards', [$this, 'renderFrontendShortcode']);
    }

    public function renderFullApp()
    {

        add_action('fluent_boards/in_menu_actions', function () {
            echo '<a title="Logout" href="' . wp_logout_url(site_url()) . '" class="fbs_menu_action_item"><span><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M17 16L21 12M21 12L17 8M21 12L7 12M13 16V17C13 18.6569 11.6569 20 10 20H6C4.34315 20 3 18.6569 3 17V7C3 5.34315 4.34315 4 6 4H10C11.6569 4 13 5.34315 13 7V8" stroke="#374151" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></span></a>';
        });

        add_filter('fluent_boards/app_logo', function ($logo) {
            if (function_exists('get_theme_mod')) {
                $custom_logo_id = get_theme_mod('custom_logo');
                $custom_logo = wp_get_attachment_url($custom_logo_id);
                if ($custom_logo) {
                    return $custom_logo;
                }
            }
            return $logo;
        }, 9, 1);

        // remove wp admin bar
        add_filter('show_admin_bar', '__return_false');

        $app = App::getInstance();

        if (get_current_user_id()) {
            $content = (string)$this->getRenderedContent();
        } else {
            $content = $this->getAuthContent();
        }

        $app->view->render('front-app', [
            'content' => $content
        ]);

        exit;
    }

    public function renderFrontendShortcode()
    {
        add_filter('fluent_boards/app_url', function () {
            $page = get_the_permalink();
            return $page . '#/';
        }, 100);

        add_filter('fluent_boards/app_icon', function ($logo) {
            if (function_exists('get_theme_mod')) {
                $custom_logo_id = get_theme_mod('custom_logo');
                $custom_logo = wp_get_attachment_url($custom_logo_id);
                if ($custom_logo) {
                    return $custom_logo;
                }
            }
            return $logo;
        }, 9, 1);

        add_filter('fluent_boards/skip_no_conflict', '__return_true');

        wp_enqueue_style('fluent-boards-pro-frontend', FLUENT_BOARDS_PRO_URL . 'dist/css/frontend.css', [], FLUENT_BOARDS_PRO_VERSION);

        if (!get_current_user_id()) {
            return $this->getAuthContent();
        }

        if (!PermissionManager::userHasAnyBoardAccess(get_current_user_id())) {
            return '<div class="fbs_no_permission">' . apply_filters('fluent_boards/no_permission_message', __('You do not have permission to view the projects', 'fluent-boards-pro')) . '</div>';
        }

        return (string)$this->getRenderedContent('fbs_page_render');
    }

    protected function getRenderedContent($extraClass = '')
    {

        add_filter('fluent_boards/menu_items', function ($items) {

            unset($items['help']);

            return $items;

        });

        (new AdminMenuHandler())->enqueueAssets();

        ob_start();

        echo '<div class="fluent_boards_frontend fbs_front ' . esc_attr($extraClass) . '">';

        (new AdminMenuHandler())->render();

        echo '</div>';

        return ob_get_clean();
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
                'redirect' => fluent_boards_page_url()
            ]);
        }

        return '<div class="fbs_login_form"><div class="fbs_login_form_heading">' . apply_filters('fluent_boards/login_header', __('Please login to view the project', 'fluent-boards-pro')) . '</div><div class="fbs_login_wrap">' . $loginContent . '</div></div>';
    }
}
