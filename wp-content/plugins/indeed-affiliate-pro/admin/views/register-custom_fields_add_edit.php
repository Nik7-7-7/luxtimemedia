<div class="uap-wrapper">
<form method="post" action="<?php echo esc_url($data['form_submit']);?>">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('User Custom Fields', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('Slug:', 'uap');?></label>
				<input type="text" name="name" value="<?php echo esc_attr($data['metas']['name']);?>" <?php echo esc_attr( $data['disabled']);?> />
				<?php if ( $data['disabled'] ):?>
						<input type="hidden" name="name" value="<?php echo esc_attr($data['metas']['name']);?>" />
				<?php endif;?>
			</div>
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('Field Type:', 'uap');?></label>
				<select id="field_type" <?php echo ($data['disabled']) ? 'disabled' : 'name="type"';?> onChange="uapRegisterFields(this.value);">
					<?php foreach ($data['field_types'] as $k=>$v): ?>
						<?php $selected = ($data['metas']['type']==$k) ? 'selected' : '';?>
						<option value="<?php echo esc_attr($k)?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
					<?php endforeach;?>
				</select>
				<?php if ( $data['disabled'] ):?>
						<input type="hidden" name="type" value="<?php echo esc_attr($data['metas']['type']);?>" />
				<?php endif;?>
			</div>

			<?php
				$display = 'none';
				if (($data['metas']['type']=='select' || $data['metas']['type']=='checkbox' || $data['metas']['type']=='radio'
					|| $data['metas']['type']=='multi_select') && ($data['metas']['name']!='tos')){
						$display = 'block';
				}
			?>
			<div class="uap-form-line uap-display-<?php echo esc_attr($display);?>" id="uap-register-field-values">
				<label class="uap-label uap-top-vertical-align"><?php esc_html_e('Values', 'uap');?></label>
					<div class="uap-register-the-values uap-display-inline">
					<?php
						if (isset($data['metas']['values']) && $data['metas']['values']){
							foreach ($data['metas']['values'] as $value){
							?>
								<div class="uap-custom-field-item-wrapp uap-fields-values-box">
									<input type="text" name="values[]" value="<?php echo uap_correct_text($value);?>" class="uap-fields-values-box-input"/>
									<i class="fa-uap fa-remove-uap uap-js-register-fields-add-edit-remove" ></i>
									<i class="fa-uap fa-arrows-uap"></i>
								</div>
							<?php
							}
						} else {
						?>
							<div class="uap-custom-field-item-wrapp uap-fields-values-box">
								<input type="text" name="values[]" value=""/>
								<i class="fa-uap uap-icon-remove-e uap-js-register-fields-add-edit-remove" ></i>
								<i class="fa-uap fa-arrows-uap"></i>
							</div>
						<?php
						}
						?>
					</div>
				<div class="uap-clear"></div>
				<div class="uap-fields-add-new-value" onclick="uapAddNewRegisterFieldValue();"><?php esc_html_e('Add New Value', 'uap');?></div>
			</div>

			<div id="uap-register-field-conditional-text" class=" <?php echo ($data['metas']['type']=='conditional_text') ? 'uap-display-block' : 'uap-display-none';?>">
				<div class="uap-form-line">
					<label class="uap-labels uap-top-vertical-align"><?php esc_html_e('Right Answer:', 'uap');?></label>
					<input type="text" value="<?php echo isset($data['metas']['conditional_text']) ? uap_correct_text($data['metas']['conditional_text']) : '';?>" name="conditional_text" />
				</div>
				<div class="uap-form-line">
					<label class="uap-labels uap-top-vertical-align"><?php esc_html_e('Error Message:', 'uap');?></label>
					<textarea name="error_message" class="uap-fields-error-mess"><?php echo isset($data['metas']['error_message']) ? uap_correct_text($data['metas']['error_message']) : ''; ?></textarea>
				</div>
			</div>

			<div class="uap-no-border <?php echo ($data['metas']['type']=='plain_text') ? 'uap-display-block' : 'uap-display-none';?>" id="uap-register-field-plain-text">
				<label class="uap-labels uap-top-vertical-align"><?php esc_html_e('Content:', 'uap');?> </label>
				<div class="uap-fields-content-box">
				<?php
					$settings = array(
									'media_buttons' => true,
									'textarea_name'=>'plain_text_value',
									'textarea_rows' => 5,
									'tinymce' => true,
									'quicktags' => true,
									'teeny' => true,
					);
					$plain_text = '';
					if(isset($data['metas']['plain_text_value'])){
						$plain_text = $data['metas']['plain_text_value'];
					}
					wp_editor(uap_correct_text($plain_text), 'plain_text_value', $settings);
				?>
				</div>
			</div>



			<div class="uap-form-line">
				<h2><?php esc_html_e('Labels', 'uap');?></h2>
				<label class="uap-label"><?php esc_html_e('Field Label:', 'uap');?> </label> <input type="text" name="label" value="<?php echo uap_correct_text($data['metas']['label']);?>"/>
			</div>
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('SubLabel:', 'uap');?></label>
				<input type="text" value="<?php echo isset($data['metas']['sublabel']) ? uap_correct_text($data['metas']['sublabel']) : '';?>" name="sublabel" class="uap-fields-extra-input" />
			</div>
			<div class="uap-form-line">
				<label class="uap-label"><?php esc_html_e('Style Class:', 'uap');?></label>
				<input type="text" value="<?php echo isset($data['metas']['class']) ? uap_correct_text($data['metas']['class']) : '';?>" name="class" class="uap-fields-extra-input" />
			</div>

			<?php if (!in_array($data['metas']['name'], $data['disabled_items'])):?>
				<div class="uap-special-line">
					<h2><?php esc_html_e("Conditional Logic", 'uap');?></h2>
					<div class="uap-form-line">
						<label class="uap-label"><?php esc_html_e('Show:', 'uap');?></label>
						<select name="conditional_logic_show">
							<option <?php echo (isset($data['metas']['conditional_logic_show']) && $data['metas']['conditional_logic_show']=='yes') ? 'selected' : '';?> value="yes"><?php esc_html_e("Yes", 'uap');?></option>
							<option <?php echo (isset($data['metas']['conditional_logic_show']) && $data['metas']['conditional_logic_show']=='no') ? 'selected' : '';?> value="no"><?php esc_html_e("No", 'uap');?></option>
						</select>
					</div>
					<div class="uap-form-line uap-text-align-left">
						<div class="uap-display-inline">
							<label class="uap-label"><?php esc_html_e('If Field:', 'uap');?></label>
							<select name="conditional_logic_corresp_field">
							<?php foreach ($data['register_fields'] as $k => $v):?>
								<?php $selected = ($data['metas']['conditional_logic_corresp_field']==$k) ? 'selected' : '';?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
							<?php endforeach;?>
							</select>
						</div>
						<div class="uap-condition-blockone">
							<select name="conditional_logic_cond_type" class="uap-fields-select">
								<option <?php echo (isset($data['metas']['conditional_logic_cond_type']) && $data['metas']['conditional_logic_cond_type']=='has') ? 'selected' : '';?> value="has"><?php esc_html_e("Is", 'uap');?></option>
								<option <?php echo (isset($data['metas']['conditional_logic_cond_type']) && $data['metas']['conditional_logic_cond_type']=='contain') ? 'selected' : '';?> value="contain"><?php esc_html_e("Contains", 'uap');?></option>
							</select>
						</div>
						<div class="uap-condition-blocktwo">
							<label class="uap-fields-label"> : </label>
							<input type="text" name="conditional_logic_corresp_field_value" value="<?php echo isset($data['metas']['conditional_logic_corresp_field_value']) ? uap_correct_text($data['metas']['conditional_logic_corresp_field_value']) : '';?>" class="uap-fields-input" />
						</div>
					</div>
				</div>
			<?php endif; ?>

			<input type="hidden" name="id" value="<?php echo esc_attr($data['id']);?>" />
			<div class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save_field" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>
</div>
