<?php

namespace MotoPress\Appointment\ListTables\Emails;

use MotoPress\Appointment\Emails\TemplateParts\AbstractTemplatePart;
use MotoPress\Appointment\ListTables\AbstractListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class TemplatePartsListTable extends AbstractListTable {

	/**
	 * @since 1.1.0
	 */
	protected function loadItems() {
		$this->items = mpapp()->templates()->getEmailTemplateParts();
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	public function getColumns() {
		return array(
			'label'   => esc_html__( 'Name', 'motopress-appointment' ),
			'actions' => '', // No title
		);
	}

	/**
	 * @param string $columnName
	 * @param AbstractTemplatePart $templatePart
	 *
	 * @since 1.1.0
	 */
	protected function displayColumn( $columnName, $templatePart ) {
		switch ( $columnName ) {
			case 'label':
				// Show label
				echo mpa_tmpl_link( $this->getSectionUrl( $templatePart ), $templatePart->getLabel() );

				// Show description
				$description = $templatePart->getDescription();

				if ( ! empty( $description ) ) {
					echo '<p>', $description, '</p>';
				}

				break;

			case 'actions':
				$editUrl    = $this->getSectionUrl( $templatePart );
				$buttonAtts = array( 'class' => 'button' );

				echo mpa_tmpl_link( $editUrl, esc_html__( 'Manage', 'motopress-appointment' ), $buttonAtts );

				break;
		}
	}

	/**
	 * @param AbstractTemplatePart $templatePart
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getSectionUrl( $templatePart ) {
		return mpapp()->pages()->settings()->getUrl( array( 'section' => $templatePart->getName() ) );
	}
}
