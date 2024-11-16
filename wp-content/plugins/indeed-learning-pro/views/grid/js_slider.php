<?php
$total_pages = $this->_total_items / $this->_shortcode_attributes['items_per_slide'];
if ($total_pages>1){
  $navigation = (empty($this->_shortcode_attributes['nav_button'])) ? 'false' : 'true';
  $bullets = (empty($this->_shortcode_attributes['bullets'])) ? 'false' : 'true';
  if (empty($this->_shortcode_attributes['autoplay'])){
    $autoplay = 'false';
    $autoplayTimeout = 5000;
  } else {
    $autoplay = 'true';
    $autoplayTimeout = $this->_shortcode_attributes['speed'];
  }
  $autoheight = (empty($this->_shortcode_attributes['autoheight'])) ? 'false' : 'true';
  $stop_hover = (empty($this->_shortcode_attributes['stop_hover'])) ? 'false' : 'true';
  $loop = (empty($this->_shortcode_attributes['loop'])) ? 'false' : 'true';
  $responsive = (empty($this->_shortcode_attributes['responsive'])) ? 'false' : 'true';
  $lazy_load = (empty($this->_shortcode_attributes['lazy_load'])) ? 'false' : 'true';
  $animation_in = (($this->_shortcode_attributes['animation_in'])=='none') ? 'false' : "'{$this->_shortcode_attributes['animation_in']}'";
  $animation_out = (($this->_shortcode_attributes['animation_out'])=='none') ? 'false' : "'{$this->_shortcode_attributes['animation_out']}'";
  $slide_pagination_speed = $this->_shortcode_attributes['pagination_speed'];
  $str .= "<span class='ulp-js-slider-option-data'
              data-target='#" . $this->_div_parent_id . "'
              data-autoHeight='$autoheight'
              data-animateOut='$animation_out'
              data-animateIn='$animation_in'
              data-lazyLoad='$lazy_load'
              data-loop='$loop'
              data-autoplay='$autoplay'
              data-autoplayTimeout='$autoplayTimeout'
              data-autoplayHoverPause='$stop_hover'
              data-autoplaySpeed='$slide_pagination_speed'
              data-nav='$navigation'
              data-navSpeed='$slide_pagination_speed'
              data-dots='$bullets'
              data-dotsSpeed='$slide_pagination_speed'
              data-responsiveClass='$responsive'
           ></span>
";
}
