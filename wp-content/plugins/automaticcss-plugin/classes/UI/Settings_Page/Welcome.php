<?php
/**
 * Automatic.css Welcome file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Settings_Page;

use Automatic_CSS\Traits\Singleton;

/**
 * Automatic.css Welcome class.
 */
class Welcome {

	use Singleton;

	/**
	 * Initialize the Welcome class.
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
			<h2>Getting started</h2>
			<p style="max-width: 80ch;">As of v3.0, Automatic.css has a real-time dashboard inside the builder and on the site's front end. To open ACSS and adjust settings, open the builder or visit the front end. You can open the dashboard by clicking the ACSS link in the WP Admin Bar (Front End), the floating icon indicator bottom right (In Builder), or with the keyboard shortcut, SHIFT&nbsp;+&nbsp;CMD&nbsp;+&nbsp;O. You can also set a custom keyboard shortcut in the Options screen in the dashboard at any time.</p>
			<p><a href="https://automaticcss.com/docs/" target="_blank">Full documentation</a></p>
			<h2>Playlist for Setup and Workflow</h2>
			<p>New 3.0 videos coming soon.</p>
			<iframe width="800" height="450" src="https://www.youtube-nocookie.com/embed/videoseries?si=6f_2oyyZ3eTFoYh3&amp;list=PL72Ci-T5YC93yut2z1NZBVY1pBYy2osB8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		<?php
	}
}
