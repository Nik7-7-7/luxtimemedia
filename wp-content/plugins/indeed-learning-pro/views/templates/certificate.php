<?php wp_enqueue_script( 'ulp_printThis' );?>
<span class="ulp-js-init-print-this" data-load_css="<?php echo ULP_URL . 'assets/css/public.css';?>"></span>

<?php if ($data ['image']):
$custom_css = '';
$custom_css .= ".ulp-certificate-wrapp{
		background-size: cover;
  	background-blend-mode: overlay;
    background-image: url('". $data ['image']."');
}";

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

 endif; ?>

<div class="ulp-invoice-bttn-wrapp">
	<div class="ulp-popup-print-bttn" onClick="ulpInitPrinthis( '<?php echo esc_attr('#' . $data['wrapp_id']);?>' );"><?php esc_html_e('Print Certificate', 'ulp');?></div>
</div>

<div class="ulp-certificate-wrapp" id="<?php echo esc_attr($data['wrapp_id']);?>" >
    <!--div class="ulp-certificate-title"><?php echo esc_ulp_content($data ['title']);?></div-->
    <div class="ulp-certificate-content"><?php echo esc_ulp_content($data ['content']);?></div>
</div>
