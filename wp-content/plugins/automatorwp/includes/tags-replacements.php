<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Tags
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param int       $automation_id  The automation ID
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 *
 * @return string
 */
function automatorwp_get_tag_replacement( $tag_name = '', $automation_id = 0, $user_id = 0, $content = '' ) {

    $replacement = '';

    // Common tags
    switch( $tag_name ) {
        // Site tags
        case 'site_name':
            $replacement = get_bloginfo( 'name' );
            break;
        case 'site_url':
            $replacement = get_site_url();
            break;
        case 'admin_email':
            $replacement = get_bloginfo( 'admin_email' );
            break;
        // Date and time tags
        case 'date':
            $replacement = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
            break;
        case 'timestamp':
            $replacement = current_time( 'timestamp' );
            break;
    }

    // User tags
    $user = get_userdata( $user_id );

    switch( $tag_name ) {
        case 'user_id':
            $replacement = ( $user ? $user->ID : '' );
            break;
        case 'user_login':
            $replacement = ( $user ? $user->user_login : '' );
            break;
        case 'user_email':
            $replacement = ( $user ? $user->user_email : '' );
            break;
        case 'display_name':
            $replacement = ( $user ? $user->display_name : '' );
            break;
        case 'first_name':
            $replacement = ( $user ? $user->first_name : '' );
            break;
        case 'last_name':
            $replacement = ( $user ? $user->last_name : '' );
            break;
        case 'user_url':
            $replacement = ( $user ? $user->user_url : '' );
            break;
        case 'avatar':
        case 'avatar_url':
            $user_id = ( $user ? $user->ID : 0 );
            $url = get_avatar_url( $user_id, array( 'force_default' => true ) );
            $replacement = $url;

            if( $tag_name === 'avatar' ) {
                $replacement = '<img src="' . $url . '"/>';
            }
            break;
        case 'reset_password_url':
        case 'reset_password_link':
            $key = ( $user ?  get_password_reset_key( $user ) : '' );
            $login = ( $user ?  rawurlencode( $user->user_login ) : '' );
            $url = ( $user ? network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . $login, 'login' ) : '' );
            $replacement = $url;

            if( $tag_name === 'reset_password_link' ) {
                $replacement = '<a href="' . $url . '">' . __( 'Click here to reset your password', 'automatorwp' ) . '</a>';
            }
            break;
    }

    /**
     * Filter the tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param int       $automation_id  The automation ID
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_tag_replacement', $replacement, $tag_name, $automation_id, $user_id, $content );

}

/**
 * Post tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $object         The object
 * @param string    $object_type    The object type
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last log object
 *
 * @return string
 */
function automatorwp_get_post_tag_replacement( $tag_name, $object, $object_type, $user_id, $content, $log ) {

    $replacement = '';

    // Post tags
    $post_tags = array_keys( automatorwp_utilities_post_tags() );
    
    // If is a post tag and log has a post assigned, pass its replacements
    if( ( in_array( $tag_name, $post_tags ) || automatorwp_starts_with( $tag_name, 'post_meta' ) || automatorwp_starts_with( $tag_name, 'post_terms' ) )
        && $log->post_id !== 0 ) {

        if( automatorwp_starts_with( $tag_name, 'post_meta' ) ) {
            // Post meta tag
            $meta_key = str_replace('post_meta:', '', $tag_name);

            if( strpos( $meta_key, '/' ) !== false ) {
                // post_meta:key/subkey
                $keys = explode( "/", $meta_key );

                $value = get_post_meta( $log->post_id, $keys[0], true );
                $meta_value = '';

                for( $i=1; $i < count( $keys ); $i++ ) {
                    $key = $keys[$i];

                    if( ! empty( $key ) ) {
                        $meta_value = isset( $value[$key] ) ? $value[$key] : '';
                    }
                }
            } else {
                // post_meta:key
                $meta_value = get_post_meta( $log->post_id, $meta_key, true );
            }

            $replacement = $meta_value;
        } else if( automatorwp_starts_with( $tag_name, 'post_terms' ) ) {
            // Post terms tag
            $taxonomy = str_replace('post_terms:', '', $tag_name);

            $terms = get_the_terms( $log->post_id, $taxonomy );
            
            if( ! is_wp_error( $terms ) ) {
                $replacement = join(', ', wp_list_pluck( $terms, 'name' ) );
            }
            
        } else {
            // Post tag
            $post = get_post( $log->post_id );

            switch( $tag_name ) {
                case 'post_id':
                    $replacement = ( $post ? $post->ID : '' );
                    break;
                case 'post_title':
                    $replacement = ( $post ? $post->post_title : '' );
                    break;
                case 'post_url':
                    $replacement = (  $post ? get_permalink( $post->ID ) : '' );
                    break;
                case 'post_link':
                    $replacement = (  $post ? '<a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a>' : '' );
                    break;
                case 'post_type':
                    $replacement = (  $post ? $post->post_type : '' );
                    break;
                case 'post_type_label':
                    $post_type = (  $post ? get_post_type_object( $post->post_type ) : false );
                    $replacement = (  $post_type ? $post_type->labels->singular_name : '' );
                    break;
                case 'post_author':
                    $replacement = (  $post ? $post->post_author : '' );
                    break;
                case 'post_author_email':
                    $author = ( $post ? get_userdata( $post->post_author ) : false );

                    $replacement = (  $author ? $author->user_email : '' );
                    break;
                case 'post_content':
                    $replacement = (  $post ? $post->post_content : '' );
                    break;
                case 'post_excerpt':
                    $replacement = (  $post ? $post->post_excerpt : '' );
                    break;
                case 'post_thumbnail':
                    $replacement = (  $post ? '<img src="' . get_the_post_thumbnail_url( $post ) . '"/>' : '' );
                    break;
                case 'post_thumbnail_id':
                    $replacement = (  $post ? get_post_thumbnail_id( $post ) : '' );
                    break;
                case 'post_thumbnail_url':
                    $replacement = (  $post ? get_the_post_thumbnail_url( $post ) : '' );
                    break;
                case 'post_status':
                    $replacement = (  $post ? $post->post_status : '' );
                    break;
                case 'post_date':
                    $replacement = ( $post ? $post->post_date : '' );
                    break;
                case 'post_date_gmt':
                    $replacement = ( $post ? $post->post_date_gmt : '' );
                    break;
                case 'post_modified':
                    $replacement = ( $post ? $post->post_modified : '' );
                    break;
                case 'post_modified_gmt':
                    $replacement = ( $post ? $post->post_modified_gmt : '' );
                    break;
                case 'post_parent':
                    $replacement = (  $post ? $post->post_parent : '' );
                    break;
                case 'menu_order':
                    $replacement = (  $post ? $post->menu_order : '' );
                    break;
            }
        }

    }

    /**
     * Filter the post tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $object         The object
     * @param string    $object_type    The object type
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last log object
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_post_tag_replacement', $replacement, $tag_name, $object, $object_type, $user_id, $content, $log );

}

/**
 * Comment tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $object         The object
 * @param string    $object_type    The object type
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_get_comment_tag_replacement( $tag_name, $object, $object_type, $user_id, $content, $log ) {

    $replacement = '';

    // Comment tags
    $comment_tags = array_keys( automatorwp_utilities_comment_tags() );

    // If is a comment tag, pass its replacements
    if( in_array( $tag_name, $comment_tags ) ) {

        $comment_id = (int) ct_get_object_meta( $log->id, 'comment_id', true );

        if( $comment_id !== 0 ) {

            $comment = get_comment( $comment_id );
            $post = get_post( $comment->comment_post_ID );

            switch( $tag_name ) {
                case 'comment_id':
                    $replacement = ( $comment ? $comment->comment_ID : '' );
                    break;
                case 'comment_post_id':
                    $replacement = ( $post ? $post->ID : '' );
                    break;
                case 'comment_post_title':
                    $replacement = ( $post ? $post->post_title : '' );
                    break;
                case 'comment_user_id':
                    $replacement = ( $comment ? $comment->user_id : '' );
                    break;
                case 'comment_author':
                    $replacement = ( $comment ? $comment->comment_author : '' );
                    break;
                case 'comment_author_email':
                    $replacement = ( $comment ? $comment->comment_author_email : '' );
                    break;
                case 'comment_author_url':
                    $replacement = ( $comment ? $comment->comment_author_url : '' );
                    break;
                case 'comment_author_ip':
                    $replacement = ( $comment ? $comment->comment_author_IP : '' );
                    break;
                case 'comment_content':
                    $replacement = ( $comment ? $comment->comment_content : '' );
                    break;
                case 'comment_type':
                    $replacement = ( $comment ? $comment->comment_type : '' );
                    break;
            }

        }

    }

    /**
     * Filter the comment tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $object         The object
     * @param string    $object_type    The object type
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last trigger log object
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_comment_tag_replacement', $replacement, $tag_name, $object, $object_type, $user_id, $content, $log );

}

/**
 * User tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $object         The object
 * @param string    $object_type    The object type
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_get_user_tag_replacement( $tag_name, $object, $object_type, $user_id, $content, $log ) {

    $replacement = '';

    // User tags
    $user_tags = array_keys( automatorwp_utilities_user_tags() );

    // If is a user tag, pass its replacements
    if( in_array( $tag_name, $user_tags ) || automatorwp_starts_with( $tag_name, 'user_meta' ) ) {

        $user_id = (int) ct_get_object_meta( $log->id, 'user_id', true );

        if( $user_id !== 0 ) {

            if( ! automatorwp_starts_with( $tag_name, 'user_meta' ) ) {

                // User tags
                $user = get_userdata( $user_id );

                switch( $tag_name ) {
                    case 'user_id':
                        $replacement = ( $user ? $user->ID : '' );
                        break;
                    case 'user_login':
                        $replacement = ( $user ? $user->user_login : '' );
                        break;
                    case 'user_email':
                        $replacement = ( $user ? $user->user_email : '' );
                        break;
                    case 'display_name':
                        $replacement = ( $user ? $user->display_name : '' );
                        break;
                    case 'first_name':
                        $replacement = ( $user ? $user->first_name : '' );
                        break;
                    case 'last_name':
                        $replacement = ( $user ? $user->last_name : '' );
                        break;
                    case 'user_url':
                        $replacement = ( $user ? $user->user_url : '' );
                        break;
                    case 'avatar':
                    case 'avatar_url':
                        $user_id = ( $user ? $user->ID : 0 );
                        $url = get_avatar_url( $user_id, array( 'force_default' => true ) );
                        $replacement = $url;

                        if( $tag_name === 'avatar' ) {
                            $replacement = '<img src="' . $url . '"/>';
                        }
                        break;
                    case 'reset_password_url':
                    case 'reset_password_link':
                        $key = ( $user ?  get_password_reset_key( $user ) : '' );
                        $login = ( $user ?  rawurlencode( $user->user_login ) : '' );
                        $url = ( $user ? network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . $login, 'login' ) : '' );
                        $replacement = $url;

                        if( $tag_name === 'reset_password_link' ) {
                            $replacement = '<a href="' . $url . '">' . __( 'Click here to reset your password', 'automatorwp' ) . '</a>';
                        }
                        break;
                }

            } else {
                // User meta tag
                $meta_key = str_replace('user_meta:', '', $tag_name);

                if( strpos( $meta_key, '/' ) !== false ) {
                    // user_meta:key/subkey
                    $keys = explode( "/", $meta_key );

                    $value = get_user_meta( $user_id, $keys[0], true );
                    $meta_value = '';

                    for( $i=1; $i < count( $keys ); $i++ ) {
                        $key = $keys[$i];

                        if( ! empty( $key ) ) {
                            $meta_value = isset( $value[$key] ) ? $value[$key] : '';
                        }
                    }
                } else {
                    // user_meta:key
                    $meta_value = get_user_meta( $user_id, $meta_key, true );
                }

                $replacement = $meta_value;
            }

        }

    }

    /**
     * Filter the user tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $object         The object
     * @param string    $object_type    The object type
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last trigger log object
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_user_tag_replacement', $replacement, $tag_name, $object, $object_type, $user_id, $content, $log );

}

/**
 * Get the user meta tags replacements
 *
 * @since 1.1.0
 *
 * @param int       $user_id The user ID
 * @param string    $content The content to parse
 *
 * @return array
 */
function automatorwp_get_user_meta_tags_replacements( $user_id = 0, $content = '' ) {

    $replacements = array();

    // Look for user meta tags
    preg_match_all( "/\{user_meta:\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $meta_key ) {
            if( strpos( $meta_key, '/' ) !== false ) {
                // user_meta:key/subkey
                $keys = explode( "/", $meta_key );

                $value = get_user_meta( $user_id, $keys[0], true );
                $meta_value = '';

                for( $i=1; $i < count( $keys ); $i++ ) {
                    $key = $keys[$i];

                    if( ! empty( $key ) ) {
                        $meta_value = isset( $value[$key] ) ? $value[$key] : '';
                    }
                }
            } else {
                // user_meta:key
                $meta_value = get_user_meta( $user_id, $meta_key, true );
            }

            $replacements['{user_meta:' . $meta_key . '}'] = $meta_value;
        }

    }

    /**
     * Filter to set custom user meta tags replacements
     *
     * @since 1.1.0
     *
     * @param array     $replacements   Replacements
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_user_meta_tags_replacements', $replacements, $user_id, $content );

}

/**
 * Parse user meta tags replacements
 *
 * @since 1.1.0
 *
 * @param int       $user_id The user ID
 * @param string    $content The content to replace
 *
 * @return string
 */
function automatorwp_parse_user_meta_tags( $user_id = 0, $content = '' ) {

    $parsed_content = $content;

    // Get user meta tags replacements
    $replacements = automatorwp_get_user_meta_tags_replacements( $user_id, $content );

    if( $replacements ) {

        $tags = array_keys( $replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $replacements, $content );

    }

    /**
     * Filter to modify a content parsed with user metas
     *
     * @since 1.1.0
     *
     * @param string    $parsed_content Content parsed
     * @param array     $replacements   Replacements
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return string
     */
    return apply_filters( 'automatorwp_parse_user_meta_tags', $parsed_content, $replacements, $user_id, $content );

}

/**
 * Get the date tag format and value
 *
 * @since 2.0.0
 *
 * @param string $format The format and value provided in FORMAT:VALUE
 *
 * @return array
 */
function automatorwp_get_date_tag_format_and_value( $format ) {

    // Default values
    $args = array(
        'format' => 'Y-m-d H:i:s',
        'value' => 'now'
    );

    // Support for default cases
    if( $format !== 'FORMAT' &&  $format !== 'FORMAT:VALUE' ) {

        if( strpos( $format, ':' ) === false ) {
            // FORMAT
            $args['format'] = $format;
        } else {
            // FORMAT:VALUE

            // Split the format by the last ":"
            $parts = preg_split('~:(?=[^:]*$)~', $format);

            if( strlen( $parts[1] ) >= 3 ) {
                // Valid FORMAT:VALUE
                if( $parts[0] !== 'FORMAT' ) {
                    $args['format'] = $parts[0];
                }

                if( $parts[1] !== 'VALUE' ) {
                    $args['value'] = $parts[1];
                }
            } else {
                // Is a FORMAT with ":" inside like H:i:s
                $args['format'] = $format;
            }
        }


    }

    return apply_filters( 'automatorwp_get_date_tag_format_and_value', $args, $format );

}

/**
 * Get the date and timestamp tags replacements
 *
 * @since 2.0.0
 *
 * @param string    $content The content to parse
 *
 * @return array
 */
function automatorwp_get_date_tags_replacements( $content = '' ) {

    $replacements = array();

    // Look for date tags
    preg_match_all( "/\{date:\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $format ) {

            $args = automatorwp_get_date_tag_format_and_value( $format );

            $replacements['{date:' . $format . '}'] = date( $args['format'], strtotime( $args['value'], current_time( 'timestamp' ) ) );
        }

    }

    // Look for timestamp tags
    preg_match_all( "/\{timestamp:\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $value ) {
            $replacements['{timestamp:' . $value . '}'] = strtotime( ( $value === 'VALUE' ? 'now' : $value ), current_time( 'timestamp' ) );
        }

    }

    /**
     * Filter to set custom date tags replacements
     *
     * @since 2.0.0
     *
     * @param array     $replacements   Replacements
     * @param string    $content        The content to parse
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_date_tags_replacements', $replacements, $content );

}

/**
 * Parse date tags replacements
 *
 * @since 2.0.0
 *
 * @param string    $content The content to replace
 *
 * @return string
 */
function automatorwp_parse_date_tags( $content = '' ) {

    $parsed_content = $content;

    // Get date tags replacements
    $replacements = automatorwp_get_date_tags_replacements( $content );

    if( $replacements ) {

        $tags = array_keys( $replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $replacements, $content );

    }

    /**
     * Filter to modify a content parsed with date tags
     *
     * @since 2.0.0
     *
     * @param string    $parsed_content Content parsed
     * @param array     $replacements   Replacements
     * @param string    $content        The content to parse
     *
     * @return string
     */
    return apply_filters( 'automatorwp_parse_date_tags', $parsed_content, $replacements, $content );

}