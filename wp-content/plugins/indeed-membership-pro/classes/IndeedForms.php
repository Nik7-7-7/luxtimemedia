<?php
namespace Indeed\Ihc;

class IndeedForms
{
    /**
     * @param string
     * @param array
     * @return string
     */
    public static function generateFieldByType( $type='', $attr=[] )
    {
        if ( method_exists( self::class, $type ) ){
            return self::{$type}( $attr );
        } else {
            return null;
        }
    }

    /**
     * @param array
     * @return array
     */
    private static function initiateFullAttr( $attr=[] )
    {
        $fullParamList = [ 'name', 'id', 'value', 'class', 'other_attr', 'disabled', 'label',
                           'placeholder', 'multiple_values', 'user_id', 'sublabel', 'other_args' ];
        foreach ( $fullParamList as $k){
            if (!isset($attr[$k])){
                $attr[$k] = '';
            }
        }
        return $attr;
    }

    /**
     * @param array
     * @return string
     */
    public static function text( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
				$output = '<input type="text" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="' . ihc_correct_text($attr['value'], false, true ) . '" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
				if (!empty($attr['sublabel'])){
					$output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
				}
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function number( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        foreach ( ['max', 'min'] as $k){
            if (!isset($attr[$k])){
                $attr[$k] = '';
            }
        }
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        $output = '<input type="number" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="'.$attr['value'].'"  '.$attr['other_args'].' '.$attr['disabled'].' min="' . $attr['min'] . '" max="' . $attr['max'] . '" />';
        if (!empty($attr['sublabel'])){
            $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function textarea( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        $output = '<textarea name="'.$attr['name'].'" '. $id_field .' class="iump-form-textarea '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' >' . ihc_correct_text($attr['value'], false, true ) . '</textarea>';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function password( $attr=[] )
    {
        global $wp_version;
        $attr = self::initiateFullAttr( $attr );
        $output = '';

        $ruleOne = (int)get_option('ihc_register_pass_min_length');
        $ruleTwo = (int)get_option('ihc_register_pass_options');

        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        $output .= '<input type="password" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="'.$attr['value'].'" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' data-rules="' . $ruleOne . ',' . $ruleTwo . '"/>';
        $output .= '<span class="ihc-hide-pw hide-if-no-js" data-toggle="0" aria-label="Show password">
            <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
          </span>';
        $output .= '<div class="ihc-strength-wrapper">';
        $output .= '<ul class="ihc-strength"><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li></ul>';
        $output .= '<div class="ihc-strength-label"></div>';
        $output .= '</div>';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function hidden( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        return '<input type="hidden" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="'.$attr['value'].'" '.$attr['other_args'].' />';
    }

    /**
     * @param array
     * @return string
     */
    public static function checkbox( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = "";
				if ($attr['multiple_values']){
          $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
          $id = 'ihc_checkbox_parent_' . rand(1,1000);
          $output .= '<div class="iump-form-checkbox-wrapper" id="' . $id . '">';
          foreach ($attr['multiple_values'] as $v){
            if (is_array($attr['value'])){
              $checked = (in_array($v, $attr['value'])) ? 'checked' : '';
            } else {
              $checked = ($v==$attr['value']) ? 'checked' : '';
            }
            $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
            $output .= '<div class="iump-form-checkbox">';
            $output .= '<input type="checkbox" name="'.$attr['name'].'[]" '. $id_field .' class="'.$attr['class'].'" value="' . ihc_correct_text($v, false, true ) . '" '.$checked.' '.$attr['other_args'].' '.$attr['disabled'].'  />';
            $output .= ihc_correct_text($v);
            $output .= '</div>';
          }
          $output .= '</div>';
        }
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }


    /**
     * @param array
     * @return string
     */
    public static function single_checkbox( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = "";
        $checked = empty($attr['value']) ? '' : 'checked';
        $output .= '<div class="ihc-tos-wrap" id="' . $attr['id'] . '">'
                . '<input type="checkbox" value="1" name="' . $attr['name'] . '" class="' . $attr['class'] . '" '.$checked.' />'
                . $attr['label'];
        if (!empty($attr['sublabel'])){
            $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function radio( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = '';
        if ( !isset( $attr['multiple_values'] ) || !$attr['multiple_values'] ){
            return null;
        }
        $id = 'ihc_radio_parent_' . rand(1,1000);
        $output .= '<div class="iump-form-radiobox-wrapper" id="' . $id . '">';
        foreach ($attr['multiple_values'] as $v){
            $checked = ($v==$attr['value']) ? 'checked' : '';
            $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
            $output .= '<div class="iump-form-radiobox">';
            $output .= '<input type="radio" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="' . ihc_correct_text( $v, false, true ) . '" '.$checked.' '.$attr['other_args'].' '.$attr['disabled'].'  />';
            $output .= ihc_correct_text($v);
            $output .= '</div>';
        }
        $output .= '</div>';
        if (!empty($attr['sublabel'])){
            $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function select( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = '';
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        if ($attr['multiple_values']){
            $output .= '<select name="'.$attr['name'].'" '. $id_field .' class="iump-form-select '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' >';
            if ($attr['multiple_values']){
              foreach ($attr['multiple_values'] as $k=>$v){
                $selected = ($k==$attr['value']) ? 'selected' : '';
                $output .= '<option value="'.$k.'" '.$selected.'>' . ihc_correct_text( $v, false, true ) . '</option>';
              }
            }
            $output .= '</select>';
        }
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function multi_select( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = '';
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        if ($attr['multiple_values']){
          $output .= '<select name="'.$attr['name'].'[]" '. $id_field .' class="iump-form-multiselect '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' multiple>';
          foreach ($attr['multiple_values'] as $k=>$v){
            if (is_array($attr['value'])){
              $selected = (in_array($v, $attr['value'])) ? 'selected' : '';
            } else {
              $selected = ($v==$attr['value']) ? 'selected' : '';
            }
            $output .= '<option value="'.$k.'" '.$selected.'>' . ihc_correct_text( $v, false, true ) . '</option>';
          }
          $output .= '</select>';
        }
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function submit( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        $output = '<input type="submit" value="' . ihc_correct_text( $attr['value'], false, true ) . '" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function date( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        wp_enqueue_script('jquery-ui-datepicker');
        if (empty($attr['class'])){
          $attr['class'] = 'ihc-date-field';
        }
        $output = '';

        global $ihc_jquery_ui_min_css;
        if (empty($ihc_jquery_ui_min_css)){
          $ihc_jquery_ui_min_css = TRUE;
          $output .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'admin/assets/css/jquery-ui.min.css"/>' ;
        }

        if (empty($attr['callback'])){
          $attr['callback'] = '';
        }

        $output .= '<span class="ihc-js-datepicker-data" data-selector=".iump-form-datepicker.'.$attr['class'].'" data-callback="' . $attr['callback'] . '"></span>';

        $output .= '<input type="text" value="'.$attr['value'].'" name="'.$attr['name'].'" id="'.$attr['id'].'" class="iump-form-datepicker '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].'   placeholder="'.$attr['placeholder'].'" />';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function file( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        wp_enqueue_script( 'ihc-jquery_form_module' );
        wp_enqueue_script( 'ihc-jquery_upload_file' );
        $output = '';
        $upload_settings = ihc_return_meta_arr('extra_settings');
        $max_size = $upload_settings['ihc_upload_max_size'] * 1000000;
        $rand = rand(1,10000);
        //$ajaxURL = IHC_URL . 'public/ajax-upload.php?ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
        $ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_public_upload_file&ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
        $output .= '<div id="ihc_fileuploader_wrapp_' . $rand . '" class="ihc-wrapp-file-upload  ihc-wrapp-file-field">';
        $output .= '<div class="ihc-file-upload ihc-file-upload-button">' . esc_html__("Upload", 'ihc') . '</div>
        <span class="ihc-js-upload-file-public-data"
            data-rand="' . $rand . '"
            data-url="' . $ajaxURL . '"
            data-max_size="' . $max_size . '"
            data-allowed_types="' . $upload_settings['ihc_upload_extensions'] . '"
            data-name="' . $attr['name'] . '"
            data-remove_label="' . esc_html__( 'Remove', 'ihc' ) . '"
            data-alert_text="' . esc_html__("To add a new file please remove the previous one!", 'ihc') . '"
        ></span>';
        if ($attr['value']){
          $attachment_type = ihc_get_attachment_details($attr['value'], 'extension');
          $url = wp_get_attachment_url($attr['value']);
          switch ($attachment_type){
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
              //print the picture
              $output .= '<img src="' . $url . '" class="ihc-member-photo" /><div class="ihc-clear"></div>';
              break;
            default:
              //default file type
              $output .= '<div class="ihc-icon-file-type"></div>';
              break;
          }
          $attachment_name = ihc_get_attachment_details($attr['value']);
          $output .= '<div class="ihc-file-name-uploaded"><a href="' . $url . '" target="_blank">' . $attachment_name . '</a></div>';
          $output .= '<div onClick=\'ihcDeleteFileViaAjax(' . $attr['value'] . ', '.$attr['user_id'].', "#ihc_fileuploader_wrapp_' . $rand . '", "' . $attr['name'] . '", "#ihc_upload_hidden_' . $rand . '");\' class="ihc-delete-attachment-bttn">Remove</div>';
        }
        $output .= '<input type="hidden" value="'.$attr['value'].'" name="' . $attr['name'] . '" id="ihc_upload_hidden_'.$rand.'" />';
        $output .= "</div>";
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function upload_image( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $data = $attr;
        $data['rand'] = rand(1, 10000);
        $data['imageClass'] = 'ihc-member-photo';
        if ( empty( $data['user_id'] ) ){
            $data['user_id'] = -1;
        }
        $data['imageUrl'] = '';
        if ( !empty($data['value']) ){
            if (strpos($data['value'], "http")===0){
                $data['imageUrl'] = $data['value'];
            } else {
                $tempData = \Ihc_Db::getMediaBaseImage($data['value']);
                if (!empty($tempData)){
                  $data['imageUrl'] = $tempData;
                }
            }
        }
        $viewObject = new \Indeed\Ihc\IndeedView();

        $filename = 'upload_image.php';
        $templateFile = IHC_PATH.'public/views/upload_image.php';
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        return $viewObject->setTemplate($templateFile)->setContentData( $data )->getOutput();
    }

    /**
     * @param array
     * @return string
     */
    public static function plain_text( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = ihc_correct_text( $attr['value'] );
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function ihc_country( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        wp_enqueue_style( 'ihc_select2_style' );
        wp_enqueue_script( 'ihc-select2' );

        $output = '';
        if (empty($attr['id'])){
          $attr['id'] = $attr['name'] . '_field';
        }
        $countries = ihc_get_countries();
        $js = '';
        //$js .= 'ihcUpdateStateField( true );';// deprecated since version 11.8
        if (isset($attr['form_type']) && $attr['form_type']=='edit'){
          $js = '';
        }


        if ( empty( $attr['value'] ) ){
            $attr['value'] = ihcGetDefaultCountry();
        }
        $output .= '<select name="' . $attr['name'] . '" id="' . $attr['id'] . '" onChange="' . $js . '" >'; /// onChange="ihc_update_tax_field();
        foreach ($countries as $k=>$v):
          $selected = ($attr['value']==$k) ? 'selected' : '';
          $output .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
        endforeach;
        $output .= '</select>';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        $output .= '<ul id="ihc_countries_list_ul" class="ihc-display-none">';

        $output .= '</ul>';
        if ( $attr['form_type'] !== 'modal' ){
          $output .= '
          <span class="ihc-js-countries-list-data"
                data-selector="#' . $attr['id'] . '"
                data-placeholder="' . esc_html__( "Select Your Country", 'ihc' ) . '"
          ></span>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function ihc_state( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $defaultCountry = ihcGetDefaultCountry();

        // since version 12.5
        if ( isset( $attr['user_id'] ) && $attr['user_id'] !== '' && $attr['user_id'] !== 0 ){ // since 12.6 added: && $attr['user_id'] !== 0
            $defaultCountry = get_user_meta($attr['user_id'], 'ihc_country', true );
        }
        // end of since version 12.5

        $onBlur = '';
        if ( !isset( $attr['id'] ) && $attr['id'] === '' ){
            $attr['id'] = 'ihc_state_field_' . rand( 1, 10000 );
        }

        if ( $defaultCountry !== false && $defaultCountry !== '' ){
            $output = ihc_get_state_field_str( $defaultCountry, $attr );
        } else {
            $output = '<input type="text" onBlur="' . $onBlur . '" name="' . $attr['name'] . '" id="' . $attr['id'] . '" class="' . $attr['class'] . '" value="' . ihc_correct_text($attr['value']) . '" placeholder="' . $attr['placeholder'] . '" ' . $attr['other_args'] . ' ' . $attr['disabled'] . ' />';
        }

        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function conditional_text( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $id_field = (isset($attr['id']) && $attr['id'] != "" ) ? 'id="'.$attr['id'].'"' : '';
        $output = '<input type="text" name="'.$attr['name'].'" '. $id_field .' class="'.$attr['class'].'" value="' . ihc_correct_text($attr['value'], false, true ) . '" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function unique_value_text( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        if ( empty( $attr['id'] ) ){
            $attr['id'] = $attr['name'] . '_' . 'unique';
        }
        //onBlur="ihcCheckUniqueValueField(\'' . $attr['name'] . '\');"
        $output = '<input type="text" data-search-unique="true" name="'.$attr['name'].'" id="'.$attr['id'].'" class="'.$attr['class'].'" value="' . ihc_correct_text( $attr['value'], false, true ) . '" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
        if (!empty($attr['sublabel'])){
            $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function ihc_invitation_code_field( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $output = '<input type="text" name="ihc_invitation_code_field" id="ihc_invitation_code_field" class="'.$attr['class'].'" value="' . ihc_correct_text($attr['value']) . '" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
        if (!empty($attr['sublabel'])){
          $output .= '<label class="iump-form-sublabel">' . ihc_correct_text($attr['sublabel']) . '</label>';
        }
        return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function ihc_dynamic_price( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
          if ( !ihc_is_magic_feat_active( 'level_dynamic_price' ) || empty( $attr['lid'] ) ){
              return null;
          }
          $output = '';
          $lid = $attr['lid'];
          $temp_settings = ihc_return_meta_arr( 'level_dynamic_price' );//getting metas
          if ( empty( $temp_settings['ihc_level_dynamic_price_levels_on'][$lid] ) ){
              return null;
          }
          $levelData = \Indeed\Ihc\Db\Memberships::getOne( $lid );
          $level_price = isset($levelData['price']) ? $levelData['price'] : 0;
          $min = isset($temp_settings['ihc_level_dynamic_price_levels_min'][$lid]) && $temp_settings['ihc_level_dynamic_price_levels_min'][$lid]!='' ? $temp_settings['ihc_level_dynamic_price_levels_min'][$lid] : 0;
          $max = isset($temp_settings['ihc_level_dynamic_price_levels_max'][$lid]) && $temp_settings['ihc_level_dynamic_price_levels_max'][$lid]!='' ? $temp_settings['ihc_level_dynamic_price_levels_max'][$lid] : $level_price;
          $step = isset($temp_settings['ihc_level_dynamic_price_step']) ? $temp_settings['ihc_level_dynamic_price_step'] : 0.01;
          $output .= "<input type='number' min='$min' max='$max' class='{$attr['class']}' step='$step' value='$level_price' name='ihc_dynamic_price' id='ihc_dynamic_price' />";
          return $output;
    }

    /**
     * @param array
     * @return string
     */
    public static function tos( $attr=[] )
    {
        $attr = self::initiateFullAttr( $attr );
        $tos_msg = stripslashes(get_option('ihc_register_terms_c'));//getting tos message
        $tos_page_id = get_option('ihc_general_tos_page');
        $tos_link = get_permalink($tos_page_id);
        /*
        if (!$tos_msg || !$tos_page_id){
            return '';
        }
        */
        if ( !$tos_msg ){
            $tos_msg = esc_html__( 'Accept our Terms&Conditions', 'ihc' );
        }
        $view = new \Indeed\Ihc\IndeedView();
        $data = array(
            'class' 						=> ( empty( $attr['class'] ) ) ? '' : $attr['class'],
            'id'								=> 'ihc_tos_field_parent_' . rand(1,1000),
            'tos_msg' 					=> $tos_msg,
            'tos_link'					=> $tos_link,
            'tos_page_id'				=> $tos_page_id,
        );

        $filename = 'register-tos.php';
        $templateFile = IHC_PATH.'public/views/register-tos.php';
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        return $view->setTemplate( $templateFile )
                    ->setContentData( $data, true )
                    ->getOutput();
    }

    /**
     * @param array
     * @return string
     */
    public static function social_media( $attr=[] )
    {
        return ihc_print_social_media_icons( 'register' );
    }

    /**
     * @param array
     * @return string
     */
    public static function capcha($attr=[])
    {
        $attr = self::initiateFullAttr( $attr );
        $type = get_option( 'ihc_recaptcha_version' );
        if ( $type !== false && $type == 'v3' ){
            $key = get_option('ihc_recaptcha_public_v3');
        } else {
            $key = get_option('ihc_recaptcha_public');
        }

        if (empty($key)){
            return '';
        }
        $key = trim( $key );
        $view = new \Indeed\Ihc\IndeedView();
        $data = array(
            'class' 		=> (empty($attr['class'])) ? '' : $attr['class'],
            'key'				=> $key,
            'langCode'	=> indeed_get_current_language_code(),
            'type'			=> $type,
        );

        $filename = 'register-captcha.php';
        $templateFile = IHC_PATH.'public/views/register-captcha.php';
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        return $view->setTemplate($templateFile)
                    ->setContentData($data, true)
                    ->getOutput();
    }

}
