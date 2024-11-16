<?php
/**
 * Class Rsssl_ReCaptcha
 *
 * The Rsssl_ReCaptcha class extends the Rsssl_Captcha_Provider class and provides functionality
 * for integrating Google reCAPTCHA into a website.
 *
 * @package     RSSSL\Pro\Security\WordPress\Captcha
 * @author     Marcel Santing <marcel@really-simple-ssl.com>
 * @version    PHP 7.2
 * @since      File available since Release 7.3
 */

namespace RSSSL\Pro\Security\WordPress\Captcha;

use RSSSL\Pro\Security\WordPress\Rsssl_Limit_Login_Attempts;

/**
 * Class Rsssl_ReCaptcha
 *
 * The Rsssl_ReCaptcha class extends the Rsssl_Captcha_Provider class and provides functionality
 * for integrating Google reCAPTCHA into a website.
 *
 * @package     RSSSL_PRO\Security\WordPress\Captcha
 * @author     Marcel Santing <marcel@really-simple-ssl.com>
 * @version    PHP 7.2
 * @since      File available since Release 7.3
 */
class Rsssl_ReCaptcha extends Rsssl_Captcha_Provider {

	/**
	 * The site key to be used for re-captcha.
	 *
	 * @var string
	 */
	public $site_key;

	/**
	 * The secret key to be used for re-captcha.
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * The enabled status of re-captcha.
	 *
	 * @var bool
	 */
	public $enabled;

	/**
	 * Construct a new instance of the class.
	 *
	 * This method initializes a new instance of the class and sets the provided
	 * site key and secret key to the respective instance variables.
	 */
	public function __construct() {
		$this->site_key   = rsssl_get_option( 'recaptcha_site_key' );
		$this->secret_key = rsssl_get_option( 'recaptcha_secret_key' );
		if ( ! empty( $this->site_key ) && ! empty( $this->secret_key ) ) {
			$this->enabled = true;
		} else {
			$this->enabled = false;
		}
	}

	/**
	 * Displays a captcha image.
	 *
	 * This method generates a captcha image and displays it on the screen.
	 * The generated captcha image is used to prevent automated activities,
	 * such as spamming or brute-force attacks, by requiring users to read
	 * and input the characters displayed in the captcha image.
	 *
	 * Function is available when the trait is used.
	 *
	 * @return void
	 */
	public function render( bool $auto_submit = false, string $form_id = '' ): void {
		if ( ! $this->enabled ) {
			return;
		}
		// Add the re-captcha script.
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', array(), '1.0', true );
		// if the auto submit is true we add a global js value with that value.
		?>
		<div class="g-recaptcha"
			data-sitekey="<?php echo esc_attr( $this->site_key ); ?>"
			<?php echo $auto_submit ? 'data-callback="auto_submit"' : ''; ?>
			style="transform:scale(0.9);transform-origin:0;-webkit-transform:scale(0.9);
					transform:scale(0.9);-webkit-transform-origin:0 0;transform-origin:0 0;"
		></div>
		<input type="hidden" name="rsssl_captcha_nonce"
				value="<?php echo esc_html( wp_create_nonce( 'rsssl_captcha_nonce' ) ); ?>">
		<?php
        if ( ! empty( $form_id ) && $auto_submit ) {
            ?>
            <script>
                function auto_submit() {
                    document.getElementById("<?php echo esc_attr( $form_id ); ?>").submit();
                }
            </script>
            <?php
        }
	}

	/**
	 * Validates the user's response to the captcha challenge.
	 *
	 * This method sends the user's response to the captcha challenge to the
	 * Google reCAPTCHA API for verification. If the response is valid, it
	 * returns true; otherwise, it returns false.
	 *
	 * @param  string $response  The user's response to the captcha challenge.
	 *
	 * @return bool True if the user's response is valid; otherwise, false.
	 */
	public function validate( $response ): bool {
		$verify_url      = 'https://www.google.com/recaptcha/api/siteverify';
		$verify_response = wp_remote_post(
			$verify_url,
			array(
				'body' => array(
					'secret'   => $this->secret_key,
					'response' => $response,
					'remoteip' => ( new Rsssl_Limit_Login_Attempts() )->get_ip_address()[0],
				),
			)
		);

		if ( is_wp_error( $verify_response ) ) {
			return false;
		}

		$verify_response = json_decode( wp_remote_retrieve_body( $verify_response ), false );

		return $verify_response->success ?? false;
	}

	/**
	 * Returns the configuration settings for the application.
	 *
	 * This method retrieves the configuration settings for the application
	 * and returns them as an array. The configuration settings contain various
	 * values that control the behavior and settings of the application.
	 *
	 * The returned configuration array may include settings like database
	 * connection details, API keys, application specific settings, etc.
	 *
	 * @return array The configuration settings for the application.
	 */
	public function get_config(): array {
		return array(
			'recaptcha_site_key'   => $this->site_key,
			'recaptcha_secret_key' => $this->secret_key,
		);
	}

	/**
	 * Retrieves the response field from the captcha verification API.
	 *
	 * This method retrieves the response field from the captcha verification API
	 * to check if the user's input is valid. The response field contains a boolean value
	 * indicating whether the user's captcha input was correct.
	 *
	 * Note that this method is abstract and should be implemented in the class that uses it.
	 * The implementation will depend on the specific captcha verification API being used.
	 *
	 * @return bool The response field value indicating whether the user's captcha input was correct.
	 */
	public function get_response_field(): bool {
		$nonce = isset( $_POST['rsssl_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rsssl_login_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'rsssl_login_nonce' ) ) {
			return false;
		}

		return isset( $_POST['g-recaptcha-response'] );
	}

	/**
	 * Retrieves the response value.
	 *
	 * This method returns the response value generated by the system.
	 * The response value can be used to determine the outcome of an action,
	 * such as the success or failure of a request.
	 *
	 * Function is available when the trait is used.
	 *
	 * @return string The response value generated by the system.
	 */
	public function get_response_value(): string {
		if ( $this->verify_nonce() ) {
			return $this->get_post_value( 'g-recaptcha-response' );
		}

		return '';
	}
}
