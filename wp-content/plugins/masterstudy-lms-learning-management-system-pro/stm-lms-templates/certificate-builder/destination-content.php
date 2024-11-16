<div class="masterstudy-certificate-destination-content">
	<div
		v-if="currentDestinationTab === 'default'"
		class="masterstudy-certificate-destination-default"
		:class="{'masterstudy-certificate-destination-default_empty': savedCertificates.length < 1 || defaultCertificate < 1}"
	>
		<div v-if="defaultCertificate?.id" class="masterstudy-certificate-destination-default__header">
			<span class="masterstudy-certificate-destination-default__header-title">
				<?php echo esc_html__( 'Default certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
			<span class="masterstudy-certificate-destination-default__header-description">
				<?php echo esc_html__( 'Your students will get this default certificate in all courses where you did not assign the specific certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
		</div>
		<div v-if="defaultCertificate?.id" class="masterstudy-certificate-destination-default__preview">
			<div class="masterstudy-certificate-destination-default__preview-header">
				<div class="masterstudy-certificate-destination-default__preview-title">
					<span class="masterstudy-certificate-destination-default__preview-title-name">
						{{defaultCertificate.title}}
					</span>
					<span class="masterstudy-certificate-destination-default__preview-title-id">
						<?php echo esc_html__( 'Certificate ID:', 'masterstudy-lms-learning-management-system-pro' ); ?>
						<span class="masterstudy-certificate-destination-default__preview-title-id-number">
							{{defaultCertificate.id}}
						</span>
					</span>
				</div>
				<div class="masterstudy-certificate-destination-default__preview-actions">
					<span @click="editInBuilder()" class="masterstudy-certificate-destination-default__preview-actions-editor">
						<?php echo esc_html__( 'Edit in the builder', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
					<a
						href="#"
						@click.prevent="openCertificatesPopup()"
						class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm"
					>
						<span class="masterstudy-button__title">
							<?php echo esc_html__( 'Change', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</a>
					<span @click="openDeletePopupDefault()" class="masterstudy-certificate-destination-default__delete"></span>
				</div>
			</div>
			<div v-if="defaultCertificate?.image" class="masterstudy-certificate-destination-default__preview-content">
				<img
					:src="defaultCertificate.image"
					class="masterstudy-certificate-destination-default__preview-image"
					:class="{'masterstudy-certificate-destination-default__preview-image_portrait': defaultCertificate.data.orientation === 'portrait'}"
				>
			</div>
		</div>
		<div v-else class="masterstudy-certificate-destination-default__empty">
			<div class="masterstudy-certificate-destination-default__empty-wrapper">
				<div class="masterstudy-certificate-destination-default__empty-icon">
					<img
						src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate.png' ); ?>"
						class="masterstudy-certificate-destination-default__empty-image"
					>
				</div>
				<span v-if="savedCertificates.length > 0" class="masterstudy-certificate-destination-default__empty-title">
					<?php echo esc_html__( 'The default certificate is not set', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-else class="masterstudy-certificate-destination-default__empty-title">
					<?php echo esc_html__( 'You have no certificates yet', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-if="savedCertificates.length > 0" class="masterstudy-certificate-destination-default__empty-description">
					<?php echo esc_html__( 'Choose the default certificate with the link below', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<a v-if="savedCertificates.length > 0"
					href="#"
					class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
					@click.prevent="openCertificatesPopup()"
				>
					<span class="masterstudy-button__title">
						<?php echo esc_html__( 'Сhoose certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
				<a v-else
					href="#"
					class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
					@click.prevent="changeCurrentTab('builder', true)"
				>
					<span class="masterstudy-button__title">
						<?php echo esc_html__( 'Create certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
				</a>
			</div>
		</div>
	</div>
	<div
		v-if="currentDestinationTab === 'categories'"
		class="masterstudy-certificate-destination-categories"
		:class="{'masterstudy-certificate-destination-categories_empty': filteredCertificates.length < 1}"
	>
		<?php STM_LMS_Templates::show_lms_template( 'certificate-builder/categories' ); ?>
	</div>
</div>
