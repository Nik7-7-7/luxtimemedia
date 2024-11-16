<div
	v-if="currentTab === 'destination'"
	class="masterstudy-certificate-select-popup"
	:class="categoriesPopupVisible || certificatesPopupVisible ? 'masterstudy-certificate-select-popup_open' : ''"
	@click="categoriesPopupVisible ? categoriesPopupWrapperClick($event) : certificatesPopupWrapperClick($event)"
>
	<div class="masterstudy-certificate-select-popup__wrapper">
		<div class="masterstudy-certificate-select-popup__container">
			<div class="masterstudy-certificate-select-popup__header">
				<span class="masterstudy-certificate-select-popup__header-title">
					<?php echo esc_html__( 'Choose a template', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
			</div>
			<div class="masterstudy-certificate-select-popup__close" @click="categoriesPopupVisible ? closeCategoriesPopup() : closeCertificatesPopup()"></div>
			<div class="masterstudy-certificate-select-popup__content">
				<span v-if="savedCertificates.length > 0" class="masterstudy-certificate-select-popup__content-title">
					<?php echo esc_html__( 'Select one of the options to continue', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<div v-if="savedCertificates.length > 0" class="masterstudy-certificate-select-popup__list">
					<div
						v-for="(certificate, key) in savedCertificates"
						class="masterstudy-certificate-select-popup__list-item"
						:class="{'masterstudy-certificate-select-popup__list-item_active': categoriesPopupVisible ? certificate?.id === newCategoryCertificate?.id : certificate?.id === newDefaultCertificate?.id}"
						:data-id="certificate?.id"
					>
						<div
							class="masterstudy-certificate-select-popup__list-item-wrapper"
							@click="categoriesPopupVisible ? checkCertificateCategory(certificate) : checkDefaultCertificate(certificate)"
						>
							<img
								v-if="certificate?.image"
								:src="certificate.image"
								class="masterstudy-certificate-select-popup__list-item-image"
								:class="{'masterstudy-certificate-select-popup__list-item-image_portrait': certificate.data.orientation === 'portrait'}"
							/>
							<span
								v-if="categoriesPopupVisible ? certificate?.id === newCategoryCertificate?.id : certificate?.id === newDefaultCertificate?.id"
								class="masterstudy-certificate-select-popup__list-item-checked">
							</span>
						</div>
					</div>
				</div>
				<div v-else class="masterstudy-certificate-select-popup__empty">
					<div class="masterstudy-certificate-select-popup__empty-wrapper">
						<div class="masterstudy-certificate-select-popup__empty-icon">
							<img
								src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate.png' ); ?>"
								class="masterstudy-certificate-select-popup__empty-image"
							>
						</div>
						<span class="masterstudy-certificate-select-popup__empty-title">
							<?php echo esc_html__( 'You do not have any available certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
						<span class="masterstudy-certificate-select-popup__empty-description">
							<?php echo esc_html__( 'Create a certificate or transfer from another category', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
						<a
							href="#"
							class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
							@click.prevent="closeCategoriesPopupWithRedirect()"
						>
							<span class="masterstudy-button__title">
								<?php echo esc_html__( 'Create Certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
						</a>
					</div>
				</div>
			</div>
			<div
				class="masterstudy-certificate-select-popup__actions"
				:class="{'masterstudy-certificate-select-popup__actions_scroll': savedCertificates.length > 20}"
			>
				<a
					href="#"
					@click.prevent="categoriesPopupVisible ? closeCategoriesPopup() : closeCertificatesPopup()"
					class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm"
				>
					<span class="masterstudy-button__title">
						<?php echo esc_html__( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
				<a
					v-if="savedCertificates.length > 0"
					href="#"
					@click.prevent="categoriesPopupVisible ? saveCertificateCategory($event) : saveDefaultCertificate($event)"
					class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
					:class="{
						'masterstudy-button_loading': loadingSaveButton,
						'masterstudy-button_disabled': categoriesPopupVisible ? !newCategoryCertificate : !newDefaultCertificate,
						'masterstudy-button_disabled': categoriesPopupVisible ? newCategoryCertificate?.id === categoryCertificate?.id : !newDefaultCertificate
					}"
				>
					<span class="masterstudy-button__title">
						<?php echo esc_html__( 'Save', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
			</div>
		</div>
	</div>
</div>
