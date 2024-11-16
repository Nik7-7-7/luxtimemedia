<div class="iump-view-user-wrapp-temp1 iump-color-scheme-<?php echo esc_attr($data['color_scheme_class']);?>">
<?php $custom_css = ''; ?>
	<?php if ($data['color_scheme_class'] !=''){
		$custom_css .= "
		.iump-view-user-wrapp-temp1 .ihc-levels-wrapper .ihc-top-level-box{
			background-color:#".$data['color_scheme_class'].";
			border-color:#".$data['color_scheme_class'].";
			color:#fff;
		}
		.iump-view-user-wrapp-temp1 .ihc-middle-side .iump-name{
			color:#".$data['color_scheme_class']."!important;
		}
		.iump-view-user-wrapp-temp1 .ihc-levels-wrapper{
			background-color: transparent;
		}";
	} ?>

	<?php if (empty($data['banner'])){
		$custom_css .= "
		.iump-view-user-wrapp-temp1 .ihc-user-page-top-ap-wrapper{
			padding-top:70px;
		}";
	 } ?>

<?php if (!empty($data['banner'])){
				if($data['banner'] !='default'){
					$custom_css .= "
					.iump-view-user-wrapp-temp1 .ihc-user-page-top-ap-background{
						background-image:url('".esc_url($data['banner'])."') !important;
					}";
				}
			} ?>
	<?php if ( !empty( $data['ihc_badges_on'] ) && !empty( $data['ihc_badge_custom_css'] ) ):?>
			<?php $custom_css .= stripslashes( $data['ihc_badge_custom_css'] );?>
	<?php endif;?>

<?php

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

 ?>
	<div class="ihc-user-page-top-ap-wrapper">
	<?php if (!empty($data['avatar'])):?>
		<div class="ihc-left-side">
			<div class="ihc-user-page-details">
				<div class="ihc-user-page-avatar"><img src="<?php echo esc_url($data['avatar']);?>" class="ihc-member-photo"></div>
			</div>
		</div>
	<?php endif;?>
	<div class="ihc-middle-side">
		<?php if (!empty($data['flag'])):?>
            <span class="iump-flag"><?php echo esc_ump_content($data['flag']);?></span>
		<?php endif;?>
		<?php if (!empty($data['name'])):?>
			<span class="iump-name"><?php echo esc_html($data['name']);?></span>
		<?php endif;?>
		<div class="iump-addiional-elements">
		<?php if (!empty($data['username'])):?>
			<span class="iump-element iump-username"><?php echo esc_html($data['username']);?></span>
		<?php endif;?>

		<?php if (!empty($data['email'])):?>
			<span class="iump-element iump-email"><?php echo esc_html($data['email']);?></span>
		<?php endif;?>

		<?php if (!empty($data['website'])):?>
			<span class="iump-element iump-website"><a href="<?php echo esc_url($data['website']);?>" target="_blank"><?php echo esc_url($data['website']);?></a></span>
		<?php endif;?>

		<?php if (!empty($data['since'])):?>
			<span class="iump-element iump-since"><?php echo esc_html__('Joined ', 'ihc');?><?php echo esc_html($data['since']);?></span>
		<?php endif;?>
		</div>

	</div>
	<div class="ihc-clear"></div>
	<?php if (!empty($data['banner'])):?>
	<div class="ihc-user-page-top-ap-background"></div>
	<?php endif;?>

	</div>
	<?php if (!empty($data['levels'])):?>
		<div class="ihc-levels-wrapper">
			<?php foreach ($data['levels'] as $lid => $level):?>
				<?php
					$is_expired_class = '';
					if (isset($level['expire_time']) && indeed_get_unixtimestamp_with_timezone()>strtotime( $level['expire_time'] ) ){
						$is_expired_class = 'ihc-expired-level';
					}
				?>
				<?php if (!empty($data['ihc_badges_on']) && !empty($level['badge_image_url'])):?>
					<div class="iump-badge-wrapper <?php echo esc_attr($is_expired_class);?>"><img src="<?php echo esc_url($level['badge_image_url']);?>" class="iump-badge" title="<?php echo esc_attr($level['label']);?>" /></div>
				<?php elseif (!empty($level['label'])):?>
					<div class="ihc-top-level-box <?php echo esc_attr($is_expired_class);?>"><?php echo esc_html($level['label']);?></div>
				<?php endif;?>
			<?php endforeach;?>
		</div>
	<?php endif;?>



	<?php if (!empty($data['custom_fields'])):
    ?>
		<div class="iump-user-fields-list">
			<?php foreach ($data['custom_fields'] as $label => $value):?>
				<?php if ($value!=''):?>
					<div class="iump-user-field"><div class="iump-label"><?php echo esc_html($label); ?></div> <div class="iump-value"><p> <?php echo esc_ump_content($value);?> </p></div><div class="ihc-clear"></div></div>

				<?php endif;?>
			<?php endforeach;?>
		</div>
	<?php endif;?>

	<?php if (!empty($data['content'])):?>
		<div class="iump-additional-content">
			<?php echo esc_ump_content($data['content']);?>
		</div>
	<?php endif;?>

</div>
