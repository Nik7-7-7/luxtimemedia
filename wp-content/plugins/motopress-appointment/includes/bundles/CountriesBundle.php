<?php

namespace MotoPress\Appointment\Bundles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class CountriesBundle {

	/**
	 * @var array [Country code => Country label]
	 *
	 * @since 1.0
	 */
	protected $countries = array();

	/**
	 * @return array [Country code => Country label]
	 *
	 * @since 1.0
	 */
	public function getCountries() {

		if ( empty( $this->countries ) ) {

			$countries = $this->countriesList();

			asort( $countries );

			/** @since 1.0 */
			$countries = apply_filters( 'mpa_countries', $countries );

			$this->countries = $countries;
		}

		return $this->countries;
	}

	/**
	 * @param string $code
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel( $code ) {

		$countries = $this->getCountries(); // Init if not already

		return array_key_exists( $code, $countries ) ? $countries[ $code ] : '';
	}

	/**
	 * @param string $label
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getCode( $label ) {

		$countries   = $this->getCountries(); // Init if not already
		$countryCode = array_search( $label, $countries );

		return $countryCode ? $countryCode : '';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function countriesList() {
		return array(
			'AF' => esc_html__( 'Afghanistan', 'motopress-appointment' ),
			'AX' => esc_html__( '&#197;land Islands', 'motopress-appointment' ),
			'AL' => esc_html__( 'Albania', 'motopress-appointment' ),
			'DZ' => esc_html__( 'Algeria', 'motopress-appointment' ),
			'AS' => esc_html__( 'American Samoa', 'motopress-appointment' ),
			'AD' => esc_html__( 'Andorra', 'motopress-appointment' ),
			'AO' => esc_html__( 'Angola', 'motopress-appointment' ),
			'AI' => esc_html__( 'Anguilla', 'motopress-appointment' ),
			'AQ' => esc_html__( 'Antarctica', 'motopress-appointment' ),
			'AG' => esc_html__( 'Antigua and Barbuda', 'motopress-appointment' ),
			'AR' => esc_html__( 'Argentina', 'motopress-appointment' ),
			'AM' => esc_html__( 'Armenia', 'motopress-appointment' ),
			'AW' => esc_html__( 'Aruba', 'motopress-appointment' ),
			'AU' => esc_html__( 'Australia', 'motopress-appointment' ),
			'AT' => esc_html__( 'Austria', 'motopress-appointment' ),
			'AZ' => esc_html__( 'Azerbaijan', 'motopress-appointment' ),
			'BS' => esc_html__( 'Bahamas', 'motopress-appointment' ),
			'BH' => esc_html__( 'Bahrain', 'motopress-appointment' ),
			'BD' => esc_html__( 'Bangladesh', 'motopress-appointment' ),
			'BB' => esc_html__( 'Barbados', 'motopress-appointment' ),
			'BY' => esc_html__( 'Belarus', 'motopress-appointment' ),
			'BE' => esc_html__( 'Belgium', 'motopress-appointment' ),
			'PW' => esc_html__( 'Belau', 'motopress-appointment' ),
			'BZ' => esc_html__( 'Belize', 'motopress-appointment' ),
			'BJ' => esc_html__( 'Benin', 'motopress-appointment' ),
			'BM' => esc_html__( 'Bermuda', 'motopress-appointment' ),
			'BT' => esc_html__( 'Bhutan', 'motopress-appointment' ),
			'BO' => esc_html__( 'Bolivia', 'motopress-appointment' ),
			'BQ' => esc_html__( 'Bonaire, Saint Eustatius and Saba', 'motopress-appointment' ),
			'BA' => esc_html__( 'Bosnia and Herzegovina', 'motopress-appointment' ),
			'BW' => esc_html__( 'Botswana', 'motopress-appointment' ),
			'BV' => esc_html__( 'Bouvet Island', 'motopress-appointment' ),
			'BR' => esc_html__( 'Brazil', 'motopress-appointment' ),
			'IO' => esc_html__( 'British Indian Ocean Territory', 'motopress-appointment' ),
			'VG' => esc_html__( 'British Virgin Islands', 'motopress-appointment' ),
			'BN' => esc_html__( 'Brunei', 'motopress-appointment' ),
			'BG' => esc_html__( 'Bulgaria', 'motopress-appointment' ),
			'BF' => esc_html__( 'Burkina Faso', 'motopress-appointment' ),
			'BI' => esc_html__( 'Burundi', 'motopress-appointment' ),
			'KH' => esc_html__( 'Cambodia', 'motopress-appointment' ),
			'CM' => esc_html__( 'Cameroon', 'motopress-appointment' ),
			'CA' => esc_html__( 'Canada', 'motopress-appointment' ),
			'CV' => esc_html__( 'Cape Verde', 'motopress-appointment' ),
			'KY' => esc_html__( 'Cayman Islands', 'motopress-appointment' ),
			'CF' => esc_html__( 'Central African Republic', 'motopress-appointment' ),
			'TD' => esc_html__( 'Chad', 'motopress-appointment' ),
			'CL' => esc_html__( 'Chile', 'motopress-appointment' ),
			'CN' => esc_html__( 'China', 'motopress-appointment' ),
			'CX' => esc_html__( 'Christmas Island', 'motopress-appointment' ),
			'CC' => esc_html__( 'Cocos (Keeling) Islands', 'motopress-appointment' ),
			'CO' => esc_html__( 'Colombia', 'motopress-appointment' ),
			'KM' => esc_html__( 'Comoros', 'motopress-appointment' ),
			'CG' => esc_html__( 'Congo (Brazzaville)', 'motopress-appointment' ),
			'CD' => esc_html__( 'Congo (Kinshasa)', 'motopress-appointment' ),
			'CK' => esc_html__( 'Cook Islands', 'motopress-appointment' ),
			'CR' => esc_html__( 'Costa Rica', 'motopress-appointment' ),
			'HR' => esc_html__( 'Croatia', 'motopress-appointment' ),
			'CU' => esc_html__( 'Cuba', 'motopress-appointment' ),
			'CW' => esc_html__( 'Cura&ccedil;ao', 'motopress-appointment' ),
			'CY' => esc_html__( 'Cyprus', 'motopress-appointment' ),
			'CZ' => esc_html__( 'Czech Republic', 'motopress-appointment' ),
			'DK' => esc_html__( 'Denmark', 'motopress-appointment' ),
			'DJ' => esc_html__( 'Djibouti', 'motopress-appointment' ),
			'DM' => esc_html__( 'Dominica', 'motopress-appointment' ),
			'DO' => esc_html__( 'Dominican Republic', 'motopress-appointment' ),
			'EC' => esc_html__( 'Ecuador', 'motopress-appointment' ),
			'EG' => esc_html__( 'Egypt', 'motopress-appointment' ),
			'SV' => esc_html__( 'El Salvador', 'motopress-appointment' ),
			'GQ' => esc_html__( 'Equatorial Guinea', 'motopress-appointment' ),
			'ER' => esc_html__( 'Eritrea', 'motopress-appointment' ),
			'EE' => esc_html__( 'Estonia', 'motopress-appointment' ),
			'ET' => esc_html__( 'Ethiopia', 'motopress-appointment' ),
			'FK' => esc_html__( 'Falkland Islands', 'motopress-appointment' ),
			'FO' => esc_html__( 'Faroe Islands', 'motopress-appointment' ),
			'FJ' => esc_html__( 'Fiji', 'motopress-appointment' ),
			'FI' => esc_html__( 'Finland', 'motopress-appointment' ),
			'FR' => esc_html__( 'France', 'motopress-appointment' ),
			'GF' => esc_html__( 'French Guiana', 'motopress-appointment' ),
			'PF' => esc_html__( 'French Polynesia', 'motopress-appointment' ),
			'TF' => esc_html__( 'French Southern Territories', 'motopress-appointment' ),
			'GA' => esc_html__( 'Gabon', 'motopress-appointment' ),
			'GM' => esc_html__( 'Gambia', 'motopress-appointment' ),
			'GE' => esc_html__( 'Georgia', 'motopress-appointment' ),
			'DE' => esc_html__( 'Germany', 'motopress-appointment' ),
			'GH' => esc_html__( 'Ghana', 'motopress-appointment' ),
			'GI' => esc_html__( 'Gibraltar', 'motopress-appointment' ),
			'GR' => esc_html__( 'Greece', 'motopress-appointment' ),
			'GL' => esc_html__( 'Greenland', 'motopress-appointment' ),
			'GD' => esc_html__( 'Grenada', 'motopress-appointment' ),
			'GP' => esc_html__( 'Guadeloupe', 'motopress-appointment' ),
			'GU' => esc_html__( 'Guam', 'motopress-appointment' ),
			'GT' => esc_html__( 'Guatemala', 'motopress-appointment' ),
			'GG' => esc_html__( 'Guernsey', 'motopress-appointment' ),
			'GN' => esc_html__( 'Guinea', 'motopress-appointment' ),
			'GW' => esc_html__( 'Guinea-Bissau', 'motopress-appointment' ),
			'GY' => esc_html__( 'Guyana', 'motopress-appointment' ),
			'HT' => esc_html__( 'Haiti', 'motopress-appointment' ),
			'HM' => esc_html__( 'Heard Island and McDonald Islands', 'motopress-appointment' ),
			'HN' => esc_html__( 'Honduras', 'motopress-appointment' ),
			'HK' => esc_html__( 'Hong Kong', 'motopress-appointment' ),
			'HU' => esc_html__( 'Hungary', 'motopress-appointment' ),
			'IS' => esc_html__( 'Iceland', 'motopress-appointment' ),
			'IN' => esc_html__( 'India', 'motopress-appointment' ),
			'ID' => esc_html__( 'Indonesia', 'motopress-appointment' ),
			'IR' => esc_html__( 'Iran', 'motopress-appointment' ),
			'IQ' => esc_html__( 'Iraq', 'motopress-appointment' ),
			'IE' => esc_html__( 'Ireland', 'motopress-appointment' ),
			'IM' => esc_html__( 'Isle of Man', 'motopress-appointment' ),
			'IL' => esc_html__( 'Israel', 'motopress-appointment' ),
			'IT' => esc_html__( 'Italy', 'motopress-appointment' ),
			'CI' => esc_html__( 'Ivory Coast', 'motopress-appointment' ),
			'JM' => esc_html__( 'Jamaica', 'motopress-appointment' ),
			'JP' => esc_html__( 'Japan', 'motopress-appointment' ),
			'JE' => esc_html__( 'Jersey', 'motopress-appointment' ),
			'JO' => esc_html__( 'Jordan', 'motopress-appointment' ),
			'KZ' => esc_html__( 'Kazakhstan', 'motopress-appointment' ),
			'KE' => esc_html__( 'Kenya', 'motopress-appointment' ),
			'KI' => esc_html__( 'Kiribati', 'motopress-appointment' ),
			'KW' => esc_html__( 'Kuwait', 'motopress-appointment' ),
			'KG' => esc_html__( 'Kyrgyzstan', 'motopress-appointment' ),
			'LA' => esc_html__( 'Laos', 'motopress-appointment' ),
			'LV' => esc_html__( 'Latvia', 'motopress-appointment' ),
			'LB' => esc_html__( 'Lebanon', 'motopress-appointment' ),
			'LS' => esc_html__( 'Lesotho', 'motopress-appointment' ),
			'LR' => esc_html__( 'Liberia', 'motopress-appointment' ),
			'LY' => esc_html__( 'Libya', 'motopress-appointment' ),
			'LI' => esc_html__( 'Liechtenstein', 'motopress-appointment' ),
			'LT' => esc_html__( 'Lithuania', 'motopress-appointment' ),
			'LU' => esc_html__( 'Luxembourg', 'motopress-appointment' ),
			'MO' => esc_html__( 'Macao S.A.R., China', 'motopress-appointment' ),
			'MK' => esc_html__( 'Macedonia', 'motopress-appointment' ),
			'MG' => esc_html__( 'Madagascar', 'motopress-appointment' ),
			'MW' => esc_html__( 'Malawi', 'motopress-appointment' ),
			'MY' => esc_html__( 'Malaysia', 'motopress-appointment' ),
			'MV' => esc_html__( 'Maldives', 'motopress-appointment' ),
			'ML' => esc_html__( 'Mali', 'motopress-appointment' ),
			'MT' => esc_html__( 'Malta', 'motopress-appointment' ),
			'MH' => esc_html__( 'Marshall Islands', 'motopress-appointment' ),
			'MQ' => esc_html__( 'Martinique', 'motopress-appointment' ),
			'MR' => esc_html__( 'Mauritania', 'motopress-appointment' ),
			'MU' => esc_html__( 'Mauritius', 'motopress-appointment' ),
			'YT' => esc_html__( 'Mayotte', 'motopress-appointment' ),
			'MX' => esc_html__( 'Mexico', 'motopress-appointment' ),
			'FM' => esc_html__( 'Micronesia', 'motopress-appointment' ),
			'MD' => esc_html__( 'Moldova', 'motopress-appointment' ),
			'MC' => esc_html__( 'Monaco', 'motopress-appointment' ),
			'MN' => esc_html__( 'Mongolia', 'motopress-appointment' ),
			'ME' => esc_html__( 'Montenegro', 'motopress-appointment' ),
			'MS' => esc_html__( 'Montserrat', 'motopress-appointment' ),
			'MA' => esc_html__( 'Morocco', 'motopress-appointment' ),
			'MZ' => esc_html__( 'Mozambique', 'motopress-appointment' ),
			'MM' => esc_html__( 'Myanmar', 'motopress-appointment' ),
			'NA' => esc_html__( 'Namibia', 'motopress-appointment' ),
			'NR' => esc_html__( 'Nauru', 'motopress-appointment' ),
			'NP' => esc_html__( 'Nepal', 'motopress-appointment' ),
			'NL' => esc_html__( 'Netherlands', 'motopress-appointment' ),
			'NC' => esc_html__( 'New Caledonia', 'motopress-appointment' ),
			'NZ' => esc_html__( 'New Zealand', 'motopress-appointment' ),
			'NI' => esc_html__( 'Nicaragua', 'motopress-appointment' ),
			'NE' => esc_html__( 'Niger', 'motopress-appointment' ),
			'NG' => esc_html__( 'Nigeria', 'motopress-appointment' ),
			'NU' => esc_html__( 'Niue', 'motopress-appointment' ),
			'NF' => esc_html__( 'Norfolk Island', 'motopress-appointment' ),
			'MP' => esc_html__( 'Northern Mariana Islands', 'motopress-appointment' ),
			'KP' => esc_html__( 'North Korea', 'motopress-appointment' ),
			'NO' => esc_html__( 'Norway', 'motopress-appointment' ),
			'OM' => esc_html__( 'Oman', 'motopress-appointment' ),
			'PK' => esc_html__( 'Pakistan', 'motopress-appointment' ),
			'PS' => esc_html__( 'Palestinian Territory', 'motopress-appointment' ),
			'PA' => esc_html__( 'Panama', 'motopress-appointment' ),
			'PG' => esc_html__( 'Papua New Guinea', 'motopress-appointment' ),
			'PY' => esc_html__( 'Paraguay', 'motopress-appointment' ),
			'PE' => esc_html__( 'Peru', 'motopress-appointment' ),
			'PH' => esc_html__( 'Philippines', 'motopress-appointment' ),
			'PN' => esc_html__( 'Pitcairn', 'motopress-appointment' ),
			'PL' => esc_html__( 'Poland', 'motopress-appointment' ),
			'PT' => esc_html__( 'Portugal', 'motopress-appointment' ),
			'PR' => esc_html__( 'Puerto Rico', 'motopress-appointment' ),
			'QA' => esc_html__( 'Qatar', 'motopress-appointment' ),
			'RE' => esc_html__( 'Reunion', 'motopress-appointment' ),
			'RO' => esc_html__( 'Romania', 'motopress-appointment' ),
			'RU' => esc_html__( 'Russia', 'motopress-appointment' ),
			'RW' => esc_html__( 'Rwanda', 'motopress-appointment' ),
			'BL' => esc_html__( 'Saint Barth&eacute;lemy', 'motopress-appointment' ),
			'SH' => esc_html__( 'Saint Helena', 'motopress-appointment' ),
			'KN' => esc_html__( 'Saint Kitts and Nevis', 'motopress-appointment' ),
			'LC' => esc_html__( 'Saint Lucia', 'motopress-appointment' ),
			'MF' => esc_html__( 'Saint Martin (French part)', 'motopress-appointment' ),
			'SX' => esc_html__( 'Saint Martin (Dutch part)', 'motopress-appointment' ),
			'PM' => esc_html__( 'Saint Pierre and Miquelon', 'motopress-appointment' ),
			'VC' => esc_html__( 'Saint Vincent and the Grenadines', 'motopress-appointment' ),
			'SM' => esc_html__( 'San Marino', 'motopress-appointment' ),
			'ST' => esc_html__( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'motopress-appointment' ),
			'SA' => esc_html__( 'Saudi Arabia', 'motopress-appointment' ),
			'SN' => esc_html__( 'Senegal', 'motopress-appointment' ),
			'RS' => esc_html__( 'Serbia', 'motopress-appointment' ),
			'SC' => esc_html__( 'Seychelles', 'motopress-appointment' ),
			'SL' => esc_html__( 'Sierra Leone', 'motopress-appointment' ),
			'SG' => esc_html__( 'Singapore', 'motopress-appointment' ),
			'SK' => esc_html__( 'Slovakia', 'motopress-appointment' ),
			'SI' => esc_html__( 'Slovenia', 'motopress-appointment' ),
			'SB' => esc_html__( 'Solomon Islands', 'motopress-appointment' ),
			'SO' => esc_html__( 'Somalia', 'motopress-appointment' ),
			'ZA' => esc_html__( 'South Africa', 'motopress-appointment' ),
			'GS' => esc_html__( 'South Georgia/Sandwich Islands', 'motopress-appointment' ),
			'KR' => esc_html__( 'South Korea', 'motopress-appointment' ),
			'SS' => esc_html__( 'South Sudan', 'motopress-appointment' ),
			'ES' => esc_html__( 'Spain', 'motopress-appointment' ),
			'LK' => esc_html__( 'Sri Lanka', 'motopress-appointment' ),
			'SD' => esc_html__( 'Sudan', 'motopress-appointment' ),
			'SR' => esc_html__( 'Suriname', 'motopress-appointment' ),
			'SJ' => esc_html__( 'Svalbard and Jan Mayen', 'motopress-appointment' ),
			'SZ' => esc_html__( 'Swaziland', 'motopress-appointment' ),
			'SE' => esc_html__( 'Sweden', 'motopress-appointment' ),
			'CH' => esc_html__( 'Switzerland', 'motopress-appointment' ),
			'SY' => esc_html__( 'Syria', 'motopress-appointment' ),
			'TW' => esc_html__( 'Taiwan', 'motopress-appointment' ),
			'TJ' => esc_html__( 'Tajikistan', 'motopress-appointment' ),
			'TZ' => esc_html__( 'Tanzania', 'motopress-appointment' ),
			'TH' => esc_html__( 'Thailand', 'motopress-appointment' ),
			'TL' => esc_html__( 'Timor-Leste', 'motopress-appointment' ),
			'TG' => esc_html__( 'Togo', 'motopress-appointment' ),
			'TK' => esc_html__( 'Tokelau', 'motopress-appointment' ),
			'TO' => esc_html__( 'Tonga', 'motopress-appointment' ),
			'TT' => esc_html__( 'Trinidad and Tobago', 'motopress-appointment' ),
			'TN' => esc_html__( 'Tunisia', 'motopress-appointment' ),
			'TR' => esc_html__( 'Turkey', 'motopress-appointment' ),
			'TM' => esc_html__( 'Turkmenistan', 'motopress-appointment' ),
			'TC' => esc_html__( 'Turks and Caicos Islands', 'motopress-appointment' ),
			'TV' => esc_html__( 'Tuvalu', 'motopress-appointment' ),
			'UG' => esc_html__( 'Uganda', 'motopress-appointment' ),
			'UA' => esc_html__( 'Ukraine', 'motopress-appointment' ),
			'AE' => esc_html__( 'United Arab Emirates', 'motopress-appointment' ),
			'GB' => esc_html__( 'United Kingdom (UK)', 'motopress-appointment' ),
			'US' => esc_html__( 'United States (US)', 'motopress-appointment' ),
			'UM' => esc_html__( 'United States (US) Minor Outlying Islands', 'motopress-appointment' ),
			'VI' => esc_html__( 'United States (US) Virgin Islands', 'motopress-appointment' ),
			'UY' => esc_html__( 'Uruguay', 'motopress-appointment' ),
			'UZ' => esc_html__( 'Uzbekistan', 'motopress-appointment' ),
			'VU' => esc_html__( 'Vanuatu', 'motopress-appointment' ),
			'VA' => esc_html__( 'Vatican', 'motopress-appointment' ),
			'VE' => esc_html__( 'Venezuela', 'motopress-appointment' ),
			'VN' => esc_html__( 'Vietnam', 'motopress-appointment' ),
			'WF' => esc_html__( 'Wallis and Futuna', 'motopress-appointment' ),
			'EH' => esc_html__( 'Western Sahara', 'motopress-appointment' ),
			'WS' => esc_html__( 'Samoa', 'motopress-appointment' ),
			'YE' => esc_html__( 'Yemen', 'motopress-appointment' ),
			'ZM' => esc_html__( 'Zambia', 'motopress-appointment' ),
			'ZW' => esc_html__( 'Zimbabwe', 'motopress-appointment' ),
		);
	}
}
