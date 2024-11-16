<?php

namespace MotoPress\Appointment\AdminPages\Traits;

/**
 * @since 1.2
 */
trait ShortcodeTitleActions {

	/**
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function addTitleActions() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$shortcodesToAdd = mpa_shortcodes()->getPostShortcodes();

		// [Label => URL to Add New Shortcode page]
		$shortcodesList = array();

		foreach ( $shortcodesToAdd as $shortcode ) {
			$shortcodesList[ $shortcode->getLabel() ] = add_query_arg(
				array(
					'post_type' => mpa_shortcode()->getPostType(),
					'shortcode' => $shortcode->getName(),
				),
				admin_url( 'post-new.php' )
			);
		}

		// Render dropdown
		$dropdown = mpa_tmpl_dropdown( esc_html__( 'Add New', 'motopress-appointment' ), $shortcodesList, array( 'inline' => true ) );

		?>
		<script type="text/javascript">
			jQuery(function () {
				let dropdownHtml = '<?php echo addslashes( mpa_strip_html_whitespaces( $dropdown ) ); ?>';
				let $titleAction = jQuery('#wpbody-content > .wrap > .wp-heading-inline + .page-title-action');

				// Add dropdown
				if ($titleAction.length > 0) {
					$titleAction.replaceWith(dropdownHtml);

					// Init dropdown
					try {
						window.MotoPress.Appointment.Bootstrap.setupDropdowns();
					} catch (error) {}
				}
			});
		</script>
		<?php
	}
}
