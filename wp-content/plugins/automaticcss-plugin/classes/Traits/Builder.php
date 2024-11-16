<?php
/**
 * Automatic.css Singleton class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Traits;

use Automatic_CSS\Helpers\Logger;

trait Builder {

	/**
	 * The prefix to use in do_action calls for this builder.
	 *
	 * @var string
	 */
	protected $builder_prefix;

	/**
	 * Execute code in this Builder's builder context.
	 *
	 * @return void
	 */
	public function in_builder_context() {
		// Call the generic builder context action.
		do_action( 'acss/core/in_builder_context' );
		// Call the builder context action.
		do_action( "acss/{$this->builder_prefix}/in_builder_context" );
	}

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_preview_context() {
		// Call the generic preview context action.
		do_action( 'acss/core/in_preview_context' );
		// Call the preview context action.
		do_action( "acss/{$this->builder_prefix}/in_preview_context" );
	}

	/**
	 * Execute code in this Builder's frontend context.
	 *
	 * @return void
	 */
	public function in_frontend_context() {
		// Call the generic frontend context action.
		do_action( 'acss/core/in_frontend_context' );
		// Call the frontend context action.
		do_action( "acss/{$this->builder_prefix}/in_frontend_context" );
	}
}
