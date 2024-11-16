<div v-if="savedCertificates.length > 0" class="masterstudy-certificate-destination-categories__header">
	<div class="masterstudy-certificate-destination-categories__header-wrapper">
		<span class="masterstudy-certificate-destination-categories__header-title">
			<?php echo esc_html__( 'Certificates for categories', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
		<span v-if="categoriesWithCertificates.length > 0" class="masterstudy-certificate-destination-categories__header-subtitle">
			{{ categoriesWithCertificates.length }}
			<?php echo esc_html__( 'from', 'masterstudy-lms-learning-management-system-pro' ); ?>
			{{ totalCategories }}
		</span>
	</div>
	<div class="masterstudy-certificate-destination-categories__search">
		<input
			type="text"
			placeholder="<?php echo esc_attr__( 'Search for categories', 'masterstudy-lms-learning-management-system-pro' ); ?>"
			class="masterstudy-certificate-destination-categories__search-input"
			v-model="searchCategories"
			@keyup.enter="getCategories(1,true)"
		>
		<span
			v-if="searchCategoriesActive && searchCategories.length > 0"
			class="masterstudy-certificate-destination-categories__search-reset"
			@click="resetCategoriesSearch()">
		</span>
		<span class="masterstudy-certificate-destination-categories__search-button" @click="getCategories(1,true)"></span>
	</div>
</div>
<div v-if="savedCertificates.length > 0" class="masterstudy-certificate-destination-categories__list">
	<div v-if="loadingCategoriesPage" class="masterstudy-certificate-destination-categories__loader">
		<div class="masterstudy-certificate-destination-categories__loader-body"></div>
	</div>
	<div
		v-if="categoriesWithCertificates.length > 0"
		v-for="(categoryWithCertificate, key) in categoriesWithCertificates"
		:key="key"
		class="masterstudy-certificate-destination-categories__item"
		:data-id="categoryWithCertificate.id"
	>
		<div class="masterstudy-certificate-destination-categories__item-image">
			<template v-if="categoryWithCertificate.certificate?.image">
				<img
					:src="categoryWithCertificate.certificate.image"
					:class="{'masterstudy-certificate-destination-categories__item-image_portrait': categoryWithCertificate.certificate?.data.orientation === 'portrait'}"
				/>
			</template>
			<template v-else>
				<img
					v-if="defaultCertificate?.image"
					:src="defaultCertificate.image"
					:class="{'masterstudy-certificate-destination-categories__item-image_portrait': defaultCertificate?.data.orientation === 'portrait'}"
				/>
				<img v-else src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate-empty.svg' ); ?>"/>
			</template>
		</div>
		<div class="masterstudy-certificate-destination-categories__item-content">
			<span class="masterstudy-certificate-destination-categories__item-title">
				{{ categoryWithCertificate.name }}
			</span>
			<div v-if="categoryWithCertificate.certificate || defaultCertificate" class="masterstudy-certificate-destination-categories__assign">
				<span v-if="categoryWithCertificate.certificate" class="masterstudy-certificate-destination-categories__assign-name">
					{{ categoryWithCertificate.certificate.title }}
				</span>
				<span v-else class="masterstudy-certificate-destination-categories__assign-name">
					<?php echo esc_html__( 'Default certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span class="masterstudy-certificate-destination-categories__assign-ceparator">
					•
				</span>
				<span class="masterstudy-certificate-destination-categories__assign-title">
					<?php echo esc_html__( 'Certificate ID:', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-if="categoryWithCertificate.certificate" class="masterstudy-certificate-destination-categories__assign-id">
					{{ categoryWithCertificate.certificate.id }}
				</span>
				<span v-else class="masterstudy-certificate-destination-categories__assign-id">
					{{ defaultCertificate.id }}
				</span>
			</div>
			<div v-else class="masterstudy-certificate-destination-categories__assign">
				<?php echo esc_html__( 'Certificate not assigned', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</div>
		</div>
		<a
			v-if="categoryWithCertificate.certificate"
			href="#"
			class="masterstudy-button masterstudy-button_style-tertiary masterstudy-button_size-sm masterstudy-unlink-certificate"
			@click.prevent="openDeletePopupCategory(categoryWithCertificate.certificate, categoryWithCertificate.id)"
		>
			<span class="masterstudy-button__title">
				<?php echo esc_html__( 'Unlink', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
		</a>
		<a
			href="#"
			class="masterstudy-button masterstudy-button_style-primary masterstudy-button_size-sm"
			@click.prevent="openCategoriesPopup(categoryWithCertificate.certificate, categoryWithCertificate.id)"
		>
			<span v-if="categoryWithCertificate.certificate" class="masterstudy-button__title">
				<?php echo esc_html__( 'Change', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
			<span v-else class="masterstudy-button__title">
				<?php echo esc_html__( 'Set certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
		</a>
	</div>
	<div v-if="categoriesWithCertificates.length < 1 && !loadingCategoriesPage" class="masterstudy-certificate-destination-categories__no-found">
		<span class="masterstudy-certificate-destination-categories__no-found-icon">
			<img src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/no-found.png' ); ?>" />
		</span>
		<span class="masterstudy-certificate-destination-categories__no-found-title">
			<?php echo esc_html__( 'No search results', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
		<span class="masterstudy-certificate-destination-categories__no-found-subtitle">
			<?php echo esc_html__( 'Try changing your search parameters', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
	</div>
</div>
<div v-else class="masterstudy-certificate-destination-categories__empty">
	<div class="masterstudy-certificate-destination-categories__empty-wrapper">
		<div class="masterstudy-certificate-destination-categories__empty-icon">
			<img
				src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate.png' ); ?>"
				class="masterstudy-certificate-destination-categories__empty-image"
			>
		</div>
		<span class="masterstudy-certificate-destination-categories__empty-title">
			<?php echo esc_html__( 'You have not linked any certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
		<a
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
<div v-if="savedCertificates.length > 0" class="masterstudy-certificate-destination-categories__pagination">
	<div v-if="categoriesPagesToShow.length > 1" class="masterstudy-pagination">
		<span
			class="masterstudy-pagination__button-prev"
			:class="{'masterstudy-pagination__button_disabled': !categoriesShowPrev}"
			@click="prevCategoriesPage($event)">
		</span>
		<div class="masterstudy-pagination__wrapper">
			<ul class="masterstudy-pagination__list">
				<li
					v-for="index in categoriesPagesToShow"
					class="masterstudy-pagination__item"
					:class="{'masterstudy-pagination__item_current': currentCategoriesPage === index}"
					:key="index"
				>
					<span class="masterstudy-pagination__item-block" :data-id="index" @click="changeCategoriesPage(index)">
						{{ index }}
					</span>
				</li>
			</ul>
		</div>
		<span
			class="masterstudy-pagination__button-next"
			:class="{'masterstudy-pagination__button_disabled': !categoriesShowNext}"
			@click="nextCategoriesPage($event)">
		</span>
	</div>
</div>
