<div class="uap-ap-wrap">

	<?php if (!empty($data['title'])):?>
		<h3><?php echo esc_uap_content($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['message'])):?>
		<p><?php echo do_shortcode($data['message']);?></p>
	<?php endif;?>

<form  method="post" class="uap-change-password-form">
	<?php if (!empty($data['module_settings_notf']['uap_referral_notifications_enable'])) : ?>

	<div class="uap-single-notf-row-wrapper">

			<div class="uap-single-notf-row-top">
				<div class="uap-single-notf-col uap-single-notf-label"><?php esc_html_e("Referral Notification", 'uap');?></div>
				<div class="uap-single-notf-col uap-single-notf-checkbox uap-text-align-center"><?php esc_html_e("Activation", 'uap');?></div>
			</div>
			<?php
				$posible_types = uap_get_possible_referral_types();
				$landing_commissions = $indeed_db->get_all_landing_commision_source_type();
				$posible_types = array_merge($posible_types, $landing_commissions);
				$items = array();
				if ($data['metas'] && isset($data['metas']['uap_notifications_on_every_referral_types'])){
					$items = explode(',', $data['metas']['uap_notifications_on_every_referral_types']);
				}
				foreach ($posible_types as $k=>$v):
					$checked = (in_array($k, $items)) ? 'checked' : '';
					?>
					<div class="uap-single-notf-row">
						<div class="uap-single-notf-col uap-single-notf-label"><?php echo esc_html($v['label']);?><span><?php echo (isset($v['sub_label']) ? $v['sub_label'] : '' );?></span></div>
						<div class="uap-single-notf-col uap-single-notf-checkbox"><input type="checkbox" onClick="uapMakeInputhString(this, '<?php echo esc_attr($k);?>', '#uap_types_in')" <?php echo esc_attr($checked);?> /></div>
					</div>
					<?php
				endforeach;
			?>
			<input type="hidden" value="<?php echo esc_attr($data['metas']['uap_notifications_on_every_referral_types']);?>" id="uap_types_in" name="uap_notifications_on_every_referral_types" />
	</div>
	<?php endif;?>
	<?php if (!empty($data['module_settings_reports']['uap_periodically_reports_enable'])) : ?>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Periodic Reports Interval", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-10">
                        <div><?php esc_html_e("You can decide if and when you want to receive reports via Email.", 'uap');?></div>
                        <div>
                            <select name="period"  class="uap-public-form-control "><?php
                                foreach (array(0 => esc_html__('Never send Reports', 'uap'), 1 => esc_html__('Daily Reports', 'uap'), 7 => esc_html__('Weekly Reports', 'uap'), 30 => esc_html__('Monthly Reports', 'uap')) as $k=>$v):
                                    $selected = ($k==$data['report_settings']['period']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
                                    <?php
                                endforeach;
                            ?></select>
                        </div>
          		</div>
             </div>
        </div>
     </div>


	<?php endif;?>
	<div class="uap-change-password-field-wrap">
		<input type="submit" value="<?php esc_html_e("Save Changes", 'uap');?>" name="save_settings" class="button button-primary button-large" />
	</div>
</form>

</div>
