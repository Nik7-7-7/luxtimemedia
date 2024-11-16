<?php

class TRP_Slug_Query{
    private $db, $error_manager;
    private $original_table_name, $translation_table_name;
    private $collation;
    public function __construct(){
        $this->init_dependencies();
    }

    private function init_dependencies(){
        global $wpdb;

        $trp = TRP_Translate_Press::get_trp_instance();

        $this->db            = $wpdb;
        $this->error_manager = $trp->get_component( 'error_manager' );

        $this->original_table_name    = $this->db->prefix . 'trp_slug_originals';
        $this->translation_table_name = $this->db->prefix . 'trp_slug_translations';

        $this->collation = 'utf8mb4_general_ci';
    }

    public function get_original_table_name(){
        return $this->original_table_name;
    }

    public function get_translation_table_name(){
        return $this->translation_table_name;
    }

    public function get_tables_collation(){
        return $this->collation;
    }

    /**
     * Inserts slugs into both original and translation tables.
     *
     * @param array  $array_of_slugs  [ [ 'original' => 'slug_name', 'translated' => 'translated_slug', 'status' => 1 | 2, 'type' (optional) => 'type_of_slug' ], [...] ]
     * @param string $language        Language code.
     * @return bool                   Whether the slugs were inserted successfully.
     */
    public function insert_slugs( $array_of_slugs, $language ){
        $original_ids = $this->insert_original_slugs( $array_of_slugs );

        if ( $original_ids === false ) return false;

        foreach ( $array_of_slugs as &$slug ){
            $original = $slug['original'];

            if ( isset( $original_ids[$original] ) ){
                $slug['original_id'] = $original_ids[$original];
            }
        }

        $successfully_inserted = $this->insert_translated_slugs( $array_of_slugs, $language ); //true if the insertion worked as expected

        return isset( $successfully_inserted ) && $successfully_inserted;
    }

    /**
     * Inserts original slugs into the trp_slug_original table
     *
     * @param array $array_of_slugs  [ [ 'original' => 'slug_name', 'type' (optional) => 'type_of_slug' ], [...] ]
     * @return array | false         $array_of_slugs with original ids or false in case of an error
     */
    public function insert_original_slugs( $array_of_slugs ){
        $insert_values = [];

        foreach ( $array_of_slugs as &$row ){
            if ( isset( $row['original'] ) ){
                $original_slug = strtolower( urlencode( urldecode( $row['original'] ) ) ); // Decode it first so the slug won't break in case it is already encoded

                $row['original']  = $original_slug;
                $type             = isset( $row['type'] ) ? $row['type'] : 'other';
                $insert_values[]  = $this->db->prepare( '(%s, %s)', $original_slug, $type );
                $original_slugs[] = $original_slug;
            }
        }

        if ( empty( $insert_values ) ) return false;

        $sql = "INSERT IGNORE INTO $this->original_table_name (original, type) VALUES " . implode(', ', $insert_values );

        $sql_result = $this->db->query( $sql );

        if ( $sql_result === false ) {
            $this->record_slug_error( 'insert_original' );

            return false;
        }

        return $this->get_ids_from_original( $original_slugs );
    }

    /**
     * Inserts translated slugs into the trp_slug_translation table
     *
     * The array is expected to be under the form
     *
     * @param array $array_of_slugs  [ [ 'original_id' => 'id', 'translated' => 'translated-slug', 'status' => 1 | 2 ], [...] ]
     * @param string $language       Language code.
     * @return bool | array          The array of slugs in case they were inserted successfully or false.
     *
     * @see self::make_slugs_unique()
     */
    public function insert_translated_slugs( $array_of_slugs, $language ){
        $insert_values  = [];

        foreach ( $array_of_slugs as &$slug ){
            if ( isset( $slug['translated'] ) )
                $slug['translated'] =  strtolower( urlencode( urldecode( $slug['translated'] ) ) );; // Make sure that we manipulate the decoded form of the translated slug
        }

        unset( $slug );

        $array_of_slugs = $this->make_slugs_unique( $array_of_slugs, $language );

        foreach ( $array_of_slugs as $slug ) {
            if ( isset( $slug['original_id'] ) && isset( $slug['translated'] ) && isset( $slug['status'] ) ) {
                $insert_values[] = $this->db->prepare( '(%d, %s, %s, %d)', $slug['original_id'], $slug['translated'], $language, $slug['status'] );
            }
        }

        if ( empty( $insert_values ) ) return false;

        $sql = "INSERT IGNORE INTO $this->translation_table_name (original_id, translated, language, status) VALUES " . implode( ', ', $insert_values );

        $sql_result = $this->db->query($sql);

        if ( $sql_result === false ){
            $this->record_slug_error( 'insert_translated' );

            return false;
        }

        return $array_of_slugs;
    }

    /**
     * Ensures uniqueness of slugs by appending a numerical suffix if necessary.
     *
     * Checks for existing slugs in the database and adjusts any duplicates
     * by appending an incrementing suffix to make them unique.
     *
     * It checks against both the original and translated versions of slugs.
     *
     * @param array $array_of_slugs An array of slugs to be checked and made unique.
     * @param string $language The language code of the translations.
     * @return array The modified array of slugs with unique values.
     */
    private function make_slugs_unique( $array_of_slugs, $language ){
        $translations_map = [];
        $original         = [];
        $translated       = [];
        $identical_found  = [];

        foreach ( $array_of_slugs as $key => $slug ){
            if ( !empty( $slug ) && isset( $slug['translated'] ) ){
                $base_of_translated_slug = preg_replace( '/-\d+$/', '', $slug['translated'] );

                $original['select_values'][]     = $slug['translated'];
                $translated['select_values'][]   = $base_of_translated_slug . '%'; // We want to retrieve translations like "slug-2/3/4/5" in order to set the appropiate prefix

                $original['like_query_part'][]   = "original = %s";
                $translated['like_query_part'][] = "translated LIKE %s";

                $translation_key = isset( $slug['original_id'] ) ? $slug['original_id'] : $key;

                $translations[ $translation_key ] = $slug['translated'];
            }
        }

        if ( empty( $original['select_values'] ) ) return $array_of_slugs;

        $original['sql'] = "SELECT original, id FROM {$this->original_table_name} 
                    WHERE " . implode(' OR ', $original['like_query_part'] );

        $translated['sql'] = "SELECT DISTINCT translated FROM {$this->translation_table_name} 
                    WHERE language = '$language' AND (" . implode(' OR ', $translated['like_query_part'] ) . ")";

        $original['prepared_query'] = $this->db->prepare( $original['sql'], $original['select_values'] );

        $translated['prepared_query'] = $this->db->prepare( $translated['sql'], $translated['select_values'] );

        $original['results'] = $this->db->get_results( $original['prepared_query'], ARRAY_A );

        $translated['results'] = $this->db->get_results( $translated['prepared_query'], ARRAY_A );

        if ( !empty( $original['results'] ) ){
            foreach ( $original['results'] as $original_result ){
                $original_slug = urldecode( $original_result['original'] );
                $original_id   = $original_result['id'];

                /**
                 * Check if the inserted translations contain any original slugs.
                 *
                 * A translation can only be equal with its own original.
                 */
                if ( in_array( $original_slug, $translations ) && array_search( $original_slug, $translations ) != $original_id ){
                    $base_of_translated_slug = preg_replace( '/-\d+$/', '', $original_slug );
                    $translations_map[$base_of_translated_slug] = [
                        'available_suffix' => 2
                    ];
                    $identical_found[ $original_slug ] = true;
                }
            }
        }

        if ( !empty( $translated['results'] ) ){
            $translated['results'] = array_column( $translated['results'], 'translated' );

            foreach ( $translations as $translation ) {
                // add suffix only if identical is found
                $identical_slugs = array_filter( $translated['results'], function ( $item ) use ( $translation ) {
                    return $item === $translation;
                } );

                if ( !empty($identical_slugs ) )
                    $identical_found[$translation] = true;

                $base_of_translated_slug = preg_replace( '/-\d+$/', '', $translation );
                // if we need to add suffix, then consider similar too (for example if input is slug then consider slug-2, slug-3 etc.)
                $existent_similar_slugs = array_filter( $translated['results'], function ( $item ) use ( $base_of_translated_slug ) {
                    return preg_match( '/^' . preg_quote( $base_of_translated_slug, '/' ) . '\-\d+$/', $item );
                } );

                $available_suffix = 2;

                // In case there are multiple suffixed slugs found in the database, increment the highest found number and assign it to $available_suffix.
                foreach ( $existent_similar_slugs as $slug ) {
                    $suffix = (int)substr( $slug, strrpos( $slug, '-' ) + 1 );

                    if ( $suffix >= $available_suffix ) {
                        $available_suffix = $suffix + 1;
                    }
                }

                $translations_map[ $base_of_translated_slug ]['available_suffix'] = $available_suffix;
            }
        }

        foreach ( $array_of_slugs as &$slug ){
            if ( !empty( $slug ) && isset( $slug['translated'] ) ) {

                if ( isset( $identical_found[ $slug['translated'] ] ) ) {
                    $base_of_translated_slug = preg_replace( '/-\d+$/', '', $slug['translated'] );
                    if ( !isset( $translations_map[ $base_of_translated_slug ]['available_suffix'] ) ) {
                        $translations_map[ $base_of_translated_slug ]['available_suffix'] = 2;
                    }
                    $slug['translated'] = $base_of_translated_slug . '-' . $translations_map[ $base_of_translated_slug ]['available_suffix'];
                    //prevent inserting duplicate translations from currently inserted slugs array
                    $translations_map[ $base_of_translated_slug ]['available_suffix']++;
                } else {
                    $identical_found[ $slug['translated'] ] = true;
                }
            }
        }

        return $array_of_slugs;
    }

    /**
     * Updates translated slugs in the trp_slug_translation table for the specified language.
     *
     * @param array  $array_of_slugs [ [ 'id' => id, 'translated' => 'updated_slug' ], [...] ]
     * @param string $language       Language code.
     * @return array|bool            Returns the array of slugs or false in case of error.
     *
     * @see self::make_slugs_unique()
     */
    public function update_slugs( $array_of_slugs, $language ){
        foreach ( $array_of_slugs as &$slug ){
            if ( isset( $slug['translated'] ) )
                $slug['translated'] = strtolower( urlencode( urldecode( $slug['translated'] ) ) ); // Make sure that we manipulate the encoded form of the translated slug
        }

        unset( $slug );

        $array_of_slugs = $this->make_slugs_unique( $array_of_slugs, $language );

        foreach ( $array_of_slugs as $slug ) {
            if ( isset( $slug['id'] ) && isset( $slug['translated'] ) ) {
                $ids[]          = $slug['id'];
                $translations[] = $this->db->prepare('WHEN %d THEN %s', $slug['id'], $slug['translated']);

                // Prepare SQL snippets for updating statuses if provided, else default to status 2
                $newStatus = isset( $slug['status'] ) ? $slug['status'] : 2;
                $statusUpdates[] = $this->db->prepare("WHEN %d THEN %d", $slug['id'], $newStatus);
            }
        }

        if ( empty( $ids ) ) return false;

        // Building the SQL query - CASE is used for bulk updating. Cases are constructed above, based on input.
        $sql = "UPDATE {$this->translation_table_name}
                SET translated = CASE id " . implode(' ', $translations) . " END,
                    status = CASE id " . implode(' ', $statusUpdates) . " END
                WHERE id IN (" . implode(',', $ids) . ") AND language = %s";

        $prepared_query = $this->db->prepare( $sql, $language );

        $sql_result = $this->db->query( $prepared_query );

        if ( $sql_result === false ){
            $this->record_slug_error( 'update_translated' );

            return false;
        }

        if ( $sql_result == 0){

            return 0;
        }

        return $array_of_slugs;
    }

    /**
     * Retrieves original slugs based on translated slugs.
     *
     * @param array  $array_of_translated_slugs  [ 'translated_slug_1', 'translated_slug_2', ... ]
     * @param string $language                   Language code.
     * @return array|false                       The array of original and translated slugs or false in case of an error.
     */
    public function get_original_slugs_from_translated( $array_of_translated_slugs, $language ){
        foreach ( $array_of_translated_slugs as &$slug ){
            if ( !empty( $slug ) ){
                $slug = strtolower( urlencode( urldecode( $slug) ) ); // Make sure that we manipulate the encoded form of the translated slug
                $select_values[] = $this->db->prepare( '%s', $slug );
            }
        }

        unset( $slug );

        if ( !isset( $select_values ) ) return false;

        $sql = "SELECT DISTINCT translated, original, st.id, st.status FROM {$this->original_table_name} AS so
                    JOIN {$this->translation_table_name} AS st ON so.id = st.original_id
                    WHERE st.language = '{$language}'
                    AND st.translated IN (". implode( ',', $select_values ) . ")";

        $sql_result = $this->db->get_results( $sql, ARRAY_A );

        if ( $sql_result === false ){
            $this->record_slug_error( 'select_original' );

            return false;
        }

        $slug_pairs = [];
        $duplicate_translations = [];

        // format the result into key-value (original => translated) array
        foreach ( $sql_result as $slug ){
            if ( isset( $slug['original'] ) && isset( $slug['translated'] ) )
                if ( isset( $slug_pairs[ $slug['translated'] ] ) ) {
                    $duplicate_translations[] = $slug;
                } else {
                    $slug_pairs[ $slug['translated'] ] = $slug['original'];
                }
        }

        // had a bug where duplicate translations where possible. If such translations are found, keep one, update the rest
       if ( !empty ( $duplicate_translations) ){
           // updating with the same translation will actually trigger adding suffixes
           $this->update_slugs( $duplicate_translations, $language );
       }

        return $slug_pairs;
    }

    /**
     * Retrieves original and translated slugs based on various filtering and ordering options.
     *
     *
     * @param array $options An associative array of options to customize the retrieval. Supported options include:
     *                       - 'slug_type' (string): Filter by the slug type. Default is an empty string (no filtering).
     *                       - 'search' (string): Search keyword for filtering within original and translated slugs. Default is empty (no search).
     *                       - 'status' (string|int): Filter by the translation status ('1' - automatically translated, '2' - manually translated). Default is empty (no status filtering).
     *                       - 'order_by' (string): Specifies the field to order the results by. Allowed values are 'original', 'translated', 'status', 'type'. Default is 'original'.
     *                       - 'order' (string): Specifies the ordering direction. Allowed values are 'ASC' for ascending and 'DESC' for descending. Default is 'ASC'.
     *                       - 'limit' (int): Specifies the limit of the query.
     *                       - 'offset' (int): Specifies the offset of the query.
     *
     * @return array|false An array of associative arrays containing details of matching slugs or false in case of an error. Each item includes 'original', 'id', 'type', 'translated', and 'status' keys.
     */
    public function get_original_slugs($options = []) {
        $sql = "SELECT DISTINCT so.original, so.id as original_id
            FROM {$this->original_table_name} AS so
            LEFT JOIN {$this->translation_table_name} AS st ON so.id = st.original_id";

        $sql = $this->get_original_slugs_parsed_query( $sql, $options );

        $results = $this->db->get_results( $sql, ARRAY_A );

        if ( $results === false ) {
            $this->record_slug_error( 'select_original_with_options' );

            return false;
        }

        return $results;
    }

    /**
     * Retrieves count of original slugs based on various filtering and ordering options.
     *
     *
     * @param array $options An associative array of options to customize the retrieval.
     *
     * @return int|false
     */
    public function get_original_slugs_count($options = []){
        $sql = "SELECT COUNT(DISTINCT so.original) as count
            FROM {$this->original_table_name} AS so
            LEFT JOIN {$this->translation_table_name} AS st ON so.id = st.original_id";

        $sql = $this->get_original_slugs_parsed_query( $sql, $options );

        $results = $this->db->get_results( $sql, ARRAY_A );

        if ( $results === false ) {
            $this->record_slug_error( 'select_original_count_with_options' );

            return false;
        }

        return (int) $results[0]['count'];
    }


    /**
     * @param string $sql     The base SQL query
     * @param array $options  Filtering arguments
     *
     * @return string         Returns prepared SQL query with arguments parsed
     */
    private function get_original_slugs_parsed_query( $sql, $options ){
        $defaults = [
            'slug_type' => '',
            'search'    => '',
            'status'    => '',
            'language'  => null,
            'order_by'  => 'original',
            'order'     => 'ASC',
            'limit'     => null,
            'offset'    => null
        ];

        $options = wp_parse_args( $options, $defaults );

        $conditions = [];
        $sql_params = [];

        if ( !empty( $options['slug_type'] ) ) {
            $conditions[] = "so.type = %s";
            $sql_params[] = $options['slug_type'];
        }

        if ( !empty( $options['search'] ) ) {
            $conditions[] = "so.original LIKE %s";
            $like_keyword = '%' . $this->db->esc_like( $options['search'] ) . '%';
            $sql_params[] = $like_keyword;
        }

        if ( !empty( $options['status'] ) ) {
            $status  = (array) $options['status'];
            $filtered_status = array_filter( $status, function( $value ) { return $value !== 0; } ); // Remove status 0

            // Not translated only
            if ( $status === [0] ) {
                $conditions[] = "st.status IS NULL";
            }

            // Not translated and another status
            elseif ( in_array( 0, $status ) && count( $status ) > 1 ) {
                $status_placeholders = implode( ',', array_fill( 0, count( $filtered_status ), '%d' ) );

                $conditions[] = "(st.status IS NULL OR st.status IN ($status_placeholders))";

                $sql_params = array_merge( $sql_params, $filtered_status );
            }

            // Automatically translated and manually translated
            else {
                $status_placeholders = implode( ',', array_fill( 0, count( $status ), '%d' ) );

                $conditions[] = "st.status IN ($status_placeholders)";

                $sql_params = array_merge( $sql_params, $status );
            }
        }

        if ( !empty( $options['language'] ) ){
            $conditions[] = "st.language = %s";

            $sql_params[] = $options['language'];
        }

        if ( !empty( $conditions ) ) {
            $sql .= " WHERE " . implode( ' AND ', $conditions );
        }

        // Validate and append the ORDER BY clause
        $allowed_order_by = ['original', 'translated', 'status', 'type']; // Defining allowed fields for ordering
        if ( in_array( $options['order_by'], $allowed_order_by ) ) {
            $order_direction = strtoupper($options['order']) == 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY {$options['order_by']} {$order_direction}";
        }

        // Pagination
        if ( isset( $options['limit'] ) && is_numeric( $options['limit'] ) ) {
            $sql .= " LIMIT %d";
            $sql_params[] = $options['limit'];

            if ( !empty( $options['offset'] ) && is_numeric( $options['offset'] ) ) {
                $sql .= " OFFSET %d";
                $sql_params[] = $options['offset'];
            }
        }

        return $this->db->prepare( $sql, $sql_params );
    }

    /**
     * Retrieves IDs from original slugs.
     *
     * @param array $array_of_original_slugs [ 'original_slug_1', 'original_slug_2', ... ]
     * @return array|false                   The array of IDs and original slugs or false in case of an error.
     */
    public function get_ids_from_original( $array_of_original_slugs ){
        foreach ( $array_of_original_slugs as $slug ){
            $original_slugs[] = $this->db->prepare( '%s', $slug );
        }

        if ( !isset( $original_slugs ) ) return false;

        $sql = "SELECT id, original
                FROM $this->original_table_name
                WHERE original IN (". implode( ',', $original_slugs ) . ")";

        $sql_result = $this->db->get_results( $sql, ARRAY_A );

        if ( $sql_result === false ){
            $this->record_slug_error( 'select_ids_from_original' );

            return false;
        }

        $original_ids = [];

        foreach ( $sql_result as $row ){
            $original_row = $row['original'];

            $original_ids[$original_row] = (int) $row['id'];
        }

        return $original_ids;
    }

    /**
     * Retrieves translated slugs from original slugs for the specified language.
     *
     * @param array  $array_of_original_slugs   [ 'original_slug_1', 'original_slug_2', ... ]
     * @param string $language                  Language code. If no language is specified, it will call $this->get_translated_slugs_from_original_all_languages
     * @param bool   $return_key_value_pair     Default is true. In case it's set to false, it will return the full array of information instead of original => translated pairs.
     * @return array|false                      The array of translated and original slugs or false in case of an error.
     */
    public function get_translated_slugs_from_original( $array_of_original_slugs, $language = null, $return_key_value_pair = true ){
        if ( !isset( $language ) ) return $this->get_translated_slugs_from_original_all_languages( $array_of_original_slugs );

        foreach ( $array_of_original_slugs as &$slug ){
            if ( !empty( $slug ) ){
                $slug = strtolower( urlencode( urldecode( $slug ) ) );
                $select_values[] = $this->db->prepare( '%s', $slug );
            }
        }

        unset( $slug );

        if ( !isset( $select_values ) ) return false;

        $sql = "SELECT DISTINCT so.original, st.translated, st.status, st.id FROM {$this->original_table_name} AS so
                JOIN {$this->translation_table_name} AS st ON so.id = st.original_id
                WHERE st.language = '{$language}'
                AND so.original IN (". implode( ',', $select_values ) . ")";

        $sql_result = $this->db->get_results( $sql, ARRAY_A );

        if ( $sql_result === false ){
            $this->record_slug_error( 'select_translated' );

            return false;
        }

        if ( !$return_key_value_pair ) return $sql_result; // return full array

        $slug_pairs = [];

        foreach ( $sql_result as $slug ){
            if ( isset( $slug['original'] ) && isset( $slug['translated'] ) )
                $slug_pairs[$slug['original']] = urldecode( $slug['translated'] );
        }

        return $slug_pairs;
    }

    /**
     * Retrieves translated slugs from original slugs for all languages
     *
     * @param array  $array_of_original_slugs   [ 'original_slug_1', 'original_slug_2', ... ]
     * @return array|false                      The array of translated and original slugs or false in case of an error.
     */
    private function get_translated_slugs_from_original_all_languages( $array_of_original_slugs ){
        foreach ( $array_of_original_slugs as &$slug ){
            if ( !empty( $slug ) ){
                $slug = strtolower( urlencode( urldecode( $slug ) ) );
                $select_values[] = $this->db->prepare( '%s', $slug );
            }
        }

        if ( !isset( $select_values ) ) return false;

        $sql = "SELECT DISTINCT so.original, st.translated, st.language, st.status, st.id, st.original_id FROM {$this->original_table_name} AS so
                JOIN {$this->translation_table_name} AS st ON so.id = st.original_id
                AND so.original IN (". implode( ',', $select_values ) . ")";

        $sql_result = $this->db->get_results( $sql, ARRAY_A );

        if ( $sql_result === false ){
            $this->record_slug_error( 'select_translated' );

            return false;
        }

        $translations_array = [];

        foreach ( $sql_result as $translation ){
            $translations_array[$translation['original_id']][$translation['language']] = $translation;
        }

        return $translations_array;
    }

    /**
     * Deletes multiple translations based on their IDs.
     *
     * @param array $ids Array of IDs to delete.
     * @return bool True if the operation was successful, false otherwise.
     */
    public function delete_translations_by_ids( $ids ) {
        $ids = array_map( 'intval', $ids );

        $ids_string = implode( ',', $ids );

        $sql = "DELETE FROM {$this->translation_table_name} WHERE id IN ($ids_string)";

        $result = $this->db->query( $sql );

        if ( $result === false ) {
            $this->record_slug_error( 'delete_translated_multiple' );

            return false;
        }

        return true;
    }

    /*
     * Function needed in the case the data migration of the slugs was not completed
     * Because in the new functions we do not keep track of the type of slugs we are searching for, in these functions, we have tp
     * look for the translation in all the old places where we stored the slugs
     *
     * For each translation found, the array of slugs we search for loses that element in order to take less time for the next search
     *
     * Started with the option based slugs because they are usually fewer than the meta based slugs
     */
    public function get_translated_slugs_from_original_if_db_migration_was_not_completed( $array_of_original_slugs_left, $language ){

        $slug_pairs = [];

        foreach ( $array_of_original_slugs_left as &$slug ) {
            if ( !empty( $slug ) ) {
                $array_of_original_slugs[] = urlencode( urldecode( $slug ) );
            }
        }

        if ( !isset( $array_of_original_slugs ) ) return false;

        //look for translation in taxonomy option
        $data_tax = get_option( 'trp_taxonomy_slug_translation', array() );

        foreach ( $data_tax as $values_array ) {
            if ( isset( $values_array["original"] ) && isset($values_array["translationsArray"][ $language ]["translated"]) && in_array( $values_array["original"], $array_of_original_slugs )){
                $slug_pairs[ $values_array["original"] ] = $values_array["translationsArray"][$language]["translated"];
            }
        }

        if ( !empty( $slug_pairs )) {
            foreach ( $array_of_original_slugs as $key => $slug_to_check ) {
                if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                    unset( $array_of_original_slugs[ $key ] );
                }
            }
        }

        if ( empty( $array_of_original_slugs )){
            return $slug_pairs;
        }else {
            //look for translation in post_type_base_slug option
            $data_post_type_base = get_option( 'trp_post_type_base_slug_translation', array() );

            foreach ( $data_post_type_base as $values_array ) {
                if ( isset( $values_array["original"] ) && isset($values_array["translationsArray"][ $language ]["translated"]) && in_array( $values_array["original"], $array_of_original_slugs ) ) {
                    $slug_pairs[ $values_array["original"] ] = $values_array["translationsArray"][ $language ]["translated"];
                }
            }

            if ( !empty( $slug_pairs ) ) {
                foreach ( $array_of_original_slugs as $key => $slug_to_check ) {
                    if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                        unset( $array_of_original_slugs[ $key ] );
                    }
                }
            }

            if ( empty( $array_of_original_slugs ) ) {
                return $slug_pairs;
            } else {
                foreach ( $array_of_original_slugs as &$slug ) {
                    if ( !empty( $slug ) ) {
                        $slug            = urldecode( $slug );
                        $select_values[] = $this->db->prepare( '%s', $slug );
                    }
                }

                if ( !isset( $select_values ) ) return false;
                // look for the translation needed in the postmeta table
                $sql = "SELECT p.post_name, pm.meta_value, pm.post_id  FROM `" . $this->db->posts . "` as p INNER JOIN `" . $this->db->postmeta . "` as pm ON pm.post_id = p.ID ";
                $sql .= "WHERE p.post_name IN (" . implode( ',', $select_values ) . ")";
                $sql .= "AND ( pm.meta_key = %s OR pm.meta_key = %s ) ";

                $prepared_query = $this->db->prepare( $sql, '_trp_automatically_translated_slug_' . $language, '_trp_translated_slug_' . $language );

                $sql_result = $this->db->get_results( $prepared_query, 'ARRAY_A' );

                if ( $sql_result !== false ) {
                    foreach ( $sql_result as $found_slug ) {
                        if ( isset( $found_slug['post_name'] ) && isset( $found_slug['meta_value'] ) )
                            $slug_pairs[ $found_slug['post_name'] ] = $found_slug['meta_value'];
                    }
                }

                //we need to check if they are slugs in the $array_of_original_slugs that do not have a translation found, so it might be a different type of slug
                if ( !empty( $slug_pairs ) ) {
                    foreach ( $array_of_original_slugs as $key => $slug_to_check ) {
                        if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                            unset( $select_values[ $key ] );
                        }
                    }
                }

                //if all the values from the $array_of_original_slugs were found we can return the slug_pairs
                if ( empty( $select_values ) )
                    return $slug_pairs;
                else {

                    //look for translation in termmeta
                    $sql = "SELECT t.name, tm.meta_value, tm.term_id  FROM `" . $this->db->terms . "` as t INNER JOIN `" . $this->db->termmeta . "` as tm ON t.term_id = tm.term_id ";
                    $sql .= "WHERE t.name IN (" . implode( ',', $select_values ) . ")";
                    $sql .= "AND ( tm.meta_key = %s OR tm.meta_key = %s ) ";

                    $prepared_query = $this->db->prepare( $sql, '_trp_automatically_translated_slug_' . $language, '_trp_translated_slug_' . $language );

                    $sql_result = $this->db->get_results( $prepared_query, 'ARRAY_A' );

                    if ( $sql_result !== false ) {
                        foreach ( $sql_result as $found_slug ) {
                            if ( isset( $found_slug['name'] ) && isset( $found_slug['meta_value'] ) )
                                $slug_pairs[ $found_slug['name'] ] = $found_slug['meta_value'];
                        }
                    }
                }
            }
        }

        if ( empty( $slug_pairs ) ) return false;

        return $slug_pairs;
    }
    public function get_original_slugs_from_translated_if_db_migration_was_not_completed( $array_of_translated_slugs_left, $language ){

        $slug_pairs = [];

        foreach ( $array_of_translated_slugs_left as &$slug ) {
            if ( !empty( $slug ) ) {
                $array_of_translated_slugs[] = urldecode( $slug );
            }
        }

        if ( !isset( $array_of_translated_slugs ) ) return false;

        $data_tax = get_option( 'trp_taxonomy_slug_translation', array() );

        foreach ( $data_tax as $values_array ) {
            if ( isset( $values_array["translationsArray"][$language]["translated"] )) {
                if ( in_array( $values_array["translationsArray"][ $language ]["translated"], $array_of_translated_slugs ) ) {
                    $slug_pairs[ $values_array["translationsArray"][ $language ]["translated"] ] = $values_array["original"];
                }
            }
        }

        if ( !empty( $slug_pairs )) {
            foreach ( $array_of_translated_slugs as $key => $slug_to_check ) {
                if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                    unset( $array_of_translated_slugs[ $key ] );
                }
            }
        }

        if ( empty( $array_of_translated_slugs )){
            return $slug_pairs;
        }else {
            //look for original in post_type_base_slug option
            $data_post_type_base = get_option( 'trp_post_type_base_slug_translation', array() );

            foreach ( $data_post_type_base as $values_array ) {
                if ( isset( $values_array["translationsArray"][$language]["translated"] ) ) {
                    if ( in_array( $values_array["translationsArray"][$language]["translated"], $array_of_translated_slugs ) ) {
                        $slug_pairs[ $values_array["translationsArray"][ $language ]["translated"] ] = $values_array["original"];
                    }
                }
            }

            if ( !empty( $slug_pairs ) ) {
                foreach ( $array_of_translated_slugs as $key => $slug_to_check ) {
                    if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                        unset( $array_of_translated_slugs[ $key ] );
                    }
                }
            }

            if ( empty( $array_of_translated_slugs ) ) {
                return $slug_pairs;
            } else {
                foreach ( $array_of_translated_slugs as &$slug ) {
                    if ( !empty( $slug ) ) {
                        $slug            = urldecode( $slug );
                        $select_values[] = $this->db->prepare( '%s', $slug );
                    }
                }

                if ( !isset( $select_values ) ) return false;
                // look for the original needed in the postmeta table
                $sql = "SELECT p.post_name, pm.meta_value, pm.post_id  FROM `" . $this->db->posts . "` as p INNER JOIN `" . $this->db->postmeta . "` as pm ON p.ID = pm.post_id ";
                $sql .= "WHERE pm.meta_value IN (". implode( ',', $select_values ) . ") ";
                $sql .= "AND ( pm.meta_key = %s OR pm.meta_key = %s ) ";

                $prepared_query = $this->db->prepare( $sql, '_trp_automatically_translated_slug_' . $language, '_trp_translated_slug_' . $language );

                $sql_result = $this->db->get_results( $prepared_query, 'ARRAY_A' );

                if ( $sql_result !== false ) {
                    foreach ( $sql_result as $found_slug ) {
                        if ( isset( $found_slug['post_name'] ) && isset( $found_slug['meta_value'] ) )
                            if ( !isset($slug_pairs[ $found_slug['meta_value'] ])) {
                                $slug_pairs[ $found_slug['meta_value'] ] = $found_slug['post_name'];
                            }
                    }
                }

                //we need to check if they are slugs in the $array_of_original_slugs that do not have a translation found, so it might be a different type of slug
                if ( !empty( $slug_pairs ) ) {
                    foreach ( $array_of_translated_slugs as $key => $slug_to_check ) {
                        if ( isset( $slug_pairs[ $slug_to_check ] ) ) {
                            unset( $select_values[ $key ] );
                        }
                    }
                }

                //if all the values from the $array_of_original_slugs were found we can return the slug_pairs
                if ( empty( $select_values ) )
                    return $slug_pairs;
                else {

                    //look for original in termmeta
                    $sql = "SELECT t.name, tm.meta_value, tm.term_id  FROM `" . $this->db->terms . "` as t INNER JOIN `" . $this->db->termmeta . "` as tm ON t.term_id = tm.term_id ";
                    $sql .= "WHERE tm.meta_value IN (" . implode( ',', $select_values ) . ") ";
                    $sql .= "AND ( tm.meta_key = %s OR tm.meta_key = %s ) ";

                    $prepared_query = $this->db->prepare( $sql, '_trp_automatically_translated_slug_' . $language, '_trp_translated_slug_' . $language );

                    $sql_result = $this->db->get_results( $prepared_query, 'ARRAY_A' );

                    if ( $sql_result !== false ) {
                        foreach ( $sql_result as $found_slug ) {
                            if ( isset( $found_slug['name'] ) && isset( $found_slug['meta_value'] ) )
                                $slug_pairs[ $found_slug['meta_value'] ] = $found_slug['name'];
                        }
                    }
                }
            }
        }

        if ( empty( $slug_pairs ) ) return false;

        return $slug_pairs;
    }

    private function record_slug_error( $type ){
        $error_type = "last_error_{$type}_slugs";

        // Error message based on $type - add key, value pair here in order to register a new error type
        $error_message = [
          'select_original'          => 'Error selecting original slugs from translated.',
          'select_translated'        => 'Error selecting translated slugs from original.',
          'select_ids_from_original' => 'Error selecting ids from original slugs',
          'insert_original'          => 'Error inserting original slugs.',
          'insert_translated'        => 'Error inserting translated slugs.',
          'update_translated'        => 'Error updating translated slugs.',
          'delete_translated'        => 'Error deleting translations.'
        ];

        $error_details = [
            $error_type                      => $this->db->last_error,
            'message'                        => $error_message[$type],
            'disable_automatic_translations' => true
        ];

        $this->error_manager->record_error( $error_details );
    }

}