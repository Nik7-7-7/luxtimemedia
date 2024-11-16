<?php
/**
 * Email template
 *
 * @var $email_manager
 * @var $status_header_footer
 * @var $footer_bg
 * @var $status_reply
 * @var $reply_icon
 * @var $footer_reply
 * @var $footer_copyrights
 */
// phpcs:ignoreFile
?>

<tr style="background-color: white;">
	<td class="headerContent-bottom no-margin"
		style="width: 620px;height: 1px;background-color: <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? '#DBE0E9' : 'white'; ?>;display: block;margin: 0 auto;margin-bottom: 0px;">
	</td>
</tr>
<?php
if ( ! empty( $footer_bg ) && ! empty( $status_header_footer ) && ! empty( $email_manager['stm_lms_email_template_branding'] ) ) {
?>
<tr class="columnOneContent courseFooter copyrights"
	style="background-image: url(<?php echo esc_attr( $footer_bg ); ?>);background-repeat: no-repeat;background-size: cover;max-width: 700px;object-fit: cover;height: 155px;width: 700px;position: relative;">
	<?php
	} else {
	?>
</tr>
<tr class="columnOneContent courseFooter copyrights"
	style="max-width: 700px;object-fit: cover;height: 155px;width: 700px;position: relative;">
	<?php
	}
	if ( ! empty( $status_reply ) ) {
		?>

		<td class="copyright-content">
			<p class="reply-email-link"
				style="margin-bottom:30px;text-align:center;vertical-align:middle;line-height:18px;">
				<?php
				if ( ! empty( $reply_icon ) && ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
					?>
					<img src="<?php echo esc_attr( $reply_icon ); ?>" class="courseFooterIcon"
						style="margin-right:3px;width:18px;display:inline-block;vertical-align:middle;">
					<?php
				}
				?>
				<span style="display:inline-block; vertical-align: middle;">
					<?php
					if ( ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
						echo esc_html( $footer_reply );
					}
					?>
				</span>
			</p>

			<?php
			if ( ! empty( $footer_copyrights ) && ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
				?>
				<div class="content">
					<p style="margin-bottom: 30px;text-align: center;">
						<?php echo $footer_copyrights; // phpcs:ignore ?>
					</p>
				</div>
				<?php
			}
			?>
		</td>
		<?php
	} elseif ( ! empty( $email_manager['stm_lms_email_template_branding'] ) && empty( $footer_bg ) ) {
	?>
</tr>
<tr class="columnOneContent courseFooter copyrights"
	style="max-width: 700px;object-fit: cover;height: 100px;width: 700px;position: relative;">
	<td>
		<p class="reply-email-link"
			style="display: flex;align-items: center;justify-content: center;margin-bottom: 30px;text-align: center;">
		</p>
	</td>
	<?php
	}
	?>
	<td></td>
</tr>
