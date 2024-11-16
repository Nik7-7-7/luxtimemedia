<?php
/**
 * Automatic.css Oxygen class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

interface Builder {

	/**
	 * Execute code in this Builder's builder context.
	 *
	 * @return void
	 */
	public function in_builder_context();

	/**
	 * Execute code in this Builder's preview context.
	 *
	 * @return void
	 */
	public function in_preview_context();

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_frontend_context();

	/**
	 * Are we in this Builder's builder context?
	 *
	 * @return boolean
	 */
	public static function is_builder_context();

	/**
	 * Are we in this Builder's preview context?
	 *
	 * @return boolean
	 */
	public static function is_preview_context();

	/**
	 * Are we in this Builder's frontend context?
	 *
	 * @return boolean
	 */
	public static function is_frontend_context();

	/**
	 * Get the version of the Builder.
	 *
	 * @return string
	 */
	public static function get_version();
}
