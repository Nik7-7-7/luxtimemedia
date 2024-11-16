<?php

namespace MotoPress\Appointment\Bundles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class CurrenciesBundle {

	private $currencyDecimals = array(
		'EUR' => 2,
		'USD' => 2,
		'GBP' => 2,
		'AED' => 2,
		'AFN' => 2,
		'ALL' => 2,
		'AMD' => 2,
		'ANG' => 2,
		'AOA' => 2,
		'ARS' => 2,
		'AUD' => 2,
		'AWG' => 2,
		'AZN' => 2,
		'BAM' => 2,
		'BBD' => 2,
		'BDT' => 2,
		'BGN' => 2,
		'BHD' => 3,
		'BIF' => 2,
		'BMD' => 2,
		'BND' => 2,
		'BOB' => 2,
		'BRL' => 2,
		'BSD' => 2,
		'BTC' => 2,
		'BTN' => 2,
		'BWP' => 2,
		'BYR' => 2,
		'BYN' => 2,
		'BZD' => 2,
		'CAD' => 2,
		'CDF' => 2,
		'CHF' => 2,
		'CLP' => 2,
		'CNY' => 2,
		'COP' => 2,
		'CRC' => 2,
		'CUC' => 2,
		'CUP' => 2,
		'CVE' => 0,
		'CZK' => 2,
		'DJF' => 0,
		'DKK' => 2,
		'DOP' => 2,
		'DZD' => 2,
		'EGP' => 2,
		'ERN' => 2,
		'ETB' => 2,
		'FJD' => 2,
		'FKP' => 2,
		'GEL' => 2,
		'GGP' => 2,
		'GHS' => 2,
		'GIP' => 2,
		'GMD' => 2,
		'GNF' => 0,
		'GTQ' => 2,
		'GYD' => 2,
		'HKD' => 2,
		'HNL' => 2,
		'HRK' => 2,
		'HTG' => 2,
		'HUF' => 2,
		'IDR' => 0,
		'ILS' => 2,
		'IMP' => 2,
		'INR' => 2,
		'IQD' => 3,
		'IRR' => 2,
		'IRT' => 2,
		'ISK' => 2,
		'JEP' => 2,
		'JMD' => 2,
		'JOD' => 3,
		'JPY' => 0,
		'KES' => 2,
		'KGS' => 2,
		'KHR' => 2,
		'KMF' => 0,
		'KPW' => 2,
		'KRW' => 0,
		'KWD' => 3,
		'KYD' => 2,
		'KZT' => 2,
		'LAK' => 2,
		'LBP' => 2,
		'LKR' => 2,
		'LRD' => 2,
		'LSL' => 2,
		'LYD' => 3,
		'MAD' => 2,
		'MDL' => 2,
		'MGA' => 2,
		'MKD' => 2,
		'MMK' => 2,
		'MNT' => 2,
		'MOP' => 2,
		'MRO' => 2,
		'MUR' => 2,
		'MVR' => 2,
		'MWK' => 2,
		'MXN' => 2,
		'MYR' => 2,
		'MZN' => 2,
		'NAD' => 2,
		'NGN' => 2,
		'NIO' => 2,
		'NOK' => 2,
		'NPR' => 2,
		'NZD' => 2,
		'OMR' => 3,
		'PAB' => 2,
		'PEN' => 2,
		'PGK' => 2,
		'PHP' => 2,
		'PKR' => 2,
		'PLN' => 2,
		'PRB' => 2,
		'PYG' => 0,
		'QAR' => 2,
		'RON' => 2,
		'RSD' => 2,
		'RUB' => 2,
		'RWF' => 0,
		'SAR' => 2,
		'SBD' => 2,
		'SCR' => 2,
		'SDG' => 2,
		'SEK' => 2,
		'SGD' => 2,
		'SHP' => 2,
		'SLL' => 2,
		'SOS' => 2,
		'SRD' => 2,
		'SSP' => 2,
		'STD' => 2,
		'SYP' => 2,
		'SZL' => 2,
		'THB' => 2,
		'TJS' => 2,
		'TMT' => 2,
		'TND' => 3,
		'TOP' => 2,
		'TRY' => 2,
		'TTD' => 2,
		'TWD' => 2,
		'TZS' => 2,
		'UAH' => 2,
		'UGX' => 0,
		'UYU' => 2,
		'UZS' => 2,
		'VEF' => 2,
		'VES' => 2,
		'VND' => 0,
		'VUV' => 0,
		'WST' => 2,
		'XAF' => 0,
		'XCD' => 2,
		'XOF' => 0,
		'XPF' => 0,
		'YER' => 2,
		'ZAR' => 2,
		'ZMW' => 2,
	);

	/**
	 * @var array [Currency code => Currency label (with symbol)]
	 *
	 * @since 1.0
	 */
	protected $currencies = array();

	/**
	 * @var array [Currency code => Currency symbol]
	 *
	 * @since 1.0
	 */
	protected $symbols = array();

	/**
	 * @return array [Currency code => Currency label (with symbol)]
	 *
	 * @since 1.0
	 */
	public function getCurrencies() {

		if ( empty( $this->currencies ) ) {

			$currencies = $this->currenciesList();
			$symbols    = $this->getSymbols();

			// Add symbols
			foreach ( $currencies as $code => &$label ) {
				$label .= ' (' . $symbols[ $code ] . ')';
			}

			unset( $label );

			/** @since 1.0 */
			$currencies = apply_filters( 'mpa_currencies', $currencies );

			$this->currencies = $currencies;
		}

		return $this->currencies;
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function getPositions() {
		return array(
			'before'            => esc_html__( 'Before', 'motopress-appointment' ),
			'after'             => esc_html__( 'After', 'motopress-appointment' ),
			'before_with_space' => esc_html__( 'Before with space', 'motopress-appointment' ),
			'after_with_space'  => esc_html__( 'After with space', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array [Currency code => Currency symbol]
	 *
	 * @since 1.0
	 */
	public function getSymbols() {
		if ( empty( $this->symbols ) ) {
			$this->symbols = $this->symbolsList();
		}

		return $this->symbols;
	}

	/**
	 * @param string $code Currency code, like 'EUR'.
	 * @return Currency symbol or '' if no such currency.
	 *
	 * @since 1.0
	 */
	public function getSymbol( $code ) {
		$symbols = $this->getSymbols();

		return array_key_exists( $code, $symbols ) ? $symbols[ $code ] : '';
	}

	/**
	 * Converts, for example dollars to cents: 100 -> 10000
	 */
	public function getAmountInSmallestCurrencyUnits( string $currencyCode, float $amount ): int {

		$currencyDecimals = 2;

		if ( ! empty( $currencyDecimals[ $currencyCode ] ) ) {

			$currencyDecimals = $this->currencyDecimals[ $currencyCode ];
		}

		return absint( $amount * ( 10 ** $currencyDecimals ) );
	}

	/**
	 * @return array [Currency code => Currency label]
	 *
	 * @since 1.0
	 */
	protected function currenciesList() {
		return array(
			'EUR' => esc_html__( 'Euro', 'motopress-appointment' ),
			'USD' => esc_html__( 'United States (US) dollar', 'motopress-appointment' ),
			'GBP' => esc_html__( 'Pound sterling', 'motopress-appointment' ),
			'AED' => esc_html__( 'United Arab Emirates dirham', 'motopress-appointment' ),
			'AFN' => esc_html__( 'Afghan afghani', 'motopress-appointment' ),
			'ALL' => esc_html__( 'Albanian lek', 'motopress-appointment' ),
			'AMD' => esc_html__( 'Armenian dram', 'motopress-appointment' ),
			'ANG' => esc_html__( 'Netherlands Antillean guilder', 'motopress-appointment' ),
			'AOA' => esc_html__( 'Angolan kwanza', 'motopress-appointment' ),
			'ARS' => esc_html__( 'Argentine peso', 'motopress-appointment' ),
			'AUD' => esc_html__( 'Australian dollar', 'motopress-appointment' ),
			'AWG' => esc_html__( 'Aruban florin', 'motopress-appointment' ),
			'AZN' => esc_html__( 'Azerbaijani manat', 'motopress-appointment' ),
			'BAM' => esc_html__( 'Bosnia and Herzegovina convertible mark', 'motopress-appointment' ),
			'BBD' => esc_html__( 'Barbadian dollar', 'motopress-appointment' ),
			'BDT' => esc_html__( 'Bangladeshi taka', 'motopress-appointment' ),
			'BGN' => esc_html__( 'Bulgarian lev', 'motopress-appointment' ),
			'BHD' => esc_html__( 'Bahraini dinar', 'motopress-appointment' ),
			'BIF' => esc_html__( 'Burundian franc', 'motopress-appointment' ),
			'BMD' => esc_html__( 'Bermudian dollar', 'motopress-appointment' ),
			'BND' => esc_html__( 'Brunei dollar', 'motopress-appointment' ),
			'BOB' => esc_html__( 'Bolivian boliviano', 'motopress-appointment' ),
			'BRL' => esc_html__( 'Brazilian real', 'motopress-appointment' ),
			'BSD' => esc_html__( 'Bahamian dollar', 'motopress-appointment' ),
			'BTC' => esc_html__( 'Bitcoin', 'motopress-appointment' ),
			'BTN' => esc_html__( 'Bhutanese ngultrum', 'motopress-appointment' ),
			'BWP' => esc_html__( 'Botswana pula', 'motopress-appointment' ),
			'BYR' => esc_html__( 'Belarusian ruble (old)', 'motopress-appointment' ),
			'BYN' => esc_html__( 'Belarusian ruble', 'motopress-appointment' ),
			'BZD' => esc_html__( 'Belize dollar', 'motopress-appointment' ),
			'CAD' => esc_html__( 'Canadian dollar', 'motopress-appointment' ),
			'CDF' => esc_html__( 'Congolese franc', 'motopress-appointment' ),
			'CHF' => esc_html__( 'Swiss franc', 'motopress-appointment' ),
			'CLP' => esc_html__( 'Chilean peso', 'motopress-appointment' ),
			'CNY' => esc_html__( 'Chinese yuan', 'motopress-appointment' ),
			'COP' => esc_html__( 'Colombian peso', 'motopress-appointment' ),
			'CRC' => esc_html__( 'Costa Rican col&oacute;n', 'motopress-appointment' ),
			'CUC' => esc_html__( 'Cuban convertible peso', 'motopress-appointment' ),
			'CUP' => esc_html__( 'Cuban peso', 'motopress-appointment' ),
			'CVE' => esc_html__( 'Cape Verdean escudo', 'motopress-appointment' ),
			'CZK' => esc_html__( 'Czech koruna', 'motopress-appointment' ),
			'DJF' => esc_html__( 'Djiboutian franc', 'motopress-appointment' ),
			'DKK' => esc_html__( 'Danish krone', 'motopress-appointment' ),
			'DOP' => esc_html__( 'Dominican peso', 'motopress-appointment' ),
			'DZD' => esc_html__( 'Algerian dinar', 'motopress-appointment' ),
			'EGP' => esc_html__( 'Egyptian pound', 'motopress-appointment' ),
			'ERN' => esc_html__( 'Eritrean nakfa', 'motopress-appointment' ),
			'ETB' => esc_html__( 'Ethiopian birr', 'motopress-appointment' ),
			'FJD' => esc_html__( 'Fijian dollar', 'motopress-appointment' ),
			'FKP' => esc_html__( 'Falkland Islands pound', 'motopress-appointment' ),
			'GEL' => esc_html__( 'Georgian lari', 'motopress-appointment' ),
			'GGP' => esc_html__( 'Guernsey pound', 'motopress-appointment' ),
			'GHS' => esc_html__( 'Ghana cedi', 'motopress-appointment' ),
			'GIP' => esc_html__( 'Gibraltar pound', 'motopress-appointment' ),
			'GMD' => esc_html__( 'Gambian dalasi', 'motopress-appointment' ),
			'GNF' => esc_html__( 'Guinean franc', 'motopress-appointment' ),
			'GTQ' => esc_html__( 'Guatemalan quetzal', 'motopress-appointment' ),
			'GYD' => esc_html__( 'Guyanese dollar', 'motopress-appointment' ),
			'HKD' => esc_html__( 'Hong Kong dollar', 'motopress-appointment' ),
			'HNL' => esc_html__( 'Honduran lempira', 'motopress-appointment' ),
			'HRK' => esc_html__( 'Croatian kuna', 'motopress-appointment' ),
			'HTG' => esc_html__( 'Haitian gourde', 'motopress-appointment' ),
			'HUF' => esc_html__( 'Hungarian forint', 'motopress-appointment' ),
			'IDR' => esc_html__( 'Indonesian rupiah', 'motopress-appointment' ),
			'ILS' => esc_html__( 'Israeli new shekel', 'motopress-appointment' ),
			'IMP' => esc_html__( 'Manx pound', 'motopress-appointment' ),
			'INR' => esc_html__( 'Indian rupee', 'motopress-appointment' ),
			'IQD' => esc_html__( 'Iraqi dinar', 'motopress-appointment' ),
			'IRR' => esc_html__( 'Iranian rial', 'motopress-appointment' ),
			'IRT' => esc_html__( 'Iranian toman', 'motopress-appointment' ),
			'ISK' => esc_html__( 'Icelandic kr&oacute;na', 'motopress-appointment' ),
			'JEP' => esc_html__( 'Jersey pound', 'motopress-appointment' ),
			'JMD' => esc_html__( 'Jamaican dollar', 'motopress-appointment' ),
			'JOD' => esc_html__( 'Jordanian dinar', 'motopress-appointment' ),
			'JPY' => esc_html__( 'Japanese yen', 'motopress-appointment' ),
			'KES' => esc_html__( 'Kenyan shilling', 'motopress-appointment' ),
			'KGS' => esc_html__( 'Kyrgyzstani som', 'motopress-appointment' ),
			'KHR' => esc_html__( 'Cambodian riel', 'motopress-appointment' ),
			'KMF' => esc_html__( 'Comorian franc', 'motopress-appointment' ),
			'KPW' => esc_html__( 'North Korean won', 'motopress-appointment' ),
			'KRW' => esc_html__( 'South Korean won', 'motopress-appointment' ),
			'KWD' => esc_html__( 'Kuwaiti dinar', 'motopress-appointment' ),
			'KYD' => esc_html__( 'Cayman Islands dollar', 'motopress-appointment' ),
			'KZT' => esc_html__( 'Kazakhstani tenge', 'motopress-appointment' ),
			'LAK' => esc_html__( 'Lao kip', 'motopress-appointment' ),
			'LBP' => esc_html__( 'Lebanese pound', 'motopress-appointment' ),
			'LKR' => esc_html__( 'Sri Lankan rupee', 'motopress-appointment' ),
			'LRD' => esc_html__( 'Liberian dollar', 'motopress-appointment' ),
			'LSL' => esc_html__( 'Lesotho loti', 'motopress-appointment' ),
			'LYD' => esc_html__( 'Libyan dinar', 'motopress-appointment' ),
			'MAD' => esc_html__( 'Moroccan dirham', 'motopress-appointment' ),
			'MDL' => esc_html__( 'Moldovan leu', 'motopress-appointment' ),
			'MGA' => esc_html__( 'Malagasy ariary', 'motopress-appointment' ),
			'MKD' => esc_html__( 'Macedonian denar', 'motopress-appointment' ),
			'MMK' => esc_html__( 'Burmese kyat', 'motopress-appointment' ),
			'MNT' => esc_html__( 'Mongolian t&ouml;gr&ouml;g', 'motopress-appointment' ),
			'MOP' => esc_html__( 'Macanese pataca', 'motopress-appointment' ),
			'MRO' => esc_html__( 'Mauritanian ouguiya', 'motopress-appointment' ),
			'MUR' => esc_html__( 'Mauritian rupee', 'motopress-appointment' ),
			'MVR' => esc_html__( 'Maldivian rufiyaa', 'motopress-appointment' ),
			'MWK' => esc_html__( 'Malawian kwacha', 'motopress-appointment' ),
			'MXN' => esc_html__( 'Mexican peso', 'motopress-appointment' ),
			'MYR' => esc_html__( 'Malaysian ringgit', 'motopress-appointment' ),
			'MZN' => esc_html__( 'Mozambican metical', 'motopress-appointment' ),
			'NAD' => esc_html__( 'Namibian dollar', 'motopress-appointment' ),
			'NGN' => esc_html__( 'Nigerian naira', 'motopress-appointment' ),
			'NIO' => esc_html__( 'Nicaraguan c&oacute;rdoba', 'motopress-appointment' ),
			'NOK' => esc_html__( 'Norwegian krone', 'motopress-appointment' ),
			'NPR' => esc_html__( 'Nepalese rupee', 'motopress-appointment' ),
			'NZD' => esc_html__( 'New Zealand dollar', 'motopress-appointment' ),
			'OMR' => esc_html__( 'Omani rial', 'motopress-appointment' ),
			'PAB' => esc_html__( 'Panamanian balboa', 'motopress-appointment' ),
			'PEN' => esc_html__( 'Sol', 'motopress-appointment' ),
			'PGK' => esc_html__( 'Papua New Guinean kina', 'motopress-appointment' ),
			'PHP' => esc_html__( 'Philippine peso', 'motopress-appointment' ),
			'PKR' => esc_html__( 'Pakistani rupee', 'motopress-appointment' ),
			'PLN' => esc_html__( 'Polish z&#x142;oty', 'motopress-appointment' ),
			'PRB' => esc_html__( 'Transnistrian ruble', 'motopress-appointment' ),
			'PYG' => esc_html__( 'Paraguayan guaran&iacute;', 'motopress-appointment' ),
			'QAR' => esc_html__( 'Qatari riyal', 'motopress-appointment' ),
			'RON' => esc_html__( 'Romanian leu', 'motopress-appointment' ),
			'RSD' => esc_html__( 'Serbian dinar', 'motopress-appointment' ),
			'RUB' => esc_html__( 'Russian ruble', 'motopress-appointment' ),
			'RWF' => esc_html__( 'Rwandan franc', 'motopress-appointment' ),
			'SAR' => esc_html__( 'Saudi riyal', 'motopress-appointment' ),
			'SBD' => esc_html__( 'Solomon Islands dollar', 'motopress-appointment' ),
			'SCR' => esc_html__( 'Seychellois rupee', 'motopress-appointment' ),
			'SDG' => esc_html__( 'Sudanese pound', 'motopress-appointment' ),
			'SEK' => esc_html__( 'Swedish krona', 'motopress-appointment' ),
			'SGD' => esc_html__( 'Singapore dollar', 'motopress-appointment' ),
			'SHP' => esc_html__( 'Saint Helena pound', 'motopress-appointment' ),
			'SLL' => esc_html__( 'Sierra Leonean leone', 'motopress-appointment' ),
			'SOS' => esc_html__( 'Somali shilling', 'motopress-appointment' ),
			'SRD' => esc_html__( 'Surinamese dollar', 'motopress-appointment' ),
			'SSP' => esc_html__( 'South Sudanese pound', 'motopress-appointment' ),
			'STD' => esc_html__( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'motopress-appointment' ),
			'SYP' => esc_html__( 'Syrian pound', 'motopress-appointment' ),
			'SZL' => esc_html__( 'Swazi lilangeni', 'motopress-appointment' ),
			'THB' => esc_html__( 'Thai baht', 'motopress-appointment' ),
			'TJS' => esc_html__( 'Tajikistani somoni', 'motopress-appointment' ),
			'TMT' => esc_html__( 'Turkmenistan manat', 'motopress-appointment' ),
			'TND' => esc_html__( 'Tunisian dinar', 'motopress-appointment' ),
			'TOP' => esc_html__( 'Tongan pa&#x2bb;anga', 'motopress-appointment' ),
			'TRY' => esc_html__( 'Turkish lira', 'motopress-appointment' ),
			'TTD' => esc_html__( 'Trinidad and Tobago dollar', 'motopress-appointment' ),
			'TWD' => esc_html__( 'New Taiwan dollar', 'motopress-appointment' ),
			'TZS' => esc_html__( 'Tanzanian shilling', 'motopress-appointment' ),
			'UAH' => esc_html__( 'Ukrainian hryvnia', 'motopress-appointment' ),
			'UGX' => esc_html__( 'Ugandan shilling', 'motopress-appointment' ),
			'UYU' => esc_html__( 'Uruguayan peso', 'motopress-appointment' ),
			'UZS' => esc_html__( 'Uzbekistani som', 'motopress-appointment' ),
			'VEF' => esc_html__( 'Venezuelan bol&iacute;var', 'motopress-appointment' ),
			'VES' => esc_html__( 'Bol&iacute;var soberano', 'motopress-appointment' ),
			'VND' => esc_html__( 'Vietnamese &#x111;&#x1ed3;ng', 'motopress-appointment' ),
			'VUV' => esc_html__( 'Vanuatu vatu', 'motopress-appointment' ),
			'WST' => esc_html__( 'Samoan t&#x101;l&#x101;', 'motopress-appointment' ),
			'XAF' => esc_html__( 'Central African CFA franc', 'motopress-appointment' ),
			'XCD' => esc_html__( 'East Caribbean dollar', 'motopress-appointment' ),
			'XOF' => esc_html__( 'West African CFA franc', 'motopress-appointment' ),
			'XPF' => esc_html__( 'CFP franc', 'motopress-appointment' ),
			'YER' => esc_html__( 'Yemeni rial', 'motopress-appointment' ),
			'ZAR' => esc_html__( 'South African rand', 'motopress-appointment' ),
			'ZMW' => esc_html__( 'Zambian kwacha', 'motopress-appointment' ),
		);
	}

	/**
	 * @return array [Currency code => Currency symbol]
	 *
	 * @since 1.0
	 */
	protected function symbolsList() {
		return array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'Kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => 'KZT',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRO' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/.',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#x434;&#x438;&#x43d;.',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STD' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.S',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		);
	}
}
