<div class="masterstudy-certificate-destination-header">
	<div class="masterstudy-certificate-destination-header__wrapper">
		<div class="masterstudy-certificate-destination-header__content">
			<span class="masterstudy-certificate-destination-header__title">
				<?php echo esc_html__( 'Link Certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
			<span class="masterstudy-certificate-destination-header__description">
				<?php echo esc_html__( 'Here you can assign your certificates to certain categories and courses', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
			<div class="masterstudy-certificate-destination-header__tabs">
				<ul class="masterstudy-tabs masterstudy-tabs_style-nav-sm">
					<li
						class="masterstudy-tabs__item"
						:class="{'masterstudy-tabs__item_active': currentDestinationTab === 'default'}"
						@click="changeCurrentDestinationTab('default')"
					>
						<?php echo esc_html__( 'Default certificate', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</li>
					<li
						class="masterstudy-tabs__item"
						:class="{'masterstudy-tabs__item_active': currentDestinationTab === 'categories'}"
						@click="changeCurrentDestinationTab('categories')"
					>
						<?php echo esc_html__( 'Categories', 'masterstudy-lms-learning-management-system-pro' ); ?>
					</li>
					<li class="masterstudy-tabs__item">
						<a href="<?php echo esc_url( admin_url() . 'edit.php?post_type=stm-courses' ); ?>" class="masterstudy-tabs__item-link" target="_blank">
							<?php echo esc_html__( 'Courses', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</a>
						<div class="masterstudy-tabs__item-hint">
							<?php echo esc_html__( 'You can link certificates to courses within the Course Builder. The course catalog will open in a new tab.', 'masterstudy-lms-learning-management-system-pro' ); ?>
						</div>
					</li>
				</ul>
			</div>
		</div>
		<div class="masterstudy-certificate-destination-header__image-wrapper">
			<img src="<?php echo esc_url( STM_LMS_PRO_URL . 'assets/img/certificate-builder/certificate_destination.png' ); ?>" class="masterstudy-certificate-destination-header__image">
		</div>
	</div>
</div>
