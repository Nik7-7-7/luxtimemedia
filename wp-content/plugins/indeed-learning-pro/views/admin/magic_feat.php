<div class="ulp-top-message-new-extension">
	<?php echo esc_html__('Extend your ', 'ulp') . '<strong>' . esc_html__('Ultimate Learning Pro', 'ulp') . '</strong>'
	. esc_html__(' system with extra features and functionality. Check additional available ', 'ulp') . '<strong>' . esc_html__('Extensions', 'ulp')
	. '</strong> <a href="https://store.wpindeed.com" target="_blank">' . esc_html__('here', 'ulp') . '</a>';?></div>

<?php foreach ($data['magic_feat'] as $slug=>$array):?>

    <div class="ulp-magic-box-wrap <?php echo ( $array['disabled']) ? 'ulp-disabled-box' : '';?> ">

        <a href="<?php echo esc_url($array ['link']);?>">

          <div class="ulp-magic-feature <?php echo esc_attr($slug);?> <?php echo ( $array ['link'] == '#') ? 'ulp-magic-feat-not-available' : ''; ?> <?php echo (isset($array['extra_class'])) ? esc_attr($array['extra_class']) : '';?> ">

              <div class="ulp-magic-box-icon"><i class="<?php echo esc_attr('fa-ulp fa-' . $slug . '-ulp');?>"></i></div>

              <div class="ulp-magic-box-title"><?php echo esc_html($array['label']);?></div>

              <div class="ulp-magic-box-desc"><?php echo esc_html($array['description']);?></div>

          </div>

        </a>

    </div>

<?php endforeach;?>
<div class="ulp-magic-box-wrap ">
		<a href="https://store.wpindeed.com/" target="_blank">
			<div class="ulp-magic-feature new_extension ulp-new-extension-box">
				<div class="ulp-magic-box-icon"><i class="fa-ulp fa-new-extension-ulp"></i></div>
				<div class="ulp-magic-box-title"><?php esc_html_e('Add new Extensions', 'ulp');?></div>
				<div class="ulp-magic-box-desc"></div>
			</div>
		</a>
	</div>
