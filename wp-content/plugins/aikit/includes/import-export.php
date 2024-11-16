<?php

class AIKit_Import_Export_Manager {

    const VERSION = '1.0.0';

    private static $instance = null;

    private $prompt_manager;

    public static function get_instance($prompt_manager) {
        if (self::$instance == null) {
            self::$instance = new AIKit_Import_Export_Manager();
            self::$instance->prompt_manager = $prompt_manager;
        }
        return self::$instance;
    }

    function export_settings_and_prompts () {
        $prompts = get_option('aikit_prompts');
        $imageStyles = get_option('aikit_setting_images_styles');
        $imageCounts = get_option('aikit_setting_images_counts');
        $language = get_option('aikit_setting_openai_language');
        $model = get_option('aikit_setting_openai_model');
        $maxTokensMultiplier = get_option('aikit_setting_openai_max_tokens_multiplier');
        $autoCompleteTextColor = get_option('aikit_setting_autocompleted_text_background_color');
        $elementorSupported = get_option('aikit_setting_elementor_supported');
        $imageSizesSmall = get_option('aikit_setting_images_size_small');
        $imageSizesMedium = get_option('aikit_setting_images_size_medium');
        $imageSizesLarge = get_option('aikit_setting_images_size_large');
        $systemMessage = get_option('aikit_setting_openai_system_message');

        $json = array(
            'pluginVersion' => aikit_get_plugin_version(),
            'version' => self::VERSION,
            'imageStyles' => $imageStyles,
            'imageCounts' => $imageCounts,
            'language' => $language,
            'model' => $model,
            'maxTokensMultiplier' => floatval($maxTokensMultiplier),
            'autoCompleteTextColor' => $autoCompleteTextColor,
            'elementorSupported' => boolval($elementorSupported),
            'imageSizesSmall' => boolval($imageSizesSmall),
            'imageSizesMedium' => boolval($imageSizesMedium),
            'imageSizesLarge' => boolval($imageSizesLarge),
            'prompts' => $prompts,
            'systemMessage' => $systemMessage,
        );

        $promptsOnly = array(
            'pluginVersion' => aikit_get_plugin_version(),
            'version' => self::VERSION,
            'prompts' => $prompts,
        );

        return array(
            'all' => json_encode($json),
            'promptsOnly' => json_encode($promptsOnly),
        );
    }

    function import_settings_and_prompts($json) {
        $json = json_decode($json, true);

        if (isset($json['imageStyles'])) {
            update_option('aikit_setting_images_styles', $json['imageStyles']);
        }

        if (isset($json['imageCounts'])) {
            update_option('aikit_setting_images_counts', $json['imageCounts']);
        }

        if (isset($json['language'])) {
            update_option('aikit_setting_openai_language', $json['language']);
        }

        if (isset($json['model'])) {
            update_option('aikit_setting_openai_model', $json['model']);
        }

        if (isset($json['maxTokensMultiplier'])) {
            update_option('aikit_setting_openai_max_tokens_multiplier', $json['maxTokensMultiplier']);
        }

        if (isset($json['autoCompleteTextColor'])) {
            update_option('aikit_setting_autocompleted_text_background_color', $json['autoCompleteTextColor']);
        }

        if (isset($json['elementorSupported'])) {
            update_option('aikit_setting_elementor_supported', $json['elementorSupported']);
        }

        if (isset($json['imageSizesSmall'])) {
            update_option('aikit_setting_images_size_small', $json['imageSizesSmall']);
        }

        if (isset($json['imageSizesMedium'])) {
            update_option('aikit_setting_images_size_medium', $json['imageSizesMedium']);
        }

        if (isset($json['imageSizesLarge'])) {
            update_option('aikit_setting_images_size_large', $json['imageSizesLarge']);
        }

        if (isset($json['prompts']) && $json['prompts'] !== false) {
            update_option('aikit_prompts', $json['prompts']);

            // get a vertical slice array for all prompts for a given language
            $promptsByLang = $this->prompt_manager->build_prompts_by_language($json['prompts']);

            foreach ($promptsByLang as $lang => $obj) {
                // save prompts for each language as options
                update_option('aikit_prompts_' . $lang, $obj);
            }
        }

        if (isset($json['systemMessage'])) {
            update_option('aikit_setting_openai_system_message', $json['systemMessage']);
        }
    }

    public function export_import_settings_page()
    {
        $settings = $this->export_settings_and_prompts();
        ?>

        <div class="wrap">
            <h1><?php echo esc_html__( 'AIKit Export/Import Settings', 'aikit' ); ?></h1>
            <h2><?php echo esc_html__( 'Export Settings', 'aikit' ); ?></h2>
            <p><?php echo esc_html__( 'Download the current settings and prompts as a json file.', 'aikit' ); ?></p>
            <a href="data:application/octet-stream;base64,<?php echo base64_encode($settings['all']); ?>" download="aikit-all-settings-<?php echo time(); ?>.json"><?php echo esc_html__( 'Export All Settings + Prompts', 'aikit' ); ?></a>
            <br />
            <a href="data:application/octet-stream;base64,<?php echo base64_encode($settings['promptsOnly']); ?>" download="aikit-prompts-<?php echo time(); ?>.json"><?php echo esc_html__( 'Export Prompts only', 'aikit' ); ?></a>

            <h2><?php echo esc_html__( 'Import Settings', 'aikit' ); ?></h2>
            <p><?php echo esc_html__( 'Upload a settings json file to import settings and/or prompts.', 'aikit' ); ?></p>
            <p><strong><?php echo esc_html__( 'Important:', 'aikit' ); ?></strong> <?php echo esc_html__( 'It\'s highly recommended to backup your current settings before importing new settings, so you can revert back if needed.', 'aikit' ); ?></p>
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="aikit_import_settings" value="1" />
                <input type="file" name="aikit_import_settings_file" />
                <br /> <br />
                <div>
                    <input type="submit" class="button button-primary" value="<?php echo esc_html__( 'Import Settings', 'aikit' ); ?>" />
                </div>
            </form>
        </div>
        <?php

        // if the user has submitted the form to import settings and prompts
        if (isset($_POST['aikit_import_settings'])) {
            // check the file was uploaded
            if (isset($_FILES['aikit_import_settings_file'])) {
                // check the file is a json file
                if ($_FILES['aikit_import_settings_file']['type'] == 'application/json') {
                    // read the file
                    $json = file_get_contents($_FILES['aikit_import_settings_file']['tmp_name']);
                    // import the settings and prompts
                    $this->import_settings_and_prompts($json);
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings were imported successfully.', 'aikit') .  '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('File must be a json file.', 'aikit') .  '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('File not uploaded.', 'aikit') . '</p></div>';
            }
        }
    }
}
