<?php
/**
 * @var $bundle
 * @var $courses
 */

wp_enqueue_style( 'masterstudy-bundle-card' );
?>

<div class="masterstudy-bundle-card <?php echo count( $bundle['courses'] ) > 3 ? 'masterstudy-bundle-card_overcoursed' : ''; ?>">
	<div class="masterstudy-bundle-card__wrapper">
		<a href="<?php echo esc_url( $bundle['url'] ); ?>" class="masterstudy-bundle-card__header">
			<span class="masterstudy-bundle-card__title">
				<?php echo esc_html( $bundle['title'] ); ?>
			</span>
			<span class="masterstudy-bundle-card__subtitle">
				<?php
				/* translators: %s Courses Count */
				printf( esc_html__( '%s Courses', 'masterstudy-lms-learning-management-system-pro' ), count( $bundle['courses'] ) );
				?>
			</span>
		</a>
		<div class="masterstudy-bundle-card__content">
			<div class="masterstudy-bundle-card__content-wrapper">
				<div class="masterstudy-bundle-card__content-courses">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/bundle/card/global/courses',
						array(
							'bundle'  => $bundle,
							'courses' => $courses,
						),
					);
					?>
				</div>
				<div class="masterstudy-bundle-card__bottom">
					<?php
					if ( ! empty( $bundle['rating'] ) ) {
						STM_LMS_Templates::show_lms_template( 'components/bundle/card/global/rating', array( 'bundle' => $bundle ) );
					}
					?>
					<?php STM_LMS_Templates::show_lms_template( 'components/bundle/card/global/price', array( 'bundle' => $bundle ) ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
