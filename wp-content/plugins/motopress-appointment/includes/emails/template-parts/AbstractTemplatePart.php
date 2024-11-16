<?php

namespace MotoPress\Appointment\Emails\TemplateParts;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
abstract class AbstractTemplatePart {

	/**
	 * @var InterfaceTags
	 *
	 * @since 1.1.0
	 */
	protected $tags;

	/**
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->tags = apply_filters( $this->getName() . '_tags', $this->initTags() );
	}

	/**
	 * @since 1.15.2
	 */
	abstract protected function initTags(): InterfaceTags;

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract public function getName();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract public function getLabel();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return '';
	}

	/**
	 * @return array Array of args.
	 *
	 * @since 1.1.0
	 */
	public function getFields() {
		$id = mpa_prefix( $this->getName() );

		$parentUrl = mpapp()->pages()->settings()->getUrl(
			array(
				'tab' => 'email',
			)
		);

		return array(
			"{$id}_group" => array(
				'type'          => 'group',
				'label'         => $this->getLabel(),
				'description'   => $this->getDescription(),
				'title_actions' => array(
					$parentUrl => esc_html__( 'Back', 'motopress-appointment' ),
				),
			),
			$id           => array(
				'type'         => 'rich-editor',
				'label'        => esc_html__( 'Template Content', 'motopress-appointment' ),
				'description'  => $this->tags->getDescription(),
				'rows'         => 20,
				'default'      => $this->renderDefaultTemplate(),
				'translatable' => true,
			),
		);
	}

	/**
	 * @return string Rendered default template.
	 *
	 * @since 1.10.2
	 */
	abstract public function renderDefaultTemplate();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderTemplate() {
		$template = get_option( mpa_prefix( $this->getName() ), '' );

		if ( empty( $template ) ) {

			$template = $this->renderDefaultTemplate();
		}

		return $template;
	}
}
