<?php

namespace MotoPress\Appointment\Fields\Basic;

use MotoPress\Appointment\Fields\AbstractField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class DateRangeField extends AbstractField {

	const TYPE = 'date-range';

	const VALUE_ARRAY_FROM = 'from';
	const VALUE_ARRAY_TO   = 'to';


	/**
	 * @return string
	 */
	public function renderInput() {

		$fromInputAtts          = $this->inputAtts();
		$fromInputAtts['name'] .= '[' . static::VALUE_ARRAY_FROM . ']';
		$fromInputAtts['value'] = '';

		$toInputAtts          = $this->inputAtts();
		$toInputAtts['name'] .= '[' . static::VALUE_ARRAY_TO . ']';
		$toInputAtts['value'] = '';

		if ( ! empty( $this->getValue() ) ) {

			$value                  = $this->getValue();
			$fromInputAtts['value'] = $value[ static::VALUE_ARRAY_FROM ];
			$toInputAtts['value']   = $value[ static::VALUE_ARRAY_TO ];
		}

		$result = '<input type="date" ' . mpa_tmpl_atts( $fromInputAtts ) .
			'>-<input type="date" ' . mpa_tmpl_atts( $toInputAtts ) . '>';

		$result .= '
			<script type="text/javascript">
				(function() {
					var dateFrom = document.querySelector("[name=\'' . $fromInputAtts['name'] . '\']");
					var dateTo = document.querySelector("[name=\'' . $toInputAtts['name'] . '\']");
					dateFrom.addEventListener("change", ()=>{
						limitDateTo();
					});

					function limitDateTo() {
						dateTo.setAttribute("min", dateFrom.value);
						if (dateTo.value < dateFrom.value) {
							dateTo.value = "";
						}
					}

					limitDateTo();
				}());
			</script>
		';

		return $result;
	}
}
