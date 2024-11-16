<div class="mailster-setup-step-body">

	<form class="mailster-setup-step-form">

	<p><?php esc_html_e( 'Mailster offers a variety of templates. Please select the design that best matches how you want your email newsletter to appear.', 'mailster' ); ?></p>
	<p><?php esc_html_e( 'The previews below should provide you with an idea of how your campaign will appear. You have the flexibility to make content adjustments and modify the visual aesthetics at any time.', 'mailster' ); ?></p>

	<div class="templates loading">
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
		<div class="template"><div class="mailster-template-preview"></div></div>
	</div>

	<p><?php esc_html_e( 'You can download other available templates later in the templates section.', 'mailster' ); ?></p>


	<input type="hidden" name="mailster_options[default_template]" value="<?php echo esc_attr( mailster_option( 'default_template' ) ); ?>" id="default_template">
	
	</form>

</div>


