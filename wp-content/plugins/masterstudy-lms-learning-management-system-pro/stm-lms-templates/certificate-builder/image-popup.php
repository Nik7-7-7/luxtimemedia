<div
	class="masterstudy-certificate-image-popup"
	:class="{'masterstudy-certificate-image-popup_open': imagePopupVisible}"
	@click="imagePopupWrapperClick($event)"
>
	<div class="masterstudy-certificate-image-popup__wrapper">
		<div class="masterstudy-certificate-image-popup__container">
			<div class="masterstudy-certificate-image-popup__header">
				<span class="masterstudy-certificate-image-popup__header-title">
					<?php echo esc_html__( 'Are you sure you want to delete a background?', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
			</div>
			<div class="masterstudy-certificate-image-popup__close" @click="closeImagePopup()"></div>
			<div class="masterstudy-certificate-image-popup__content">
				<span class="masterstudy-certificate-image-popup__content-title">
					<?php echo esc_html__( 'After deleting, this certificate will not have a background and you can add another one.', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<div class="masterstudy-certificate-image-popup__actions">
					<a
						href="#"
						@click.prevent="closeImagePopup()"
						class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm"
					>
						<span class="masterstudy-button__title">
							<?php echo esc_html__( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</a>
					<a
						href="#"
						@click.prevent="deleteImage($event)"
						class="masterstudy-button masterstudy-button_style-danger masterstudy-button_size-sm"
					>
						<span class="masterstudy-button__title">
							<?php echo esc_html__( 'Delete image', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
