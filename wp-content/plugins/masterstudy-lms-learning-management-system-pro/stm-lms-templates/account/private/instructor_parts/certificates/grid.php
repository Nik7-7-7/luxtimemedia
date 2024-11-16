<?php

use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateRepository;

wp_enqueue_style( 'masterstudy-instructor-certificates' );
wp_enqueue_script( 'masterstudy-instructor-certificates' );

$current_page        = get_query_var( 'page' ) > 0 ? get_query_var( 'page' ) : 1;
$repo                = new CertificateRepository();
$data                = $repo->get_all();
$default_certificate = get_option( 'stm_default_certificate' );
$is_admin            = current_user_can( 'administrator' );
$categories          = get_terms(
	array(
		'taxonomy'   => 'stm_lms_course_taxonomy',
		'hide_empty' => false,
	)
);
if ( ! empty( $categories ) ) {
	foreach ( $categories as $category ) {
		$category_options[ $category->term_id ] = $category->name;
	}
}
if ( ! empty( $data['certificates'] ) ) {
	foreach ( $data['certificates'] as &$item ) {
		$item['instructor']                        = get_the_author_meta( 'display_name', $item['author_id'] );
		$instructors_options[ $item['author_id'] ] = $item['instructor'];
	}
}
?>

<div class="masterstudy-instructor-certificates">
	<?php
	if ( empty( $data['certificates'] ) && empty( $_GET ) ) {
		STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/certificates/no-certificates' );
	} else {
		?>
		<div class="masterstudy-instructor-certificates__header">
			<span class="masterstudy-instructor-certificates__title">
				<?php echo esc_html__( 'Certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
			<?php
			STM_LMS_Templates::show_lms_template(
				'components/search',
				array(
					'select_name'  => 's',
					'is_queryable' => false,
					'placeholder'  => esc_html__( 'Search by name', 'masterstudy-lms-learning-management-system-pro' ),
				)
			);
			if ( $is_admin ) {
				?>
				<div class="masterstudy-instructor-certificates__header-wrapper">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/select',
						array(
							'select_name'  => 'by_category',
							'placeholder'  => esc_html__( 'All categories', 'masterstudy-lms-learning-management-system-pro' ),
							'select_width' => '200px',
							'is_queryable' => false,
							'options'      => $category_options ?? array(),
						)
					);
					STM_LMS_Templates::show_lms_template(
						'components/select',
						array(
							'select_name'  => 'by_instructor',
							'placeholder'  => esc_html__( 'All instructors', 'masterstudy-lms-learning-management-system-pro' ),
							'select_width' => '220px',
							'is_queryable' => false,
							'options'      => $instructors_options ?? array(),
						)
					);
					?>
				</div>
				<?php
			}
			STM_LMS_Templates::show_lms_template(
				'components/button',
				array(
					'id'    => 'masterstudy-add-new-certificate',
					'title' => esc_html__( 'Add new', 'masterstudy-lms-learning-management-system-pro' ),
					'link'  => admin_url( 'admin.php?page=certificate_builder' ),
					'style' => 'primary',
					'size'  => 'sm',
				)
			);
		?>
		</div>
		<div class="masterstudy-instructor-certificates__content">
			<div class="masterstudy-instructor-certificates__heading">
				<div class="masterstudy-instructor-certificates__heading-title">
					<?php echo esc_html__( 'Certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</div>
				<?php if ( $is_admin ) { ?>
					<div class="masterstudy-instructor-certificates__heading-category">
						<?php echo esc_html__( 'Category', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</div>
					<div class="masterstudy-instructor-certificates__heading-instructor">
						<?php echo esc_html__( 'Instructor', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</div>
				<?php } ?>
				<div class="masterstudy-instructor-certificates__heading-id">
					<?php echo esc_html__( 'ID', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</div>
				<div class="masterstudy-instructor-certificates__heading-actions"></div>
			</div>
			<ul class="masterstudy-instructor-certificates__list">
				<li class="masterstudy-instructor-certificates__item masterstudy-instructor-certificates__item--hidden">
					<div class="masterstudy-instructor-certificates__item-wrapper">
						<div class="masterstudy-instructor-certificates__item-heading">
							<?php echo esc_html__( 'Certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</div>
						<div class="masterstudy-instructor-certificates__item-image masterstudy-instructor-certificates__data" data-key="image" data-value=""></div>
						<div class="masterstudy-instructor-certificates__item-title masterstudy-instructor-certificates__data" data-key="title" data-value=""></div>
					</div>
					<div class="masterstudy-instructor-certificates__item-wrapper">
						<?php if ( $is_admin ) { ?>
							<div class="masterstudy-instructor-certificates__item-meta">
								<div class="masterstudy-instructor-certificates__item-meta-title">
									<?php echo esc_html__( 'Category', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
								<div class="masterstudy-instructor-certificates__item-category masterstudy-instructor-certificates__data" data-key="category_name" data-value="">
									<?php echo esc_html__( 'All categories', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
							</div>
							<div class="masterstudy-instructor-certificates__item-meta">
								<div class="masterstudy-instructor-certificates__item-meta-title">
									<?php echo esc_html__( 'Instructor', 'masterstudy-lms-learning-management-system-pro' ); ?>
								</div>
								<div class="masterstudy-instructor-certificates__item-instructor masterstudy-instructor-certificates__data" data-key="instructor"></div>
							</div>
						<?php } ?>
						<div class="masterstudy-instructor-certificates__item-meta">
							<div class="masterstudy-instructor-certificates__item-meta-title">
								<?php echo esc_html__( 'ID', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</div>
							<div class="masterstudy-instructor-certificates__item-id masterstudy-instructor-certificates__data" data-key="id" data-value=""></div>
						</div>
						<div class="masterstudy-instructor-certificates__item-actions masterstudy-instructor-certificates__data" data-key="edit_link" data-value="">
							<?php
							STM_LMS_Templates::show_lms_template(
								'components/button',
								array(
									'id'    => 'masterstudy-instructor-certificates-edit',
									'title' => esc_html__( 'Edit', 'masterstudy-lms-learning-management-system-pro' ),
									'link'  => '',
									'style' => 'secondary',
									'size'  => 'sm',
								)
							);
							?>
							<span class="masterstudy-instructor-certificates__delete masterstudy-instructor-certificates__data" data-key="delete_id" data-value=""></span>
						</div>
					</div>
				</li>
				<li class="masterstudy-instructor-certificates__list-not-found masterstudy-instructor-certificates__item--hidden">
					<?php echo esc_html__( 'Certificates not found', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</li>
			</ul>
			<div class="masterstudy-instructor-certificates__content-bottom hidden">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/pagination',
					array(
						'max_visible_pages' => 3,
						'total_pages'       => 1,
						'dark_mode'         => false,
						'current_page'      => 1,
						'is_queryable'      => false,
						'is_ajax'           => true,
						'done_indicator'    => false,
					)
				);
				?>
				<div class="masterstudy-instructor-certificates__content-bottom-wrapper">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/select',
						array(
							'select_name'  => 'per_page',
							'placeholder'  => esc_html__( '10 per page', 'masterstudy-lms-learning-management-system-pro' ),
							'select_width' => '160px',
							'is_queryable' => false,
							'options'      => array(
								'25'  => esc_html__( '25 per page', 'masterstudy-lms-learning-management-system-pro' ),
								'50'  => esc_html__( '50 per page', 'masterstudy-lms-learning-management-system-pro' ),
								'75'  => esc_html__( '75 per page', 'masterstudy-lms-learning-management-system-pro' ),
								'100' => esc_html__( '100 per page', 'masterstudy-lms-learning-management-system-pro' ),
							),
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
		do_action( 'masterstudy_after_certificates_grid' );
		STM_LMS_Templates::show_lms_template(
			'components/alert',
			array(
				'id'                  => 'masterstudy-instructor-certificates-alert',
				'title'               => esc_html__( 'Delete certificate', 'masterstudy-lms-learning-management-system-pro' ),
				'text'                => esc_html__( 'Are you sure you want to delete this certificate?', 'masterstudy-lms-learning-management-system-pro' ),
				'submit_button_text'  => esc_html__( 'Delete', 'masterstudy-lms-learning-management-system-pro' ),
				'cancel_button_text'  => esc_html__( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ),
				'submit_button_style' => 'danger',
				'cancel_button_style' => 'tertiary',
				'dark_mode'           => false,
			)
		);
		STM_LMS_Templates::show_lms_template(
			'components/loader',
			array(
				'dark_mode' => false,
				'is_local'  => true,
			)
		);
	}
	?>
</div>
