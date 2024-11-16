<?php
wp_enqueue_style( 'ihc_jquery-ui.min.css', IHC_URL . 'admin/assets/css/jquery-ui.min.css');
wp_enqueue_script( 'ihc-drip-content', IHC_URL . 'admin/assets/js/drip-content.js', ['jquery'], 10.1 );
global $post;
$meta_arr = ihc_post_metas($post->ID);

if ($meta_arr['ihc_mb_type']=='show' && !empty($meta_arr['ihc_mb_who'])){
	$show_options = 'ihc-display-block';
	$show_not_available = 'ihc-display-none';
} else {
	$show_options = 'ihc-display-none';
	$show_not_available = 'ihc-display-block';
}
?>

<div id="ihc_drip_content_meta_box" class="<?php echo esc_attr($show_options);?>">

	<div class="ihc_drip_content_meta_box-wrapper">
		<label class="iump_label_shiwtch ihc-switch-button-margin">
			<?php $checked = ($meta_arr['ihc_drip_content'] == 1) ? 'checked' : '';?>
			<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_drip_content_h');" <?php echo esc_attr($checked);?> />
			<div class="switch ihc-display-inline"></div>
		</label>
		<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_drip_content']);?>" name="ihc_drip_content" id="ihc_drip_content_h" />
	</div>
	<div>
	 <p><?php esc_html_e("Set to release content at regular intervals by create a schedule of your content", 'ihc');?></p>
	</div>
	<div class="ihc-drip-content-special-box">
		<b><?php esc_html_e('Available for: ', 'ihc');?> </b>
<?php
		$posible_values = array('all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users','ihc'), 'unreg'=>esc_html__('Unregistered Users','ihc') );
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		if ($levels){
			foreach ($levels as $id=>$level){
				$posible_values[$id] = $level['name'];
			}
		}
		if (strpos($meta_arr['ihc_mb_who'], ',')!==FALSE){
			$values = explode(',', $meta_arr['ihc_mb_who']);
		} else {
			$values[] = $meta_arr['ihc_mb_who'];
		}
		$print_levels = array();
		?>
		<div id="ihc_drip_content_list_targets">
		<?php
		if (count($values)>0){
			foreach ($values as $v){
				if (!empty($posible_values[$v])){
				?>
					<span id="ihc_drip_target-<?php echo esc_attr($v);?>" ><?php echo esc_html($posible_values[$v]);?></span>
				<?php
				}
			}
		}
		?>
		</div>
	</div>

	<?php if ( function_exists( 'register_block_type' ) ) : // Gutenberg ?>
		<div>
				<h3 class="ihc-meta-drip-subtitle"><?php esc_html_e("Release Time", 'ihc');?></h3>
				<div>

						<div  class="ihc-inside-bootstrap-slide" id="ihc_slide_2" >

									<div>
											<div class="title-select"><?php esc_html_e('Type of release time', 'ihc');?></div>
											<select name="ihc_drip_start_type" class="js-ump-select-drip-content-start-time">
													<option value="1" <?php echo ( $meta_arr['ihc_drip_start_type'] == 1 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'Instantanly Subscription', 'ihc');?></option>
													<option value="2" <?php echo ( $meta_arr['ihc_drip_start_type'] == 2 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'After Subscription', 'ihc');?></option>
													<option value="3" <?php echo ( $meta_arr['ihc_drip_start_type'] == 3 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'On Specific Date', 'ihc');?></option>
											</select>
									</div>

								<div class="js-ump-select-drip-content-start-time-after-subscription ihc-inside-bootstrap-slide-div-2 <?php echo ( $meta_arr['ihc_drip_start_type'] != 2) ? 'ihc-display-none': ''; ?>"  >
										<div class="title-select"><?php esc_html_e('After Subscription:', 'ihc');?></div>
										<div>
												<input type="number" min="0" value="<?php echo esc_attr($meta_arr['ihc_drip_start_numeric_value']);?>" name="ihc_drip_start_numeric_value" class="ihc_drip_start_numeric_value" />
												<select name="ihc_drip_start_numeric_type"><?php
														foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
														?>
																<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['ihc_drip_start_numeric_type']==$k) ? 'selected' : ''; ?> ><?php echo esc_html( $v );?></option>
														<?php
														}
												?></select>
										</div>
								</div>

								<div class="js-ump-select-drip-content-start-time-on-specific-date ihc-inside-bootstrap-slide-div-3 <?php echo ( $meta_arr['ihc_drip_start_type'] != 3) ? 'ihc-display-none': ''; ?>"  >
										<div class="ihc-inside-bootstrap-slide-div-3">
												<div class="title-select"><?php esc_html_e('On Specific Date:', 'ihc');?></div>
												<input type="text" value="<?php echo esc_attr($meta_arr['ihc_drip_start_certain_date']);?>" name="ihc_drip_start_certain_date" id="ihc_drip_start_certain_date"/>
												<div><?php esc_html_e('Pick the desired date when the Page will be available', 'ihc');?></div>
										</div>
								</div>
						</div>

				</div>
				<div class="ihc-clear"></div>
		</div>

		<div class="ihc-drip-content-special-box">
			<h3 class="ihc-meta-drip-subtitle" ><?php esc_html_e("Expiration Time", 'ihc');?></h3>
			<div>

				<div class="ihc-inside-bootstrap-slide" id="ihc_slide_3" >

					<div >
							<div class="title-select"><?php esc_html_e('Type of expiration time', 'ihc');?></div>
							<select name="ihc_drip_end_type" class="js-ump-select-drip-content-end-time" >
									<option value="1" <?php echo ( $meta_arr['ihc_drip_end_type'] == 1 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'Never', 'ihc');?></option>
									<option value="2" <?php echo ( $meta_arr['ihc_drip_end_type'] == 2 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'After certain Period', 'ihc');?></option>
									<option value="3" <?php echo ( $meta_arr['ihc_drip_end_type'] == 3 ) ? 'selected' : ''; ?> ><?php esc_html_e( 'On Specific Date', 'ihc');?></option>
							</select>
					</div>


					<div class="js-ump-select-drip-content-end-time-after-subscription <?php echo ( $meta_arr['ihc_drip_end_type'] != 2) ? 'ihc-display-none': ''; ?>" >
							<div class="title-select"><?php esc_html_e('After certain Period:', 'ihc');?></div>
							<div>
									<input type="number" min="0" value="<?php echo esc_attr($meta_arr['ihc_drip_end_numeric_value']);?>" name="ihc_drip_end_numeric_value" class="ihc_drip_start_numeric_value" />
									<select name="ihc_drip_end_numeric_type"><?php
										foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
											?>
											<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['ihc_drip_end_numeric_type']==$k) ? 'selected' : ''; ?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?></select>
							</div>
					</div>

					<div class="js-ump-select-drip-content-end-time-on-specific-date <?php echo ( $meta_arr['ihc_drip_end_type'] != 3) ? 'ihc-display-none': ''; ?>"  >
						<div class="ihc-inside-bootstrap-slide-div-3">
								<div class="title-select"><?php esc_html_e('On Specific Date', 'ihc');?></div>
								<input type="text" value="<?php echo esc_attr($meta_arr['ihc_drip_end_certain_date']);?>" name="ihc_drip_end_certain_date" id="ihc_drip_end_certain_date"/>
						</div>
					</div>

				</div>
			</div>
		</div>

	<?php else :?>
	<div>
		<h3 class="ihc-meta-drip-subtitle"><?php esc_html_e("Release Time", 'ihc');?></h3>
		<div>
			<input id="ihc_drip_start_type" type="text" name="ihc_drip_start_type" />
			<div class="ihc-inside-bootstrap-slide" id="ihc_slide_1">
				<div class="ihc-inside-bootstrap-slide-div-1"><div class="title-select"><?php esc_html_e('Instantanly Subscription', 'ihc');?></div>
				<span><?php esc_html_e('after the user bought the Subscription access', 'ihc');?></span>
				</div>
				<div class="ihc-inside-bootstrap-slide-div-2">
					<div class="title-select"><?php esc_html_e('After Subscription:', 'ihc');?></div>
					<div>
					<input type="number" min="0" value="<?php echo esc_attr($meta_arr['ihc_drip_start_numeric_value']);?>" name="ihc_drip_start_numeric_value" class="ihc_drip_start_numeric_value"/>
					<select name="ihc_drip_start_numeric_type" ><?php
						foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['ihc_drip_start_numeric_type']==$k) ? 'selected' : ''; ?> ><?php echo esc_html($v);?></option>
							<?php
						}
					?></select>
					</div>
				</div>
				<div class="ihc-inside-bootstrap-slide-div-3">
					<div class="title-select"><?php esc_html_e('On Specific Date:', 'ihc');?></div>
					<input type="text" value="<?php echo esc_attr($meta_arr['ihc_drip_start_certain_date']);?>" name="ihc_drip_start_certain_date" id="ihc_drip_start_certain_date"/>

					<div><?php esc_html_e('Pick the desired date when the Page will be available', 'ihc');?></div>

				</div>
			</div>
			<div class="ihc-clear"></div>
		</div>
	</div>

	<div class="ihc-drip-content-special-box">
		<h3 class="ihc-meta-drip-subtitle"><?php esc_html_e("Expiration Time", 'ihc');?></h3>
		<div>
			<input id="ihc_drip_end_type" type="text" name="ihc_drip_end_type"/>
			<div class="ihc-inside-bootstrap-slide" id="ihc_slide_2">
				<div class="ihc-inside-bootstrap-slide-div-1"><div class="title-select"><?php esc_html_e('Never', 'ihc');?></div>
				<span><?php esc_html_e('once is available the content will not expire', 'ihc');?></span>
				</div>
				<div class="ihc-inside-bootstrap-slide-div-2">
					<div class="title-select"><?php esc_html_e('After certain Period:', 'ihc');?></div>
					<input type="number" min="0" value="<?php echo esc_attr($meta_arr['ihc_drip_end_numeric_value']);?>" name="ihc_drip_end_numeric_value"  class="ihc_drip_start_numeric_value" />
					<select name="ihc_drip_end_numeric_type" ><?php
						foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo  ($meta_arr['ihc_drip_end_numeric_type']==$k) ? 'selected' : ''; ?> ><?php echo esc_html( $v );?></option>
							<?php
						}
					?></select>
				</div>
				<div class="ihc-inside-bootstrap-slide-div-3">
					<div class="title-select"><?php esc_html_e('On Specific Date', 'ihc');?></div>
					<input type="text" value="<?php echo esc_attr($meta_arr['ihc_drip_end_certain_date']);?>" name="ihc_drip_end_certain_date" id="ihc_drip_end_certain_date"/>
				</div>
			</div>
		</div>
	</div>

<?php endif;?>

</div>

<div id="ihc_drip_content_empty_meta_box" class="<?php echo esc_attr($show_not_available);?>">
	<?php esc_html_e("First you must select 'Show Page Only' from 'Locker' and add some Targets in order to access this feature!", 'ihc');?>
</div>
