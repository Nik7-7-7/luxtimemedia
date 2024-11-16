<?php

/** Functions useful for term slugs and post slugs */
class TRP_IN_SP_Meta_Based_Strings{

    protected $human_translated_slug_meta     = '_trp_translated_slug_';
    protected $automatic_translated_slug_meta = '_trp_automatically_translated_slug_';

    /**
     * @return string
     */
    public function get_human_translated_slug_meta() {
        return $this->human_translated_slug_meta;
    }

    /**
     * @return string
     */
    public function get_automatic_translated_slug_meta() {
        return $this->automatic_translated_slug_meta;
    }

    public function get_translations_from_meta_table( $ids, $table, $id_column_name ) {
        global $wpdb;
        $placeholders = array();
        $values       = array();

        foreach ( $ids as $id ) {
            $placeholders[]      = '%d';
            $values[]            = $id;
            $translations[ $id ] = array();
        }

        $select_query = "SELECT " . $id_column_name . ", meta_key, meta_value FROM `" . $wpdb->$table . "` ";
        $select_query .= "WHERE ( meta_key LIKE '" . $this->human_translated_slug_meta . "%' OR meta_key LIKE '" . $this->automatic_translated_slug_meta . "%' ) ";
        $select_query .= "AND " . $id_column_name . " IN (" . implode( ", ", $placeholders ) . " )";

        $prepared_query = $wpdb->prepare( $select_query, $values );
        return $wpdb->get_results( $prepared_query, 'ARRAY_A' );
    }

    public function get_translations_array_from_sql_results( $ids, $results, $trp_query, $id_column_name ) {
        $trp                = TRP_Translate_Press::get_trp_instance();
        $trp_settings       = $trp->get_component( 'settings' );
        $settings           = $trp_settings->get_settings();
        $translationsArrays = array();
        foreach ( $ids as $id ) {
            foreach ( $settings['translation-languages'] as $language ) {
                if ( $settings['default-language'] === $language ) {
                    continue;
                }

                $translationsArrays[ $id ][ $language ] = array(
                    'editedTranslation' => '',
                    'translated'        => '',
                    'status'            => $trp_query->get_constant_not_translated(),
                    'id'                => $id,
                );
            }
        }

        if ( $results && count( $results ) > 0 ) {
            foreach ( $results as $key => $result ) {
                foreach ( $settings['translation-languages'] as $language ) {
                    if ( $settings['default-language'] === $language ) {
                        continue;
                    }
                    if ( $result['meta_key'] === $this->automatic_translated_slug_meta . $language && !empty( $result['meta_value'] ) ) {
                        $translationsArrays[ $result[$id_column_name] ][ $language ]['status']     = $trp_query->get_constant_machine_translated();
                        $translationsArrays[ $result[$id_column_name] ][ $language ]['translated'] = $translationsArrays[ $result[$id_column_name] ][ $language ]['editedTranslation'] = $result['meta_value'];
                    }
                    if ( $result['meta_key'] === $this->human_translated_slug_meta . $language && !empty( $result['meta_value'] ) ) {
                        $translationsArrays[ $result[$id_column_name] ][ $language ]['status']     = $trp_query->get_constant_human_reviewed();
                        $translationsArrays[ $result[$id_column_name] ][ $language ]['translated'] = $translationsArrays[ $result[$id_column_name] ][ $language ]['editedTranslation'] = $result['meta_value'];
                    }
                }
            }
        }

        return $translationsArrays;
    }

    public function get_translation_status_wp_query_args( $wp_query_args, $sanitized_args, $trp_query ) {
        $wp_query_args['meta_query'] = array( 'relation' => 'AND' );
        $slug_meta_key_suffix        = ( empty( $sanitized_args['language'] ) ) ? '$' : $sanitized_args['language'];

        if ( in_array( $trp_query->get_constant_not_translated(), $sanitized_args['status'] ) ) {

            // if the request is to show only not translated slugs then both meta_query arrays will be added
            if ( in_array( $trp_query->get_constant_machine_translated(), $sanitized_args['status'] ) || count( $sanitized_args['status'] ) === 1 ) {
                $wp_query_args['meta_query'][] = $this->get_meta_query_array_for_meta_key( $this->human_translated_slug_meta . $slug_meta_key_suffix, false );
            }

            if ( in_array( $trp_query->get_constant_human_reviewed(), $sanitized_args['status'] ) || count( $sanitized_args['status'] ) === 1 ) {
                $wp_query_args['meta_query'][] = $this->get_meta_query_array_for_meta_key( $this->automatic_translated_slug_meta . $slug_meta_key_suffix, false );
            }

        } else {
            $meta_query = array();
            if ( in_array( $trp_query->get_constant_machine_translated(), $sanitized_args['status'] ) ) {
                $meta_query[] = $this->get_meta_query_array_for_meta_key( $this->automatic_translated_slug_meta . $slug_meta_key_suffix, true );
            }
            if ( in_array( $trp_query->get_constant_human_reviewed(), $sanitized_args['status'] ) ) {
                $meta_query[] = $this->get_meta_query_array_for_meta_key( $this->human_translated_slug_meta . $slug_meta_key_suffix, true );
            }

            if ( count( $meta_query ) >= 2 ) {
                // if the request is to show both not translated slugs then both meta_query arrays will be added
                $meta_query['relation']        = "OR";
                $wp_query_args['meta_query'][] = $meta_query;
            } else {
                $wp_query_args['meta_query'] = array_merge( $wp_query_args['meta_query'], $meta_query );
            }

        }

        return $wp_query_args;
    }

    public function get_meta_query_array_for_meta_key( $meta_key, $exists ) {
        $exists = ( $exists ) ? 'EXISTS' : 'NOT EXISTS';
        $return = array(
            'key'     => $meta_key,
            'compare' => $exists
        );
        if ( $exists === 'NOT EXISTS' ) {
            // 'value' needs to be set when NOT EXISTS, otherwise it won't work
            $return['value'] = '';
        }

        return $return;
    }
}