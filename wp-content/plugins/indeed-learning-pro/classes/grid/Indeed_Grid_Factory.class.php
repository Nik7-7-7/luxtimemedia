<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Indeed_Grid')){
   return;
}
class Indeed_Grid_Factory{
    private $_shortcode_attributes = [];
    private $_current_page = 1;
    private $_total_items = 0;
    private $_total_pages = 0;
    private $_items = null;
    private $_query_arg_name = 'ulp_grid_page';
    private $_div_parent_id = '';
    private $_settings = [];
    private $_factory_single_item = null;
    public function __construct(Indeed_Grid_Single_Item $factory_single_element=null, $shortcode_attributes=[],
      $query_arg_name='', $total_items=0
    ){
        /// params
        $this->_factory_single_item = $factory_single_element;
        $this->_shortcode_attributes = $shortcode_attributes;
        $this->_div_parent_id = 'ulp_grid_' . rand(1, 1000);
        $this->_query_arg_name = $query_arg_name;
        $this->_total_items = $total_items;
        ///
        $this->_current_page = isset($_GET[$this->_query_arg_name]) ? (int)$_GET[$this->_query_arg_name] : 1;
        $this->_total_pages = ceil($this->_total_items/$this->_shortcode_attributes['entries_per_page']);
    }
    public function set_items($input_data=null){
        $this->_items = $input_data;
    }
    public function get_output(){
        $css_files = $this->_css_files();
        $js_files = $this->_js_files();
        $css = $this->_css();
        $js = $this->_js();
        $pagination = $this->_pagination();
        $items_output = $this->_items_output();
        $output = $this->_wrapper($items_output);
        if ($pagination){
            switch ($this->_shortcode_attributes ['pagination_pos']) {
              case 'top':
                $output = $pagination . $output;
                break;
              case 'bottom':
                $output = $output . $pagination;
                break;
              default:
                $output = $pagination . $output . $pagination;
                break;
            }
        }
        return $css_files . $js_files . $css . $js . $output;
    }
    private function _items_output(){
        $output = [];
        $this->_factory_single_item->set_shortcode_attributes($this->_shortcode_attributes);
        foreach ($this->_items as $key => $object){
            $this->_factory_single_item->build($object);
            $output [] = $this->_factory_single_item->get_output();
        }
        $this->_factory_single_item->reset();
        return $output;
    }
    private function _wrapper($items_output=''){
        $num = rand(1, 10000);
        $data = array(
            'items_output' => $items_output,
            'color_class' => (empty($this->_shortcode_attributes ['color_scheme'])) ? 'style_0a9fd8' : 'style_' . $this->_shortcode_attributes ['color_scheme'],
            'theme' => $this->_shortcode_attributes ['theme'],
            'extra_class' => (empty($this->_shortcode_attributes['pagination_theme'])) ? '' : $this->_shortcode_attributes['pagination_theme'],
            'parent_class' => (empty($this->_shortcode_attributes ['slider_set'])) ? 'ulp-content-grid-list' : 'ulp-carousel-view',
            'div_parent_id' => $this->_div_parent_id,///'indeed_carousel_view_widget_' . $num,
            'ul_id' => 'ulp_grid_list_ul_' . $num,
            'items_per_slide' => empty($this->_shortcode_attributes ['slider_set']) ? $this->_total_items : $this->_shortcode_attributes ['items_per_slide'] ,
            'total_items' => $this->_total_items,
			      'columns' => $this->_shortcode_attributes ['columns'],
            'li_width' => 'calc(' . 100/$this->_shortcode_attributes['columns'] . '% - 1px)',
        );

        $template = ULP_PATH . 'views/grid/wrapper.php';
        $template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'wrapper.php' );

        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }
    private function _pagination(){
        /*
  			 * @param none
  			 * @return string
  			 */
        if (!empty($this->_shortcode_attributes['slider_set']) || $this->_shortcode_attributes['entries_per_page']>=$this->_total_items)
            return '';
  			$url = ULP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  			$str = '';
        include ULP_PATH . 'views/grid/pagination.php';
  			return $str;
    }
    private function _css_files(){
        $str = '';
        if (!empty($this->_shortcode_attributes ['slider_set']) && !defined('ULP_SLIDER_LOAD_CSS')){
          ///// SLIDER CSS
          wp_enqueue_style( 'ulp-owl-carousel', ULP_URL . 'assets/css/owl.carousel.css', [], 3.6, 'all' );
          wp_enqueue_style( 'ulp-grid-owl-theme', ULP_URL . 'assets/css/owl.theme.css', [], 3.6, 'all' );
          wp_enqueue_style( 'ulp-owl-transitions', ULP_URL . 'assets/css/owl.transitions.css', [], 3.6, 'all' );
          define('ULP_SLIDER_LOAD_CSS', TRUE);
        }
        if (!empty($this->_shortcode_attributes ['theme'])){

        }
        if (!defined('ULP_COLOR_CSS_FILE')){
          wp_enqueue_style( 'ulp-grid-layouts', ULP_URL . 'assets/css/layouts.css', [], 3.6, 'all' );
          define('ULP_COLOR_CSS_FILE', TRUE);
        }
        return $str;
    }
    private function _js_files(){
        $str = '';
        if (!empty($this->_shortcode_attributes ['slider_set']) && !defined('ULP_SLIDER_LOAD_JS')){
            wp_enqueue_script( 'ulp-owl', ULP_URL . 'assets/js/owl.carousel.js', ['jquery'], 3.6, false );
            define('ULP_SLIDER_LOAD_JS', TRUE);
        }
        return $str;
    }
    private function _js(){
        /*
  			 * @param
  			 * @return string
  			 */
  			$str = '';
  			if (!empty($this->_shortcode_attributes['slider_set'])){
            include ULP_PATH . 'views/grid/js_slider.php';
  			}
  			return $str;
    }
    private function _css(){
      /*
       * @param none
       * @return string
       */
      //add the themes and the rest of CSS here...
      $str = '';
      include ULP_PATH . 'views/grid/build_style.php';
      return $str;
    }
}
