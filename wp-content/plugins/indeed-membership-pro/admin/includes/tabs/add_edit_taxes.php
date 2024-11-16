<?php
$id = empty($_GET['edit']) ? 0 : sanitize_text_field($_GET['edit']);
$data['metas'] = Ihc_Db::get_tax($id);
require_once IHC_PATH . 'public/static-data.php';
$data['countries'] = ihc_get_countries();
wp_enqueue_script( 'ihc-select2', ['jquery'] );
?>
	<form method="post" action="<?php echo admin_url('admin.php?page=ihc_manage&tab=taxes');?>">
		<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />
		<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />
		<input type="hidden" name="status" value="<?php echo isset( $data['metas']['status'] ) ? $data['metas']['status'] : '';?>" />
		<div class="ihc-stuffbox">
			<h3><?php esc_html_e('Add/Edit Tax', 'ihc');?></h3>
			<div class="inside">

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e('Label:', 'ihc');?></label>
					<input type="text" name="label" value="<?php echo isset( $data['metas']['label'] ) ? $data['metas']['label'] : '';?>" />
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e('Description:', 'ihc');?></label>
					<textarea name="description"><?php echo isset( $data['metas']['description'] ) ? $data['metas']['description'] : '';?></textarea>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e('Country:', 'ihc');?></label>
					<select name="country_code" id="country_field">
						<?php
						if ( !isset( $data['metas']['country_code'] ) ){
								$data['metas']['country_code'] = '';
						}
						?>
						<?php foreach ($data['countries'] as $k=>$v):?>
							<option value="<?php echo esc_attr($k);?>" <?php if ($data['metas']['country_code']==$k){
								 echo esc_attr('selected');
							}
							?> ><?php echo esc_html($v);?></option>
						<?php endforeach;?>
					</select>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e('State:', 'ihc');?></label>
					<input type="text" name="state_code" value="<?php echo isset( $data['metas']['state_code'] ) ? $data['metas']['state_code'] : '';?>" />
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e('Tax Value:', 'ihc');?></label>
					<input type="number" name="amount_value" value="<?php echo isset( $data['metas']['amount_value'] ) ? $data['metas']['amount_value'] : '';?>" min="0" step="0.01" /> %
				</div>

				<div>
					<input type="submit" value="<?php if ($id){
						esc_html_e('Update', 'ihc');
					}else{
						esc_html_e('Add New', 'ihc');
					}
					?>" name="ihc_save" class="button button-primary button-large">
				</div>
			</div>
		</div>
	</form>
