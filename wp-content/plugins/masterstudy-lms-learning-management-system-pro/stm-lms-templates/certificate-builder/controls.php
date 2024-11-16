<div class="masterstudy-certificate-controls" :class="{ 'masterstudy-certificate-controls_open': openControlsSidebar }">
	<div class="masterstudy-certificate-controls__toggler" @click="openRightSidebar">
		<span class="masterstudy-certificate-controls__toggler-icon">
			<span></span>
			<span></span>
			<span></span>
		</span>
		<span class="masterstudy-certificate-controls__toggler-text">
			<?php echo esc_html__( 'Elements & Backgrounds', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
	</div>
	<div class="masterstudy-certificate-controls__header">
		<ul class="masterstudy-tabs">
			<li class="masterstudy-tabs__item"
				:class="{'masterstudy-tabs__item_active': activeControlsTab === 'elements'}"
				@click="switchControlsTab('elements')"
			>
				<?php echo esc_html__( 'Elements', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</li>
			<li class="masterstudy-tabs__item"
				:class="{'masterstudy-tabs__item_active': activeControlsTab === 'backgrounds'}"
				@click="switchControlsTab('backgrounds')"
			>
				<?php echo esc_html__( 'Backgrounds', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</li>
		</ul>
	</div>
	<div class="masterstudy-certificate-controls__content">
		<transition name="fade">
			<div class="masterstudy-certificate-controls__content-elements" v-if="activeControlsTab === 'elements'">
				<div class="masterstudy-certificate-controls__category" v-for="category in fieldsCategories" :key="category">
					<span class="masterstudy-certificate-controls__category-title">{{ category }}</span>
					<div
						class="masterstudy-certificate-controls__item"
						:class="{'masterstudy-certificate-controls__item_disabled': fields[fieldKey]?.available && fields[fieldKey]?.available.length > 0}"
						v-for="(fieldKey, index) in Object.keys(fields)"
						:key="index"
						v-if="fields[fieldKey].category === category"
						@click="addField($event, fieldKey)"
					>
						<span
							class="masterstudy-certificate-controls__item-icon"
							:class="`masterstudy-certificate-controls__item-icon_${fieldKey}`"
						>
						</span>
						<span class="masterstudy-certificate-controls__item-title" v-html="fields[fieldKey].name"></span>
						<span v-if="fields[fieldKey]?.available && fields[fieldKey]?.available.length > 0" class="masterstudy-certificate-controls__item-available">
							{{ fields[fieldKey]?.available }}
						</span>
						<span class="masterstudy-certificate-controls__item-add">
							<?php echo esc_html__( 'Add', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</span>
					</div>
				</div>
			</div>
		</transition>
		<transition name="fade">
			<div class="masterstudy-certificate-controls__content-backgrounds" v-if="activeControlsTab === 'backgrounds'">
				<div class="masterstudy-certificate-controls__background-image">
					<span class="masterstudy-certificate-controls__background-image-title">
						<?php echo esc_html__( 'Background Image', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</span>
					<div class="masterstudy-certificate-controls__background-image-field">
						<div
							v-if="certificates[currentCertificate]?.thumbnail"
							class="masterstudy-certificate-controls__background-image-thumbnail"
						>
							<img
								class="masterstudy-certificate-controls__background-image-preview"
								:class="{'masterstudy-certificate-controls__background-image-preview_portrait': certificates[currentCertificate]?.data?.orientation === 'portrait'}"
								:src="certificates[currentCertificate].thumbnail"
							/>
							<span class="masterstudy-certificate-controls__background-image-delete" @click="openImagePopup()"></span>
						</div>
						<div v-else class="masterstudy-certificate-controls__background-image-choose">
							<span v-if="isAdmin" class="masterstudy-certificate-controls__background-image-add" @click="uploadImage()">
								<?php echo esc_html__( 'Select Image', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
							<label v-if="!isAdmin" for="background_image" class="masterstudy-certificate-controls__background-image-add">
								<?php echo esc_html__( 'Select Image', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</label>
							<input v-if="!isAdmin" type="file" id="background_image" name="background_image" @change="handleFileUpload($event, true)" class="masterstudy-certificate-controls__background-image-input">
							<span v-if="certificates[currentCertificate]?.data?.orientation === 'portrait'" class="masterstudy-certificate-controls__background-image-text">
								<?php echo esc_html__( 'We recommend use images with 1050 x 1600 px size or higher', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
							<span v-else class="masterstudy-certificate-controls__background-image-text">
								<?php echo esc_html__( 'We recommend use images with 1600 x 1050 px size or higher', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
							<span v-if="notImageError" class="masterstudy-certificate-controls__background-image-error">
								<?php echo esc_html__( 'Only images allowed', 'masterstudy-lms-learning-management-system-pro' ); ?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</div>
</div>
