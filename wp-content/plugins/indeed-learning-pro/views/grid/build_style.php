<?php
$custom_css ='';

///// SLIDER COLORS
if (!empty($this->_shortcode_attributes['color_scheme']) && !empty($this->_shortcode_attributes['slider_set'])){
  $custom_css .= '
        .style_'.$this->_shortcode_attributes['color_scheme'].' .owl-ulp-theme .owl-ulp-dots .owl-ulp-dot.active span, .style_'.$this->_shortcode_attributes['color_scheme'].'  .owl-ulp-theme .owl-ulp-dots .owl-ulp-dot:hover span { background: #'.$this->_shortcode_attributes['color_scheme'].' !important; }
        .style_'.$this->_shortcode_attributes['color_scheme'].' .pag-theme1 .ulp-wrapp-list-grid .owl-ulp-carousel .owl-ulp-controls .owl-ulp-nav [class*="owl-ulp-"]:hover{ background-color: #'.$this->_shortcode_attributes['color_scheme'].'; }
        .style_'.$this->_shortcode_attributes['color_scheme'].' .pag-theme2 .ulp-wrapp-list-grid .owl-ulp-carousel .owl-ulp-controls .owl-ulp-nav [class*="owl-ulp-"]:hover{ color: #'.$this->_shortcode_attributes['color_scheme'].'; }
        .style_'.$this->_shortcode_attributes['color_scheme'].' .pag-theme3 .ulp-wrapp-list-grid .owl-ulp-carousel .owl-ulp-controls .owl-ulp-nav [class*="owl-ulp-"]:hover{ background-color: #'.$this->_shortcode_attributes['color_scheme'].';}
      ';
}
if (!empty($this->_shortcode_attributes['color_scheme'])){
 $custom_css .= '.ulp-grid-list-courses .ulp-course-price { color:#'.$this->_shortcode_attributes['color_scheme'].'; }';
}
////// ALIGN CENTER
if (!empty($this->_shortcode_attributes['align_center'])) {
  $custom_css .= '#'.$this->_div_parent_id.' ul{text-align: center;}';
  $custom_css .= '#'.$this->_div_parent_id.' ul li{float: none;}';
}
///// CUSTOM CSS
if (!empty($this->_settings['ulp_listing_users_custom_css'])){
  $custom_css .= stripslashes($this->_settings['ulp_listing_users_custom_css']);
}
//// RESPONSIVE
if (!empty($this->_settings['ulp_responsive_small'])){
  $width = 100 / $this->_settings['ulp_responsive_small'];
  $custom_css .= '
      @media only screen and (max-width: 479px){
        #' . $this->_div_parent_id . ' ul li{
          width: calc(' . $width . '% - 1px) !important;
        }
      }
  ';
}
if (!empty($this->_settings['ulp_responsive_medium'])){
  $width = 100 / $this->_settings['ulp_responsive_medium'];
  $custom_css .= '
      @media only screen and (min-width: 480px) and (max-width: 767px){
        #' . $this->_div_parent_id . ' ul li{
          width: calc(' . $width . '% - 1px) !important;
        }
      }
  ';
}
if (!empty($this->_settings['ulp_responsive_large'])){
  $width = 100 / $this->_settings['ulp_responsive_large'];
  $custom_css .= '
      @media only screen and (min-width: 768px) and (max-width: 959px){
        #' . $this->_div_parent_id . ' ul li{
          width: calc(' . $width . '% - 1px) !important;
        }
      }
  ';
}

if($custom_css !== ''){
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', $custom_css );
}
