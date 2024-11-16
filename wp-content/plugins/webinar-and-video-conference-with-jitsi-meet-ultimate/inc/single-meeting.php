<?php
if (!defined('ABSPATH')) {
	exit;
}
$prefix = 'jitsi_opt_';
global $options;
$options = get_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', true);
$gmt_offset = get_option('gmt_offset');
$gmt_offset_val = $gmt_offset*60*60;
$saved_time = $options['jitsi_pro__start_time']; 
global $attende_login;
$attende_login = false;
$nexttime = 0;


if(isset($_POST['meeting_action'])){
    if ( !wp_verify_nonce( $_POST['nonce'], "jitsi_meeting_nonce")) {
        exit("No naughty business please");
    } 
    if($_POST['meeting_action'] == 'start'){
        if(!(((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) < 0)){
            $saved_time = current_time('Y-m-d H:i:s');
            if($options['jitsi_pro__recurring']){
                $recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'start_time'] = $saved_time;
                update_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', $recuring_meeting_data);
            } else {
                $options['jitsi_pro__start_time'] = $saved_time;
                update_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', $options);
            }
        }   
        
        if(!(((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) > 0)){
            $saved_time = current_time('Y-m-d H:i:s');
            if(!$options['jitsi_pro__recurring']){
                $options['jitsi_pro__start_time'] = $saved_time;
                update_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', $options);
            }
        }
    }

    if($_POST['meeting_action'] == 'end'){
        if($options['jitsi_pro__recurring']){
            $recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'] = current_time('Y-m-d H:i:s');
            update_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', $recuring_meeting_data);
        } else {
            $options['jitsi_pro__end_time'] = current_time('Y-m-d H:i:s');
            update_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', $options);
        }
    }
}

if(isset($options['jitsi_pro__recurring']) && $options['jitsi_pro__recurring']){
    $recuring_meeting_data = get_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', true) ? get_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', true) : [];
    switch($options['jitsi_pro__recurring_repeat']){
        case 'day':
            $saved_time = $options['jitsi_pro__recurrence_time'];
            if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                $nexttime = 24*60*60;
            }
            break;
        case 'week':
            $saved_time = $options['jitsi_pro__recurrence_time'];
            $dayname = $options['jitsi_pro__recurring_on_weekday'];
            if(strtolower(date('l')) == $dayname){
                if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                    $nexttime = 7*24*60*60;
                }
            } else {   
                $saved_time = date('Y-m-d H:i', strtotime('next ' . $dayname . $options['jitsi_pro__recurrence_time']) );
            }
            break;
        case 'month':
            $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
            $dateofmonth = $options['jitsi_pro__recurring_on_monthly'];
            if(date('j') == $dateofmonth){
                if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
                }
            } elseif(date('j') > $dateofmonth) {
                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
            } else {
                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m'), $dateofmonth, date('Y')));
            }
            break;
        case 'year':
            $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
            $monthofyear = $options['jitsi_pro__recurring_on_yearly'];
            $dateofmonth = $options['jitsi_pro__recurring_on_yearly_date'];
            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')));
            if(date('n') == $monthofyear){
                if(date('j') == $dateofmonth){
                    if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                    }
                }                
                if(date('j') > $dateofmonth){
                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                }
            } 

            if(date('n') > $monthofyear){
                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
            }
            break;
        default:
            $saved_time = $options['jitsi_pro__recurrence_time'];
    }
}

get_header();

if(isset($_POST['meeting_password'])){
    if ( !wp_verify_nonce( $_POST['nonce'], "jitsi_meeting_login_nonce")) {
        exit("No naughty business please");
    } 

    if(!isset($_POST['meeting_email'])){
        exit("No naughty business please");
    }
    $attendee = get_post_meta(get_the_ID(), 'registered_attendee', true) ? get_post_meta(get_the_ID(), 'registered_attendee', true) : [];
    if($_POST['meeting_password'] == $options['jitsi_pro__password'] && in_array($_POST['meeting_email'], $attendee)){
        $attende_login = true;
    }
}

?>
<div class="jitsi-container">
    <div class="jitsi-row">
        <div class="jitsi-col-8">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header>
                <div class="entry-content">
                    <?php 
                    add_filter( 'the_content', 'filter_the_content_for_jitsi', 1 );
    
                    function filter_the_content_for_jitsi( $content ) {
                        ob_start();
                        global $options;
                        global $attende_login;
                        if(!function_exists('jitsi_meeting_output')){
                            function jitsi_meeting_output(){
                                ob_start();
                                $prefix = 'jitsi_opt_';
                                $options = get_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', true);
                                $gmt_offset = get_option('gmt_offset');
                                $gmt_offset_val = $gmt_offset*60*60;
                                $saved_time = $options['jitsi_pro__start_time'];
                                $meeting_running = false;

                                if(isset($options['jitsi_pro__recurring']) && $options['jitsi_pro__recurring']){
                                    $recuring_meeting_data = get_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', true) ? get_post_meta(get_the_ID(), 'jitsi_pro__meeting_recurring_data', true) : [];                                           
                                    
                                    switch($options['jitsi_pro__recurring_repeat']){
                                        case 'day':
                                            $saved_time = $options['jitsi_pro__recurrence_time'];
                                            if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                                $nexttime = 24*60*60;
                                            }
                                            break;
                                        case 'week':
                                            $saved_time = $options['jitsi_pro__recurrence_time'];
                                            $dayname = $options['jitsi_pro__recurring_on_weekday'];
                                            if(strtolower(date('l')) == $dayname){
                                                if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                                    $nexttime = 7*24*60*60;
                                                }
                                            } else {   
                                                $saved_time = date('Y-m-d H:i', strtotime('next ' . $dayname . $options['jitsi_pro__recurrence_time']) );
                                            }
                                            break;
                                        case 'month':
                                            $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
                                            $dateofmonth = $options['jitsi_pro__recurring_on_monthly'];
                                            if(date('j') == $dateofmonth){
                                                if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
                                                }
                                            } elseif(date('j') > $dateofmonth) {
                                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
                                            } else {
                                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m'), $dateofmonth, date('Y')));
                                            }
                                            break;
                                        case 'year':
                                            $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
                                            $monthofyear = $options['jitsi_pro__recurring_on_yearly'];
                                            $dateofmonth = $options['jitsi_pro__recurring_on_yearly_date'];
                                            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')));
                                            if(date('n') == $monthofyear){
                                                if(date('j') == $dateofmonth){
                                                    if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                                    }
                                                }                
                                                if(date('j') > $dateofmonth){
                                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                                }
                                            } 
                                
                                            if(date('n') > $monthofyear){
                                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                            }
                                            break;
                                        default:
                                            $saved_time = $options['jitsi_pro__recurrence_time'];
                                    }
                                    
                                    if(((isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'start_time']) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && !isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time']))){
                                        $meeting_running = true;
                                    } 
                                } else {
                                    if((!isset($options['jitsi_pro__end_time']) || !($options['jitsi_pro__end_time'])) && (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)){
                                        $meeting_running = true;
                                    }
                                }
    
                                if($meeting_running){
                                    $paramArr = array(
                                        'width' 						=> get_option('jitsi_opt_width', 700),
                                        'height' 						=> get_option('jitsi_opt_height', 700),
                                        'startaudioonly'				=> get_option('jitsi_opt_start_audio_only', 0),
                                        'startaudiomuted'				=> get_option('jitsi_opt_start_audio_muted', 10),
                                        'startwithaudiomuted'			=> get_option('jitsi_opt_start_local_audio_muted', 0),
                                        'startsilent'					=> get_option('jitsi_opt_start_silent', 0),
                                        'resolution'					=> get_option('jitsi_opt_video_resolution', 720),
                                        'maxfullresolutionparticipant'	=> get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                                        'disablesimulcast'				=> get_option('jitsi_opt_disableSimulcast', 0),
                                        'startvideomuted'				=> get_option('jitsi_opt_startVideoMuted', 10),
                                        'startwithvideomuted'			=> get_option('jitsi_opt_startWithVideoMuted', 0),
                                        'startscreensharing'			=> get_option('jitsi_opt_startScreenSharing', 0),
                                        'filerecordingsenabled'			=> get_option('jitsi_opt_enable_recording', 1),
                                        'transcribingenabled'			=> get_option('jitsi_opt_enable_transcription', 1),
                                        'livestreamingenabled'			=> get_option('jitsi_opt_enable_livestream', 1),
                                        'enablewelcomepage'				=> get_option('jitsi_opt_enableWelcomePage', 1),
                                        'invite'				        => get_option('jitsi_opt_invite', 1)
                                    );
                    
                                    $post_meta = get_post_meta(get_the_ID(), 'jitsi_pro__meeting_settings', true);
                    
                                    foreach ($paramArr as $key => $value) {
                                        if (isset($post_meta['jitsi_pro__' . $key])) {
                                            $paramArr[$key] = $post_meta['jitsi_pro__' . $key];
                                        }
                                    }
                    
                                    $extra_data = '';
                                    foreach ($paramArr as $key => $value) {
                                        $extra_data .= 'data-' . $key . '="'.$value.'"';
                                    }
                    
                                    $extra_message = '';
                                    if (!get_option($prefix . 'api_key', '')) {
                                        $extra_data .= 'data-loadfree="true"';
                                        $extra_message = 'API key is missing. Loaded meeting via free api';
                                    }
                    
                                    if (!get_option($prefix . 'app_id', '')) {
                                        $extra_data .= 'data-loadfree="true"';
                                        $extra_message = 'APP id is missing. Loaded meeting via free api';
                                    }
                    
                                    if (!get_option($prefix . 'private_key', '')) {
                                        $extra_data .= 'data-loadfree="true"';
                                        $extra_message = 'Private key is missing. Loaded meeting via free api';
                                    }
                                    printf('<div class="jitsi-wrapper" data-name="%1$s" style="width:%2$spx" %3$s></div><p class="extra-message">%4$s</p>', basename(get_permalink(get_the_ID())), $paramArr['width'], $extra_data, $extra_message);    
                                } else {
                                    _e('Meeting is not running now');
                                }
                                echo ob_get_clean();
                            }
                        }

                        $meet_instance = new Jitsi_Meet_WP();
                        $is_ultimate = $meet_instance->is_ultimate_active();
                        $product_of_meeting = get_post_meta(get_the_ID(), '_product_id', true);
                        if($is_ultimate && $product_of_meeting){
                            if($options['jitsi_pro__host'] == get_current_user_id()){
                                jitsi_meeting_output();
                            } else {
                                if($attende_login){
                                    jitsi_meeting_output();
                                } else { ?>
                                    <h4><?php _e('Signin with registration detail to attend the meeting:', 'jitsi-pro'); ?></h4>
                                    <form id="attendee_login_form" method="post">
                                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_login_nonce"); ?>"/>
                                        <input type="email" name="meeting_email" placeholder="<?php esc_attr_e('Meeting Email', 'jitsi-pro'); ?>"/>
                                        <input type="password" name="meeting_password" placeholder="<?php esc_attr_e('Meeting Password', 'jitsi-pro'); ?>"/>
                                        <button type="submit"><?php _e('Signin to Meeting', 'jitsi-pro'); ?></button>
                                    </form>
                                    <h4><?php _e('Or, Buy the ticket for the meeting:', 'jitsi-pro'); ?></h4>
                                    <a class="jitsi-buy-btn" href="<?php echo get_permalink($product_of_meeting); ?>"><?php _e('Buy Now', 'jitsi-pro'); ?></a>
                                <?php }
                            }
                        } else {
                            if(isset($options['jitsi_pro__should_register']) && $options['jitsi_pro__should_register']){
                                if(!($options['jitsi_pro__host'] == get_current_user_id()) && !($options['jitsi_pro__end_time'])){
                                    if($attende_login){
                                        jitsi_meeting_output();
                                    } else { 
                                        ?>
                                        <h4><?php _e('Signin with registration detail to attend the meeting:', 'jitsi-pro'); ?></h4>
                                        <form id="attendee_login_form" method="post">
                                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_login_nonce"); ?>"/>
                                            <div class="jitsi-form-row">
                                                <div class="jitsi-form-col">
                                                    <input type="email" name="meeting_email" placeholder="<?php esc_attr_e('Meeting Email', 'jitsi-pro'); ?>"/>
                                                </div>
                                                <div class="jitsi-form-col">
                                                    <input type="password" name="meeting_password" placeholder="<?php esc_attr_e('Meeting Password', 'jitsi-pro'); ?>"/>
                                                </div>
                                                <div class="jitsi-form-col">
                                                    <button type="submit"><?php _e('Signin to Meeting', 'jitsi-pro'); ?></button>
                                                </div>
                                            </div>
                                        </form>
                                        <h4><?php _e('Or, Register to join the meeting:', 'jitsi-pro'); ?></h4>
                                        <form id="attendee_registration_form" method="post">
                                            <p class="form-message"></p>
                                            <input type="hidden" name="action" value="register_to_meeting"/>
                                            <input type="hidden" name="post_id" value="<?php the_ID(); ?>"/>
                                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_register_nonce"); ?>"/>
                                            <div class="jitsi-form-row">
                                                <div class="jitsi-form-col">
                                                    <input type="text" name="meeting_rname" id="meeting_rname" placeholder="<?php esc_html_e('Your Name', 'jitsi-pro'); ?>"/> 
                                                </div>
                                                <div class="jitsi-form-col">
                                                    <input type="email" name="meeting_remail" id="meeting_remail" placeholder="<?php esc_attr_e('Your Email', 'jitsi-pro'); ?>"/>
                                                </div>
                                                <div class="jitsi-form-col">
                                                    <button type="submit"><?php _e('Register to Meeting', 'jitsi-pro'); ?></button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php }
                                }
                                if ($options['jitsi_pro__host'] == get_current_user_id() && !$options['jitsi_pro__end_time']) {
                                    jitsi_meeting_output();
                                }
                            } elseif(isset($options['jitsi_pro__booked_meeting']) && $options['jitsi_pro__booked_meeting']){
                                if(get_current_user_id() == $options['jitsi_pro__booked_for']){
                                    if(((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) < 0){
                                        _e('You are too early. Your schedule is:', 'jitsi-pro');
                                        ?><dd class="jitsi-usertime" data-time="<?php echo date('n/d/Y g:i:s A T', (strtotime($saved_time) - $gmt_offset_val) + $nexttime); ?>"></dd><?php 
                                    } else {
                                        jitsi_meeting_output();
                                    }                            
                                } elseif($options['jitsi_pro__host'] == get_current_user_id()){
                                    jitsi_meeting_output();
                                }else {
                                    _e('Sorry this is a booked meeting', 'jitsi-pro');
                                }
                            } else {
                                jitsi_meeting_output();
                            }
                        }
                        $jitsi_meet_content = ob_get_clean();
                        return $content . $jitsi_meet_content;
                    }
                    the_content(); 
                    ?>
                </div>
            </article>
        </div>
        <div class="jitsi-col-4">
            <div class="jitsi-sidebar">
                <div class="jitsi-single-widget jitsi-single-widget-countdown">
                    <?php if(isset($options['jitsi_pro__recurring']) && $options['jitsi_pro__recurring']){ 
                        switch($options['jitsi_pro__recurring_repeat']){
                            case 'day':
                                $saved_time = $options['jitsi_pro__recurrence_time'];
                                if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                    $nexttime = 24*60*60;
                                }
                                break;
                            case 'week':
                                $saved_time = $options['jitsi_pro__recurrence_time'];
                                $dayname = $options['jitsi_pro__recurring_on_weekday'];
                                if(strtolower(date('l')) == $dayname){
                                    if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                        $nexttime = 7*24*60*60;
                                    }
                                } else {   
                                    $saved_time = date('Y-m-d H:i', strtotime('next ' . $dayname . $options['jitsi_pro__recurrence_time']) );
                                }
                                break;
                            case 'month':
                                $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
                                $dateofmonth = $options['jitsi_pro__recurring_on_monthly'];
                                if(date('j') == $dateofmonth){
                                    if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
                                    }
                                } elseif(date('j') > $dateofmonth) {
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m')+1, $dateofmonth, date('Y')));
                                } else {
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m'), $dateofmonth, date('Y')));
                                }
                                break;
                            case 'year':
                                $saved_timeval = explode(':', $options['jitsi_pro__recurrence_time']);
                                $monthofyear = $options['jitsi_pro__recurring_on_yearly'];
                                $dateofmonth = $options['jitsi_pro__recurring_on_yearly_date'];
                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')));
                                if(date('n') == $monthofyear){
                                    if(date('j') == $dateofmonth){
                                        if(isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])){
                                            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                        }
                                    }                
                                    if(date('j') > $dateofmonth){
                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                    }
                                } 
                    
                                if(date('n') > $monthofyear){
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')+1));
                                }
                                break;
                            default:
                                $saved_time = $options['jitsi_pro__recurrence_time'];
                        }
                        ?>
                        <h4 class="jitsi-widget-title"><?php esc_html_e('Next meeting on', 'jitsi-pro'); ?></h4>
                        <div class="jitsi-widget-inner">
                            <?php if((array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'start_time', $recuring_meeting_data) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'end_time', $recuring_meeting_data)){ ?>
                                <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', (strtotime($saved_time) - $gmt_offset_val) + $nexttime); ?>"></div>
                            <?php } elseif((array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'start_time', $recuring_meeting_data) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && !(array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'end_time', $recuring_meeting_data))) { ?>
                               <div class="jitsi-countdown"><span class="jitsi-countedown-block"><span class="jitsi-countdown-value"><?php _e('Meeting is running', 'jitsi-pro'); ?></span><span class="jitsi-countdown-label"><?php _e('The meeting is started and running', 'jitsi-pro'); ?></span></span></div>
                            <?php } else { ?>
                                <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($saved_time) - $gmt_offset_val); ?>"></div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <h4 class="jitsi-widget-title"><?php esc_html_e('Time to go', 'jitsi-pro'); ?></h4>
                        <div class="jitsi-widget-inner">
                            <?php if(!isset($options['jitsi_pro__end_time']) || !$options['jitsi_pro__end_time']){ ?>
                                <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($options['jitsi_pro__start_time']) - $gmt_offset_val); ?>"></div>
                            <?php } else { 
                                if(isset($options['jitsi_pro__booked_meeting']) && $options['jitsi_pro__booked_meeting']){
                                    if(isset($options['jitsi_pro__end_time']) && $options['jitsi_pro__end_time']){
                                        ?>
                                        <div class="jitsi-countdown"><span class="jitsi-countedown-block"><span class="jitsi-countdown-value"><?php _e('Meeting is finished'); ?></span><span class="jitsi-countdown-label"><?php _e('You are late for the meeting. The meeting was helded', 'jitsi-pro'); ?></span></span></div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($options['jitsi_pro__start_time']) - $gmt_offset_val); ?>"></div>
                                        <?php 
                                    }                                    
                                } else {
                                    ?>
                                    <div class="jitsi-countdown"><span class="jitsi-countedown-block"><span class="jitsi-countdown-value"><?php _e('Meeting is finished'); ?></span><span class="jitsi-countdown-label"><?php _e('You are late for the meeting. The meeting was helded', 'jitsi-pro'); ?></span></span></div>
                                    <?php
                                }
                            } ?>
                        </div>
                    <?php } ?>
                </div>

                <?php 
                if($options['jitsi_pro__host'] == get_current_user_id()){
                    if(isset($options['jitsi_pro__recurring']) && $options['jitsi_pro__recurring']){
                        if(date("Y-m-d") == date("Y-m-d", strtotime($saved_time))){
                            if(!(array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'end_time', $recuring_meeting_data))){
                                ?>
                                <div class="jitsi-single-widget jitsi-single-widget-host-actions">
                                    <div class="jitsi-widget-inner">
                                        <form id="jitsi-meeting-host-actions" method="post">
                                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_nonce"); ?>"/>
                                            <?php if(array_key_exists( 'jitsi_pro__' . date("Y-m-d") . 'start_time', $recuring_meeting_data) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)){ ?>
                                                <button type="submit" name="meeting_action" value="end"><?php _e('End Meeting', 'jitsi-pro'); ?></button>
                                            <?php } else { ?>
                                                <button type="submit" name="meeting_action" value="start"><?php _e('Start Meeting', 'jitsi-pro'); ?></button>
                                            <?php } ?>
                                        </form>
                                        <p><?php _e('You are seeing this because you are the author of this meeting', 'jitsi-pro') ?></p>
                                    </div>
                                </div>
                                <?php 
                            }
                        }
                    } else {
                        if((!isset($options['jitsi_pro__end_time']) || !($options['jitsi_pro__end_time'])) && (date("Y-m-d") == date("Y-m-d", strtotime($saved_time)))){
                            ?>
                            <div class="jitsi-single-widget jitsi-single-widget-host-actions">
                                <div class="jitsi-widget-inner">
                                    <form id="jitsi-meeting-host-actions" method="post">
                                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_nonce"); ?>"/>
                                        <?php if(((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0){ ?>
                                            <button type="submit" name="meeting_action" value="end"><?php _e('End Meeting', 'jitsi-pro'); ?></button>
                                        <?php } else { ?>
                                            <button type="submit" name="meeting_action" value="start"><?php _e('Start Meeting', 'jitsi-pro'); ?></button>
                                        <?php } ?>
                                    </form>
                                    <p><?php _e('You are seeing this because you are the author of this meeting', 'jitsi-pro') ?></p>
                                </div>
                            </div>
                            <?php 
                        }
                    }
                }
                ?>

                <div class="jitsi-single-widget jitsi-single-widget-detail">
                    <h4 class="jitsi-widget-title"><?php esc_html_e('Details', 'jitsi-pro'); ?></h4>
                    <div class="jitsi-widget-inner">
                        <dl>
                            <dt><?php _e('Topic:', 'jitsi-pro'); ?></dt>
                            <dd><?php the_title(); ?></dd>
                            <dt><?php _e('Hosted by:', 'jitsi-pro'); ?></dt>
                            <dd><?php 
                                $host = get_user_by( 'id', $options['jitsi_pro__host'] );
                                echo $host->data->display_name;
                            ?></dd>
                            <?php 
                            if(isset($options['jitsi_pro__recurring']) && $options['jitsi_pro__recurring']){ 
                                ?>
                                <dt><?php _e('Host Time:', 'jitsi-pro'); ?></dt>
                                <dd>
                                    <?php 
                                        echo date(get_option('date_format') .' '. get_option('time_format'), strtotime($saved_time) + $nexttime); 
                                        if($gmt_offset > 0){
                                            echo ' (GMT+' . $gmt_offset . ')';
                                        } else {
                                            echo ' (GMT' . $gmt_offset . ')';
                                        }
                                    ?>
                                </dd>
                                <dt><?php _e('GMT Time:', 'jitsi-pro'); ?></dt>
                                <dd>
                                    <?php
                                    if($gmt_offset > 0){
                                        echo date(get_option('date_format') .' '. get_option('time_format'), (strtotime($saved_time) - $gmt_offset_val) + $nexttime);
                                    } else {
                                        echo date(get_option('date_format') .' '. get_option('time_format'), strtotime($saved_time) + $gmt_offset_val + $nexttime);
                                    }
                                    ?>
                                </dd>
                                <dt><?php _e('Your Time', 'jisi-pro'); ?></dt>
                                <dd class="jitsi-usertime" data-time="<?php echo date('n/d/Y g:i:s A T', (strtotime($saved_time) - $gmt_offset_val) + $nexttime); ?>"></dd>
                            <?php } else { ?>
                                <dt><?php _e('Host Time:', 'jitsi-pro'); ?></dt>
                                <dd>
                                    <?php 
                                        echo date(get_option('date_format') .' '. get_option('time_format'), strtotime($saved_time)); 
                                        if($gmt_offset > 0){
                                            echo ' (GMT+' . $gmt_offset . ')';
                                        } else {
                                            echo ' (GMT' . $gmt_offset . ')';
                                        }
                                    ?>
                                </dd>
                                <dt><?php _e('GMT Time:', 'jitsi-pro'); ?></dt>
                                <dd>
                                    <?php
                                    if($gmt_offset > 0){
                                        echo date(get_option('date_format') .' '. get_option('time_format'), strtotime($options['jitsi_pro__start_time']) - $gmt_offset_val);
                                    } else {
                                        echo date(get_option('date_format') .' '. get_option('time_format'), strtotime($options['jitsi_pro__start_time']) + $gmt_offset_val);
                                    }
                                    ?>
                                </dd>
                                <dt><?php _e('Your Time', 'jisi-pro'); ?></dt>
                                <dd class="jitsi-usertime" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($options['jitsi_pro__start_time']) - $gmt_offset_val); ?>"></dd>
                            <?php } ?>
                        </dl>
                    </div>
                </div>

                <?php if(((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) < strtotime($saved_time))){ ?>
                <div class="jitsi-single-widget jitsi-single-widget-add-calendar">
                    <h4 class="jitsi-widget-title"><?php esc_html_e('Add to', 'jitsi-pro'); ?></h4>
                    <div class="jitsi-widget-inner">
                        <?php $meeting_linked_text =  get_the_title() . ' <a href="'.get_the_permalink().'">'.esc_html__('Go to Meeting', 'jitsi-pro').'</a>'?>
                        <a class="jitsi-meeting-add-to" href="http://www.google.com/calendar/render?action=TEMPLATE&text=<?php the_title(); ?>&dates=<?php echo date('Ymd\\THi00\\Z', strtotime($saved_time) + $nexttime - $gmt_offset_val); ?>/<?php echo date('Ymd\\THi00\\Z', strtotime($saved_time) + $nexttime + (3600 * (isset($options['jitsi_pro__duration']) ? $options['jitsi_pro__duration'] : 24)) - $gmt_offset_val); ?>&details=<?php echo esc_attr($meeting_linked_text); ?>" target="_blank" rel="nofollow">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:v="https://vecta.io/nano" viewBox="0 0 215.147 215.147"><path d="M22.583 12.391v24.597h-1.264C9.564 36.988 0 46.55 0 58.305v1.127l.168 1.114 7.415 49.08v.137 77.993c0 11.755 9.563 21.317 21.319 21.317h157.343c11.755 0 21.319-9.563 21.319-21.317v-77.993-.137l7.415-49.08.168-1.114v-1.127c0-11.755-9.564-21.317-21.319-21.317h-1.264V12.391a6.32 6.32 0 0 0-6.319-6.317H28.902a6.32 6.32 0 0 0-6.319 6.317zm15 8.683h139.98v15.914H37.583V21.074zm156.245 30.914a6.32 6.32 0 0 1 6.319 6.317l-7.584 50.194v1.264 40.079 37.914a6.32 6.32 0 0 1-6.319 6.317H28.902a6.32 6.32 0 0 1-6.319-6.317v-37.914-40.079-1.264L15 58.305a6.32 6.32 0 0 1 6.319-6.317h172.509zM55.591 141.714l.008-.296.657-1.971h13.539v2c0 3.252 1.14 5.913 3.485 8.137 2.375 2.25 5.395 3.345 9.232 3.345 3.749 0 6.589-1.099 8.684-3.36 2.135-2.306 3.173-5.278 3.173-9.088 0-4.358-.966-7.604-2.873-9.646-1.87-2.001-4.984-3.017-9.253-3.017H71.542V115.6h10.701c4.082 0 6.935-.941 8.48-2.797 1.635-1.966 2.464-4.852 2.464-8.577 0-3.466-.882-6.152-2.697-8.212-1.749-1.983-4.359-2.947-7.979-2.947-3.585 0-6.447 1.06-8.749 3.239-2.273 2.149-3.377 4.774-3.377 8.027v2H57.052l-.911-1.821-.013-.439c-.198-6.46 2.266-12.04 7.322-16.588 4.977-4.475 11.39-6.743 19.062-6.743 7.74 0 13.935 2.022 18.411 6.012 4.543 4.051 6.846 10.001 6.846 17.688 0 4.021-1.072 7.743-3.187 11.064-1.447 2.271-3.301 4.214-5.537 5.803 2.689 1.617 4.867 3.708 6.499 6.248 2.26 3.519 3.406 7.795 3.406 12.71 0 7.721-2.51 13.887-7.459 18.326-4.887 4.385-11.273 6.608-18.98 6.608-7.084 0-13.414-2.12-18.814-6.301-5.576-4.314-8.304-10.096-8.106-17.186zm82.983-46.051l-17.026.161V83.841l31.661-3.32v83.552h-14.635v-68.41z"/></svg>
                            <span><?php _e('google calendar', 'jitsi-pro') ?></span>
                        </a>
                    </div>
                </div>  
                <?php } ?>                
            </div>
        </div>
    </div>
</div>
<?php 
get_footer();