<?php
/**
 * Automatic.css Dashboard file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Dashboard class.
 */
class Dashboard {

	use Singleton;

	/**
	 * Initialize the Dashboard class.
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
			<h2>How do I get to the dashboard?</h2>
			<p style="max-width: 80ch;">As of v3.0, Automatic.css has a real-time dashboard inside the builder and on the site's front end. To open ACSS and adjust settings, open the builder or visit the front end. You can open the dashboard by clicking the ACSS link in the WP Admin Bar (Front End), the floating icon indicator bottom right (In Builder), or with the keyboard shortcut, SHIFT&nbsp;+&nbsp;CMD&nbsp;+&nbsp;O. You can also set a custom keyboard shortcut in the Options screen in the dashboard at any time.</p>
			<p><a href="https://automaticcss.com/docs/" target="_blank">Full documentation</a></p>
		<?php
	}
}
