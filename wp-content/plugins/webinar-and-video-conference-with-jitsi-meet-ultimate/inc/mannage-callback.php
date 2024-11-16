<?php
if (!defined('ABSPATH')) {
    exit;
}
class Mannage_Callback
{
    public function checkboxsanitize($input)
    {
        return (isset($input) ? true : false);
    }

    public function jitsi_settings_callback($input)
    {
        return $input;
    }

    public function jitsi_general($args)
    {
        $name   = $args['label_for'];
        $default = isset($args['default']) ? $args['default'] : '';
        $value = get_option($name, $default) ? get_option($name, $default) : $default;
        $dataDepend = isset($args['depend']) ? " data-depend='" . json_encode($args['depend']) . "'" : "";
        printf('<input class="jitsi-admin-field" type="text" name="%1$s" id="%1$s" value="%2$s"%3$s/>', esc_attr($name), $value, $dataDepend);
    }

    public function jitsi_general_disable($args)
    {
        $name   = $args['label_for'];
        $default = isset($args['default']) ? $args['default'] : '';
        $value = $default;
        $dataDepend = isset($args['depend']) ? " data-depend='" . json_encode($args['depend']) . "'" : "";
        printf('<input class="jitsi-admin-field" disabled type="text" name="%1$s" id="%1$s" value="%2$s"%3$s/>', esc_attr($name), $value, $dataDepend);
    }

    public function jitsi_textrea($args)
    {
        $name   = $args['label_for'];
        $value = get_option($name, '');
        $dataDepend = isset($args['depend']) ? " data-depend='" . json_encode($args['depend']) . "'" : "";
        printf('<textarea class="jitsi-admin-field" name="%1$s" id="%1$s" rows="4"%3$s>%2$s</textarea>', esc_attr($name), $value, $dataDepend);
    }

    public function jitsi_number($args)
    {
        $name   = $args['label_for'];
        $default = intval($args['default']);
        $value = get_option($name, $default);
        printf('<input class="jitsi-admin-field" type="number" name="%1$s" id="%1$s" value="%2$s"/>', esc_attr($name), intval($value));
    }

    public function jitsi_select($args)
    {
        $name   = $args['label_for'];
        $default = $args['default'];
        $optionsarr = $args['options'];
        $value = get_option($name, $default);
        $options = '';
        foreach($optionsarr as $key=>$val){
            $selected = $key ==  $value ? 'selected' : ''; 
            $options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $key, $val, $selected);
        }

        printf('<select class="jitsi-admin-field" name="%1$s" id="%1$s" value="%2$s">%3$s</select>', esc_attr($name), $value, $options);
    }

    public function jitsi_switch($args)
    {
        $name = $args['label_for'];
        $default = $args['default'];
        $value = get_option($name, $default);
        $class = $value ? 'jitsi-field-switch active' : 'jitsi-field-switch';
        printf('<label class="%2$s"><input class="jitsi-admin-field" type="checkbox" name="%1$s" id="%1$s" value="1" '.checked(1, $value, false).'/><span></span></label>', esc_attr($name), esc_attr($class));
    }

    public function jitsi_multiswitch($args)
    {
        $name   = $args['label_for'];
        $default = $args['default'];
        $optionsarr = $args['options'];
        $value = get_option($name, $default);
        $options = '';
        foreach($optionsarr as $key=>$val){
            $selected = $key ==  $value ? 'checked' : ''; 
            $options .= sprintf('<label><input class="jitsi-admin-field" type="radio" name="%4$s" value="%1$s" %3$s/><span>%2$s</span></label>', $key, $val, $selected, esc_attr($name));
        }

        printf('<div class="jitsi-admin-field jitsi-admin-field-multiswitch">%1$s</div>', $options);
    }

    public function jitsi_single_img($args)
    {
        $name = $args['label_for'];
        $default = $args['default'];
        $value = get_option($name, $default);
        
        ?>
        <div class="jitsi-field-single-image">
            <div class="jitsi-uploader-preview">
        <?php if($value) { printf('<div id="%1$s-prev-wrap" class="jitsi-uploader-preview-img"><img  id="%1$s-prev" src="%2$s" alt="%3$s"/></div>', esc_attr($name), esc_url($value), esc_attr__('Preview Image Upload', 'jitsi-pro')); 
        } ?>
                <div data-for="<?php esc_attr_e($name); ?>" class="jitsi-uploader-preview-placehold jitsi-uploader-button">
                    <div data-for="<?php esc_attr_e($name); ?>">
                        <svg data-for="<?php esc_attr_e($name); ?>" width="47" height="30" viewBox="0 0 47 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M46.25 20.625C46.25 15.712 42.4707 11.6816 37.6608 11.2825C36.0069 4.7964 30.1261 0 23.125 0C16.1239 0 10.2431 4.7964 8.58925 11.2825C3.77926 11.6816 0 15.7119 0 20.625C0 25.8027 4.19733 30 9.375 30H19.5267V19.5267H15L23.2766 11.25L31.5533 19.5267H27.0267V30H36.875C42.0527 30 46.25 25.8027 46.25 20.625Z" fill="#0376DA"></path></svg>
                        <h5 data-for="<?php esc_attr_e($name); ?>"><?php esc_html_e('Upload an image', 'jitsi-pro'); ?></h5>
                        <p data-for="<?php esc_attr_e($name); ?>"><?php echo $args['size']; ?></p>
                    </div>
                </div>
                <div class="jisti-uploader-btn-group">
                    <input class="jitsi-admin-field" type="text" id="<?php esc_attr_e($name); ?>" name="<?php esc_attr_e($name); ?>" value="<?php echo $value ? $value : $default ?>">
                    <button data-for="<?php esc_attr_e($name); ?>" class="button jitsi-uploader-reset" type="button"><svg data-for="<?php esc_attr_e($name); ?>" size="20" color="#5E6D7A" viewBox="0 0 24 24" class="sc-htoDjs jivEt"><g><path fill-rule="evenodd" d="M8 2c-1.1046 0-2 .8954-2 2v2H3c-.5523 0-1 .4477-1 1s.4477 1 1 1h1.125l.7578 12.1248C4.9487 21.1788 5.8228 22 6.8789 22h10.2422c1.0561 0 1.9302-.8212 1.9961-1.8752L19.875 8H21c.5523 0 1-.4477 1-1s-.4477-1-1-1h-3V4c0-1.1046-.8954-2-2-2H8zm0 2v2h8V4H8zM6.1289 8h11.7422l-.75 12H6.8789l-.75-12zM9 11c0-.5523.4477-1 1-1s1 .4477 1 1v7c0 .5523-.4477 1-1 1s-1-.4477-1-1v-7zm5-1c-.5523 0-1 .4477-1 1v7c0 .5523.4477 1 1 1s1-.4477 1-1v-7c0-.5523-.4477-1-1-1z" clip-rule="evenodd"></path></g></svg></button>
                    <button data-for="<?php esc_attr_e($name); ?>" class="button jitsi-uploader-button" type="button"><svg data-for="<?php esc_attr_e($name); ?>" size="20" color="#5E6D7A" viewBox="0 0 24 24" class="sc-htoDjs jivEt"><g><path fill-rule="evenodd" d="M18.9706 3.4142l1.4142 1.4142c.7811.781.7811 2.0474 0 2.8285l-9.8995 9.8994-6.364 2.1214 2.1214-6.364 9.8995-9.8995c.781-.781 2.0474-.781 2.8284 0zm-1.4142 4.2426l1.4142-1.4142-1.4142-1.4142-1.4142 1.4142 1.4142 1.4143zm-1.4142 1.4143L14.728 7.6569 7.9908 14.394l-.7072 2.1213 2.1214-.7071 6.7372-6.7372z" clip-rule="evenodd"></path></g></svg></button>
                </div>
            </div>
        </div>
        <?php
    }

    public function jitsi_other_admin()
    {
        printf('<p class="other-admin-setting">%1$s <a href="%2$s" target="_blank">%3$s</a></p>', esc_html__('Some settings like Comany Logo, Background etc are currently not available via api. You can set your company logo, background ect from the', 'jitsi-pro'), esc_url('https://jaas.8x8.vc/#/branding'), esc_html__('jaas console', 'jitsi-pro'));
    }
}
