<?php
if ($this->_total_pages<=5){
  //show all the links
  for ($i=1; $i<=$this->_total_pages; $i++){
    $show_links[] = $i;
  }
} else {
  // we want to show only first, last, and the first neighbors of current page
  $show_links = array(1, $this->_total_pages, $this->_current_page, $this->_current_page+1, $this->_current_page-1);
}
for ($i=1; $i<=$this->_total_pages; $i++){
  if (in_array($i, $show_links)){
    $href = (defined('IS_PREVIEW')) ? '#' : add_query_arg($this->_query_arg_name, $i, $url);
    $selected = ($this->_current_page==$i) ? '-selected' : '';
    $str .= "<a href='$href' class='ulp-grid-list-pagination-item" . $selected . "'>" . $i . '</a>';
    $dots_on = TRUE;
  } else {
    if (!empty($dots_on)){
      $str .= '<span class="ulp-grid-list-pagination-item-break">...</span>';
      $dots_on = FALSE;
    }
  }
}
/// Back link
if ($this->_current_page>1){
  $prev_page = $this->_current_page - 1;
  $href = (defined('IS_PREVIEW')) ? '#' : add_query_arg($this->_query_arg_name, $prev_page, $url);
  $str = "<a href='" . $href . "' class='ulp-grid-list-pagination-item ulp-pagination-prev'><</a>" . $str;
}
///Forward link
if ($this->_current_page<$this->_total_pages){
  $next_page = $this->_current_page + 1;
  $href = (defined('IS_PREVIEW')) ? '#' : add_query_arg($this->_query_arg_name, $next_page, $url);
  $str = $str . "<a href='" . $href . "' class='ulp-grid-list-pagination-item ulp-pagination-next'>></a>";
}
//Wrappers
$str = "<div class='ulp-grid-list-pagination'>" . $str . "</div><div class='ulp-clear'></div>";
