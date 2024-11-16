<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\ListTables\AbstractListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class ListTableField extends AbstractField {

	/** @since 1.1.0 */
	const TYPE = 'list-table';

	/**
	 * @var AbstractListTable
	 *
	 * @since 1.1.0
	 */
	protected $listTable = null;

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function mapFields() {
		return parent::mapFields() + array(
			'list_table' => 'listTable',
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function renderInput() {
		if ( ! is_null( $this->listTable ) ) {
			ob_start();
			$this->listTable->display();
			return ob_get_clean();
		} else {
			return '';
		}
	}
}
