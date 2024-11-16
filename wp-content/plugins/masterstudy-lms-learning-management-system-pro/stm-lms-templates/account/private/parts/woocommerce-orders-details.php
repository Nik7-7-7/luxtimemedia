<?php
get_header();
do_action( 'stm_lms_template_main' );

stm_lms_register_style( 'user_info_top' );
stm_lms_register_style( 'user-orders' );
wp_enqueue_style( 'masterstudy-woocommerce-orders' );
wp_enqueue_script( 'masterstudy-woocommerce-orders' );
wp_localize_script(
	'masterstudy-woocommerce-orders',
	'masterstudy_woocommerce_orders',
	array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ms_lms_nonce' ),
	)
);

$order_info    = STM_LMS_Order::get_order_info( $order_id );
$order_details = apply_filters( 'stm_lms_order_details', null, $order_id );

STM_LMS_Templates::show_lms_template( 'modals/preloader' );
?>
<div class="stm-lms-wrapper user-account-page">
	<div class="container">
		<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>
		<div class="masterstudy-orders">
			<img src="<?php echo esc_url( STM_LMS_PRO_URL . '/assets/img/ms-logo.png' ); ?>" style="display: none;" width="180" height="40" class="masterstudy-orders__site-logo">
			<div class="masterstudy-orders-details">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'title'         => '',
						'link'          => ms_plugin_user_account_url( 'my-orders' ),
						'style'         => 'secondary',
						'size'          => 'sm',
						'icon_position' => 'left',
						'icon_name'     => 'arrow-left',
					)
				);
				?>
				<div class="masterstudy-orders-details__id">
					<span><?php echo esc_html__( 'Order:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<?php echo esc_html( $order_info['id'] ); ?>
				</div>
				<div class="masterstudy-orders-details__date">
					<span><?php echo esc_html__( 'Date:', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
					<?php echo esc_html( $order_info['date_formatted'] ); ?>
				</div>
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'title'         => esc_html__( 'Print', 'masterstudy-lms-learning-management-system-pro' ),
						'link'          => '#',
						'style'         => 'secondary',
						'size'          => 'sm',
						'id'            => 'print-button',
						'icon_position' => 'left',
						'icon_name'     => 'print',
					)
				);
				?>
			</div>
			<div class="multiseparator"></div>
			<div class="masterstudy-orders-container">
				<div id="masterstudy-order-template">
					<div class="masterstudy-orders-table masterstudy-orders-table__details">
						<div class="masterstudy-orders-table__header">
							<div class="masterstudy-orders-course-info">
								<div class="masterstudy-orders-course-info__id"><?php echo esc_html__( 'Order details', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
							</div>
						</div>
						<div class="masterstudy-orders-table__body">
							<?php
								$items_info = array();

							foreach ( $order_info['items'] as $item ) {
								$items_info[ $item['item_id'] ] = array(
									'bundle_id'     => $item['bundle_id'] ?? null,
									'enterprise_id' => $item['enterprise_id'] ?? null,
								);
							}

							foreach ( $order_info['cart_items'] as $item_id => $order ) :
								$bundle_id     = $items_info[ $item_id ]['bundle_id'] ?? null;
								$enterprise_id = $items_info[ $item_id ]['enterprise_id'] ?? null;
								?>
							<div class="masterstudy-orders-table__body-row">
								<div class="masterstudy-orders-course-info">
									<div class="masterstudy-orders-course-info__image">
										<a href="<?php echo esc_url( $order['link'] ); ?>">
											<img width="300" height="225" src="<?php echo esc_url( wp_get_attachment_url( $order['thumbnail_id'] ) ); ?>" class="attachment-img-300-225 size-img-300-225 wp-post-image" alt="<?php echo esc_attr( $order['title'] ); ?>" decoding="async" loading="lazy">
										</a>
									</div>
									<div class="masterstudy-orders-course-info__common">
										<div class="masterstudy-orders-course-info__title">
											<a href="<?php echo esc_url( $order['link'] ); ?>"><?php echo esc_html( $order['title'] ); ?></a>
											<?php if ( ! empty( $bundle_id ) ) : ?>
											<span class="order-status"><?php echo esc_html__( 'bundle', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
											<?php endif; ?>
											<?php if ( ! empty( $enterprise_id ) ) : ?>
											<span class="order-status"><?php echo esc_html__( 'enterprise', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
											<?php endif; ?>
										</div>
										<div class="masterstudy-orders-course-info__category">
										<?php
										if ( ! empty( $order['terms'] ) ) {
											echo esc_html( implode( ' ', $order['terms'] ) );
										}

										if ( ! empty( $order['bundle_courses_count'] ) ) {
											echo esc_html( $order['bundle_courses_count'] . ' ' . $order_info['i18n']['bundle'] );
										}
										?>
										</div>
									</div>
									<div class="masterstudy-orders-course-info__price">
										<?php echo esc_html( $order['price_formatted'] ); ?>
									</div>
									<?php
										STM_LMS_Templates::show_lms_template(
											'components/button',
											array(
												'title' => esc_html__( 'Go to course', 'masterstudy-lms-learning-management-system-pro' ),
												'link'  => esc_url( $order['link'] ),
												'style' => 'secondary',
												'size'  => 'sm',
											)
										);
									?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="masterstudy-orders-table__footer">
							<div class="masterstudy-orders-course-info">
								<div class="masterstudy-orders-course-info__label"><?php echo esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ); ?>:</div>
								<div class="masterstudy-orders-course-info__price"><?php echo esc_html( $order_info['total'] ); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="masterstudy-orders-row">
				<div class="masterstudy-orders-column">
					<div class="masterstudy-orders-table masterstudy-orders-table__details">
						<div class="masterstudy-orders-table__header">
							<div class="masterstudy-orders-course-info"><?php echo esc_html__( 'Address', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
						</div>
						<div class="masterstudy-orders-table__body">
						<?php
							$order_fields = array(
								'Full name' => ! empty( $order_details['billing']['first_name'] ) || ! empty( $order_details['billing']['last_name'] )
									? $order_details['billing']['first_name'] . ' ' . $order_details['billing']['last_name']
									: '',
								'Address'   => ! empty( $order_details['billing']['address_1'] ) ? $order_details['billing']['address_1'] : '',
								'Country'   => ! empty( $order_details['billing']['country'] ) ? $order_details['billing']['country'] : '',
								'Email'     => ! empty( $order_details['billing']['email'] ) ? $order_details['billing']['email'] : '',
								'Phone'     => ! empty( $order_details['billing']['phone'] ) ? $order_details['billing']['phone'] : '',
							);

							foreach ( $order_fields as $label => $value ) :
								if ( empty( $value ) ) {
									continue;
								}
								?>
								<div class="masterstudy-orders-table__body-row">
									<div class="masterstudy-orders-course-info">
										<div class="masterstudy-orders-course-info__label">
											<?php echo sprintf( esc_html__( '%s:', 'masterstudy-lms-learning-management-system-pro' ), esc_html( $label ) ); ?>
										</div>
										<div class="masterstudy-orders-course-info__value">
											<?php echo esc_html( $value ); ?>
										</div>
									</div>
								</div>
								<?php
							endforeach;
							?>
						</div>
					</div>
				</div>
				<div class="masterstudy-orders-column">
					<div class="masterstudy-orders-table masterstudy-orders-table__details">
						<div class="masterstudy-orders-table__header">
							<div class="masterstudy-orders-course-info"><?php echo esc_html__( 'Total Billed', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
						</div>
						<?php
						$order_info_fields = array(
							'payment_code' => array(
								'label' => esc_html__( 'Payment method', 'masterstudy-lms-learning-management-system-pro' ),
								'value' => $order_details['payment_code'],
								'class' => 'masterstudy-payment-method',
							),
							'total'        => array(
								'label' => esc_html__( 'Total', 'masterstudy-lms-learning-management-system-pro' ),
								'value' => $order_info['total'],
								'class' => '',
							),
							'status'       => array(
								'label' => esc_html__( 'Status', 'masterstudy-lms-learning-management-system-pro' ),
								'value' => $order_info['status'],
								'class' => 'order-status ' . $order_info['status'],
							),
							'transaction'  => array(
								'label' => esc_html__( 'Transaction ID', 'masterstudy-lms-learning-management-system-pro' ),
								'value' => $order_details['billing']['transaction'],
								'class' => '',
							),
						);
						?>
						<div class="masterstudy-orders-table__body">
							<?php foreach ( $order_info_fields as $field ) : ?>
								<div class="masterstudy-orders-table__body-row">
									<div class="masterstudy-orders-course-info">
										<div class="masterstudy-orders-course-info__label">
											<?php echo esc_html( $field['label'] ); ?>:
										</div>
										<div class="masterstudy-orders-course-info__value <?php echo esc_attr( $field['class'] ); ?>">
											<?php echo esc_html( $field['value'] ); ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
