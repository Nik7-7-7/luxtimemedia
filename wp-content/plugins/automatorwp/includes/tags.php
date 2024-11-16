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
 * Get tags
 *
 * @since 1.0.0
 *
 * @param array $tags The global tags
 *
 * @return array
 */
function automatorwp_get_tags() {

    $tags = array();

    // ---------------------------------
    // Site tags
    // ---------------------------------

    $tags['site'] = array(
        'label' => __( 'Site', 'automatorwp' ),
        'tags'  => array(),
        'icon'  => AUTOMATORWP_URL . 'assets/img/integration-default.svg',
    );

    $tags['site']['tags']['site_name'] = array(
        'label'     => __( 'Site name', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => get_bloginfo( 'name' ),
    );

    $tags['site']['tags']['site_url'] = array(
        'label'     => __( 'Site URL', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => get_site_url(),
    );

    $tags['site']['tags']['admin_email'] = array(
        'label'     => __( 'Admin email', 'automatorwp' ),
        'type'      => 'email',
        'preview'   => get_bloginfo( 'admin_email' ),
    );

    // ---------------------------------
    // User tags
    // ---------------------------------

    $tags['user'] = array(
        'label' => __( 'User', 'automatorwp' ),
        'tags'  => array(),
        'icon'  => AUTOMATORWP_URL . 'assets/img/integration-default.svg',
    );

    $tags['user']['tags']['user_id'] = array(
        'label'     => __( 'ID', 'automatorwp' ),
        'type'      => 'integer',
        'preview'   => '123',
    );

    $tags['user']['tags']['user_login'] = array(
        'label'     => __( 'Username', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => 'automatorwp',
    );

    $tags['user']['tags']['user_email'] = array(
        'label'     => __( 'Email', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => 'contact@automatorwp.com',
    );

    $tags['user']['tags']['display_name'] = array(
        'label'     => __( 'Display name', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'AutomatorWP Plugin', 'automatorwp' ),
    );

    $tags['user']['tags']['first_name'] = array(
        'label'     => __( 'First name', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => 'AutomatorWP',
    );

    $tags['user']['tags']['last_name'] = array(
        'label'     => __( 'Last name', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Plugin', 'automatorwp' ),
    );

    $tags['user']['tags']['user_url'] = array(
        'label'     => __( 'User\'s website URL', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => 'https://automatorwp.com',
    );

    $tags['user']['tags']['avatar'] = array(
        'label'     => __( 'Avatar', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => '<img src="' . get_option( 'home' ) . '/wp-content/uploads/avatar.jpg'  . '"/>',
    );

    $tags['user']['tags']['avatar_url'] = array(
        'label'     => __( 'Avatar URL', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => get_option( 'home' ) . '/wp-content/uploads/avatar.jpg',
    );

    $tags['user']['tags']['reset_password_url'] = array(
        'label'     => __( 'Reset password URL', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => get_option( 'home' ) . '/wp-login.php?action=rp',
    );

    $tags['user']['tags']['reset_password_link'] = array(
        'label'     => __( 'Reset password link', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => '<a href="' . get_option( 'home' ) . '/wp-login.php?action=rp' . '">' . __( 'Click here to reset your password', 'automatorwp' ) . '</a>',
    );

    $tags['user']['tags']['user_meta:META_KEY'] = array(
        'label'     => __( 'User Meta', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'User meta value, replace "META_KEY" by the user meta key.', 'automatorwp' ),
    );

    // ---------------------------------
    // Date and time tags
    // ---------------------------------

    $tags['date'] = array(
        'label' => __( 'Date and time', 'automatorwp' ),
        'tags'  => array(),
        'icon'  => AUTOMATORWP_URL . 'assets/img/integration-default.svg',
    );

    $tags['date']['tags']['date:FORMAT'] = array(
        'label'     => __( 'Date and time', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'The current date and time, replace "FORMAT" by the date format. Default format is "Y-m-d H:i:s".', 'automatorwp' ),
    );

    $tags['date']['tags']['date:FORMAT:VALUE'] = array(
        'label'     => __( 'Relative date and time', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'The relative date and time, replace "FORMAT" by the date format and "VALUE" by the relative date. Default format is "Y-m-d H:i:s" and default value is "now".', 'automatorwp' ),
    );

    $tags['date']['tags']['timestamp'] = array(
        'label'     => __( 'Timestamp', 'automatorwp' ),
        'type'      => 'int',
        'preview'   => __( 'The current timestamp.', 'automatorwp' ),
    );

    $tags['date']['tags']['timestamp:VALUE'] = array(
        'label'     => __( 'Relative timestamp', 'automatorwp' ),
        'type'      => 'int',
        'preview'   => __( 'The relative timestamp, replace "VALUE" by the relative date. Default value is "now".', 'automatorwp' ),
    );

    /**
     * Filter tags
     *
     * @since 1.0.0
     *
     * @param array $tags The tags
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_tags', $tags );

}

/**
 * Get all automation tags
 *
 * @since 1.0.0
 *
 * @param int $automation_id The automation ID
 *
 * @return array
 */
function automatorwp_get_automation_tags( $automation_id ) {

    $tags = automatorwp_get_tags();
    
    // Get all automation triggers to generate their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $object ) {

        $trigger_tags = automatorwp_get_trigger_tags( $object );

        // Append all trigger tags to the tags array
        foreach( $trigger_tags as $trigger_tag_id => $trigger_tag ) {
            // Note: Don't use array merge since trigger IDs indexes gets replaced
            $tags['t:'.$trigger_tag_id] = $trigger_tag;
        }

    }

    // Get all automation actions to generate their tags
    $actions = automatorwp_get_automation_actions( $automation_id );
    
    foreach( $actions as $object ) {

        $action_tags = automatorwp_get_action_tags( $object );
        
        // Append all action tags to the tags array
        foreach( $action_tags as $action_tag_id => $action_tag ) {
            // Note: Don't use array merge since action IDs indexes gets replaced
            $tags['a:'.$action_tag_id] = $action_tag;
        }

    }
    
    /**
     * Filter all automation tags
     *
     * @since 1.0.0
     *
     * @param array $tags           The automation tags
     * @param int   $automation_id  The automation ID
     *
     * @return array
     */
    return apply_filters( 'automatorwp_automation_tags', $tags, $automation_id );

}

/**
 * Get action tags
 *
 * @since 1.0.0
 *
 * @param stdClass $object The trigger object
 *
 * @return array
 */
function automatorwp_get_action_tags( $object ) {

    $action = automatorwp_get_action( $object->type );

    // Skip item if not has a action registered
    if( ! $action ) {
        return array();
    }

    // Skip action if not has any tags
    if( empty( $action['tags'] ) ) {
        return array();
    }

    $action_tags = array();

    foreach( $action['tags'] as $tag_id => $tag ) {
        $action_tags['a:' . $object->id . ':' . $tag_id] = $tag;
    }
    
    /**
     * Filter action tags to ally dynamic tags inserting
     *
     * @since 1.0.0
     *
     * @param array $tags       The action tags
     * @param int   $action     The action object
     *
     * @return array
     */
    $action_tags = apply_filters( 'automatorwp_action_tags', $action_tags, $object );
    
    // Skip action if not has any tags
    if( empty( $action_tags ) ) {
        return array();
    }

    $integration = automatorwp_get_integration( $action['integration'] );

    $tags = array();

    $tags[$object->id] = array(
        'label' => automatorwp_parse_automation_item_edit_label( $object, 'action', 'edit' ),
        'tags' => array(),
        'icon' => $integration['icon'],
    );

    $tags[$object->id]['tags'] = $action_tags;
    
    return $tags;

}

/**
 * Get trigger tags
 *
 * @since 1.0.0
 *
 * @param stdClass $object The trigger object
 *
 * @return array
 */
function automatorwp_get_trigger_tags( $object ) {

    $trigger = automatorwp_get_trigger( $object->type );

    // Skip item if not has a trigger registered
    if( ! $trigger ) {
        return array();
    }

    // Skip trigger if not has any tags
    if( empty( $trigger['tags'] ) ) {
        return array();
    }

    $trigger_tags = array();

    foreach( $trigger['tags'] as $tag_id => $tag ) {
        $trigger_tags['t:' . $object->id . ':' . $tag_id] = $tag;
    }

    /**
     * Filter trigger tags to ally dynamic tags inserting
     *
     * @since 1.0.0
     *
     * @param array $tags       The trigger tags
     * @param int   $trigger    The trigger object
     *
     * @return array
     */
    $trigger_tags = apply_filters( 'automatorwp_trigger_tags', $trigger_tags, $object );

    // Skip trigger if not has any tags
    if( empty( $trigger_tags ) ) {
        return array();
    }

    $integration = automatorwp_get_integration( $trigger['integration'] );

    $tags = array();

    $tags[$object->id] = array(
        'label' => automatorwp_parse_automation_item_edit_label( $object, 'trigger', 'edit' ),
        'tags' => array(),
        'icon' => $integration['icon'],
    );

    $tags[$object->id]['tags'] = $trigger_tags;

    return $tags;

}

/**
 * Get the tags select element
 *
 * @since 1.0.0
 *
 * @param stdClass  $automation The automation object
 * @param stdClass  $object     The trigger/action object
 * @param string    $item_type  The item type (trigger|action)
 *
 * @return string
 */
function automatorwp_get_tags_selector_html( $automation, $object, $item_type ) {

    $tags = automatorwp_get_automation_tags( $automation->id );

    // Check the conditions to remove the user tags group
    if( isset( $tags['user'] ) ) {
        // Remove from the anonymous user action
        if( $automation->type === 'anonymous' && $object->type === 'automatorwp_anonymous_user' ) {
            unset( $tags['user'] );
        }

        // Remove from triggers
        if( $automation->type === 'anonymous' && $item_type === 'trigger' ) {
            unset( $tags['user'] );
        }
    }

    /**
     * Available filter to override tags displayed on the tag selector
     *
     * @since 1.3.0
     *
     * @param array     $tags       The tags
     * @param stdClass  $automation The automation object
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The item type (trigger|action)
     *
     * @return array
     */
    $tags = apply_filters( 'automatorwp_tags_selector_html_tags', $tags, $automation, $object, $item_type );

    $trigger_sep = false;
    $action_sep = false;
    
    ob_start(); ?>
    <select class="automatorwp-automation-tag-selector">

        <?php foreach( $tags as $tags_group_id => $tags_group ) {

            // Triggers separator
            if( ! $trigger_sep && automatorwp_starts_with( $tags_group_id, 't:' ) ) {
                $trigger_sep = true;
                echo '<option value="triggers_sep" disabled="disabled">' . __( 'Triggers', 'automatorwp' ) . '</option>';
            }

            // Actions separator
            if( ! $action_sep && automatorwp_starts_with( $tags_group_id, 'a:' ) ) {
                $action_sep = true;
                echo '<option value="actions_sep" disabled="disabled">' . __( 'Actions', 'automatorwp' ) . '</option>';
            }

            echo automatorwp_get_tags_selector_group_html( $tags_group_id, $tags_group );
        } ?>

    </select>

    <?php $html = ob_get_clean();

    return $html;

}

/**
 * Get optgroup element from a group of tags
 *
 * @since 1.0.0
 *
 * @param string    $tags_group_id  The tags group ID
 * @param array     $tags_group      The tags group args
 *
 * @return string
 */
function automatorwp_get_tags_selector_group_html( $tags_group_id, $tags_group ) {

    ob_start(); ?>

    <optgroup label="<?php echo esc_attr( $tags_group['label'] ); ?>"
              data-id="<?php echo esc_attr( $tags_group_id ); ?>"
              data-icon="<?php echo esc_attr( $tags_group['icon'] ); ?>">

        <?php foreach( $tags_group['tags'] as $tag_id => $tag ) :
            // Formatted text to make tags more visible
            $text = '<strong>' . esc_attr( $tag['label'] ) . '</strong> <span>' . ( isset( $tag['preview'] ) ? htmlspecialchars( esc_attr( $tag['preview'] ) ) : '' ) . '</span>'; ?>

            <option value="<?php echo esc_attr( $tag_id ); ?>" data-text="<?php echo $text; ?>"><?php echo $tag['label']; ?></option>
        <?php endforeach; ?>

    </optgroup>

    <?php $html = ob_get_clean();

    return $html;

}

/**
 * Parse automation tags to received content
 *
 * @since 1.1.0
 *
 * @param int       $automation_id  The automation ID
 * @param int       $user_id        The user ID
 * @param mixed     $content        The content to parse (arrays supported)
 *
 * @return string|array
 */
function automatorwp_parse_automation_tags( $automation_id = 0, $user_id = 0, $content = '' ) {

    // Check if content given is an array to parse each array element
    if( is_array( $content ) ) {

        foreach( $content as $k => $v ) {
            // Replace all tags on this array element
            $content[$k] = automatorwp_parse_automation_tags( $automation_id, $user_id, $v );
        }

        return $content;
        
    }
    
    // Get all tags replacements to being passed to all actions
    $replacements = automatorwp_get_automation_tags_replacements( $automation_id, $user_id, $content );
    
    $tags = array_keys( $replacements );

    $parsed_content = $content;

    // First, parse dynamic tags like post meta, user meta and other plugin tags

    // Parse user meta tags (required here since user meta tags are based on the content)
    $parsed_content = automatorwp_parse_user_meta_tags( $user_id, $parsed_content );

    // Parse date tags
    $parsed_content = automatorwp_parse_date_tags( $parsed_content );

    /**
     * Available filter to setup custom replacements
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param array     $replacements       Automation replacements
     * @param int       $automation_id      The automation ID
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'automatorwp_parse_automation_tags', $parsed_content, $replacements, $automation_id, $user_id, $content );

    // Finally, parse automation tags ensuring that all tags not parsed will be empty
    $parsed_content = str_replace( $tags, $replacements, $parsed_content );

    /**
     * Available filter to setup custom replacements (with parsed content parsed)
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param array     $replacements       Automation replacements
     * @param int       $automation_id      The automation ID
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'automatorwp_post_parse_automation_tags', $parsed_content, $replacements, $automation_id, $user_id, $content );

    return $parsed_content;

}

/**
 * Get the automation tags replacements
 *
 * @since 1.0.0
 *
 * @param int       $automation_id  The automation ID
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 *
 * @return array
 */
function automatorwp_get_automation_tags_replacements( $automation_id = 0, $user_id = 0, $content = '' ) {

    $replacements = array();

    // Look for tags
    preg_match_all( "/\{\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            /**
             * Filter to skip a tag replacement
             *
             * @since 1.0.0
             *
             * @param bool      $skip           If tag should get skipped, by default false
             * @param string    $tag_name       The tag name (without "{}")
             * @param int       $automation_id  The automation ID
             * @param int       $user_id        The user ID
             * @param string    $content        The content to parse
             *
             * @return bool
             */
            $skip = apply_filters( 'automatorwp_skip_tag_replacement', false, $tag_name, $automation_id, $user_id, $content );

            if( ! $skip ) {
                // Setup tags replacements
                $replacements['{' . $tag_name . '}'] = automatorwp_get_tag_replacement( $tag_name, $automation_id, $user_id, $content );
            }
        }

    }

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        // Look for trigger tags
        preg_match_all( "/\{t:" . $trigger->id . ":\s*(.*?)\s*\}/", $content, $matches );

        if( is_array( $matches ) && !empty( $matches[1] ) ) {

            $trigger_replacements = automatorwp_get_trigger_tags_replacements( $trigger, $user_id, $content );

            foreach( $trigger_replacements as $trigger_tag => $trigger_replacement ) {
                // Tags on triggers are as {t:id:tag}
                $replacements['{t:' . $trigger->id . ':' . $trigger_tag. '}'] = $trigger_replacement;
            }

        }

        // Compatibility for old tags from <4.2.0 versions
        // Look for trigger old tags
        preg_match_all( "/\{" . $trigger->id . ":\s*(.*?)\s*\}/", $content, $matches );

        if ( is_array( $matches ) && !empty( $matches[1] ) ) {

            $trigger_replacements = automatorwp_get_trigger_tags_replacements( $trigger, $user_id, $content );

            foreach( $trigger_replacements as $trigger_tag => $trigger_replacement ) {
                // Tags on triggers are as {id:tag}
                $replacements['{' . $trigger->id . ':' . $trigger_tag. '}'] = $trigger_replacement;
            }
        }
    }

    // Get automation actions to pass their tags
    $actions = automatorwp_get_automation_actions( $automation_id );

    foreach( $actions as $action ) {

        $action_replacements = automatorwp_get_action_tags_replacements( $action, $user_id, $content );

        foreach( $action_replacements as $action_tag => $action_replacement ) {
            // Tags on actions are as {a:id:tag}
            $replacements['{a:' . $action->id . ':' . $action_tag. '}'] = $action_replacement;
        }

    }

    /**
     * Available filter to setup custom replacements
     *
     * @since 1.0.0
     *
     * @param array     $replacements   Automation replacements
     * @param int       $automation_id  The automation ID
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_automation_tags_replacements', $replacements, $automation_id, $user_id, $content );

}

/**
 * Get the trigger tags replacements
 *
 * @since 1.0.0
 *
 * @param stdClass  $trigger    The trigger object
 * @param int       $user_id    The user ID
 * @param string    $content    The content to parse
 *
 * @return array
 */
function automatorwp_get_trigger_tags_replacements( $trigger, $user_id, $content = '' ) {

    // Get the last completion log for this trigger (where data for tags replacement is)
    $log = automatorwp_get_trigger_last_completion_log( $trigger, $user_id, $content );
    
    if( ! $log ) {
        return array();
    }

    ct_setup_table( 'automatorwp_logs' );

    $replacements = array();
	
	// Look for trigger tags
    preg_match_all( "/\{" . $trigger->id . ":\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            $replacements[$tag_name] = automatorwp_get_trigger_tag_replacement( $tag_name, $trigger, $user_id, $content, $log );

        }

    }
	
    // Look for trigger tags
    preg_match_all( "/\{t:" . $trigger->id . ":\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            $replacements[$tag_name] = automatorwp_get_trigger_tag_replacement( $tag_name, $trigger, $user_id, $content, $log );

        }

    }

    /**
     * Filter to setup custom trigger tags replacements
     *
     * Note: Post and times tags replacements are already passed
     *
     * @since 1.0.0
     *
     * @param array     $replacements   The trigger replacements
     * @param stdClass  $trigger        The trigger object
     * @param int       $user_id        The user ID
     * @param stdClass  $log            The last trigger log object
     *
     * @return array
     */
    $replacements = apply_filters( 'automatorwp_trigger_tags_replacements', $replacements, $trigger, $user_id, $log );

    ct_reset_setup_table();
    
    return $replacements;

}

/**
 * Get the action tags replacements
 *
 * @since 1.0.0
 *
 * @param stdClass  $action    The action object
 * @param int       $user_id    The user ID
 * @param string    $content    The content to parse
 *
 * @return array
 */
function automatorwp_get_action_tags_replacements( $action, $user_id, $content = '' ) {

    // Get the last completion log for this action (where data for tags replacement is)
    $log = automatorwp_get_action_last_completion_log( $action, $user_id, $content );
    
    if( ! $log ) {
        return array();
    }

    ct_setup_table( 'automatorwp_logs' );

    $replacements = array();

    // Look for action tags
    preg_match_all( "/\{a:" . $action->id . ":\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            $replacements[$tag_name] = automatorwp_get_action_tag_replacement( $tag_name, $action, $user_id, $content, $log );

        }

    }

    /**
     * Filter to setup custom action tags replacements
     *
     * Note: Post and times tags replacements are already passed
     *
     * @since 1.0.0
     *
     * @param array     $replacements   The trigger replacements
     * @param stdClass  $action         The trigger object
     * @param int       $user_id        The user ID
     * @param stdClass  $log            The last trigger log object
     *
     * @return array
     */
    $replacements = apply_filters( 'automatorwp_action_tags_replacements', $replacements, $action, $user_id, $log );

    ct_reset_setup_table();
    
    return $replacements;

}

/**
 * Trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $action         The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_get_action_tag_replacement( $tag_name, $action, $user_id, $content, $log ) {

    $replacement = '';

    // Post tags
    $post_replacement = automatorwp_get_post_tag_replacement( $tag_name, $action, 'action', $user_id, $content, $log );

    if( ! empty( $post_replacement ) ) {
        $replacement = $post_replacement;
    }

    // Comment tags
    $comment_replacement = automatorwp_get_comment_tag_replacement( $tag_name, $action, 'action', $user_id, $content, $log );

    if( ! empty( $comment_replacement ) ) {
        $replacement = $comment_replacement;
    }

    // User tags
    $user_replacement = automatorwp_get_user_tag_replacement( $tag_name, $action, 'action', $user_id, $content, $log );

    if( ! empty( $user_replacement ) ) {
        $replacement = $user_replacement;
    }

    /**
     * Filter the action tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last trigger log object
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_action_tag_replacement', $replacement, $tag_name, $action, $user_id, $content, $log );

}

/**
 * Trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_get_trigger_tag_replacement( $tag_name, $trigger, $user_id, $content, $log ) {

    $replacement = '';

    switch( $tag_name ) {
        case 'times':
            if( in_array( $trigger->type, array( 'automatorwp_all_users', 'automatorwp_all_posts' ) ) ) {
                $replacement = automatorwp_get_object_completion_times( $trigger->id, 'trigger' );
            } else {
                $replacement = automatorwp_get_user_completion_times( $trigger->id, $user_id, 'trigger' );
            }
            break;
    }

    // Post tags
    $post_replacement = automatorwp_get_post_tag_replacement( $tag_name, $trigger, 'trigger', $user_id, $content, $log );

    if( ! empty( $post_replacement ) ) {
        $replacement = $post_replacement;
    }

    // Comment tags
    $comment_replacement = automatorwp_get_comment_tag_replacement( $tag_name, $trigger, 'trigger', $user_id, $content, $log );

    if( ! empty( $comment_replacement ) ) {
        $replacement = $comment_replacement;
    }

    /**
     * Filter the trigger tag replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $trigger        The trigger object
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last trigger log object
     *
     * @return string
     */
    return apply_filters( 'automatorwp_get_trigger_tag_replacement', $replacement, $tag_name, $trigger, $user_id, $content, $log );

}

/**
 * Turns a parameters string to array
 * A to array( A )
 * A:B:C to array( A, B, C )
 * A:[B1,B2]:C to array( A, array( B1, B2 ), C )
 *
 * @since 1.0.0
 *
 * @param string    $tag            The tag name (without "{}")
 * @param string    $parameters     The tags parameters
 *
 * @return array
 */
function automatorwp_get_tag_parameters_to_array( $tag, $parameters ) {

    preg_match_all( "/([^\:]*[^\:])/", $parameters, $matches );
    $array = array();

    if( is_array( $matches ) && isset( $matches[0] ) ) {

        foreach( $matches[0] as $param ) {

            if( strpos( $param, '[' ) !== false ) {
                // Array value like [ A, B, C ]

                // Remove [ and ]
                $param = substr_replace( $param, '', strpos( $param, '[' ), 1 );
                $param = substr_replace( $param, '', strrpos( $param, ']' ), 1 );

                $param = explode(',', $param );

                foreach ( $param as $i => $p ) {
                    $param[$i] = trim( $p );
                }
            } else {
                // Single value
                $param = trim( $param );
            }

            $array[] = $param;


        }

    } else {
        $array = array( trim( $parameters ) );
    }

    /**
     * Filter to turn a parameters string to array
     *
     * @since 1.0.0
     *
     * @param string    $array          The tags parameters as array
     * @param string    $tag            The tag name (without "{}")
     * @param string    $parameters     The tags parameters string
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_tag_parameters_to_array', $array, $tag, $parameters );

}