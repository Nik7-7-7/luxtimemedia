<?php

/**
 * The code that runs during plugin activation.
 */


if(!function_exists('trp_in_sp_activator')){
    function trp_in_sp_activator( $addon ){
        if ( $addon === 'tp-add-on-seo-pack/tp-seo-pack.php' ) {
            trp_in_sp_wpseo_clear_sitemap();
        }
    }

    add_action('trp_add_ons_activate', 'trp_in_sp_activator', 10, 1);
    add_action('trp_add_ons_deactivate','trp_in_sp_activator', 10, 1);


    function trp_in_sp_activator_actions( $addon ){
        if ( $addon === 'tp-add-on-seo-pack/tp-seo-pack.php' ) {
            trp_in_sp_create_db_tables();
            trp_in_sp_check_if_migration_is_necessary_and_show_notice_to_run_database();
            trp_in_sp_add_woo_translations();
        }
    }

    add_action('trp_add_ons_activate', 'trp_in_sp_activator_actions', 10, 1);
}

if(!function_exists('trp_in_sp_wpseo_clear_sitemap')) {
    function trp_in_sp_wpseo_clear_sitemap() {
        global $wpdb;
        // delete all "yst_sm" transients
        $sql = "
            DELETE
            FROM {$wpdb->options}
            WHERE option_name like '\_transient\_yst\_sm%'
            OR option_name like '\_transient\_timeout\_yst\_sm%'
        ";

        $wpdb->query( $sql );
    }
}

function trp_in_sp_create_db_tables(){
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    global $wpdb;

    $trp = TRP_Translate_Press::get_trp_instance();

    $error_manager = $trp->get_component( 'error_manager' );

    /*
     * @IMPORTANT
     * The names of the tables are changed from trp_slug_original -> trp_slug_originals
     *                                          trp_slug_translation -> trp_slug_translations
     * This is caused by the unsuccessful slug refactor update that created the slugs tables in db, and we want to recreate them
     */
    $original_table_name    = $wpdb->prefix . 'trp_slug_originals';
    $translation_table_name = $wpdb->prefix . 'trp_slug_translations';
    $post_table_name        = $wpdb->posts;
    $database_name          = DB_NAME;

    $sql_get_posts_collation = $wpdb->prepare("SELECT table_collation FROM information_schema.tables WHERE table_schema = %s AND table_name = %s", $database_name, $post_table_name);
    $collation               = $wpdb->get_var( $sql_get_posts_collation ); // Match wp posts collation

    $original_table_exists    = $wpdb->get_var("SHOW TABLES LIKE '$original_table_name'") == $original_table_name;
    $translation_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$translation_table_name'") == $translation_table_name;

    if ( !$original_table_exists ) {
        $sql_original = "CREATE TABLE $original_table_name (
            id INT NOT NULL AUTO_INCREMENT,
            original VARCHAR(200) NOT NULL,
            type VARCHAR(50),
            PRIMARY KEY (id),
            INDEX idx_original (original),
            UNIQUE (original(191))
        ) COLLATE $collation";

        // use prefix for original UNIQUE index due to key length constraints
        dbDelta( $sql_original );

        if ( $wpdb->last_error ) {
            $error_details = [
                'last_error_create_original'     => $wpdb->last_error,
                'message'                        => "Error creating $original_table_name table",
                'disable_automatic_translations' => true
            ];

            $error_manager->record_error( $error_details );
        }
    }

    if ( !$translation_table_exists ) {
        $sql_translation = "CREATE TABLE $translation_table_name (
            id INT NOT NULL AUTO_INCREMENT,
            original_id INT NOT NULL,
            translated VARCHAR(255) NOT NULL,
            language VARCHAR(20),
            status INT,
            PRIMARY KEY (id),
            UNIQUE INDEX idx_original_id_language (original_id, language),
            INDEX idx_language (language),
            INDEX idx_translated (translated),
            FOREIGN KEY (original_id) REFERENCES $original_table_name(id) ON DELETE CASCADE
        ) COLLATE $collation";

        dbDelta( $sql_translation );

        if ( $wpdb->last_error ) {
            $error_details = [
                'last_error_create_translated'   => $wpdb->last_error,
                'message'                        => "Error creating $translation_table_name table",
                'disable_automatic_translations' => true
            ];

            $error_manager->record_error( $error_details );
        }
    }
}

function trp_in_sp_check_if_migration_is_necessary_and_show_notice_to_run_database(){

    $array_of_option_names = ['trp_migrate_old_slug_to_new_parent_and_translate_slug_table_post_type_and_tax_284','trp_migrate_old_slug_to_new_parent_and_translate_slug_table_post_meta_284','trp_migrate_old_slug_to_new_parent_and_translate_slug_table_term_meta_284'];
    foreach ($array_of_option_names as $option ){
        $option_result = get_option( $option, 'not_set' );

        if ( $option_result === 'yes' ){
            continue;
        }

        if ( $option_result === 'seopack_inactive' ){
            update_option($option,'no');
            delete_option('trp_show_error_db_message');
            continue;
        }

        if ( $option_result === 'no' && class_exists( 'TRP_Slug_Query' ) ){
            delete_option('trp_show_error_db_message');
            continue;
        }

        $old_translation_exist = get_option( 'trp_taxonomy_slug_translation', 'not_set' );
        if ( $old_translation_exist === 'not_set' ) {
            global $wpdb;
            $old_slug_translations_exist = $wpdb->get_var("SELECT meta_id FROM " . $wpdb->postmeta . " WHERE `meta_key` LIKE '%_trp%' LIMIT 1" );
            $old_slug_translations_exist = !empty( $old_slug_translations_exist );
            if ( !$old_translation_exist){
                $old_slug_translations_exist = $wpdb->get_var("SELECT meta_id FROM " . $wpdb->termmeta . " WHERE `meta_key` LIKE '%_trop%' LIMIT 1" );
                $old_slug_translations_exist = !empty( $old_slug_translations_exist );
            }
        }else{
            $old_slug_translations_exist = true;
        }
        if ( $option_result === 'not_set' && $old_slug_translations_exist ) {
            update_option($option,'no');
            delete_option('trp_show_error_db_message');
        }
    }
}

function trp_in_sp_add_woo_translations(){
    $was_data_migration_completed = get_option( 'trp_migrate_old_slug_to_new_parent_and_translate_slug_table_term_meta_284', 'not_set' );

    // new installs only
    if ( $was_data_migration_completed == 'not_set' ){
        update_option('trp_in_sp_add_gettext_slugs', 'todo');
    }
}

add_action('wp_loaded', 'trp_remove_woo_compat_function_284', 1);
function trp_remove_woo_compat_function_284() {
    remove_action( 'wp_loaded', 'trp_check_condition_and_flush_rewrite_rules' );
}
