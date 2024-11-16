<?php

if (!defined('ABSPATH')) {
    exit;
}

class Jitsi_Pro_Post
{
    public $metafields;
    public $prefix;

    public function __construct()
    {
        add_action('init', [$this, 'jitsi_pro_post_type']);
        add_filter('manage_meeting_posts_columns', [$this, 'jitsi_pro_edit_meeting_columns']);
        add_action('manage_meeting_posts_custom_column', [$this, 'jitsi_pro_book_column'], 10, 2);
        add_filter('single_template', [$this, 'jitsi_pro_meeting_template']);
        add_action('add_meta_boxes', [$this, 'jitsi_pro_post_meta_box']);
        add_action('save_post', [$this, 'jitsi_pro_post_meta_save']);
        $this->prefix = 'jitsi_pro__';
        $this->setMetaFields();
        add_action( 'wp_ajax_register_to_meeting', [$this, 'jitsi_register_to_meeting'] );
        add_action( 'wp_ajax_nopriv_register_to_meeting', [$this, 'jitsi_register_to_meeting'] );
    }

    public function setMetaFields()
    {
        $this->metafields = array(
            array(
                'name'              => __('Meeting Host *', 'jitsi-pro'),
                'id'                => $this->prefix . 'host',
                'description'       => __('This is host ID for the meeting', 'jitsi-pro'),
                'type'              => 'host',
                'std'               => get_current_user_id()
            ),
            array(
                'name'              => __('Recurring meeting?', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring',
                'description'       => __('Is this a recurring meeting?', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Start Date/Time *', 'jitsi-pro'),
                'id'                => $this->prefix . 'start_time',
                'description'       => __('Starting date and time of the meeting', 'jitsi-pro'),
                'type'              => 'date',
                'std'               => current_time('Y-m-d H:i'),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 0
                    )
                )
            ),
            array(
                'name'              => __('End Date/Time *', 'jitsi-pro'),
                'id'                => $this->prefix . 'end_time',
                'description'       => __('Ending date and time of the meeting', 'jitsi-pro'),
                'type'              => 'date_end',
                'std'               => '',
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 0
                    )
                )
            ),
            array(
                'name'              => __('Recurrence', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring_repeat',
                'description'       => __('How the meeting should repeat', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 'month',
                'options'           => array(
                    'day'           => __('Daily', 'jitsi-pro'),
                    'week'          => __('Weekly', 'jitsi-pro'),
                    'month'         => __('Monthly', 'jitsi-pro'),
                    'year'          => __('Yearly', 'jitsi-pro')
                ),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    )
                )
            ),
            array(
                'name'              => __('Recurrence on', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring_on_weekday',
                'description'       => __('Which day on the week?', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 'monday',
                'options'           => array(
                    'monday'        => __('Monday', 'jitsi-pro'),
                    'tuesday'       => __('Tuesday', 'jitsi-pro'),
                    'wednesday'     => __('Wednesday', 'jitsi-pro'),
                    'thursday'      => __('Thursday', 'jitsi-pro'),
                    'friday'        => __('Friday', 'jitsi-pro'),
                    'saturday'      => __('Saturday', 'jitsi-pro'),
                    'sunday'        => __('Sunday', 'jitsi-pro')
                ),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    ),
                    array(
                        'id'            => $this->prefix . 'recurring_repeat',
                        'value'         => 'week'
                    )
                )
            ),
            array(
                'name'              => __('Recurrence month', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring_on_yearly',
                'description'       => __('Which month of the year?', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 1,
                'options'           => array(
                    1               => __('January', 'jitsi-pro'),
                    2               => __('February', 'jitsi-pro'),
                    3               => __('March', 'jitsi-pro'),
                    4               => __('April', 'jitsi-pro'),
                    5               => __('May', 'jitsi-pro'),
                    6               => __('June', 'jitsi-pro'),
                    7               => __('July', 'jitsi-pro'),
                    8               => __('August', 'jitsi-pro'),
                    9               => __('September', 'jitsi-pro'),
                    10              => __('October', 'jitsi-pro'),
                    11              => __('November', 'jitsi-pro'),
                    12              => __('December', 'jitsi-pro'),
                ),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    ),
                    array(
                        'id'            => $this->prefix . 'recurring_repeat',
                        'value'         => 'year'
                    )
                )
            ),
            array(
                'name'              => __('Recurrence date', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring_on_yearly_date',
                'description'       => __('Which date of the month?', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 1,
                'options'           => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    ),
                    array(
                        'id'            => $this->prefix . 'recurring_repeat',
                        'value'         => 'year'
                    )
                )
            ),
            array(
                'name'              => __('Recurrence date', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurring_on_monthly',
                'description'       => __('Which date of the month?', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 1,
                'options'           => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    ),
                    array(
                        'id'            => $this->prefix . 'recurring_repeat',
                        'value'         => 'month'
                    )
                )
            ),
            array(
                'name'              => __('Recurrence time *', 'jitsi-pro'),
                'id'                => $this->prefix . 'recurrence_time',
                'description'       => __('Time when the meeting will start', 'jitsi-pro'),
                'type'              => 'time',
                'std'               => current_time('H:i'),
                'dependency'        => array(
                    array(
                        'id'            => $this->prefix . 'recurring',
                        'value'         => 1
                    )
                )
            ),
            array(
                'name'              => __('Duration (hr)', 'jitsi-pro'),
                'id'                => $this->prefix . 'duration',
                'description'       => __('Probable running time of the meeting', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => 1
            ),
            array(
                'name'              => __('Password', 'jitsi-pro'),
                'id'                => $this->prefix . 'password',
                'description'       => __('Password to join the meeting', 'jitsi-pro'),
                'type'              => 'password',
                'std'               => wp_generate_password()
            ),
            array(
                'name'              => __('Should Register', 'jitsi-pro'),
                'id'                => $this->prefix . 'should_register',
                'description'       => __('If user should be register to join the meeting', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Width', 'jitsi-pro'),
                'id'                => $this->prefix . 'width',
                'description'       => __('Width in pixel when not on fullscreen', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => get_option('jitsi_opt_width', 1080)
            ),
            array(
                'name'              => __('Height', 'jitsi-pro'),
                'id'                => $this->prefix . 'height',
                'description'       => __('Height in pixel when not on fullscreen', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => get_option('jitsi_opt_height', 720)
            ),
            array(
                'name'              => __('Welcome Page', 'jitsi-pro'),
                'id'                => $this->prefix . 'enablewelcomepage',
                'description'       => __('Enable/Disable welcome page', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 1
            ),
            array(
                'name'              => __('Start Audio Only', 'jitsi-pro'),
                'id'                => $this->prefix . 'startaudioonly',
                'description'       => __('Start conference on audio only', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Start Audio Muted', 'jitsi-pro'),
                'id'                => $this->prefix . 'startaudiomuted',
                'description'       => __('Participant after nth will be muted', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => 10
            ),
            array(
                'name'              => __('Yourself Muted', 'jitsi-pro'),
                'id'                => $this->prefix . 'startwithaudiomuted',
                'description'       => __('Start with yourself muted', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Start Silent', 'jitsi-pro'),
                'id'                => $this->prefix . 'startsilent',
                'description'       => __('Disable local audio output', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Video Resolution', 'jitsi-pro'),
                'id'                => $this->prefix . 'resolution',
                'description'       => __('Start with preferred resolution', 'jitsi-pro'),
                'type'              => 'select',
                'std'               => 720,
                'options'           => array(
                    480             => __('480p', 'jitsi-pro'),
                    720             => __('720p', 'jitsi-pro'),
                    1080            => __('1080p', 'jitsi-pro'),
                    1440            => __('1440p', 'jitsi-pro'),
                    2160            => __('2160p', 'jitsi-pro'),
                    4320            => __('4320p', 'jitsi-pro'),
                )
            ),
            array(
                'name'              => __('Max Full Resolution', 'jitsi-pro'),
                'id'                => $this->prefix . 'maxfullresolutionparticipant',
                'description'       => __('Number of participant with default resolution', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => 2
            ),
            array(
                'name'              => __('Start Video Muted', 'jitsi-pro'),
                'id'                => $this->prefix . 'startwithvideomuted',
                'description'       => __('Start call with video muted', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 1
            ),
            array(
                'name'              => __('Video Muted After', 'jitsi-pro'),
                'id'                => $this->prefix . 'startvideomuted',
                'description'       => __('Every participant after nth will start video muted', 'jitsi-pro'),
                'type'              => 'number',
                'std'               => 2
            ),
            array(
                'name'              => __('Start Screen Sharing', 'jitsi-pro'),
                'id'                => $this->prefix . 'startscreensharing',
                'description'       => __('Start call with screen sharing instead of camera video', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Enable Livestream', 'jitsi-pro'),
                'id'                => $this->prefix . 'livestreamingenabled',
                'description'       => __('Turn on livestreaming', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Enable Recording', 'jitsi-pro'),
                'id'                => $this->prefix . 'filerecordingsenabled',
                'description'       => __('Turn on to record the meeting', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Enable Transcription', 'jitsi-pro'),
                'id'                => $this->prefix . 'transcribingenabled',
                'description'       => __('Transcript the meeting', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            ),
            array(
                'name'              => __('Simulcast', 'jitsi-pro'),
                'id'                => $this->prefix . 'disablesimulcast',
                'description'       => __('Enable/Disable simulcast', 'jitsi-pro'),
                'type'              => 'switch',
                'std'               => 0
            )
        );
    }

    //Add post type
    public function jitsi_pro_post_type()
    {
        $labels = array(
            'name'               => __('Meeting', 'jitsi-pro'),
            'singular_name'      => __('Meeting', 'jitsi-pro'),
            'menu_name'          => __('Meeting', 'jitsi-pro'),
            'name_admin_bar'     => __('Meeting', 'jitsi-pro'),
            'add_new'            => __('Add New', 'jitsi-pro'),
            'add_new_item'       => __('Add New Meeting', 'jitsi-pro'),
            'new_item'           => __('New Meeting', 'jitsi-pro'),
            'edit_item'          => __('Edit Meeting', 'jitsi-pro'),
            'view_item'          => __('View Meeting', 'jitsi-pro'),
            'all_items'          => __('All Meeting', 'jitsi-pro'),
            'search_items'       => __('Search Meeting', 'jitsi-pro'),
            'parent_item_colon'  => __('Parent Meeting:', 'jitsi-pro'),
            'not_found'          => __('No meeting found.', 'jitsi-pro'),
            'not_found_in_trash' => __('No meeting found in Trash.', 'jitsi-pro')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Meeting for jitsi pro', 'jitsi-pro'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-embed-video',
            'exclude_from_search'=> true,
            'taxonomies'         => array( 'category', 'post_tag' ),
            'supports'           => array( 'title', 'editor' )
        );

        register_post_type('meeting', $args);
        flush_rewrite_rules();
    }

    // Add the custom columns to the meeting post type
    public function jitsi_pro_edit_meeting_columns($columns)
    {
        $columns['shortcode'] = __('Shortcode <span class="jitsi-shortcode-copied" id="jitsi-shortcode-copied">Copied to clipboard</span>', 'jitsi-pro');
        $columns['embed_in'] = __('Embed In', 'jitsi-pro');
        return $columns;
    }

    // Add the data to the custom columns for the meeting post type
    public function jitsi_pro_book_column($column, $post_id)
    {
        if ($column == 'shortcode') {
            printf('<div class="jitsi-shortcode-copy"><code>[jitsi-meet-wp-meeting id="%1$s"/]</code><input type="text" id="jitsi_sc_post_%1$s" value=\'[jitsi-meet-wp-meeting id="%1$s"/]\'/><button class="jitsi-copy-sc" data-for="jitsi_sc_post_%1$s"><svg data-for="jitsi_sc_post_%1$s" size="20" color="#0376DA" viewBox="0 0 24 24" class="sc-htoDjs fgURlR"><g><path fill-rule="evenodd" d="M4 4c0-1.1046.8954-2 2-2h8c1.1046 0 2 .8954 2 2H6v14c-1.1046 0-2-.8954-2-2V4zm6 4v12h8V8h-8zm0-2h8c1.1046 0 2 .8954 2 2v12c0 1.1046-.8954 2-2 2h-8c-1.1046 0-2-.8954-2-2V8c0-1.1046.8954-2 2-2z" clip-rule="evenodd"></path></g></svg></button></div>', $post_id);
        }

        if ($column == 'embed_in') {
            $results = get_transient('jitsi_pro_embed_on_' . $post_id);
            
            if (false === $results) {
                global $wpdb;
                $query = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_content LIKE '%[jitsi-meet-wp-meeting id=\"{$post_id}\"%' AND post_status = 'publish'";
                $results = $wpdb->get_results($query);
                set_transient('jitsi_pro_embed_on_' . $post_id, $results);
            }
            
            if (count($results)) {
                echo '<ul class="embed-on-list">';
                foreach ($results as $result) {
                    echo '<li>';
                    edit_post_link($result->post_title, '', '', $result->ID);
                    echo '</li>';
                }
                echo '</ul>';
            }
        }
    }

    public function jitsi_pro_meeting_template($single)
    {
        global $post;
        if ($post->post_type == 'meeting') {
            if (!locate_template( 'single-meeting.php' ) && file_exists(JITSI_ULTIMATE_FILE_PATH . 'inc/single-meeting.php')) {
                return JITSI_ULTIMATE_FILE_PATH . 'inc/single-meeting.php';
            }
        }
        return $single;
    }

    public function jitsi_pro_post_meta_box()
    {
        $screens = [ 'meeting' ];
        foreach ($screens as $screen) {
            add_meta_box(
                $this->prefix . 'meeting_settings',
                __('Meeting Settings', 'jitsi-pro'),
                [$this, 'jitsi_pro_post_meta_box_html'],
                $screen,
                'normal',
                'high',
                $this->metafields
            );
        }
    }

    public function jitsi_pro_post_meta_box_html($post, $metabox)
    {
        $saved = get_post_meta($post->ID, $metabox['id'], true);
        echo '<table>';
        foreach ($metabox['args'] as $key => $value) {
                $saved_val = isset($saved[$value['id']]) ? $saved[$value['id']] : $value['std']; ?>
                <?php 
                    if(isset($value['dependency'])){
                        $depends = [];
                        $dependValues = [];
                        $dependDisplay = 'table-row';
                        foreach($value['dependency'] as $val){
                            $depends[] = $val['id'];
                            $dependValue = isset($saved[$val['id']]) ? $saved[$val['id']] : 0;
                            $dependValues[] = $val['value'];
                            if($dependValue != $val['value']) {
                                $dependDisplay = 'none';
                            }
                        }
                        printf('<tr class="jitsi-pro-metafield" data-depend="%1$s" data-value="%2$s" style="display: %3$s">', esc_attr(json_encode($depends)), esc_attr(json_encode($dependValues)), esc_attr($dependDisplay));
                    } else {
                        echo '<tr class="jitsi-pro-metafield">';
                    }
                ?>
                <th>
                    <label for="<?php echo esc_attr($value['id']); ?>">
                        <?php echo $value['name']; ?>
                        <?php echo isset($value['description']) ? '<small class="description">' . $value['description'] . '</small>' : ''; ?>
                    </label>
                </th>
                <td><?php
                    switch ($value['type']) {
                        case 'number':
                            printf('<input type="number" name="%1$s" id="%1$s" value="%2$s"/>', $value['id'], intval($saved_val));
                            break;
                        case 'switch':
                            $class = $saved_val == 1 ? 'jitsi-field-switch active' : 'jitsi-field-switch';
                            printf('<label class="%3$s"><input class="jitsi-admin-field jitsi-post-switch" type="checkbox" name="%1$s" id="%1$s" value="1" %2$s><span></span></label>', $value['id'], checked(1, $saved_val, false), $class);
                            break;
                        case 'select':
                            $select_options = '';
                            if(array_keys($value['options']) !== range(0, count($value['options']) - 1)){
                                foreach ($value['options'] as $k=>$val) {
                                    $selected = $k ==  $saved_val ? 'selected' : '';
                                    $select_options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $k, $val, $selected);
                                }
                            } else {
                                foreach ($value['options'] as $val) {
                                    $selected = $val ==  $saved_val ? 'selected' : '';
                                    $select_options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $val, $val, $selected);
                                }
                            }                            
                            printf('<select name="%1$s" id="%1$s">%2$s</select>', $value['id'], $select_options);
                            break;
                        case 'date':
                            printf('<input type="text" class="jitsi_datepicker" value="%1$s" id="%2$s" name="%2$s"/><span>%3$s%4$s</span>', $saved_val, $value['id'], esc_html__('Time is on GMT'), get_option('gmt_offset'));
                            break;
                        case 'time':
                            printf('<input type="text" class="jitsi_timepicker" value="%1$s" id="%2$s" name="%2$s"/><span>%3$s%4$s</span>', $saved_val, $value['id'], esc_html__('Time is on GMT'), get_option('gmt_offset'));
                            break;
                        case 'date_end':
                            if($saved_val){
                                printf('<input type="hidden" value="%1$s" id="%2$s" name="%2$s"/><span>%1$s</span><span> (%3$s%4$s) </span>', $saved_val, $value['id'], esc_html__('Time is on GMT'), get_option('gmt_offset'));
                            } else {
                                printf('<span>%1$s</span>', __('Meeting not ended yet', 'jitsi-pro'));
                            }
                            break;
                        case 'host':
                            $current_user = get_user_by('id', $saved_val);
                            printf('<input type="hidden" name="%1$s" value="%2$s"/><span>%3$s (%4$s)</span>', $value['id'], $saved_val, $current_user->data->display_name, $current_user->data->user_email);
                            break;
                        case 'password':
                            printf('<input type="text" id="%1$s" name="%1$s" value="%2$s"/>', $value['id'], $saved_val);
                            break;
                        default:
                            printf('<input type="text" name="%1$s" id="%1$s" value="%2$s"/>', $value['id'], $saved_val);
                    } ?></td>
                </tr>
            <?php
        }
        echo '</table>';
    }

    public function jitsi_pro_post_meta_save($post_id)
    {
        if (!function_exists('get_current_screen')) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }
        $currentScreen = get_current_screen();
        if (!empty($currentScreen) && $currentScreen->id === "meeting" && !empty($_POST)) {
            $saved = get_post_meta($post_id, $this->prefix . 'meeting_settings', true) ? get_post_meta($post_id, $this->prefix . 'meeting_settings', true) : [];

            foreach ($this->metafields as $key => $value) {
                $new_valueid = $value['id'];
                $new_valuetype = $value['type'];
                $new_value = isset($_POST[$new_valueid]) ? $_POST[$new_valueid] : (isset($saved[$new_valueid]) ? $saved[$new_valueid] : $value['std']);
    
                switch ($new_valuetype) {
                    case 'number':
                        $new_value = intval($new_value);
                        break;
                    case 'switch':
                        $new_value = isset($_POST[$new_valueid]) ? 1: 0;
                        break;
                    default:
                        $new_value = sanitize_text_field($new_value);
                }
    
                $saved[$new_valueid] = $new_value;
            }
    
            update_post_meta($post_id, $this->prefix . 'meeting_settings', $saved);
        }

        $this->jitsi_pro_delete_transients('jitsi_pro_embed_on_');
    }

    //tramsient deletion
    public function jitsi_pro_delete_transients($prefix)
    {
        global $wpdb;
        $prefix = $wpdb->esc_like('_transient_'.$prefix);
        $sql = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
        $transients = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

        if (!$transients || is_wp_error($transients) || !is_array($transients)) {
            return false;
        }
        
        foreach ($transients as $transient) {
            delete_transient(str_replace('_transient_', '', $transient['option_name']));
        }
    }

    //Register to meeting
    public function jitsi_register_to_meeting(){
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'jitsi_meeting_register_nonce' ) ) {
            exit("No naughty business please");
        }

        $attendee_before = get_post_meta($_REQUEST['post_id'], 'registered_attendee', true) ? get_post_meta($_REQUEST['post_id'], 'registered_attendee', true) : [];
        if(in_array($_REQUEST['meeting_remail'], $attendee_before)){
            echo json_encode(array('type' => 'failed', 'statusText' => esc_html__('This email is already registered', 'jitsi-pro')));
            die();
        } else {
            $attendee_before[] = $_REQUEST['meeting_remail'];
            array_unique($attendee_before);
            update_post_meta($_REQUEST['post_id'], 'registered_attendee', $attendee_before);
            $meeting_metas = get_post_meta($_REQUEST['post_id'], 'jitsi_pro__meeting_settings', true);
            $password = $meeting_metas['jitsi_pro__password'];
            $meeting_time = $meeting_metas['jitsi_pro__start_time'];

            //Send Mail
            ob_start();
            ?>
            <div style="background-color:#f7f7f7;margin:0;padding:70px 0;width:100%"><div class="adM">
			</div><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tbody><tr>
					<td align="center" valign="top">
						<div id="m_-5184889933011112581template_header_image">
													</div>
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="m_-5184889933011112581template_container" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px">
							<tbody><tr>
								<td align="center" valign="top">
									
									<table border="0" cellpadding="0" cellspacing="0" width="100%" id="m_-5184889933011112581template_header" style="background-color:#16a085;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;border-radius:3px 3px 0 0">
										<tbody><tr>
											<td id="m_-5184889933011112581header_wrapper" style="padding:36px 48px;display:block">
												<h1 style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left;color:#ffffff;background-color:inherit"><?php esc_html_e('Thank you for your registration', 'jitsi-pro') ?></h1>
											</td>
										</tr>
									</tbody></table>
									
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="m_-5184889933011112581template_body">
										<tbody><tr>
											<td valign="top" id="m_-5184889933011112581body_content" style="background-color:#ffffff">
												
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tbody><tr>
														<td valign="top" style="padding:48px 48px 32px">
															<div id="m_-5184889933011112581body_content_inner" style="color:#636363;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left">

                                                            <p style="margin:0 0 16px"><?php printf('%1$s %2$s', esc_html__('Hi', 'jitsi-pro'), $_REQUEST['meeting_rname']) ?>,</p>
                                                            <p style="margin:0 0 16px"><?php printf('%2$s %1$s', get_the_title($_REQUEST['post_id']), esc_html__('Just to let you know — we\'ve received your request and confirm your registration for', 'jitsi-pro')); ?></p>
                                                            <p style="margin:0 0 16px"><?php esc_html_e('Here below is your login details', 'jitsi-pro'); ?></p>
                                                            <h2><?php esc_html_e('Meeting Logins', 'jitsi-pro'); ?></h2>
<div style="margin-bottom:40px"><table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
<thead><tr>
<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php esc_html_e('Meeting Link', 'jitsi-pro') ?></th>
<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php esc_html_e('Login', 'jitsi-pro') ?></th>
<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php esc_html_e('Password', 'jitsi-pro') ?></th>
<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php esc_html_e('Time', 'jitsi-pro') ?></th>
</tr></thead>
<tbody>
<tr>
<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><a href="<?php echo get_permalink($_REQUEST['post_id']) ?>" style="color:#16a085;font-weight:normal;text-decoration:underline" target="_blank" ><?php echo get_the_title($_REQUEST['post_id']); ?></a></td>
<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $_REQUEST['meeting_remail']; ?></td>
<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $password; ?></td>
<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word"><?php echo $meeting_time; ?></td>
</tr>
</tbody>
</table></div>


														</div>
														</td>
													</tr>
												</tbody></table>
												
											</td>
										</tr>
									</tbody></table>
									
								</td>
							</tr>
						</tbody></table>
					</td>
				</tr>
			</tbody></table><div class="yj6qo"></div><div class="adL">
		</div></div>
            <?php 
            $mailmessage = ob_get_clean();
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail( $_REQUEST['meeting_remail'], sprintf('%1$s %2$s', esc_html__('Your login detail for ', 'jitsi-pro'), get_the_title($_REQUEST['post_id'])), $mailmessage, $headers );
            
            echo json_encode(array('type' => 'success', 'statusText' => esc_html__('You are registered. Password sent to your email', 'jitsi-pro')));
            die();
        }
    } 
}

new Jitsi_Pro_Post();
