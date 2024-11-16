<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('IndeedPagination')):
class IndeedPagination{
	/*
	 * @var string
	 */
	private $base_url;
	/*
	 * @var string
	 */
	private $param_name = 'page';
	/*
	 * @var int
	 */
	private $current_page;
	/*
	 * @var int
	 */
	private $total_items;
	/*
	 * @var int
	 */
	private $items_per_page = 30;
	/*
	 * @var string
	 */
	private $link_class = 'ulp-pagination-item';
	/*
	 * @var string
	 */
	private $selected_link_class = 'ulp-pagination-item-selected';
	/*
	 * @var string
	 */
	private $class_item_break = 'ulp-pagination-item-break';
	/*
	 * @var string
	 */
	private $wrapper_class = 'ulp-pagination';
	/*
	 * @var boolean
	 */
	private $is_unset = FALSE;
	/*
	 * @param array
	 * @return none
	 */
	public function __construct($input=array()){
		if (!empty($input) && is_array($input)){
			$required = array('base_url', 'param_name', 'total_items', 'items_per_page', 'current_page');
			foreach ($required as $key){
				if (empty($input[$key])){
					$this->is_unset = TRUE;
				}
				$this->$key = $input[$key];
			}
		} else {
			$this->is_unset = TRUE;
		}
	}
	/*
	 * @param array
	 * @return none
	 */
	public function output(){
		if ($this->is_unset){
			return '';
		}
		$output = '';
		$total_pages = ceil($this->total_items/$this->items_per_page);
		if ($total_pages<2){
			 return '';
		}
		if ($total_pages<=5){
			//show all the links
			for ($i=1; $i<=$total_pages; $i++){
				$show_links[] = $i;
			}
		} else {
			// we want to show only first, last, and the first neighbors of current page
			$show_links = array(1, $total_pages, $this->current_page, $this->current_page+1, $this->current_page-1);
		}
		for ($i=1; $i<=$total_pages; $i++){
			if (in_array($i, $show_links)){
				$href = add_query_arg($this->param_name, $i, $this->base_url);
				$class = ($this->current_page==$i) ? $this->selected_link_class : $this->link_class;
				$output .= "<a href='$href' class='$class'>" . $i . '</a>';
				$dots_on = TRUE;
			} else {
				if (!empty($dots_on)){
					$output .= '<span class="' . $this->class_item_break . '">...</span>';
					$dots_on = FALSE;
				}
			}
		}
		/// Back link
		if ($this->current_page>1){
			$prev_page = $this->current_page - 1;
			$href = add_query_arg($this->param_name, $prev_page, $this->base_url);
			$output = "<a href='" . $href . "' class='" . $this->link_class . "'> < </a>" . $output;
		}
		///Forward link
		if ($this->current_page<$total_pages){
			$next_page = $this->current_page + 1;
			$href = add_query_arg($this->param_name, $next_page, $this->base_url);
			$output = $output . "<a href='" . $href . "' class='" . $this->link_class . "'> > </a>";
		}
		//Wrappers
		$output = "<div class='" . $this->wrapper_class . "'>" . $output . "</div><div class='ulp-clear'></div>";
		return $output;
	}
}//end of class
endif;
