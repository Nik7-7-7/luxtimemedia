<?php

namespace MotoPress\Appointment\ListTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
abstract class AbstractSettingsListTable extends AbstractListTable {

	/**
	 * @since 1.5.0
	 *
	 * @param string $columnName
	 * @param mixed $item Single item object (email, payment gateway etc.).
	 */
	protected function displayColumn( $columnName, $item ) {

		switch ( $columnName ) {
			case 'switch':
				echo $this->isEnabled( $item ) ? esc_html__( 'Yes', 'motopress-appointment' ) : esc_html__( 'No', 'motopress-appointment' );
				break;

			case 'actions':
				$editUrl    = $this->getSectionUrl( $item );
				$buttonAtts = array( 'class' => 'button' );

				echo mpa_tmpl_link( $editUrl, esc_html__( 'Manage', 'motopress-appointment' ), $buttonAtts );

				break;
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @param mixed $item
	 * @return bool
	 */
	abstract protected function isEnabled( $item);

	/**
	 * @since 1.5.0
	 *
	 * @param mixed $item
	 * @return string
	 */
	abstract protected function getSectionUrl( $item);
}
