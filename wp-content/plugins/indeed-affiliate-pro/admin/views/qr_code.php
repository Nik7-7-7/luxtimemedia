<div class="uap-wrapper"><form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Affiliate QR Codes', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/qr-codes/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<h2><?php esc_html_e('Activate/Hold Affiliate QR Codes', 'uap');?></h2>
					<p><?php esc_html_e('Affiliates may download and share their QR codes anywhere out of the website.', 'uap');?></p>
					<label class="woo_account_page_enable uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_qr_code_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_qr_code_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_qr_code_enable" value="<?php echo esc_attr($data['metas']['uap_qr_code_enable']);?>" id="uap_qr_code_enable" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-10">
					<h2><?php esc_html_e('Additional Settings', 'uap');?></h2>
					<br/>
					<h4><?php esc_html_e(' QRCode Image Size', 'uap');?></h4>
					<p><?php esc_html_e('Decides the image size for the QR code. Bigger is much easier to scan, but has an increased load time.', 'uap');?></p>
					<input type="number" value="<?php echo esc_attr($data['metas']['uap_qr_code_size']);?>" name="uap_qr_code_size" min="1" max="10" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-10">
					<h4><?php esc_html_e('ECC Data Level', 'uap');?></h4>
					<p><?php esc_html_e('Error Code Correction is their ability to sustain "damage" and continue to function even when a part of the QR code image is obscured. Level L or Level M represent the best compromise between density and ruggedness for general marketing use. ', 'uap');?></p>
					<select name="uap_qr_code_ecc_level">
						<?php foreach (array('l' => 'L', 'm' => 'M', 'q' => 'Q', 'h' => 'H') as $k=>$v): ?>
							<?php $selected = ($k==$data['metas']['uap_qr_code_ecc_level']) ? 'selected' : '';?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
						<?php endforeach;?>
					</select>
				</div>
			</div>


			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
</div>
