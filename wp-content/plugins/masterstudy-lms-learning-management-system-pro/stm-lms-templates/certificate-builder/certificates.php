<div class="masterstudy-certificate-templates">
	<div class="masterstudy-certificate-templates__header">
		<div class="masterstudy-certificate-templates__header-title">
			<?php esc_html_e( 'Certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
		<div class="masterstudy-certificate-templates__header-quantity">
			{{ Object.keys(certificatesList).length }}
		</div>
		<span class="masterstudy-certificate-templates__header-add" @click="openCreatePopup()"></span>
	</div>
	<div class="masterstudy-certificate-templates__search">
		<input
			type="text"
			class="masterstudy-certificate-templates__search-input"
			v-model="certificateSearchQuery"
			placeholder="<?php echo esc_attr__( 'Search certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>"
		>
		<span
			v-if="certificateSearchQuery.length > 0"
			class="masterstudy-certificate-templates__search-reset"
			@click="resetCertificatesSearch()">
		</span>
		<span v-else class="masterstudy-certificate-templates__search-button"></span>
	</div>
	<div v-if="isAdmin && uniqueInstructors.length > 1" class="masterstudy-certificate-templates__select" @click="toggleSelectDropdown">
		<div
			v-if="selectedInstructorName.length > 0"
			class="masterstudy-certificate-templates__select-selected"
			:class="{'masterstudy-certificate-templates__select-selected_active': selectDropdownOpen}"
		>
			{{ selectedInstructorName }}
		</div>
		<div
			v-else
			class="masterstudy-certificate-templates__select-selected"
			:class="{'masterstudy-certificate-templates__select-selected_active': selectDropdownOpen}"
		>
			<?php echo esc_html__( 'All instructors', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
		<div class="masterstudy-certificate-templates__select-items" v-show="selectDropdownOpen">
			<div
				class="masterstudy-certificate-templates__select-item"
				:class="{'masterstudy-certificate-templates__select-item_active': !selectedInstructorId}"
				@click.stop="selectInstructor(null)"
			>
				<?php echo esc_html__( 'All instructors', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</div>
			<div
				v-for="instructor in uniqueInstructors"
				:key="instructor.id"
				class="masterstudy-certificate-templates__select-item"
				:class="{'masterstudy-certificate-templates__select-item_active': instructor.id === selectedInstructorId}"
				@click.stop="selectInstructor(instructor)"
			>
				{{ instructor.name }}
			</div>
		</div>
	</div>
	<div
		class="masterstudy-certificate-templates__content"
		:class="{'masterstudy-certificate-templates__content_admin': isAdmin && uniqueInstructors.length > 1}"
	>
		<label
			v-if="certificatesList.length > 0"
			v-for="(certificate, key) in certificatesList"
			class="masterstudy-certificate-templates__item"
			:class="{'masterstudy-certificate-templates__item_active': currentCertificate === key}"
			:data-id="certificate.id"
		>
			<span class="masterstudy-certificate-templates__item-delete" @click="openDeletePopupCertificate(key)"></span>
			<div
				class="masterstudy-certificate-templates__item-image"
				:class="{'masterstudy-certificate-templates__item-image_portrait': certificate.data.orientation === 'portrait'}"
			>
				<img v-if="certificate?.image" :src="certificate.image"/>
			</div>
			<div class="masterstudy-certificate-templates__item-content">
				<span class="masterstudy-certificate-templates__item-title">{{certificate.title}}</span>
				<span class="masterstudy-certificate-templates__item-id">{{certificate.id}}</span>
			</div>
			<input type="radio" v-model="currentCertificate" :value="key" class="masterstudy-certificate-templates__item-input"/>
		</label>
		<span v-if="certificatesList.length < 1" class="masterstudy-certificate-templates__no-found">
			<?php echo esc_html__( 'No results for this criteria', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
	</div>
</div>
