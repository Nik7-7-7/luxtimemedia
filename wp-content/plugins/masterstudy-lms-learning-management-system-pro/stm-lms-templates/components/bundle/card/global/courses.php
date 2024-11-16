<?php
/**
 * @var $bundle
 * @var $courses
 */

if ( ! empty( $bundle['courses'] ) ) { ?>
	<div class="masterstudy-bundle-card__courses">
		<?php
		foreach ( $bundle['courses'] as $course_id ) {
			if ( empty( $courses[ $course_id ] ) ) {
				continue;
			}
			$course_data = $courses[ $course_id ];
			?>
			<a class="masterstudy-bundle-card__course" href="<?php echo esc_url( $course_data['link'] ); ?>">
				<div class="masterstudy-bundle-card__course-image">
					<?php echo stm_lms_filtered_output( $course_data['image'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="masterstudy-bundle-card__course-data">
					<div class="masterstudy-bundle-card__course-title">
						<?php echo esc_html( $course_data['title'] ); ?>
					</div>
					<div class="masterstudy-bundle-card__course-price">
						<?php echo esc_html( ! empty( $course_data['sale_price'] ) ? $course_data['sale_price'] : $course_data['price'] ); ?>
					</div>
					<?php if ( ! empty( $course_data['sale_price'] ) ) { ?>
						<small class="masterstudy-bundle-card__course-price_discounted">
						<?php echo esc_html( $course_data['price'] ); ?>
						</small>
					<?php } ?>
				</div>
			</a>
		<?php } ?>
	</div>
<?php } ?>
