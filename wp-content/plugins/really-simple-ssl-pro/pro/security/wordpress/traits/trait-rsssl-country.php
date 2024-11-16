<?php
/**
 * Trait Rsssl_Country
 * This trait is used to get the country list, continent list and combining them.
 *
 * @package RSSSL\Pro\Security\WordPress\Traits
 * @since 7.2
 * @see https://really-simple-ssl.com
 */

namespace RSSSL\Pro\Security\WordPress\Traits;

use Exception;
use RSSSL\Pro\Security\DynamicTables\Rsssl_Data_Table;

/**
 * Trait Rsssl_Country
 * This trait is used to get the country list.
 *
 * @package RSSSL\Pro\Security\WordPress\Traits
 */
trait Rsssl_Country {
	/**
	 * This function is used to get the country list.
	 *
	 * @return array
	 */
	private function get_country_list(): array {
		return array(
			'AF' => __( 'Afghanistan', 'really-simple-ssl' ),
			'AX' => __( 'Aland Islands', 'really-simple-ssl' ),
			'AL' => __( 'Albania', 'really-simple-ssl' ),
			'DZ' => __( 'Algeria', 'really-simple-ssl' ),
			'AS' => __( 'American Samoa', 'really-simple-ssl' ),
			'AD' => __( 'Andorra', 'really-simple-ssl' ),
			'AO' => __( 'Angola', 'really-simple-ssl' ),
			'AI' => __( 'Anguilla', 'really-simple-ssl' ),
			'AQ' => __( 'Antarctica', 'really-simple-ssl' ),
			'AG' => __( 'Antigua and Barbuda', 'really-simple-ssl' ),
			'AR' => __( 'Argentina', 'really-simple-ssl' ),
			'AM' => __( 'Armenia', 'really-simple-ssl' ),
			'AW' => __( 'Aruba', 'really-simple-ssl' ),
			'AU' => __( 'Australia', 'really-simple-ssl' ),
			'AT' => __( 'Austria', 'really-simple-ssl' ),
			'AZ' => __( 'Azerbaijan', 'really-simple-ssl' ),
			'BS' => __( 'Bahamas', 'really-simple-ssl' ),
			'BH' => __( 'Bahrain', 'really-simple-ssl' ),
			'BD' => __( 'Bangladesh', 'really-simple-ssl' ),
			'BB' => __( 'Barbados', 'really-simple-ssl' ),
			'BY' => __( 'Belarus', 'really-simple-ssl' ),
			'BE' => __( 'Belgium', 'really-simple-ssl' ),
			'BZ' => __( 'Belize', 'really-simple-ssl' ),
			'BJ' => __( 'Benin', 'really-simple-ssl' ),
			'BM' => __( 'Bermuda', 'really-simple-ssl' ),
			'BT' => __( 'Bhutan', 'really-simple-ssl' ),
			'BO' => __( 'Bolivia', 'really-simple-ssl' ),
			'BQ' => __( 'Bonaire, Sint Eustatius and Saba', 'really-simple-ssl' ),
			'BA' => __( 'Bosnia and Herzegovina', 'really-simple-ssl' ),
			'BW' => __( 'Botswana', 'really-simple-ssl' ),
			'BV' => __( 'Bouvet Island', 'really-simple-ssl' ),
			'BR' => __( 'Brazil', 'really-simple-ssl' ),
			'IO' => __( 'British Indian Ocean Territory', 'really-simple-ssl' ),
			'BN' => __( 'Brunei Darussalam', 'really-simple-ssl' ),
			'BG' => __( 'Bulgaria', 'really-simple-ssl' ),
			'BF' => __( 'Burkina Faso', 'really-simple-ssl' ),
			'BI' => __( 'Burundi', 'really-simple-ssl' ),
			'KH' => __( 'Cambodia', 'really-simple-ssl' ),
			'CM' => __( 'Cameroon', 'really-simple-ssl' ),
			'CA' => __( 'Canada', 'really-simple-ssl' ),
			'CV' => __( 'Cape Verde', 'really-simple-ssl' ),
			'KY' => __( 'Cayman Islands', 'really-simple-ssl' ),
			'CF' => __( 'Central African Republic', 'really-simple-ssl' ),
			'TD' => __( 'Chad', 'really-simple-ssl' ),
			'CL' => __( 'Chile', 'really-simple-ssl' ),
			'CN' => __( 'China', 'really-simple-ssl' ),
			'CX' => __( 'Christmas Island', 'really-simple-ssl' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'really-simple-ssl' ),
			'CO' => __( 'Colombia', 'really-simple-ssl' ),
			'KM' => __( 'Comoros', 'really-simple-ssl' ),
			'CG' => __( 'Congo', 'really-simple-ssl' ),
			'CD' => __( 'Congo, Democratic Republic of the Congo', 'really-simple-ssl' ),
			'CK' => __( 'Cook Islands', 'really-simple-ssl' ),
			'CR' => __( 'Costa Rica', 'really-simple-ssl' ),
			'CI' => __( "Cote D'Ivoire", 'really-simple-ssl' ),
			'HR' => __( 'Croatia', 'really-simple-ssl' ),
			'CU' => __( 'Cuba', 'really-simple-ssl' ),
			'CW' => __( 'Curacao', 'really-simple-ssl' ),
			'CY' => __( 'Cyprus', 'really-simple-ssl' ),
			'CZ' => __( 'Czech Republic', 'really-simple-ssl' ),
			'DK' => __( 'Denmark', 'really-simple-ssl' ),
			'DJ' => __( 'Djibouti', 'really-simple-ssl' ),
			'DM' => __( 'Dominica', 'really-simple-ssl' ),
			'DO' => __( 'Dominican Republic', 'really-simple-ssl' ),
			'EC' => __( 'Ecuador', 'really-simple-ssl' ),
			'EG' => __( 'Egypt', 'really-simple-ssl' ),
			'SV' => __( 'El Salvador', 'really-simple-ssl' ),
			'GQ' => __( 'Equatorial Guinea', 'really-simple-ssl' ),
			'ER' => __( 'Eritrea', 'really-simple-ssl' ),
			'EE' => __( 'Estonia', 'really-simple-ssl' ),
			'ET' => __( 'Ethiopia', 'really-simple-ssl' ),
			'FK' => __( 'Falkland Islands (Malvinas)', 'really-simple-ssl' ),
			'FO' => __( 'Faroe Islands', 'really-simple-ssl' ),
			'FJ' => __( 'Fiji', 'really-simple-ssl' ),
			'FI' => __( 'Finland', 'really-simple-ssl' ),
			'FR' => __( 'France', 'really-simple-ssl' ),
			'GF' => __( 'French Guiana', 'really-simple-ssl' ),
			'PF' => __( 'French Polynesia', 'really-simple-ssl' ),
			'TF' => __( 'French Southern Territories', 'really-simple-ssl' ),
			'GA' => __( 'Gabon', 'really-simple-ssl' ),
			'GM' => __( 'Gambia', 'really-simple-ssl' ),
			'GE' => __( 'Georgia', 'really-simple-ssl' ),
			'DE' => __( 'Germany', 'really-simple-ssl' ),
			'GH' => __( 'Ghana', 'really-simple-ssl' ),
			'GI' => __( 'Gibraltar', 'really-simple-ssl' ),
			'GR' => __( 'Greece', 'really-simple-ssl' ),
			'GL' => __( 'Greenland', 'really-simple-ssl' ),
			'GD' => __( 'Grenada', 'really-simple-ssl' ),
			'GP' => __( 'Guadeloupe', 'really-simple-ssl' ),
			'GU' => __( 'Guam', 'really-simple-ssl' ),
			'GT' => __( 'Guatemala', 'really-simple-ssl' ),
			'GG' => __( 'Guernsey', 'really-simple-ssl' ),
			'GN' => __( 'Guinea', 'really-simple-ssl' ),
			'GW' => __( 'Guinea-Bissau', 'really-simple-ssl' ),
			'GY' => __( 'Guyana', 'really-simple-ssl' ),
			'HT' => __( 'Haiti', 'really-simple-ssl' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'really-simple-ssl' ),
			'VA' => __( 'Holy See (Vatican City State)', 'really-simple-ssl' ),
			'HN' => __( 'Honduras', 'really-simple-ssl' ),
			'HK' => __( 'Hong Kong', 'really-simple-ssl' ),
			'HU' => __( 'Hungary', 'really-simple-ssl' ),
			'IS' => __( 'Iceland', 'really-simple-ssl' ),
			'IN' => __( 'India', 'really-simple-ssl' ),
			'ID' => __( 'Indonesia', 'really-simple-ssl' ),
			'IR' => __( 'Iran, Islamic Republic of', 'really-simple-ssl' ),
			'IQ' => __( 'Iraq', 'really-simple-ssl' ),
			'IE' => __( 'Ireland', 'really-simple-ssl' ),
			'IM' => __( 'Isle of Man', 'really-simple-ssl' ),
			'IL' => __( 'Israel', 'really-simple-ssl' ),
			'IT' => __( 'Italy', 'really-simple-ssl' ),
			'JM' => __( 'Jamaica', 'really-simple-ssl' ),
			'JP' => __( 'Japan', 'really-simple-ssl' ),
			'JE' => __( 'Jersey', 'really-simple-ssl' ),
			'JO' => __( 'Jordan', 'really-simple-ssl' ),
			'KZ' => __( 'Kazakhstan', 'really-simple-ssl' ),
			'KE' => __( 'Kenya', 'really-simple-ssl' ),
			'KI' => __( 'Kiribati', 'really-simple-ssl' ),
			'KP' => __( "Korea, Democratic People's Republic of", 'really-simple-ssl' ),
			'KR' => __( 'Korea, Republic of', 'really-simple-ssl' ),
			'XK' => __( 'Kosovo', 'really-simple-ssl' ),
			'KW' => __( 'Kuwait', 'really-simple-ssl' ),
			'KG' => __( 'Kyrgyzstan', 'really-simple-ssl' ),
			'LA' => __( "Lao People's Democratic Republic", 'really-simple-ssl' ),
			'LV' => __( 'Latvia', 'really-simple-ssl' ),
			'LB' => __( 'Lebanon', 'really-simple-ssl' ),
			'LS' => __( 'Lesotho', 'really-simple-ssl' ),
			'LR' => __( 'Liberia', 'really-simple-ssl' ),
			'LY' => __( 'Libyan Arab Jamahiriya', 'really-simple-ssl' ),
			'LI' => __( 'Liechtenstein', 'really-simple-ssl' ),
			'LT' => __( 'Lithuania', 'really-simple-ssl' ),
			'LU' => __( 'Luxembourg', 'really-simple-ssl' ),
			'MO' => __( 'Macao', 'really-simple-ssl' ),
			'MK' => __( 'Macedonia, the Former Yugoslav Republic of', 'really-simple-ssl' ),
			'MG' => __( 'Madagascar', 'really-simple-ssl' ),
			'MW' => __( 'Malawi', 'really-simple-ssl' ),
			'MY' => __( 'Malaysia', 'really-simple-ssl' ),
			'MV' => __( 'Maldives', 'really-simple-ssl' ),
			'ML' => __( 'Mali', 'really-simple-ssl' ),
			'MT' => __( 'Malta', 'really-simple-ssl' ),
			'MH' => __( 'Marshall Islands', 'really-simple-ssl' ),
			'MQ' => __( 'Martinique', 'really-simple-ssl' ),
			'MR' => __( 'Mauritania', 'really-simple-ssl' ),
			'MU' => __( 'Mauritius', 'really-simple-ssl' ),
			'YT' => __( 'Mayotte', 'really-simple-ssl' ),
			'MX' => __( 'Mexico', 'really-simple-ssl' ),
			'FM' => __( 'Micronesia, Federated States of', 'really-simple-ssl' ),
			'MD' => __( 'Moldova, Republic of', 'really-simple-ssl' ),
			'MC' => __( 'Monaco', 'really-simple-ssl' ),
			'MN' => __( 'Mongolia', 'really-simple-ssl' ),
			'ME' => __( 'Montenegro', 'really-simple-ssl' ),
			'MS' => __( 'Montserrat', 'really-simple-ssl' ),
			'MA' => __( 'Morocco', 'really-simple-ssl' ),
			'MZ' => __( 'Mozambique', 'really-simple-ssl' ),
			'MM' => __( 'Myanmar', 'really-simple-ssl' ),
			'NA' => __( 'Namibia', 'really-simple-ssl' ),
			'NR' => __( 'Nauru', 'really-simple-ssl' ),
			'NP' => __( 'Nepal', 'really-simple-ssl' ),
			'NL' => __( 'Netherlands', 'really-simple-ssl' ),
			'AN' => __( 'Netherlands Antilles', 'really-simple-ssl' ),
			'NC' => __( 'New Caledonia', 'really-simple-ssl' ),
			'NZ' => __( 'New Zealand', 'really-simple-ssl' ),
			'NI' => __( 'Nicaragua', 'really-simple-ssl' ),
			'NE' => __( 'Niger', 'really-simple-ssl' ),
			'NG' => __( 'Nigeria', 'really-simple-ssl' ),
			'NU' => __( 'Niue', 'really-simple-ssl' ),
			'NF' => __( 'Norfolk Island', 'really-simple-ssl' ),
			'MP' => __( 'Northern Mariana Islands', 'really-simple-ssl' ),
			'NO' => __( 'Norway', 'really-simple-ssl' ),
			'OM' => __( 'Oman', 'really-simple-ssl' ),
			'PK' => __( 'Pakistan', 'really-simple-ssl' ),
			'PW' => __( 'Palau', 'really-simple-ssl' ),
			'PS' => __( 'Palestinian Territory, Occupied', 'really-simple-ssl' ),
			'PA' => __( 'Panama', 'really-simple-ssl' ),
			'PG' => __( 'Papua New Guinea', 'really-simple-ssl' ),
			'PY' => __( 'Paraguay', 'really-simple-ssl' ),
			'PE' => __( 'Peru', 'really-simple-ssl' ),
			'PH' => __( 'Philippines', 'really-simple-ssl' ),
			'PN' => __( 'Pitcairn', 'really-simple-ssl' ),
			'PL' => __( 'Poland', 'really-simple-ssl' ),
			'PT' => __( 'Portugal', 'really-simple-ssl' ),
			'PR' => __( 'Puerto Rico', 'really-simple-ssl' ),
			'QA' => __( 'Qatar', 'really-simple-ssl' ),
			'RE' => __( 'Reunion', 'really-simple-ssl' ),
			'RO' => __( 'Romania', 'really-simple-ssl' ),
			'RU' => __( 'Russian Federation', 'really-simple-ssl' ),
			'RW' => __( 'Rwanda', 'really-simple-ssl' ),
			'BL' => __( 'Saint Barthelemy', 'really-simple-ssl' ),
			'SH' => __( 'Saint Helena', 'really-simple-ssl' ),
			'KN' => __( 'Saint Kitts and Nevis', 'really-simple-ssl' ),
			'LC' => __( 'Saint Lucia', 'really-simple-ssl' ),
			'MF' => __( 'Saint Martin', 'really-simple-ssl' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'really-simple-ssl' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'really-simple-ssl' ),
			'WS' => __( 'Samoa', 'really-simple-ssl' ),
			'SM' => __( 'San Marino', 'really-simple-ssl' ),
			'ST' => __( 'Sao Tome and Principe', 'really-simple-ssl' ),
			'SA' => __( 'Saudi Arabia', 'really-simple-ssl' ),
			'SN' => __( 'Senegal', 'really-simple-ssl' ),
			'RS' => __( 'Serbia', 'really-simple-ssl' ),
			'CS' => __( 'Serbia and Montenegro', 'really-simple-ssl' ),
			'SC' => __( 'Seychelles', 'really-simple-ssl' ),
			'SL' => __( 'Sierra Leone', 'really-simple-ssl' ),
			'SG' => __( 'Singapore', 'really-simple-ssl' ),
			'SX' => __( 'St Martin', 'really-simple-ssl' ),
			'SK' => __( 'Slovakia', 'really-simple-ssl' ),
			'SI' => __( 'Slovenia', 'really-simple-ssl' ),
			'SB' => __( 'Solomon Islands', 'really-simple-ssl' ),
			'SO' => __( 'Somalia', 'really-simple-ssl' ),
			'ZA' => __( 'South Africa', 'really-simple-ssl' ),
			'GS' => __( 'South Georgia and the South Sandwich Islands', 'really-simple-ssl' ),
			'SS' => __( 'South Sudan', 'really-simple-ssl' ),
			'ES' => __( 'Spain', 'really-simple-ssl' ),
			'LK' => __( 'Sri Lanka', 'really-simple-ssl' ),
			'SD' => __( 'Sudan', 'really-simple-ssl' ),
			'SR' => __( 'Suriname', 'really-simple-ssl' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'really-simple-ssl' ),
			'SZ' => __( 'Swaziland', 'really-simple-ssl' ),
			'SE' => __( 'Sweden', 'really-simple-ssl' ),
			'CH' => __( 'Switzerland', 'really-simple-ssl' ),
			'SY' => __( 'Syrian Arab Republic', 'really-simple-ssl' ),
			'TW' => __( 'Taiwan, Province of China', 'really-simple-ssl' ),
			'TJ' => __( 'Tajikistan', 'really-simple-ssl' ),
			'TZ' => __( 'Tanzania, United Republic of', 'really-simple-ssl' ),
			'TH' => __( 'Thailand', 'really-simple-ssl' ),
			'TL' => __( 'Timor-Leste', 'really-simple-ssl' ),
			'TG' => __( 'Togo', 'really-simple-ssl' ),
			'TK' => __( 'Tokelau', 'really-simple-ssl' ),
			'TO' => __( 'Tonga', 'really-simple-ssl' ),
			'TT' => __( 'Trinidad and Tobago', 'really-simple-ssl' ),
			'TN' => __( 'Tunisia', 'really-simple-ssl' ),
			'TR' => __( 'Turkey', 'really-simple-ssl' ),
			'TM' => __( 'Turkmenistan', 'really-simple-ssl' ),
			'TC' => __( 'Turks and Caicos Islands', 'really-simple-ssl' ),
			'TV' => __( 'Tuvalu', 'really-simple-ssl' ),
			'UG' => __( 'Uganda', 'really-simple-ssl' ),
			'UA' => __( 'Ukraine', 'really-simple-ssl' ),
			'AE' => __( 'United Arab Emirates', 'really-simple-ssl' ),
			'GB' => __( 'United Kingdom', 'really-simple-ssl' ),
			'US' => __( 'United States', 'really-simple-ssl' ),
			'UM' => __( 'United States Minor Outlying Islands', 'really-simple-ssl' ),
			'UY' => __( 'Uruguay', 'really-simple-ssl' ),
			'UZ' => __( 'Uzbekistan', 'really-simple-ssl' ),
			'VU' => __( 'Vanuatu', 'really-simple-ssl' ),
			'VE' => __( 'Venezuela', 'really-simple-ssl' ),
			'VN' => __( 'Viet Nam', 'really-simple-ssl' ),
			'VG' => __( 'Virgin Islands, British', 'really-simple-ssl' ),
			'VI' => __( 'Virgin Islands, U.s.', 'really-simple-ssl' ),
			'WF' => __( 'Wallis and Futuna', 'really-simple-ssl' ),
			'EH' => __( 'Western Sahara', 'really-simple-ssl' ),
			'YE' => __( 'Yemen', 'really-simple-ssl' ),
			'ZM' => __( 'Zambia', 'really-simple-ssl' ),
			'ZW' => __( 'Zimbabwe', 'really-simple-ssl' ),
		);
	}

	/**
	 * This function is used to get the continent list.
	 *
	 * @return array
	 */
	private function get_continent_list(): array {
		return array(
			'AF' => __( 'Africa', 'really-simple-ssl' ),
			'AN' => __( 'Antarctica', 'really-simple-ssl' ),
			'AS' => __( 'Asia', 'really-simple-ssl' ),
			'EU' => __( 'Europe', 'really-simple-ssl' ),
			'NA' => __( 'North America', 'really-simple-ssl' ),
			'OC' => __( 'Oceania', 'really-simple-ssl' ),
			'SA' => __( 'South America', 'really-simple-ssl' ),
		);
	}

	/**
	 * This function is used to build the country continent lookup array.
	 *
	 * @param string $country The country to use.
	 *
	 * @return string The continent corresponding to the provided country.
	 */
	private function build_country_continent_lookup_array( string $country ): string {
		if ( 'XX' === $country ) {
			return $country;
		}
		$country_continent_lookup = $this->create_country_continent_lookup();
		return $country_continent_lookup[ $country ] ?? '';
	}

	/**
	 * This function is used to create a lookup array mapping country ISO2 codes to continent ISO2 codes.
	 *
	 * @return array A lookup array where the keys are country ISO2 codes and the values are continent ISO2 codes.
	 */
	private function create_country_continent_lookup(): array {
		$lookup_array = array();
		$continents   = $this->get_continent_list();

		foreach ( $continents as $continent_iso2_code => $continent_name ) {
			$country_iso2_codes = $this->continent_to_iso2country( $continent_iso2_code );
			foreach ( $country_iso2_codes as $country_iso2_code ) {
				$lookup_array[ $country_iso2_code ] = $continent_iso2_code;
			}
		}

		return $lookup_array;
	}

	/**
	 * This function is used to get the name of a region based on its ISO2 code.
	 *
	 * @param  string $continent_iso2_code  The ISO2 code of the continent.
	 *
	 * @return string The name of the region.
	 */
	private function get_region_name( string $continent_iso2_code ): string {
		$continents = $this->get_continent_list();

		return $continents[ $continent_iso2_code ] ?? 'UNKNOWN';
	}

	/**
	 * This function is used to get the name of a country based on its ISO2 code.
	 *
	 * @param  string $country_iso2_code  The ISO2 code of the country.
	 *
	 * @return string The name of the country if found, otherwise 'UNKNOWN'.
	 */
	private function get_country_name( string $country_iso2_code ): string {
		$countries = $this->get_country_list();

		return $countries[ $country_iso2_code ] ?? 'Unknown Country';
	}

	/**
	 * This function is used to get the continent to iso2 country.
	 *
	 * @param string $continent The continent to use.
	 *
	 * @return array
	 */
	private function continent_to_iso2country( string $continent ): array {
		$african_country_iso2_codes        = array(
			'DZ',
			'AO',
			'BJ',
			'BW',
			'BF',
			'BI',
			'CV',
			'CM',
			'CF',
			'TD',
			'KM',
			'CG',
			'CD',
			'CI',
			'DJ',
			'EG',
			'GQ',
			'ER',
			'SZ',
			'ET',
			'GA',
			'GM',
			'GH',
			'GN',
			'GW',
			'KE',
			'LS',
			'LR',
			'LY',
			'MG',
			'MW',
			'ML',
			'MR',
			'MU',
			'MA',
			'MZ',
			'NA',
			'NE',
			'NG',
			'RE',
			'RW',
			'ST',
			'SN',
			'SC',
			'SL',
			'SO',
			'ZA',
			'SS',
			'SD',
			'TZ',
			'TG',
			'TN',
			'UG',
			'EH',
			'YT',
			'ZM',
			'ZW',
		);
		$antarctica_country_iso2_codes     = array( 'AQ', 'BV', 'TF', 'HM', 'GS' );
		$asian_country_iso2_codes          = array(
			'AF',
			'AM',
			'AZ',
			'BH',
			'BD',
			'BT',
			'BN',
			'KH',
			'CN',
			'CX',
			'CC',
			'IO',
			'GE',
			'HK',
			'IN',
			'ID',
			'IR',
			'IQ',
			'IL',
			'JP',
			'JO',
			'KZ',
			'KP',
			'KR',
			'KW',
			'KG',
			'LA',
			'LB',
			'MO',
			'MY',
			'MV',
			'MN',
			'MM',
			'NP',
			'OM',
			'PK',
			'PS',
			'PH',
			'QA',
			'SA',
			'SG',
			'LK',
			'SY',
			'TW',
			'TJ',
			'TH',
			'TL',
			'TM',
			'AE',
			'UZ',
			'VN',
			'YE',
		);
		$european_country_iso2_codes       = array(
			'AL',
			'AD',
			'AT',
			'AX',
			'BY',
			'BE',
			'BA',
			'BG',
			'HR',
			'CS',
			'CY',
			'CZ',
			'DK',
			'EE',
			'FO',
			'FI',
			'FR',
			'DE',
			'GG',
			'GI',
			'GR',
			'HU',
			'IS',
			'IE',
			'IM',
			'IT',
			'JE',
			'XK',
			'LV',
			'LI',
			'LT',
			'LU',
			'MT',
			'MD',
			'MC',
			'ME',
			'NL',
			'MK',
			'NO',
			'PL',
			'PT',
			'RO',
			'RU',
			'SM',
			'RS',
			'SK',
			'SI',
			'SJ',
			'ES',
			'SE',
			'CH',
			'TC',
			'TR',
			'UA',
			'GB',
			'VA',
		);
		$north_american_country_iso2_codes = array(
			'AG',
			'AI',
			'AW',
			'BS',
			'BB',
			'BL',
			'BM',
			'BZ',
			'CA',
			'CR',
			'CU',
			'DM',
			'DO',
			'SH',
			'SV',
			'GD',
			'GT',
			'HT',
			'HN',
			'GL',
			'GP',
			'JM',
			'KY',
			'MF',
			'PM',
			'MS',
			'MQ',
			'MX',
			'NI',
			'PA',
			'PR',
			'KN',
			'LC',
			'VC',
			'VG',
			'VI',
			'SX',
			'US',
		);
		$oceanian_country_iso2_codes       = array(
			'AS',
			'AU',
			'CK',
			'FJ',
			'PF',
			'GU',
			'KI',
			'MH',
			'FM',
			'NR',
			'NC',
			'NZ',
			'NU',
			'NF',
			'MP',
			'PW',
			'PG',
			'PN',
			'WS',
			'SB',
			'TK',
			'TO',
			'TV',
			'UM',
			'VU',
			'WF',
		);
		$south_american_country_iso2_codes = array(
			'AN',
			'AR',
			'BO',
			'BR',
			'BQ',
			'CL',
			'CO',
			'CW',
			'EC',
			'FK',
			'GF',
			'GY',
			'PY',
			'PE',
			'SR',
			'TT',
			'UY',
			'VE',
		);

		switch ( $continent ) {
			case 'AF':
				return $african_country_iso2_codes;
			case 'AN':
				return $antarctica_country_iso2_codes;
			case 'AS':
				return $asian_country_iso2_codes;
			case 'EU':
				return $european_country_iso2_codes;
			case 'NA':
				return $north_american_country_iso2_codes;
			case 'OC':
				return $oceanian_country_iso2_codes;
			case 'SA':
				return $south_american_country_iso2_codes;
			default:
				return array();
		}
	}

	public function get_countries_by_region_code( $region_iso_2_code) {
		return $this->continent_to_iso2country($region_iso_2_code);
	}

	/**
	 * Fetches a list of countries using the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param array $data The data array containing additional parameters for fetching the list.
	 *
	 * @return array The fetched list of countries.
	 * @throws Exception Throws an exception when things go wrong.
	 */
	protected function fetch_country_list( array $data ): array {
		// Logic specific to fetching country list.
		return $this->fetch_list( 'country', $data );
	}

	/**
	 * Fetches a list of continents using the specified Rsssl_Geo_Block object and data array.
	 *
	 * @param array $data The data array containing additional parameters for fetching the list.
	 *
	 * @return array The fetched list of continents.
	 * @throws Exception Throws an exception when things go wrong.
	 */
	private function fetch_continent_list( array $data ): array {
		// Logic specific to fetching continent list.
		return $this->fetch_list( 'regions', $data );
	}

	/**
	 * This function is used to set up the data table array. These are all allowed countries.
	 *
	 * @param array $original_array The original array that needs to change.
	 *
	 * @return array
	 */
	private function transpose_array( array $original_array ): array {
		$id = 1;

		$is_continent_list = $original_array === $this->get_continent_list();

		$transformed_array = array();

		foreach ( $this->filter_array( $original_array, $is_continent_list, true ) as $iso2_code => $name ) {
			if ( $is_continent_list ) {
				$transformed_array[] = $this->create_array_with_continent_name( $id, $iso2_code, $name );
			} else {
				$transformed_array[] = $this->create_array_with_country_name( $id, $iso2_code, $name );
			}
			++$id;
		}
		return $transformed_array;
	}

	/**
	 * This function is used to get the results from the data table.
	 *
	 * @param Rsssl_Data_Table $data_table The data table to use.
	 * @param array|null       $data The data to use.
	 *
	 * @throws Exception Throws if all function fails for TS purposes.
	 */
	private function get_results_from_data_table( Rsssl_Data_Table $data_table, ?array $data ): array {
		try {
			$result         = $data_table
				->validate_search()
				->validate_sorting()
				->validate_pagination()
				->get_results();
			$result['post'] = $data;
		} catch ( Exception $e ) {
			// Handle exception, e.g. log error message.
			return $this->handle_exception( $e );
		}
		return $result;
	}

	/**
	 * This function is used to get the results from the data table.
	 *
	 * @param Rsssl_Data_Table $data_table The data table to use.
	 * @param array|null       $data The data to use.
	 *
	 * @throws Exception Throws if all function fails for TS purposes.
	 */
	private function get_results_from_data_table_no_pagination( Rsssl_Data_Table $data_table, ?array $data ): array {
		try {
			$result         = $data_table
				->validate_search()
				->validate_sorting()
				->validate_pagination()
				->get_results_without_paginate();
			$result['post'] = $data;
		} catch ( Exception $e ) {
			// Handle exception, e.g. log error message.
			return $this->handle_exception( $e );
		}
		return $result;
	}

	/**
	 * Retrieves the ISO2 code(s) of a country name.
	 *
	 * @param  string $country_name  The country name for which the ISO2 code(s) are requested.
	 *
	 * @return array An array containing the ISO2 code(s) of the country. If the country name is not found, an empty array is returned.
	 */
	public function get_iso2_codes_by_country_name( string $country_name ): array {
		$countries          = $this->get_country_list();
		$matching_countries = array_filter(
			$countries,
			static function ( $country ) use ( $country_name ) {
				return false !== stripos( $country, $country_name );
			},
			ARRAY_FILTER_USE_BOTH
		);

		return $matching_countries ? array_keys( $matching_countries ) : array();
	}

	/**
	 * This function retrieves the continent list and returns the iso2
	 * codes of the matching continents in an array.
	 * If a continent does not exist in the continent list,
	 * an empty array is returned.
	 *
	 * @param string $continent_name The name of the continent to search for.
	 * @return array
	 */
	public function get_iso2_codes_by_continent_name( string $continent_name ): array {
		$continents          = $this->get_continent_list();
		$matching_continents = array_filter(
			$continents,
			static function ( $continent ) use ( $continent_name ) {
				return false !== stripos( $continent, $continent_name );
			},
			ARRAY_FILTER_USE_BOTH
		);

		return $matching_continents ? array_keys( $matching_continents ) : array();
	}
}
