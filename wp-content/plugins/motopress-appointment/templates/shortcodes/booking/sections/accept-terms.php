<?php

if ( ! empty( mpapp()->settings()->getTermsPageIdForAcceptance() ) ) {

	?>

	<section class="mpa-terms-and-conditions-accept mpa-checkout-section">
		<p class="mpa-input-wrapper">
			<label>
				<input 
					type="checkbox" 
					class="mpa-accept-terms" 
					id="mpa-accept-terms-<?php echo esc_attr( $html_id ); ?>" 
					name="mpa-accept-terms" 
					value="1" 
					required="required">

				<?php

					echo wp_kses_post(
						sprintf(
							// translators: %s is the Terms page link.
							__( 'I\'ve read and accept the <a class="mpa-terms-and-conditions-link" href="%s" target="_blank"> terms &amp; conditions</a>', 'motopress-appointment' ),
							esc_attr( get_page_link( mpapp()->settings()->getTermsPageIdForAcceptance() ) )
						)
					);
				?>
				<?php echo mpa_tmpl_required(); ?>
			</label>
		</p>
	</section>

<?php } ?>
