(function (date, mpaData, intlTelInput) {
	'use strict';

	/**
	 * @param {Array} array
	 * @return {Array}
	 *
	 * @since 1.0
	 */
	function mpa_array_unique(array) {
	  let unique = array.filter((value, index, self) => {
	    return self.indexOf(value) === index;
	  });
	  return unique;
	}

	/**
	 * @param {Array} array1
	 * @param {Array} array2
	 * @return {Array}
	 *
	 * @since 1.2.1
	 */
	function mpa_array_intersect(array1, array2) {
	  return array1.filter(value => array2.indexOf(value) != -1);
	}

	/**
	 * @since 1.4.0
	 *
	 * @param {Array} keys
	 * @param {Array} values
	 * @return {Object}
	 */
	function mpa_array_combine(keys, values) {
	  let size = Math.min(keys.length, values.length);
	  let object = {};
	  for (let i = 0; i < size; i++) {
	    object[keys[i]] = values[i];
	  }
	  return object;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param {Number} start
	 * @param {Number} end
	 * @param {Number} step Optional. 1 by default.
	 * @return {Array} An array of numbers from <code>start</code> to <code>end</code>
	 *		with step <code>step</code>.
	 */
	function mpa_array_range(start, end, step = 1) {
	  let validStep = step || 1; // No 0
	  let length = Math.abs(Math.floor((end - start) / validStep)) + 1;
	  let range = [...Array(length).keys()].map(i => i * step + start);
	  return range;
	}

	let namespace = '/motopress/appointment/v1';

	/**
	 * @param {String} route
	 * @param {Object} args Optional.
	 * @param {String} type Optional. 'GET' by default.
	 * @return {Promise}
	 *
	 * @since 1.0
	 */
	function mpa_rest_request(route, args = {}, type = 'GET') {
	  return new Promise((resolve, reject) => {
	    wp.apiRequest({
	      path: namespace + route,
	      type: type,
	      data: args
	    })
	    // Convert jqXHR object into Promise
	    .done(responseData => resolve(responseData)).fail((request, statusText) => {
	      let message = 'parsererror'; // Default response for PHP error

	      // Get error message
	      if (request.responseJSON && request.responseJSON.message) {
	        message = request.responseJSON.message;
	      } else {
	        message = `Status: ${statusText}`;
	      }
	      if (message == 'parsererror') {
	        message = 'REST request failed. Maybe PHP error on the server side. Check PHP logs.';
	      }
	      reject(new Error(message));
	    });
	  });
	}

	/**
	 * @param {String} route
	 * @param {Object} args Optional.
	 * @return {Promise}
	 *
	 * @since 1.0
	 */
	function mpa_rest_get(route, args = {}) {
	  return mpa_rest_request(route, args, 'GET');
	}

	/**
	 * @param {String} route
	 * @param {Object} args
	 * @return {Promise}
	 *
	 * @since 1.0
	 */
	function mpa_rest_post(route, args) {
	  return mpa_rest_request(route, args, 'POST');
	}

	/**
	 * @since 1.0
	 */
	class Settings {
	  /**
	   * @since 1.0
	   */
	  constructor() {
	    this.settings = this.getDefaults();
	    this.loadingPromise = this.load();
	  }

	  /**
	   * @since 1.0
	   *
	   * @access protected
	   *
	   * @return {Object}
	   */
	  getDefaults() {
	    return {
	      plugin_name: 'Appointment Booking',
	      // No translation required
	      today: '2030-01-01',
	      business_name: '',
	      default_time_step: 30,
	      default_booking_status: 'confirmed',
	      confirmation_mode: 'auto',
	      terms_page_id_for_acceptance: 0,
	      allow_multibooking: false,
	      allow_coupons: false,
	      allow_customer_account_creation: false,
	      country: '',
	      currency: 'EUR',
	      currency_symbol: '&euro;',
	      currency_position: 'before',
	      decimal_separator: '.',
	      thousand_separator: ',',
	      number_of_decimals: 2,
	      timezone: 'UTC',
	      date_format: 'F j, Y',
	      time_format: 'H:i',
	      week_starts_on: 0,
	      thumbnail_size: {
	        width: 150,
	        height: 150
	      },
	      flatpickr_locale: 'en',
	      enable_payments: false,
	      active_gateways: [],
	      reservation_received_page_url: '',
	      failed_transaction_page_url: '',
	      default_payment_gateway: ''
	    };
	  }

	  /**
	   * @return {Promise}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  load() {
	    return new Promise((resolve, reject) => {
	      mpa_rest_get('/settings').then(responseData => this.settings = responseData, error => console.error('Unable to load public settings.', error)).finally(() => resolve(this.settings));
	    });
	  }

	  /**
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  ready() {
	    return this.loadingPromise;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String}
	   */
	  getPluginName() {
	    return this.settings.plugin_name;
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @return {String} 'Y-m-d'
	   */
	  getBusinessDate() {
	    return this.settings.today;
	  }

	  /**
	   * @return {String}
	   */
	  getBusinessName() {
	    return this.settings.business_name;
	  }

	  /**
	   * @return {Number} Step for time slots (in minutes).
	   *
	   * @since 1.0
	   */
	  getTimeStep() {
	    return this.settings.default_time_step;
	  }

	  /**
	   * @return {String} 'pending' or 'confirmed'.
	   *
	   * @since 1.1.0
	   */
	  getDefaultBookingStatus() {
	    return this.settings.default_booking_status;
	  }

	  /**
	   * @since 1.5.0
	   * @return {String} auto|manual|payment
	   */
	  getConfirmationMode() {
	    return this.settings.confirmation_mode;
	  }

	  /**
	   * @since 1.10.2
	   * @return {Integer} 0 | page id
	   */
	  getTermsPageIdForAcceptance() {
	    return this.settings.terms_page_id_for_acceptance;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  isMultibookingEnabled() {
	    return this.settings.allow_multibooking;
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @return {Boolean}
	   */
	  isCouponsEnabled() {
	    return this.settings.allow_coupons;
	  }

	  /**
	   * @since 1.18.0
	   *
	   * @return {Boolean}
	   */
	  isAllowCustomerAccountCreation() {
	    return this.settings.allow_customer_account_creation;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String}
	   */
	  getCountry() {
	    return this.settings.country;
	  }

	  /**
	   * @return {String} Currency code, like "EUR".
	   *
	   * @since 1.0
	   */
	  getCurrency() {
	    return this.settings.currency;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getCurrencySymbol() {
	    return this.settings.currency_symbol;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getCurrencyPosition() {
	    return this.settings.currency_position;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getDecimalSeparator() {
	    return this.settings.decimal_separator;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getThousandSeparator() {
	    return this.settings.thousand_separator;
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.0
	   */
	  getDecimalsCount() {
	    return this.settings.number_of_decimals;
	  }

	  /**
	   * @return {string}
	   *
	   * @since 1.22.0
	   */
	  getTimezone() {
	    return this.settings.timezone;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getDateFormat() {
	    return this.settings.date_format;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getTimeFormat() {
	    return this.settings.time_format;
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.0
	   */
	  getFirstDayOfWeek() {
	    return this.settings.week_starts_on;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Object} {width, height}
	   */
	  getThumbnailSize() {
	    return this.settings.thumbnail_size;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.2.1
	   */
	  getFlatpickrLocale() {
	    return this.settings.flatpickr_locale;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Boolean}
	   */
	  isPaymentsEnabled() {
	    return this.settings.enable_payments;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String[]}
	   */
	  getActiveGateways() {
	    return this.settings.active_gateways;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String}
	   */
	  getReservationReceivedPageUrl() {
	    return this.settings.reservation_received_page_url;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String}
	   */
	  getFailedTransactionPageUrl() {
	    return this.settings.failed_transaction_page_url;
	  }

	  /**
	   * @since 1.6.2
	   *
	   * @return {String}
	   */
	  getDefaultPaymentGateway() {
	    return this.settings.default_payment_gateway;
	  }
	}

	/**
	 * @since 1.0
	 */
	class Plugin {
	  /**
	   * @since 1.0
	   */
	  constructor() {
	    this.settingsCtrl = new Settings();
	    this.loadingPromise = this.load();
	  }

	  /**
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  load() {
	    return Promise.all([this.settingsCtrl.ready()]).then(() => this);
	  }

	  /**
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  ready() {
	    return this.loadingPromise;
	  }

	  /**
	   * @since 1.0
	   */
	  settings() {
	    return this.settingsCtrl;
	  }

	  /**
	   * @since 1.0
	   */
	  static getInstance() {
	    if (Plugin.instance == undefined) {
	      Plugin.instance = new Plugin();
	    }
	    return Plugin.instance;
	  }
	}

	/**
	 * @return {Plugin}
	 *
	 * @since 1.0
	 */
	function mpapp() {
	  return Plugin.getInstance();
	}

	const localTranslate = (text, domain = '') => text;
	const localTranslateWithContext = (text, context, domain = '') => text;
	const localSprintf = (format, ...args) => {
	  let argIndex = 0;
	  return format.replace(/%([sdf])/g, (match, specifier) => {
	    if (argIndex >= args.length) return match;
	    let value = args[argIndex++];
	    switch (specifier) {
	      case 's':
	        return String(value);
	      case 'd':
	        return parseInt(value, 10);
	      case 'f':
	        return parseFloat(value);
	      default:
	        return match;
	    }
	  });
	};
	const __ = typeof wp !== 'undefined' && wp.i18n && wp.i18n.__ ? wp.i18n.__ : localTranslate;
	const _x = typeof wp !== 'undefined' && wp.i18n && wp.i18n._x ? wp.i18n._x : localTranslateWithContext;
	typeof wp !== 'undefined' && wp.i18n && wp.i18n.sprintf ? wp.i18n.sprintf : localSprintf;

	const locale = {
	  weekdays: {
	    shorthand: [__('Sun', 'motopress-appointment'), __('Mon', 'motopress-appointment'), __('Tue', 'motopress-appointment'), __('Wed', 'motopress-appointment'), __('Thu', 'motopress-appointment'), __('Fri', 'motopress-appointment'), __('Sat', 'motopress-appointment')],
	    longhand: [__('Sunday', 'motopress-appointment'), __('Monday', 'motopress-appointment'), __('Tuesday', 'motopress-appointment'), __('Wednesday', 'motopress-appointment'), __('Thursday', 'motopress-appointment'), __('Friday', 'motopress-appointment'), __('Saturday', 'motopress-appointment')]
	  },
	  months: {
	    shorthand: [__('Jan', 'motopress-appointment'), __('Feb', 'motopress-appointment'), __('Mar', 'motopress-appointment'), __('Apr', 'motopress-appointment'),
	    // Translators: Month name (short variant, like "Apr").
	    _x('May', 'Month (short)', 'motopress-appointment'), __('Jun', 'motopress-appointment'), __('Jul', 'motopress-appointment'), __('Aug', 'motopress-appointment'), __('Sep', 'motopress-appointment'), __('Oct', 'motopress-appointment'), __('Nov', 'motopress-appointment'), __('Dec', 'motopress-appointment')],
	    longhand: [__('January', 'motopress-appointment'), __('February', 'motopress-appointment'), __('March', 'motopress-appointment'), __('April', 'motopress-appointment'), _x('May', 'Month', 'motopress-appointment'), __('June', 'motopress-appointment'), __('July', 'motopress-appointment'), __('August', 'motopress-appointment'), __('September', 'motopress-appointment'), __('October', 'motopress-appointment'), __('November', 'motopress-appointment'), __('December', 'motopress-appointment')]
	  },
	  amPM: ['AM', 'PM'],
	  firstDayOfWeek: mpapp().settings().getFirstDayOfWeek()
	};

	/**
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_date_format() {
	  return mpapp().settings().getDateFormat();
	}

	/**
	 * Based on Flatpickr's formatDate().
	 *
	 * @see Flatpickr.formatDate(): https://github.com/flatpickr/flatpickr/blob/master/src/utils/formatting.ts
	 * @see PHP date formatting tokens: https://www.php.net/manual/en/datetime.format.php
	 *
	 * @param {Date|String} date
	 * @param {String} Optional. 'public', 'internal' ('Y-m-d') or custom date
	 *     format. 'public' by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_format_date(date$1, dateFormat = 'public') {
	  if (typeof date$1 == 'string') {
	    return date$1; // Already stringified
	  }

	  if (dateFormat == 'internal') {
	    return mpa_format_date(date$1, 'Y-m-d');
	  } else if (dateFormat == 'public') {
	    // https://developer.wordpress.org/block-editor/reference-guides/packages/packages-date/#format
	    return date.format(mpa_date_format(), date$1);
	  }

	  // Handle custom date format
	  let pad = (number, length = 2) => {
	    // Use at least two zeros for a padding to support milliseconds
	    return ('00' + number).slice(-length);
	  };

	  // Go through the each symbol
	  let isSlashed = false;
	  let dateString = dateFormat.split('').map(char => {
	    // Handle slashes '\'
	    if (isSlashed) {
	      isSlashed = false;
	      return char;
	    }

	    // Handle any other symbols
	    switch (char) {
	      // Handle slashes '\'
	      case '\\':
	        isSlashed = true;
	        return '';
	      // Day
	      case 'j':
	        // 1 to 31
	        return date$1.getDate();
	      case 'd':
	        // 01 to 31
	        return pad(date$1.getDate());
	      case 'D':
	        // Mon through Sun
	        return locale.weekdays.shorthand[date$1.getDay()];
	      case 'l':
	        // Monday through Sunday
	        return locale.weekdays.longhand[date$1.getDay()];
	      case 'N':
	        // 1 (Monday) to 7 (Sunday)
	        return date$1.getDay() || 7;
	      case 'w':
	        // 0 (Sunday) to 6 (Saturday)
	        return date$1.getDay();
	      case 'z':
	        // 0 to 365
	        // Thanks to Alex Turpin https://stackoverflow.com/a/8619946
	        let yearStart = new Date(date$1.getFullYear(), 0, 1);

	        // Compensate daylight savings time
	        let timezoneOffset = yearStart.getTimezoneOffset() - date$1.getTimezoneOffset();
	        let diffMilliseconds = date$1 - yearStart + timezoneOffset * 60 * 1000;
	        let millisecondsInDay = 1000 * 60 * 60 * 24;
	        return Math.floor(diffMilliseconds / millisecondsInDay);

	      // Week
	      case 'W':
	        // 1 to 52
	        // Thanks to RobG https://stackoverflow.com/a/6117889
	        // Don't modify the original date
	        let cloneDate = new Date(Date.UTC(date$1.getFullYear(), date$1.getMonth(), date$1.getDate()));
	        // Use 7 as a day number for Sunday
	        let currentDay = cloneDate.getUTCDay() || 7;
	        // Set to nearest Thursday: %current date% + 4 - %current day%
	        cloneDate.setUTCDate(cloneDate.getUTCDate() + 4 - currentDay);
	        // Get the first day of the year
	        let utcYear = new Date(Date.UTC(cloneDate.getUTCFullYear(), 0, 1));
	        // Calculate full weeks to nearest Thursday
	        let oneDayMs = 1000 * 60 * 60 * 24;
	        let weekNo = Math.ceil(((cloneDate - utcYear) / oneDayMs + 1) / 7);
	        return weekNo;

	      // Month
	      case 'F':
	        // January through December
	        return locale.months.longhand[date$1.getMonth()];
	      case 'M':
	        // Jan through Dec
	        return locale.months.shorthand[date$1.getMonth()];
	      case 'm':
	        // 01 to 12
	        return pad(date$1.getMonth() + 1);
	      case 'n':
	        // 1 to 12
	        return date$1.getMonth() + 1;
	      case 't':
	        // 28 to 31
	        // Here is a trick. The days are 1-based. So when we pass 0 as a day number
	        // then JS Date object goes a day before (the last day of the previous month).
	        // So that's how it works: we select the 0-day of the next month and then go
	        // to the last day of the previous (required) month
	        let lastDate = new Date(date$1.getFullYear(), date$1.getMonth() + 1, 0);
	        return lastDate.getDate();

	      // Year
	      case 'Y':
	        // Examples: 1999 or 2003
	        return date$1.getFullYear();
	      case 'y':
	        // Examples: 99 or 03
	        return String(date$1.getFullYear()).substring(2);
	      case 'L':
	        // 1 (it's a leap year) or 0 (otherwise)
	        return date$1.getFullYear() % 4 == 0 ? 1 : 0;

	      // Time
	      case 'A':
	        // AM or PM
	        return locale.amPM[date$1.getHours() > 11 ? 1 : 0];
	      case 'a':
	        // am or pm
	        return locale.amPM[date$1.getHours() > 11 ? 1 : 0].toLowerCase();
	      case 'H':
	        // Hours, 00 to 23
	        return pad(date$1.getHours());
	      case 'h':
	        // Hours, 01 to 12
	        return pad(date$1.getHours() % 12 || 12);
	      case 'G':
	        // Hours, 0 to 23
	        return date$1.getHours();
	      case 'g':
	        // Hours, 1 to 12
	        return date$1.getHours() % 12 || 12;
	      case 'i':
	        // Minutes, 00 to 59
	        return pad(date$1.getMinutes());
	      case 's':
	        // Seconds, 00 to 59
	        return pad(date$1.getSeconds());
	      case 'v':
	        // Milliseconds, 000 to 999
	        return pad(date$1.getMilliseconds(), 3);
	      case 'u':
	        // Microseconds, 000000 to 999999
	        return pad(date$1.getMilliseconds(), 3) + '000';
	      // Partial support

	      // Timezone
	      case 'O': // +0200
	      case 'P':
	        // +02:00
	        let offset = -date$1.getTimezoneOffset();
	        let sign = offset >= 0 ? '+' : '-';
	        let hours = Math.floor(Math.abs(offset) / 60);
	        let minutes = Math.abs(offset) % 60;
	        let separator = char == 'O' ? '' : ':';
	        return sign + pad(hours) + separator + pad(minutes);
	      case 'Z':
	        // Offset in seconds, -43200 to 50400
	        return date$1.getTimezoneOffset() * 60;

	      // Full date/time
	      case 'U':
	        // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
	        return Math.floor(date$1.getTime() / 1000);
	      case 'c':
	        // ISO 8601 date, like: 2004-02-12T15:19:21+00:00

	        // date.toISOString() will return the string with the suffix
	        // '.065Z' (milliseconds) instead of '+02:00'
	        return mpa_format_date(date$1, 'Y-m-d\\TH:i:sP');
	      case 'r':
	        // RFC 2822 date, like: Thu, 21 Dec 2000 16:01:07 +0200

	        // date.toUTCString() will return the string with the timezone
	        // identifier suffix (GMT) instead of '+0200'
	        return mpa_format_date(date$1, 'D, d M Y H:i:s O');

	      // Not supported
	      case 'S': // Ordinal suffix, like: st, nd, rd or th
	      case 'o': // ISO-8601 week-numbering year (examples: 1999 or 2003)
	      case 'B': // Swatch Internet time
	      case 'e': // Timezone identifier, like: UTC, GMT or Atlantic/Azores
	      case 'T': // Timezone abbreviation, like: EST, MDT, etc.
	      case 'I':
	        // Whether or not the date is in daylight saving time
	        return '';

	      // Undefined
	      default:
	        return char;
	    } // switch (char)
	  }) // Map each symbol
	  .join('');
	  return dateString;
	}

	/**
	 * @param {String} dateString Only the internal format is acceptable - 'Y-m-d'.
	 * @return {Date|null}
	 *
	 * @since 1.0
	 * @since 1.11.0 returns null if the date string is invalid.
	 */
	function mpa_parse_date(dateString) {
	  let date = dateString.match(mpa_validate_date_pattern());
	  if (date != null) {
	    let year = parseInt(date[1]);
	    let month = parseInt(date[2]);
	    let day = parseInt(date[3]);
	    return new Date(year, month - 1, day);
	  } else {
	    return null;
	  }
	}

	/**
	 * @return {RegExp}
	 *
	 * @since 1.0
	 */
	function mpa_validate_date_pattern() {
	  return /(\d{4})-(\d{2})-(\d{2})/;
	}

	/**
	 * @return {Date}
	 *
	 * @since 1.0
	 */
	function mpa_today() {
	  let today = new Date();
	  today.setHours(0, 0, 0, 0);
	  return today;
	}

	function getDefaultExportFromCjs (x) {
		return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
	}

	var md5$1 = {exports: {}};

	var crypt = {exports: {}};

	(function() {
	  var base64map
	      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',

	  crypt$1 = {
	    // Bit-wise rotation left
	    rotl: function(n, b) {
	      return (n << b) | (n >>> (32 - b));
	    },

	    // Bit-wise rotation right
	    rotr: function(n, b) {
	      return (n << (32 - b)) | (n >>> b);
	    },

	    // Swap big-endian to little-endian and vice versa
	    endian: function(n) {
	      // If number given, swap endian
	      if (n.constructor == Number) {
	        return crypt$1.rotl(n, 8) & 0x00FF00FF | crypt$1.rotl(n, 24) & 0xFF00FF00;
	      }

	      // Else, assume array and swap all items
	      for (var i = 0; i < n.length; i++)
	        n[i] = crypt$1.endian(n[i]);
	      return n;
	    },

	    // Generate an array of any length of random bytes
	    randomBytes: function(n) {
	      for (var bytes = []; n > 0; n--)
	        bytes.push(Math.floor(Math.random() * 256));
	      return bytes;
	    },

	    // Convert a byte array to big-endian 32-bit words
	    bytesToWords: function(bytes) {
	      for (var words = [], i = 0, b = 0; i < bytes.length; i++, b += 8)
	        words[b >>> 5] |= bytes[i] << (24 - b % 32);
	      return words;
	    },

	    // Convert big-endian 32-bit words to a byte array
	    wordsToBytes: function(words) {
	      for (var bytes = [], b = 0; b < words.length * 32; b += 8)
	        bytes.push((words[b >>> 5] >>> (24 - b % 32)) & 0xFF);
	      return bytes;
	    },

	    // Convert a byte array to a hex string
	    bytesToHex: function(bytes) {
	      for (var hex = [], i = 0; i < bytes.length; i++) {
	        hex.push((bytes[i] >>> 4).toString(16));
	        hex.push((bytes[i] & 0xF).toString(16));
	      }
	      return hex.join('');
	    },

	    // Convert a hex string to a byte array
	    hexToBytes: function(hex) {
	      for (var bytes = [], c = 0; c < hex.length; c += 2)
	        bytes.push(parseInt(hex.substr(c, 2), 16));
	      return bytes;
	    },

	    // Convert a byte array to a base-64 string
	    bytesToBase64: function(bytes) {
	      for (var base64 = [], i = 0; i < bytes.length; i += 3) {
	        var triplet = (bytes[i] << 16) | (bytes[i + 1] << 8) | bytes[i + 2];
	        for (var j = 0; j < 4; j++)
	          if (i * 8 + j * 6 <= bytes.length * 8)
	            base64.push(base64map.charAt((triplet >>> 6 * (3 - j)) & 0x3F));
	          else
	            base64.push('=');
	      }
	      return base64.join('');
	    },

	    // Convert a base-64 string to a byte array
	    base64ToBytes: function(base64) {
	      // Remove non-base-64 characters
	      base64 = base64.replace(/[^A-Z0-9+\/]/ig, '');

	      for (var bytes = [], i = 0, imod4 = 0; i < base64.length;
	          imod4 = ++i % 4) {
	        if (imod4 == 0) continue;
	        bytes.push(((base64map.indexOf(base64.charAt(i - 1))
	            & (Math.pow(2, -2 * imod4 + 8) - 1)) << (imod4 * 2))
	            | (base64map.indexOf(base64.charAt(i)) >>> (6 - imod4 * 2)));
	      }
	      return bytes;
	    }
	  };

	  crypt.exports = crypt$1;
	})();

	var cryptExports = crypt.exports;

	var charenc = {
	  // UTF-8 encoding
	  utf8: {
	    // Convert a string to a byte array
	    stringToBytes: function(str) {
	      return charenc.bin.stringToBytes(unescape(encodeURIComponent(str)));
	    },

	    // Convert a byte array to a string
	    bytesToString: function(bytes) {
	      return decodeURIComponent(escape(charenc.bin.bytesToString(bytes)));
	    }
	  },

	  // Binary encoding
	  bin: {
	    // Convert a string to a byte array
	    stringToBytes: function(str) {
	      for (var bytes = [], i = 0; i < str.length; i++)
	        bytes.push(str.charCodeAt(i) & 0xFF);
	      return bytes;
	    },

	    // Convert a byte array to a string
	    bytesToString: function(bytes) {
	      for (var str = [], i = 0; i < bytes.length; i++)
	        str.push(String.fromCharCode(bytes[i]));
	      return str.join('');
	    }
	  }
	};

	var charenc_1 = charenc;

	/*!
	 * Determine if an object is a Buffer
	 *
	 * @author   Feross Aboukhadijeh <https://feross.org>
	 * @license  MIT
	 */

	// The _isBuffer check is for Safari 5-7 support, because it's missing
	// Object.prototype.constructor. Remove this eventually
	var isBuffer_1 = function (obj) {
	  return obj != null && (isBuffer(obj) || isSlowBuffer(obj) || !!obj._isBuffer)
	};

	function isBuffer (obj) {
	  return !!obj.constructor && typeof obj.constructor.isBuffer === 'function' && obj.constructor.isBuffer(obj)
	}

	// For Node v0.10 support. Remove this eventually.
	function isSlowBuffer (obj) {
	  return typeof obj.readFloatLE === 'function' && typeof obj.slice === 'function' && isBuffer(obj.slice(0, 0))
	}

	(function(){
	  var crypt = cryptExports,
	      utf8 = charenc_1.utf8,
	      isBuffer = isBuffer_1,
	      bin = charenc_1.bin,

	  // The core
	  md5 = function (message, options) {
	    // Convert to byte array
	    if (message.constructor == String)
	      if (options && options.encoding === 'binary')
	        message = bin.stringToBytes(message);
	      else
	        message = utf8.stringToBytes(message);
	    else if (isBuffer(message))
	      message = Array.prototype.slice.call(message, 0);
	    else if (!Array.isArray(message) && message.constructor !== Uint8Array)
	      message = message.toString();
	    // else, assume byte array already

	    var m = crypt.bytesToWords(message),
	        l = message.length * 8,
	        a =  1732584193,
	        b = -271733879,
	        c = -1732584194,
	        d =  271733878;

	    // Swap endian
	    for (var i = 0; i < m.length; i++) {
	      m[i] = ((m[i] <<  8) | (m[i] >>> 24)) & 0x00FF00FF |
	             ((m[i] << 24) | (m[i] >>>  8)) & 0xFF00FF00;
	    }

	    // Padding
	    m[l >>> 5] |= 0x80 << (l % 32);
	    m[(((l + 64) >>> 9) << 4) + 14] = l;

	    // Method shortcuts
	    var FF = md5._ff,
	        GG = md5._gg,
	        HH = md5._hh,
	        II = md5._ii;

	    for (var i = 0; i < m.length; i += 16) {

	      var aa = a,
	          bb = b,
	          cc = c,
	          dd = d;

	      a = FF(a, b, c, d, m[i+ 0],  7, -680876936);
	      d = FF(d, a, b, c, m[i+ 1], 12, -389564586);
	      c = FF(c, d, a, b, m[i+ 2], 17,  606105819);
	      b = FF(b, c, d, a, m[i+ 3], 22, -1044525330);
	      a = FF(a, b, c, d, m[i+ 4],  7, -176418897);
	      d = FF(d, a, b, c, m[i+ 5], 12,  1200080426);
	      c = FF(c, d, a, b, m[i+ 6], 17, -1473231341);
	      b = FF(b, c, d, a, m[i+ 7], 22, -45705983);
	      a = FF(a, b, c, d, m[i+ 8],  7,  1770035416);
	      d = FF(d, a, b, c, m[i+ 9], 12, -1958414417);
	      c = FF(c, d, a, b, m[i+10], 17, -42063);
	      b = FF(b, c, d, a, m[i+11], 22, -1990404162);
	      a = FF(a, b, c, d, m[i+12],  7,  1804603682);
	      d = FF(d, a, b, c, m[i+13], 12, -40341101);
	      c = FF(c, d, a, b, m[i+14], 17, -1502002290);
	      b = FF(b, c, d, a, m[i+15], 22,  1236535329);

	      a = GG(a, b, c, d, m[i+ 1],  5, -165796510);
	      d = GG(d, a, b, c, m[i+ 6],  9, -1069501632);
	      c = GG(c, d, a, b, m[i+11], 14,  643717713);
	      b = GG(b, c, d, a, m[i+ 0], 20, -373897302);
	      a = GG(a, b, c, d, m[i+ 5],  5, -701558691);
	      d = GG(d, a, b, c, m[i+10],  9,  38016083);
	      c = GG(c, d, a, b, m[i+15], 14, -660478335);
	      b = GG(b, c, d, a, m[i+ 4], 20, -405537848);
	      a = GG(a, b, c, d, m[i+ 9],  5,  568446438);
	      d = GG(d, a, b, c, m[i+14],  9, -1019803690);
	      c = GG(c, d, a, b, m[i+ 3], 14, -187363961);
	      b = GG(b, c, d, a, m[i+ 8], 20,  1163531501);
	      a = GG(a, b, c, d, m[i+13],  5, -1444681467);
	      d = GG(d, a, b, c, m[i+ 2],  9, -51403784);
	      c = GG(c, d, a, b, m[i+ 7], 14,  1735328473);
	      b = GG(b, c, d, a, m[i+12], 20, -1926607734);

	      a = HH(a, b, c, d, m[i+ 5],  4, -378558);
	      d = HH(d, a, b, c, m[i+ 8], 11, -2022574463);
	      c = HH(c, d, a, b, m[i+11], 16,  1839030562);
	      b = HH(b, c, d, a, m[i+14], 23, -35309556);
	      a = HH(a, b, c, d, m[i+ 1],  4, -1530992060);
	      d = HH(d, a, b, c, m[i+ 4], 11,  1272893353);
	      c = HH(c, d, a, b, m[i+ 7], 16, -155497632);
	      b = HH(b, c, d, a, m[i+10], 23, -1094730640);
	      a = HH(a, b, c, d, m[i+13],  4,  681279174);
	      d = HH(d, a, b, c, m[i+ 0], 11, -358537222);
	      c = HH(c, d, a, b, m[i+ 3], 16, -722521979);
	      b = HH(b, c, d, a, m[i+ 6], 23,  76029189);
	      a = HH(a, b, c, d, m[i+ 9],  4, -640364487);
	      d = HH(d, a, b, c, m[i+12], 11, -421815835);
	      c = HH(c, d, a, b, m[i+15], 16,  530742520);
	      b = HH(b, c, d, a, m[i+ 2], 23, -995338651);

	      a = II(a, b, c, d, m[i+ 0],  6, -198630844);
	      d = II(d, a, b, c, m[i+ 7], 10,  1126891415);
	      c = II(c, d, a, b, m[i+14], 15, -1416354905);
	      b = II(b, c, d, a, m[i+ 5], 21, -57434055);
	      a = II(a, b, c, d, m[i+12],  6,  1700485571);
	      d = II(d, a, b, c, m[i+ 3], 10, -1894986606);
	      c = II(c, d, a, b, m[i+10], 15, -1051523);
	      b = II(b, c, d, a, m[i+ 1], 21, -2054922799);
	      a = II(a, b, c, d, m[i+ 8],  6,  1873313359);
	      d = II(d, a, b, c, m[i+15], 10, -30611744);
	      c = II(c, d, a, b, m[i+ 6], 15, -1560198380);
	      b = II(b, c, d, a, m[i+13], 21,  1309151649);
	      a = II(a, b, c, d, m[i+ 4],  6, -145523070);
	      d = II(d, a, b, c, m[i+11], 10, -1120210379);
	      c = II(c, d, a, b, m[i+ 2], 15,  718787259);
	      b = II(b, c, d, a, m[i+ 9], 21, -343485551);

	      a = (a + aa) >>> 0;
	      b = (b + bb) >>> 0;
	      c = (c + cc) >>> 0;
	      d = (d + dd) >>> 0;
	    }

	    return crypt.endian([a, b, c, d]);
	  };

	  // Auxiliary functions
	  md5._ff  = function (a, b, c, d, x, s, t) {
	    var n = a + (b & c | ~b & d) + (x >>> 0) + t;
	    return ((n << s) | (n >>> (32 - s))) + b;
	  };
	  md5._gg  = function (a, b, c, d, x, s, t) {
	    var n = a + (b & d | c & ~d) + (x >>> 0) + t;
	    return ((n << s) | (n >>> (32 - s))) + b;
	  };
	  md5._hh  = function (a, b, c, d, x, s, t) {
	    var n = a + (b ^ c ^ d) + (x >>> 0) + t;
	    return ((n << s) | (n >>> (32 - s))) + b;
	  };
	  md5._ii  = function (a, b, c, d, x, s, t) {
	    var n = a + (c ^ (b | ~d)) + (x >>> 0) + t;
	    return ((n << s) | (n >>> (32 - s))) + b;
	  };

	  // Package private blocksize
	  md5._blocksize = 16;
	  md5._digestsize = 16;

	  md5$1.exports = function (message, options) {
	    if (message === undefined || message === null)
	      throw new Error('Illegal argument ' + message);

	    var digestbytes = crypt.wordsToBytes(md5(message, options));
	    return options && options.asBytes ? digestbytes :
	        options && options.asString ? bin.bytesToString(digestbytes) :
	        crypt.bytesToHex(digestbytes);
	  };

	})();

	var md5Exports = md5$1.exports;
	var md5 = /*@__PURE__*/getDefaultExportFromCjs(md5Exports);

	/**
	 * Notice: all nullable properties must be set till the checkout.
	 *
	 * @since 1.0
	 */
	class CartItem {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    /**
	     * @since 1.4.0
	     * @var {String}
	     * @access protected
	     */
	    this.itemId = '';

	    /**
	     * @since 1.4.0
	     * @var {Service|Null}
	     */
	    this.service = null;
	    this.serviceCategories = {};

	    /**
	     * @since 1.4.0
	     * @var {Employee|Null}
	     */
	    this.employee = null;

	    /**
	     * @since 1.4.0
	     * @var {Location|Null}
	     */
	    this.location = null;

	    /**
	     * @since 1.0
	     * @var {Date|Null}
	     */
	    this.date = null;

	    /**
	     * @since 1.0
	     * @var {TimePeriod|Null}
	     */
	    this.time = null;

	    /**
	     * @since 1.0
	     * @var {Number}
	     */
	    this.capacity = 1;

	    /**
	     * An array of all available employees. For example, when no exact
	     * employee was selected on the previous steps ("- Any -").
	     *
	     * @since 1.4.0
	     * @var {Employee[]}
	     */
	    this.availableEmployees = [];

	    /**
	     * An array of all available locations. For example, when no exact
	     * location was selected on the previous steps ("- Any -").
	     *
	     * @since 1.4.0
	     * @var {Location[]}
	     */
	    this.availableLocations = [];

	    /**
	     * @since 2.0.0
	     *
	     * @var {Array} [
	     *     {
	     *         employeeId:  Number,
	     *         locationId:  Number,
	     *         minCapacity: Number,
	     *         maxCapacity: Number, // Max available (free) capacity
	     *     },
	     *     ...
	     * ]
	     */
	    this.bookingVariants = [];
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {String} itemId
	   */
	  constructor(itemId) {
	    this.setupProperties();
	    this.itemId = itemId;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Date|Null}
	   */
	  getDate() {
	    return this.date;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {TimePeriod|Null}
	   */
	  getTime() {
	    return this.time;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {String}
	   */
	  getItemId() {
	    return this.itemId;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Number[]}
	   */
	  getAvailableEmployeeIds() {
	    return this.availableEmployees.map(entity => entity.id);
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Number[]}
	   */
	  getAvailableLocationIds() {
	    return this.availableLocations.map(entity => entity.id);
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Object}
	   */
	  getAvailableIds() {
	    return {
	      // service and employee can impact to time slots availability
	      service_id: this.service !== null ? this.service.id : 0,
	      employee_id: this.employee !== null ? this.employee.id : 0,
	      employee_ids: this.getAvailableEmployeeIds(),
	      location_ids: this.getAvailableLocationIds()
	    };
	  }

	  /**
	   * @since 1.0
	   *
	   * @return {Object}
	   */
	  getIds() {
	    return {
	      service_id: this.service !== null ? this.service.id : 0,
	      employee_id: this.employee !== null ? this.employee.id : 0,
	      location_id: this.location !== null ? this.location.id : 0
	    };
	  }

	  /**
	   * @since 1.0
	   * @since 1.4.0 added the <code>fields</code> argument.
	   *
	   * @param {String} fields Optional. all|ids|period|availability. 'all' by default.
	   * @return {Object}
	   */
	  toArray(fields = 'all') {
	    if (fields === 'ids') {
	      return this.getIds();
	    } else if (fields === 'availability') {
	      return this.getAvailableIds();
	    } else if (fields === 'period') {
	      return {
	        date: this.date !== null ? mpa_format_date(this.date, 'internal') : '',
	        time: this.time !== null ? this.time.toString('internal') : ''
	      };
	    } else {
	      return jQuery.extend(this.getIds(), {
	        date: this.date !== null ? mpa_format_date(this.date, 'internal') : '',
	        time: this.time !== null ? this.time.toString('internal') : '',
	        capacity: this.capacity
	      });
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {String} fields Optional. all|ids|period. 'all' by default.
	   * @return {Boolean}
	   */
	  isSet(fields = 'all') {
	    let isSet = true;
	    if (fields === 'all' || fields === 'ids') {
	      isSet = isSet && this.service !== null && this.employee !== null && this.location !== null;
	    }
	    if (fields === 'all' || fields === 'period') {
	      isSet = isSet && this.date !== null && this.time !== null;
	    }
	    return isSet;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Date} date
	   * @param {TimePeriod} time
	   */
	  isAtTime(date, time) {
	    if (this.date !== null && this.time !== null && mpa_format_date(this.date, 'internal') == mpa_format_date(date, 'internal') && this.time.toString('internal') == time.toString('internal')) {
	      return true;
	    } else {
	      return false;
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getCapacity() {
	    return this.capacity;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getMinCapacity() {
	    return this.service !== null ? this.service.getMinCapacity(this.getEmployeeId()) : 1;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getMaxCapacity() {
	    return this.service !== null ? this.service.getMaxCapacity(this.getEmployeeId()) : 1;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getMinPossibleCapacity() {
	    if (this.service === null) {
	      return 1;
	    } else {
	      let minCapacity = this.getMaxCapacity(); // Just a starting value

	      for (let variant of this.bookingVariants) {
	        minCapacity = Math.min(minCapacity, variant.minCapacity);
	      }
	      return minCapacity;
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getMaxPossibleCapacity() {
	    if (this.service === null) {
	      return 1;
	    } else {
	      let maxCapacity = this.getMinCapacity(); // Just a starting value

	      for (let variant of this.bookingVariants) {
	        maxCapacity = Math.max(maxCapacity, variant.maxCapacity);
	      }
	      return maxCapacity;
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number[]} [1, 2, 3, ...]. May contain gaps: [1, 2, 4, 5].
	   */
	  getCapacityOptions() {
	    if (this.service === null) {
	      return [1];
	    } else {
	      let capacityOptions = [];
	      for (let variant of this.bookingVariants) {
	        capacityOptions = capacityOptions.concat(mpa_array_range(variant.minCapacity, variant.maxCapacity));
	      }
	      return mpa_array_unique(capacityOptions);
	    }
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @return {Number}
	   */
	  getPrice() {
	    if (!this.service) {
	      return 0;
	    }
	    let employeeId = this.employee ? this.employee.id : 0;
	    return this.service.getPrice(employeeId, this.capacity);
	  }

	  /**
	   * @since 1.14.0
	   *
	   * @return {Number}
	   * @param {number} priceAfterPossibleDiscount
	   */
	  getDeposit(priceAfterPossibleDiscount) {
	    let deposit = 0;
	    switch (this.service.depositType) {
	      case 'disabled':
	        deposit = priceAfterPossibleDiscount;
	        break;
	      case 'fixed':
	        deposit = this.service.depositAmount;
	        break;
	      case 'percentage':
	        deposit = priceAfterPossibleDiscount * this.service.depositAmount / 100;
	        break;
	      default:
	        deposit = priceAfterPossibleDiscount;
	    }
	    if (deposit > priceAfterPossibleDiscount) {
	      return priceAfterPossibleDiscount;
	    }
	    return deposit;
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {String} fields Optional. all|ids|availability. 'all' by default.
	   * @return {String} Snapshot MD5 hash.
	   */
	  getHash(fields = 'all') {
	    return md5(JSON.stringify(this.toArray(fields)));
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {String} hash Last saved snapshot.
	   * @param {String} fields Optional. all|ids|availability. 'all' by default.
	   * @return {Boolean}
	   */
	  didChange(hash, fields = 'all') {
	    return hash !== this.getHash(fields);
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getEmployeeId() {
	    return this.employee ? this.employee.getId() : 0;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} employeeId
	   * @return {Employee|Null}
	   */
	  getEmployee(employeeId) {
	    if (this.employee !== null && this.employee.getId() == employeeId) {
	      return this.employee;
	    } else {
	      for (let employee of this.availableEmployees) {
	        if (employee.id == employeeId) {
	          return employee;
	        }
	      }
	    }
	    return null;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getLocationId() {
	    return this.location ? this.location.getId() : 0;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} locationId
	   * @return {Location|Null}
	   */
	  getLocation(locationId) {
	    if (this.location !== null && this.location.id == locationId) {
	      return this.location;
	    } else {
	      for (let location of this.availableLocations) {
	        if (location.id == locationId) {
	          return location;
	        }
	      }
	    }
	    return null;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Service|Null}
	   */
	  getService() {
	    return this.service;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  hasMultipleAvailableEmployees() {
	    return this.availableEmployees.length > 1;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  hasMultipleAvailableLocations() {
	    return this.availableLocations.length > 1;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  hasMultipleAvailableVariants() {
	    return this.hasMultipleAvailableEmployees() || this.hasMultipleAvailableLocations();
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Service} service
	   */
	  setService(service) {
	    this.service = service;
	  }

	  /**
	   * @param {category slug: category name, ...} serviceCategories 
	   */
	  setServiceCategories(serviceCategories) {
	    this.serviceCategories = serviceCategories;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Employee|Number} employee Employee object or ID.
	   * @param {Boolean} resetAvailable Optional. True by default.
	   */
	  setEmployee(employee, resetAvailable = true) {
	    if (typeof employee === 'number') {
	      employee = this.getEmployee(employee);
	    }
	    this.employee = employee;
	    if (resetAvailable === true) {
	      this.availableEmployees = [employee];
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Employee[]} availableEmployees
	   * @param {Boolean} resetEmployee Optional. True by default.
	   */
	  setAvailableEmployees(availableEmployees, resetEmployee = true) {
	    this.availableEmployees = availableEmployees;
	    if (resetEmployee === true) {
	      this.employee = null;
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Location|Number} location Location object or ID.
	   * @param {Boolean} resetAvailable Optional. True by default.
	   */
	  setLocation(location, resetAvailable = true) {
	    if (typeof location === 'number') {
	      location = this.getLocation(location);
	    }
	    this.location = location;
	    if (resetAvailable === true) {
	      this.availableLocations = [location];
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Location[]} availableLocations
	   * @param {Boolean} resetLocation Optional. True by default.
	   */
	  setAvailableLocations(availableLocations, resetLocation = true) {
	    this.availableLocations = availableLocations;
	    if (resetLocation === true) {
	      this.location = null;
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Number} capacity
	   */
	  setCapacity(capacity) {
	    this.capacity = capacity;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Array} bookingVariants [
	   *     [
	   *         0: Employee ID,
	   *         1: Location ID,
	   *         2: Min capacity,
	   *         3: Max/free capacity,
	   *     ],
	   *     ...
	   * ]
	   */
	  setBookingVariants(bookingVariants) {
	    this.bookingVariants = [];
	    for (let variant of bookingVariants) {
	      this.bookingVariants.push({
	        employeeId: variant[0],
	        locationId: variant[1],
	        minCapacity: variant[2],
	        maxCapacity: variant[3]
	      });
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Number} capacity
	   * @return {Array|False}
	   */
	  getBookingVariantForCapacity(capacity) {
	    for (let variant of this.bookingVariants) {
	      if (capacity >= variant.minCapacity && capacity <= variant.maxCapacity) {
	        return variant;
	      }
	    }
	    return {
	      employeeId: this.getEmployeeId(),
	      locationId: this.getLocationId(),
	      minCapacity: this.getMinCapacity(),
	      maxCapacity: this.getMaxCapacity()
	    };
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Number} employeeId
	   */
	  removeBookingVariatForEmployee(employeeId) {
	    for (let i in this.bookingVariants) {
	      let variant = this.bookingVariants[i];
	      if (variant.employeeId == employeeId) {
	        this.bookingVariants.splice(i, 1);
	      }
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class Map {
	  /**
	   * @param {Object|Null} values Optional. Starting values. Null by default.
	   *
	   * @since 1.0
	   */
	  constructor(values = null) {
	    this.setupProperties();
	    if (values != null) {
	      this.merge(values);
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    this.keys = [];
	    this.values = {};
	    this.length = 0;
	  }

	  /**
	   * @param {Object} values
	   *
	   * @since 1.0
	   */
	  merge(values) {
	    for (let key in values) {
	      this.push(key, values[key]);
	    }
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @param {*} value
	   * @return {Boolean} Is this a new item (key) or a duplicate.
	   *
	   * @since 1.0
	   */
	  push(key, value) {
	    let isNew = !this.includesKey(key);

	    // Add value
	    this.values[key] = value;

	    // Add key
	    if (isNew) {
	      this.keys.push(key);
	      this.length++;
	    }
	    return isNew;
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @param {*} defaultValue Optional. Null by default.
	   * @return {*} Item value or default value.
	   *
	   * @since 1.0
	   */
	  find(key, defaultValue = null) {
	    if (this.includesKey(key)) {
	      return this.values[key];
	    } else {
	      return defaultValue;
	    }
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @param {*} defaultValue Optional. Null by default.
	   * @return {*} Next item value or default value.
	   *
	   * @since 1.9.0
	   */
	  findNext(key, defaultValue = null) {
	    let nextKey = this.findNextKey(key);
	    if (nextKey !== '') {
	      return this.values[nextKey];
	    } else {
	      return defaultValue;
	    }
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @return {String|Number|Symbol} Next or last key.
	   *
	   * @since 1.9.0
	   */
	  findNextKey(key) {
	    let index = this.keys.indexOf(key);
	    if (index === -1) {
	      return '';
	    }
	    let nextIndex = index + 1;
	    let nextKey = nextIndex < this.length ? this.keys[nextIndex] : this.keys[index];
	    return nextKey;
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @param {*} defaultValue Optional. Null by default.
	   * @return {*} Previous item value or default value.
	   *
	   * @since 1.9.0
	   */
	  findPrevious(key, defaultValue = null) {
	    let previousKey = this.findPreviousKey(key);
	    if (previousKey !== '') {
	      return this.values[previousKey];
	    } else {
	      return defaultValue;
	    }
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @return {String|Number|Symbol} Previous or first key.
	   *
	   * @since 1.9.0
	   */
	  findPreviousKey(key) {
	    let index = this.keys.indexOf(key);
	    if (index === -1) {
	      return '';
	    }
	    let previousIndex = index - 1;
	    let previousKey = previousIndex >= 0 ? this.keys[previousIndex] : this.keys[index];
	    return previousKey;
	  }

	  /**
	   * Currently it's an alias of push() and has no difference.
	   *
	   * @param {String|Number|Symbol} key
	   * @param {*} value
	   * @return {Boolean} Is this a new item (key) or a duplicate.
	   *
	   * @since 1.0
	   */
	  update(key, value) {
	    return this.push(key, value);
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @return {*} The removed value or null if there was no item with a such key.
	   *
	   * @since 1.0
	   */
	  remove(key) {
	    if (!this.includesKey(key)) {
	      return null;
	    }
	    let value = this.values[key];

	    // Remove value
	    delete this.values[key];

	    // Remove key
	    let index = this.keys.indexOf(key);
	    this.keys.splice(index, 1);

	    // Update length
	    this.length--;
	    return value;
	  }

	  /**
	   * @return {Map}
	   *
	   * @since 1.0
	   */
	  empty() {
	    this.keys = [];
	    this.values = {};
	    this.length = 0;
	    return this;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isEmpty() {
	    return this.length == 0;
	  }

	  /**
	   * @param {String|Number|Symbol} key
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  includesKey(key) {
	    return key in this.values;
	  }

	  /**
	   * @since 1.6.2
	   *
	   * @return {String|null}
	   */
	  firstKey() {
	    if (this.keys.length > 0) {
	      return this.keys[0];
	    } else {
	      return null;
	    }
	  }

	  /**
	   * @since 1.6.2
	   *
	   * @return {*|null}
	   */
	  firstValue() {
	    let firstKey = this.firstKey();
	    if (firstKey !== null) {
	      return this.values[firstKey];
	    } else {
	      return null;
	    }
	  }

	  /**
	   * @return {*}
	   *
	   * @since 1.0
	   */
	  lastValue() {
	    let lastKey = this.lastKey();
	    if (lastKey != null) {
	      return this.values[lastKey];
	    } else {
	      return null;
	    }
	  }

	  /**
	   * @return {String|Number|Symbol|Null}
	   *
	   * @since 1.0
	   */
	  lastKey() {
	    if (!this.isEmpty()) {
	      return this.keys[this.length - 1];
	    } else {
	      return null;
	    }
	  }

	  /**
	   * @return {Array}
	   *
	   * @since 1.0
	   */
	  cloneKeys() {
	    return [...this.keys];
	  }

	  /**
	   * @param {String|Number|Symbol} column
	   * @return {Array} Column values (only unique ones).
	   *
	   * @since 1.0
	   */
	  getColumn(column) {
	    let values = [];
	    for (let key of this.keys) {
	      let value = this.values[key][column];
	      if (value != undefined) {
	        if (Array.isArray(value)) {
	          // Merge arrays
	          values = values.concat(value);
	        } else {
	          // Add value to array
	          values.push(value);
	        }
	      }
	    }
	    return mpa_array_unique(values);
	  }

	  /**
	   * @param {Function} callback
	   *
	   * @since 1.0
	   */
	  forEach(callback) {
	    let i = 0;
	    for (let key of this.keys) {
	      let response = callback(this.values[key], i, key, this);
	      i++;
	      if (response === false) {
	        break;
	      }
	    }
	  }

	  /**
	   * @param {Function} callback
	   * @return {Array} May have duplicates.
	   *
	   * @since 1.0
	   */
	  map(callback) {
	    let values = [];
	    let i = 0;
	    for (let key of this.keys) {
	      values.push(callback(this.values[key], i, key, this));
	      i++;
	    }
	    return values;
	  }

	  /**
	   * @return {Array}
	   *
	   * @since 1.0
	   */
	  toArray() {
	    let values = [];
	    for (let key of this.keys) {
	      values.push(this.values[key]);
	    }
	    return values;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Number}
	   */
	  getLength() {
	    return this.length;
	  }
	}

	let globals = {};

	/**
	 * @param {*} value
	 * @param {Boolean} strict Optional. Don't count non-iterable types. False by
	 *     default.
	 * @return {Number}
	 *
	 * @since 1.0
	 */
	function mpa_count(value, strict = false) {
	  if (typeof value == 'object') {
	    if (Array.isArray(value)) {
	      return value.length;
	    } else {
	      return Object.keys(value).length;
	    }
	  } else {
	    return strict ? 0 : 1;
	  }
	}

	/**
	 * @param {*} value
	 * @param {Boolean} strict Optional. False by default.
	 * @return {Boolean}
	 *
	 * @since 1.0
	 */
	function mpa_empty(value, strict = false) {
	  if (typeof value == 'object') {
	    return mpa_count(value) == 0;
	  } else {
	    return strict ? true : !value;
	  }
	}

	/**
	 * @param {String} prefix Optional. '' by default.
	 * @param {Boolean} moreEntropy Optional. False by default.
	 * @return {String}
	 *
	 * @see https://locutus.io/php/misc/uniqid/
	 *
	 * @since 1.0
	 */
	function mpa_uniqid(prefix = '', moreEntropy = false) {
	  let mpa_format_seed = function (seed, requiredWidth) {
	    seed = parseInt(seed, 10).toString(16); // To hex string

	    if (requiredWidth < seed.length) {
	      // So long we split
	      return seed.slice(seed.length - requiredWidth);
	    } else if (requiredWidth > seed.length) {
	      // So short we pad
	      return Array(1 + (requiredWidth - seed.length)).join('0') + seed;
	    } else {
	      // Exact width
	      return seed;
	    }
	  };
	  if (!globals.uniqid_seed) {
	    // Init seed with big random int
	    globals.uniqid_seed = Math.floor(Math.random() * 0x75bcd15);
	  }
	  globals.uniqid_seed++;

	  // Start with prefix
	  let id = prefix;
	  // Add current milliseconds (hex string)
	  id += mpa_format_seed(parseInt(new Date().getTime() / 1000, 10), 8);
	  // Add seed hex string
	  id += mpa_format_seed(globals.uniqid_seed, 5);
	  if (moreEntropy) {
	    // For more entropy we add a float lowe to 10
	    id += (Math.random() * 10).toFixed(8).toString();
	  }
	  return id;
	}

	/**
	 * @since 1.0
	 */
	class Cart {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    /**
	     * @since 1.0
	     * @var {Map}
	     */
	    this.items = new Map();

	    /**
	     * @since 1.4.0 (Replaced the property <code>selectedItem</code>)
	     * @var {CartItem|Null}
	     */
	    this.activeItem = null;

	    /**
	     * @since 1.0
	     * @var {Object}
	     */
	    this.customerDetails = {
	      name: '',
	      email: '',
	      phone: ''
	    };

	    /**
	     * @since 1.5.0
	     * @var {Object}
	     */
	    this.paymentDetails = {
	      booking_id: 0,
	      gateway_id: 'none'
	    };

	    /**
	     * @since 1.11.0
	     * @var {Coupon|Null}
	     */
	    this.coupon = null;
	  }

	  /**
	   * @since 1.0
	   */
	  constructor() {
	    this.setupProperties();
	  }

	  /**
	   * @since 1.0
	   * @since 1.4.0 added the <code>itemId</code> argument.
	   * @since 1.4.0 returns <code>CartItem</code> object instead of item identifier.
	   *
	   * @param {String} itemId Optional.
	   * @return {CartItem} New item.
	   */
	  createItem(itemId = '') {
	    if (!itemId) {
	      itemId = mpa_uniqid();
	    }
	    let item = new CartItem(itemId);
	    this.items.push(itemId, item);
	    this.activeItem = item;
	    return item;
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {String} itemId
	   * @return {CartItem|Null}
	   */
	  getItem(itemId) {
	    return this.items.find(itemId);
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {CartItem|Null}
	   */
	  getActiveItem() {
	    return this.activeItem;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {String}
	   */
	  getActiveItemId() {
	    if (this.activeItem !== null) {
	      return this.activeItem.getItemId();
	    } else {
	      return '';
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Map}
	   */
	  getItems() {
	    return this.items;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Number}
	   */
	  getItemsCount() {
	    return this.items.getLength();
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {String|CartItem} item
	   */
	  setActiveItem(item) {
	    if (typeof item === 'string') {
	      this.activeItem = this.getItem(item);
	    } else {
	      this.activeItem = item;
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {String|CartItem} item
	   */
	  removeItem(item) {
	    if (typeof item === 'string') {
	      this.items.remove(item);
	    } else {
	      this.items.remove(item.getItemId());
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  isEmpty() {
	    return this.getItemsCount() === 0;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Object} {Product name: Product price}
	   */
	  getProducts() {
	    let products = [];
	    this.items.forEach(cartItem => {
	      if (cartItem.service != null) {
	        products.push({
	          name: cartItem.service.name,
	          price: cartItem.getPrice(),
	          capacity: cartItem.getCapacity(),
	          quantity_label: cartItem.getService().getQuantityLabel()
	        });
	      }
	    });
	    return products;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Array|Null} products Optional. List of products to calculate the
	   *		subtotal price. Products of the cart by default.
	   * @return {Number}
	   */
	  getSubtotalPrice(products = null) {
	    if (products === null) {
	      products = this.getProducts();
	    }
	    let subtotal = 0;
	    for (let product of products) {
	      subtotal += product.price;
	    }
	    return subtotal;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Object|Null} products Optional. List of products to calculate the
	   *		total price. Products of the cart by default.
	   * @return {Number}
	   */
	  getTotalPrice(products = null) {
	    let subtotalPrice = this.getSubtotalPrice(products);
	    if (this.hasCoupon()) {
	      let discountPrice = this.coupon.calcDiscountAmount(this);
	      return Math.max(0, subtotalPrice - discountPrice);
	    } else {
	      return subtotalPrice;
	    }
	  }

	  /**
	   * @since 1.14.0
	   * @return {Number}
	   */
	  getDeposit() {
	    let deposit = 0;
	    this.items.forEach(cartItem => {
	      // TODO: ask all prices via ajax call to avoid calculation duplication
	      let price = cartItem.getPrice();
	      if (this.hasCoupon()) {
	        price = price - this.coupon.calcDiscountForCartItem(cartItem);
	      }
	      deposit += cartItem.getDeposit(price);
	    });
	    return deposit;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Object}
	   */
	  getCustomer() {
	    return this.customerDetails;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Object}
	   */
	  getOrder() {
	    let products = this.getProducts();
	    let order = {
	      products: products,
	      subtotal: this.getSubtotalPrice(products),
	      total: this.getTotalPrice(products),
	      customer: this.getCustomer()
	    };
	    if (this.hasCoupon()) {
	      order['coupon'] = {
	        code: this.coupon.getCode(),
	        amount: this.coupon.calcDiscountAmount(this)
	      };
	    }
	    order['deposit'] = this.getDeposit();
	    return order;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Object}
	   */
	  getPaymentDetails() {
	    return this.paymentDetails;
	  }

	  /**
	   * @since 1.0
	   * @since 1.4.0 added the <code>fields</code> argument.
	   *
	   * @param {String} fields Optional. all|items. 'all' by default.
	   * @return {Object|Array}
	   */
	  toArray(fields = 'all') {
	    let cart = {
	      items: [],
	      customer: this.customerDetails
	    };

	    // Add items
	    this.items.forEach(cartItem => {
	      if (!cartItem.isSet()) {
	        return;
	      }
	      cart.items.push(cartItem.toArray());
	    });

	    // Add payment details
	    if (mpapp().settings().isPaymentsEnabled()) {
	      cart.payment_details = this.paymentDetails;
	    }

	    // Add coupon
	    if (this.hasCoupon()) {
	      cart['coupon'] = this.coupon.getCode();
	    }
	    if (fields === 'items') {
	      return cart.items;
	    } else {
	      return cart;
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} fields Optional. all|order|items. 'all' by default.
	   * @return {String} Snapshot MD5 hash.
	   */
	  getHash(fields = 'all') {
	    if (fields !== 'order') {
	      return md5(JSON.stringify(this.toArray(fields)));
	    } else {
	      return md5(JSON.stringify(this.getOrder()));
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} hash Last saved snapshot.
	   * @param {String} fields Optional. all|order|items. 'all' by default.
	   * @return {Boolean}
	   */
	  didChange(hash, fields = 'all') {
	    return hash !== this.getHash(fields);
	  }

	  /**
	   * @since 1.4.0 (Replaced the method <code>addCustomerDetails()</code>)
	   *
	   * @param {Object} customerDetails
	   */
	  setCustomerDetails(customerDetails) {
	    jQuery.extend(this.customerDetails, customerDetails);
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {Object} paymentDetails
	   */
	  setPaymentDetails(paymentDetails) {
	    jQuery.extend(this.paymentDetails, paymentDetails);
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    this.setupProperties();
	  }

	  /**
	   * @since 1.9.0
	   *
	   * @return {Date}
	   */
	  getMinDate() {
	    let minDate = null;
	    this.items.forEach(cartItem => {
	      if (!cartItem.date) {
	        return;
	      }
	      if (!minDate || minDate > cartItem.date) {
	        minDate = new Date(cartItem.date.getTime());
	      }
	    });
	    return minDate || mpa_today();
	  }

	  /**
	   * @since 1.9.0
	   *
	   * @return {Number[]}
	   */
	  getServiceIds() {
	    let serviceIds = this.items.map(cartItem => {
	      return cartItem.service != null ? cartItem.service.id : 0;
	    });
	    serviceIds = mpa_array_unique(serviceIds);
	    return serviceIds;
	  }

	  /**
	   * @since 1.9.0
	   *
	   * @param {Service[]} services
	   */
	  updateServices(services) {
	    for (let service of services) {
	      this.items.forEach(cartItem => {
	        if (cartItem.service != null && cartItem.service.id === service.id) {
	          cartItem.service = service;
	        }
	      });
	    }
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @param {Coupon} coupon
	   */
	  setCoupon(coupon) {
	    this.coupon = coupon;
	  }

	  /**
	   * @since 1.11.0
	   */
	  removeCoupon() {
	    this.coupon = null;
	  }

	  /**
	   * @since 1.11.0
	   */
	  hasCoupon() {
	    return this.coupon != null;
	  }

	  /**
	   * @since 1.11.0
	   */
	  testCoupon() {
	    if (this.hasCoupon() && !this.coupon.isApplicableForCart(this)) {
	      this.removeCoupon();
	    }
	  }
	}

	/**
	 * @abstract
	 *
	 * @since 1.0
	 */
	class AbstractEntity {
	  /**
	   * @param {Number} id
	   * @param {Object} properties Optional.
	   *
	   * @since 1.0
	   */
	  constructor(id, properties = {}) {
	    this.id = id;
	    this.setupProperties();
	    this.setupValues(properties);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {}

	  /**
	   * @param {Object} properties
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupValues(properties) {
	    for (let property in properties) {
	      this[property] = properties[property];
	    }
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.11.0
	   */
	  getId() {
	    return this.id;
	  }
	}

	/**
	 * Draft version just to present employee as an object in some code.
	 *
	 * @since 1.4.0
	 */
	class Employee extends AbstractEntity {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.name = '';
	  }
	}

	/**
	 * Draft version just to present location as an object in some code.
	 *
	 * @since 1.4.0
	 */
	class Location extends AbstractEntity {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.name = '';
	  }
	}

	/**
	 * @since 1.0
	 */
	class Service extends AbstractEntity {
	  /**
	   * @since 1.0
	   * @access protected
	   */
	  setupProperties() {
	    // Declare only the required minimum for methods. Still get all fields
	    // from REST requests
	    super.setupProperties();
	    this.name = '';
	    this.price = 0;

	    /**
	     *  @since 1.14.0
	     *  string disabled|fixed|percentage
	     */
	    this.depositType = 'disabled';
	    /**
	     *  @since 1.14.0
	     */
	    this.depositAmount = 0;
	    this.duration = 0; // Minutes

	    this.bufferTimeBefore = 0; // Minutes
	    this.bufferTimeAfter = 0; // Minutes
	    this.timeBeforeBooking = ''; // php DateInterval string P%yY%mM%dDT%hH%iM%sS
	    this.maxAdvanceTimeBeforeReservation = ''; // php DateInterval string P%yY%mM%dDT%hH%iM%sS

	    this.minCapacity = 1;
	    this.maxCapacity = 1;
	    this.multiplyPrice = false;

	    /**
	     * @since 2.0.0
	     *
	     * @var {Boolean}
	     */
	    this.isGroupServiceEnabled = false;

	    /**
	     * @since 2.0.0
	     *
	     * @var {String}
	     */
	    this.customQuantityLabel = '';
	    this.variations = {}; // {Employee ID: {employee, price, duration, min_capacity, max_capacity}}

	    /** @since 1.4.0 */
	    this.image = '';

	    /** @since 1.4.0 */
	    this.thumbnail = '';
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {String}
	   */
	  getName() {
	    return this.name;
	  }

	  /**
	   * @since 1.0
	   * @since 1.3.1 added the <code>capacity</code> argument.
	   *
	   * @param {Number} employeeId Optional. 0 by default.
	   * @param {Number} capacity Optional. 0 by default (minimum capacity of the
	   *     service).
	   * @return {Number}
	   */
	  getPrice(employeeId = 0, capacity = 0) {
	    if (!capacity) {
	      capacity = this.minCapacity;
	    }
	    let price = this.getVariation('price', employeeId, this.price);
	    if (this.multiplyPrice) {
	      price *= capacity;
	    }
	    return price;
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {Number} employeeId Optional.
	   * @return {Number}
	   */
	  getDuration(employeeId = 0) {
	    return this.getVariation('duration', employeeId, this.duration);
	  }

	  /**
	   * @since 1.3.1
	   *
	   * @param {Number} employeeId Optional.
	   * @return {Number}
	   */
	  getMinCapacity(employeeId = 0) {
	    return this.getVariation('min_capacity', employeeId, this.minCapacity);
	  }

	  /**
	   * @since 1.3.1
	   *
	   * @param {Number} employeeId Optional.
	   * @return {Number}
	   */
	  getMaxCapacity(employeeId = 0) {
	    return this.getVariation('max_capacity', employeeId, this.maxCapacity);
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   *
	   * @param {String} field
	   * @param {Number} employeeId
	   * @param {*} defaultValue
	   * @return {*}
	   */
	  getVariation(field, employeeId, defaultValue) {
	    if (employeeId in this.variations) {
	      return this.variations[employeeId][field];
	    } else {
	      return defaultValue;
	    }
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {String} name
	   */
	  setName(name) {
	    this.name = name;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Boolean}
	   */
	  isGroupService() {
	    return this.isGroupServiceEnabled;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {String}
	   */
	  getCustomQuantityLabel() {
	    return this.customQuantityLabel;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {String}
	   */
	  getQuantityLabel() {
	    if (this.customQuantityLabel !== '') {
	      return this.getCustomQuantityLabel();
	    } else {
	      return __('Clients', 'motopress-appointment');
	    }
	  }
	}

	/**
	 * @since 1.4.0
	 */
	class EntityUtils {
	  /**
	   * @since 1.4.0
	   *
	   * @param {AbstractEntity} entity
	   * @param {AbstractRepository} repository
	   * @param {Boolean} forceReload Optional. False by default.
	   * @return {Promise}
	   */
	  static loadInBackground(entity, repository, forceReload = false) {
	    return repository.findById(entity.id, forceReload).then(loadedEntity => {
	      // If got real result (not null)...
	      if (loadedEntity !== null) {
	        // ... then copy all property values to existing entity
	        for (let property in loadedEntity) {
	          entity[property] = loadedEntity[property];
	        }
	      }
	      return loadedEntity;
	    });
	  }
	}

	/**
	 * @since 1.11.0
	 */
	class Coupon extends AbstractEntity {
	  setupProperties() {
	    super.setupProperties();
	    this.status = 'new';
	    this.code = '';
	    this.description = '';
	    this.type = 'fixed';
	    this.amount = 0;
	    this.expirationDate = null;
	    this.serviceIds = [];
	    this.minDate = null;
	    this.maxDate = null;
	    this.usageLimit = 0;
	    this.usageCount = 0;
	  }

	  /**
	   * @access protected
	   *
	   * @param {Object} properties
	   */
	  setupValues(properties) {
	    // Parse dates
	    for (let dateProperty of ['expirationDate', 'minDate', 'maxDate']) {
	      let dateValue = properties[dateProperty];
	      if (dateValue != null && dateValue !== '') {
	        this[dateProperty] = mpa_parse_date(dateValue);
	      }
	      delete properties[dateProperty];
	    }

	    // Set up other properties
	    super.setupValues(properties);
	  }

	  /**
	   * @return {String}
	   */
	  getCode() {
	    return this.code;
	  }

	  /**
	   * @param {Cart} cart
	   * @return {Boolean}
	   */
	  isApplicableForCart(cart) {
	    let isApplicable = false;
	    cart.items.forEach(cartItem => {
	      if (this.isApplicableForCartItem(cartItem)) {
	        isApplicable = true;
	        return false; // Stop the cycle
	      }
	    });

	    return isApplicable;
	  }

	  /**
	   * @param {CartItem} cartItem
	   * @return {Boolean}
	   */
	  isApplicableForCartItem(cartItem) {
	    if (!cartItem.isSet()) {
	      return false;
	    }
	    if (this.serviceIds.length > 0 && this.serviceIds.indexOf(cartItem.service.id) == -1) {
	      return false;
	    }
	    if (this.minDate != null && cartItem.date < this.minDate) {
	      return false;
	    }
	    if (this.maxDate != null && cartItem.date > this.maxDate) {
	      return false;
	    }

	    // Applicable
	    return true;
	  }

	  /**
	   * @param {Cart} cart
	   * @return {Number}
	   */
	  calcDiscountAmount(cart) {
	    let discountPrice = this.calcDiscountForCart(cart);
	    return Math.min(discountPrice, cart.getSubtotalPrice());
	  }

	  /**
	   * @access protected
	   *
	   * @param {Cart} cart
	   * @return {Number}
	   */
	  calcDiscountForCart(cart) {
	    let discount = 0;
	    cart.items.forEach(cartItem => {
	      discount += this.calcDiscountForCartItem(cartItem);
	    });
	    return discount;
	  }

	  /**
	   * @access protected
	   *
	   * @param {CartItem} cartItem
	   * @return {Number}
	   */
	  calcDiscountForCartItem(cartItem) {
	    let discountPrice = 0;
	    if (this.isApplicableForCartItem(cartItem)) {
	      let itemPrice = cartItem.getPrice();
	      switch (this.type) {
	        case 'fixed':
	          discountPrice = this.amount;
	          break;
	        case 'percentage':
	          discountPrice = itemPrice * this.amount / 100;
	          break;
	      }

	      // Don't exceed with amount > 100%
	      discountPrice = Math.min(discountPrice, itemPrice);
	    }
	    return discountPrice;
	  }
	}

	/**
	 * @param {Number} number
	 * @param {Number} decimals
	 * @param {String} decimalSeparator
	 * @param {String} thousandsSeparator
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_number_format(number, decimals = 0, decimalSeparator = '.', thousandsSeparator = ',') {
	  // + Original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	  // + Improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +   Bugfix by: Michael White (http://crestidg.com)
	  let sign = '',
	    i,
	    j,
	    kw,
	    kd,
	    km;
	  if (number < 0) {
	    sign = '-';
	    number *= -1;
	  }
	  i = parseInt(number = (+number || 0).toFixed(decimals)) + '';
	  if ((j = i.length) > 3) {
	    j = j % 3;
	  } else {
	    j = 0;
	  }
	  km = j ? i.substr(0, j) + thousandsSeparator : '';
	  kw = i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousandsSeparator);
	  kd = decimals ? decimalSeparator + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : '';
	  return sign + km + kw + kd;
	}

	/**
	 * @param {String} string
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_unprefix(string) {
	  if (string.indexOf('mpa_') === 0) {
	    return string.substring(4); // Remove the public prefix
	  } else if (string.indexOf('_mpa_') === 0) {
	    return string.substring(5); // Remove the private prefix
	  } else {
	    return string; // Already without the prefix
	  }
	}

	/**
	 * Will trim '5.00' to '5', but leave '5.50' as is.
	 *
	 * @param {String} price
	 * @param {String} decimalSeparator Optional. Decimal separator from settings by
	 *     default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_trim_price(price, decimalSeparator = null) {
	  if (decimalSeparator == null) {
	    decimalSeparator = mpapp().settings().getDecimalSeparator();
	  }
	  let regex = new RegExp('\\' + decimalSeparator + '0+$'); // /\.0+$/
	  price = price.replace(regex, '');
	  return price;
	}

	/**
	 * @param {*} value
	 * @return {Boolean}
	 *
	 * @since 1.2
	 */
	function mpa_boolval(value) {
	  return !!value;
	}

	/**
	 * @param {*} value
	 * @return {Number}
	 *
	 * @since 1.2
	 */
	function mpa_intval(value) {
	  let intValue = parseInt(value);
	  if (!isNaN(intValue)) {
	    return intValue;
	  } else {
	    return value << 0;
	  }
	}

	/**
	 * @since 1.3.1
	 *
	 * @param {Number} number
	 * @param {Number} min
	 * @param {Number} max
	 * @return {Number} The number in range [min; max].
	 */
	function mpa_limit(number, min, max) {
	  return Math.max(min, Math.min(number, max));
	}

	/**
	 * @abstract
	 *
	 * @since 1.0
	 */
	class AbstractRepository {
	  /**
	   * @since 1.0
	   */
	  constructor(postType) {
	    this.postType = postType;
	    this.entityType = mpa_unprefix(postType);
	    this.savedEntities = {}; // {Entity ID: Entity instance}
	  }

	  /**
	   * @param {Number} id
	   * @param {Boolean} forceReload Optional. False by default.
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  findById(id, forceReload = false) {
	    if (!id) {
	      return Promise.resolve(null);
	    } else if (!forceReload && this.haveEntity(id) && this.getEntity(id) != null) {
	      return Promise.resolve(this.getEntity(id));
	    }
	    return this.requestEntity(id).then(entityData => {
	      let entity = this.mapRestDataToEntity(entityData);

	      // Save the most actual result
	      this.saveEntity(id, entity);
	      return entity;
	    }, error => {
	      // Rewrite the last result, even if entity == null
	      this.saveEntity(id, null);
	      return null;
	    });
	  }

	  /**
	   * @param {Number[]} ids
	   * @param {Boolean} forceReload
	   * @return {AbstractEntity[])
	   */
	  findAll(ids, forceReload = false) {
	    let loadIds = [];
	    let entities = [];
	    for (let id of ids) {
	      if (this.haveEntity(id) && !forceReload) {
	        entities.push(this.getEntity(id));
	      } else {
	        loadIds.push(id);
	      }
	    }
	    if (loadIds.length === 0) {
	      return Promise.resolve(entities);
	    }
	    return this.requestEntities(loadIds).then(rawEntities => {
	      for (let rawEntity of rawEntities) {
	        let entity = this.mapRestDataToEntity(rawEntity);
	        this.saveEntity(entity.id, entity);
	        entities.push(entity);
	      }
	      return entities;
	    }, error => []);
	  }

	  /**
	   * @param {Number} id
	   * @return {Promise}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  requestEntity(id) {
	    return mpa_rest_get(this.getRoute(), {
	      id
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @param {Number[]} ids
	   * @return {Promise}
	   */
	  requestEntities(ids) {
	    return mpa_rest_get(this.getRoute(), {
	      id: ids
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @param {Number} id
	   * @return {Boolean}
	   */
	  haveEntity(id) {
	    return id in this.savedEntities;
	  }

	  /**
	   * @access protected
	   *
	   * @param {Number} id
	   * @return {AbstractEntity|null}
	   */
	  getEntity(id) {
	    return this.savedEntities[id] || null;
	  }

	  /**
	   * @param {Number} id
	   * @param {AbstractEntity|Null} entity
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  saveEntity(id, entity) {
	    this.savedEntities[id] = entity;
	  }

	  /**
	   * @abstract
	   *
	   * @param {Object} entityData
	   * @return {AbstractEntity}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  mapRestDataToEntity(entityData) {
	    return null;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getRoute() {
	    return `/${this.entityType}s`; // '/services'
	  }
	}

	/**
	 * @since 1.11.0
	 */
	class CouponRepository extends AbstractRepository {
	  /**
	   * @return {Promise} Coupon or null|Error.
	   */
	  findByCode(couponCode, suppressErrors = false) {
	    return mpa_rest_get(this.getRoute(), {
	      code: couponCode
	    }).then(couponData => {
	      let coupon = this.mapRestDataToEntity(couponData);

	      // Save the most actual result
	      this.saveEntity(coupon.getId(), coupon);
	      return coupon;
	    }, error => {
	      if (!suppressErrors) {
	        throw error;
	      } else {
	        return null;
	      }
	    });
	  }

	  /**
	   * @param {Object} entityData
	   * @return {Coupon}
	   *
	   * @access protected
	   */
	  mapRestDataToEntity(entityData) {
	    return new Coupon(entityData.id, entityData);
	  }
	}

	/**
	 * @param {Date|String} time
	 * @param {String} format Optional. 'public', 'internal' ('H:i') or custom time
	 *     format. 'public' by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_format_time(time, format = 'public') {
	  if (format == 'internal') {
	    return mpa_format_date(time, 'H:i');
	  } else if (format == 'public') {
	    return mpa_format_date(time, mpa_time_format());
	  } else {
	    return mpa_format_date(time, format);
	  }
	}

	/**
	 * @param {String} timeString Only the internal format is acceptable - 'H:i'
	 *     (the function will not check if the format is OK).
	 * @return {Date}
	 *
	 * @since 1.0
	 */
	function mpa_parse_time(timeString) {
	  let time = timeString.split(':');
	  let hours = parseInt(time[0]);
	  let minutes = parseInt(time[1]);
	  let date = mpa_today();
	  date.setHours(hours, minutes);
	  return date;
	}

	/**
	 * Public time format, set in Settings > General.
	 *
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_time_format() {
	  return mpapp().settings().getTimeFormat();
	}

	/**
	 * @param {Number} serviceId
	 * @param {Date} fromDate
	 * @param {Date} toDate
	 * @param {Object} args Optional.
	 *     @param {Number[]} args['employees']   Optional. Limited list of employee IDs.
	 *     @param {Number[]} args['locations']   Optional. Limited list of location IDs.
	 *     @param {String}   args['exclude']     Optional. Items to exclude in the format of the cart items.
	 * @return {Promise} <code>{'Y-m-d' => {Time period string => Array of [Employee ID, Location ID]}}</code>
	 *
	 * @since 1.2.1
	 */
	function mpa_time_slots(serviceId, fromDate, toDate, args) {
	  // Build new args object for REST API
	  let restArgs = {
	    service_id: serviceId,
	    employee_in: args['employee_in'] ? args['employee_in'].join(',') : '',
	    location_in: args['location_in'] ? args['location_in'].join(',') : '',
	    date_from: mpa_format_date(fromDate, 'internal'),
	    date_to: mpa_format_date(toDate, 'internal'),
	    exclude_cart: args['exclude_cart'] ? args['exclude_cart'] : []
	  };

	  // Request time slots
	  return mpa_rest_get('/calendar/time', restArgs).catch(error => console.error('Failed to make time slots in mpa_time_slots().', error.message) || {});
	}

	/**
	 * @since 1.0
	 */
	class TimePeriod {
	  /**
	   * @param {Date|String} timeOrPeriod
	   * @param {Date|String|Null} endTime
	   *
	   * @since 1.0
	   */
	  constructor(timeOrPeriod, endTime = null) {
	    this.setupProperties();
	    if (endTime == null) {
	      this.parsePeriod(timeOrPeriod);
	    } else {
	      this.setStartTime(timeOrPeriod);
	      this.setEndTime(endTime);
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    this.startTime = null; // Date
	    this.endTime = null; // Date
	  }

	  /**
	   * @param {String} period
	   *
	   * @since 1.0
	   */
	  parsePeriod(period) {
	    // Explode '08:00 - 14:00' into ['08:00', '14:00']
	    let time = period.split(' - ');
	    this.setStartTime(time[0]);
	    this.setEndTime(time[1]);
	  }

	  /**
	   * @param {Date|String} startTime
	   *
	   * @since 1.0
	   */
	  setStartTime(startTime) {
	    if (typeof startTime == 'string') {
	      this.startTime = mpa_parse_time(startTime);
	    } else {
	      this.startTime = new Date(startTime); // Clone
	    }
	  }

	  /**
	   * @param {Date|String} endTime
	   *
	   * @since 1.0
	   */
	  setEndTime(endTime) {
	    if (typeof endTime == 'string') {
	      this.endTime = mpa_parse_time(endTime);
	    } else {
	      this.endTime = new Date(endTime); // Clone
	    }

	    // if endTime = 00:00 hour than it could be a full day rental
	    if (0 === this.endTime.getHours() && 0 === this.endTime.getMinutes() &&
	    // IMPORTANT: we assume startTime is set before endTime!
	    this.startTime.getFullYear() === this.endTime.getFullYear() && this.startTime.getMonth() === this.endTime.getMonth() && this.startTime.getDate() === this.endTime.getDate()) {
	      this.endTime.setDate(this.endTime.getDate() + 1);
	    }
	  }

	  /**
	   * @param {Date} date
	   *
	   * @since 1.0
	   */
	  setDate(date) {
	    this.startTime.setFullYear(date.getFullYear());
	    this.startTime.setMonth(date.getMonth(), date.getDate());
	    this.endTime.setFullYear(date.getFullYear());
	    this.endTime.setMonth(date.getMonth(), date.getDate());

	    // if endTime = 00:00 hour than it could be a full day rental
	    if (0 === this.endTime.getHours() && 0 === this.endTime.getMinutes() &&
	    // IMPORTANT: we assume startTime is set before endTime!
	    this.startTime.getFullYear() === this.endTime.getFullYear() && this.startTime.getMonth() === this.endTime.getMonth() && this.startTime.getDate() === this.endTime.getDate()) {
	      this.endTime.setDate(this.endTime.getDate() + 1);
	    }
	  }

	  /**
	   * @param {TimePeriod} period
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  intersectsWith(period) {
	    return this.startTime < period.endTime && this.endTime > period.startTime;
	  }

	  /**
	   * @param {TimePeriod} period
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isSubperiodOf(period) {
	    return this.startTime >= period.startTime && this.endTime <= period.endTime;
	  }

	  /**
	   * @param {TimePeriod} period
	   *
	   * @since 1.0
	   */
	  mergePeriod(period) {
	    this.startTime.setTime(Math.min(this.startTime.getTime(), period.startTime.getTime()));
	    this.endTime.setTime(Math.max(this.endTime.getTime(), period.endTime.getTime()));
	  }

	  /**
	   * @param {TimePeriod} period
	   *
	   * @since 1.0
	   */
	  diffPeriod(period) {
	    if (this.startTime < period.startTime) {
	      this.endTime.setTime(Math.min(period.startTime.getTime(), this.endTime.getTime()));
	    } else {
	      this.startTime.setTime(Math.max(period.endTime.getTime(), this.startTime.getTime()));
	    }
	  }

	  /**
	   * @param {TimePeriod} period Must be subperiod of the current object.
	   * @return {TimePeriod[]}
	   *
	   * @since 1.0
	   */
	  splitByPeriod(period) {
	    let split = [];
	    if (period.startTime.getTime() - this.startTime.getTime() > 0) {
	      split.push(new TimePeriod(this.startTime, period.startTime));
	    }
	    if (this.endTime.getTime() - period.endTime.getTime() > 0) {
	      split.push(new TimePeriod(period.endTime, this.endTime));
	    }
	    return split;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isEmpty() {
	    return this.endTime.getTime() - this.startTime.getTime() <= 0;
	  }

	  /**
	   * @param {String} format Optional. 'public', 'short', 'internal' or custom
	   *     time format. 'public' by default.
	   * @param {String} glue Optional. ' - ' by default.
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  toString(format = 'public', glue = ' - ') {
	    // Force glue ' - ' for internal values
	    if (format == 'internal') {
	      glue = ' - ';
	    }

	    // mpa_format_time() does not support format 'short'
	    let timeFormat = format == 'short' ? 'public' : format;
	    let startTime = mpa_format_time(this.startTime, timeFormat);
	    let endTime = mpa_format_time(this.endTime, timeFormat);
	    if ('internal' !== format && 0 === this.startTime.getHours() && 0 === this.startTime.getMinutes() && startTime === endTime) {
	      return __('All day', 'motopress-appointment');
	    } else if (format == 'short' && startTime == endTime) {
	      return startTime;
	    } else {
	      return startTime + glue + endTime;
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class Reservation extends AbstractEntity {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    // Declare only the required minimum for methods. Still get all fields
	    // from REST requests
	    super.setupProperties();
	    this.serviceId = 0;
	    this.date = null; // Date

	    this.serviceTime = null; // TimePeriod
	    this.bufferTime = null; // TimePeriod
	  }

	  /**
	   * @param {Object} properties
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupValues(properties) {
	    for (let property in properties) {
	      if (property == 'date') {
	        this.setDate(properties[property]);
	      } else if (property == 'serviceTime') {
	        this.setServiceTime(properties[property]);
	      } else if (property == 'bufferTime') {
	        this.setBufferTime(properties[property]);
	      } else {
	        this[property] = properties[property];
	      }
	    }
	  }

	  /**
	   * @param {Date|String} date
	   *
	   * @since 1.0
	   */
	  setDate(date) {
	    if (typeof date == 'string') {
	      this.date = mpa_parse_date(date);
	    } else {
	      this.date = date;
	    }
	    if (this.serviceTime != null) {
	      this.serviceTime.setDate(this.date);
	    }
	    if (this.bufferTime != null) {
	      this.bufferTime.setDate(this.date);
	    }
	  }

	  /**
	   * @param {TimePeriod|String} serviceTime
	   *
	   * @since 1.0
	   */
	  setServiceTime(serviceTime) {
	    if (typeof serviceTime == 'string') {
	      this.serviceTime = new TimePeriod(serviceTime);
	    } else {
	      this.serviceTime = serviceTime;
	    }
	    if (this.date != null) {
	      this.serviceTime.setDate(this.date);
	    }
	  }

	  /**
	   * @param {TimePeriod|String} bufferTime
	   *
	   * @since 1.0
	   */
	  setBufferTime(bufferTime) {
	    if (typeof bufferTime == 'string') {
	      this.bufferTime = new TimePeriod(bufferTime);
	    } else {
	      this.bufferTime = bufferTime;
	    }
	    if (this.date != null) {
	      this.bufferTime.setDate(this.date);
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class ReservationRepository extends AbstractRepository {
	  /**
	   * @param {Number} serviceId
	   * @param {Object} args Optional.
	   *     @param {Date|String} args['from_date']
	   *     @param {Date|String} args['to_date']
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  findAllByService(serviceId, args = {}) {
	    let restArgs = {
	      service_id: serviceId
	    };

	    // Add date range
	    if (args.from_date != undefined) {
	      restArgs['from_date'] = mpa_format_date(args.from_date, 'internal');
	    }
	    if (args.to_date != undefined) {
	      restArgs['to_date'] = mpa_format_date(args.to_date, 'internal');
	    }

	    // Request reservations
	    let findPromise = mpa_rest_get('/bookings/reservations', restArgs)

	    // Save entities
	    .then(reservations => {
	      let entities = [];
	      for (let reservation of reservations) {
	        let entity = this.mapRestDataToEntity(reservation);

	        // Save entities
	        this.saveEntity(entity.id, entity);
	        entities.push(entity);
	      }
	      return entities;
	    })

	    // Log error
	    .catch(error => {
	      console.error('No reservations found.', error.message);
	      return []; // Always return array
	    });

	    return findPromise;
	  }

	  /**
	   * @param {Object} entityData
	   * @return {Reservation}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  mapRestDataToEntity(entityData) {
	    return new Reservation(entityData.id, entityData);
	  }
	}

	/**
	 * @since 1.0
	 */
	class DatePeriod {
	  /**
	   * @param {Date|String} dateOrPeriod
	   * @param {Date|String|Null} endDate
	   *
	   * @since 1.0
	   */
	  constructor(dateOrPeriod, endDate = null) {
	    this.setupProperties();
	    if (endDate == null) {
	      this.parsePeriod(dateOrPeriod);
	    } else {
	      this.setStartDate(dateOrPeriod);
	      this.setEndDate(endDate);
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    this.startDate = null; // Date
	    this.endDate = null; // Date
	  }

	  /**
	   * @param {String} period
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  parsePeriod(period) {
	    // Explode '2020-01-25 - 2020-02-10' into ['2020-01-25', '2020-02-10']
	    let dates = period.split(' - ');
	    this.setStartDate(dates[0]);
	    this.setEndDate(dates[1]);
	  }

	  /**
	   * @param {String|Date} startDate
	   *
	   * @since 1.0
	   */
	  setStartDate(startDate) {
	    this.startDate = this.convertToDate(startDate);
	  }

	  /**
	   * @param {String|Date} endDate
	   *
	   * @since 1.0
	   */
	  setEndDate(endDate) {
	    this.endDate = this.convertToDate(endDate);
	  }

	  /**
	   * @param {Date|String} input
	   * @return {Date}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  convertToDate(input) {
	    if (typeof input == 'string') {
	      return mpa_parse_date(input) || mpa_today();
	    } else {
	      return new Date(input); // Clone
	    }
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.0
	   */
	  calcDays() {
	    let diff = this.endDate.getTime() - this.startDate.getTime();

	    // Convert milliseconds to days
	    let days = Math.round(diff / 1000 / 3600 / 24); // Remember: both dates have time 00:00:00.000,
	    // so the math function is not so important

	    return days;
	  }

	  /**
	   * @param {Date|String} date
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  inPeriod(date) {
	    if (typeof date == 'string') {
	      date = mpa_parse_date(date);
	    }
	    return date != null && date >= this.startDate && date <= this.endDate;
	  }

	  /**
	   * @return {Object} {"Y-m-d" date string: Date}
	   *
	   * @since 1.0
	   */
	  splitToDates() {
	    let dates = {};
	    for (let date = new Date(this.startDate); date <= this.endDate; date.setDate(date.getDate() + 1)) {
	      let dateString = mpa_format_date(date, 'internal');
	      let dateClone = new Date(date);
	      dates[dateString] = dateClone;
	    }
	    return dates;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  toString() {
	    return mpa_format_date(this.startDate, 'internal') + ' - ' + mpa_format_date(this.endDate, 'internal');
	  }
	}

	/**
	 * @since 1.0
	 */
	class Schedule extends AbstractEntity {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    // Declare only the required minimum for methods. Still get all fields
	    // from REST requests
	    super.setupProperties();
	    this.timetable = []; // [Day index: array of {time_period, location, activity}]
	    this.workTimetable = []; // [Day index: array of {time_period, location}]
	    this.customWorkdays = []; // Array of {date_period, time_period}
	    this.daysOff = {}; // {"Y-m-d" date string: Date}
	  }

	  /**
	   * @param {Object} properties
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupValues(properties) {
	    for (let property in properties) {
	      if (property == 'timetable') {
	        this.setTimetable(properties[property]);
	      } else if (property == 'customWorkdays') {
	        this.setCustomWorkdays(properties[property]);
	      } else if (property == 'daysOff') {
	        this.setDaysOff(properties[property]);
	      } else {
	        this[property] = properties[property];
	      }
	    }
	  }

	  /**
	   * @param {Array} timetable [Day index: array of {time_period, location, activity}].
	   *
	   * @since 1.0
	   */
	  setTimetable(timetable) {
	    this.timetable = [];
	    this.workTimetable = [];

	    // For each day of a week
	    timetable.forEach(periods => {
	      let dayPeriods = [];
	      let workPeriods = [];

	      // Add periods to current day
	      periods.forEach(period => {
	        let timePeriod = new TimePeriod(period['time_period']);

	        // Add period to general list
	        dayPeriods.push({
	          time_period: timePeriod,
	          location: period['location'],
	          activity: period['activity']
	        });

	        // Add period to working list
	        if (period['activity'] == 'work') {
	          workPeriods.push({
	            time_period: timePeriod,
	            location: period['location']
	          });
	        }
	      });

	      // Save day periods
	      this.timetable.push(dayPeriods);
	      this.workTimetable.push(workPeriods);
	    });
	  }

	  /**
	   * @param {Array} customWorkdays Array of {date_period, time_period}.
	   *
	   * @since 1.0
	   */
	  setCustomWorkdays(customWorkdays) {
	    this.customWorkdays = [];
	    for (let period of customWorkdays) {
	      this.customWorkdays.push({
	        date_period: new DatePeriod(period['date_period']),
	        time_period: new TimePeriod(period['time_period'])
	      });
	    }
	  }

	  /**
	   * @param {String[]} daysOff
	   *
	   * @since 1.0
	   */
	  setDaysOff(daysOff) {
	    this.daysOff = {};

	    // For each date period
	    for (let dayOff of daysOff) {
	      let datePeriod = new DatePeriod(dayOff);
	      let datesInPeriod = datePeriod.splitToDates();
	      jQuery.extend(this.daysOff, datesInPeriod);
	    }
	  }

	  /**
	   * @param {Date|String} date
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isDayOff(date) {
	    if (typeof date != 'string') {
	      date = mpa_format_date(date, 'internal');
	    }
	    return date in this.daysOff;
	  }

	  /**
	   * @param {Date|String} date
	   * @param {Number} locationId Optional.
	   * @return {TimePeriod[]}
	   *
	   * @since 1.0
	   */
	  getWorkingHours(date, locationId = 0) {
	    // Check the days off first
	    if (this.isDayOff(date)) {
	      return [];
	    }

	    // Convert date string to Date object
	    if (typeof date == 'string') {
	      date = mpa_parse_date(date);
	    }
	    if (date == null) {
	      return [];
	    }

	    // First, get time periods from the timetable
	    let timePeriods = [];
	    let dayOfWeek = date.getDay(); // 0-6 (Sunday-Saturday)

	    for (let period of this.workTimetable[dayOfWeek]) {
	      // Filter by location ID
	      if (locationId == 0 || period['location'] == locationId) {
	        timePeriods.push(period['time_period']);
	      }
	    }

	    // Second, add custom workdays no matter the location
	    for (let period of this.customWorkdays) {
	      if (period['date_period'].inPeriod(date)) {
	        timePeriods.push(period['time_period']);
	      }
	    }
	    return timePeriods;
	  }
	}

	/**
	 * @since 1.0
	 */
	class ScheduleRepository extends AbstractRepository {
	  /**
	   * @param {Object} entityData
	   * @return {Schedule}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  mapRestDataToEntity(entityData) {
	    return new Schedule(entityData.id, entityData);
	  }
	}

	/**
	 * @since 1.0
	 */
	class ServiceRepository extends AbstractRepository {
	  /**
	   * @param {Object} entityData
	   * @return {Service}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  mapRestDataToEntity(entityData) {
	    return new Service(entityData.id, entityData);
	  }
	}

	/**
	 * @since 1.0
	 */
	class RepositoriesContainer {
	  /**
	   * @since 1.0
	   */
	  constructor() {
	    this.repositories = {};
	  }

	  /**
	   * @return {ScheduleRepository}
	   *
	   * @since 1.0
	   */
	  schedule() {
	    if (this.repositories['schedule'] == undefined) {
	      this.repositories['schedule'] = new ScheduleRepository('mpa_schedule');
	    }
	    return this.repositories['schedule'];
	  }

	  /**
	   * @return {ServiceRepository}
	   *
	   * @since 1.0
	   */
	  service() {
	    if (this.repositories['service'] == undefined) {
	      this.repositories['service'] = new ServiceRepository('mpa_service');
	    }
	    return this.repositories['service'];
	  }

	  /**
	   * @return {ReservationRepository}
	   *
	   * @since 1.0
	   */
	  reservation() {
	    if (this.repositories['reservation'] == undefined) {
	      this.repositories['reservation'] = new ReservationRepository('mpa_reservation');
	    }
	    return this.repositories['reservation'];
	  }

	  /**
	   * @return {CouponRepository}
	   *
	   * @since 1.11.0
	   */
	  coupon() {
	    if (this.repositories['coupon'] == undefined) {
	      this.repositories['coupon'] = new CouponRepository('mpa_coupon');
	    }
	    return this.repositories['coupon'];
	  }

	  /**
	   * @return {CustomerRepository}
	   *
	   * @since 1.18.0
	   */
	  customer() {
	    if (this.repositories['customer'] === undefined) {
	      this.repositories['customer'] = new CustomerRepository();
	    }
	    return this.repositories['customer'];
	  }

	  /**
	  * @return {RepositoriesContainer}
	  *
	  * @since 1.0
	  */
	  static getInstance() {
	    if (RepositoriesContainer.instance == undefined) {
	      RepositoriesContainer.instance = new RepositoriesContainer();
	    }
	    return RepositoriesContainer.instance;
	  }
	}

	// Move repository functions to separate file to prevent a dependency cycle that
	// leads to an error "Cannot read property 'default' of undefined" on mpapp()


	/**
	 * @return {RepositoriesContainer}
	 *
	 * @since 1.0
	 */
	function mpa_repositories() {
	  return RepositoriesContainer.getInstance();
	}

	let extractedServices = null; // Promise

	/**
	 * @param {Boolean} forceReload Optional. False by default.
	 * @return {Promise}
	 *
	 * @since 1.0
	 */
	function mpa_extract_available_services(forceReload = false) {
	  if (forceReload || extractedServices == null) {
	    // Load available services
	    extractedServices = mpa_rest_get('/services/available').catch(error => {
	      console.error('Unable to extract available services.');
	      return {};
	    });
	  }
	  return extractedServices;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param {*} value
	 * @return {Boolean}
	 */
	function mpa_filter_default(value) {
	  return mpa_boolval(value);
	}

	/**
	 * @since 1.4.0 (Replaced the class <code>BookingService</code>)
	 */
	class AvailabilityService {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    /**
	     * @since 1.4.0
	     * @var {Object}
	     * @access protected
	     * @see PHP function mpa_extract_available_services() to see the structure.
	     */
	    this.availability = {};

	    /**
	     * @since 1.4.0
	     * @var {Object} {Service ID: Service name}
	     */
	    this.services = {};

	    /**
	     * @since 1.4.0
	     * @var {Object} {Category slug: Category name}
	     */
	    this.serviceCategories = {};

	    /**
	     * @since 1.4.0
	     * @var {Object} {Employee ID: Employee name}
	     */
	    this.employees = {};

	    /**
	     * @since 1.4.0
	     * @var {Object} {Location ID: Location name}
	     */
	    this.locations = {};

	    /**
	     * @since 2.0.0
	     *
	     * @var {Promise|Null}
	     */
	    this.servicePromise = null;

	    /**
	     * @since 1.4.0
	     * @var {Promise}
	     * @access protected
	     */
	    this.readyPromise = null;
	  }

	  /**
	   * @since 1.4.0
	   */
	  constructor() {
	    this.setupProperties();
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {Boolean} forceReload
	   * @return {Promise}
	   */
	  load(forceReload = false) {
	    this.readyPromise = mpa_extract_available_services(forceReload).then(availability => {
	      this.setAvailability(availability);
	      return this;
	    });
	    return this.readyPromise;
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   *
	   * @param {Object} availability
	   */
	  setAvailability(availability) {
	    this.availability = availability;

	    // List available services, categories, employees and locations
	    for (let serviceId in availability) {
	      let service = availability[serviceId];

	      // List services
	      this.services[serviceId] = service.name;

	      // List categories
	      for (let categorySlug in service.categories) {
	        let categoryName = service.categories[categorySlug];
	        this.serviceCategories[categorySlug] = categoryName;
	      }

	      // List employees and locations
	      for (let employeeId in service.employees) {
	        let employee = service.employees[employeeId];

	        // List employees
	        this.employees[employeeId] = employee.name;

	        // List locations
	        for (let locationId in employee.locations) {
	          let locationName = employee.locations[locationId];
	          this.locations[locationId] = locationName;
	        }
	      }
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  isEmpty() {
	    return mpa_empty(this.availability);
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Promise}
	   */
	  ready() {
	    if (this.readyPromise === null) {
	      this.load();
	    }
	    return this.readyPromise;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @return {Promise|Null}
	   */
	  getServicePromise() {
	    return this.servicePromise;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} serviceId
	   * @param {Boolean} load Optional. Load properties of the service. True by
	   *		default.
	   * @param {Function} afterLoad
	   * @return {Service}
	   */
	  getService(serviceId, load = true, afterLoad = null) {
	    let service = new Service(serviceId);

	    // Set known values
	    if (this.services.hasOwnProperty(serviceId)) {
	      service.setName(this.services[serviceId]);
	    }

	    // Load all other properties
	    if (load === true) {
	      this.servicePromise = EntityUtils.loadInBackground(service, mpa_repositories().service());
	      if (afterLoad !== null) {
	        this.servicePromise.then(afterLoad);
	      }
	      this.servicePromise.then(() => service);
	    } else {
	      this.servicePromise = null;
	    }
	    return service;
	  }

	  /**
	   * @param {Number} serviceId
	   * @returns {category slug: category name, ...}
	   */
	  getServiceCategories(serviceId) {
	    return this.availability[serviceId].categories;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} employeeId
	   * @return {Employee}
	   */
	  getEmployee(employeeId) {
	    let employee = new Employee(employeeId);

	    // Set known values
	    if (this.employees.hasOwnProperty(employeeId)) {
	      employee.name = this.employees[employeeId];
	    }
	    return employee;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} locationId
	   * @return {Location}
	   */
	  getLocation(locationId) {
	    let location = new Location(locationId);

	    // Set known values
	    if (this.locations.hasOwnProperty(locationId)) {
	      location.name = this.locations[locationId];
	    }
	    return location;
	  }

	  /**
	   * @since 1.4.0
	   * @since 1.19.0 param locationId
	   * @since 1.19.0 param employeeId
	   *
	   * @param {String} categorySlug Optional. '' by default (all categories).
	   * @param {Number} locationId Optional. 0 by default (all locations).
	   * @param {Number} employeeId Optional. 0 by default (all employees).
	   * @return {Object} {Service ID: Service name}
	   */
	  getAvailableServices(categorySlug = '', locationId = 0, employeeId = 0) {
	    let services = {};

	    // For each service
	    for (let serviceId in this.availability) {
	      let service = this.availability[serviceId];

	      // Filter by category
	      if (categorySlug !== '' && !(categorySlug in service.categories)) {
	        continue;
	      }

	      // Filter by location
	      if (locationId !== 0) {
	        let found = false;
	        Object.keys(service.employees).forEach(employeeIndex => {
	          if (service.employees[employeeIndex].locations.hasOwnProperty(locationId)) {
	            found = true;
	          }
	        });
	        if (!found) {
	          continue;
	        }
	      }

	      // Filter by employee
	      if (employeeId !== 0 && !(employeeId in service.employees)) {
	        continue;
	      }

	      // Add service
	      services[serviceId] = service.name;
	    }
	    return services;
	  }

	  /**
	   * @return {Object} {Category slug: Category name}
	   * @since 1.4.0
	   */
	  getAvailableServiceCategories() {
	    let categories = {};

	    // For each service
	    for (let offerId in this.availability) {
	      let service = this.availability[offerId];

	      // Merge service categories
	      jQuery.extend(categories, service.categories);
	    }
	    return categories;
	  }

	  /**
	   * @since 1.4.0
	   * @since 1.19.0 param locationId
	   *
	   * @param {Number} serviceId Optional. 0 by default (all services).
	   * @param {Number} locationId Optional. 0 by default (all locations).
	   * @return {Object} {Employee ID: Employee name}
	   */
	  getAvailableEmployees(serviceId = 0, locationId = 0) {
	    let employees = {};

	    // For each service
	    for (let offerId in this.availability) {
	      // Filter by service ID
	      if (serviceId != 0 && offerId != serviceId) {
	        continue;
	      }
	      let service = this.availability[offerId];

	      // For each employee
	      for (let employeeId in service.employees) {
	        let employee = service.employees[employeeId];

	        // Filter by locationId
	        if (locationId !== 0 && !(locationId in employee.locations)) {
	          continue;
	        }

	        // Add employee
	        employees[employeeId] = employee.name;
	      }
	    }
	    return employees;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} serviceId Optional. 0 by default (all services).
	   * @param {Number} employeeId Optional. 0 by default (all employees).
	   * @return {Object} {Location ID: Location name}
	   */
	  getAvailableLocations(serviceId = 0, employeeId = 0) {
	    let locations = {};

	    // For each service
	    for (let offerId in this.availability) {
	      // Filter by service ID
	      if (serviceId != 0 && offerId != serviceId) {
	        continue;
	      }
	      let service = this.availability[offerId];

	      // For each employee
	      for (let workerId in service.employees) {
	        // Filter by employee ID
	        if (employeeId != 0 && workerId != employeeId) {
	          continue;
	        }
	        let employee = service.employees[workerId];

	        // Merge locations
	        jQuery.extend(locations, employee.locations);
	      }
	    }
	    return locations;
	  }

	  /**
	   * @since 1.19.0
	   *
	   * @param {string} categorySlug
	   * @return boolean
	   */
	  isAvailableServiceCategory(categorySlug) {
	    return this.getAvailableServiceCategories().hasOwnProperty(categorySlug);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * @param {Number} serviceId
	   * @return boolean
	   */
	  isAvailableService(serviceId) {
	    return this.getAvailableServices().hasOwnProperty(serviceId);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * @param {Number} locationId
	   * @return boolean
	   */
	  isAvailableLocation(locationId) {
	    return this.getAvailableLocations().hasOwnProperty(locationId);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * @param {Number} employeeId
	   * @return boolean
	   */
	  isAvailableEmployee(employeeId) {
	    return this.getAvailableEmployees().hasOwnProperty(employeeId);
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} serviceId
	   * @param {Number|Number[]} locationId Optional. One or move allowed location.
	   * @param {String} output Optional. ids|entities. 'ids' by default.
	   * @return {Array}
	   */
	  filterAvailableEmployees(serviceId, locationId = 0, output = 'ids') {
	    if (!(serviceId in this.availability)) {
	      // No such service in available services list
	      return [];
	    }

	    // Init allowed location IDs
	    let allowedLocations = [];
	    if (Array.isArray(locationId)) {
	      allowedLocations = locationId.filter(mpa_filter_default);
	    } else if (locationId !== 0) {
	      allowedLocations.push(locationId);
	    }

	    // Select all valid employee IDs
	    let employees = [];

	    // Filter #1: filter by service ID
	    for (let employeeId in this.availability[serviceId]['employees']) {
	      employeeId = mpa_intval(employeeId);
	      let employee = this.availability[serviceId]['employees'][employeeId];

	      // Filter #2: filter by location ID
	      if (allowedLocations.length === 0) {
	        // Any employee with any locations list is valid
	        employees.push(employeeId);
	      } else {
	        // Does employee have a valid location?
	        let employeeLocations = Object.keys(employee.locations).map(mpa_intval);
	        let allowedForEmployee = mpa_array_intersect(allowedLocations, employeeLocations);
	        if (allowedForEmployee.length > 0) {
	          employees.push(employeeId);
	        }
	      }
	    }

	    // Convert to proper output format
	    if (employees.length === 0) {
	      return [];
	    } else if (output === 'entities') {
	      return employees.map(employeeId => this.getEmployee(employeeId));
	    } else {
	      return employees;
	    }
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Number} serviceId
	   * @param {Number|Number[]} employeeId Optional. One or move allowed employee.
	   * @param {String} output Optional. ids|entities. 'ids' by default.
	   * @return {Array}
	   */
	  filterAvailableLocations(serviceId, employeeId = 0, output = 'ids') {
	    if (!(serviceId in this.availability)) {
	      // No such service in available services list
	      return [];
	    }

	    // Init allowed employee IDs
	    let allowedEmployees = [];
	    if (Array.isArray(employeeId)) {
	      allowedEmployees = employeeId.filter(mpa_filter_default);
	    } else if (employeeId !== 0) {
	      allowedEmployees.push(employeeId);
	    }

	    // Select all valid location IDs
	    let locations = [];

	    // Filter #1: filter by service ID
	    for (employeeId in this.availability[serviceId]['employees']) {
	      employeeId = mpa_intval(employeeId);

	      // Filter #2: filter by employee ID
	      if (allowedEmployees.length > 0 && allowedEmployees.indexOf(employeeId) === -1) {
	        continue;
	      }
	      let employee = this.availability[serviceId]['employees'][employeeId];

	      // Get all available location
	      for (let locationId in employee.locations) {
	        locations.push(mpa_intval(locationId));
	      }
	    }

	    // Filter duplicates
	    locations = mpa_array_unique(locations);

	    // Convert to proper output format
	    if (locations.length === 0) {
	      return [];
	    } else if (output === 'entities') {
	      return locations.map(locationId => this.getLocation(locationId));
	    } else {
	      return locations;
	    }
	  }
	}

	class BookingSteps {
	  /**
	   * @param {Cart} cart
	   */
	  constructor(cart) {
	    this.cart = cart;
	    this.steps = new Map();
	    this.currentStep = null;
	    this.currentStepId = '';
	  }

	  /**
	   * @param {AbstractStep} step
	   * @return {BookingSteps}
	   */
	  addStep(step) {
	    this.steps.push(step.stepId, step);
	    return this;
	  }

	  /**
	   * @param {String} stepId
	   * @return {AbstractStep|null}
	   */
	  getStep(stepId) {
	    return this.steps.find(stepId);
	  }
	  mount($element) {
	    this.addListeners($element);
	  }

	  /**
	   * @access protected
	   */
	  addListeners($element) {
	    $element.children('.mpa-booking-step').on('mpa_booking_step_next', (event, details) => this.onStep('next', details)).on('mpa_booking_step_back', (event, details) => this.onStep('back', details)).on('mpa_booking_step_new', (event, details) => this.onStep('new', details)).on('mpa_reset_booking', (event, details) => this.onStep('reset', details));
	  }

	  /**
	   * @access protected
	   *
	   * @param {String} move next|back|new|reset
	   * @param {Object} eventDetails
	   *		@param {String} eventDetails['step'] Optional. Step ID that initiates the switch.
	   */
	  onStep(move, eventDetails) {
	    if (eventDetails && eventDetails.step && eventDetails.step !== this.currentStepId) {
	      return; // Don't accept events from inactive steps
	    }

	    // OK, switch step
	    switch (move) {
	      case 'next':
	        this.goToNextStep();
	        break;
	      case 'back':
	        this.goToPreviousStep();
	        break;
	      case 'new':
	        this.goToFirstStep();
	        break;
	      case 'reset':
	        this.reset();
	        break;
	    }
	  }
	  goToNextStep() {
	    if (this.steps.isEmpty()) {
	      return; // Nowhere to go
	    }

	    let nextStepId = !this.currentStep ? this.steps.firstKey() : this.steps.findNextKey(this.currentStepId);
	    if (nextStepId === this.currentStepId) {
	      return; // Nowhere to go, already at the last step
	    }

	    this.switchStep(nextStepId);
	    this.skipNextHiddenSteps();
	  }

	  /**
	   * @since 1.19.0
	   */
	  skipNextHiddenSteps() {
	    if (this.currentStep === null) {
	      return;
	    }
	    this.currentStep.ready().finally(() => {
	      if (this.currentStep.isHiddenStep) {
	        // go to next step with submitting data
	        this.currentStep.submit();
	      }
	    });
	  }
	  goToPreviousStep() {
	    if (this.steps.isEmpty()) {
	      return; // Nowhere to go
	    }

	    let previousStepId = !this.currentStep ? '' : this.steps.findPreviousKey(this.currentStepId);
	    if (!previousStepId || previousStepId === this.currentStepId) {
	      return; // Nowhere to go, already at the beginning
	    }

	    this.switchStep(previousStepId);
	    this.skipPreviousHiddenSteps();
	  }

	  /**
	   * @since 1.19.0
	   */
	  skipPreviousHiddenSteps() {
	    if (this.currentStep === null) {
	      return;
	    }
	    this.currentStep.ready().finally(() => {
	      if (this.currentStep.isHiddenStep) {
	        // go to previous step
	        this.currentStep.cancel();
	      }
	    });
	  }

	  /**
	   * Goes to the first step when adding new item to the cart.
	   */
	  goToFirstStep() {
	    if (this.steps.isEmpty()) {
	      return; // Nowhere to go
	    }

	    // Add new item to the cart
	    this.cart.createItem();

	    // Reset cart item steps
	    this.steps.forEach(step => {
	      if (step.getCartContext() === 'cart item') {
	        step.reset();
	      }
	    });

	    // Switch to the first step
	    let firstStepId = this.steps.firstKey();
	    this.switchStep(firstStepId);
	    this.skipNextHiddenSteps();
	  }

	  /**
	   * @param {String} stepId
	   */
	  goToStep(stepId) {
	    this.switchStep(stepId);
	  }

	  /**
	   * @since 1.19.0
	   * @return {String} Step Id
	   */
	  getFirstVisibleStepId() {
	    let firstVisibleStepId = null;
	    this.steps.forEach(step => {
	      if (step.isHiddenStep === false) {
	        firstVisibleStepId = step.stepId;
	        return false;
	      }
	    });
	    return firstVisibleStepId;
	  }

	  /**
	   * @since 1.19.0
	   * @param {String} stepId
	   * @return {boolean}
	   */
	  isFirstVisibleStepId(stepId) {
	    return this.getFirstVisibleStepId() === stepId;
	  }

	  /**
	   * @access protected
	   *
	   * @param {String} newStepId
	   */
	  switchStep(newStepId) {
	    let newStep = this.steps.find(newStepId);
	    if (newStep == null) {
	      return; // Step not found
	    }

	    if (this.isFirstVisibleStepId(newStepId)) {
	      newStep.hideButtonBack();
	    }

	    // Hide current step
	    if (this.currentStep != null) {
	      this.currentStep.hide();
	    }
	    this.currentStep = newStep;
	    this.currentStepId = newStepId;

	    // Load and show new step
	    newStep.load();
	    newStep.ready().finally(() => newStep.show());
	  }
	  reset() {
	    this.cart.reset();

	    // goToFirstStep() will also do the cart.createItem() after the reset
	    this.goToFirstStep();

	    // Cart item steps already reseted. Reset all other steps.
	    this.steps.forEach(step => {
	      if (step.getCartContext() !== 'cart item') {
	        step.reset();
	      }
	    });
	  }
	}

	/**
	 * @abstract
	 *
	 * @since 1.0
	 */
	class AbstractStep {
	  /**
	   * @param {Object} $element jQuery element.
	   * @param {Cart} cart
	   *
	   * @since 1.0
	   */
	  constructor($element, cart) {
	    this.$element = $element;
	    this.cart = cart;
	    this.setupProperties();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    this.stepId = this.theId();
	    this.schema = this.propertiesSchema();
	    this.isActive = false;
	    this.isLoaded = false;
	    this.isHiddenStep = false;
	    this.preventReact = false; // See setProperty()
	    this.preventUpdate = false; // See setProperty()

	    // Hide buttons if one of the values is not valid. If false, then
	    // buttons will be disabled, but visible
	    this.hideButtons = false;
	    this.readyPromise = null;
	    this.$buttons = this.$element.find('.mpa-actions');
	    this.$buttonBack = this.$buttons.find('.mpa-button-back');
	    this.$buttonNext = this.$buttons.find('.mpa-button-next');
	  }

	  /**
	   * @abstract
	   *
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  theId() {
	    return 'abstract';
	  }

	  /**
	   * @return {String} 'cart'|'cart item'
	   *
	   * @since 1.9.0
	   */
	  getCartContext() {
	    return 'cart';
	  }

	  /**
	   * @return {Object} {Property name: {type, default}}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  propertiesSchema() {
	    return {};
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    this.$buttonBack.on('click', this.cancel.bind(this));
	    this.$buttonNext.on('click', this.submit.bind(this));
	  }

	  /**
	   * @since 1.0
	   */
	  load() {
	    if (!this.isLoaded) {
	      this.readyPromise = this.loadEntities();
	      this.isLoaded = true;
	    } else {
	      this.readyPromise = this.reload();
	    }
	  }

	  /**
	   * @return {Promise}
	   * @access protected
	   *
	   * @since 1.0
	   */
	  loadEntities() {
	    return Promise.resolve(this);
	  }

	  /**
	   * @return {Promise}
	   * @access protected
	   *
	   * @since 1.0
	   */
	  reload() {
	    return Promise.resolve(this);
	  }

	  /**
	   * Reset step before a new item.
	   *
	   * @since 1.4.0
	   * @abstract
	   */
	  reset() {}

	  /**
	   * @return {Promise}
	   *
	   * @since 1.0
	   */
	  ready() {
	    return this.readyPromise;
	  }

	  /**
	   * @abstract
	   *
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isValidInput() {
	    return false;
	  }

	  /**
	   * @param {String} propertyName
	   * @param {*} value
	   *
	   * @since 1.0
	   */
	  setProperty(propertyName, value) {
	    if (this.preventUpdate) {
	      return;
	    }
	    let newValue = this.validateProperty(propertyName, value);

	    // Do nothing if nothing changed
	    if (newValue === this[propertyName]) {
	      return;
	    }

	    // Is it a change of the dependent property?
	    let isChain = this.preventReact;

	    // Block excess reacts and update linked values
	    this.preventReact = true;
	    this.updateProperty(propertyName, newValue);

	    // Run react() after all changes (once)
	    if (!isChain) {
	      if (this.isActive) {
	        this.react();
	      }
	      this.preventReact = false;
	    }
	  }

	  /**
	   * @param {String} propertyName
	   *
	   * @since 1.0
	   */
	  resetProperty(propertyName) {
	    this.setProperty(propertyName);
	  }

	  /**
	   * @param {String} propertyName
	   * @param {*} value
	   * @return {*}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  validateProperty(propertyName, value) {
	    let validValue = value;
	    if (propertyName in this.schema) {
	      let propertySchema = this.schema[propertyName];
	      if (value == null) {
	        validValue = propertySchema.default;
	      } else {
	        switch (propertySchema.type) {
	          case 'bool':
	            validValue = mpa_boolval(value);
	            break;
	          case 'integer':
	            validValue = mpa_intval(value);
	            break;
	        }

	        // Check allowed options (but don't check empty values, like 0
	        // or "" - they are kind of allowed (to filter dependent fields))
	        if (!mpa_empty(validValue) && propertySchema.options != undefined) {
	          let isAllowed = propertySchema.options.indexOf(validValue) >= 0;

	          // If new value is not allowed, then don't change the value
	          if (!isAllowed) {
	            validValue = this[propertyName];
	          }
	        }
	      } // If value not null
	    } else if (value == undefined) {
	      validValue = null;
	    }
	    return validValue;
	  }

	  /**
	   * Executed only when the new value is different from the old one.
	   *
	   * @param {String} propertyName
	   * @param {*} newValue
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  updateProperty(propertyName, newValue) {
	    let oldValue = this[propertyName];
	    this[propertyName] = newValue;
	    this.afterUpdate(propertyName, newValue, oldValue);
	  }

	  /**
	   * Executed only when the new value is different from the old one.
	   *
	   * @param {String} propertyName
	   * @param {*} newValue
	   * @param {*} oldValue
	   *
	   * @access protected
	   *
	   * @since 1.0
	   * @since 1.5.0 added the <code>oldValue</code> argument.
	   */
	  afterUpdate(propertyName, newValue, oldValue) {}

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  react() {
	    let inputsValid = this.isValidInput();
	    this.$buttonNext.prop('disabled', !inputsValid);
	    if (this.hideButtons) {
	      this.$buttons.toggleClass('mpa-hide', !inputsValid);
	    }
	  }

	  /**
	   * @since 1.0
	   */
	  show() {
	    // Set active
	    this.enable();

	    // Update elements due to the state
	    this.react();

	    // Show element
	    this.$element.removeClass('mpa-hide');

	    // Hide loading GIF
	    this.readyPromise.finally(() => this.showReady());
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  showReady() {
	    // Hide loading GIF
	    this.$element.addClass('mpa-loaded');

	    // Show buttons
	    if (!this.hideButtons) {
	      this.$buttons.removeClass('mpa-hide');
	    }
	  }

	  /**
	   * @since 1.0
	   */
	  hide() {
	    // Set inactive
	    this.disable();

	    // Hide element
	    this.$element.addClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  enable() {
	    this.isActive = true;
	    this.$buttonBack.prop('disabled', false);
	    this.$buttonNext.prop('disabled', false);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  disable() {
	    this.isActive = false;
	    this.$buttonBack.prop('disabled', true);
	    this.$buttonNext.prop('disabled', true);
	  }

	  /**
	   * @since 1.0
	   */
	  cancel(event) {
	    if (typeof event !== 'undefined') {
	      event.stopPropagation();
	    }
	    if (!this.isActive) {
	      return;
	    }

	    // Stop editing
	    this.disable();

	    // Trigger as cancelled
	    this.triggerBack();
	  }

	  /**
	   * @since 1.0
	   */
	  submit(event) {
	    if (typeof event !== 'undefined') {
	      event.stopPropagation();
	    }
	    if (!this.isActive || !this.isValidInput()) {
	      return;
	    }

	    // Stop editing
	    this.disable();

	    // Submit data
	    let submitted = this.maybeSubmit();
	    if (submitted == undefined) {
	      // No response - take it as a success
	      this.triggerNext();
	    } else if (typeof submitted !== 'object') {
	      // If not a Promise
	      // Succeded or failed
	      submitted ? this.triggerNext() : this.cancelSubmission();
	    } else {
	      // Wait for a Promise
	      submitted.then(
	      // executes if submitted will be resolved
	      this.triggerNext.bind(this),
	      // executes if submitted will be rejected
	      this.cancelSubmission.bind(this));
	    }
	  }

	  /**
	   * @since 1.0
	   * @since 1.5.0 may return <code>Promise</code>.
	   *
	   * @access protected
	   *
	   * @return {*}
	   */
	  maybeSubmit() {}

	  /**
	   * @since 1.5.0
	   *
	   * @access protected
	   */
	  cancelSubmission() {
	    this.enable();
	    this.react();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  triggerBack() {
	    this.$element.trigger('mpa_booking_step_back', {
	      step: this.stepId
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  triggerNext() {
	    this.$element.trigger('mpa_booking_step_next', {
	      step: this.stepId
	    });
	  }

	  /**
	   * @since 1.19.0
	   */
	  hideButtonBack() {
	    this.$buttonBack.prop('disabled', true);
	    this.$buttonBack.toggleClass('mpa-hide', true);
	  }
	}

	class AddToCalendar {
	  /**
	   * @param {String} timezone Timezone offset in format "+00:00"
	   *
	   * @return {Number} Offset in minutes
	   */
	  static calculateTimezoneOffset(timezone) {
	    if ('UTC' === timezone) {
	      return 0;
	    }
	    const [hoursOffset, minutesOffset] = timezone.split(':').map(Number);
	    if (isNaN(hoursOffset) || isNaN(minutesOffset)) {
	      throw new Error('Unknown timezone format: ' + timezone);
	    }
	    return hoursOffset * 60 + minutesOffset;
	  }

	  /**
	   *
	   * @param {Date} date
	   * @param {Number} timezoneOffset Offset in minutes
	   * @return {Date}
	   */
	  static applyTimezoneOffset(date, timezoneOffset) {
	    const adjustedDate = new Date(date);
	    adjustedDate.setMinutes(date.getMinutes() - timezoneOffset);
	    return adjustedDate;
	  }

	  /**
	   * @param {string} timezone
	   * @return {boolean}
	   */
	  static isTimezoneProvideByIANA(timezone) {
	    const ianaPattern = /^[A-Za-z]+\/[A-Za-z_]+(\/[A-Za-z_]+)?$/;
	    return ianaPattern.test(timezone);
	  }
	  static formatDateToCalendar(date) {
	    return date.toISOString().replace(/-|:|\.\d{3}/g, '');
	  }
	  static formatDateToCalendarLocal(date) {
	    return date.toISOString().replace(/-|:|\.\d{3}|Z/g, '');
	  }
	  static formatDateForOffsetTimeZone(date, timezone) {
	    const defaultBrowserTimezoneOffset = new Date().getTimezoneOffset();
	    let adjustedDate = this.applyTimezoneOffset(date, defaultBrowserTimezoneOffset);
	    const timezoneOffset = this.calculateTimezoneOffset(timezone);
	    adjustedDate = this.applyTimezoneOffset(adjustedDate, timezoneOffset);
	    return this.formatDateToCalendar(adjustedDate);
	  }
	  static formatDateForIANATimeZone(date) {
	    const defaultBrowserTimezoneOffset = new Date().getTimezoneOffset();
	    let adjustedDate = this.applyTimezoneOffset(date, defaultBrowserTimezoneOffset);
	    return this.formatDateToCalendarLocal(adjustedDate);
	  }
	  static formatDateForCalendar(date, timezone) {
	    return this.isTimezoneProvideByIANA(timezone) ? this.formatDateForIANATimeZone(date) : this.formatDateForOffsetTimeZone(date, timezone);
	  }
	  static createICSURL(uid, startDate, endDate, summary, description, location) {
	    const timezone = mpapp().settings().getTimezone();
	    let formattedStartDate = this.formatDateForCalendar(startDate, timezone);
	    let formattedEndDate = this.formatDateForCalendar(endDate, timezone);

	    // all day event
	    if (0 === startDate.getHours() && 0 === startDate.getMinutes() && 0 === endDate.getHours() && 0 === endDate.getMinutes()) {
	      // remove from YYYYMMDDTHHmmSSZ part: THHmmSSZ 
	      formattedStartDate = formattedStartDate.replace(/T\d\d\d\d\d\d|Z/g, '');
	      formattedEndDate = formattedEndDate.replace(/T\d\d\d\d\d\d|Z/g, '');
	    }
	    const icsComponents = ['BEGIN:VCALENDAR', 'VERSION:2.0', `PRODID:${mpapp().settings().getBusinessName()}`];
	    if (this.isTimezoneProvideByIANA(timezone)) {
	      // To make the ICS file valid, DTSTART, TZOFFSETFROM, and TZOFFSETTO need to be added.
	      // However, without using external libraries, it's impossible to calculate the correct timezone offset for daylight/standard time.
	      icsComponents.push('BEGIN:VTIMEZONE', 'TZID:' + timezone, 'END:VTIMEZONE');
	    }
	    icsComponents.push('BEGIN:VEVENT', 'DTSTAMP:' + this.formatDateToCalendar(new Date()), 'UID:' + uid, 'DTSTART' + (this.isTimezoneProvideByIANA(timezone) ? ';TZID=' + timezone + ':' : ':') + formattedStartDate, 'DTEND' + (this.isTimezoneProvideByIANA(timezone) ? ';TZID=' + timezone + ':' : ':') + formattedEndDate, 'SUMMARY:' + summary, 'DESCRIPTION:' + description, 'LOCATION:' + location, 'END:VEVENT');
	    icsComponents.push('END:VCALENDAR');
	    const icsData = icsComponents.join('\n');
	    const blob = new Blob([icsData], {
	      type: 'text/calendar'
	    });
	    return window.URL.createObjectURL(blob);
	  }
	  static createGoogleCalendarURL(startDate, endDate, summary, description, location) {
	    const baseUrl = "https://www.google.com/calendar/render";
	    const url = new URL(baseUrl);
	    const timezone = mpapp().settings().getTimezone();
	    let formattedStartDate = this.formatDateForCalendar(startDate, timezone);
	    let formattedEndDate = this.formatDateForCalendar(endDate, timezone);

	    // all day event
	    if (0 === startDate.getHours() && 0 === startDate.getMinutes() && 0 === endDate.getHours() && 0 === endDate.getMinutes()) {
	      // remove from YYYYMMDDTHHmmSSZ part: THHmmSSZ 
	      formattedStartDate = formattedStartDate.replace(/T\d\d\d\d\d\d|Z/g, '');
	      formattedEndDate = formattedEndDate.replace(/T\d\d\d\d\d\d|Z/g, '');
	    }
	    url.search = new URLSearchParams({
	      action: "TEMPLATE",
	      text: summary,
	      dates: `${formattedStartDate}/${formattedEndDate}`,
	      details: description,
	      location: location
	    }).toString();
	    if (this.isTimezoneProvideByIANA(timezone)) {
	      url.searchParams.append("ctz", timezone);
	    }
	    return url.toString();
	  }

	  /**
	   * Yahoo calendar not supported IANA timezones.
	   * Dates with offset provide in UTC format and with condition IANA timezone provide in local format.
	   */
	  static createYahooCalendarURL(startDate, endDate, summary, description, location) {
	    const baseUrl = "https://calendar.yahoo.com/";
	    const url = new URL(baseUrl);
	    const timezone = mpapp().settings().getTimezone();
	    let formattedStartDate = this.formatDateForCalendar(startDate, timezone);
	    let formattedEndDate = this.formatDateForCalendar(endDate, timezone);
	    let eventData = {
	      v: '60',
	      view: 'd',
	      type: '20',
	      title: summary,
	      desc: description,
	      in_loc: location
	    };

	    // all day event
	    if (0 === startDate.getHours() && 0 === startDate.getMinutes() && 0 === endDate.getHours() && 0 === endDate.getMinutes()) {
	      // remove from YYYYMMDDTHHmmSSZ part: THHmmSSZ 
	      eventData.st = formattedStartDate.replace(/T\d\d\d\d\d\d|Z/g, '');
	      eventData.dur = 'allday';
	    } else {
	      eventData.st = formattedStartDate;
	      eventData.et = formattedEndDate;
	    }
	    url.search = new URLSearchParams(eventData).toString();
	    return url.toString();
	  }
	}

	class BookingDetailsSection {
	  /**
	   * @param {jQuery} $bookingDetailsSection
	   * @param {Cart} cart
	   */
	  constructor($bookingDetailsSection, cart) {
	    this.cart = cart;
	    this.$bookingDetailsSection = $bookingDetailsSection;
	    this.$bookingCartItems = this.$bookingDetailsSection.find('.booking-reservations');
	    this.$bookingCartItem = this.$bookingCartItems.find('.reservation');
	    this.$addToCalendarGoogle = this.$bookingCartItem.find('.mpa-add-to-calendar-link--google');
	    this.$addToCalendarApple = this.$bookingCartItem.find('.mpa-add-to-calendar-link--apple');
	    this.$addToCalendarOutlook = this.$bookingCartItem.find('.mpa-add-to-calendar-link--outlook');
	    this.$addToCalendarYahoo = this.$bookingCartItem.find('.mpa-add-to-calendar-link--yahoo');
	  }
	  assignURL($element, url) {
	    $element.attr('href', url);
	  }
	  initBookingCart() {
	    this.$bookingCartItems.empty();
	    wp.hooks.doAction('mpa_booking_details_section_init', this.$bookingDetailsSection, this.cart);
	    this.cart.items.forEach(cartItem => {
	      let $newItem = this.$bookingCartItem.clone();
	      this.$bookingCartItems.append($newItem);
	      const service = cartItem.getService();
	      const title = service.getName();
	      const summary = cartItem.employee.name + '. ' + service.getQuantityLabel() + ': ' + cartItem.getCapacity() + '.';
	      let titleHtml = title;
	      if (cartItem.getCapacity() > 1) {
	        titleHtml += ' ';
	        titleHtml += '<span class="mpa-reservation-capacity">';
	        titleHtml += service.getQuantityLabel() + ': ' + cartItem.getCapacity();
	        titleHtml += '</span>';
	      }
	      $newItem.find('.reservation-title').html(titleHtml);
	      $newItem.find('.reservation-date').html(mpa_format_date(cartItem.date));
	      $newItem.find('.reservation-time').html(cartItem.time.toString());
	      const ICSDownloadUrl = AddToCalendar.createICSURL(cartItem.getItemId(), cartItem.time.startTime, cartItem.time.endTime, title, summary, cartItem.location.name);
	      const googleCalendarURL = AddToCalendar.createGoogleCalendarURL(cartItem.time.startTime, cartItem.time.endTime, title, summary, cartItem.location.name);
	      const yahooCalendarURL = AddToCalendar.createYahooCalendarURL(cartItem.time.startTime, cartItem.time.endTime, title, summary, cartItem.location.name);
	      this.assignURL($newItem.find('.mpa-add-to-calendar-link--google'), googleCalendarURL);
	      this.assignURL($newItem.find('.mpa-add-to-calendar-link--apple'), ICSDownloadUrl);
	      this.assignURL($newItem.find('.mpa-add-to-calendar-link--outlook'), ICSDownloadUrl);
	      this.assignURL($newItem.find('.mpa-add-to-calendar-link--yahoo'), yahooCalendarURL);
	    });
	    this.$bookingDetailsSection.toggleClass('mpa-hide', false);
	  }
	  reset() {
	    this.$bookingDetailsSection.toggleClass('mpa-hide', true);
	    const defaultURL = '#';
	    this.assignURL(this.$addToCalendarGoogle, defaultURL);
	    this.assignURL(this.$addToCalendarApple, defaultURL);
	    this.assignURL(this.$addToCalendarOutlook, defaultURL);
	    this.assignURL(this.$addToCalendarYahoo, defaultURL);
	  }
	}

	/**
	 * @since 1.0
	 */
	class StepBooking extends AbstractStep {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.hideButtons = true;
	    this.isPosted = false;
	    this.isBooked = false;
	    this.$message = this.$element.find('.mpa-message').first();
	    this.$buttonReset = this.$buttons.find('.mpa-button-reset');
	    this.$bookingDetails = this.$element.find('.mpa-booking-details').first();
	    if (this.$bookingDetails.length > 0) {
	      this.bookingDetails = new BookingDetailsSection(this.$bookingDetails, this.cart);
	    }
	  }

	  /**
	   * @return {Promise}
	   * @access protected
	   *
	   * @since 1.0
	   */
	  reload() {
	    this.isPosted = false;
	    this.isBooked = false;
	    this.setMessage(__('Making a reservation...', 'motopress-appointment') + ' <span class="mpa-preloader"></span>');
	    if (this.bookingDetails) {
	      this.bookingDetails.reset();
	    }
	    return Promise.resolve(this);
	  }

	  /**
	   * @since 1.6.2
	   * @access protected
	   */
	  addListeners() {
	    super.addListeners();
	    this.$buttonReset.on('click', this.resetForm.bind(this));
	  }

	  /**
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  theId() {
	    return 'booking';
	  }

	  /**
	   * @since 1.6.2
	   * @access protected
	   */
	  react() {
	    if (!this.isPosted) {
	      return;
	    }
	    this.$buttons.removeClass('mpa-hide');
	    this.$buttonBack.toggleClass('mpa-hide', this.isBooked);
	    this.$buttonReset.toggleClass('mpa-hide', !this.isBooked || this.isRedirectNeeded());
	  }

	  /**
	   * @since 1.0
	   */
	  show() {
	    super.show();
	    this.createBooking();
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @access private
	   */
	  createBooking() {
	    // Book services
	    mpa_rest_post('/bookings', wp.hooks.applyFilters('mpa_booking_cart_data', this.cart.toArray())).then(response => {
	      if (this.isRedirectNeeded()) {
	        this.redirectPayment();
	        return;
	      }
	      this.isPosted = this.isBooked = true;
	      this.cart.paymentDetails.booking_id = response.booking_id;
	      this.setMessage(response.message);
	      if (this.bookingDetails) {
	        this.bookingDetails.initBookingCart();
	      }
	      this.react();
	    }, error => {
	      this.isPosted = true;
	      this.setMessage(error.message);
	      this.react();
	    });
	  }

	  /**
	   * @since 1.6.2
	   * @access protected
	   */
	  showReady() {
	    super.showReady();
	    this.$buttonBack.addClass('mpa-hide');
	    this.$buttonReset.addClass('mpa-hide');
	  }

	  /**
	   * @param {String} message
	   *
	   * @since 1.0
	   */
	  setMessage(message) {
	    this.$message.html(message);
	  }

	  /**
	   * Redirect to complete the payment.
	   *
	   * @since 1.6.2
	   */
	  redirectPayment() {
	    this.setMessage(__('You will be redirected to a secure page to complete the payment.', 'motopress-appointment'));
	    let paymentDetails = this.cart.getPaymentDetails();
	    window.location.href = paymentDetails.redirect_url;
	  }

	  /**
	   * @since 1.6.2
	   * @access protected
	   */
	  isRedirectNeeded() {
	    let paymentDetails = this.cart.getPaymentDetails();
	    return 'redirect_url' in paymentDetails && paymentDetails.redirect_url != '';
	  }

	  /**
	   * @since 1.6.2
	   * @access protected
	   */
	  resetForm(event) {
	    event.preventDefault();
	    if (this.isPosted && this.isBooked) {
	      this.$element.trigger('mpa_reset_booking');
	    }
	  }
	}

	/**
	 * @param {Object} atts
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_atts(atts) {
	  let output = '';
	  for (let name in atts) {
	    output += ' ' + name + '="' + atts[name] + '"';
	  }
	  return output;
	}

	/**
	 * @param {String} label
	 * @param {Object} atts
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_button(label, atts = {}) {
	  atts = jQuery.extend({}, {
	    type: 'button',
	    'class': 'button'
	  }, atts);
	  return '<button' + mpa_tmpl_atts(atts) + '>' + label + '</button>';
	}

	/**
	 * @since 1.0.0
	 * @since 1.4.0 builds output only by template. Added the <code>$template</code> argument.
	 *
	 * @param {CartItem} cartItem
	 * @param {Object} $template
	 * @return {String}
	 */
	function mpa_tmpl_cart_item(cartItem, $template) {
	  let selectors = {
	    // Template tag:   jQuery selector
	    service_id: '.mpa-service-id',
	    service_name: '.mpa-service-name',
	    service_thumbnail: '.mpa-service-thumbnail',
	    employee_id: '.mpa-employee-id',
	    employee_name: '.mpa-employee-name',
	    location_id: '.mpa-location-id',
	    location_name: '.mpa-location-name',
	    reservation_date: '.mpa-reservation-date',
	    // Public date string
	    reservation_save_date: '.mpa-reservation-save-date',
	    // Internal date string

	    reservation_time: '.mpa-reservation-time',
	    // Public start time string
	    reservation_period: '.mpa-reservation-period',
	    // Public period time string
	    reservation_save_period: '.mpa-reservation-save-period',
	    // Internal period time string

	    reservation_capacity: '.mpa-reservation-capacity',
	    // Wrapper for <select> with capacity options
	    reservation_clients: '.mpa-reservation-clients',
	    // Only the capacity <option>'s
	    reservation_clients_count: '.mpa-reservation-clients-count',
	    // Capacity number: "1", "2" etc.

	    reservation_price: '.mpa-reservation-price'
	  };

	  // Generate new item
	  let $cartItem = $template.clone();

	  // Set cart ID
	  $cartItem.attr('data-id', cartItem.getItemId());

	  // Replace all tags
	  let capacityOptions = cartItem.getCapacityOptions();
	  for (let tagName in selectors) {
	    let selector = selectors[tagName];
	    let $element = $cartItem.find(selector).first();
	    let elementHtml = $element.length > 0 ? $element.html() : '';
	    let tag = '{' + tagName + '}';
	    if (!elementHtml.includes(tag)) {
	      continue;
	    }
	    let content = '';
	    switch (tagName) {
	      case 'service_id':
	        content = cartItem.service.id;
	        break;
	      case 'service_name':
	        content = cartItem.service.name;
	        break;
	      case 'service_thumbnail':
	        content = mpa_tmpl_thumbnail_image(cartItem.service.thumbnail);
	        break;
	      case 'employee_id':
	        content = cartItem.employee.id;
	        break;
	      case 'employee_name':
	        content = cartItem.employee.name;
	        break;
	      case 'location_id':
	        content = cartItem.location.id;
	        break;
	      case 'location_name':
	        content = cartItem.location.name;
	        break;
	      case 'reservation_date':
	        content = mpa_format_date(cartItem.date);
	        break;
	      case 'reservation_save_date':
	        content = mpa_format_date(cartItem.date, 'internal');
	        break;
	      case 'reservation_time':
	        content = cartItem.time.toString('short');
	        break;
	      case 'reservation_period':
	        content = cartItem.time.toString();
	        break;
	      case 'reservation_save_period':
	        content = cartItem.time.toString('internal');
	        break;
	      case 'reservation_capacity':
	        let capacityVariants = mpa_array_combine(capacityOptions, capacityOptions);
	        content = mpa_tmpl_select(capacityVariants, cartItem.capacity);
	        break;
	      case 'reservation_clients':
	        let capacityClients = mpa_array_combine(capacityOptions, capacityOptions);
	        content = mpa_tmpl_select_options(capacityClients, cartItem.capacity);
	        break;
	      case 'reservation_clients_count':
	        content = cartItem.capacity;
	        break;
	      case 'reservation_price':
	        let employeeId = cartItem.employee.id;
	        let price = cartItem.service.getPrice(employeeId, cartItem.capacity);
	        content = mpa_tmpl_price(price);
	        break;
	    }
	    $element.html($element.html().replace(tag, content));
	  } // For each tag

	  // Update clients label
	  $cartItem.find('.cell-people .cell-title').html(cartItem.getService().getQuantityLabel());

	  // Replace {item_id} with the actual ID
	  $cartItem.find('[name*="{item_id}"]').each((i, element) => {
	    element.name = element.name.replace('{item_id}', cartItem.getItemId());
	  });

	  // hide capacity if we have only one possible client
	  if (1 === capacityOptions.length) {
	    $cartItem.find('.cell-people').addClass('mpa-hide');
	  }
	  return $cartItem;
	}

	/**
	 * @since 1.4.0
	 * @see Cart.getOrder()
	 *
	 * @param {Object} order
	 * @return {String}
	 */
	function mpa_tmpl_order(order) {
	  let output = '';
	  output += '<table class="mpa-order widefat">';
	  output += '<tbody>';
	  for (let product of order.products) {
	    output += '<tr class="mpa-order-service">';
	    output += '<td class="column-service">';
	    output += '<span class="mpa-service-name">' + product.name + '</span>';
	    if (product.capacity > 1) {
	      output += '<span class="mpa-reservation-capacity">';
	      output += product.quantity_label + ': ' + product.capacity;
	      output += '</span>';
	    }
	    output += '</td>';
	    output += '<td class="column-price">' + mpa_tmpl_price_number(product.price) + '</td>';
	    output += '</tr>';
	  }

	  // Show subtotal price
	  output += '<tr class="mpa-order-subtotal">';
	  output += '<th class="column-subtotal">' + __('Subtotal', 'motopress-appointment') + '</th>';
	  output += '<th class="column-price">' + mpa_tmpl_price_number(order.subtotal) + '</th>';
	  output += '</tr>';
	  output += '</tbody>';
	  output += '<tfoot>';
	  if (order.coupon) {
	    output += '<tr class="mpa-order-coupon">';
	    output += '<th class="column-coupon">';
	    output +=
	    // Translators: %s: Coupon code.
	    __('Coupon: %s', 'motopress-appointment').replace('%s', order.coupon.code);
	    output += '</th>';
	    output += '<td class="column-price">';
	    output += mpa_tmpl_price_number(-order.coupon.amount);
	    output += ' ';
	    output += '<a href="#" class="mpa-remove-coupon">' + __('Remove', 'motopress-appointment') + '</a>';
	    output += '</td>';
	    output += '</tr>';
	  }

	  // Shot total price
	  output += '<tr class="mpa-order-total">';
	  output += '<th class="column-total">' + __('Total', 'motopress-appointment') + '</th>';
	  output += '<th class="column-price">' + mpa_tmpl_price_number(order.total) + '</th>';
	  output += '</tr>';
	  output += '</tfoot>';
	  output += '</table>';
	  return output;
	}

	/**
	 * @since 1.14.0
	 * @see Cart.getOrder()
	 *
	 * @param {Object} order
	 * @return {String}
	 */
	function mpa_tmpl_deposit(order) {
	  const leftToPay = parseFloat(order.total) - parseFloat(order.deposit);
	  let output = '';
	  if (leftToPay > 0) {
	    output += '<table class="widefat">';
	    output += '<tbody>';
	    output += '<tr class="mpa-deposit-title">';
	    output += '<td class="column-title" colspan="2">';
	    output += __('Deposit', 'motopress-appointment');
	    output += '</td>';
	    output += '</tr>';
	    output += '<tr class="mpa-deposit-now">';
	    output += '<th class="column-title">';
	    output += __('Paying now', 'motopress-appointment');
	    output += '</th>';
	    output += '<th class="column-price">';
	    output += mpa_tmpl_price_number(order.deposit);
	    output += '</th>';
	    output += '</tr>';
	    output += '<tr class="mpa-deposit-left">';
	    output += '<th class="column-title">';
	    output += __('Left to pay', 'motopress-appointment');
	    output += '</th>';
	    output += '<th class="column-price">';
	    output += mpa_tmpl_price_number(leftToPay);
	    output += '</th>';
	    output += '</tr>';
	    output += '</tbody>';
	    output += '</table>';
	  }
	  return output;
	}

	/**
	 * @param {Number} price
	 * @param {Object} args
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_price(price, args = {}) {
	  let settings = mpapp().settings();
	  args = jQuery.extend({
	    currency_symbol: settings.getCurrencySymbol(),
	    currency_position: settings.getCurrencyPosition(),
	    decimal_separator: settings.getDecimalSeparator(),
	    thousand_separator: settings.getThousandSeparator(),
	    decimals: settings.getDecimalsCount(),
	    literal_free: true,
	    trim_zeros: true
	  }, args);
	  let priceString = mpa_number_format(Math.abs(price), args.decimals, args.decimal_separator, args.thousand_separator);
	  let classes = 'mpa-price';
	  if (price == 0) {
	    classes += ' mpa-zero-price';
	  }
	  if (price == 0 && args.literal_free) {
	    // Use text 'Free' as a price string
	    classes += ' mpa-price-free';
	    priceString = _x('Free', 'Zero price', 'motopress-appointment');
	  } else {
	    // Trim zeros
	    if (args.trim_zeros) {
	      priceString = mpa_trim_price(priceString);
	    }

	    // Add currency to the price
	    let currencySpan = '<span class="mpa-currency">' + args.currency_symbol + '</span>';
	    switch (args.currency_position) {
	      case 'before':
	        priceString = currencySpan + priceString;
	        break;
	      case 'after':
	        priceString = priceString + currencySpan;
	        break;
	      case 'before_with_space':
	        priceString = currencySpan + '&nbsp;' + priceString;
	        break;
	      case 'after_with_space':
	        priceString = priceString + '&nbsp;' + currencySpan;
	        break;
	    }

	    // Add sign
	    if (price < 0) {
	      priceString = '-' + priceString;
	    }
	  }
	  let priceHtml = '<span class="' + classes + '">' + priceString + '</span>';
	  return priceHtml;
	}

	/**
	 * @since 1.4.0
	 *
	 * @param {Number} price
	 * @param {Object} args Optional. See mpa_tmpl_price() for details.
	 * @return {String}
	 */
	function mpa_tmpl_price_number(price, args = {}) {
	  // Force number for all results
	  args.literal_free = false;
	  return mpa_tmpl_price(price, args);
	}

	/**
	 * @param {Object} options
	 * @param {*} selected
	 * @param {Object} atts Optional. {} by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_select(options, selected, atts = {}) {
	  let output = '<select' + mpa_tmpl_atts(atts) + '>';
	  output += mpa_tmpl_select_options(options, selected);
	  output += '</select>';
	  return output;
	}

	/**
	 * @param {String|Number} value
	 * @param {String} label
	 * @param {Boolean} isSelected
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_select_option(value, label, isSelected = false) {
	  let output = '';
	  let selectedAttr = isSelected ? ' selected="selected"' : '';
	  output = '<option value="' + value + '"' + selectedAttr + '>';
	  output += label;
	  output += '</option>';
	  return output;
	}

	/**
	 * @param {Object} options
	 * @param {*} selected
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_select_options(options, selected) {
	  let output = '';
	  for (let value in options) {
	    output += mpa_tmpl_select_option(value, options[value], value == selected);
	  }
	  return output;
	}

	/**
	 * @param {Object} $select jQuery element.
	 * @param {Object} emptyOptions '— Select —'/'— Any —' value.
	 * @param {Object} allowedOptions All other values.
	 * @param {*} selected Selected option value.
	 *
	 * @since 1.19.0
	 */
	function update_select_options($select, emptyOptions, allowedOptions, selected) {
	  let emptyOptionsHtml = mpa_tmpl_select_options(emptyOptions, selected);
	  let allowedOptionsHtml = mpa_tmpl_select_options(allowedOptions, selected);
	  let optionsHtml = emptyOptionsHtml + allowedOptionsHtml;
	  $select.empty().append(optionsHtml).val(selected);
	}

	/**
	 * @since 1.4.0
	 *
	 * @param {String} imageUrl
	 * @return {String}
	 */
	function mpa_tmpl_thumbnail_image(imageUrl) {
	  let {
	    width,
	    height
	  } = mpapp().settings().getThumbnailSize();
	  let atts = {
	    width,
	    height,
	    src: imageUrl,
	    'class': 'attachment-thumbnail size-thumbnail'
	  };
	  return '<img' + mpa_tmpl_atts(atts) + '>';
	}

	/**
	 * @since 1.4.0
	 */
	class StepCart extends AbstractStep {
	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.isBeginCheckoutEventSent = false;
	    this.$cart = this.$element.find('.mpa-cart');
	    this.$items = this.$cart.find('.mpa-cart-items');
	    this.$itemTemplate = this.$cart.find('.mpa-cart-item-template');
	    this.$noItems = this.$element.find('.no-items'); // May be out of the .mpa-cart

	    this.$totalPrice = this.$element.find('.mpa-cart-total-price');
	    this.$buttonNew = this.$buttons.find('.mpa-button-new');
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  theId() {
	    return 'cart';
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  addListeners() {
	    super.addListeners();
	    this.$buttonNew.on('click', this.createNew.bind(this));
	  }

	  /**
	   * @since 1.4.0
	   */
	  load() {
	    // Remove template from the cart
	    this.$itemTemplate.remove();
	    this.$itemTemplate.removeClass('mpa-cart-item-template');

	    // Remove current employee from other variants
	    if (this.cart.getActiveItem() !== null) {
	      // Check for "Edit Booking" page
	      let activeItem = this.cart.getActiveItem();
	      let activeItemId = activeItem.getItemId();
	      let date = activeItem.getDate();
	      let time = activeItem.getTime();
	      this.cart.getItems().forEach(cartItem => {
	        if (cartItem.isSet() && cartItem.getItemId() != activeItemId && cartItem.isAtTime(date, time)) {
	          cartItem.removeBookingVariatForEmployee(activeItem.getEmployeeId());
	        }
	      });
	    }

	    // Update view
	    this.updateActiveItemCapacity();
	    this.refreshCart();
	    this.isLoaded = true;
	    this.readyPromise = Promise.resolve(this);
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    this.$items.find('.mpa-cart-item').remove();
	    this.$noItems.removeClass('mpa-hide');
	    this.isBeginCheckoutEventSent = false;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @access protected
	   */
	  updateActiveItemCapacity() {
	    let cartItem = this.cart.getActiveItem();
	    if (!cartItem) {
	      return;
	    }

	    // Validate the capacity for the selected employee/location
	    let minCapacity = cartItem.getMinCapacity();
	    let maxCapacity = cartItem.getMaxCapacity();

	    // Validate capacity to the limits of the new employee
	    cartItem.setCapacity(mpa_limit(cartItem.getCapacity(), minCapacity, maxCapacity));
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  refreshCart() {
	    this.cart.getActiveItemId();

	    // Add new items
	    this.cart.items.forEach((cartItem, _, cartItemId) => {
	      let itemSelector = '.mpa-cart-item[data-id="' + cartItemId + '"]';
	      let $cartItem = this.$items.find(itemSelector);
	      if ($cartItem.length === 0) {
	        // Add new item
	        $cartItem = this.addItem(cartItem);
	        this.bindListeners($cartItem);
	      } else {
	        // Update item
	        $cartItem = this.updateItem($cartItem, cartItem);
	        this.bindListeners($cartItem);
	      }
	    });

	    // Update total price
	    this.updateTotalPrice();
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   *
	   * @param {CartItem} cartItem
	   * @return {Object} New cart item element (jQuery object).
	   */
	  addItem(cartItem) {
	    let $cartItem = mpa_tmpl_cart_item(cartItem, this.$itemTemplate);
	    this.$items.append($cartItem);
	    this.$noItems.addClass('mpa-hide');
	    return $cartItem;
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   *
	   * @param {Object} $foundItem Existing item (jQuery element).
	   * @param {CartItem} cartItem
	   * @return {Object} New cart item element (jQuery object).
	   */
	  updateItem($cartItem, cartItem) {
	    let $newCartItem = mpa_tmpl_cart_item(cartItem, this.$itemTemplate);
	    $cartItem.replaceWith($newCartItem);
	    return $newCartItem;
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @param {Object} $item
	   */
	  bindListeners($item) {
	    let cartItemId = $item.data('id');
	    let cartItem = this.cart.getItem(cartItemId);
	    let $people = $item.find('.mpa-reservation-capacity select, .mpa-reservation-clients select');
	    let $price = $item.find('.mpa-reservation-price');
	    let $removeButton = $item.find('.mpa-button-remove, .mpa-button-edit-or-remove');
	    let $updateButton = $item.find('.mpa-button-edit, .mpa-button-edit-or-remove');

	    // Change cart item capacity and prices when amount of people changes
	    $people.on('change', event => {
	      let newCapacity = mpa_intval(event.target.value);

	      // Update item capacity
	      cartItem.setCapacity(newCapacity);

	      // Maybe change employee and location
	      let bookingVariant = cartItem.getBookingVariantForCapacity(newCapacity);
	      let employeeId = bookingVariant.employeeId;
	      let locationId = bookingVariant.locationId;
	      if (cartItem.getEmployeeId() != employeeId) {
	        cartItem.setEmployee(employeeId, false);
	        cartItem.setLocation(locationId, false);

	        // Update the entire item
	        $item = this.updateItem($item, cartItem);
	        this.bindListeners($item);
	      } else {
	        // Update price only
	        let itemPrice = cartItem.service.getPrice(employeeId, newCapacity);
	        $price.html(mpa_tmpl_price(itemPrice));
	      }

	      // Update total price
	      this.updateTotalPrice();
	    });

	    // Add "Remove" listener
	    if (this.isMultibookingEnabled()) {
	      $removeButton.on('click', event => {
	        event.stopPropagation();

	        // Remove item element
	        $item.remove();

	        // Remove item from the cart
	        let removedCartItem = this.cart.getItem(cartItemId);
	        this.cart.removeItem(cartItemId);
	        if (this.cart.isEmpty()) {
	          this.$noItems.removeClass('mpa-hide');
	        }

	        // React to changes
	        this.updateTotalPrice();
	        this.react();
	        document.dispatchEvent(new CustomEvent('mpa_remove_from_cart', {
	          detail: {
	            cartItem: removedCartItem,
	            currencyCode: mpapp().settings().getCurrency()
	          }
	        }));
	      });
	    }

	    // Add "Update" listener
	    if (!this.isMultibookingEnabled()) {
	      $updateButton.on('click', () => {
	        this.cart.setActiveItem(cartItemId);

	        // Go back
	        this.cancel();
	      });
	    }
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  updateTotalPrice() {
	    this.$totalPrice.html(mpa_tmpl_price_number(this.cart.getTotalPrice()));
	  }
	  isMultibookingEnabled() {
	    return mpapp().settings().isMultibookingEnabled();
	  }

	  /**
	   * @since 1.4.0
	   *
	   * @return {Boolean}
	   */
	  isValidInput() {
	    return !this.cart.isEmpty();
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  createNew() {
	    if (!this.isActive) {
	      return;
	    }

	    // Stop editing
	    this.disable();

	    // Trigger as cancelled
	    this.triggerNew();
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  triggerNew() {
	    this.$element.trigger('mpa_booking_step_new', {
	      step: this.stepId
	    });
	  }

	  /**
	   * @access protected
	   */
	  maybeSubmit() {
	    if (!this.isBeginCheckoutEventSent) {
	      document.dispatchEvent(new CustomEvent('mpa_begin_checkout', {
	        detail: {
	          cart: this.cart,
	          currencyCode: mpapp().settings().getCurrency()
	        }
	      }));
	      this.isBeginCheckoutEventSent = true;
	    }
	  }
	}

	const KEY_ENTER = 'Enter';

	/**
	 * @since 1.11.0
	 */
	class CouponSection {
	  /**
	   * @param {jQuery} $element
	   * @param {Cart} cart
	   */
	  constructor($element, cart) {
	    this.cart = cart;
	    this.$element = $element;
	    this.$couponCode = $element.find('[name="coupon_code"]');
	    this.$applyButton = $element.find('.mpa-apply-coupon-button');
	    this.$messageHolder = $element.find('.mpa-message-wrapper');
	    this.$preloader = $element.find('.mpa-preloader');
	    this.$parentForm = $element.parents('.mpa-booking-step').first();
	    this.addListeners();
	    // hide coupon section if cart total price = 0
	    this.reset();
	  }

	  /**
	   * @access protected
	   */
	  addListeners() {
	    this.$couponCode.on('keydown', event => {
	      if (event.code === KEY_ENTER) {
	        this.onEnter(event);
	      }
	    });
	    this.$applyButton.on('click', this.onSubmit.bind(this));
	  }

	  /**
	   * Triggers when user types [Enter] in the input field.
	   *
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  onEnter(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    this.applyCouponCode(event.target.value);
	  }

	  /**
	   * Triggers when user clicks "Apply" button.
	   *
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  onSubmit(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    this.applyCouponCode(this.$couponCode.val());
	  }

	  /**
	   * @param {String} couponCode
	   */
	  applyCouponCode(couponCode) {
	    this.clearMessage();
	    if (!couponCode) {
	      this.setMessage(__('Coupon code is empty.', 'motopress-appointment'));
	      return;
	    }
	    this.pauseAll();
	    mpa_repositories().coupon().findByCode(couponCode).then(coupon => {
	      if (coupon.isApplicableForCart(this.cart)) {
	        this.cart.setCoupon(coupon);
	        this.reset();
	        this.triggerApplied(coupon);
	        this.setMessage(__('Coupon code applied successfully.', 'motopress-appointment'));
	      } else {
	        this.setMessage(__('Sorry, your booking is not eligible for this coupon.', 'motopress-appointment'));
	      }
	      this.unpauseAll();
	    }, error => {
	      this.setMessage(error.message);
	      this.unpauseAll();
	    });
	  }
	  reset() {
	    this.$couponCode.val('');
	    this.clearMessage();
	    if (0 === this.cart.getTotalPrice()) {
	      this.disable();
	      this.$element.addClass('mpa-hide');
	    } else {
	      this.enable();
	      this.$element.removeClass('mpa-hide');
	    }
	  }
	  disable() {
	    this.$couponCode.prop('disabled', true);
	    this.$applyButton.prop('disabled', true);
	  }
	  enable() {
	    this.$couponCode.prop('disabled', false);
	    this.$applyButton.prop('disabled', false);
	  }

	  /**
	   * @access protected
	   */
	  pauseAll() {
	    this.disable();
	    this.showPreloader();
	    this.$parentForm.trigger('mpa_booking_step_disable');
	  }

	  /**
	   * @access protected
	   */
	  unpauseAll() {
	    this.enable();
	    this.hidePreloader();
	    this.$parentForm.trigger('mpa_booking_step_enable');
	  }

	  /**
	   * @access protected
	   *
	   * @param {Coupon} coupon
	   */
	  triggerApplied(coupon) {
	    this.$parentForm.trigger('mpa_booking_coupon_applied', {
	      coupon
	    });
	  }

	  /**
	   * @param {String} message
	   */
	  setMessage(message) {
	    this.$messageHolder.html(message).removeClass('mpa-hide');
	  }
	  clearMessage() {
	    this.$messageHolder.html('').addClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   */
	  showPreloader() {
	    this.$preloader.removeClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   */
	  hidePreloader() {
	    this.$preloader.addClass('mpa-hide');
	  }
	}

	/**
	 * @since 1.22.0
	 *
	 * @param $phoneInputElement jQuery object
	 */
	function mpa_intl_tel_input($phoneInputElement) {
	  const $phoneErrorElement = jQuery('<span/>', {
	    id: $phoneInputElement.attr('id') + '_error',
	    class: 'mpa-phone-field-error mpa-hide',
	    text: __('Phone number is invalid.', 'motopress-appointment')
	  });
	  $phoneInputElement.after('<br>', $phoneErrorElement);
	  const iti = intlTelInput($phoneInputElement[0], {
	    separateDialCode: true,
	    initialCountry: mpaData.settings.country,
	    hiddenInput: $phoneInputElement.attr('name'),
	    utilsScript: mpaData.urls.plugin + 'assets/js/intl-tel-input-17.0.19/js/utils.js'
	  });
	  iti.promise.then(() => {
	    if ($phoneInputElement.val()) {
	      showOrHidePhoneError();
	    }
	    $phoneInputElement.on("countrychange", event => {
	      showOrHidePhoneError();
	    });
	    $phoneInputElement.on("input", event => {
	      showOrHidePhoneError();
	    });
	  });
	  const showOrHidePhoneError = () => {
	    if (iti.isValidNumber()) {
	      jQuery("input[type='hidden'][name='" + $phoneInputElement.attr('name') + "']").val(iti.getNumber(intlTelInputUtils.numberFormat.E164));
	      $phoneInputElement.removeClass('mpa-phone-number--invalid');
	      $phoneErrorElement.addClass('mpa-hide');
	    } else {
	      $phoneInputElement.addClass('mpa-phone-number--invalid');
	      $phoneErrorElement.removeClass('mpa-hide');
	    }
	  };
	  return iti;
	}
	window.mpa_intl_tel_input = mpa_intl_tel_input;

	/**
	 * @since 1.0
	 */
	class StepCheckout extends AbstractStep {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.name = '';
	    this.email = '';
	    this.phone = '';
	    this.notes = '';
	    this.acceptTerms = false;
	    this.createAccount = false;
	    this.$checkoutForm = this.$element.find('.mpa-checkout-form');
	    this.$name = this.$element.find('.mpa-customer-name');
	    this.$email = this.$element.find('.mpa-customer-email');
	    this.$phone = this.$element.find('.mpa-customer-phone');
	    this.$notes = this.$element.find('.mpa-customer-notes');
	    this.$order = this.$element.find('.mpa-order');
	    wp.hooks.doAction('mpa_step_checkout_form', this.$checkoutForm);
	    if (this.$phone.length !== 0) {
	      this.phoneValidator = mpa_intl_tel_input(this.$phone);
	    }
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms = this.$element.find('.mpa-accept-terms');
	    }
	    this.$messageHolder = this.$element.find('.mpa-message').first();
	    this.$preloader = this.$element.find('.mpa-loading');
	    if (mpapp().settings().isAllowCustomerAccountCreation()) {
	      this.$createAccount = this.$element.find('.mpa-customer-create-account');
	      this.$createAccountDescription = this.$element.find('.mpa-customer-create-account-description');

	      // Set default value of property createAccount.
	      // Actually for mode: 'Customer account creation' = 'create_automatically'
	      this.setProperty('createAccount', this.$createAccount.prop('checked'));
	    }
	    if (mpaData && mpaData.currentCustomer && mpaData.currentCustomer.name) {
	      this.setProperty('name', mpaData.currentCustomer.name);
	      this.$name.val(mpaData.currentCustomer.name);
	    }
	    if (mpaData && mpaData.currentCustomer && mpaData.currentCustomer.email) {
	      this.setProperty('email', mpaData.currentCustomer.email);
	      this.$email.val(mpaData.currentCustomer.email);
	    }
	    if (mpaData && mpaData.currentCustomer && mpaData.currentCustomer.phone !== 'undefined') {
	      this.setProperty('phone', mpaData.currentCustomer.phone);

	      // set phone number through intl-tel-input to remove country code from number if it is there
	      this.phoneValidator.setNumber(mpaData.currentCustomer.phone);
	      // send event to validate phone number
	      this.$phone.trigger('input');
	    }
	    this.service = null;
	    this.couponSection = null;
	  }

	  /**
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  theId() {
	    return 'checkout';
	  }

	  /**
	   * @return {Object} {Property name: {type, default}}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  propertiesSchema() {
	    return {
	      name: {
	        type: 'string',
	        default: ''
	      },
	      email: {
	        type: 'string',
	        default: ''
	      },
	      phone: {
	        type: 'string',
	        default: ''
	      },
	      notes: {
	        type: 'string',
	        default: ''
	      },
	      acceptTerms: {
	        type: 'bool',
	        default: false
	      },
	      $createAccount: {
	        type: 'bool',
	        default: false
	      }
	    };
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    super.addListeners();
	    this.$checkoutForm.on('submit', event => false); // Disable submit

	    this.$name.on('input', event => this.setProperty('name', event.target.value));
	    this.$email.on('input', event => this.setProperty('email', event.target.value));
	    this.$phone.on('input', event => {
	      this.setProperty('phone', '');
	      if (this.phoneValidator.isValidNumber()) {
	        this.setProperty('phone', this.phoneValidator.getNumber(intlTelInputUtils.numberFormat.E164));
	      }
	    });
	    this.$phone.on("countrychange", event => {
	      this.setProperty('phone', '');
	      if (this.phoneValidator.isValidNumber()) {
	        this.setProperty('phone', this.phoneValidator.getNumber(intlTelInputUtils.numberFormat.E164));
	      }
	    });
	    this.$notes.on('input', event => this.setProperty('notes', event.target.value));
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms.on('input', event => this.setProperty('acceptTerms', event.target.checked));
	    }
	    if (mpapp().settings().isAllowCustomerAccountCreation()) {
	      this.$createAccount.on('input', event => {
	        this.setProperty('createAccount', event.target.checked);
	        if (event.target.checked) {
	          this.$createAccountDescription.removeClass('mpa-hide');
	        } else {
	          this.$createAccountDescription.addClass('mpa-hide');
	        }
	      });
	    }
	    this.$element.on('mpa_booking_step_disable', this.disable.bind(this));
	    this.$element.on('mpa_booking_step_enable', this.enable.bind(this));
	    this.$element.on('mpa_booking_coupon_applied', () => this.updateOrder());
	  }

	  /**
	   * @since 1.4.0
	   */
	  load() {
	    // Initialize coupon section control
	    if (!this.couponSection) {
	      if (mpapp().settings().isCouponsEnabled()) {
	        this.couponSection = new CouponSection(this.$element.find('.mpa-coupon-details'), this.cart);
	      }
	    } else {
	      // Instead of reload()
	      this.couponSection.reset(); // Clear previous messages
	    }

	    // Is the coupon still applicable to the cart?
	    if (this.cart.hasCoupon()) {
	      this.cart.testCoupon();
	    }
	    this.updateOrder();
	    this.isLoaded = true;
	    this.readyPromise = Promise.resolve(this);
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    wp.hooks.doAction('mpa_step_checkout_reset', this.$checkoutForm);
	    // do not clear name, email and phone for second reservation and nextes
	    this.$notes.val('');
	    // val() will not trigger 'input' event, so reset the properties manually
	    this.resetProperty('notes');
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms.prop('checked', false);
	      this.resetProperty('acceptTerms');
	    }
	    if (mpapp().settings().isAllowCustomerAccountCreation()) {
	      this.clearMessage();
	      this.$createAccount.prop('checked', false);
	      this.resetProperty('createAccount');
	    }

	    // Reset coupon section
	    if (this.couponSection) {
	      this.couponSection.reset();
	    }
	  }

	  /**
	   * @since 1.4.0
	   * @access protected
	   */
	  updateOrder() {
	    if (this.$order.length === 0) {
	      return;
	    }
	    this.$order.empty();
	    this.$order.html(mpa_tmpl_order(this.cart.getOrder()));

	    // Bind remove coupon button
	    let $removeCoupon = this.$order.find('.mpa-remove-coupon');
	    if ($removeCoupon.length > 0) {
	      $removeCoupon.on('click', this.removeCoupon.bind(this));
	    }
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  removeCoupon(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    this.cart.removeCoupon();
	    this.couponSection.clearMessage();
	    this.updateOrder();
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isValidInput() {
	    return this.isValidName() && this.isValidEmail() && this.isValidPhone() && this.isValidAcceptTerms() && wp.hooks.applyFilters('mpa_step_checkout_form_valid', true, this.$checkoutForm);
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   *
	   * @return {Boolean} 
	   */
	  isValidName() {
	    if (this.$name.length > 0 && this.$name.is('[required]')) {
	      return this.name !== '';
	    }
	    return true;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @access protected
	   *
	   * @since 1.1.0
	   */
	  isValidEmail() {
	    if (this.$email.length > 0 && this.$email.is('[required]')) {
	      return this.email !== '' && !!this.email.match(/.+@.+/);
	    }
	    return true;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @access protected
	   *
	   * @since 1.1.0
	   */
	  isValidPhone() {
	    if (this.$phone.length > 0 && this.$phone.is('[required]')) {
	      return this.phoneValidator.isValidNumber();
	    }
	    return true;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @access protected
	   *
	   * @since 1.10.2
	   */
	  isValidAcceptTerms() {
	    return !mpapp().settings().getTermsPageIdForAcceptance() || mpapp().settings().isPaymentsEnabled() || this.acceptTerms;
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  react() {
	    super.react();

	    // Always enable submit button to allow validation messages
	    this.$buttonNext.prop('disabled', false);
	  }

	  /**
	   * @param {String} message
	   *
	   * @since 1.18.0
	   */
	  setMessage(message) {
	    this.$messageHolder.html(message).removeClass('mpa-hide');
	  }

	  /**
	   * @since 1.18.0
	   */
	  clearMessage() {
	    this.$messageHolder.html('').addClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.18.0
	   */
	  showPreloader() {
	    this.$preloader.removeClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.18.0
	   */
	  hidePreloader() {
	    this.$preloader.addClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  async maybeSubmit() {
	    if (wp.hooks.hasFilter('mpa_step_checkout_maybe_submit')) {
	      await wp.hooks.applyFilters('mpa_step_checkout_maybe_submit', {}, this.$checkoutForm);
	    }

	    // Disable coupons section
	    if (this.couponSection) {
	      this.couponSection.disable();
	    }
	    this.cart.setCustomerDetails({
	      name: this.name,
	      email: this.email,
	      phone: this.phone,
	      notes: this.notes,
	      acceptTerms: this.acceptTerms
	    });
	    if (this.createAccount && this.email !== '') {
	      this.showPreloader();
	      const customerData = {
	        name: this.name,
	        email: this.email,
	        phone: this.phone
	      };
	      return mpa_rest_post('/customers/create', customerData).then(customer => {
	        this.hidePreloader();
	        this.clearMessage();
	      }, error => {
	        this.hidePreloader();
	        this.setMessage(error);
	        throw error;
	      });
	    }
	  }
	}

	/**
	 * @since 1.5.0
	 */
	class PaymentGateway {
	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  setupProperties() {
	    /**
	     * @since 1.5.0
	     * @var {String}
	     */
	    this.gatewayId = 'basic';

	    /**
	     * @since 1.5.0
	     * @var {Object}
	     */
	    this.settings = this.getDefaults();

	    /**
	     * @since 1.5.0
	     * @var {Object}
	     */
	    this.$mountWrapper = null;

	    /**
	     * @since 1.5.0
	     * @var {Promise}
	     */
	    this.loadPromise = null;

	    /**
	     * @since 1.5.0
	     * @var {Boolean}
	     */
	    this.isEnabled = false;

	    /**
	     * @since 1.5.0
	     * @var {Boolean}
	     */
	    this.isMounted = false;

	    /**
	     * Input(s) have errors.
	     *
	     * @since 1.5.0
	     * @var {Boolean}
	     */
	    this.haveErrors = false;
	  }

	  /**
	   * @since 1.5.0
	   * @since 1.6.0 Added cart parameter
	   *
	   * @param {Object} $mountWrapper
	   */
	  constructor($mountWrapper, cart) {
	    this.setupProperties();
	    this.$mountWrapper = $mountWrapper;
	    this.cart = cart;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Promise}
	   */
	  load() {
	    this.addListeners();
	    this.loadPromise = Promise.resolve(this);
	    return this.loadPromise;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  addListeners() {}

	  /**
	   * @since 1.5.0
	   */
	  onCartChange(cart) {}

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {Object} $mountWrapper
	   */
	  mount($mountWrapper) {}

	  /**
	   * @since 1.5.0
	   *
	   * @return {Promise}
	   */
	  ready() {
	    return this.loadPromise;
	  }

	  /**
	   * @since 1.5.0
	   */
	  enable() {
	    if (!this.isEnabled) {
	      if (!this.isMounted) {
	        this.mount(this.$mountWrapper);
	        this.isMounted = true;
	      }
	      this.$mountWrapper.removeClass('mpa-hide');
	      this.isEnabled = true;
	    }
	  }

	  /**
	   * @since 1.5.0
	   */
	  disable() {
	    if (this.isEnabled) {
	      this.$mountWrapper.addClass('mpa-hide');
	      this.isEnabled = false;
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Boolean}
	   */
	  isValid() {
	    return !this.haveErrors;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {Cart} cart
	   * @param {Object} bookingDetails
	   * @return {Promise} On fulfillment: return processed payment data. On
	   *		rejection: throw error.
	   */
	  processPayment(cart, bookingDetails) {
	    return mpa_rest_post('/payments/prepare', {
	      payment_details: cart.paymentDetails
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Object}
	   */
	  getDefaults() {
	    return {
	      country: mpapp().settings().getCountry(),
	      redirect_url: {
	        payment_received: mpapp().settings().getReservationReceivedPageUrl(),
	        failed_transaction: mpapp().settings().getFailedTransactionPageUrl()
	      }
	    };
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {}
	}

	/**
	 * @since 1.14.0
	 */
	class FreeGateway extends PaymentGateway {
	  enable() {}
	}

	/**
	 * @since 1.5.0
	 */
	class StripeGatewayView {
	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  setupProperties() {
	    /**
	     * @since 1.14.0
	     * @var {Methods}
	     */
	    this.methods = null;

	    /**
	     * @since 1.5.0
	     * @var {String}
	     */
	    this.uid = '';

	    /**
	     * @since 1.5.0
	     * @var {Map} Key - method name ('card', 'ideal'), value - {$nav, $fields}.
	     */
	    this.paymentMethods = new Map();

	    /**
	     * @since 1.5.0
	     * @var {String}
	     */
	    this.selectedMethod = '';

	    /**
	     * @since 1.5.0
	     * @var {Object|null}
	     */
	    this.$mountWrapper = null;

	    /**
	     * @since 1.5.0
	     * @var {Object|null}
	     */
	    this.$errorsWrapper = null;

	    /**
	     * @since 1.16.0
	     * @type {Object}
	     */
	    this.$gatewayPreloader = null;

	    /**
	     * @since 1.16.0
	     * @type {Array}
	     * [ {method_name} => (bool), true - mounted ]
	     */
	    this.mountedMethods = [];
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {Object[]} Stripe payment methods
	   */
	  constructor(methods) {
	    this.setupProperties();
	    this.methods = methods;
	    this.uid = mpa_uniqid();
	    this.addPaymentMethods(this.methods);
	  }
	  mountedMethod() {
	    let hasUnmountedMethod = false;
	    Object.entries(this.mountedMethods).forEach((mountedMethod, isMountedMethod) => {
	      if (!isMountedMethod) {
	        hasUnmountedMethod = true;
	      }
	    });
	    if (hasUnmountedMethod) {
	      this.$gatewayPreloader.addClass('mpa-hide');
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String[]} paymentMethods
	   */
	  addPaymentMethods(paymentMethods) {
	    for (const paymentMethod in paymentMethods) {
	      if (this.paymentMethods.includesKey(paymentMethod)) {
	        continue; // Already exists
	      }

	      // Add new payment method
	      this.paymentMethods.push(paymentMethod, {
	        $nav: null,
	        $fields: null
	      });

	      // Init this.selectedMethod property
	      if (!this.selectedMethod) {
	        this.selectedMethod = paymentMethod;
	      }
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Boolean}
	   */
	  isMounted() {
	    return this.$mountWrapper !== null;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {Object} $mountWrapper
	   */
	  mount($mountWrapper) {
	    $mountWrapper.append(this.render());
	    this.$gatewayPreloader = $mountWrapper.parent().find('.mpa-payment-gateway-title .mpa-preloader');
	    this.$gatewayPreloader.removeClass('mpa-hide');

	    // Init all payment methods
	    this.paymentMethods.forEach((references, i, paymentMethod) => {
	      references.$nav = $mountWrapper.find('.mpa-stripe-payment-method.' + paymentMethod);
	      references.$fields = $mountWrapper.find('.mpa-stripe-payment-fields.' + paymentMethod);
	      const methodControl = this.methods[paymentMethod].getControl();
	      if (methodControl !== null) {
	        const elementSelector = this.getElementSelector(paymentMethod);
	        this.mountedMethods[paymentMethod] = false;
	        methodControl.mount(elementSelector);
	        methodControl.on('ready', paymentMethod => {
	          this.mountedMethod(paymentMethod);
	          document.querySelector(elementSelector).classList.remove('mpa-preloader-skeleton-pulsate');
	        });
	      }

	      // todo: better to refactor
	      if (paymentMethod === 'card') {
	        this.methods['card'].isCanMakePaymentRequest().then(canMakePayment => {
	          const elementPaymentRequestButtonSelector = this.getElementSelector('payment-request-button');
	          const elementPaymentRequestButton = document.querySelector(elementPaymentRequestButtonSelector);
	          if (!elementPaymentRequestButton) {
	            return;
	          }
	          if (canMakePayment) {
	            this.mountedMethods['payment_request_button'] = false;
	            this.methods['card'].paymentRequestButton.mount(elementPaymentRequestButtonSelector);
	            this.methods['card'].paymentRequestButton.on('ready', event => {
	              this.mountedMethod('payment_request_button');
	              elementPaymentRequestButton.classList.remove('mpa-preloader-skeleton-pulsate');
	            });
	          } else {
	            elementPaymentRequestButton.classList.add('mpa-hide');
	            document.querySelector('.mpa-stripe-payment-request-button-separator').classList.add('mpa-hide');
	          }
	        });
	      }
	    });

	    // Listen change of payment method
	    $mountWrapper.find('input[name="stripe_payment_method"]').on('change', this.onPaymentMethodChange.bind(this));
	    this.$mountWrapper = $mountWrapper;
	    this.$errorsWrapper = $mountWrapper.find('.mpa-errors');
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  onPaymentMethodChange(event) {
	    // Clear previous control
	    let previousControl = null;
	    switch (this.selectedMethod) {
	      case 'payment':
	      case 'card':
	      case 'ideal':
	      case 'sepa_debit':
	        previousControl = this.methods[this.selectedMethod].getControl();
	        break;
	    }
	    if (previousControl !== null) {
	      previousControl.clear();
	    }

	    // Select new payment method
	    this.selectPaymentMethod(event.target.value);
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} paymentMethod
	   */
	  selectPaymentMethod(paymentMethod) {
	    if (paymentMethod !== this.selectedMethod) {
	      this.togglePaymentMethod(this.selectedMethod, false);
	      this.togglePaymentMethod(paymentMethod, true);
	      this.selectedMethod = paymentMethod;
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} paymentMethod
	   * @param {Boolean} enable
	   */
	  togglePaymentMethod(paymentMethod, enable) {
	    if (this.isMounted() && this.paymentMethods.includesKey(paymentMethod)) {
	      let refs = this.paymentMethods.find(paymentMethod);
	      refs.$nav.toggleClass('active', enable);
	      refs.$fields.toggleClass('mpa-hide', !enable);
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} paymentMethod
	   * @return {String}
	   */
	  getElementSelector(paymentMethod) {
	    if (paymentMethod === 'sepa_debit') {
	      paymentMethod = 'iban';
	    }
	    return '#mpa-stripe-' + paymentMethod + '-element-' + this.uid;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {String}
	   */
	  render() {
	    let output = '';
	    output += '<section class="mpa-stripe-payment-container">';

	    // Navigation tabs
	    if (this.paymentMethods.length > 1) {
	      output += this.renderNavigation();
	    }

	    // Payment fields
	    for (let paymentMethod of this.paymentMethods.keys) {
	      output += this.renderFields(paymentMethod);
	    }

	    // Errors wrapper
	    output += '<div class="mpa-errors"></div>';
	    output += '</section>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  renderNavigation() {
	    let output = '';
	    output += '<nav class="mpa-stripe-payment-methods">';
	    output += '<ul>';
	    for (let paymentMethod of this.paymentMethods.keys) {
	      let isSelected = paymentMethod === this.selectedMethod;
	      let activeClass = isSelected ? ' active' : '';
	      let checkedAttr = isSelected ? ' checked="checked"' : '';
	      output += '<li class="mpa-stripe-payment-method ' + paymentMethod + activeClass + '">';
	      output += '<label>';
	      output += '<input type="radio" name="stripe_payment_method" value="' + paymentMethod + '"' + checkedAttr + '>';
	      output += ' ' + this.methods[paymentMethod].title;
	      output += '</label>';
	      output += '</li>';
	    }
	    output += '</ul>';
	    output += '</nav>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {String} paymentMethod
	   * @return {String}
	   */
	  renderFields(paymentMethod) {
	    let isSelected = paymentMethod === this.selectedMethod;
	    let hideClass = isSelected ? '' : ' mpa-hide';
	    let output = '';
	    output += '<div class="mpa-stripe-payment-fields ' + paymentMethod + hideClass + '">';
	    output += '<fieldset>';
	    switch (paymentMethod) {
	      case 'payment':
	        output += this.renderPaymentFields();
	        break;
	      case 'card':
	        output += this.renderCardFields();
	        break;
	      case 'ideal':
	        output += this.renderIdealFields();
	        break;
	      case 'sepa_debit':
	        output += this.renderSepaDebitFields();
	        break;
	      default:
	        output += this.renderRedirectNotice();
	        break;
	    }
	    output += '</fieldset>';
	    if (paymentMethod === 'sepa_debit') {
	      output += '<p class="notice">';
	      output +=
	      // https://stripe.com/docs/sources/sepa-debit#prerequisite
	      // Translators: %s: Business name.
	      __('By providing your IBAN and confirming this payment, you authorise (A) %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and (B) your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'motopress-appointment').replace('%s', mpapp().settings().getBusinessName());
	      output += '</p>';
	    }
	    output += '</div>';
	    return output;
	  }
	  renderPaymentFields() {
	    let output = '';
	    output += '<div id="mpa-stripe-payment-element-' + this.uid + '" class="mpa-stripe-element mpa-stripe-payment-element"></div>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  renderCardFields() {
	    let output = '';
	    output += '<label for="mpa-stripe-card-element-' + this.uid + '">';
	    output += __('Credit or debit card', 'motopress-appointment');
	    output += '</label>';
	    if (this.methods['card'].isEnabledWallets()) {
	      output += '<div id="mpa-stripe-payment-request-button-element-' + this.uid + '" class="mpa-stripe-element mpa-stripe-payment-request-button-element mpa-preloader-skeleton-pulsate StripeElement"></div>';
	      output += '<div class="mpa-stripe-payment-request-button-separator">' + __('or', 'motopress-appointment') + '</div>';
	    }
	    output += '<div id="mpa-stripe-card-element-' + this.uid + '" class="mpa-stripe-element mpa-stripe-card-element mpa-preloader-skeleton-pulsate"></div>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  renderIdealFields() {
	    let output = '';
	    output += '<label for="mpa-stripe-ideal-element-' + this.uid + '">';
	    output += __('Select iDEAL Bank', 'motopress-appointment');
	    output += '</label>';
	    output += '<div id="mpa-stripe-ideal-element-' + this.uid + '" class="mpa-stripe-element mpa-stripe-ideal-element mpa-preloader-skeleton-pulsate"></div>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  renderSepaDebitFields() {
	    let output = '';
	    output += '<label for="mpa-stripe-iban-element-' + this.uid + '">';
	    output += __('IBAN', 'motopress-appointment');
	    output += '</label>';
	    output += '<div id="mpa-stripe-iban-element-' + this.uid + '" class="mpa-stripe-element mpa-stripe-iban-element mpa-preloader-skeleton-pulsate"></div>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  renderRedirectNotice() {
	    let output = '';
	    output += '<p class="notice">';
	    output += __('You will be redirected to a secure page to complete the payment.', 'motopress-appointment');
	    output += '</p>';
	    return output;
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {String} message
	   */
	  showError(message) {
	    if (this.isMounted()) {
	      this.$errorsWrapper.html(message).removeClass('mpa-hide');
	    }
	  }

	  /**
	   * @since 1.5.0
	   */
	  hideErrors() {
	    if (this.isMounted()) {
	      this.$errorsWrapper.addClass('mpa-hide').html('');
	    }
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    let resetMethod = this.paymentMethods.firstKey();
	    this.selectPaymentMethod(resetMethod);

	    // No need to clear the control, StripeGateway will do the job:
	    //     this.methods.reset();
	  }
	}

	/**
	 * @since 1.5.0
	 */
	class CustomizablePaymentGateway extends PaymentGateway {
	  /**
	   * @since 1.5.0
	   *
	   * @return {Promise}
	   */
	  load() {
	    // Note: there is no addListeners() here anymore
	    this.loadPromise = mpa_rest_get('/payments/settings', {
	      gateway_id: this.gatewayId
	    }).catch(error => console.error(error.message) || {}) // "Invalid request: ..."
	    .then(settings => {
	      jQuery.extend(this.settings, settings);
	      return this;
	    });
	    return this.loadPromise;
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class AbstractMethod {
	  name = null;
	  title = null;
	  control = null;
	  api = null;
	  elements = null;

	  /**
	   *
	   * @param {Stripe} api Stripe api ref
	   * @param {Elements} elements Stripe.elements()
	   * @param {Elements} elements Stripe.elements()
	   */
	  constructor(api, elements, settings) {
	    this.api = api;
	    this.settings = settings;
	    this.elements = elements;
	    if (new.target === AbstractMethod) {
	      throw new Error("Cannot construct Abstract instances directly");
	    }
	    if (this.setupProperties === undefined) {
	      throw new Error("Must override method: setupProperties()");
	    }
	    this.setupProperties();
	    if (this.name === null || this.name === undefined) {
	      throw new Error('"name" must be defined in a non-abstract payment method class');
	    }
	    if (this.title === null || this.title === undefined) {
	      throw new Error('"title" must be defined in a non-abstract payment method class');
	    }
	  }

	  /**
	   * If payment method has control elements, this method must creates an instance of the Payment Element.
	   * If hasn't controls, need return null.
	   *
	   * @see {@link https://stripe.com/docs/js/elements_object/create_payment_element}
	   *
	   * @return {Promise<Object>|null}
	   */
	  createControl() {
	    return null;
	  }

	  /**
	   * If payment method has control elements, this method must creates an instance of the Payment Element.
	   * If hasn't controls, need return null.
	   *
	   * @see {@link https://stripe.com/docs/js/elements_object/create_payment_element}
	   *
	   * @return {Promise<Object>|null}
	   */
	  getControl() {
	    if (!this.control) {
	      this.control = this.createControl();
	    }
	    return this.control;
	  }

	  /**
	   * @see {@link https://stripe.com/docs/js/element/other_methods/clear}
	   */
	  reset() {
	    if (this.control !== null) {
	      this.control.clear();
	    }
	  }

	  /**
	   *
	   * @param {string} name
	   * @param {string} email
	   * @param {string} phone
	   *
	   * @return {Object} PaymentMethodData
	   * @see https://stripe.com/docs/api/payment_methods/object
	   */
	  createPaymentMethodData(name, email, phone) {
	    let paymentMethodData = {
	      type: this.name,
	      billing_details: {
	        name: name.padEnd(3, ' '),
	        // Some methods require name length 3 or more symbols. Fill requirements by spaces.
	        email: email,
	        phone: phone
	      }
	    };
	    if (this.control !== null) {
	      paymentMethodData[this.name] = this.control;
	    }
	    return paymentMethodData;
	  }

	  /**
	   *
	   * @param {Object} paymentMethodData
	   * @see https://stripe.com/docs/api/payment_methods/object
	   *
	   * @return {Promise} paymentMethod or error
	   */
	  createPaymentMethod(paymentMethodData) {
	    return this.api.createPaymentMethod(paymentMethodData);
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    throw new Error('Abstract Method has no implementation');
	  }

	  /**
	   * @param {Cart} cart
	   * @param {Object} paymentDetails Gateway settings + booking details.
	   * @param {Object} processArgs Optional.
	   *        @param {Function} processArgs['error_handler'] Callback to log errors.
	   * @return {Promise}
	   */
	  processPayment(cart, paymentDetails, processArgs) {
	    const customer = cart.getCustomer();
	    const paymentMethodData = this.createPaymentMethodData(customer.name, customer.email, customer.phone);

	    // Step 1: create payment method on the client side
	    return this.createPaymentMethod(paymentMethodData)

	    // Step 2: create payment intent on the server side
	    .then(paymentMethod => {
	      if (paymentMethod.error) {
	        throw new Error(paymentMethod.error.message);
	      }
	      const paymentDetails = jQuery.extend(cart.paymentDetails, {
	        payment_method_id: paymentMethod.paymentMethod.id
	      });
	      return mpa_rest_post('/payments/prepare', {
	        payment_details: paymentDetails
	      });
	    })

	    // Step 3: confirm payment intent on the client side
	    .then(({
	      client_secret,
	      return_url
	    }) => {
	      return this.confirmPayment(client_secret, return_url)

	      // Handle errors
	      .then(confirmation => {
	        if (confirmation.error) {
	          throw new Error(confirmation.error.message);
	        } else {
	          return confirmation.paymentIntent;
	        }
	      });
	    })

	    // Step 4: build the final payment data
	    .then(paymentIntent => {
	      let paymentData = {
	        payment_method: this.name,
	        payment_intent_id: paymentIntent.id
	      };
	      if (paymentIntent.status == 'requires_action' && paymentIntent.next_action.type == 'redirect_to_url') {
	        paymentData.redirect_url = paymentIntent.next_action.redirect_to_url.url;
	      }
	      return paymentData;
	    })

	    // Catch any error
	    .catch(error => {
	      console.error('Unable to process payment.', error.message);
	      if (processArgs.error_handler != undefined) {
	        processArgs.error_handler(error.message);
	      }
	      throw error;
	    });
	  }
	}

	/**
	 * @since 1.23.0
	 */
	class PaymentMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'payment';
	    this.title = __('Payment methods', 'motopress-appointment');
	    this.customerDetails = {
	      name: '',
	      email: '',
	      phone: ''
	    };
	  }

	  /*
	   * @param {Cart} cart
	   */
	  provideCart(cart) {
	    this.cart = cart;
	  }

	  /**
	   * @see Cart.getCustomer()
	   *
	   * @returns {Object} customerDetails
	   * @returns {string} customerDetails.name
	   * @returns {string} customerDetails.email
	   * @returns {string} customerDetails.phone
	   */
	  getCustomerDetails() {
	    return this.cart ? this.cart.getCustomer() : {
	      name: '',
	      email: '',
	      phone: ''
	    };
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param return_url
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, return_url) {
	    const customerDetails = this.getCustomerDetails();
	    const elements = this.elements;

	    // Using a promise to handle the result of submit
	    return new Promise((resolve, reject) => {
	      elements.submit().then(({
	        error: submitError
	      }) => {
	        if (submitError) {
	          // Throwing an exception if there is an error
	          const errorMessage = submitError.message || '';
	          reject(new Error(errorMessage));
	        } else {
	          // Continuing if there is no error
	          resolve();
	        }
	      }).catch(error => {
	        // Handling other errors that occur during submit
	        reject(error);
	      });
	    }).then(() => {
	      var _customerDetails$name, _customerDetails$emai, _customerDetails$phon;
	      // API call to confirm the payment
	      return this.api.confirmPayment({
	        elements,
	        clientSecret: client_secret,
	        confirmParams: {
	          payment_method_data: {
	            billing_details: {
	              name: (_customerDetails$name = customerDetails?.name) !== null && _customerDetails$name !== void 0 ? _customerDetails$name : null,
	              email: (_customerDetails$emai = customerDetails?.email) !== null && _customerDetails$emai !== void 0 ? _customerDetails$emai : null,
	              phone: (_customerDetails$phon = customerDetails?.phone) !== null && _customerDetails$phon !== void 0 ? _customerDetails$phon : null,
	              address: {
	                line1: null,
	                line2: null,
	                city: null,
	                state: null,
	                country: null,
	                postal_code: null
	              }
	            }
	          },
	          return_url: return_url
	        },
	        redirect: 'if_required'
	      });
	    }).catch(error => {
	      // Handling errors that occur during payment confirmation
	      console.error('Error during payment confirmation:', error);
	      throw error;
	    });
	  }

	  /**
	   * @param {Cart} cart
	   * @param {Object} paymentDetails Gateway settings + booking details.
	   * @param {Object} processArgs Optional.
	   *        @param {Function} processArgs['error_handler'] Callback to log errors.
	   * @return {Promise}
	   */
	  processPayment(cart, paymentDetails, processArgs) {
	    // Step 1: create payment method on the client side
	    return mpa_rest_post('/payments/prepare', {
	      payment_details: cart.paymentDetails
	    })
	    // Step 2: confirm payment intent on the client side
	    .then(({
	      client_secret,
	      return_url
	    }) => {
	      return this.confirmPayment(client_secret, return_url)

	      // Handle errors
	      .then(confirmation => {
	        if (confirmation.error) {
	          throw new Error(confirmation.error.message);
	        } else {
	          return confirmation.paymentIntent;
	        }
	      });
	    })

	    // Step 3: build the final payment data
	    .then(paymentIntent => {
	      let paymentData = {
	        payment_method: this.name,
	        payment_intent_id: paymentIntent.id
	      };
	      if (paymentIntent.status === 'requires_action') {
	        /**
	         * @see StepBooking.isRedirectNeeded()
	         */
	        if (paymentIntent.next_action.type === 'redirect_to_url') {
	          /**
	           * If redirection is required, store the URL for later use
	           * But most stripe methods have a built-in redirect function
	           */
	          paymentData.redirect_url = paymentIntent.next_action.redirect_to_url.url;
	        } else {
	          /**
	           * In this case, it's likely that the client closed the payment pop-up,
	           * and the payment was not completed. Therefore, we throw an empty error.
	           * This halts the payment processing and prevents the form from advancing to the next step.
	           * The empty message is used to ensure that no error message is displayed to the user.
	           */
	          throw new Error('The user has cancelled or failed to complete the payment.');
	        }
	      }
	      return paymentData;
	    })

	    // Catch any error
	    .catch(error => {
	      if (error.message) {
	        console.error('Unable to process payment.', error.message);
	        throw error;
	      }
	      throw new Error('Unable to process payment.');
	    });
	  }

	  /**
	   * @see https://stripe.com/docs/js/elements_object/create_payment_element
	   *
	   * @return {Promise<Object>}
	   */
	  createControl() {
	    const customerDetails = this.getCustomerDetails();
	    return this.elements.create('payment', {
	      defaultValues: {
	        billingDetails: {
	          address: {
	            country: this.settings.country
	          }
	        }
	      },
	      fields: {
	        billingDetails: {
	          name: customerDetails?.name ? 'never' : 'auto',
	          email: customerDetails?.email ? 'never' : 'auto',
	          phone: customerDetails?.phone ? 'never' : 'auto',
	          address: {
	            line1: 'auto',
	            line2: 'auto',
	            city: 'auto',
	            state: 'auto',
	            country: 'auto',
	            postalCode: 'auto'
	          }
	        }
	      }
	    });
	  }
	}

	/**
	 * snake_case to camelCase
	 *
	 * @param {String}
	 * @return {String}
	 *
	 * @since 1.16.0
	 */
	function mpa_snake_to_camel_case(string) {
	  return string.toLowerCase().replace(/([-_][a-z])/g, group => group.toUpperCase().replace('-', '').replace('_', ''));
	}

	/**
	 * @since 1.14.0
	 */
	class CardMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'card';
	    this.title = __('Card', 'motopress-appointment');

	    /**
	     * Payment process event object with payment request button
	     * Necessary for correct processing by the processPayment() method and completion of the payment process
	     *
	     * @since 1.16.0
	     * @protected
	     *
	     * @type {Event}
	     */
	    this.paymentRequestButtonEvent = null;

	    /**
	     * @since 1.16.0
	     * @protected
	     *
	     * @type {Promise<array|null>}
	     */
	    this.canMakePaymentRequest = Promise.resolve(null);
	    if (this.isEnabledWallets()) {
	      this.paymentRequest = this.createPaymentRequest();
	      this.canMakePaymentRequest = this.paymentRequest.canMakePayment();
	    }
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @protected
	   *
	   * @return {PaymentRequest} Object of PaymentRequest
	   */
	  createPaymentRequest() {
	    if (this.paymentRequest) {
	      return this.paymentRequest;
	    }
	    return this.api.paymentRequest({
	      country: this.settings.country,
	      currency: mpapp().settings().getCurrency().toLowerCase(),
	      total: {
	        label: __('Total', 'motopress-appointment'),
	        amount: 0,
	        pending: true
	      },
	      requestPayerName: false,
	      requestPayerEmail: false,
	      requestPayerPhone: false,
	      requestShipping: false,
	      disableWallets: this.getDisabledWallets()
	    });
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @return {Promise<null|array>}
	   */
	  isCanMakePaymentRequest() {
	    return this.canMakePaymentRequest;
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @protected
	   *
	   * @return {string[]}
	   */
	  getPossibleWallets() {
	    return ['apple_pay', 'google_pay', 'link'];
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @protected
	   *
	   * @return {Boolean}
	   */
	  isEnabledWallets() {
	    let isEnabledWallets = false;
	    const wallets = this.getPossibleWallets();
	    wallets.forEach(wallet => {
	      if (this.settings.payment_methods.includes(wallet)) {
	        isEnabledWallets = true;
	      }
	    });
	    return isEnabledWallets;
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @return {Array} Array of disabled wallets name
	   */
	  getDisabledWallets() {
	    let disabledWallets = [];
	    const wallets = this.getPossibleWallets();
	    wallets.forEach(wallet => {
	      if (!this.settings.payment_methods.includes(wallet)) {
	        const walletNameInCamelCase = mpa_snake_to_camel_case(wallet);
	        disabledWallets.push(walletNameInCamelCase);
	      }
	    });
	    return disabledWallets;
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @see https://stripe.com/docs/js/elements_object/create_payment_element
	   *
	   * @protected
	   *
	   * @return {Object}
	   */
	  createPaymentRequestButton() {
	    return this.elements.create('paymentRequestButton', {
	      paymentRequest: this.paymentRequest,
	      style: {
	        paymentRequestButton: {
	          height: '50px'
	        }
	      }
	    });
	  }

	  /**
	   * @since 1.16.0
	   * @protected
	   *
	   * @param {Event}
	   */
	  processPaymentRequestButton(event) {
	    this.paymentRequestButtonEvent = event;
	    jQuery('.mpa-booking-step-payment .mpa-actions .mpa-button-next').trigger('click');
	  }

	  /**
	   * @since 1.16.0
	   *
	   * @param {Cart} cart
	   * @param {Object} processArgs Optional.
	   *        @param {Function} processArgs['error_handler'] Callback to log errors.
	   * @return {Promise}
	   */
	  proccessPaymentRequestButtonHandler(cart, processArgs) {
	    const customer = cart.getCustomer();
	    return this.api.createPaymentMethod({
	      type: 'card',
	      card: {
	        token: this.paymentRequestButtonEvent.token.id
	      },
	      billing_details: {
	        name: customer.name,
	        email: customer.email,
	        phone: customer.phone
	      }
	    }).then(paymentMethod => {
	      if (paymentMethod.error) {
	        this.paymentRequestButtonEvent.complete('fail');
	        throw new Error(paymentMethod.error.message);
	      }
	      const paymentDetails = jQuery.extend(cart.paymentDetails, {
	        payment_method_id: paymentMethod.paymentMethod.id
	      });
	      return mpa_rest_post('/payments/prepare', {
	        payment_details: paymentDetails
	      });
	    }).then(({
	      client_secret
	    }) => {
	      return this.confirmPayment(client_secret)

	      // Handle errors
	      .then(confirmation => {
	        if (confirmation.error) {
	          this.paymentRequestButtonEvent.complete('fail');
	          this.paymentRequestButtonEvent = null;
	          throw new Error(confirmation.error.message);
	        } else {
	          return confirmation.paymentIntent;
	        }
	      });
	    })

	    // Step 4: build the final payment data
	    .then(paymentIntent => {
	      let paymentData = {
	        payment_method: this.name,
	        payment_intent_id: paymentIntent.id
	      };
	      this.paymentRequestButtonEvent.complete('success');
	      this.paymentRequestButtonEvent = null;
	      return paymentData;
	    })

	    // Catch any error
	    .catch(error => {
	      this.paymentRequestButtonEvent.complete('fail');
	      this.paymentRequestButtonEvent = null;
	      console.error('Unable to process payment.', error.message);
	      if (processArgs.error_handler != undefined) {
	        processArgs.error_handler(error.message);
	      }
	      throw error;
	    });
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_card_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret) {
	    return this.api.confirmCardPayment(client_secret);
	  }

	  /**
	   * @param {Cart} cart
	   * @param {Object} paymentDetails Gateway settings + booking details.
	   * @param {Object} processArgs Optional.
	   *        @param {Function} processArgs['error_handler'] Callback to log errors.
	   * @return {Promise}
	   */
	  processPayment(cart, paymentDetails, processArgs) {
	    if (this.paymentRequestButtonEvent) {
	      return this.proccessPaymentRequestButtonHandler(cart, processArgs);
	    }
	    return super.processPayment(cart, paymentDetails, processArgs);
	  }

	  /**
	   * @see https://stripe.com/docs/js/elements_object/create_payment_element
	   *
	   * @return {Object}
	   */
	  createControl() {
	    return this.elements.create(this.name, {
	      style: this.settings.style,
	      hidePostalCode: this.settings.hide_postal_code
	    });
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class SepaDebitMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'sepa_debit';
	    this.title = __('SEPA Direct Debit', 'motopress-appointment');
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_sepa_debit_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    return this.api.confirmSepaDebitPayment(client_secret);
	  }

	  /**
	   * @see https://stripe.com/docs/js/elements_object/create_payment_element
	   *
	   * @return {Promise<Object>}
	   */
	  createControl() {
	    return this.elements.create('iban', {
	      style: this.settings.style,
	      supportedCountries: ['SEPA']
	    });
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class BancontactMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'bancontact';
	    this.title = __('Bancontact', 'motopress-appointment');
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_bancontact_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    return this.api.confirmBancontactPayment(client_secret, {
	      return_url: redirect_url_received
	    }, {
	      handleActions: false
	    });
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class IdealMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'ideal';
	    this.title = __('iDEAL', 'motopress-appointment');
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_ideal_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    return this.api.confirmIdealPayment(client_secret, {
	      return_url: redirect_url_received
	    }, {
	      handleActions: false
	    });
	  }

	  /**
	   * @see https://stripe.com/docs/js/elements_object/create_payment_element
	   *
	   * @return {Promise<Object>}
	   */
	  createControl() {
	    return this.elements.create('idealBank', {
	      style: this.settings.style
	    });
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class GiropayMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'giropay';
	    this.title = __('Giropay', 'motopress-appointment');
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_giropay_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    return this.api.confirmGiropayPayment(client_secret, {
	      return_url: redirect_url_received
	    }, {
	      handleActions: false
	    });
	  }
	}

	/**
	 * @since 1.14.0
	 */
	class SofortMethod extends AbstractMethod {
	  setupProperties() {
	    this.name = 'sofort';
	    this.title = __('SOFORT', 'motopress-appointment');
	  }

	  /**
	   *
	   * @param client_secret The client secret of the PaymentIntent.
	   * @param redirect_url_received
	   *
	   * @see {@link https://stripe.com/docs/js/payment_intents/confirm_sofort_payment}
	   *
	   * @return {Promise}
	   */
	  confirmPayment(client_secret, redirect_url_received) {
	    return this.api.confirmSofortPayment(client_secret, {
	      return_url: redirect_url_received
	    }, {
	      handleActions: false
	    });
	  }

	  /**
	   *
	   * @param {string} name
	   * @param {string} email
	   * @param {string} phone
	   *
	   * @return {Promise} paymentMethod or error
	   */
	  createPaymentMethodData(name, email, phone) {
	    let paymentMethodData = super.createPaymentMethodData(name, email, phone);
	    paymentMethodData.sofort = {
	      country: this.settings.country
	    };
	    return paymentMethodData;
	  }
	}

	/**
	 * @since 1.5.0
	 */
	class StripeGateway extends CustomizablePaymentGateway {
	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();

	    /**
	     * @since 1.5.0
	     * @var {Object}
	     */
	    this.$gatewayPreloader = null;

	    /**
	     * @since 1.5.0
	     * @var {String}
	     */
	    this.gatewayId = 'stripe';

	    /**
	     * @since 1.14.0
	     * @var {Object[]} Stripe payment methods
	     */
	    this.methods = null;

	    /**
	     * @since 1.5.0
	     * @var {StripeGatewayView}
	     */
	    this.view = null;
	  }

	  /**
	   * @since 1.5.0
	   * @since 1.6.0 Added cart parameter
	   *
	   * @param {Object} $mountWrapper
	   */
	  constructor($mountWrapper, cart) {
	    super($mountWrapper, cart);
	    this.$gatewayPreloader = $mountWrapper.parent().find('.mpa-payment-gateway-title .mpa-preloader');
	  }

	  /**
	   * Validation checks Terms & Conditions checkbox. Checkbox must be checked.
	   * If validation crash, showing default html validation message.
	   *
	   * todo: paypal has the same method, we need to move the check to another method
	   *
	   * @return {Boolean}
	   */
	  isValidAcceptTerms() {
	    if (!mpapp().settings().getTermsPageIdForAcceptance()) {
	      return true;
	    }
	    const acceptTermsMarkup = this.$mountWrapper.closest("form").find('.mpa-accept-terms')[0];
	    if (!acceptTermsMarkup.checkValidity()) {
	      acceptTermsMarkup.reportValidity();
	      return false;
	    }
	    return true;
	  }

	  /**
	   * @see Similar method to StripeAPI::convertToSmallestUnit() in StripeAPI.php.
	   *
	   * @since 1.16.0
	   *
	   * Only used for wallets payments in the paymentRequest object.
	   * This is necessary to display the correct price in the paymentRequest browser payment interface.
	   * The real payment price is formed on the backend side when the PaymentIntent is created.
	   *
	   * @param {Number} amount
	   * @param {String} currency Optional.
	   *
	   * @return {Number}
	   */
	  convertToSmallestUnit(amount, currency) {
	    if (!currency) {
	      currency = mpapp().settings().getCurrency();
	    }

	    // See all currencies presented as links on page
	    // https://stripe.com/docs/currencies#presentment-currencies
	    switch (currency.toUpperCase()) {
	      // Zero decimal currencies
	      case 'BIF':
	      case 'CLP':
	      case 'DJF':
	      case 'GNF':
	      case 'JPY':
	      case 'KMF':
	      case 'KRW':
	      case 'MGA':
	      case 'PYG':
	      case 'RWF':
	      case 'UGX':
	      case 'VND':
	      case 'VUV':
	      case 'XAF':
	      case 'XOF':
	      case 'XPF':
	        amount = Math.floor(amount); // Remove cents
	        break;
	      default:
	        amount = Math.round(amount * 100); // In cents
	        break;
	    }
	    return amount;
	  }
	  getFormattedTotalPrice() {
	    const order = this.cart.getOrder();
	    let totalPrice = parseFloat(order.total);
	    if (this.cart.paymentDetails.deposit) {
	      totalPrice = parseFloat(order.deposit);
	    }
	    return this.convertToSmallestUnit(totalPrice, mpapp().settings().getCurrency().toLowerCase());
	  }

	  /**
	   * @since 1.16.0
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  onClickPaymentRequestButton(event) {
	    if (!this.isValidAcceptTerms()) {
	      event.preventDefault();
	      return;
	    }
	    this.methods['card'].paymentRequest.update({
	      total: {
	        amount: this.getFormattedTotalPrice(),
	        label: __('Total', 'motopress-appointment'),
	        pending: false
	      }
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  onChange(event) {
	    this.haveErrors = !!event.error;
	    if (this.haveErrors) {
	      this.view.showError(event.error.message);
	    } else {
	      this.view.hideErrors();
	    }
	  }
	  onCartChange(cart) {
	    if (this.isMounted && 0 < this.getFormattedTotalPrice() && 0 === Object.keys(this.methods).length) {
	      // re-mount if cart total price is changed from 0 to bigger than 0
	      this.$mountWrapper.empty();
	      this.mount(this.$mountWrapper);
	    }
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {Object} $mountWrapper
	   */
	  mount($mountWrapper) {
	    this.ready().then(() => {
	      this.methods = [];
	      if (0 < this.getFormattedTotalPrice()) {
	        this.methods = this.createPaymentMethods();
	      }
	      this.view = new StripeGatewayView(this.methods);
	      this.view.mount($mountWrapper);
	      this.addListeners();
	    });
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @param {Cart} cart
	   * @param {Object} bookingDetails Booking and payment ID/UID.
	   * @return {Promise}
	   */
	  processPayment(cart, bookingDetails) {
	    if (!this.isValid()) {
	      return Promise.reject(new Error('The payment gateway is not valid.'));
	    }
	    this.$gatewayPreloader.removeClass('mpa-hide');
	    let paymentMethod = this.view.selectedMethod;
	    let paymentDetails = jQuery.extend({
	      payment_method: paymentMethod
	    }, this.settings, bookingDetails);
	    let processArgs = {
	      error_handler: this.view.showError.bind(this.view)
	    };
	    const processPayment = this.methods[paymentMethod].processPayment(cart, paymentDetails, processArgs);
	    return processPayment.then(paymentData => {
	      this.$gatewayPreloader.addClass('mpa-hide');
	      return paymentData;
	    }, error => {
	      this.$gatewayPreloader.addClass('mpa-hide');
	      throw error;
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Object}
	   */
	  getDefaults() {
	    return jQuery.extend(super.getDefaults(), {
	      hide_postal_code: true,
	      locale: 'auto',
	      payment_methods: [],
	      public_key: '',
	      style: {}
	    });
	  }

	  /**
	   * @since 1.14.0
	   *
	   * @return {Object[]} Stripe payment methods
	   */
	  createPaymentMethods() {
	    let methods = [];
	    const stripeApi = Stripe(this.settings.public_key, {
	      apiVersion: "2023-10-16"
	    });

	    // https://docs.stripe.com/elements/express-checkout-element
	    const stripeElements = stripeApi.elements({
	      mode: 'payment',
	      locale: this.settings.locale,
	      currency: mpapp().settings().getCurrency().toLowerCase(),
	      amount: this.getFormattedTotalPrice(),
	      payment_method_configuration: this.settings.payment_method_configuration
	    });
	    this.settings.payment_methods.forEach(method => {
	      switch (method) {
	        case 'payment':
	          methods['payment'] = new PaymentMethod(stripeApi, stripeElements, this.settings);
	          // Todo: maybe better pass var via constructor
	          methods['payment'].provideCart(this.cart);
	          break;
	        case 'card':
	          methods['card'] = new CardMethod(stripeApi, stripeElements, this.settings);
	          methods['card'].getControl().on('change', this.onChange.bind(this));

	          // todo: better to refactor
	          methods['card'].isCanMakePaymentRequest().then(canMakePayment => {
	            if (canMakePayment) {
	              methods['card'].paymentRequest.on('token', async event => methods['card'].processPaymentRequestButton(event));
	              methods['card'].paymentRequest.on('cancel', () => {
	                methods['card'].paymentRequestButtonEvent = null;
	              });
	              methods['card'].paymentRequestButton = methods['card'].createPaymentRequestButton();
	              methods['card'].paymentRequestButton.on('click', this.onClickPaymentRequestButton.bind(this));
	            }
	          });
	          break;
	        case 'sepa_debit':
	          methods['sepa_debit'] = new SepaDebitMethod(stripeApi, stripeElements, this.settings);
	          methods['sepa_debit'].getControl().on('change', this.onChange.bind(this));
	          break;
	        case 'bancontact':
	          methods['bancontact'] = new BancontactMethod(stripeApi, stripeElements, this.settings);
	          break;
	        case 'ideal':
	          methods['ideal'] = new IdealMethod(stripeApi, stripeElements, this.settings);
	          break;
	        case 'giropay':
	          methods['giropay'] = new GiropayMethod(stripeApi, stripeElements, this.settings);
	          break;
	        case 'sofort':
	          methods['sofort'] = new SofortMethod(stripeApi, stripeElements, this.settings);
	          break;
	      }
	    });
	    return methods;
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    if (this.methods) {
	      // resetting each of methods
	      Object.entries(this.methods).forEach(([methodName, method]) => {
	        method.reset();
	      });
	    }
	    if (this.view) {
	      this.view.reset();
	    }
	  }
	}

	/**
	 * @since 1.6.0
	 */
	class PayPalGateway extends CustomizablePaymentGateway {
	  /**
	   * @since 1.6.0
	   */
	  setupProperties() {
	    super.setupProperties();

	    /**
	     * @var {String}
	     */
	    this.gatewayId = 'paypal';
	  }

	  /**
	   * @since 1.6.0
	   */
	  enable() {
	    super.enable();
	    if (this.isEnabled) {
	      this.$mountWrapper.closest('form').find('.mpa-button-next').hide();
	    }
	  }

	  /**
	   * @since 1.6.0
	   */
	  disable() {
	    super.disable();
	    if (!this.isEnabled) {
	      this.$mountWrapper.closest('form').find('.mpa-button-next').show();
	    }
	  }

	  /**
	   * @since 1.6.0
	   * @access protected
	   * @param {Object} $mountWrapper
	   */
	  mount($mountWrapper) {
	    let self = this;
	    self.$errorWrapper = $mountWrapper.find('.mpa-paypal-error');
	    self.$gatewayPreloader = $mountWrapper.parent().find('.mpa-payment-gateway-title .mpa-preloader');
	    paypal.Buttons({
	      onInit(data, actions) {
	        if (mpapp().settings().getTermsPageIdForAcceptance()) {
	          const acceptTerms = self.$mountWrapper.closest("form").find('.mpa-accept-terms')[0];
	          if (!acceptTerms.checkValidity()) {
	            actions.disable();
	          }
	          acceptTerms.addEventListener('change', e => {
	            if (e.target.checked) {
	              actions.enable();
	            } else {
	              actions.disable();
	            }
	          });
	        }
	      },
	      onClick: function (data, actions) {
	        if (mpapp().settings().getTermsPageIdForAcceptance()) {
	          const acceptTerms = self.$mountWrapper.closest("form").find('.mpa-accept-terms')[0];
	          if (!acceptTerms.checkValidity()) {
	            acceptTerms.reportValidity();
	          }
	        }
	        if (0 === self.cart.getTotalPrice()) {
	          self.paypalDetails = {};
	          jQuery('.mpa-booking-step-payment .mpa-actions .mpa-button-next').trigger('click');
	        }
	      },
	      /**
	       * PayPal checkout server side integration.
	       * Creates PayPal order via REST API request from backend with a correct price.
	       *
	       * @param data
	       * @param actions
	       * @returns {Promise<string>} Order id
	       */
	      createOrder: function (data, actions) {
	        self.$errorWrapper.addClass('mpa-hide');
	        self.$gatewayPreloader.removeClass('mpa-hide');
	        return mpa_rest_post('/payments/prepare', {
	          payment_details: self.cart.paymentDetails
	        }).then(orderId => {
	          self.$gatewayPreloader.addClass('mpa-hide');
	          return orderId;
	        });
	      },
	      onApprove: function (data, actions) {
	        return actions.order.capture().then(function (details) {
	          self.paypalDetails = details;
	          jQuery('.mpa-booking-step-payment .mpa-actions .mpa-button-next').trigger('click');
	        });
	      },
	      onCancel: function (data) {},
	      onError: function (error) {
	        console.log(error);
	        self.$errorWrapper.text(self.settings.paypal_error_message);
	        self.$errorWrapper.removeClass('mpa-hide');
	      }
	    }).render($mountWrapper.find('.mpa-paypal-container')[0]);
	  }

	  /**
	   * @since 1.6.0
	   * @param {Cart} cart
	   * @param {Object} bookingDetails Booking and payment ID/UID.
	   * @return {Promise}
	   */
	  processPayment(cart, bookingDetails) {
	    return Promise.resolve({
	      paypalDetails: this.paypalDetails
	    });
	  }
	}

	/**
	 * @since 1.5.0
	 */
	class GatewayFactory {
	  /**
	   * @since 1.5.0
	   * @since 1.6.0 Added cart parameter
	   *
	   * @param {Object} $paymentGateways
	   * @return {PaymentGateway[]}
	   */
	  static createGateways($paymentGateways, cart) {
	    let activeGateways = {};
	    for (let gatewayId of mpapp().settings().getActiveGateways()) {
	      let $billingFields = $paymentGateways.find('.mpa-' + gatewayId + '-payment-gateway .mpa-billing-fields');
	      let gateway = $billingFields.length !== 0 ? GatewayFactory.createGateway(gatewayId, $billingFields, cart) : null;
	      if (gateway !== null) {
	        activeGateways[gatewayId] = gateway;
	      }
	    }
	    activeGateways['free'] = new FreeGateway({}, cart);
	    return activeGateways;
	  }

	  /**
	   * @since 1.5.0
	   * @since 1.6.0 Added cart parameter
	   * @access protected
	   *
	   * @return {PaymentGateway|null}
	   */
	  static createGateway(gatewayId, $billingFields, cart) {
	    switch (gatewayId) {
	      case 'manual':
	      case 'test':
	      case 'cash':
	      case 'bank':
	        return new PaymentGateway($billingFields, cart);
	      case 'paypal':
	        return new PayPalGateway($billingFields, cart);
	      case 'stripe':
	        return new StripeGateway($billingFields, cart);
	      default:
	        return wp.hooks.applyFilters('mpa_create_gateway', null, gatewayId, $billingFields, cart);
	    }
	  }
	}

	/**
	 * @since 1.5.0
	 */
	class StepPayment extends AbstractStep {
	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();
	    this.lastCartHash = ''; // Track cart changes

	    this.gatewayId = '';
	    this.gateways = {}; // {Gateway ID: Gateway object}

	    this.bookingDetails = {}; // Booking and payment ID/UID

	    this.$form = this.$element.find('.mpa-checkout-form');
	    this.$order = this.$element.find('.mpa-order');
	    this.$billingSection = this.$element.find('.mpa-billing-details');
	    this.$paymentGateways = this.$billingSection.find('.mpa-payment-gateway');
	    this.$paymentGatewayButtons = this.$paymentGateways.find('input[name="payment_gateway_id"]');
	    this.$message = this.$element.find('.mpa-message').first();
	    this.acceptTerms = false;
	    this.onlinePayment = false;
	    this.isDepositDisabled = false;
	    this.$deposit = this.$element.find('.mpa-deposit-section');
	    this.$depositSwitcher = this.$element.find('input[name="mpa-deposit-switcher"]');
	    this.$depositTable = this.$element.find('#mpa-deposit-table');
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms = this.$element.find('.mpa-accept-terms');
	    }
	    this.couponSection = null;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {String}
	   */
	  theId() {
	    return 'payment';
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Object} {Property name: Property settings}
	   */
	  propertiesSchema() {
	    return {
	      gatewayId: {
	        type: 'string',
	        default: ''
	      },
	      isDepositDisabled: {
	        type: 'bool',
	        default: false
	      },
	      acceptTerms: {
	        type: 'bool',
	        default: false
	      }
	    };
	  }

	  /**
	   * @param {String} message
	   *
	   * @since 1.19.1
	   */
	  setErrorMessage(message) {
	    this.$message.html(message);
	    this.$message.toggleClass('mpa-hide', !message.trim().length);
	  }

	  /**
	   * @since 1.19.1
	   */
	  clearErrorMessage() {
	    this.setErrorMessage('');
	  }

	  /**
	   * @since 1.14.0
	   */
	  hideDeposit() {
	    this.$deposit.addClass('mpa-hide');
	    this.$depositSwitcher.prop('disabled', true);
	    this.isDepositDisabled = true;
	  }

	  /**
	   * @since 1.14.0
	   */
	  showDeposit() {
	    this.$deposit.removeClass('mpa-hide');
	    this.$depositSwitcher.prop('disabled', false);
	    this.setProperty('isDepositDisabled', this.$depositSwitcher.prop('checked'));
	  }

	  /**
	   * @since 1.14.0
	   */
	  toggleDepositSection() {
	    const order = this.cart.getOrder();
	    const leftToPay = parseFloat(order.total) - parseFloat(order.deposit);
	    if (leftToPay && this.onlinePayment) {
	      this.showDeposit();
	    } else {
	      this.hideDeposit();
	    }
	  }

	  /**
	   * Set payment gateway name to payment details.
	   * Toggle deposit functions If it unavailable for setted gateway
	   *
	   * @param {string} gatewayId Payment gateway name
	   * @param {integer} isOnlinePayment 0 or 1
	   * @since 1.14.0
	   */
	  setGatewayId(gatewayId, isOnlinePayment) {
	    this.setProperty('gatewayId', gatewayId);
	    this.onlinePayment = parseInt(isOnlinePayment);
	    this.toggleDepositSection();
	    this.cart.setPaymentDetails({
	      gateway_id: this.gatewayId,
	      deposit: !this.isDepositDisabled
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  addListeners() {
	    super.addListeners();

	    // Disable submit
	    this.$form.on('submit', event => false);
	    this.$paymentGatewayButtons.on('change', event => {
	      this.setGatewayId(event.target.value, event.target.dataset.isOnlinePayment);
	    });
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms.on('input', event => this.setProperty('acceptTerms', event.target.checked));
	    }
	    if (this.$depositSwitcher.length > 0) {
	      this.$depositSwitcher.on('input', event => {
	        this.$depositTable.toggleClass('mpa-hide', event.target.checked);
	        this.setProperty('isDepositDisabled', event.target.checked);
	        this.cart.setPaymentDetails({
	          deposit: !this.isDepositDisabled
	        });
	      });
	    }
	    this.$element.on('mpa_booking_step_disable', this.disable.bind(this));
	    this.$element.on('mpa_booking_step_enable', this.enable.bind(this));
	    this.$element.on('mpa_booking_coupon_applied', () => {
	      this.notifyCartChanged();
	      this.updateOrderDetails();
	      this.cart.setPaymentDetails({
	        coupon_code: this.cart.hasCoupon() ? this.cart.coupon.getCode() : ''
	      });
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Promise}
	   */
	  loadEntities() {
	    // show payment step before start loading.
	    // preloader toggling in another place.
	    if (!this.isLoaded) {
	      this.$element.removeClass('mpa-hide');
	    }
	    this.lastCartHash = this.cart.getHash('order');

	    // Initialize coupon section control
	    if (mpapp().settings().isCouponsEnabled()) {
	      this.couponSection = new CouponSection(this.$element.find('.mpa-coupon-details'), this.cart);
	    }

	    // Init/update the order
	    this.updateOrderDetails();

	    // Load gateways
	    let awaitLoading = [];
	    if (this.gatewayId !== 'free') {
	      awaitLoading.push(this.loadGateways());
	    } else {
	      this.loadGateways(); // Just load in the background
	    }

	    // Draft posts
	    awaitLoading.push(this.loadDrafts());
	    return Promise.all(awaitLoading).then(() => {
	      // Actions after loaded all entities and inited gateways

	      // init default gateway by :checked markup received from backend
	      // calling enable() for needle gateway
	      // which mount loaded control element to checkout markup
	      this.initDefaultGateway();
	      return this;
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @return {Promise}
	   * @access protected
	   */
	  reload() {
	    this.clearErrorMessage();

	    // Is the coupon still applicable to the cart?
	    if (this.cart.hasCoupon()) {
	      this.cart.testCoupon();
	    }

	    // Clear previous messages
	    if (this.couponSection) {
	      if (this.cart.hasCoupon()) {
	        this.couponSection.clearMessage();
	      } else {
	        this.couponSection.reset();
	      }
	    }

	    // Init/update the order
	    this.updateOrderDetails();

	    // Maybe notify payment gateways about cart changes
	    if (this.cart.didChange(this.lastCartHash, 'order')) {
	      this.lastCartHash = this.cart.getHash('order');
	      this.notifyCartChanged();

	      // Update booking draft on the backend
	      return this.loadDrafts();
	    }

	    // Trigger reload drafts if needed
	    if (wp.hooks.applyFilters('mpa_booking_reload_drafts', false)) {
	      // Update booking draft on the backend
	      return this.loadDrafts();
	    }
	    return Promise.resolve(this);
	  }

	  /**
	   * @since 1.6.2
	   */
	  reset() {
	    if (mpapp().settings().getTermsPageIdForAcceptance()) {
	      this.$acceptTerms.prop('checked', false);
	      this.resetProperty('acceptTerms');
	    }
	    this.lastCartHash = '';

	    // Enable default gateway
	    let defaultGateway = mpapp().settings().getDefaultPaymentGateway();
	    this.$paymentGatewayButtons.filter(':checked').prop('checked', false);
	    if (defaultGateway in this.gateways) {
	      this.setProperty('gatewayId', defaultGateway);
	      this.$paymentGatewayButtons.filter('[value="' + defaultGateway + '"]').prop('checked', true);
	    } else {
	      this.resetProperty('gatewayId');
	    }

	    // Reset payment gateways
	    for (let gatewayId in this.gateways) {
	      this.gateways[gatewayId].reset();
	    }

	    // Reset coupon section
	    if (this.couponSection) {
	      this.couponSection.reset();
	    }
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @access protected
	   */
	  notifyCartChanged() {
	    for (let gatewayId in this.gateways) {
	      this.gateways[gatewayId].onCartChange(this.cart);
	    }
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  updateOrderDetails() {
	    this.$order.empty();
	    this.$order.html(mpa_tmpl_order(this.cart.getOrder()));
	    if (this.$depositTable.length > 0) {
	      const depositTableHtml = mpa_tmpl_deposit(this.cart.getOrder());
	      this.$depositTable.html(depositTableHtml);
	      if (this.$paymentGatewayButtons.filter(':checked').length > 0) {
	        this.toggleDepositSection();
	      }
	    }

	    // Bind remove coupon button
	    let $removeCoupon = this.$order.find('.mpa-remove-coupon');
	    if ($removeCoupon.length > 0) {
	      $removeCoupon.on('click', this.removeCoupon.bind(this));
	    }
	    this.toggleAvailablePaymentMethods();
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @access protected
	   *
	   * @param {Event} event
	   */
	  removeCoupon(event) {
	    event.preventDefault();
	    event.stopPropagation();
	    this.cart.removeCoupon();
	    this.couponSection.clearMessage();
	    this.cart.setPaymentDetails({
	      coupon_code: ''
	    });
	    this.notifyCartChanged();
	    this.updateOrderDetails();
	    this.couponSection.reset();
	  }

	  /**
	   * Hide unavailable payment methods for order.
	   *
	   * @since 1.14.0
	   * @access protected
	   */
	  toggleAvailablePaymentMethods() {
	    const isFreeOrder = this.cart.getTotalPrice() === 0;
	    if (isFreeOrder) {
	      this.setGatewayId('free', false);
	    } else {
	      const $currentGateway = this.$paymentGatewayButtons.filter(':checked');
	      if ($currentGateway.length > 0) {
	        this.setGatewayId($currentGateway[0].value, $currentGateway[0].dataset.isOnlinePayment);
	      }
	    }
	    this.$billingSection.toggleClass('mpa-hide', isFreeOrder);
	    this.$paymentGatewayButtons.prop('required', !isFreeOrder);
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Promise[]}
	   */
	  loadGateways() {
	    let $paymentGateways = this.$billingSection.find('.mpa-payment-gateways');
	    this.gateways = GatewayFactory.createGateways($paymentGateways, this.cart);
	    let awaitGateways = [];
	    for (let gatewayId in this.gateways) {
	      awaitGateways.push(this.gateways[gatewayId].load());
	    }
	    return awaitGateways;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Promise}
	   */
	  loadDrafts() {
	    let cartData = this.cart.toArray();
	    cartData.payment = true;
	    return mpa_rest_post('/bookings/draft', wp.hooks.applyFilters('mpa_booking_draft_data', cartData)).then(drafts => {
	      this.bookingDetails = drafts;
	      /**
	       * @todo: param paymentDetails[payment_id] need only for backward compatibility with a mpa-woocommerce addon v1.0.0
	       */
	      const paymentDetails = {
	        booking_id: drafts.booking_id,
	        payment_id: drafts.payment_id
	      };
	      this.cart.setPaymentDetails(paymentDetails);
	    }, error => {
	      this.setErrorMessage(error.message);
	    }).then(() => {
	      this.enableGateways();
	      return this;
	    });
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  enableGateways() {
	    this.$paymentGatewayButtons.prop('disabled', false);
	  }

	  /**
	   * @since 1.15.0
	   * @access protected
	   */
	  initDefaultGateway() {
	    let $defaultGateway = this.$paymentGatewayButtons.filter(':checked');
	    if ($defaultGateway.length > 0) {
	      this.gateways[$defaultGateway.val()].enable();
	    }
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @return {Boolean}
	   */
	  isValidInput() {
	    return this.isValidGatewayId() && this.isValidGateway() && this.isValidAcceptTerms();
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {Boolean} 
	   */
	  isValidGatewayId() {
	    return this.gatewayId !== '';
	  }

	  /**
	   * @since 1.5.0
	   *
	   * @access protected
	   *
	   * @return {Boolean}
	   */
	  isValidGateway() {
	    if (this.gatewayId in this.gateways) {
	      return this.gateways[this.gatewayId].isValid();
	    } else {
	      return true;
	    }
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @access protected
	   *
	   * @since 1.10.2
	   */
	  isValidAcceptTerms() {
	    return !mpapp().settings().getTermsPageIdForAcceptance() || this.acceptTerms;
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @param {String} propertyName
	   * @param {*} newValue
	   * @param {*} oldValue
	   */
	  afterUpdate(propertyName, newValue, oldValue) {
	    // Toggle gateways
	    if (oldValue in this.gateways) {
	      this.gateways[oldValue].disable();
	    }
	    if (newValue in this.gateways) {
	      this.gateways[newValue].enable();
	    }
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   */
	  react() {
	    super.react();

	    // Always enable submit button to allow validation messages
	    this.$buttonNext.prop('disabled', false);
	  }

	  /**
	   * @since 1.5.0
	   * @access protected
	   *
	   * @return {undefined|Promise}
	   */
	  maybeSubmit() {
	    // Disable coupons section
	    if (this.couponSection) {
	      this.couponSection.disable();
	    }
	    if (this.gatewayId in this.gateways) {
	      let gateway = this.gateways[this.gatewayId];
	      let result = gateway.processPayment(this.cart, this.bookingDetails);

	      // If a Promise
	      if (typeof result === 'object' && typeof result.then === 'function') {
	        result.then(paymentData => {
	          this.cart.setPaymentDetails(paymentData);
	          return paymentData;
	        }, error => {
	          this.setErrorMessage(error.message);
	        }

	        // Leave errors to AbstractStep.submit() or re-throw it, so
	        // AbstractStep may cancel submission
	        );
	      }

	      return result;
	    }
	  }

	  /**
	   * @since 1.11.0
	   *
	   * @access protected
	   */
	  cancelSubmission() {
	    super.cancelSubmission();
	    if (this.couponSection) {
	      this.couponSection.enable();
	    }
	  }
	}

	/**
	 * @param {Object} element jQuery or DOM element.
	 * @param {Object} args
	 * @return {Object}
	 *
	 * @since 1.2.1
	 */
	function mpa_flatpickr(element, args) {
	  let locale = args.locale || mpapp().settings().getFlatpickrLocale();
	  let l10n = flatpickr.l10ns[locale] || locale;

	  // Use first day of the week value from Settings > General > Week Starts On
	  if (typeof l10n == 'object') {
	    l10n.firstDayOfWeek = mpapp().settings().getFirstDayOfWeek();
	  }

	  // Merge args
	  let defaultArgs = {
	    formatDate: mpa_format_date,
	    inline: true,
	    locale: l10n,
	    monthSelectorType: 'static',
	    showMonths: 1
	  };
	  args = jQuery.extend({}, defaultArgs, args);

	  // Create instance
	  let instance = null;
	  if (element instanceof jQuery) {
	    instance = flatpickr(element[0], args);
	  } else {
	    instance = flatpickr(element, args);
	  }
	  return instance;
	}

	/**
	 * @since 1.0
	 */
	class StepPeriod extends AbstractStep {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    super.setupProperties();

	    /**
	     * @var {CartItem}
	     */
	    this.cartItem = null;
	    this.lastHash = ''; // Track cart changes

	    /**
	     * @var {Object} {
	     *     'YYYY-MM': {
	     *         'Y-m-d': {
	     *             Time period (string): [
	     *                 // Variations
	     *                 [ Employee ID, Location ID, Free capacity ],
	     *                 [ Employee 2, Location 2, Free capacity ],
	     *                 ...
	     *             ],
	     *         }
	     *     },
	     * }
	     */
	    this.monthSlots = {};
	    this.date = '';
	    this.time = '';
	    this.datepicker = null;
	    this.$dateWrapper = this.$element.find('.mpa-date-wrapper');
	    this.$dateInput = this.$element.find('.mpa-date');
	    this.$timeWrapper = this.$element.find('.mpa-time-wrapper');
	    this.$times = this.$timeWrapper.find('.mpa-times');

	    /**
	     * Look ahead to find first available day and slot.
	     *
	     * @since 1.19.0
	     */
	    this.lookedAheadMonths = 0;

	    /** @since 1.19.0 */
	    this.maxLookAheadMonths = 12;

	    /**
	     * Already selected the first date/time slot after the first render of
	     * the calendar.
	     *
	     * @since 1.19.0
	     */
	    this.isSelectedFirstAvailableSlot = false;

	    /**
	     * @since 2.0.0
	     *
	     * @var {AvailabilityService}
	     */
	    this.availabilityService = null;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {AvailabilityService} availabilityService
	   */
	  setAvailabilityService(availabilityService) {
	    this.availabilityService = availabilityService;
	  }

	  /**
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  theId() {
	    return 'period';
	  }

	  /**
	   * @return {String} 'cart'|'cart item'
	   *
	   * @since 1.9.0
	   */
	  getCartContext() {
	    return 'cart item';
	  }

	  /**
	   * @return {Object}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  propertiesSchema() {
	    return {
	      date: {
	        type: 'string',
	        default: ''
	      },
	      time: {
	        type: 'string',
	        default: ''
	      }
	    };
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    super.addListeners();
	    this.$dateInput.on('change', event => this.setProperty('date', event.target.value));
	  }

	  /**
	   * @return {Promise}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  loadEntities() {
	    this.cartItem = this.cart.getActiveItem();
	    this.lastHash = this.cartItem.getHash('availability');
	    return Promise.resolve(this);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  reload() {
	    // Did something changed?
	    if (this.cartItem.didChange(this.lastHash, 'availability')) {
	      // Reset all and reload
	      this.$element.removeClass('mpa-loaded');
	      this.resetDate(); // This also resets the time
	      this.readyPromise = this.loadEntities();

	      // Reset saved time slots
	      this.monthSlots = {};

	      // Re-enable days
	      if (this.datepicker != null) {
	        this.setEnabledDays([]);
	        this.readyPromise.finally(() => this.resetEnabledDays());
	      }
	      return this.readyPromise;
	    } else {
	      return Promise.resolve(this);
	    }
	  }

	  /**
	   * Reset step before a new item.
	   *
	   * @since 1.4.0
	   */
	  reset() {
	    this.cartItem = this.cart.getActiveItem();
	    this.lastHash = '';
	    this.monthSlots = {};
	    this.resetDate();
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isValidInput() {
	    return this.date != '' && this.time != '';
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  resetDate() {
	    // Will also reset the time in afterUpdate()
	    this.resetProperty('date');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  resetTime() {
	    this.$times.empty();
	    this.resetProperty('time');
	  }

	  /**
	   * @param {Array}
	   *
	   * @since 1.0
	   */
	  setEnabledDays(allowedDays) {
	    if (!mpa_empty(allowedDays, true)) {
	      this.datepicker.set('enable', allowedDays);
	    } else {
	      // Just a date in the past to disable all days. With [] Flatpickr
	      // allows all dates and disables nothing
	      this.datepicker.set('enable', ['2000-01-01']);
	    }
	  }

	  /**
	   * Executed only when the new value is different from the old one.
	   *
	   * @param {String} propertyName
	   * @param {*} newValue
	   * @param {*} oldValue
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  afterUpdate(propertyName, newValue, oldValue) {
	    if (propertyName == 'date') {
	      if (newValue == '') {
	        this.resetTime();
	      } else {
	        this.resetTimeSlots();
	      }
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  react() {
	    super.react();

	    // Toggle time slots
	    this.$timeWrapper.toggleClass('mpa-hide', this.date == '');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  showReady() {
	    super.showReady();

	    // Show datepicker
	    if (this.datepicker == null) {
	      this.showDatepicker();

	      // Enable days for current month
	      this.resetEnabledDays();
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  showDatepicker() {
	    this.datepicker = mpa_flatpickr(this.$dateInput, this.getDatepickerArgs());
	  }

	  /**
	   * @access protected
	   */
	  getDatepickerArgs() {
	    return {
	      minDate: mpapp().settings().getBusinessDate(),
	      // Don't allow to go to
	      // the previous months
	      onMonthChange: () => this.resetEnabledDays()
	    };
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  maybeSubmit() {
	    let cartItem = this.cartItem;
	    cartItem.date = mpa_parse_date(this.date);
	    cartItem.time = new TimePeriod(this.time);

	    /**
	     * @todo: It's necessary to use setDate because the TimePeriod constructor
	     *     takes a time period in the form of a string like "08:00 - 14:00".
	     *     This string does not contain any date information. It would be
	     *     better to pass the time period in the form of two ready Date
	     *     variables, which will contain information about the date and time.
	     */
	    if (cartItem.date && cartItem.time) {
	      cartItem.time.setDate(cartItem.date);
	    }

	    // Autoselect employee/location, if needed
	    if (cartItem.employee === null || cartItem.location === null) {
	      let autoselectedIds = this.autoselectIds();
	      let autoEmployeeId = autoselectedIds[0];
	      let autoLocationId = autoselectedIds[1];
	      if (cartItem.employee === null) {
	        cartItem.setEmployee(autoEmployeeId, false);
	      }
	      if (cartItem.location === null) {
	        cartItem.setLocation(autoLocationId, false);
	      }
	    }

	    // Save available variations for selected time slot
	    let monthKey = this.getCurrentMonthKey();
	    this.cartItem.setBookingVariants(this.monthSlots[monthKey][this.date][this.time]);

	    // Trigger events
	    document.dispatchEvent(new CustomEvent('mpa_add_to_cart', {
	      detail: {
	        cartItem: cartItem,
	        currencyCode: mpapp().settings().getCurrency()
	      }
	    }));
	    document.dispatchEvent(new CustomEvent('mpa_view_cart', {
	      detail: {
	        cart: this.cart,
	        currencyCode: mpapp().settings().getCurrency()
	      }
	    }));
	  }

	  /**
	   * After loading time slots of month, we set the first available timeslot as
	   * the initial value of the calendar.
	   *
	   * @since 1.19.0
	   */
	  selectFirstDateTimeSlot() {
	    let year = this.datepicker.currentYear;
	    let month = this.datepicker.currentMonth;
	    let monthKey = this.getMonthKey(year, month);
	    const slotsForThisMonth = this.monthSlots[monthKey];
	    if (slotsForThisMonth && Object.keys(slotsForThisMonth).length > 0) {
	      // Get the first available date and time
	      const firstDate = Object.keys(slotsForThisMonth)[0];
	      const firstTimeSlot = Object.keys(slotsForThisMonth[firstDate])[0];

	      // Programmatically set the date value for Flatpickr and call the change handler
	      this.datepicker.setDate(firstDate, true);
	      const targetTimeElement = this.$times.children('.mpa-time-period').filter((index, elem) => {
	        return elem.getAttribute('date-time') === firstTimeSlot;
	      });
	      targetTimeElement.trigger('click');
	      this.isSelectedFirstAvailableSlot = true;
	    } else {
	      // Make scroll of calendar for first render after loading
	      // and skip scroll of calendar for calendar scroll by user actions
	      if (this.isSelectedFirstAvailableSlot === true) {
	        return;
	      }

	      // Prevent recursive loop
	      if (this.lookedAheadMonths >= this.maxLookAheadMonths) {
	        this.datepicker.changeMonth(-this.lookedAheadMonths);
	        this.isSelectedFirstAvailableSlot = true;
	        return;
	      }
	      this.lookedAheadMonths += 1;

	      // If no available slots for the current month, move to the next month
	      this.datepicker.changeMonth(1);

	      // Reload timeslots for new month and then again call
	      // resetEnabledDays() => selectFirstDateTimeSlot() method to try
	      // again with the next month To be careful. This is recursion.
	      this.reload();
	    }
	  }

	  /**
	   * @return {Number[]} [Employee ID, Location ID]
	   *
	   * @access protected
	   *
	   * @since 1.2.1
	   */
	  autoselectIds() {
	    let ids = [0, 0];
	    let monthKey = this.getCurrentMonthKey();
	    if (this.monthSlots[monthKey] && this.monthSlots[monthKey][this.date]) {
	      let timeSlots = this.monthSlots[monthKey][this.date];

	      // Search for the proper period
	      for (let timeStr in timeSlots) {
	        if (timeStr === this.time) {
	          let employees = timeSlots[timeStr];

	          // Get the first pair
	          ids[0] = employees[0][0]; // Copy values, not
	          ids[1] = employees[0][1]; // the array pointer

	          break;
	        }
	      }
	    }
	    return ids;
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @access private
	   *
	   * @return {Promise}
	   */
	  waitForServiceToLoad() {
	    let serviceReadyPromise = this.availabilityService.getServicePromise();
	    if (serviceReadyPromise !== null) {
	      return serviceReadyPromise;
	    } else {
	      return Promise.resolve(this.cartItem.getService());
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  resetEnabledDays() {
	    // Reset date & time
	    this.resetDate(); // This also resets the time

	    // Disable all days
	    this.setEnabledDays([]);

	    // Add preloader
	    this.$dateWrapper.removeClass('mpa-loaded');

	    // Get new enabled days
	    let year = this.datepicker.currentYear;
	    let month = this.datepicker.currentMonth;
	    let monthKey = this.getMonthKey(year, month);

	    // Query time slots
	    let queryPromise = null;
	    if (!this.monthSlots[monthKey]) {
	      // Query slots for new month
	      let serviceId = this.cartItem.service.id;
	      let dateFrom = new Date(year, month, 1);
	      let dateTo = new Date(year, month + 1, 1); // June 1, 00:00 - July 1, 00:00

	      queryPromise = mpa_time_slots(serviceId, dateFrom, dateTo, this.getTimeSlotsQueryArgs());
	    } else {
	      // Get already loaded month
	      queryPromise = Promise.resolve(this.monthSlots[monthKey]);
	    }

	    // Wait for service with preloader
	    Promise.all([queryPromise, this.waitForServiceToLoad()])
	    // Update enabled days
	    .then(response => {
	      let daySlots = response[0]; // [ daySlots, service ]

	      // Save slots for future
	      this.monthSlots[monthKey] = daySlots;
	      this.setEnabledDays(Object.keys(daySlots));
	      this.$dateWrapper.addClass('mpa-loaded');
	      this.selectFirstDateTimeSlot();
	    });
	  }

	  /**
	   * @access protected
	   */
	  getTimeSlotsQueryArgs() {
	    let employeeId = this.cartItem.getEmployeeId();
	    let locationId = this.cartItem.getLocationId();
	    return {
	      employee_in: employeeId ? [employeeId] : this.cartItem.getAvailableEmployeeIds(),
	      location_in: locationId ? [locationId] : this.cartItem.getAvailableLocationIds(),
	      exclude_cart: this.cart.toArray('items')
	    };
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  resetTimeSlots() {
	    // Clear time
	    this.resetTime();

	    // Get time slots
	    let timeSlots = {};
	    let monthKey = this.getCurrentMonthKey();
	    if (this.monthSlots[monthKey][this.date] != undefined) {
	      timeSlots = this.monthSlots[monthKey][this.date];
	    }

	    // Add buttons to the $times
	    let slotsAdded = 0;
	    for (let timeStr in timeSlots) {
	      let period = new TimePeriod(timeStr); // '10:00 - 11:00'

	      // '10:00<span class="mpa-period-end-time"> - 11:00</span>', so we
	      // can use CSS to hide second part in narrow columns
	      let periodLabel = period.toString('public', '<span class="mpa-period-end-time"> - ') + '</span>';

	      // Add free places counter
	      let service = this.cartItem.getService();
	      if (service.isGroupService()) {
	        let maxCapacity = service.getMinCapacity();
	        for (let bookingVariant of timeSlots[timeStr]) {
	          maxCapacity = Math.max(maxCapacity, bookingVariant[3]);
	        }
	        periodLabel += ' ';
	        periodLabel += '<span class="mpa-slot-capacity">';
	        periodLabel += '<span class="mpa-slot-capacity-label">' + service.getQuantityLabel() + ':</span>';
	        periodLabel += '&nbsp;';
	        periodLabel += '<span class="mpa-slot-capacity-number">' + maxCapacity + '</span>';
	        periodLabel += '</span>';
	      }

	      // Add button
	      let periodButton = mpa_tmpl_button(periodLabel, {
	        'class': 'button button-secondary mpa-time-period',
	        'date-time': timeStr
	      });
	      this.$times.append(periodButton);
	      slotsAdded++;
	    }

	    // Add listeners or error message
	    if (slotsAdded > 0) {
	      this.$times.children('.mpa-time-period').on('click', event => this.onTime(event, event.currentTarget));
	    } else {
	      this.$times.text(__('Sorry, but we were unable to allocate time slots for the date you selected.', 'motopress-appointment'));
	    }
	  }

	  /**
	   * @param {Number} year
	   * @param {Number} month
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.2.1
	   */
	  getMonthKey(year, month) {
	    if (month <= 8) {
	      // '2021-04'
	      return year + '-0' + (month + 1);
	    } else {
	      // '2021-10'
	      return year + '-' + (month + 1);
	    }
	  }

	  /**
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.2.1
	   */
	  getCurrentMonthKey() {
	    if (this.date !== '') {
	      let date = mpa_parse_date(this.date);
	      return this.getMonthKey(date.getFullYear(), date.getMonth());
	    } else {
	      return '2000-01';
	    }
	  }

	  /**
	   * @param {Event} event
	   * @param {Button} button Clicked button.
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  onTime(event, button) {
	    // Toggle class ".mpa-time-period-selected"
	    this.$times.children('.mpa-time-period-selected').removeClass('mpa-time-period-selected');
	    button.classList.add('mpa-time-period-selected');

	    // Update time property
	    this.setProperty('time', button.getAttribute('date-time'));
	  }
	}

	/**
	 * @since 1.0
	 */
	class StepServiceForm extends AbstractStep {
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupProperties() {
	    super.setupProperties();

	    /**
	     * @var {AvailabilityService}
	     */
	    this.availabilityService = null;
	    this.category = '';
	    this.serviceId = 0;
	    this.employeeId = 0;
	    this.locationId = 0;
	    this.isHiddenStep = true;
	    this.$form = this.$element.find('.mpa-service-form');
	    this.$categories = this.$element.find('.mpa-service-category-wrapper');
	    this.$services = this.$element.find('.mpa-service-wrapper');
	    this.$employees = this.$element.find('.mpa-employee-wrapper');
	    this.$locations = this.$element.find('.mpa-location-wrapper');
	    this.$selects = this.$element.find('.mpa-input-wrapper select');
	    this.$categoriesSelect = this.$selects.filter('.mpa-service-category');
	    this.$servicesSelect = this.$selects.filter('.mpa-service');
	    this.$employeesSelect = this.$selects.filter('.mpa-employee');
	    this.$locationsSelect = this.$selects.filter('.mpa-location');
	    this.unselectedServiceText = this.$servicesSelect.children('[value=""]').text();
	    this.unselectedOptionText = this.$selects.filter('.mpa-optional-select').first().find('option:first').text();
	  }

	  /**
	   * @since 2.0.0
	   *
	   * @param {AvailabilityService} availabilityService
	   */
	  setAvailabilityService(availabilityService) {
	    this.availabilityService = availabilityService;
	  }

	  /**
	   * @return {String}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  theId() {
	    return 'service-form';
	  }

	  /**
	   * @return {String} 'cart'|'cart item'
	   *
	   * @since 1.9.0
	   */
	  getCartContext() {
	    return 'cart item';
	  }

	  /**
	   * @return {Object}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  propertiesSchema() {
	    return {
	      category: {
	        type: 'string',
	        default: ''
	      },
	      serviceId: {
	        type: 'integer',
	        default: 0
	      },
	      employeeId: {
	        type: 'integer',
	        default: 0
	      },
	      locationId: {
	        type: 'integer',
	        default: 0
	      }
	    };
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    super.addListeners();
	    this.$form.on('submit', this.submitForm.bind(this));
	    this.$categoriesSelect.on('change', event => this.setProperty('category', event.target.value));
	    this.$servicesSelect.on('change', event => this.setProperty('serviceId', event.target.value));
	    this.$employeesSelect.on('change', event => this.setProperty('employeeId', event.target.value));
	    this.$locationsSelect.on('change', event => this.setProperty('locationId', event.target.value));
	  }
	  isHiddenElementByProp($element) {
	    const isHidden = $element.attr('data-is-hidden');
	    if (typeof isHidden !== 'undefined' && isHidden !== 'false') {
	      return true;
	    }
	    return false;
	  }
	  initCategoriesSelect() {
	    if (this.$categoriesSelect.length == 0) {
	      return; // The select is disabled by parameter show_*
	    }

	    this.updateCategorySchema();
	    let value = this.$categoriesSelect.val();
	    let isHiddenSelect = this.isHiddenElementByProp(this.$categoriesSelect);
	    if (this.$categoriesSelect.attr('data-default')) {
	      const defaultValue = this.$categoriesSelect.attr('data-default');
	      if (this.isValidCategoryBySchema(defaultValue)) {
	        value = defaultValue;
	      } else {
	        isHiddenSelect = false;
	      }
	    }
	    this.setProperty('category', value);
	    this.renderCategorySelect();
	    if (!isHiddenSelect) {
	      this.isHiddenStep = false;
	    }
	    this.$categories.toggleClass('mpa-hide', isHiddenSelect);
	  }
	  initServicesSelect() {
	    if (this.$servicesSelect.length == 0) {
	      return; // The select is disabled by parameter show_*
	    }

	    this.updateServiceSchema();
	    let value = this.$servicesSelect.val();
	    let isHiddenSelect = this.isHiddenElementByProp(this.$servicesSelect);
	    if (this.$servicesSelect.attr('data-default')) {
	      const defaultValue = mpa_intval(this.$servicesSelect.attr('data-default'));
	      if (this.isValidServiceBySchema(defaultValue)) {
	        value = defaultValue;
	      } else {
	        isHiddenSelect = false;
	      }
	    }
	    this.setProperty('serviceId', value);
	    this.renderServiceSelect();
	    if (!isHiddenSelect) {
	      this.isHiddenStep = false;
	    }
	    this.$services.toggleClass('mpa-hide', isHiddenSelect);
	  }
	  initEmployeesSelect() {
	    if (this.$employeesSelect.length == 0) {
	      return; // The select is disabled by parameter show_*
	    }

	    this.updateEmployeeSchema();
	    let value = this.$employeesSelect.val();
	    let isHiddenSelect = this.isHiddenElementByProp(this.$employeesSelect);
	    if (this.$employeesSelect.attr('data-default')) {
	      const defaultValue = mpa_intval(this.$employeesSelect.attr('data-default'));
	      if (this.isValidEmployeeBySchema(defaultValue)) {
	        value = defaultValue;
	      } else {
	        isHiddenSelect = false;
	      }
	    }
	    this.setProperty('employeeId', value);
	    this.renderEmployeeSelect();
	    if (!isHiddenSelect) {
	      this.isHiddenStep = false;
	    }
	    this.$employees.toggleClass('mpa-hide', isHiddenSelect);
	  }
	  initLocationsSelect() {
	    if (this.$locationsSelect.length == 0) {
	      return; // The select is disabled by parameter show_*
	    }

	    this.updateLocationSchema();
	    let value = this.$locationsSelect.val();
	    let isHiddenSelect = this.isHiddenElementByProp(this.$locationsSelect);
	    if (this.$locationsSelect.attr('data-default')) {
	      const defaultValue = mpa_intval(this.$locationsSelect.attr('data-default'));
	      if (this.isValidLocationBySchema(defaultValue)) {
	        value = defaultValue;
	      } else {
	        isHiddenSelect = false;
	      }
	    }
	    this.setProperty('locationId', value);
	    this.renderLocationSelect();
	    if (!isHiddenSelect) {
	      this.isHiddenStep = false;
	    }
	    this.$locations.toggleClass('mpa-hide', isHiddenSelect);
	  }

	  /**
	   * @return {Promise}
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  loadEntities() {
	    return this.availabilityService.ready().finally(() => {
	      // Hide other empty selects or hide selects with the "data-is-hidden"
	      // setting, excluding unselected and required selects.
	      this.initServicesSelect();
	      this.initCategoriesSelect(); // needs to be initialized after this.initServicesSelect(),
	      // because hiding the field depends on hiding the services
	      // select field.
	      this.initEmployeesSelect();
	      this.initLocationsSelect();
	      return this;
	    });
	  }

	  /**
	   * Reset step before a new item.
	   *
	   * @since 1.4.0
	   */
	  reset() {
	    let $selects = {
	      category: this.$categoriesSelect,
	      serviceId: this.$servicesSelect,
	      employeeId: this.$employeesSelect,
	      locationId: this.$locationsSelect
	    };
	    this.preventReact = true;

	    // Set default values
	    for (let propertyName in $selects) {
	      let $select = $selects[propertyName];
	      let defaultValue = $select.attr('data-default');
	      if (defaultValue) {
	        this.setProperty(propertyName, defaultValue);
	      } else {
	        this.resetProperty(propertyName);
	      }
	    }
	    this.preventReact = false;

	    // Render changes if this step is active
	    if (this.isActive) {
	      this.react();
	    }
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isValidInput() {
	    return this.serviceId != 0;
	  }

	  /**
	   * @since 1.19.0
	   *
	   * Save allowed options for validateProperty()
	   */
	  updateCategorySchema() {
	    const categories = this.availabilityService.getAvailableServiceCategories();
	    this.schema.category.options = Object.keys(categories);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * Save allowed options for validateProperty()
	   */
	  updateServiceSchema() {
	    const services = this.availabilityService.getAvailableServices(this.category, this.locationId, this.employeeId);
	    this.schema.serviceId.options = Object.keys(services).map(mpa_intval);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * Save allowed options for validateProperty()
	   */
	  updateEmployeeSchema() {
	    const employees = this.availabilityService.getAvailableEmployees(this.serviceId, this.locationId);
	    this.schema.employeeId.options = Object.keys(employees).map(mpa_intval);
	  }

	  /**
	   * @since 1.19.0
	   *
	   * Save allowed options for validateProperty()
	   */
	  updateLocationSchema() {
	    const locations = this.availabilityService.getAvailableLocations(this.serviceId, this.employeeId);
	    this.schema.locationId.options = Object.keys(locations).map(mpa_intval);
	  }

	  /**
	   * @param {String} categorySlug
	   * @return {Boolean}
	   */
	  isValidCategoryBySchema(categorySlug) {
	    return this.schema.category.options.includes(categorySlug);
	  }

	  /**
	   * @param {Number} serviceId
	   * @return {Boolean}
	   */
	  isValidServiceBySchema(serviceId) {
	    return this.schema.serviceId.options.includes(serviceId);
	  }

	  /**
	   * @param {Number} locationId
	   * @return {Boolean}
	   */
	  isValidLocationBySchema(locationId) {
	    return this.schema.locationId.options.includes(locationId);
	  }

	  /**
	   * @param {Number} locationId
	   * @return {Boolean}
	   */
	  isValidEmployeeBySchema(employeeId) {
	    return this.schema.employeeId.options.includes(employeeId);
	  }

	  /**
	   * Executed only when the new value is different from the old one.
	   *
	   * @param {String} propertyName
	   * @param {*} newValue
	   * @param {*} oldValue
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  afterUpdate(propertyName, newValue, oldValue) {
	    this.updateCategorySchema();
	    this.updateServiceSchema();
	    this.updateEmployeeSchema();
	    this.updateLocationSchema();
	    if ('category' === propertyName) {
	      let availableServices = this.availabilityService.getAvailableServices(this.category, this.locationId, this.employeeId);
	      if (!(this.serviceId in availableServices)) {
	        this.resetProperty('serviceId');
	        this.resetProperty('employeeId');
	        this.resetProperty('locationId');
	      }
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  react() {
	    super.react();

	    // Update dependent <select>'s
	    this.$categoriesSelect.val(this.category || '');
	    this.$servicesSelect.val(this.serviceId || '');
	    this.$employeesSelect.val(this.employeeId);
	    this.$locationsSelect.val(this.locationId);
	    this.$categoriesSelect.toggleClass('mpa-selected', this.category != '');
	    this.$servicesSelect.toggleClass('mpa-selected', this.serviceId != 0);
	    this.$employeesSelect.toggleClass('mpa-selected', this.employeeId != 0);
	    this.$locationsSelect.toggleClass('mpa-selected', this.locationId != 0);

	    // re-renden select options
	    this.renderCategorySelect();
	    this.renderServiceSelect();
	    this.renderEmployeeSelect();
	    this.renderLocationSelect();

	    // Always enable submit button to allow validation messages
	    this.$buttonNext.prop('disabled', false);
	  }

	  /**
	   * @since 1.19.0
	   * @access protected
	   */
	  renderCategorySelect() {
	    this.preventUpdate = true;
	    update_select_options(this.$categoriesSelect, {
	      '': this.unselectedOptionText
	    }, this.availabilityService.getAvailableServiceCategories(), this.category || '');
	    this.preventUpdate = false;
	  }

	  /**
	   * @since 1.19.0
	   * @access protected
	   */
	  renderServiceSelect() {
	    this.preventUpdate = true;
	    let availableServices = this.availabilityService.getAvailableServices(this.category, this.locationId, this.employeeId);
	    update_select_options(this.$servicesSelect, {
	      '': this.unselectedServiceText
	    }, availableServices, this.serviceId in availableServices ? this.serviceId : '');
	    this.preventUpdate = false;
	  }

	  /**
	   * @since 1.19.0
	   * @access protected
	   */
	  renderEmployeeSelect() {
	    this.preventUpdate = true;
	    update_select_options(this.$employeesSelect, {
	      0: this.unselectedOptionText
	    }, this.availabilityService.getAvailableEmployees(this.serviceId, this.locationId), this.employeeId);
	    this.preventUpdate = false;
	  }

	  /**
	   * @since 1.19.0
	   * @access protected
	   */
	  renderLocationSelect() {
	    this.preventUpdate = true;
	    update_select_options(this.$locationsSelect, {
	      0: this.unselectedOptionText
	    }, this.availabilityService.getAvailableLocations(this.serviceId, this.employeeId), this.locationId);
	    this.preventUpdate = false;
	  }
	  show() {
	    this.$servicesSelect.prop('required', true);
	    super.show();
	  }
	  hide() {
	    super.hide();
	    this.$servicesSelect.prop('required', false);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  enable() {
	    super.enable();

	    // Enable all selects
	    this.$selects.prop('disabled', false);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  disable() {
	    super.disable();

	    // Disable all selects
	    this.$selects.prop('disabled', true);
	  }

	  /**
	   * @param {Event} event
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  submitForm(event) {
	    // Never submit the form, but allow validation
	    if (!this.isActive || this.isValidInput()) {
	      event.preventDefault();
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  maybeSubmit() {
	    let cartItem = this.cart.getActiveItem();
	    if (cartItem === null) {
	      return console.error('Unable to get active cart item in StepServiceForm.maybeSubmit().');
	    }

	    // Save service
	    cartItem.setService(this.availabilityService.getService(this.serviceId, true, () => {
	      document.dispatchEvent(new CustomEvent('mpa_view_item', {
	        detail: {
	          cartItem: cartItem,
	          currencyCode: mpapp().settings().getCurrency()
	        }
	      }));
	    }));
	    cartItem.setServiceCategories(this.availabilityService.getServiceCategories(this.serviceId));

	    // Save employee(-s)
	    if (this.employeeId !== 0) {
	      cartItem.setEmployee(this.availabilityService.getEmployee(this.employeeId));
	    } else {
	      cartItem.setAvailableEmployees(this.availabilityService.filterAvailableEmployees(this.serviceId, this.locationId, 'entities'));
	    }

	    // Save location(-s)
	    if (this.locationId !== 0) {
	      cartItem.setLocation(this.availabilityService.getLocation(this.locationId));
	    } else {
	      let allowedEmployees = this.employeeId || cartItem.getAvailableEmployeeIds();
	      cartItem.setAvailableLocations(this.availabilityService.filterAvailableLocations(this.serviceId, allowedEmployees, 'entities'));
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class AppointmentFormShortcode {
	  /**
	   * @since 1.0
	   */
	  constructor($element) {
	    this.$element = $element;
	    this.$message = this.$element.children('.mpa-message');
	    this.cart = new Cart();
	    this.steps = new BookingSteps(this.cart);
	    this.load(); // And show()
	  }

	  /**
	   * @since 1.9.0
	   * @access protected
	   */
	  setupSteps() {
	    this.steps.addStep(new StepServiceForm(this.$element.find('.mpa-booking-step-service-form'), this.cart)).addStep(new StepPeriod(this.$element.find('.mpa-booking-step-period'), this.cart)).addStep(new StepCart(this.$element.find('.mpa-booking-step-cart'), this.cart)).addStep(new StepCheckout(this.$element.find('.mpa-booking-step-checkout'), this.cart));
	    if (mpapp().settings().isPaymentsEnabled()) {
	      this.steps.addStep(new StepPayment(this.$element.find('.mpa-booking-step-payment'), this.cart));
	    }
	    this.steps.addStep(new StepBooking(this.$element.find('.mpa-booking-step-booking'), this.cart));
	    this.steps.mount(this.$element);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  load() {
	    // Create the initial cart item for cart item steps
	    this.cart.createItem();

	    // Check if we have any service to book
	    let availabilityService = new AvailabilityService();
	    Promise.all([availabilityService.load(), mpapp().settings().ready()]).finally(() => {
	      // Setup steps only when the settings are loaded
	      this.setupSteps();
	      this.steps.getStep('service-form').setAvailabilityService(availabilityService);
	      this.steps.getStep('period').setAvailabilityService(availabilityService);
	      this.show();
	      if (!availabilityService.isEmpty()) {
	        this.steps.goToNextStep(); // Show the first step
	      } else {
	        this.$message.html(__('Sorry, there are no services, employees or locations to book.', 'motopress-appointment'));
	        this.$message.removeClass('mpa-hide');
	      }
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  show() {
	    // Remove loading GIF
	    this.$element.addClass('mpa-loaded');
	  }
	}

	/**
	 * @since 1.3
	 */
	class AppointmentFormWidget extends AppointmentFormShortcode {
	  /**
	   * @since 1.6.0
	   */
	  constructor($element) {
	    super($element.children('.widget-body').first());
	  }
	}

	// Init shortcodes and widgets
	jQuery('.appointment-form-shortcode').each((i, element) => {
	  new AppointmentFormShortcode(jQuery(element));
	});
	jQuery('.appointment-form-widget').each((i, element) => {
	  new AppointmentFormWidget(jQuery(element));
	});

	// init links for sending reservations to calendars in account and reservation received page
	jQuery(document).ready(function () {
	  // we need to wait for loading timezone setting from server
	  mpapp().settings().ready().then(() => {
	    jQuery('.mpa-booking-details-section [data-reservation-id]').each((index, reservationEement) => {
	      let $reservationEement = jQuery(reservationEement);
	      const reservationId = $reservationEement.data('reservation-id');
	      const startTime = new Date($reservationEement.data('start-time'));
	      const endTime = new Date($reservationEement.data('end-time'));
	      const title = $reservationEement.data('service-name');
	      const summary = $reservationEement.data('employee-name') + '. ' + $reservationEement.data('quantity-label') + ': ' + $reservationEement.data('capacity') + '.';
	      const locationName = $reservationEement.data('location-name');
	      const ICSDownloadUrl = AddToCalendar.createICSURL(reservationId, startTime, endTime, title, summary, locationName);
	      const googleCalendarURL = AddToCalendar.createGoogleCalendarURL(startTime, endTime, title, summary, locationName);
	      const yahooCalendarURL = AddToCalendar.createYahooCalendarURL(startTime, endTime, title, summary, locationName);
	      $reservationEement.find('.mpa-add-to-calendar-link--google').attr('href', googleCalendarURL);
	      $reservationEement.find('.mpa-add-to-calendar-link--apple').attr('href', ICSDownloadUrl);
	      $reservationEement.find('.mpa-add-to-calendar-link--outlook').attr('href', ICSDownloadUrl);
	      $reservationEement.find('.mpa-add-to-calendar-link--yahoo').attr('href', yahooCalendarURL);
	    });
	  });
	});

})(wp.date, mpaData, intlTelInput);
