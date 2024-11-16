<?php

namespace FluentBoardsPro\App\Services;

class HelperInstaller
{
    public function register()
    {
        add_action('plugins_loaded', function () {
            if (!defined('FLUENT_BOARDS')) {
                $this->initInstaller();
            }

            add_action('wp_ajax_fluent_boards_installer', [$this, 'ajaxHandler']);
        });
    }

    public function ajaxHandler()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json(['success' => false, 'message' => 'You do not have permission to install plugins'], 422);
        }

        if (defined('FLUENT_BOARDS')) {
            wp_send_json([
                'message'  => 'Plugins has been installed successfully',
                'plugins'  => [
                    'fluent-boards' => defined('FLUENT_BOARDS'),
                    'fluent-crm'    => defined('FLUENTCRM'),
                ],
                'redirect' => fluent_boards_page_url()
            ]);
        }

        // Install Fluent Boards
        $this->installFluentBoards();
        $this->installFluentCRM();

        if (!defined('FLUENT_BOARDS')) {
            wp_send_json(['success' => false, 'message' => 'Sorry, we could not install the required plugin. Please try again'], 422);
        }

        wp_send_json([
            'message'  => 'Plugins has been installed successfully',
            'plugins'  => [
                'fluent-boards' => defined('FLUENT_BOARDS'),
                'fluent-crm'    => defined('FLUENTCRM'),
            ],
            'redirect' => fluent_boards_page_url()
        ]);
    }

    public function initInstaller()
    {

        // add admin notice
        add_action('admin_notices', function () {

            if(isset($_GET['page']) && $_GET['page'] == 'fluent-boards-pro-installer'){
                return;
            }
            ?>
            <div class="notice notice-warning is-dismissible">
                <h3>Thank you for installing FluentBoards Pro. Please configure Fluent Boards</h3>
                <a href="<?php echo admin_url('?page=fluent-boards-pro-installer'); ?>" class="button button-primary">Configure Fluent Boards</a>
                <p></p>
            </div>
            <?php
        });

        add_action('admin_menu', [$this, 'addInstallerMenu'], 11);
    }

    public function addInstallerMenu()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        add_menu_page(
            __('Fluent Boards', 'fluent-boards-pro'),
            __('Fluent Boards', 'fluent-boards-pro'),
            'manage_options',
            'fluent-boards-pro-installer',
            [$this, 'installerPage'],
            'dashicons-welcome-widgets-menus',
            2
        );
    }

    public function installerPage()
    {
        $btnTitle = __('Install FluentBoards Core Plugin', 'fluent-boards-pro');
        if (!defined('FLUENTCRM')) {
            $btnTitle = __('Install FluentCRM & Core Plugin', 'fluent-boards-pro');
        }
        ?>
        <style>
            .fsb_installer_wrap {
                background: #fff;
                padding: 20px;
                margin: 100px auto 0;
                text-align: center;
                max-width: 600px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
        </style>
        <div class="wrap">
            <div class="fsb_installer_wrap">
                <h1><?php _e('Welcome to FluentBoards', 'fluent-boards-pro'); ?></h1>
                <p><?php _e('Thank you for installing Fluent Boards Pro Plugin. Fluent Boards require base plugin & FluentCRM to get you started.', 'fluent-boards-pro'); ?></p>
                <form id="fluent_boards_installer" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">
                    <input type="hidden" name="action" value="fluent_boards_installer"/>
                    <?php wp_nonce_field('fluent_boards_installer_nonce', 'fluent_boards_installer_nonce'); ?>
                    <button type="submit" name="install_fluent_boards" class="button button-primary">
                        <?php echo $btnTitle; ?>
                    </button>
                </form>
                <div style="margin-top: 10px;" class="fsb_installer_message"></div>
            </div>
        </div>
        <?php

        add_action('admin_footer', function () {

            $directUrl = 'https://wpcolorlab.s3.amazonaws.com/fluent-boards.zip';

            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#fluent_boards_installer').on('submit', function (e) {
                        e.preventDefault();
                        var $form = $(this);
                        var data = $form.serialize();
                        var $button = $form.find('button[type="submit"]');
                        $button.attr('disabled', 'disabled');
                        $.post($form.attr('action'), data)
                            .then(response => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    alert(response.data.message);
                                }
                            })
                            .catch(error => {
                                let errorMessage = error.responseText;
                                if (error && error.responseJSON && error.responseJSON.message) {
                                    errorMessage = error.responseJSON.message;
                                }
                                $('.fsb_installer_message').html('<div class="notice notice-error"><h3>Something is wrong when installing the plugin. <a target="_blank" href="<?php echo $directUrl; ?>">Please download the base plugin and install manually from plugins menu.</a></h3><pre>' + errorMessage + '</pre></div>');
                            })
                            .always(() => {
                                $button.removeAttr('disabled');
                            });
                    });
                });
            </script>
            <?php
        });
    }

    private function installFluentBoards()
    {
        if (defined('FLUENT_BOARDS')) {
            return true;
        }

        $plugin = [
            'name'      => 'FluentBoards',
            'repo-slug' => 'fluent-boards',
            'file'      => 'fluent-boards.php',
        ];

        $plugin_id = 'fluent-boards';

        $this->repoBackgroundInstaller($plugin, $plugin_id);
    }

    private function installFluentCRM()
    {
        if (defined('FLUENTCRM')) {
            return true;
        }

        $plugin_id = 'fluent-crm';
        $plugin = [
            'name'      => 'Fluent CRM',
            'repo-slug' => 'fluent-crm',
            'file'      => 'fluent-crm.php',
        ];
        $this->repoBackgroundInstaller($plugin, $plugin_id);
    }

    private function repoBackgroundInstaller($plugin_to_install, $plugin_id)
    {
        if (!empty($plugin_to_install['repo-slug'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            WP_Filesystem();

            $skin = new \Automatic_Upgrader_Skin();
            $upgrader = new \WP_Upgrader($skin);
            $installed_plugins = array_reduce(array_keys(\get_plugins()), array($this, 'associate_plugin_file'), array());
            $plugin_slug = $plugin_to_install['repo-slug'];
            $plugin_file = isset($plugin_to_install['file']) ? $plugin_to_install['file'] : $plugin_slug . '.php';
            $installed = false;
            $activate = false;

            // See if the plugin is installed already.
            if (isset($installed_plugins[$plugin_file])) {
                $installed = true;
                $activate = !is_plugin_active($installed_plugins[$plugin_file]);
            }

            // Install this thing!
            if (!$installed) {
                // Suppress feedback.
                ob_start();

                try {
                    $plugin_information = plugins_api(
                        'plugin_information',
                        array(
                            'slug'   => $plugin_slug,
                            'fields' => array(
                                'short_description' => false,
                                'sections'          => false,
                                'requires'          => false,
                                'rating'            => false,
                                'ratings'           => false,
                                'downloaded'        => false,
                                'last_updated'      => false,
                                'added'             => false,
                                'tags'              => false,
                                'homepage'          => false,
                                'donate_link'       => false,
                                'author_profile'    => false,
                                'author'            => false,
                            ),
                        )
                    );

                    if (is_wp_error($plugin_information)) {
                        throw new \Exception($plugin_information->get_error_message());
                    }

                    $package = $plugin_information->download_link;
                    $download = $upgrader->download_package($package);

                    if (is_wp_error($download)) {
                        throw new \Exception($download->get_error_message());
                    }

                    $working_dir = $upgrader->unpack_package($download, true);

                    if (is_wp_error($working_dir)) {
                        throw new \Exception($working_dir->get_error_message());
                    }

                    $result = $upgrader->install_package(
                        array(
                            'source'                      => $working_dir,
                            'destination'                 => WP_PLUGIN_DIR,
                            'clear_destination'           => false,
                            'abort_if_destination_exists' => false,
                            'clear_working'               => true,
                            'hook_extra'                  => array(
                                'type'   => 'plugin',
                                'action' => 'install',
                            ),
                        )
                    );

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }

                    $activate = true;

                } catch (\Exception $e) {
                }

                // Discard feedback.
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing.
            if ($activate) {
                try {
                    $result = activate_plugin($installed ? $installed_plugins[$plugin_file] : $plugin_slug . '/' . $plugin_file);

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    /*
     * Install Plugins with direct download link ( which doesn't have wordpress.org repo )
     */
    public function backgroundInstaller($plugin_to_install, $plugin_id, $downloadUrl)
    {
        if (!empty($plugin_to_install['repo-slug'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            \WP_Filesystem();

            $skin = new \Automatic_Upgrader_Skin();
            $upgrader = new \WP_Upgrader($skin);
            $installed_plugins = array_reduce(array_keys(\get_plugins()), array($this, 'associate_plugin_file'), array());
            $plugin_slug = $plugin_to_install['repo-slug'];
            $plugin_file = isset($plugin_to_install['file']) ? $plugin_to_install['file'] : $plugin_slug . '.php';
            $installed = false;
            $activate = false;

            // See if the plugin is installed already.
            if (isset($installed_plugins[$plugin_file])) {
                $installed = true;
                $activate = !is_plugin_active($installed_plugins[$plugin_file]);
            }

            // Install this thing!
            if (!$installed) {
                // Suppress feedback.
                ob_start();

                try {
                    $package = $downloadUrl;
                    $download = $upgrader->download_package($package);

                    if (is_wp_error($download)) {
                        throw new \Exception($download->get_error_message());
                    }

                    $working_dir = $upgrader->unpack_package($download, true);

                    if (is_wp_error($working_dir)) {
                        throw new \Exception($working_dir->get_error_message());
                    }

                    $result = $upgrader->install_package(
                        array(
                            'source'                      => $working_dir,
                            'destination'                 => WP_PLUGIN_DIR,
                            'clear_destination'           => false,
                            'abort_if_destination_exists' => false,
                            'clear_working'               => true,
                            'hook_extra'                  => array(
                                'type'   => 'plugin',
                                'action' => 'install',
                            ),
                        )
                    );

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }

                    $activate = true;

                } catch (\Exception $e) {
                }

                // Discard feedback.
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing.
            if ($activate) {
                try {
                    $result = activate_plugin($installed ? $installed_plugins[$plugin_file] : $plugin_slug . '/' . $plugin_file);

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    private function associate_plugin_file($plugins, $key)
    {
        $path = explode('/', $key);
        $filename = end($path);
        $plugins[$filename] = $key;
        return $plugins;
    }

}
