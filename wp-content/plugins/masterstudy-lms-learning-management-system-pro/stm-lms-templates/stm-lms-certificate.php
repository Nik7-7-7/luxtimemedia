<?php
use MasterStudy\Lms\Repositories\CourseRepository;
use MasterStudy\Lms\Pro\addons\certificate_builder\CertificateRepository;

if ( ! is_ms_lms_addon_enabled( 'certificate_builder' ) ) {
	return;
}

$course_id             = isset( $_GET['course'] ) ? intval( $_GET['course'] ) : null;
$user_id               = isset( $_GET['user'] ) ? intval( $_GET['user'] ) : null;
$demo                  = isset( $_GET['demo'] ) ? sanitize_text_field( $_GET['demo'] ) : null;
$certificate_threshold = STM_LMS_Options::get_option( 'certificate_threshold', 70 );
$course_passed         = false;
$demo_course_title     = __( 'Demo course', 'masterstudy-lms-learning-management-system-pro' );
$cert_repo             = new CertificateRepository();
$certificate_page_url  = $cert_repo->certificate_page_url();
$certificate_page_url  = add_query_arg(
	array(
		'user'   => $user_id,
		'course' => $course_id,
	),
	$certificate_page_url
);

if ( $course_id && $user_id ) {
	$progress = STM_LMS_Lesson::get_total_progress( $user_id ?? null, $course_id );

	if ( ! empty( $progress['course']['progress_percent'] ) ) {
		$course_passed = $progress['course']['progress_percent'] >= $certificate_threshold;
	}
}

if ( ( ! $course_passed || ! masterstudy_lms_course_has_certificate( $course_id ) ) && ! $demo ) {
	echo esc_html__( 'The course has not yet been completed by the current user or a certificate has not been assigned for the course.', 'masterstudy-lms-learning-management-system-pro' );
	return;
}

$date_format = get_option( 'date_format', 'F j, Y' );

if ( ! $demo ) {
	$end_date = get_user_meta( $user_id, 'last_progress_time', true );
	$course   = ( new CourseRepository() )->find( $course_id );
	$user     = STM_LMS_USER::get_current_user( $user_id );

	if ( ! empty( $end_date[ $course_id ] ) ) {
		$certificate_date = date_i18n( $date_format, $end_date[ $course_id ] );
	}
}

wp_enqueue_style( 'masterstudy-loader' );
wp_enqueue_style( 'masterstudy-certificate-page' );
wp_register_script( 'jspdf', STM_LMS_PRO_URL . '/assets/js/certificate-builder/jspdf.umd.js', array(), STM_LMS_PRO_VERSION, false );
wp_register_script( 'pdfjs', STM_LMS_PRO_URL . '/assets/js/certificate-builder/pdf.min.js', array(), STM_LMS_PRO_VERSION, true );
wp_register_script( 'pdfjs_worker', STM_LMS_PRO_URL . '/assets/js/certificate-builder/pdf.worker.min.js', array(), STM_LMS_PRO_VERSION, true );
wp_register_script( 'qrcode', STM_LMS_PRO_URL . '/assets/js/certificate-builder/qrcode.min.js', array(), STM_LMS_PRO_VERSION, false );
wp_enqueue_script( 'masterstudy_generate_certificate', STM_LMS_URL . '/assets/js/course-player/generate-certificate.js', array( 'jspdf', 'pdfjs', 'pdfjs_worker', 'qrcode', 'masterstudy_certificate_fonts' ), STM_LMS_PRO_VERSION, true );
wp_localize_script(
	'masterstudy_generate_certificate',
	'course_certificate',
	array(
		'nonce'     => wp_create_nonce( 'stm_get_certificate' ),
		'ajax_url'  => admin_url( 'admin-ajax.php' ),
		'user_id'   => $user_id,
		'course_id' => $course_id,
		'preview'   => true,
		'shapes'    => $cert_repo->get_shapes(),
		'demo'      => $demo,
	)
);
?>

<div class="masterstudy-page-certificate">
	<?php if ( $demo ) { ?>
		<div class="masterstudy-page-certificate__banner">
			<?php echo esc_html__( 'You are currently on the preview page.', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
	<?php } ?>
	<div class="masterstudy-page-certificate__header">
		<div class="masterstudy-page-certificate__header-subtitle">
			<?php echo esc_html__( 'Course certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
		<div class="masterstudy-page-certificate__header-title">
			<?php echo ! $demo ? esc_html( $course->title ) : esc_html( $demo_course_title ); ?>
		</div>
	</div>
	<div class="masterstudy-page-certificate__content">
		<div class="masterstudy-page-certificate__preview-wrapper">
			<?php if ( ! $demo ) { ?>
				<div class="masterstudy-page-certificate__preview masterstudy-page-certificate__preview_empty">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/loader',
						array(
							'dark_mode' => false,
							'is_local'  => true,
						)
					);
					?>
				</div>
				<?php
			} else {
				?>
				<div class="masterstudy-page-certificate__preview">
					<img src="<?php echo esc_url( STM_LMS_PRO_URL . '/assets/img/certificate-builder/demo-cert.png' ); ?>">
				</div>
				<?php
			}
			?>
		</div>
		<div class="masterstudy-page-certificate__sidebar">
			<div class="masterstudy-page-certificate__sidebar-block">
				<div class="masterstudy-page-certificate__user">
					<img src="<?php echo ! $demo ? esc_url( $user['avatar_url'] ) : esc_url( STM_LMS_PRO_URL . '/assets/img/certificate-builder/demo-user.png' ); ?>" class="masterstudy-page-certificate__user-avatar">
					<div class="masterstudy-page-certificate__user-info">
						<?php echo esc_html__( 'Issued to', 'masterstudy-lms-learning-management-system-pro' ); ?>
						<a href="<?php echo ! $demo ? esc_url( $user['url'] ) : '#'; ?>" class="masterstudy-page-certificate__user-name" target="_blank">
							<?php echo ! $demo ? esc_html( $user['login'] ) : esc_html__( 'Demo student', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</a>
					</div>
				</div>
				<?php if ( ! empty( $certificate_date ) || $demo ) { ?>
					<div class="masterstudy-page-certificate__date">
						<div class="masterstudy-page-certificate__date-icon"></div>
						<div class="masterstudy-page-certificate__date-info">
							<?php echo esc_html__( 'Issue date', 'masterstudy-lms-learning-management-system-pro' ); ?>
							<span class="masterstudy-page-certificate__date-value">
								<?php echo ! $demo ? esc_html( $certificate_date ) : esc_html( date_i18n( $date_format ) ); ?>
							</span>
						</div>
					</div>
				<?php } ?>
				<div class="masterstudy-page-certificate__download">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/button',
						array(
							'title'         => esc_html__( 'Download Certificate', 'masterstudy-lms-learning-management-system-pro' ),
							'link'          => ! $demo ? '' : '#',
							'style'         => 'tertiary',
							'size'          => 'sm',
							'id'            => 'download-certificate',
							'download'      => ! $demo ? 'Certificate.pdf' : '',
							'icon_position' => 'left',
							'icon_name'     => 'upload-alt',
						)
					);
					?>
				</div>
				<?php if ( ! empty( $certificate_page_url ) ) { ?>
					<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo rawurlencode( $certificate_page_url ); ?>&title=<?php echo rawurlencode( ! $demo ? $user['login'] : '' ); ?>&source=<?php echo rawurlencode( $certificate_page_url ); ?>" target="_blank" class="masterstudy-page-certificate__share">
						<?php echo esc_html__( 'Share on Linkedin', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</a>
				<?php } ?>
			</div>
			<div class="masterstudy-page-certificate__sidebar-block">
				<?php if ( ! empty( $course->thumbnail ) ) { ?>
					<img src="<?php echo esc_url( $course->thumbnail['url'] ); ?>" class="masterstudy-page-certificate__course-image">
					<?php
				} elseif ( $demo ) {
					?>
					<img src="<?php echo esc_url( esc_url( STM_LMS_PRO_URL . '/assets/img/certificate-builder/demo-course.png' ) ); ?>" class="masterstudy-page-certificate__course-image">
					<?php
				}
				?>
				<div class="masterstudy-page-certificate__course-title">
					<?php echo ! $demo ? esc_html( $course->title ) : esc_html( $demo_course_title ); ?>
				</div>
				<div class="masterstudy-page-certificate__course-info">
					<?php if ( ! $demo && ! empty( $course->level ) ) { ?>
						<div class="masterstudy-page-certificate__course-info-item">
							<?php echo esc_html( $course->level ); ?>
						</div>
						<?php
					}
					if ( ! $demo && ! empty( $course->duration_info ) ) {
						?>
						<span class="masterstudy-page-certificate__course-info-separator">·</span>
						<div class="masterstudy-page-certificate__course-info-item">
							<?php echo esc_html( $course->duration_info ); ?>
						</div>
					<?php } ?>
				</div>
				<div class="masterstudy-page-certificate__course-action">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/button',
						array(
							'title'  => esc_html__( 'Explore Course', 'masterstudy-lms-learning-management-system-pro' ),
							'link'   => ! $demo ? esc_url( $course->url ) : '#',
							'style'  => 'tertiary',
							'size'   => 'sm',
							'id'     => 'explore-course',
							'target' => ! $demo ? '_blank' : '',
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
</div>
