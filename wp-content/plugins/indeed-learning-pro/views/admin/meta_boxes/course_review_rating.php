<select name="rating">
		<option value="" selected>...</option>
		<?php for ($i=1; $i<6; $i++):?>
				<option value="<?php echo esc_attr($i);?>" <?php echo ($i==$data['rating']) ? 'selected' : '';?> ><?php echo esc_html($i) .  esc_html__(' stars', 'ulp');?></option>
		<?php endfor;?>
</select>
