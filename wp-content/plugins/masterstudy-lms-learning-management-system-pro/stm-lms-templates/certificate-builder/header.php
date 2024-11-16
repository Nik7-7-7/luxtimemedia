<div class="masterstudy-certificate-header">
	<div class="masterstudy-certificate-header__back">
		<?php
		STM_LMS_Templates::show_lms_template(
			'components/back-link',
			array(
				'id'  => 'masterstudy-certificate-back',
				'url' => ms_plugin_user_account_url( 'certificates' ),
			)
		);
		?>
	</div>
	<span class="masterstudy-certificate-header__title">
		<?php esc_html_e( 'Certificate Builder', 'masterstudy-lms-learning-management-system-pro' ); ?>
	</span>
	<div v-if="isAdmin" class="masterstudy-certificate-header__navigation">
		<ul class="masterstudy-tabs masterstudy-tabs_style-nav-sm">
			<li
				class="masterstudy-tabs__item"
				:class="{'masterstudy-tabs__item_active': currentTab === 'builder'}"
				@click="changeCurrentTab('builder')"
			>
				<?php echo esc_html__( 'Certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</li>
			<li
				class="masterstudy-tabs__item"
				:class="{'masterstudy-tabs__item_active': currentTab === 'destination'}"
				@click="changeCurrentTab('destination')"
			>
				<?php echo esc_html__( 'Link Certificates', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</li>
		</ul>
	</div>
</div>
