<?php
$font_awesome = DbUlp::get_font_awesome_codes();
?>
<?php foreach ($font_awesome as $base_class => $code):?>
	<div class="ulp-font-awesome-popup-item" data-class="<?php echo esc_attr($base_class);?>" data-code="<?php echo esc_attr($code);?>"><i class="fa-ulp-preview fa-ulp <?php echo esc_attr($base_class);?>"></i></div>
<?php endforeach;?>
