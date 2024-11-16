<?php // phpcs:ignoreFile
/**
 * Email template
 *
 * @var $icon
 * @var $value
 * @var $text
 * @var $style_type  // 'learning' or 'common' to switch styles dynamically
 */

// Define dynamic styles based on $style_type
if ( $style_type === 'learning' ) {
	// Learning-specific styles (column layout)
	$container_styles = "background-color: #f0f4f8; border-radius: 8px; text-align: center; font-size: 18px; font-weight: bold; padding: 20px; width: 100%;";
	$icon_styles = "margin-bottom: 10px; display: block; text-align: center;";
	$inner_values_styles = "text-align: center;";
} else {
	// Common style for row layout using table
	$container_styles = "background-color: #f0f4f8; padding: 20px 30px; border-radius: 8px; font-size: 18px; font-weight: bold; width: 100%;";
	$icon_styles = "padding-right: 5px; vertical-align: middle; text-align: left;";
	$inner_values_styles = "vertical-align: middle; text-align: left;";
}
?>

<table border="0" cellpadding="0" cellspacing="0" style="<?php echo $container_styles; ?>" width="100%">
	<?php if ( $style_type === 'learning' ) : ?>
		<!-- Learning layout as a column -->
		<tr>
			<td class="analytics-reports-icon" style="<?php echo $icon_styles; ?>">
				<img src="<?php echo esc_url( $icon ); ?>" alt="" style="max-width: 50px; height: auto;">
			</td>
		</tr>
		<tr>
			<td class="analytics-reports-inner-values" style="<?php echo $inner_values_styles; ?>">
				<div class="analytics-reports-inner-value" style="color: #001931; font-family: 'system-ui'; font-size: 18px; font-style: normal; font-weight: 700; line-height: normal;">
					<?php
					if ( is_array( $value ) ) {
						echo esc_html( $value[0] ?? 0 );
					} elseif ( is_scalar( $value ) ) {
						echo esc_html( $value );
					} else {
						echo esc_html( 0 );
					}
					?>
				</div>
				<div class="analytics-reports-inner-text" style="line-height: 1.2; color: #4D5E6F; font-family: 'system-ui'; font-size: 14px; font-style: normal; font-weight: 500;">
					<?php echo esc_html( $text ); ?>
				</div>
			</td>
		</tr>
	<?php else : ?>
		<!-- Common layout as a row -->
		<tr>
			<td class="analytics-reports-icon" style="<?php echo $icon_styles; ?>" width="50">
				<img src="<?php echo esc_url( $icon ); ?>" alt="" style="max-width: 50px; height: auto;">
			</td>
			<td class="analytics-reports-inner-values" style="<?php echo $inner_values_styles; ?>">
				<div class="analytics-reports-inner-value" style="color: #001931; font-family: 'system-ui'; font-size: 18px; font-style: normal; font-weight: 700; line-height: normal;">
					<?php
					if ( is_array( $value ) ) {
						echo esc_html( $value[0] ?? 0 );
					} elseif ( is_scalar( $value ) ) {
						echo esc_html( $value );
					} else {
						echo esc_html( 0 );
					}
					?>
				</div>
				<div class="analytics-reports-inner-text" style="line-height: 1.2; color: #4D5E6F; font-family: 'system-ui'; font-size: 14px; font-style: normal; font-weight: 500;">
					<?php echo esc_html( $text ); ?>
				</div>
			</td>
		</tr>
	<?php endif; ?>
</table>
