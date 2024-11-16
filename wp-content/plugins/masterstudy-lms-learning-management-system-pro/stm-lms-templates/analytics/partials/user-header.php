<?php
/**
 * @var string $page_slug
 * @var string $default_title
 * @var string $previous_page
 * @var boolean $is_user_account
 * @var int $user_id
 */
?>

<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__header">
	<?php
	STM_LMS_Templates::show_lms_template(
		'components/back-link',
		array(
			'id'  => $page_slug,
			'url' => ! empty( $previous_page ) ? $previous_page : masterstudy_get_current_url( array( 'user_id', 'role' ) ),
		)
	);
	?>
	<h1 class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__title">
		<?php
		$default_title_class = "masterstudy-analytics-{$page_slug}-page__title-role_self";

		if ( ! empty( $user_id ) ) {
			$user_info = get_userdata( $user_id );

			if ( $user_info && ( ! empty( $user_info->first_name ) || ! empty( $user_info->last_name ) ) ) {
				$default_title_class = '';
				?>
				<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__name">
					<?php echo esc_html( $user_info->first_name . ' ' . $user_info->last_name ); ?>
				</span>
				<?php
			}
		}
		?>
		<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__role <?php echo esc_attr( $default_title_class ); ?>">
			<?php echo esc_html( $default_title ); ?>
		</span>
	</h1>
	<div class="masterstudy-analytics__date">
		<div class="masterstudy-analytics__date-label"></div>
		<div class="masterstudy-analytics__date-value"></div>
	</div>
</div>
<?php if ( $is_user_account ) { ?>
	<div class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator">
		<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator-short"></span>
		<span class="masterstudy-analytics-<?php echo esc_html( $page_slug ); ?>-page__separator-long"></span>
	</div>
	<?php
}
STM_LMS_Templates::show_lms_template(
	'components/analytics/datepicker-modal',
	array(
		'id' => $page_slug,
	)
);
