<?php
/**
 * Class Rsssl_Captcha
 *
 * This class handles the rendering of captcha images using different providers.
 *
 * @package     RSSSL_PRO\Security\WordPress\Captcha
 * @since       File available since Release 7.3
 * @version     7.3
 * @subpackage  WordPress\Captcha
 * @category    Security
 * @category    WordPress
 * @category    Captcha
 * @author      Marcel Santing<marcel@really-simple-plugins.com>
 *      Really Simple Plugins
 */

namespace RSSSL\Pro\Security\WordPress\Captcha;

/**
 * Class Rsssl_Captcha
 *
 * The 'Rsssl_Captcha' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class handles the rendering of captcha images using different providers.
 *
 * @package     RSSSL_PRO\Security\WordPress\Captcha  // The categorization of this class.
 * @author      Really Simple Plugins  // The creator of the class.
 */
class Rsssl_Captcha {

	/**
	 * The captcha provider to be used.
	 *
	 * @var object
	 */
	public $captcha_provider;

	/**
	 * Constructor method for the class.
	 *
	 * This method initializes a new instance of the class and calls the
	 * get_set_provider method upon instantiation.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->get_set_provider();
	}

	/**
	 * Sets the captcha provider based on the enabled option from the settings.
	 *
	 * @return void
	 */
	public function get_set_provider(): void {
		$providers = array(
			'hcaptcha'  => new Rsssl_HCaptcha(),
			'recaptcha' => new Rsssl_ReCaptcha(),
		);

		$this->captcha_provider = $providers[ rsssl_get_option( 'enabled_captcha_provider' ) ];
	}

	/**
	 * Renders the captcha image.
	 *
	 * This method instantiates a new instance of the Rsssl_Captcha class
	 * and calls the render method on the captcha_provider property to retrieve
	 * the captcha image.
	 *
	 * @param bool $auto_submit Submits any form if true.
	 * @param string $form_id Id of the used form.
	 *
	 * @return void The rendered captcha image.
	 */
	public static function render( bool $auto_submit = false, string $form_id = ''): void {
		$captcha = new Rsssl_Captcha();
		$captcha->captcha_provider->render( $auto_submit, $form_id );
	}

	/**
	 * Post evaluation method.
	 *
	 * @return bool Returns true if the captcha response value is not empty, false otherwise.
	 */
	public function post_evaluation(): bool {
		$captcha = new Rsssl_Captcha();
		if ( $captcha->captcha_provider->get_response_field() ) {
			return ! empty( $captcha->captcha_provider->get_response_value() );
		}
	}
}
