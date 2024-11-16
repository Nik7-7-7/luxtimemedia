<?php

namespace MotoPress\Appointment\AdminPages\Custom;

use MotoPress\Appointment\Helpers\AdminUIHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SettingsPage extends AbstractCustomPage {


	/**
	 * Don't access directly. This is a cache field. Use getCurrentTab()
	 * instead.
	 *
	 * @var string|null
	 *
	 * @see SettingsPage::getCurrentTab()
	 *
	 * @since 1.0
	 */
	protected $currentTab = null;

	/**
	 * Don't access directly. This is a cache field. Use getCurrentSection()
	 * instead.
	 *
	 * @var string|null
	 *
	 * @see SettingsPage::getCurrentSection()
	 *
	 * @since 1.1.0
	 */
	protected $currentSection = null;

	/**
	 * @var \MotoPress\Appointment\Fields\AbstractField[] [Option name => Field object]
	 *
	 * @since 1.0
	 */
	protected $fields = array();

	/**
	 * @var bool
	 *
	 * @since 1.0
	 */
	protected $saved = false;

	/**
	 * @access protected
	 *
	 * @since 1.1.0
	 */
	public function enqueueScripts() {

		if ( in_array( $this->getCurrentTab(), array( 'email', 'misc' ) )  ) {

			mpapp()->assets()->enqueueBundle( 'spectrum' ); // For colorpicker
		}

		mpapp()->assets()->enqueueBundle( 'mpa-settings-page' );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function load() {

		$currentTab     = $this->getCurrentTab();
		$currentSection = $this->getCurrentSection();

		$fields = array();

		// TODO: refactor this after settings bundle deletion
		if ( empty( $currentSection ) ||
			( 'integrations' === $currentTab && 'google_cal_sync' === $currentSection ) ||
			'misc' === $currentTab
		) {
			$fields = mpapp()->bundles()->settings()->getSettings( $currentTab, $currentSection );

		} else {

			$fields = apply_filters( "mpa_{$currentTab}_section_settings", array(), $currentSection );
		}

		$this->fields += mpa_create_fields( $fields );

		// Maybe save changes
		$this->maybeSave();
	}

	/**
	 * @since 1.0
	 */
	protected function maybeSave() {

		if ( ! $this->canSave() ) {
			return;
		}

		try {
			$newValues = $this->parseRequest();

			foreach ( $newValues as $optionName => $optionValue ) {
				update_option( $optionName, $optionValue, 'yes' );
			}

			$this->saved = true;

			do_action( 'mpa_settings_saved', $this->getCurrentTab(), $this->getCurrentSection(), $newValues );

		} catch ( \Throwable $e ) {

			AdminUIHelper::addAdminNotice(
				AdminUIHelper::ADMIN_NOTICE_TYPE_ERROR,
				$e->getMessage()
			);
		}
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected function canSave() {

		if ( ! isset( $_POST['mpa_save_settings'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$currentTab = $this->getCurrentTab();

		$nonceAction = "mpa_save_{$currentTab}_settings";
		$nonceField  = "_mpa_{$currentTab}_settings_nonce";

		if ( ! mpa_verify_nonce( $nonceAction, $nonceField ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return array Values to save: [Option name => New value].
	 *
	 * @since 1.0
	 */
	protected function parseRequest() {

		$values = array();

		foreach ( $this->fields as $optionName => $field ) {

			if ( ! isset( $_POST[ $optionName ] ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$field->setValue( wp_unslash( $_POST[ $optionName ] ), 'validate' );
			$newValue = $field->getValue( 'save' );

			// Save all values, not just the new ones
			$values[ $optionName ] = $newValue;
		}

		return $values;
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function display() {

		echo '<div class="wrap">';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderTabs();

		if ( $this->saved ) {

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo mpa_tmpl_notice( 'success', esc_html__( 'Settings saved.', 'motopress-appointment' ) );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderForm();

		echo '</div>';
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	private function renderTabs() {

		$tabs = mpapp()->bundles()->settings()->getSettingsTabs();

		$currentTab     = $this->getCurrentTab();
		$currentSection = $this->getCurrentSection();

		$output = '<h1 class="nav-tab-wrapper">';

		foreach ( $tabs as $tab => $label ) {

			$tabUrl = $this->getUrl( array( 'tab' => $tab ) );

			if ( $tab !== $currentTab ) {

				$output .= mpa_tmpl_link( $tabUrl, $label, array( 'class' => 'nav-tab' ) );

			} elseif ( ! empty( $currentSection ) ) {

				// Add link to the "parent" settings
				$output .= mpa_tmpl_link( $tabUrl, $label, array( 'class' => 'nav-tab nav-tab-active' ) );

			} else {

				$output .= '<span class="nav-tab nav-tab-active">' . esc_html( $label ) . '</span>';
			}
		}

		$output .= '</h1>';

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	private function renderForm() {

		$output = '';

		$currentTabSlug = $this->getCurrentTab();
		$currentTabSections = $this->getTabSections( $currentTabSlug );

		$output      = '<form action="' . esc_url( $this->getUrl() ) . '" method="POST">';
			$output .= wp_nonce_field( "mpa_save_{$currentTabSlug}_settings", "_mpa_{$currentTabSlug}_settings_nonce", true, false );

			$output .= mpa_render_template( 'private/fields/form-table.php', array( 'fields' => $this->fields ) );

			$output         .= '<div class="mpa-settings-tab-actions">';
				$output     .= '<p class="submit">';
					$output .= '<input type="submit" name="mpa_save_settings" class="button button-primary" value="' . esc_html__( 'Save Changes', 'motopress-appointment' ) . '">';
				$output     .= '</p>';
			$output         .= '</div>';
		$output             .= '</form>';

		if ( $currentTabSections ) {

			$currentTabSections = apply_filters(
				"mpa_{$currentTabSlug}_settings_tab_sections",
				$currentTabSections
			);

			$currentSection = $this->getCurrentSection();
			$currentSection = empty( $currentSection ) ? array_key_first( $currentTabSections ) : $currentSection;

			$currentSectionsOutput = '';

			foreach ( $currentTabSections as $sectionSlug => $sectionLabel ) {

				$sectionUrl = $this->getUrl(
					array(
						'tab'     => $currentTabSlug,
						'section' => $sectionSlug,
					)
				);

				if ( $currentSection === $sectionSlug ) {

					$currentSectionsOutput .= '<h4 class="mpa-settings__tab-section mpa-settings__tab-section--active">' .
						esc_html( $sectionLabel ) . '</h4>';

				} else {

					$currentSectionsOutput .= mpa_tmpl_link( $sectionUrl, $sectionLabel, array( 'class' => 'mpa-settings__tab-section' ) );

				}
			}

			$output = '<div class="mpa-settings__tab-content">
					<div class="mpa-settings__tab-sections">' . $currentSectionsOutput . '</div>
					<div>' . $output . '</div>
				</div>';
		}

		return $output;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getCurrentTab() {

		if ( is_null( $this->currentTab ) ) {

			if ( isset( $_GET['tab'] ) ) {

				$currentTab = wp_strip_all_tags( wp_unslash( $_GET['tab'] ) );

			} else {

				$currentTab = 'general';
			}

			$tabs = mpapp()->bundles()->settings()->getSettingsTabs();

			if ( ! array_key_exists( $currentTab, $tabs ) ) {

				$currentTab = mpa_first_key( $tabs );
			}

			$this->currentTab = $currentTab;
		}

		return $this->currentTab;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getCurrentSection() {

		if ( is_null( $this->currentSection ) ) {

			// phpcs:ignore
			if ( isset( $_GET['section'] ) ) {

				// phpcs:ignore
				$currentSection = wp_strip_all_tags( wp_unslash( $_GET['section'] ) );

			} else {

				$currentSection = '';

				if ( 'integrations' === $this->getCurrentTab() ) {

					$currentSection = '';
				}
			}

			$this->currentSection = $currentSection;
		}

		return $this->currentSection;
	}

	/**
	 * 	Return array of subtabs(sections) for provided tab slug.
	 *
	 * @param string $tabSlug
	 * @return array
	 */
	public function getTabSections( $tabSlug ) {
		$sections = array();
		$tabSections = mpapp()->bundles()->settings()->getTabsSections();

		if ( array_key_exists( $tabSlug, $tabSections ) ) {
			return $tabSections[ $tabSlug ];
		}

		return $sections;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getPageTitle() {
		return esc_html__( 'Settings', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function getMenuTitle() {
		return esc_html__( 'Settings', 'motopress-appointment' );
	}

	/**
	 * @param array $additionalArgs Optional.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getUrl( $additionalArgs = array() ) {

		$moreArgs = array();

		// Add tab and section to URL
		if ( $this->isCurrentPage() ) {

			$currentTab = $this->getCurrentTab(); // Not empty ('')

			if ( 'general' !== $currentTab ) {

				$moreArgs['tab'] = $currentTab;

				// Add section to URL
				$currentSection = $this->getCurrentSection();

				// But don't add section to the URL if making an URL of random tab
				if ( ! empty( $currentSection ) && ! isset( $additionalArgs['tab'] ) ) {

					$moreArgs['section'] = $currentSection;
				}
			}
		}

		return parent::getUrl( array_merge( $moreArgs, $additionalArgs ) );
	}
}
