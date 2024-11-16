<?php
require_once UAP_PATH . 'admin/font-awesome_codes.php';
$font_awesome = uap_return_font_awesome();
?>
<?php foreach ($font_awesome as $base_class => $code):?>
	<div class="uap-font-awesome-popup-item" data-class="<?php echo esc_attr($base_class);?>" data-code="<?php echo esc_attr($code);?>"><i class="fa-uap-preview fa-uap <?php echo esc_attr($base_class);?>"></i></div>
<?php endforeach;?>
