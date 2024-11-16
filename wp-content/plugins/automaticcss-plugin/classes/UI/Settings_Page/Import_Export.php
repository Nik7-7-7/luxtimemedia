<?php
/**
 * Automatic.css Import & Export file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Import & Export class.
 */
class Import_Export {

	use Singleton;

	/**
	 * Initialize the Import_Export class.
	 *
	 * @return void
	 */
	public function init() {

	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public static function settings_page() {
		$model = Database_Settings::get_instance();
		$settings = $model->get_vars();
		$defaults = ( new UI() )->get_default_settings();
		$nonce = wp_create_nonce( 'automatic_css_save_settings' );
		$ajax_url = admin_url( 'admin-ajax.php' );
		?>
			<div class="wrap">
				<h2><?php esc_html_e( 'Import & Export Options' ); ?></h2>

				<div id="acss-settings__message-container"></div>

				<form method="post" action="#" id="automatic-css-import-export-form" name="automatic-css-import-export-form">
					<textarea name="automatic-css-import-export-settings" id="automatic-css-import-export-settings"><?php echo json_encode( $settings ); ?></textarea>
					<textarea name="automatic-css-defaults-settings" id="automatic-css-defaults-settings" style="display: none"><?php echo json_encode( $defaults ); ?></textarea>
					<div>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Framework Settings">
						<button type="button" class="button button-secondary" id="automatic-css-set-defaults">Restore default values</button>
					</div>
				</form>

				<script>
					// Import & Export.
					let settings_form = document.querySelector('#automatic-css-import-export-form');
					let settings_json = document.querySelector('#automatic-css-import-export-settings');
					let message_container = document.querySelector("#acss-settings__message-container");
					settings_form.addEventListener('submit', (event) => {
						event.preventDefault();
						let data = new FormData();
						data.append("action", "automaticcss_save_settings_new");
						data.append("nonce", "<?php echo esc_attr( $nonce ); ?>");
						try {
							let settings_data = settings_json.value.trim();
							if(false !== JSON.parse(settings_data) ) { // Valid JSON string.
								data.append("database_settings", settings_data);
								let message_string = "Submitting form to Automatic.css backend..."
								console.log(message_string);
								let message = `<p class="">${message_string}</p>`;
								message_container.innerHTML = message;
								fetch("<?php echo esc_url( $ajax_url ); ?>", {
									method: "POST",
									credentials: "same-origin",
									body: data,
								})
									.then((response) => response.json())
									.then((response) => {
										console.log(
											"Received response from Automatic.css backend",
											response
										);
										if (!response.hasOwnProperty("success")) {
											console.error(
												"Expecting a success status from the AJAX call, but missing",
												response.success
											);
											return;
										}
										let message_text = response.data; // good if success is true.
										if( false === response.success ) {
											message_text = response.data.hasOwnProperty("message") ? response.data.message : "An error occurred.";
											if ( response.data.hasOwnProperty("errors") ) {
												message_text += "<ul>";
												// errors is a key => value array in PHP, so it's an object in JS. We want the object values.
												for ( let error of Object.values( response.data.errors ) ) {
													message_text += `<li>${error}</li>`;
												}
												message_text += "</ul>";
											}
										}
										let message_class =
											true === response.success ? "success" : "error";
										let message = `<p class="${message_class}">${message_text}</p>`;
										message_container.innerHTML = message;
									})
									.catch((error) => {
										console.error(
											"Received an error from Automatic.css backend",
											error
										);
										let message_class = "error";
										let message = `<p class="${message_class}"><strong>An error occurred</strong>: ${error}</p>`;
										message_container.innerHTML = message;
										loading_animation("off");
									});
							}
							return true;
						} catch(e) {
							let message_class = "error";
							let message = `<p class="${message_class}"><strong>An error occurred</strong>: ${e.message}</p>`;
							message_container.innerHTML = message;
							return false;
						}
					});

					// Restore defaults.
					let set_defaults_button = document.querySelector("#automatic-css-set-defaults");
					let defaults_json = document.querySelector('#automatic-css-defaults-settings');
					set_defaults_button.addEventListener("click", function(event) {
						event.preventDefault();
						settings_json.value = defaults_json.value;
						let message = `<p class="">Please <strong>press the Update Framework Settings button</strong> to proceed resetting Automatic.css to its default values</p>`;
						message_container.innerHTML = message;
					});
				</script>
			</div>
		<?php
	}
}
