<?php
/**
 * Automatic.css Support file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Support class.
 */
class Support {

	use Singleton;

	/**
	 * Initialize the Support class.
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
		?>
			<h2>Support</h2>
			<p style="max-width: 80ch;">You can get support by going to our <a href="https://community.automaticcss.com/home" target="_blank">Community Forum</a> or sending an email to <a href="mailtoacss@digitalgravy.co">acss@digitalgravy.co</a></p>
			<p>Before you do, make sure to check out our <a href="https://automaticcss.com/docs/" target="_blank">documentation</a></p>
		<?php
	}
}
