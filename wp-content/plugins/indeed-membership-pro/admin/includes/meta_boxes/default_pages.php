<?php
global $post;
$set_arr = ihc_get_default_pages_il(true);
if ($set_arr && count($set_arr) && in_array($post->ID, $set_arr)) {
	//if current page is set to be default page go back
	echo ihc_meta_box_page_type_message();
}else{
	$unset_arr = ihc_get_default_pages_il();//getting the unset pages
	if ($unset_arr){
		$unset_arr = ihc_get_default_pages_il();
		if($unset_arr){
			//the select
			?>
			<div class="ihc-padding">
			<div class="ihc-bold"><?php esc_html_e('Set this Page as:', 'ihc');?></div>
			<select class="ihc-fullwidth ihc-select" name="ihc_set_page_as_default_something">
				<option value="-1">...</option>
				<?php
					foreach($unset_arr as $name=>$label){
						?>
							<option value="<?php echo esc_attr($name);?>"><?php echo esc_html($label);?> <?php esc_html_e('Page', 'ihc');?></option>
						<?php
					}
				?>
			</select>
			<input type="hidden" name="ihc_post_id" value="<?php echo esc_attr($post->ID);?>" />
			</div>
			<?php
		}
		echo esc_ump_content('<div class="ihc-info-box">');
		echo ihc_check_default_pages_set(true);
		echo esc_ump_content('</div>');
	} else {
		?>
		<div class="ihc-meta-box-message"><?php echo esc_html__('All the required pages are properly set, to change them click ', 'ihc') . '<a href="' . admin_url('admin.php?page=ihc_manage&tab=general') . '">' . esc_html__('here', 'ihc') . '</a>.';?></div>
		<?php
	}
}
