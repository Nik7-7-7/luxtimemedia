<?php

class TRP_String_Translation_Array{
    protected $settings;
    protected $array_of_original_slugs;
    protected $translations_arrays;
    protected $formatted_translations_array;
    protected $slug_type;
    protected $slug_query;

    public function __construct( $array_of_original_slugs, $array_of_translations, $slug_type ) {
        $this->init( $array_of_translations, $slug_type );
        $this->insert_missing_original_slugs( $array_of_original_slugs );
        $this->format_translation_array();
    }

    public function get_formatted_translations_array(){
        return $this->formatted_translations_array;
    }

    protected function init( $sql_results, $slug_type ){
        $trp                = TRP_Translate_Press::get_trp_instance();
        $trp_settings       = $trp->get_component( 'settings' );

        $this->translations_arrays          = $sql_results;
        $this->formatted_translations_array = [];
        $this->slug_type                    = $slug_type;
        $this->settings                     = $trp_settings->get_settings();
        $this->slug_query                   = new TRP_Slug_Query();
    }

    protected function prepare_original_slugs_array( $array_of_original_slugs ){
        $prepared_array = [];

        foreach ( $array_of_original_slugs as $slug ){
            $prepared_array[] = [
              'original' => $slug,
              'type'     => $this->slug_type
            ];
        }

        return $prepared_array;
    }

    protected function insert_missing_original_slugs( $array_of_original_slugs ){
        $prepared_array = $this->prepare_original_slugs_array( $array_of_original_slugs );

        $db_inserted_array = $this->slug_query->insert_original_slugs( $prepared_array );

        if ( $db_inserted_array === false ) return;

        $this->array_of_original_slugs = $db_inserted_array;
    }

    public function format_translation_array(){
        $formatted_array = [];

        if ( empty( $this->array_of_original_slugs ) ) return;

        foreach ( $this->array_of_original_slugs as $original_slug => $original_id ){
            foreach ( $this->settings['translation-languages'] as $language_key ){
                if ( $language_key === $this->settings['default-language'] ){
                    continue;
                }

                if ( isset( $this->translations_arrays[$original_id][$language_key] ) ){
                    $current_translations_array = $this->translations_arrays[$original_id][$language_key];

                    $formatted_array[$original_slug][$language_key] = [
                        'editedTranslation' => $current_translations_array['translated'],
                        'translated'        => $current_translations_array['translated'],
                        'status'            => $current_translations_array['status'],
                        'original_id'       => $current_translations_array['original_id'],
                        'translation_id'    => $current_translations_array['id']
                    ];
                }

                else {
                    $formatted_array[$original_slug][$language_key] = [
                        'editedTranslation' => '',
                        'translated'        => '',
                        'status'            => 0,
                        'original_id'       => $original_id,
                        'translation_id'    => ''
                    ];
                }

            }
        }

        $this->formatted_translations_array = $formatted_array;
    }

}