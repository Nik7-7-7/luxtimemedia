<?php
$data['icon_prin_id'] = rand(1,1000) . 'printdiv';
$data['wrapp_id'] = rand(1,1000) . 'ihccard';
?>


<div id="<?php echo esc_attr($data['wrapp_id']);?>" onmouseover="ihcShowPrint('<?php echo esc_ump_content('#') . esc_attr($data['icon_prin_id']);?>');" onMouseOut="ihcHidePrint('<?php echo esc_ump_content('#') . esc_attr($data['icon_prin_id']);?>');" class="ihc-membership-card-wrapp <?php echo (isset($data['metas']['ihc_membership_card_size'])) ? esc_attr($data['metas']['ihc_membership_card_size']) : '';?>
	 <?php echo (isset($data['metas']['ihc_membership_card_template'])) ? $data['metas']['ihc_membership_card_template'] : '';?>">
	 <?php if(isset($data['metas']['ihc_membership_card_background_color']) && $data['metas']['ihc_membership_card_background_color'] != ''){
	 	$custom_css = '';
	 	$custom_css .= "
	 	.ihc-membership-card-wrapp{
	 		background-color:".$data['metas']['ihc_membership_card_background_color']." !important;
	 	}
	 	";
	 	wp_register_style( 'dummy-handle', false );
	 	wp_enqueue_style( 'dummy-handle' );
	 	wp_add_inline_style( 'dummy-handle', $custom_css );

	 	?>
		<style><?php echo esc_html($custom_css);?></style>
	 <?php } ?>
<?php switch ($data['metas']['ihc_membership_card_template']) {
	case 'ihc-membership-card-2':
	case 'ihc-membership-card-3': ?>
		<div class="ihc-membership-card-img">
			<div class="ihc-membership-card-image">
				<?php if ($data['metas']['ihc-membership-card-settings-image-type'] === '1' && !empty($data['metas']['ihc_membership_card_image'])){
					?>
					<img src="<?php echo esc_url($data['metas']['ihc_membership_card_image']);?>" alt="<?php echo (isset($data['full_name'])) ? $data['full_name'] : '';?>" />
				<?php }elseif($data['metas']['ihc-membership-card-settings-image-type'] === '2'){
								$avatar_img = ihc_get_avatar_for_uid($current_user->ID);
					?>
						<img src="<?php echo esc_url($avatar_img) ;?>" alt="<?php echo (isset($data['full_name'])) ? $data['full_name'] : '';?>" />
				<?php } ?>
			</div>
		</div>
		<div class="ihc-membership-card-content">
			<div class="ihc-membership-card-full-name">
				<?php echo (isset($data['full_name'])) ? $data['full_name'] : '';?>
			</div>
			<?php if (!empty($data['metas']['ihc_membership_member_since_enable'])):?>
				<div class="ihc-membership-card-member-since">
					<label><?php echo (isset($data['metas']['ihc_membership_member_since_label'])) ? $data['metas']['ihc_membership_member_since_label'] : '';?></label> <span class="ihc-membership-card-data"> <?php echo (isset($data['member_since'])) ? $data['member_since'] : '';?></span>
				</div>
			<?php endif;?>
			<?php if (!empty($data['metas']['ihc_membership_member_show_uid'])):?>
				<div class="ihc-membership-card-uid">
					<label><?php echo (isset($data['metas']['ihc_membership_member_uid_label'])) ? $data['metas']['ihc_membership_member_uid_label'] : '';?></label> <span class="ihc-membership-card-data"><?php echo (isset($current_user->ID)) ? $current_user->ID : '';?></span>
				</div>
			<?php endif;?>
			<div class="ihc-membership-card-level">
				<label><?php echo (isset($data['metas']['ihc_membership_member_level_label'])) ? $data['metas']['ihc_membership_member_level_label'] : '';?></label> <span class="ihc-membership-card-data"> <?php echo (isset($level_data['label'])) ? $level_data['label'] : '';?></span>
			</div>
			<?php if (!empty($data['metas']['ihc_membership_member_level_expire'])):?>
				<div class="ihc-membership-level-expire">
					<label><?php echo (isset($data['metas']['ihc_membership_member_level_expire_label'])) ? $data['metas']['ihc_membership_member_level_expire_label'] : '';?></label> <span class="ihc-membership-card-data"> <?php echo (isset($level_data['expire_time'])) ? ihc_convert_date_to_us_format($level_data['expire_time']) : '';?></span>
				</div>
			<?php endif;?>

			<?php if (!empty($data['custom_fields'])):?>
				<div class="ihc-membership-extra-fields">
					<?php foreach ($data['custom_fields'] as $key => $value) { ?>
						<div class="ihc-membership-extra-field">
							<label><?php echo (isset($key)) ? $key : '';?>:</label> <span class="ihc-membership-card-data"> <?php echo (isset($value)) ? $value : '';?></span>
						</div>
					<?php } ?>
				</div>
			<?php endif;?>

		</div>
	<?php
			break;
	?>
	<?php default: ?>
	<div class="ihc-membership-card-content">
		<div class="ihc-membership-card-full-name">
			<?php echo (isset($data['full_name'])) ? $data['full_name'] : '';?>
		</div>
		<?php if (!empty($data['metas']['ihc_membership_member_since_enable'])):?>
			<div class="ihc-membership-card-member-since">
				<label><?php echo (isset($data['metas']['ihc_membership_member_since_label'])) ? $data['metas']['ihc_membership_member_since_label'] : '';?></label>
				<span class="ihc-membership-card-data"> <?php echo (isset($data['member_since'])) ? $data['member_since'] : '';?></span>
			</div>
		<?php endif;?>
        <?php if (!empty($data['metas']['ihc_membership_member_show_uid'])):?>
				<div class="ihc-membership-card-uid">
					<label><?php echo (isset($data['metas']['ihc_membership_member_uid_label'])) ? $data['metas']['ihc_membership_member_uid_label'] : '';?></label>
					<span class="ihc-membership-card-data"><?php echo (isset($current_user->ID)) ? $current_user->ID : '';?></span>
				</div>
			<?php endif;?>
		<div class="ihc-membership-card-level">
			<label><?php echo (isset($data['metas']['ihc_membership_member_level_label'])) ? $data['metas']['ihc_membership_member_level_label'] : '';?></label>
			<span class="ihc-membership-card-data"> <?php echo (isset($level_data['label'])) ? $level_data['label'] : '';?></span>
		</div>
		<?php if (!empty($data['metas']['ihc_membership_member_level_expire'])):?>
			<div class="ihc-membership-level-expire">
				<label><?php echo (isset($data['metas']['ihc_membership_member_level_expire_label'])) ? $data['metas']['ihc_membership_member_level_expire_label'] : '';?></label> <span class="ihc-membership-card-data"> <?php echo (isset($level_data['expire_time'])) ? ihc_convert_date_to_us_format($level_data['expire_time']) : '';?></span>
			</div>
		<?php endif;?>


		<?php if (!empty($data['custom_fields'])):?>
			<div class="ihc-membership-extra-fields">
				<?php foreach ($data['custom_fields'] as $key => $value) { ?>
					<div class="ihc-membership-extra-field">
						<label><?php echo (isset($key)) ? $key : '';?>:</label> <span class="ihc-membership-card-data"> <?php echo (isset($value)) ? $value : '';?></span>
					</div>
				<?php } ?>
			</div>
		<?php endif;?>

	</div>
	<div class="ihc-membership-card-img">
		<div class="ihc-membership-card-image">
			<?php if (!empty($data['metas']['ihc_membership_card_image'])):?>
				<img src="<?php echo esc_url($data['metas']['ihc_membership_card_image']);?>" alt="<?php echo (isset($data['full_name'])) ? $data['full_name'] : '';?>"  />
			<?php endif;?>
		</div>
	</div>
	<?php } ?>
	<div class="ihc-print-icon" id="<?php echo esc_attr($data['icon_prin_id']);?>"><i class="fa-ihc fa-print-ihc" onClick="ihcHidePrint('<?php echo esc_ump_content('#') . esc_attr($data['icon_prin_id']);?>');" data-id-to-print="<?php echo esc_attr($data['wrapp_id']);?>" ></i></div>

</div>
