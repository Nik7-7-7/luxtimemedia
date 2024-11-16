<div
	class="masterstudy-certificate-delete-popup"
	:class="{'masterstudy-certificate-delete-popup_open': deletePopupVisible}"
	@click="deletePopupWrapperClick($event)"
>
	<div class="masterstudy-certificate-delete-popup__wrapper">
		<div class="masterstudy-certificate-delete-popup__container">
			<div class="masterstudy-certificate-delete-popup__header">
				<span v-if="deleteAlertType == 'category'" class="masterstudy-certificate-delete-popup__header-title">
					<?php echo esc_html__( 'Are you sure you want to unlink this certificate?', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span  v-else class="masterstudy-certificate-delete-popup__header-title">
					<?php echo esc_html__( 'Are you sure you want to delete this certificate?', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
			</div>
			<div class="masterstudy-certificate-delete-popup__close" @click="closeDeletePopup()"></div>
			<div class="masterstudy-certificate-delete-popup__content">
				<span v-if="deleteAlertType == 'category'" class="masterstudy-certificate-delete-popup__content-title">
					<?php echo esc_html__( 'After unlinking this certificate, the course/category will not have a certificate and students will get the default certificate.', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-if="deleteAlertType == 'default'" class="masterstudy-certificate-delete-popup__content-title">
					<?php echo esc_html__( 'After deleting it, students will not get any certificate for courses and categories where the default certificate is assigned.', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-if="deleteAlertType == 'certificate'" class="masterstudy-certificate-delete-popup__content-title">
					<?php echo esc_html__( 'After deleting, all courses and categories with this certificate will not have a certificate.', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<div class="masterstudy-certificate-delete-popup__actions">
					<a
						href="#"
						@click.prevent="closeDeletePopup()"
						class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm"
					>
						<span class="masterstudy-button__title">
							<?php echo esc_html__( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</a>
					<a
						href="#"
						@click.prevent="deleteAlertType == 'category' ? unlinkCategory($event, certificateForDelete, categoryForDelete) : deleteAlertType == 'default' ? deleteDefaultCertificate($event) : deleteCertificate($event, certificateForDelete)"
						class="masterstudy-button masterstudy-button_style-danger masterstudy-button_size-sm"
					>
						<span v-if="deleteAlertType == 'category'" class="masterstudy-button__title">
							<?php echo esc_html__( 'Unlink certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
						<span v-else class="masterstudy-button__title">
							<?php echo esc_html__( 'Delete certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
