<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Ulp_Invoices')){
	 return;
}
class Ulp_Invoices{
	private $uid = 0;
	private $order_id = 0;
  private $metas = array();
	private $is_preview = FALSE;
	private $_course_id = 0;
	public function __construct($uid=0, $order_id=0, $metas=array() ){
		if (count($metas)>0){
			$this->is_preview = TRUE;
			foreach ( $metas as $key => $metaArr ){
					$this->metas[$metaArr['name']] = $metaArr['value'];
			}
			//$this->metas = $metas;

		} else {
			$this->metas = DbUlp::getOptionMetaGroup('invoices');
		}
		$this->uid = $uid;
		$this->order_id = $order_id;
	}
	public function output(){
		$data = $this->metas;
		$data['icon_print_id'] = 'iump-print-popup-content';
		$data['wrapp_id'] = 'ulp_invoice_' . $this->order_id;
		require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
		$DbUlpOrdersMeta = new DbUlpOrdersMeta();
		$data += $DbUlpOrdersMeta->getAllMetasAsArray($this->order_id);
		if ($this->uid && $this->order_id){
				$this->course_id = $DbUlpOrdersMeta->get($this->order_id, 'course_id');
		}

		$data['ulp_invoices_bill_to'] = ulp_replace_constants($data['ulp_invoices_bill_to'], $this->uid, $this->course_id, FALSE);
		$data['ulp_invoices_bill_to'] = stripslashes(htmlspecialchars_decode(indeed_format_str_like_wp($data['ulp_invoices_bill_to'])));
		$data['ulp_invoices_company_field'] = stripslashes(htmlspecialchars_decode(indeed_format_str_like_wp($data['ulp_invoices_company_field'])));
		$data['ulp_invoices_footer'] = stripslashes(htmlspecialchars_decode(indeed_format_str_like_wp($data['ulp_invoices_footer'])));
		$data['ulp_invoices_title'] = stripslashes($data['ulp_invoices_title']);
		$data['ulp_invoices_custom_css'] = stripslashes($data['ulp_invoices_custom_css']);
		///db data
		if ($this->is_preview){
				$data ['course_price'] = $DbUlpOrdersMeta->get($this->order_id, 'amount');
				if ( $data['course_price'] === null ){
						$data ['course_price'] = ulp_format_price( 24.50 );
						$data['total_amount'] = ulp_format_price( 24.50 );
						$data['course_label'] = esc_html__( 'Course Name', 'ulp' );
				} else {
  						$data ['course_price'] = ulp_format_price($data ['course_price']);
				}
		} else {
  			$currency = get_option('ulp_currency');
  			$data['course_label'] = \DbUlp::getPostTitleByPostId( $this->course_id);
  			$data['total_amount'] = ulp_format_price($data['amount']);
  			$data['course_price'] = ulp_format_price($data['amount']);
		}
		$data['order_details']['code'] = $DbUlpOrdersMeta->get($this->order_id, 'code');
		if (empty($data['order_details']['code'])){
				 $data['order_details']['code'] = $this->order_id;
		}
		$data['order_details']['create_date'] = get_the_date('',$this->order_id);
		$data['order_details']['create_date'] = ulp_print_date_like_wp($data['order_details']['create_date']);

		if ($this->is_preview && empty($data['order_details']['code'])){
			$data['order_details']['code'] = '001';
			$data['order_details']['create_date'] = ulp_print_date_like_wp(date("Y-m-d H:i:s"));
		}
      /// output
      $view = new ViewUlp();
  		$view->setTemplate(ULP_PATH . 'views/templates/invoice.php');
  		$view->setContentData($data);
      if (empty($this->is_preview)){
  				return $this->_wrapp_into_popup($view->getOutput());
      }
			return $view->getOutput();
	}
	private function _wrapp_into_popup($input=''){
		$data ['content'] = $input;
		$data ['title'] = esc_html__('Invoice', 'ulp');
    $view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/templates/popup.php');
		$view->setContentData($data);
		return $view->getOutput();
	}
}
