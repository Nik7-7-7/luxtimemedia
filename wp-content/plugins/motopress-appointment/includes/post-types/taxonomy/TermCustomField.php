<?php

namespace MotoPress\Appointment\PostTypes\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
abstract class TermCustomField {

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	public $name;

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	public $label;

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	public $description;

	/**
	 * @var string
	 *
	 * @since 1.2
	 */
	public $taxonomy;

	/**
	 * @param string $name Unprefixed name.
	 * @param string $taxonomy
	 * @param array $args Optional.
	 *     @param string $args['label'] '' by default.
	 *     @param string $args['description'] '' by default.
	 *
	 * @since 1.2
	 */
	public function __construct( $name, $taxonomy, $args = array() ) {
		$this->name     = $name;
		$this->taxonomy = $taxonomy;

		// Init label and description
		$defaultArgs = array(
			'label'       => '',
			'description' => '',
		);

		$args += $defaultArgs;

		$this->label       = $args['label'];
		$this->description = $args['description'];

		$this->addActions();
	}

	/**
	 * @since 1.2
	 */
	protected function addActions() {}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getMetaName() {
		return mpa_prefix( $this->name, 'private' ); // '_mpa_featured_image'
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getInputName() {
		return "mpa_meta[{$this->name}]"; // 'mpa_meta[featured_image]'
	}

	/**
	 * @return string
	 *
	 * @since 1.2
	 */
	public function getInputId() {
		return mpa_tmpl_id( "mpa-meta-{$this->name}" ); // 'mpa-meta-featured-image'
	}

	/**
	 * @param \WP_Term|int $term
	 * @return mixed
	 *
	 * @since 1.2
	 */
	public function getValue( $term ) {
		if ( is_object( $term ) ) {
			$termId = $term->term_id;
		} else {
			$termId = (int) $term;
		}

		return get_term_meta( $termId, $this->getMetaName(), true );
	}

	/**
	 * @param \WP_Term $term
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	abstract public function display( $term);

	/**
	 * @param \WP_Term $term
	 * @param 'add'|'edit' $view
	 *
	 * @since 1.2
	 */
	abstract protected function displayInput( $term, $view);

	/**
	 * @param int $termId
	 *
	 * @access protected
	 *
	 * @since 1.2
	 */
	public function save( $termId ) {
		if (
			! isset( $_POST['mpa_meta'] )
			|| ! isset( $_POST['mpa_meta'][ $this->name ] )
			|| ! current_user_can( 'edit_term', $termId )
			|| ! $this->verifyNonce( $termId )
		) {
			return;
		}

		$metaValue = wp_unslash( $_POST['mpa_meta'][ $this->name ] );
		$metaValue = $this->sanitizeValue( $metaValue );

		if ( '' !== $metaValue ) {
			update_term_meta( $termId, $this->getMetaName(), $metaValue );
		} else {
			delete_term_meta( $termId, $this->getMetaName() );
		}
	}

	/**
	 * @param int $termId
	 * @return bool
	 *
	 * @since 1.2
	 */
	abstract protected function verifyNonce( $termId);

	/**
	 * @param string $value
	 * @return mixed
	 *
	 * @since 1.2
	 */
	public function sanitizeValue( $value ) {
		return sanitize_text_field( $value );
	}
}
