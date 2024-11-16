<?php

class TRP_IN_SP_Editor_Actions{
    protected $slug_query; /** @var TRP_Slug_Query */
    protected $settings;

    public function __construct( $slug_query, $settings ){
        $this->slug_query = $slug_query;
        $this->settings   = $settings;
    }
    public function save_slugs( $slugs, $type = null ) {
        $update_slugs = array();
        global $wpdb;

        foreach ( $slugs as $language => $language_slugs ) {
            if ( in_array( $language, $this->settings['translation-languages'] ) && $language != $this->settings['default-language'] ) {
                $update_slugs[ $language ] = array();
                foreach ( $language_slugs as $key => $slug ) {

                    $slug = (array) $slug;
                    if ( isset( $slug['original_id'] ) ) {
                        $other_type_occurrences = $this->check_if_slug_has_other_occurrences( $slug['original'], $type );

                        $update_slugs[ $language ][ $key ] = array(
                            'original_id'    => (int)$slug['original_id'],
                            'translation_id' => (int)$slug['translation_id'],
                            'translated'     => $slug['translated'],
                            'status'         => (int)$slug['status']
                        );

                        if ( $other_type_occurrences )
                            $update_slugs[ $language ][ $key ][ 'other_type_occurrences' ] = $other_type_occurrences;
                    }
                }
            }
        }

        foreach ( $update_slugs as $language => $update_slugs_array ) {
            foreach ( $update_slugs_array as $slug_key => $slug ) {
                if ( !empty( $slug['original_id'] ) ) {
                    $sanitized_slug                                       = sanitize_title( $slug['translated'] );
                    $sanitized_slug                                       = urldecode( $sanitized_slug );
                    $update_slugs[ $language ][ $slug_key ]               = $slug;
                    $update_slugs[ $language ][ $slug_key ]['translated'] = $sanitized_slug;

                    $slug_db_array_insert = [
                        'original_id' => (int)$slug['original_id'],
                        'translated'  => $sanitized_slug,
                        'status'      => 2
                    ];

                    if ( empty( $slug['translation_id'] ) ) {
                        $inserted_slugs = $this->slug_query->insert_translated_slugs( [ $slug_db_array_insert ], $language );

                        $translation_id = $wpdb->insert_id;

                        $update_slugs[ $language ][ $slug_key ]['translation_id'] = $translation_id;

                        $new_slug = $inserted_slugs[0]['translated'];
                    } else {
                        if ( $sanitized_slug === "" ) {
                            $this->slug_query->delete_translations_by_ids( (array)$slug['translation_id'] );

                            continue;
                        }

                        $slug_db_array_update = [
                            'original_id' => (int)$slug['original_id'],
                            'id'         => (int)$slug['translation_id'],
                            'translated' => $sanitized_slug,
                            'status'     => 2
                        ];

                        $updated_slugs = $this->slug_query->update_slugs( [ $slug_db_array_update ], $language );

                        if ( $updated_slugs == 0 ){

                            $inserted_slugs_in_same_request = $this->slug_query->insert_translated_slugs( [ $slug_db_array_insert ], $language );

                            $translation_id = $wpdb->insert_id;
                            $update_slugs[ $language ][ $slug_key ]['translation_id'] = $translation_id;

                            $new_slug = $inserted_slugs_in_same_request[0]['translated'];

                        }else {
                            $new_slug = $updated_slugs[0]['translated'];
                        }
                    }

                    if ( $new_slug !== $sanitized_slug ) $update_slugs[ $language ][ $slug_key ]['translated'] = $new_slug;
                }

                if ( isset( $type ) ) do_action( "trp_update_{$type}_slug", $slug, $language, $update_slugs );
            }
        }

        return $update_slugs;
    }

    /**
     * Checks if the slug is found as a { post / term / taxonomy / post-type-base } slug
     *
     * Returns false in case the slug is unique across all slug types
     *         an array (containing the matching types) in case the slug is found in another types
     *
     * @param string $slug
     * @param string $type
     * @return false | array
     */
    private function check_if_slug_has_other_occurrences( $slug, $type ){
        $slug_found_in_types = [];

        if ( $type !== 'taxonomy' ){
            $taxonomies = get_taxonomies(['public' => true ], 'objects');

            foreach ( $taxonomies as $taxonomy ) {
                if ( $taxonomy->rewrite !== false && isset( $taxonomy->rewrite['slug'] ) && $taxonomy->rewrite['slug'] === $slug )
                    $slug_found_in_types[] = 'taxonomy';
            }
        }

        if ( $type !== 'term' ){
            $terms = get_terms([
                'slug' => $slug,
                'hide_empty' => false,
            ]);

            if ( !empty($terms) )
                $slug_found_in_types[] = 'term';
        }

        if ( $type !== 'post' ){
            $posts = get_posts([
                'name' => $slug,
                'post_type' => 'any',
                'post_status' => 'any',
                'numberposts' => 1,
            ]);

            if ( !empty( $posts ) )
                $slug_found_in_types[] = 'post';
        }

        if ( $type !== 'post-type-base' ){
            $post_types = get_post_types( ['publicly_queryable' => true], 'objects' );

            foreach ( $post_types as $post_type ) {
                if ( $post_type->rewrite !== false && isset( $post_type->rewrite['slug'] ) && $post_type->rewrite['slug'] === $slug )
                    $slug_found_in_types[] = 'post-type-base';
            }
        }

        return !empty( $slug_found_in_types ) ? $slug_found_in_types : false; // false in case the slug is unique
    }

}