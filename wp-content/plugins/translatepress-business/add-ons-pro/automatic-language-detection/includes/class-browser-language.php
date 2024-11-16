<?php

class TRP_IN_Browser_Language {

	/**
	 * Return language code preferred by browser from the TP published languages
	 * Return null if none matches.
	 *
	 * @param $published_languages array       TranslatePress settings['publish-languages']
	 * @param $iso_codes array                 Iso codes of the published languages
	 *
	 * @return string|null
	 */
	public function get_browser_language( $published_languages, $iso_codes ) {
		if ( empty ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return null;
		}

		/** @var TRP_Languages $trp_languages */
		$iso_codes_array = array( 'no_matches' => null ) + $iso_codes;

		// First select from browser languages, the preferred language among all the iso codes of the settings available languages.
		$matched_iso_code = $this->preferred_language( array_values( $iso_codes_array ) );

		// Second, select from browser languages, the full language codes(country code included) matching that previous iso code.
		$matched_language_code = $this->preferred_language_code( $published_languages, $iso_codes, $matched_iso_code );

		return $matched_language_code;
	}

	/**
	 * Return an array containing only the language codes that match
	 *
	 * @param $language_code_array
	 * @param $iso_codes
	 * @param $matched_iso_code
	 *
	 * @return array
	 */
	public function matching_language_codes ( $language_code_array, $iso_codes, $matched_iso_code ){
		$return = array( 'no_matches' => null );
		foreach ( $language_code_array as $language_code ){
			if( $iso_codes[$language_code] == $matched_iso_code ) {
				$return[$language_code] = $this->convert_lowercase_dashed($language_code);
			}
		}
		return $return;
	}

	/**
	 * Return language code corresponding to the matching iso code.
	 *
	 * If more than one language codes match the iso code, select the one that fits best with the browser language preferences.
	 *
	 * @param $published_languages
	 * @param $iso_codes
	 * @param $matched_iso_code
	 *
	 * @return mixed|null
	 */
	public function preferred_language_code( $published_languages, $iso_codes, $matched_iso_code ){
		$language_array = $this->matching_language_codes( $published_languages, $iso_codes, $matched_iso_code );
		if ( count ( $language_array ) < 2 ){
			// $language_array always contains one null value
			return null;
		}
		if ( count ( $language_array ) == 2 ){
			// there is only one language code in our list that corresponds with the matched iso code
			// return the language code (the second key of the array)
			$language_array = array_slice($language_array, 1, 1);
			return key($language_array);
		}

		// See which language code matches best with browser preferences
		$preferred_language = $this->preferred_language( array_values( $language_array ) );
		$matched_language_code = array_search( $preferred_language, $language_array );

		if ( ( $matched_language_code == 'no_matches' ) && ( $matched_iso_code != null ) ){
			// we didn't find matches for any specific language codes
			// return the first language code that corresponds to the iso code that matched (the second key of the array)
			$language_array = array_slice($language_array, 1, 1);
			return key( $language_array );
		}
		if ( $matched_language_code == 'no_matches' ){
			$matched_language_code = null;
		}
		return $matched_language_code;
	}

	/**
	 * Determine which language out of an available set the user prefers most
	 * https://stackoverflow.com/a/6038460
	 *
	 * @param $available_languages              array with language-tag-strings (must be lowercase) that are available
	 * @param string $http_accept_language      a HTTP_ACCEPT_LANGUAGE string (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out)
	 *
	 * @return string
	 */
	public function preferred_language( $available_languages, $http_accept_language = "auto" ) {
		// if $http_accept_language was left out, read it from the HTTP-Header
		if ( $http_accept_language == "auto" ) {
			$http_accept_language = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? filter_var( $_SERVER['HTTP_ACCEPT_LANGUAGE'], FILTER_SANITIZE_STRING ) : '';
		}

		// standard  for HTTP_ACCEPT_LANGUAGE is defined under
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
		// pattern to find is therefore something like this:
		//    1#( language-range [ ";" "q" "=" qvalue ] )
		// where:
		//    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
		//    qvalue         = ( "0" [ "." 0*3DIGIT ] )
		//            | ( "1" [ "." 0*3("0") ] )
		preg_match_all( "/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
		                "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
			$http_accept_language, $hits, PREG_SET_ORDER );

		// default language (in case of no hits) is the first in the array
		$bestlang = $available_languages[0];
		$bestqval = 0;

		foreach ( $hits as $arr ) {
			// read data from the array of this hit
			$langprefix = strtolower( $arr[1] );
			if ( ! empty( $arr[3] ) ) {
				$langrange = strtolower( $arr[3] );
				$language  = $langprefix . "-" . $langrange;
			} else {
				$language = $langprefix;
			}
			$qvalue = 1.0;
			if ( ! empty( $arr[5] ) ) {
				$qvalue = floatval( $arr[5] );
			}

			// find q-maximal language
			if ( in_array( $language, $available_languages ) && ( $qvalue > $bestqval ) ) {
				$bestlang = $language;
				$bestqval = $qvalue;
			} // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
			else if ( in_array( $langprefix, $available_languages ) && ( ( $qvalue * 0.9 ) > $bestqval ) ) {
				$bestlang = $langprefix;
				$bestqval = $qvalue * 0.9;
			}
		}

		return $bestlang;
	}

	/**
	 * Return language code as the browser needs it
	 *
	 * @param $code string      Language code
	 *
	 * @return string
	 */
	public function convert_lowercase_dashed( $code ){
		$code = str_replace ( '_', '-', $code );
		$code = strtolower( $code );
		return $code;
	}
}