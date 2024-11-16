<?php
/**
 * Email template
 *
 * @var $email_manager
 * @var $status_header_footer
 * @var $header_bg
 * @var $logo
 */
?>
<tr>
	<td align="center" valign="top">
		<!-- // Begin Template Header \\ -->
		<?php
		if ( $email_manager['stm_lms_email_template_branding'] ) {
			?>
			<table border="0" cellpadding="0" cellspacing="0" id="templateHeader"
				style="border-bottom: 0;">
				<tbody>
				<tr
					<?php if ( ! empty( $header_bg ) && ! empty( $status_header_footer ) ) { ?>
						style="background-image: url(<?php echo esc_attr( $header_bg ); ?>); background-repeat: no-repeat; background-size: cover;"
					<?php } ?>
				>
					<td class="headerContent" style="text-align:center;">
						<div
							style="max-width:700px;object-fit:cover;height:95px;width:700px;line-height:95px;"
							id="headerImage campaign-icon">
							<?php
							if ( ! empty( $logo ) && ! empty( $status_header_footer ) ) {
								?>
								<img src="<?php echo esc_attr( $logo ); ?>"
									style="max-width:700px;width:200px;height:35px;object-fit:contain;display: inline-block; vertical-align: middle;"
									id="headerImage campaign-icon" mc:label="header_image"
									mc:edit="header_image" mc:allowdesigner="" mc:allowtext=""
									alt="" class="email-logo">
								<?php
							}
							?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="headerContent-bottom"
						style="width: 620px;height: 1px;background-color: <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? '#DBE0E9' : 'white'; ?>;display: block;margin: 0 auto;margin-bottom: 50px;">
					</td>
				</tr>
				</tbody>
			</table>
			<?php
		}
		?>
		<!-- // End Template Header \\ -->
	</td>
</tr>
