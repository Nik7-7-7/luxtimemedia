<div
	v-if="currentTab === 'builder'"
	class="masterstudy-certificate-create-popup"
	:class="{'masterstudy-certificate-create-popup_open': createPopupVisible}"
	@click="createPopupWrapperClick($event)"
>
	<div class="masterstudy-certificate-create-popup__wrapper">
		<div class="masterstudy-certificate-create-popup__container">
			<div class="masterstudy-certificate-create-popup__header">
				<span class="masterstudy-certificate-create-popup__header-title">
					<?php echo esc_html__( 'Create a new certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
			</div>
			<div class="masterstudy-certificate-create-popup__close" @click="closeCreatePopup()"></div>
			<div class="masterstudy-certificate-create-popup__content">
				<span class="masterstudy-certificate-create-popup__content-title">
					<?php echo esc_html__( 'Choose the layout for the certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>:
				</span>
				<div class="masterstudy-certificate-create-popup__layout">
					<div class="masterstudy-certificate-create-popup__layout-type" @click="addCertificate('portrait')">
						<span class="masterstudy-certificate-create-popup__layout-icon"></span>
						<span class="masterstudy-certificate-create-popup__layout-title">
							<?php echo esc_html__( 'Vertical', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</div>
					<div class="masterstudy-certificate-create-popup__layout-type" @click="addCertificate('landscape')">
						<span class="masterstudy-certificate-create-popup__layout-icon"></span>
						<span class="masterstudy-certificate-create-popup__layout-title">
							<?php echo esc_html__( 'Horizontal', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
