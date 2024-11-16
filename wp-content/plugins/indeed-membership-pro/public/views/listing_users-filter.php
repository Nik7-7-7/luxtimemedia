<?php if ($this->filter_form_fields):?>
<div class="iump-listing-users-filter">
<div class="iump-filter-title"><?php esc_html_e("Search for specific Members", 'ihc');?></div>
<form action="<?php echo esc_url($base_url);?>" method="get" class="ihc-js-listing-user-filter-form" data-base_url="<?php echo esc_url($base_url);?>" >
	<?php foreach ($this->filter_form_fields as $field):?>

		<?php switch ($field['type']):
			case 'number':
				global $ihc_jquery_ui_min_css;
				if (empty($ihc_jquery_ui_min_css)){
					$ihc_jquery_ui_min_css = TRUE;
					?><link rel="stylesheet" type="text/css" href="<?php echo esc_url(IHC_URL . 'admin/assets/css/jquery-ui.min.css');?>"/><?php
				}
				$hidden_min = '';
				$hidden_max = '';
				if (!isset($field['values']['min'])){
					$field['values']['min'] = '';
				}
				if (!isset($field['values']['max'])){
					$field['values']['max'] = '';
				}

				if (isset($_GET[$field['name']]) && isset($_GET[$field['name']][0]) && $_GET[$field['name']][0]!=''){
					$current['min'] = sanitize_text_field($_GET[$field['name']][0]);
					$hidden_min = sanitize_text_field($_GET[$field['name']][0]);
				} else {
					$current['min'] = $field['values']['min'];
				}
				if (isset($_GET[$field['name']]) && isset($_GET[$field['name']][1]) && $_GET[$field['name']][1]!=''){
					$current['max'] = sanitize_text_field($_GET[$field['name']][1]);
					$hidden_max = sanitize_text_field($_GET[$field['name']][1]);
				} else {
					$current['max'] = $field['values']['max'];
				}

				if ($field['values']['min']!=$field['values']['max']):
				?>
				<div class="iump-filter-row">
					<label><?php echo esc_html($field['label']);?></label>
					<div class="ihc-filter-value" id="<?php echo esc_attr('iump_slider_' .  $field['name']  . '_view_values');?>">
						<?php echo esc_html($current['min']) . ' - ' . esc_html($current['max']);?>
					</div>
					<div id="<?php echo esc_attr( 'iump_slider_' . $field['name'] );?>"></div>
				</div>
				<span class="ihc-js-listin-users-filter-number-data"
						data-selector="<?php echo esc_ump_content('#iump_slider_') . esc_attr($field['name']);?>"
						data-min="<?php echo esc_attr($field['values']['min']);?>"
						data-max="<?php echo esc_attr($field['values']['max']);?>"
						data-current_min="<?php echo esc_attr($current['min']);?>"
						data-current_max="<?php echo esc_attr($current['max']);?>"
						data-min_selector="<?php echo esc_ump_content('#') . esc_attr( $field['name'] . 'min' );?>"
						data-max_selector="<?php echo esc_ump_content('#') . esc_attr( $field['name'] . 'max' );?>"
						data-view_selector="<?php echo esc_ump_content('#iump_slider_') . esc_attr( $field['name'] . '_view_values' );?>"
				></span>
				  <?php endif;?>
				  <input type="hidden" name="<?php echo esc_attr($field['name']);?>[0]" value="<?php echo esc_attr($hidden_min);?>" id="<?php echo esc_attr($field['name']) . 'min';?>" />
				  <input type="hidden" name="<?php echo esc_attr($field['name']);?>[1]" value="<?php echo esc_attr($hidden_max);?>" id="<?php echo esc_attr($field['name']) . 'max';?>" />
				<?php
				break;
			case 'select':

					?>
						<div class="iump-filter-row iump-filter-country">
							<label><?php echo esc_html( $field['label'] );?></label>
							<select name="<?php echo esc_attr( $field['name'] );?>" class="iump-form-select" >
								<option value="" selected><?php esc_html_e('All', 'ihc');?></option>
							<?php
								$get_value = (isset($_GET[$field['name']])) ? $_GET[$field['name']] : '';
								if ($field['values']):
									foreach ($field['values'] as $k){
										$selected = ($get_value==$k) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr( $k );?>" <?php echo esc_attr($selected);?> ><?php echo esc_html( ihc_correct_text( $k ) );?></option>
										<?php
									}
								endif;
							?>
							</select>
						</div>
					<?php

				break;
			case 'ihc_country':

					?>
						<div class="iump-filter-row iump-filter-country">
							<label><?php echo esc_html( $field['label'] );?></label>
							<select name="<?php echo esc_attr( $field['name'] );?>" class="iump-form-select" >
								<option value="" selected><?php esc_html_e('All', 'ihc');?></option>
							<?php
								$get_value = (isset($_GET[$field['name']])) ? $_GET[$field['name']] : '';
								if ($field['values']):
									foreach ($field['values'] as $k){
										$selected = ($get_value==$k) ? 'selected' : '';
										if ( !isset( $countries[$k] ) ){
												continue;
										}
										?>
										<option value="<?php echo esc_attr( $k );?>" <?php echo esc_ump_content( $selected );?> ><?php echo esc_html($countries[$k]);?></option>
										<?php
									}
								endif;
							?>
							</select>
						</div>
					<?php

				break;
			case 'multi_select':

				?>
				<div class="iump-filter-row iump-filter-multi">
					<label><?php echo esc_html($field['label']);?></label>
					<select name="<?php echo esc_attr($field['name']);?>[]" class="iump-form-select" multiple >
						<option value="" selected><?php esc_html_e('All', 'ihc');?></option>
					<?php
						$get_value = (isset($_GET[$field['name']])) ? $_GET[$field['name']] : array();
						if ($field['values']):
							foreach ($field['values'] as $k){
								$selected = (in_array($k, $get_value)) ? 'selected' : '';
								?><option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html(ihc_correct_text($k));?></option><?php
							}
						endif;
					?>
					</select>
				</div>
				<?php

				break;
			case 'radio':
				if ($field['values']):
					?>
					<div class="iump-filter-row iump-filter-radio">
						<label><?php echo esc_html($field['label']);?></label>
						<div class="iump-form-radiobox-wrapper">
							<div class="iump-form-radiobox">
								<input type="radio" name="<?php echo esc_attr($field['name']);?>" value="" checked />
								<?php esc_html_e('All', 'ihc');?>
							</div>
						<?php
						$get_value = (isset($_GET[$field['name']])) ? $_GET[$field['name']] : array();
						foreach ($field['values'] as $v){
							$checked = ($get_value==$v) ? 'checked' : '';
							?><div class="iump-form-radiobox">
							<input type="radio" name="<?php echo esc_attr($field['name']);?>" value="<?php echo esc_attr(ihc_correct_text($v));?>" <?php echo esc_attr($checked);?> />
							<?php echo esc_html(ihc_correct_text($v));?>
							</div><?php
						}?>
						</div>
					</div>
					<?php
				endif;
				break;
			case 'checkbox':
				if ($field['values']):
				$get_value = (isset($_GET[$field['name']])) ? $_GET[$field['name']] : array();
				?>
				<div class="iump-filter-row iump-filter-check">
					<label><?php echo esc_html($field['label']);?></label>
					<div class="iump-form-checkbox-wrapper">
						<div class="iump-form-checkbox">
							<?php $checked = (empty($get_value)) ? 'checked' : '';?>
							<input type="checkbox" name="<?php echo esc_attr($field['name']);?>[]" value="" onClick="ihcDeselectAll('<?php echo esc_attr($field['name']);?>', this);" <?php echo esc_attr($checked);?> />
							<?php esc_html_e('All', 'ihc');?>
						</div>
				<?php
				foreach ($field['values'] as $v){
					if (is_array($get_value)){
						$checked = (in_array($v, $get_value)) ? 'checked' : '';
					} else {
						$checked = ($v==$get_value) ? 'checked' : '';
					}
					?>
						<div class="iump-form-checkbox">
							<input type="checkbox" name="<?php echo esc_attr($field['name']);?>[]" value="<?php echo esc_attr(ihc_correct_text($v));?>" <?php echo esc_attr($checked);?> />
							<?php echo esc_html(ihc_correct_text($v));?>
						</div>
					<?php
				}
				?>
					</div>
				</div>
				<?php
				endif;
				break;
			case 'date':
				wp_enqueue_script('jquery-ui-datepicker');
				$min_value = '';
				$max_value = '';
				global $ihc_jquery_ui_min_css;
				if (empty($ihc_jquery_ui_min_css)){
					$ihc_jquery_ui_min_css = TRUE;
					?><link rel="stylesheet" type="text/css" href="<?php echo esc_url(IHC_URL . 'admin/assets/css/jquery-ui.min.css');?>"/><?php
				}
				$start_id = 'iump_start_' . $field['name'];
				$end_id = 'iump_end_' . $field['name'];
				if (isset($_GET[$field['name']]) && isset($_GET[$field['name']][0])){
					$field['values']['min'] = sanitize_text_field($_GET[$field['name']][0]);
					$min_value = sanitize_text_field($_GET[$field['name']][0]);
					//$min_value = filter_var( $min_value, FILTER_SANITIZE_STRING );
					$min_value = preg_replace( "([^0-9-])", '', $min_value );
				}
				if (isset($_GET[$field['name']]) && isset($_GET[$field['name']][1])){
					$field['values']['max'] = sanitize_text_field($_GET[$field['name']][1]);
					$max_value = sanitize_text_field($_GET[$field['name']][1]);
					//$max_value = filter_var( $max_value, FILTER_SANITIZE_STRING );
					$max_value = preg_replace( "([^0-9-])", '', $max_value );
				}
				?>
				<span class="ihc-js-listing-users-filter-data" data-start_selector="#<?php echo esc_attr($start_id);?>" data-end_selector="#<?php echo esc_attr($end_id);?>" ></span>

				<div class="iump-filter-row iump-filter-date">
					<label><?php echo esc_attr($field['label']);?></label>
					<input type="text" value="<?php echo esc_attr($min_value);?>" name="<?php echo esc_attr($field['name']) . '[0]';?>" id="<?php echo esc_attr($start_id);?>" class="iump-form-datepicker ihc-min"  />
					<span class="ihc-line" >-</span>
					<input type="text" value="<?php echo esc_attr($max_value);?>" name="<?php echo esc_attr($field['name']) . '[1]';?>" id="<?php echo esc_attr($end_id);?>" class="iump-form-datepicker ihc-min" />
				</div>
				<?php
				break;

		endswitch;?>
	<?php endforeach;?>
	<input type="hidden" name="iump_filter" value="0" />
	<div class="iump-filter-submit">
		<input type="submit" name="filter" value="<?php echo esc_attr__('Search', 'ihc');?>"/>
		<input type="submit" name="reset" value="<?php esc_attr_e('Reset', 'ihc');?>" id="iump_reset_bttn" />
	</div>
</form>
</div>
<?php endif;?>
