<?php
/**
 * Tags
 *
 * @package     AutomatorWP\QSM\Tags
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Quiz tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_qsm_get_quiz_tags() {

    return array(
        'quiz_name' => array(
            'label' => __('Quiz name', 'automatorwp-qsm'),
            'type' => 'text',
            'preview' => 'The quiz name'
        ),
        'quiz_id' => array(
            'label' => __('Quiz id', 'automatorwp-qsm'),
            'type' => 'integer',
            'preview' => '123'
        ),
        'points' => array(
            'label' => __('Points', 'automatorwp-qsm'),
            'type' => 'integer',
            'preview' => '123'
        ),
    );

}

/**
 * Custom trigger tag replacement
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
function automatorwp_qsm_get_trigger_quiz_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {
    
    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'qsm' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'quiz_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'quiz_name', true );
            break;
        case 'quiz_id':
            $replacement = automatorwp_get_log_meta( $log->id, 'quiz_id', true );
            break;
        case 'points':
            $replacement = automatorwp_get_log_meta( $log->id, 'points', true );
            break;
    }
    return $replacement;

}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_qsm_get_trigger_quiz_tag_replacement', 10, 6 );
