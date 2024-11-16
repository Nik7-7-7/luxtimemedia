(function (date, intlTelInput, mpaData) {
	'use strict';

	/**
	 * @since 1.0
	 */
	class BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.0
	   */
	  constructor($element) {
	    this.$element = $element;
	    this.type = $element.data('type');
	    this.$element.attr('data-inited', 'true');
	  }
	}

	/**
	 * @since 1.1.0
	 */
	class ColorPickerField extends BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.1.0
	   */
	  constructor($element) {
	    super($element);
	    this.$input = $element.find('input').first();
	    this.$input.spectrum();
	  }
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
	const sprintf = typeof wp !== 'undefined' && wp.i18n && wp.i18n.sprintf ? wp.i18n.sprintf : localSprintf;

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
	 * @since 1.11.0
	 */
	class DateField extends BasicField {
	  constructor($element) {
	    super($element);
	    this.$dateInput = this.$element.find('.mpa-date-input').first();
	    this.datepicker = null; // See initDatepicker()
	    this.displayFormat = this.$element.data('display-format');
	    this.sizeClass = this.$element.data('size');
	    this.initDatepicker();
	    this.removePreloader();
	  }

	  /**
	   * @access protected
	   */
	  initDatepicker() {
	    this.datepicker = mpa_flatpickr(this.$dateInput, {
	      altFormat: this.displayFormat,
	      altInput: true,
	      altInputClass: 'mpa-alt-date-input ' + this.sizeClass,
	      inline: false,
	      showMonths: 2
	    });

	    // Set disabled
	    if (this.$dateInput.prop('disabled')) {
	      this.$element.find('.mpa-alt-date-input').prop('disabled', true);
	    }
	  }

	  /**
	   * @access protected
	   */
	  removePreloader() {
	    this.$element.find('.mpa-preloader').remove();
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
	 * @since 1.2
	 */
	class ImageField extends BasicField {
	  /**
	   * @param {Object} $element jQuery element.
	   *
	   * @since 1.2
	   */
	  constructor($element) {
	    super($element);
	    this.setupProperties();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  setupProperties() {
	    // Selectors must be compatible with Edit Category page
	    this.$input = this.$element.find('input[type="hidden"]');
	    this.$preview = this.$element.find('.mpa-preview-wrapper > img');
	    this.$addButton = this.$element.find('.mpa-add-media');
	    this.$removeButton = this.$element.find('.mpa-remove-media');
	    this.thumbnailSize = this.$input.attr('thumbnail-size');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  addListeners() {
	    this.$preview.on('click', this.selectMedia.bind(this));
	    this.$addButton.on('click', this.selectMedia.bind(this));
	    this.$removeButton.on('click', this.removeMedia.bind(this));
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.2
	   */
	  getRawValue() {
	    return this.$input.val();
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.2
	   */
	  getValue() {
	    return mpa_intval(this.getRawValue());
	  }

	  /**
	   * @param {*} value
	   *
	   * @since 1.2
	   */
	  setValue(value) {
	    this.updateValue(value);
	    this.react();
	  }

	  /**
	   * @param {*} value
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  updateValue(value) {
	    this.$input.val(value);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  react() {
	    let isSet = mpa_boolval(this.getValue());
	    this.$addButton.toggleClass('mpa-hide', isSet);
	    this.$removeButton.toggleClass('mpa-hide', !isSet);
	    if (isSet) {
	      this.updatePreview();
	    } else {
	      this.resetPreview();
	    }
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  updatePreview() {
	    let attachment = wp.media.attachment(this.getValue());
	    let previewSrc = attachment.attributes.sizes[this.thumbnailSize].url;
	    this.$preview.removeClass('mpa-hide').attr('src', previewSrc);
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  resetPreview() {
	    this.$preview.addClass('mpa-hide').attr('src', '');
	  }

	  /**
	   * @param {Event} event
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  selectMedia(event) {
	    event.preventDefault();
	    let media = wp.media({
	      multiple: false
	    });
	    media.open().on('select', event => {
	      let image = media.state().get('selection').first();
	      let imageId = image.toJSON().id;
	      this.setValue(imageId);
	    });
	  }

	  /**
	   * @param {Event} event
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  removeMedia(event) {
	    event.preventDefault();
	    this.setValue('');
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
	 * @since 1.22.0
	 */
	class PhoneField extends BasicField {
	  /**
	   * @param {Object} $element
	   */
	  constructor($element) {
	    super($element);
	    this.$input = $element.find('input').first();
	    mpa_intl_tel_input(this.$input);
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

	/**
	 * @param {Number|String} minutes
	 * @param {String} format Optional. 'public', 'internal' ('H:i') or custom time
	 *     format. 'public' by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_format_minutes(minutes, format = 'public') {
	  minutes %= 1440; // Stay in range 00:00 - 23:59

	  let hours = parseInt(minutes / 60);
	  minutes = minutes % 60;
	  let date = mpa_today();
	  date.setHours(hours, minutes);
	  return mpa_format_time(date, format);
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
	 * @param {String} name
	 * @param {String} className Optional. Additional class to set. '' by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_dashicon(name, className = '') {
	  let classes = `dashicons dashicons-${name} ${className}`;
	  return '<span class="' + classes.trimRight() + '"></span>';
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
	 * @param {Date} startDate
	 * @param {Date} endDate
	 * @param {String} format Optional. 'public', 'short' or 'internal'. 'public'
	 *     by default.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_date_period(startDate, endDate, format = 'public') {
	  let dateFormat = format == 'short' ? 'public' : format;
	  let startDateStr = mpa_format_date(startDate, dateFormat);
	  let endDateStr = mpa_format_date(endDate, dateFormat);
	  if (format == 'short' && startDateStr == endDateStr) {
	    return startDateStr;
	  } else {
	    return startDateStr + ' - ' + endDateStr;
	  }
	}

	/**
	 * @param {Number} postId
	 * @param {String} title Optional.
	 * @return {String}
	 *
	 * @since 1.0
	 */
	function mpa_tmpl_edit_post_link(postId, title = '') {
	  let editUrl = `wp-admin/post.php?post=${postId}&action=edit`;
	  return '<a href="' + editUrl + '" title="' + title + '">' + title + '</a>';
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
	 * @since 1.2
	 */
	class AttributesField extends BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.2
	   */
	  constructor($element) {
	    super($element);
	    this.setupProperties();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  setupProperties() {
	    this.$table = this.$element.find('table');
	    this.$rows = this.$table.children('tbody');
	    this.$addButton = this.$element.find('.mpa-add-button');
	    this.baseName = this.$element.attr('data-base-name');
	    this.rows = {}; // {Row ID: Row jQuery element}
	    this.rowsCount = 0;

	    // Find rows
	    this.$element.find('.mpa-attribute').each((i, element) => {
	      let $row = jQuery(element);
	      let id = $row.attr('data-id');
	      this.rows[id] = $row;
	      this.rowsCount++;
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  addListeners() {
	    let self = this;

	    // Add button
	    this.$addButton.on('click', () => {
	      this.addRow();
	    });

	    // Remove buttons
	    this.$element.find('.mpa-remove-button').on('click', function () {
	      self.removeRowByElement(this);
	    });
	  }

	  /**
	   * @since 1.2
	   */
	  addRow() {
	    let id = mpa_uniqid();

	    // Add new row
	    let newRowHtml = this.renderRow(id);
	    this.$rows.append(newRowHtml);

	    // Save new row
	    let $row = this.$rows.find('[data-id="' + id + '"]');
	    this.rows[id] = $row;
	    this.rowsCount++;

	    // Show table
	    this.$table.removeClass('mpa-hide');

	    // Add listeners for new buttons
	    let self = this;
	    $row.find('.mpa-remove-button').on('click', function () {
	      self.removeRowByElement(this);
	    });
	  }

	  /**
	   * @param {String} id New row ID.
	   * @return {String} New row HTML.
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  renderRow(id) {
	    let namePrefix = this.baseName + '[' + id + ']'; // "_mpa_attributes[5ee2f5005b982]"
	    let output = '';
	    output += '<tr class="mpa-attribute" data-id="' + id + '">';
	    output += '<td class="column-label">';
	    output += '<input type="text" name="' + namePrefix + '[label]" value="" class="large-text">';
	    output += '</td>';
	    output += '<td class="column-content">';
	    output += '<input type="text" name="' + namePrefix + '[content]" value="" class="large-text">';
	    output += '</td>';
	    output += '<td class="column-link">';
	    output += '<input type="text" name="' + namePrefix + '[link]" value="" class="large-text">';
	    output += '</td>';
	    output += '<td class="column-class">';
	    output += '<input type="text" name="' + namePrefix + '[class]" value="" class="large-text">';
	    output += '</td>';
	    output += '<td class="column-actions">' + mpa_tmpl_dashicon('trash', 'mpa-remove-button') + '</td>';
	    output += '</tr>';
	    return output;
	  }

	  /**
	   * @param {Element} element
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  removeRowByElement(element) {
	    let rowId = jQuery(element).parents('.mpa-attribute').attr('data-id');
	    if (rowId) {
	      this.removeRow(rowId);
	    }
	  }

	  /**
	   * @param {String} id Row ID.
	   *
	   * @since 1.2
	   */
	  removeRow(id) {
	    if (!this.rows.hasOwnProperty(id)) {
	      return;
	    }
	    this.rows[id].remove();
	    this.rowsCount--;
	    delete this.rows[id];
	    if (this.rowsCount == 0) {
	      this.$table.addClass('mpa-hide');
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class CustomWorkdaysField extends BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.0
	   */
	  constructor($element) {
	    super($element);
	    this.$table = this.$element.children('table');
	    this.$tableBody = this.$table.children('tbody');
	    this.$noItemsRow = this.$tableBody.children('.no-items');
	    this.$newPeriodRow = this.$tableBody.children('.mpa-new-period');
	    this.timeSelects = this.$newPeriodRow.find('.mpa-period');
	    this.$startTimeHoursInput = this.$newPeriodRow.find('.mpa-period__start-hours');
	    this.$startTimeMinutesInput = this.$newPeriodRow.find('.mpa-period__start-minutes');
	    this.$endTimeHoursInput = this.$newPeriodRow.find('.mpa-period__end-hours');
	    this.$endTimeMinutesInput = this.$newPeriodRow.find('.mpa-period__end-minutes');
	    this.$timeAllDayInput = this.$newPeriodRow.find('.mpa-period__all-day');
	    this.baseName = this.$element.attr('data-base-name');
	    this.datepicker = null;

	    // A set of {Period ID: Period dates and time}
	    this.periods = {
	      length: 0,
	      add: function (id, value) {
	        let isNew = this[id] == undefined;
	        this[id] = value;
	        if (isNew) {
	          this.length++;
	        }
	      },
	      remove: function (id) {
	        if (this[id] == undefined) {
	          return;
	        }
	        delete this[id];
	        this.length--;
	      },
	      hasItems: function () {
	        return this.length > 0;
	      }
	    };
	    this.parseInitialState();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  parseInitialState() {
	    this.$tableBody.children(':not(.no-items, .mpa-new-period)').each((i, element) => {
	      let periodId = element.getAttribute('data-id');
	      let periodValue = jQuery(element).find('.column-actions > input').val();
	      this.periods.add(periodId, periodValue);
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  initDatepicker() {
	    this.datepicker = mpa_flatpickr(this.$tableBody.find('.mpa-new-period > .column-dates > input'), {
	      mode: 'range'
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    this.$timeAllDayInput.on('change', e => {
	      if (e.target.checked) {
	        this.timeSelects.hide();
	      } else {
	        this.timeSelects.show();
	      }
	    });

	    // Add button in the head
	    this.$table.find('thead .mpa-add-button').on('click', () => {
	      this.toggleEdit();
	    });

	    // Add button near the datepicker
	    this.$tableBody.find('.mpa-add-button').on('click', () => {
	      this.onAdd();
	    });
	    let self = this;
	    this.$tableBody.find('.mpa-remove-button').on('click', function () {
	      self.removePeriodByParent(this);
	    });
	  }

	  /**
	   * @since 1.0
	   */
	  toggleEdit() {
	    if (this.$newPeriodRow.hasClass('mpa-hide')) {
	      this.$newPeriodRow.removeClass('mpa-hide');
	      if (this.datepicker == null) {
	        this.initDatepicker();
	      } else {
	        this.datepicker.clear();
	      }
	    } else {
	      this.$newPeriodRow.addClass('mpa-hide');
	    }
	  }

	  /**
	   * @param {String} dates Dates interval.
	   * @param {String} time Time interval.
	   * @param {String} datesView Dates interval view.
	   * @param {String} timeView Time interval view.
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addPeriod(dates, time, datesView, timeView) {
	    let period = dates + ', ' + time;
	    let periodId = mpa_uniqid();
	    let periodHtml = this.renderPeriod(periodId, dates, time, datesView, timeView);

	    // Add period
	    jQuery(periodHtml).insertAfter(this.$newPeriodRow);
	    this.periods.add(periodId, period);

	    // Add listener for new button
	    let removeButton = this.$tableBody.find('[data-id="' + periodId + '"] .mpa-remove-button');
	    let self = this;
	    removeButton.on('click', function () {
	      self.removePeriodByParent(this);
	    });

	    // Hide the edit row and "no-items" row
	    this.toggleEdit();
	    this.$noItemsRow.addClass('mpa-hide');
	  }

	  /**
	   * @param {String} periodId
	   * @param {String} dates Dates interval.
	   * @param {String} time Time interval.
	   * @param {String} datesView Dates interval view.
	   * @param {String} timeView Time interval view.
	   * @return {String} New period HTML.
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  renderPeriod(periodId, dates, time, datesView, timeView) {
	    let period = dates + ', ' + time;
	    let output = '';
	    output += '<tr class="mpa-period" data-id="' + periodId + '">';
	    output += '<td class="column-dates mpa-badge-new">';
	    output += datesView;
	    output += '</td>';
	    output += '<td class="column-time mpa-badge-new">';
	    output += timeView;
	    output += '</td>';
	    output += '<td class="column-actions">';
	    output += '<input type="hidden" name="' + this.baseName + '[]" value="' + period + '">';
	    output += mpa_tmpl_button(__('Remove', 'motopress-appointment'), {
	      'class': 'button button-secondary mpa-remove-button'
	    });
	    output += '</td>';
	    output += '</tr>';
	    return output;
	  }

	  /**
	   * @return {Number}
	   */
	  getStartTime() {
	    let startTimeInMinutes = 0;
	    if (!this.$timeAllDayInput.prop('checked')) {
	      startTimeInMinutes = parseInt(this.$startTimeHoursInput.val()) * 60 + parseInt(this.$startTimeMinutesInput.val());
	    }
	    return startTimeInMinutes;
	  }

	  /**
	   * @return {Number}
	   */
	  getEndTime() {
	    let endTimeInMinutes = 0;
	    if (!this.$timeAllDayInput.prop('checked')) {
	      endTimeInMinutes = parseInt(this.$endTimeHoursInput.val()) * 60 + parseInt(this.$endTimeMinutesInput.val());
	    }
	    return endTimeInMinutes;
	  }
	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  onAdd() {
	    let startTime = this.getStartTime();
	    let endTime = this.getEndTime();
	    let dateRangeSelected = this.datepicker.selectedDates.length >= 2;

	    // if endTime = 0 than it could be a full day rental
	    if (!dateRangeSelected || startTime === endTime && 0 !== endTime || startTime > endTime && 0 !== endTime) {
	      return;
	    }
	    let startDate = this.datepicker.selectedDates[0];
	    let endDate = this.datepicker.selectedDates[1];
	    let dates = mpa_format_date(startDate, 'internal') + ' - ' + mpa_format_date(endDate, 'internal');
	    let datesView = mpa_tmpl_date_period(startDate, endDate, 'short');
	    let time = mpa_format_minutes(startTime, 'internal') + ' - ' + mpa_format_minutes(endTime, 'internal');
	    let timeView = mpa_format_minutes(startTime) + ' - ' + mpa_format_minutes(endTime);
	    if (0 === startTime && 0 === startTime && startTime === endTime) {
	      timeView = __('All day', 'motopress-appointment');
	    }
	    this.addPeriod(dates, time, datesView, timeView);
	  }

	  /**
	   * @param {Element} caller
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  removePeriodByParent(caller) {
	    let $periodRow = jQuery(caller).parents('tr.mpa-period');
	    if ($periodRow.length == 0) {
	      return;
	    }

	    // Remove the period from the list
	    let periodId = $periodRow.attr('data-id');
	    this.periods.remove(periodId);
	    if (!this.periods.hasItems()) {
	      this.$noItemsRow.removeClass('mpa-hide');
	    }

	    // Remove the row in the table
	    $periodRow.remove();
	  }
	}

	/**
	 * @since 1.0
	 */
	class DaysOffField extends BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.0
	   */
	  constructor($element) {
	    super($element);
	    this.$table = this.$element.children('table');
	    this.$tableBody = this.$table.children('tbody');
	    this.$noItemsRow = this.$tableBody.children('.no-items');
	    this.$newPeriodRow = this.$tableBody.children('.mpa-new-period');
	    this.baseName = this.$element.attr('data-base-name');
	    this.datepicker = null;

	    // A set of {Period ID: Period dates}
	    this.periods = {
	      length: 0,
	      add: function (id, value) {
	        let isNew = this[id] == undefined;
	        this[id] = value;
	        if (isNew) {
	          this.length++;
	        }
	      },
	      remove: function (id) {
	        if (this[id] == undefined) {
	          return;
	        }
	        delete this[id];
	        this.length--;
	      },
	      hasItems: function () {
	        return this.length > 0;
	      }
	    };
	    this.parseInitialState();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  parseInitialState() {
	    this.$tableBody.children(':not(.no-items, .mpa-new-period)').each((i, element) => {
	      let periodId = element.getAttribute('data-id');
	      let periodValue = jQuery(element).find('.column-actions > input').val();
	      this.periods.add(periodId, periodValue);
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  initDatepicker() {
	    this.datepicker = mpa_flatpickr(this.$tableBody.find('.mpa-new-period > .column-dates > input'), {
	      mode: 'range',
	      showMonths: 2
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    let self = this;

	    // Add button in the head
	    this.$table.find('thead .mpa-add-button').on('click', () => {
	      this.toggleEdit();
	    });

	    // Add button near the datepicker
	    this.$tableBody.find('.mpa-add-button').on('click', () => {
	      this.onAdd();
	    });

	    // Remove buttons
	    this.$tableBody.find('.mpa-remove-button').on('click', function () {
	      self.removePeriodByParent(this);
	    });
	  }

	  /**
	   * @since 1.0
	   */
	  toggleEdit() {
	    if (this.$newPeriodRow.hasClass('mpa-hide')) {
	      this.$newPeriodRow.removeClass('mpa-hide');
	      if (this.datepicker == null) {
	        this.initDatepicker();
	      } else {
	        this.datepicker.clear();
	      }
	    } else {
	      this.$newPeriodRow.addClass('mpa-hide');
	    }
	  }

	  /**
	   * @param {String} dates Dates interval.
	   * @param {String} datesView Dates interval view.
	   *
	   * @since 1.0
	   */
	  addPeriod(dates, datesView) {
	    let periodId = mpa_uniqid();
	    let periodHtml = this.renderPeriod(periodId, dates, datesView);

	    // Add period
	    jQuery(periodHtml).insertAfter(this.$newPeriodRow);
	    this.periods.add(periodId, dates);

	    // Add listener for new button
	    let removeButton = this.$tableBody.find('[data-id="' + periodId + '"] .mpa-remove-button');
	    let self = this;
	    removeButton.on('click', function () {
	      self.removePeriodByParent(this);
	    });

	    // Hide the edit row and "no-items" row
	    this.toggleEdit();
	    this.$noItemsRow.addClass('mpa-hide');
	  }

	  /**
	   * @param {String} periodId
	   * @param {String} dates Dates interval.
	   * @param {String} datesView Dates interval view.
	   * @return {String} New period HTML.
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  renderPeriod(periodId, dates, datesView) {
	    let output = '';
	    output += '<tr class="mpa-period" data-id="' + periodId + '">';
	    output += '<td class="column-dates mpa-badge-new">';
	    output += datesView;
	    output += '</td>';
	    output += '<td class="column-actions">';
	    output += '<input type="hidden" name="' + this.baseName + '[]" value="' + dates + '">';
	    output += mpa_tmpl_button(__('Remove', 'motopress-appointment'), {
	      'class': 'button button-secondary mpa-remove-button'
	    });
	    output += '</td>';
	    output += '</tr>';
	    return output;
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  onAdd() {
	    if (this.datepicker.selectedDates.length < 2) {
	      return;
	    }
	    let startDate = this.datepicker.selectedDates[0];
	    let endDate = this.datepicker.selectedDates[1];
	    let dates = mpa_format_date(startDate, 'internal') + ' - ' + mpa_format_date(endDate, 'internal');
	    let datesView = mpa_tmpl_date_period(startDate, endDate, 'short');
	    this.addPeriod(dates, datesView);
	  }

	  /**
	   * @param {Element} caller
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  removePeriodByParent(caller) {
	    let $periodRow = jQuery(caller).parents('tr.mpa-period');
	    if ($periodRow.length == 0) {
	      return;
	    }

	    // Remove the period from the list
	    let periodId = $periodRow.attr('data-id');
	    this.periods.remove(periodId);
	    if (!this.periods.hasItems()) {
	      this.$noItemsRow.removeClass('mpa-hide');
	    }

	    // Remove the row in the table
	    $periodRow.remove();
	  }
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
	 * @param {Number[]} ids
	 * @param {Boolean} forceReload
	 * @return {Promise} Service[]
	 */
	function mpa_get_services(ids, forceReload = false) {
	  return mpa_repositories().service().findAll(ids, forceReload);
	}

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

	/**
	 * @param {Object} $element
	 * @param {Cart} cart
	 * @returns {Cart}
	 */
	function mpa_parse_cart($element, cart = null) {
	  if (!cart) {
	    cart = new Cart();
	  }
	  $element.find('.mpa-cart-item:not(.mpa-cart-item-template)').each((i, element) => {
	    let itemId = element.getAttribute('data-id') || '';
	    let cartItem = cart.createItem(itemId);
	    let $cartItem = jQuery(element);

	    // Add attribute "data-id" if it does not exist
	    if (itemId !== cartItem.getItemId()) {
	      itemId = cartItem.getItemId();
	      $cartItem.attr('data-id', itemId);
	    }

	    // Parse IDs
	    let serviceId = $cartItem.find('input[name*="service_id"], select[name*="service_id"]').val();
	    let employeeId = $cartItem.find('input[name*="employee_id"], select[name*="employee_id"]').val();
	    let locationId = $cartItem.find('input[name*="location_id"], select[name*="location_id"]').val();
	    if (serviceId) {
	      cartItem.service = new Service(mpa_intval(serviceId));
	    }
	    if (employeeId) {
	      cartItem.employee = new Employee(mpa_intval(employeeId));
	      cartItem.employee.name = $cartItem.find('.mpa-employee-name').text().trim();
	    }
	    if (locationId) {
	      cartItem.location = new Location(mpa_intval(locationId));
	      cartItem.location.name = $cartItem.find('.mpa-location-name').text().trim();
	    }

	    // Parse date and time
	    let date = $cartItem.find('input[name*="date"]').val();
	    let time = $cartItem.find('input[name*="time"]').val();
	    if (date) {
	      cartItem.date = mpa_parse_date(date);
	    }
	    if (time) {
	      cartItem.time = new TimePeriod(time);
	    }

	    /**
	     * @todo: It's necessary to use setDate because the TimePeriod constructor takes a time period in the form of a string like "08:00 - 14:00".
	     * This string does not contain any date information.
	     * It would be better to pass the time period in the form of two ready Date variables, which will contain information about the date and time.
	     */
	    if (cartItem.date && cartItem.time) {
	      cartItem.time.setDate(cartItem.date);
	    }

	    // Parse capacity
	    let capacity = $cartItem.find('input[name*="capacity"], select[name*="capacity"]').val();
	    if (capacity) {
	      cartItem.capacity = mpa_intval(capacity);
	    }
	  }); // For each cart item element

	  return cart;
	}

	/**
	 * Helps to load services after mpa_parse_cart().
	 *
	 * @param {Cart} cart
	 * @returns {Promise} Service[]
	 */
	function mpa_load_cart_services(cart) {
	  return mpa_get_services(cart.getServiceIds()).then(services => {
	    cart.updateServices(services);
	    return services;
	  });
	}

	class StepAdminCart extends StepCart {
	  /**
	   * @access protected
	   */
	  setupProperties() {
	    super.setupProperties();

	    // There was several reservations when the step launched
	    this.wasMultipleReservation = false;
	    this.$buttonEdit = this.$buttons.find('.mpa-button-edit');
	  }

	  /**
	   * @access protected
	   */
	  addListeners() {
	    super.addListeners();
	    this.$buttonEdit.on('click', this.startEditing.bind(this));
	  }

	  /**
	   * @access protected
	   */
	  startEditing() {
	    this.$element.removeClass('mpa-loaded').addClass('editable');
	    this.$buttonNew.prop('disabled', true);

	    // Parse existing reservations
	    mpa_parse_cart(this.$element, this.cart);
	    this.cart.setActiveItem(null);
	    this.wasMultipleReservation = this.cart.getItemsCount() > 1;

	    // React when we know the real items count in the cart (maybe hide
	    // "Add More" button from scratch)
	    this.react();

	    // Bind listeners
	    this.cart.items.forEach(cartItem => {
	      let $cartItem = this.$items.find('.mpa-cart-item[data-id="' + cartItem.getItemId() + '"]');
	      if ($cartItem.length > 0) {
	        this.bindListeners($cartItem);
	      }
	    });

	    // Load real services data
	    mpa_load_cart_services(this.cart).then(() => {
	      // Enable step
	      this.$element.addClass('mpa-loaded');
	      this.$buttonNew.prop('disabled', false);
	    });
	  }

	  /**
	   * @access protected
	   */
	  react() {
	    super.react();
	    let allowMoreButton = this.cart.getItemsCount() < 1 || this.isMultibookingEnabled();
	    this.$buttonNew.toggleClass('mpa-hide', !allowMoreButton);
	  }
	  isMultibookingEnabled() {
	    return this.wasMultipleReservation || super.isMultibookingEnabled();
	  }
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

	class StepAdminPeriod extends StepPeriod {
	  /**
	   * @access protected
	   */
	  getDatepickerArgs() {
	    let datepickerArgs = super.getDatepickerArgs();

	    // Extend min date to latest month in the cart (enable editing of that months)
	    let minDate = this.cart.getMinDate();
	    let minMonth = new Date(minDate.getFullYear(), minDate.getMonth());
	    datepickerArgs.minDate = mpa_format_date(minMonth, 'internal');
	    return datepickerArgs;
	  }

	  /**
	   * @access protected
	   */
	  getTimeSlotsQueryArgs() {
	    let queryArgs = super.getTimeSlotsQueryArgs();

	    // Disable building only actual slots
	    queryArgs.since_today = false;
	    return queryArgs;
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

	class EditReservationsField extends BasicField {
	  constructor($element) {
	    super($element);
	    this.cart = new Cart();
	    this.steps = new BookingSteps(this.cart);
	    this.load();
	  }

	  /**
	   * @access protected
	   */
	  setupSteps() {
	    this.steps.addStep(new StepServiceForm(this.$element.find('.mpa-booking-step-service-form'), this.cart)).addStep(new StepAdminPeriod(this.$element.find('.mpa-booking-step-period'), this.cart)).addStep(new StepAdminCart(this.$element.find('.mpa-booking-step-cart'), this.cart));
	    this.steps.mount(this.$element);
	    let availabilityService = new AvailabilityService();
	    this.steps.getStep('service-form').setAvailabilityService(availabilityService);
	    this.steps.getStep('period').setAvailabilityService(availabilityService);
	    this.steps.goToStep('cart');
	  }

	  /**
	   * @access protected
	   */
	  load() {
	    this.setupSteps();
	  }
	}

	/**
	 * @since 1.0
	 */
	class ServiceVariationsField extends BasicField {
	  /**
	   * @since 1.0
	   *
	   * @param {Object} $element
	   */
	  constructor($element) {
	    super($element);
	    this.$table = this.$element.find('table');
	    this.$rows = this.$table.children('tbody');
	    this.$addButton = this.$element.find('.mpa-add-button');
	    this.inputName = this.$element.attr('data-base-name');
	    this.variations = {}; // {Row ID: Row element (jQuery object)}
	    this.count = 0;
	    this.employees = {}; // {ID: Name}
	    this.durations = {}; // {15: '15m', 30: '30m', ...}

	    this.findVariations();
	    this.getEmployees();
	    this.getDurations();
	    this.clearElement();
	    this.addListeners();
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   */
	  findVariations() {
	    this.$element.find('.mpa-variation').each((i, element) => {
	      let $row = jQuery(element);
	      let rowId = $row.attr('data-id');
	      this.variations[rowId] = $row;
	      this.count++;
	    });
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   */
	  getEmployees() {
	    this.$element.find('.mpa-employees-list option').each((i, option) => {
	      let employeeId = parseInt(option.value);
	      let employeeName = option.text;
	      this.employees[employeeId] = employeeName;
	    });
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   */
	  getDurations() {
	    this.$element.find('.mpa-durations-list option').each((i, option) => {
	      let minutesAmount = parseInt(option.value);
	      let durationText = option.text;
	      this.durations[minutesAmount] = durationText;
	    });
	  }

	  /**
	   * @since 1.3.1
	   * @access protected
	   */
	  clearElement() {
	    this.$element.children('.mpa-data-lists').remove();
	  }

	  /**
	   * @since 1.0
	   * @access protected
	   */
	  addListeners() {
	    // Add button
	    this.$addButton.on('click', this.addRow.bind(this));

	    // Remove buttons
	    this.$element.find('.mpa-remove-button').on('click', event => this.removeRowByElement(event.target));
	  }

	  /**
	   * @since 1.0
	   */
	  addRow() {
	    let rowId = mpa_uniqid();

	    // Append new row in the <tbody>
	    let rowHtml = this.renderRow(rowId);
	    this.$rows.append(rowHtml);

	    // Save new row
	    let $row = this.$rows.find('[data-id="' + rowId + '"]');
	    this.variations[rowId] = $row;
	    this.count++;

	    // Show table with at least one variation in it
	    this.$table.removeClass('mpa-hide');

	    // Add listener for new button
	    $row.find('.mpa-remove-button').on('click', event => this.removeRowByElement(event.target));
	  }

	  /**
	   * @since 1.0
	   * @access protected
	   *
	   * @param {String} rowId New row unique ID.
	   * @return {String} New row HTML.
	   */
	  renderRow(rowId) {
	    let inputPrefix = this.inputName + '[' + rowId + ']'; // '_mpa_variations[5ee2f5005b982]'

	    // Render the row
	    let output = '';
	    output += '<tr class="mpa-variation" data-id="' + rowId + '">';
	    // Employee select
	    output += '<td class="column-employee">';
	    output += mpa_tmpl_select(this.employees, 0, {
	      name: `${inputPrefix}[employee]`,
	      'class': 'mpa-employees'
	    });
	    output += '</td>';

	    // Price
	    output += '<td class="column-price">';
	    output += '<input class="mpa-price" type="number" name="' + inputPrefix + '[price]" value="" min="0" step="0.01">';
	    output += '</td>';

	    // Duration
	    output += '<td class="column-duration">';
	    output += mpa_tmpl_select(this.durations, 0, {
	      name: `${inputPrefix}[duration]`,
	      'class': 'mpa-durations'
	    });
	    output += '</td>';

	    // Min capacity
	    output += '<td class="column-min-capacity">';
	    output += '<input class="small-text" type="number" name="' + inputPrefix + '[min_capacity]" value="" min="1" step="1">';
	    output += '</td>';

	    // Max capacity
	    output += '<td class="column-max-capacity">';
	    output += '<input class="small-text" type="number" name="' + inputPrefix + '[max_capacity]" value="" min="1" step="1">';
	    output += '</td>';

	    // Column actions
	    output += '<td class="column-actions">' + mpa_tmpl_dashicon('trash', 'mpa-remove-button') + '</td>';
	    output += '</tr>';
	    return output;
	  }

	  /**
	   * @since 1.0
	   * @access protected
	   *
	   * @param {Element} element
	   */
	  removeRowByElement(element) {
	    let rowId = jQuery(element).parents('.mpa-variation').attr('data-id');
	    if (rowId) {
	      this.removeRow(rowId);
	    }
	  }

	  /**
	   * @since 1.0
	   *
	   * @param {String} rowId Row unique ID.
	   */
	  removeRow(rowId) {
	    if (!this.variations.hasOwnProperty(rowId)) {
	      return;
	    }

	    // Remove the row
	    this.variations[rowId].remove();
	    delete this.variations[rowId];
	    this.count--;

	    // Hide table if there are no variations left
	    if (this.count === 0) {
	      this.$table.addClass('mpa-hide');
	    }
	  }
	}

	/**
	 * @since 1.0
	 */
	class TimetableField extends BasicField {
	  /**
	   * @param {Object} $element
	   *
	   * @since 1.0
	   */
	  constructor($element) {
	    super($element);
	    this.$daysContainer = this.$element.find('.mpa-days-container');

	    // Form table elements
	    this.$formTable = this.$element.find('.mpa-edit-table');
	    this.$dayInput = this.$formTable.find('.mpa-day-of-week');
	    this.timeSelects = this.$formTable.find('.mpa-period');
	    this.$startTimeHoursInput = this.$formTable.find('.mpa-period__start-hours');
	    this.$startTimeMinutesInput = this.$formTable.find('.mpa-period__start-minutes');
	    this.$endTimeHoursInput = this.$formTable.find('.mpa-period__end-hours');
	    this.$endTimeMinutesInput = this.$formTable.find('.mpa-period__end-minutes');
	    this.$timeAllDayInput = this.$formTable.find('.mpa-period__all-day');
	    this.$activityInput = this.$formTable.find('.mpa-activity');
	    this.$locationInput = this.$formTable.find('.mpa-location');
	    this.$errorWrapper = this.$formTable.find('.mpa-end-time + .mpa-error');

	    // Button elements
	    this.$addButton = this.$element.find('.mpa-add-button');
	    this.$cancelButton = this.$element.find('.mpa-cancel-button');

	    // Other fields
	    this.addingPeriod = false;
	    this.baseName = this.$element.attr('data-base-name');
	    this.findPeriods();
	    this.fillActivities();
	    this.fillLocations();
	    this.addListeners();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  findPeriods() {
	    this.periods = {
	      monday: {},
	      tuesday: {},
	      wednesday: {},
	      thursday: {},
	      friday: {},
	      saturday: {},
	      sunday: {}
	    };

	    // {Period ID: Day of the week} - {'5ee2c925cf811': 'monday'}
	    this.periodsMap = {};
	    let self = this;
	    this.$element.find('.mpa-day-period').each(function (i, element) {
	      let $period = jQuery(element);
	      let id = $period.attr('data-id');
	      let day = $period.children('.mpa-period-day').val();
	      let startTime = parseInt($period.children('.mpa-period-start').val());
	      self.periods[day][id] = {
	        startTime: startTime,
	        $element: $period
	      };
	      self.periodsMap[id] = day;
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  fillActivities() {
	    this.activities = {};
	    this.$activityInput.children().each((i, option) => {
	      let name = option.value;
	      let label = option.text;
	      this.activities[name] = label;
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  fillLocations() {
	    this.locations = {};
	    this.$locationInput.children().each((i, option) => {
	      let id = option.value;
	      let label = option.text;
	      if (id !== '') {
	        this.locations[id] = label;
	      }
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addListeners() {
	    this.$timeAllDayInput.on('change', e => {
	      if (e.target.checked) {
	        this.timeSelects.hide();
	      } else {
	        this.timeSelects.show();
	      }
	    });
	    this.$addButton.on('click', () => {
	      if (!this.addingPeriod) {
	        this.addingPeriod = true;
	        this.resetInputs();
	        this.$formTable.removeClass('mpa-hide');
	      } else {
	        if (!this.isValidEditingPeriod()) {
	          this.$errorWrapper.removeClass('mpa-hide');
	        } else {
	          this.$errorWrapper.addClass('mpa-hide');
	          this.addPeriod({
	            day: this.getDay(),
	            startTime: this.getStartTime(),
	            endTime: this.getEndTime(),
	            activity: this.getActivity(),
	            location: this.getLocation()
	          });
	          this.$formTable.addClass('mpa-hide');
	          this.addingPeriod = false;
	        }
	      }
	    });
	    this.$cancelButton.on('click', () => {
	      this.$formTable.addClass('mpa-hide');
	      this.addingPeriod = false;
	    });
	    let self = this;
	    this.$element.find('.mpa-remove-button').on('click', function () {
	      self.removePeriodByElement(this);
	    });
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getDay() {
	    return this.$dayInput.val();
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.0
	   */
	  getStartTime() {
	    let startTimeInMinutes = 0;
	    if (!this.$timeAllDayInput.prop('checked')) {
	      startTimeInMinutes = parseInt(this.$startTimeHoursInput.val()) * 60 + parseInt(this.$startTimeMinutesInput.val());
	    }
	    return startTimeInMinutes;
	  }

	  /**
	   * @return {Number}
	   *
	   * @since 1.0
	   */
	  getEndTime() {
	    let endTimeInMinutes = 0;
	    if (!this.$timeAllDayInput.prop('checked')) {
	      endTimeInMinutes = parseInt(this.$endTimeHoursInput.val()) * 60 + parseInt(this.$endTimeMinutesInput.val());
	    }
	    return endTimeInMinutes;
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getActivity() {
	    return this.$activityInput.val();
	  }

	  /**
	   * @return {String}
	   *
	   * @since 1.0
	   */
	  getLocation() {
	    let location = this.$locationInput.val();
	    if (location !== '') {
	      location = parseInt(location);
	    }
	    return location;
	  }

	  /**
	   * @return {Boolean}
	   *
	   * @since 1.0
	   */
	  isValidEditingPeriod() {
	    let startTime = this.getStartTime();
	    let endTime = this.getEndTime();

	    // if endTime = 0 than it could be a full day rental
	    return endTime > startTime || 0 === endTime;
	  }

	  /**
	   * @param {Object} period
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  addPeriod(period) {
	    let id = mpa_uniqid();

	    // Add new period to HTML
	    let newHtml = this.renderPeriod(period, id);
	    let isAdded = false;

	    // Try to add it in ascending order
	    for (let pid in this.periods[period.day]) {
	      if (period.startTime < this.periods[period.day][pid].startTime) {
	        jQuery(newHtml).insertBefore(this.periods[period.day][pid].$element);
	        isAdded = true;
	        break;
	      }
	    }

	    // Or just add it to the end of the container
	    if (!isAdded) {
	      this.$daysContainer.children('[data-for="' + period.day + '"]').children('.mpa-day-periods').append(newHtml);
	    }

	    // Save period
	    let $element = this.$daysContainer.find('[data-id="' + id + '"]');
	    this.periods[period.day][id] = {
	      startTime: period.startTime,
	      $element: $element
	    };
	    this.periodsMap[id] = period.day;

	    // Add listeners for new buttons
	    let self = this;
	    $element.find('.mpa-remove-button').on('click', function () {
	      self.removePeriodByElement(this);
	    });
	  }

	  /**
	   * @param {Object} period
	   * @param {String} id New period ID.
	   * @return {String} New period HTML.
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  renderPeriod(period, id) {
	    let namePrefix = this.baseName + '[' + id + ']'; // '_mpa_timetable[5ee2f5005b982]'
	    let output = '';
	    output += '<div class="mpa-day-period" data-id="' + id + '">';
	    output += '<input type="hidden" name="' + namePrefix + '[day]" value="' + period.day + '">';
	    output += '<input type="hidden" name="' + namePrefix + '[start]" value="' + period.startTime + '">';
	    output += '<input type="hidden" name="' + namePrefix + '[end]" value="' + period.endTime + '">';
	    output += '<input type="hidden" name="' + namePrefix + '[activity]" value="' + period.activity + '">';
	    output += '<input type="hidden" name="' + namePrefix + '[location]" value="' + period.location + '">';
	    output += '<span class="mpa-period-time">';
	    if (0 === period.startTime && 0 === period.startTime && period.startTime === period.endTime) {
	      output += __('All day', 'motopress-appointment');
	    } else {
	      output += mpa_format_minutes(period.startTime);
	      output += '&nbsp;—&nbsp;';
	      output += mpa_format_minutes(period.endTime);
	    }
	    output += '</span>';
	    output += mpa_tmpl_dashicon('trash', 'mpa-remove-button');
	    output += '<br>';
	    output += '<span class="mpa-period-activity">';
	    output += this.activities[period.activity];
	    output += '</span>';
	    if (period.location !== '' && period.activity == 'work') {
	      output += '<br>';
	      output += '<span class="mpa-period-location">';
	      output += _x('at %s', 'Working at %s', 'motopress-appointment').replace('%s', mpa_tmpl_edit_post_link(period.location, this.locations[period.location]));
	      output += '</span>';
	    }
	    output += '</div>';
	    return output;
	  }

	  /**
	   * @param {Element} element
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  removePeriodByElement(element) {
	    let periodId = jQuery(element).parents('.mpa-day-period').attr('data-id');
	    if (periodId) {
	      this.removePeriod(periodId);
	    }
	  }

	  /**
	   * @param {String} id
	   *
	   * @since 1.0
	   */
	  removePeriod(id) {
	    if (!this.periodsMap.hasOwnProperty(id)) {
	      return;
	    }
	    let day = this.periodsMap[id];
	    this.periods[day][id].$element.remove();
	    delete this.periods[day][id];
	    delete this.periodsMap[id];
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  resetInputs() {
	    this.$errorWrapper.addClass('mpa-hide');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.0
	   */
	  onCancel() {
	    this.$formTable.addClass('mpa-hide');
	    this.addingPeriod = false;
	  }
	}

	/**
	 * @since 1.7.0
	 */
	class EmployeeUserField extends BasicField {
	  /**
	  * @param {Object} $element
	  *
	  * @since 1.7.0
	  */
	  constructor($element) {
	    super($element);
	    this.$input = this.$element.find('input');
	    this.$datalist = this.$element.find('datalist');
	    this.$input.on('input', this.reloadUserEmails.bind(this));
	    this.reloadUserEmails();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.7.0
	   */
	  reloadUserEmails() {
	    let self = this;
	    jQuery.ajax({
	      url: mpaUserMetaboxSettings.root + 'wp/v2/users/',
	      method: 'GET',
	      beforeSend: function (xhr) {
	        xhr.setRequestHeader('X-WP-Nonce', mpaUserMetaboxSettings.nonce);
	      },
	      data: {
	        'context': 'edit',
	        'search': self.$input.val(),
	        'per_page': 100,
	        'orderby': 'email'
	      }
	    }).done(function (response) {
	      self.$datalist.empty();
	      response.forEach(user => {
	        self.$datalist.append('<option value="' + user.email + '"></option>');
	      });
	    });
	  }
	}

	/**
	 * @since 1.0
	 */
	class EditPostPage {
	  /**
	   * @since 1.0
	   */
	  constructor() {
	    this.setupFields(jQuery('.mpa-ctrl:not([data-inited])'));
	  }

	  /**
	   * @param {Object[]} $fields
	   *
	   * @access protected
	   *
	   * @since 1.0
	   */
	  setupFields($fields) {
	    $fields.each(function (i, element) {
	      let $element = jQuery(element);
	      let type = $element.attr('data-type');
	      switch (type) {
	        // Base fields
	        case 'color-picker':
	          new ColorPickerField($element);
	          break;
	        case 'date':
	          new DateField($element);
	          break;
	        case 'image':
	          new ImageField($element);
	          break;
	        case 'phone':
	          new PhoneField($element);
	          break;

	        // Custom fields
	        case 'attributes':
	          new AttributesField($element);
	          break;
	        case 'custom-workdays':
	          new CustomWorkdaysField($element);
	          break;
	        case 'days-off':
	          new DaysOffField($element);
	          break;
	        case 'edit-reservations':
	          new EditReservationsField($element);
	          break;
	        case 'service-variations':
	          new ServiceVariationsField($element);
	          break;
	        case 'timetable':
	          new TimetableField($element);
	          break;
	        case 'employee-user':
	          new EmployeeUserField($element);
	          break;
	      }
	    });
	  }
	}

	/**
	 * @since 1.19.0
	 */
	class EditShortcodeAppointmentForm {
	  constructor() {
	    if (jQuery('#mpa_appointment_form_metabox').length === 0) {
	      return;
	    }
	    this.availability = new AvailabilityService();

	    // Use loaded values in render
	    this.availability.load().finally(() => {
	      this.defaultValuesDependency();
	    });
	    this.$showItemsCategory = jQuery('#_mpa_show_items-category');
	    this.$showItemsCategoryLabel = this.$showItemsCategory.parent();
	    this.$showItemsService = jQuery('#_mpa_show_items-service');
	    this.$showItemsServiceLabel = this.$showItemsService.parent();
	    this.$defaultValuesCategory = jQuery('#_mpa_default_category');
	    this.$defaultValuesService = jQuery('#_mpa_default_service');
	    this.$defaultValuesLocation = jQuery('#_mpa_default_location');
	    this.$defaultValuesEmployee = jQuery('#_mpa_default_employee');
	    this.showItemsCategoryProp = this.$showItemsCategory.prop('checked');
	    this.showItemsCategoryLabelTooltipText = sprintf(
	    // Translators: %s: Checkbox label.
	    __("To enable this option, you need to check the '%s' box.", 'motopress-appointment'), __('Service', 'motopress-appointment'));
	    this.showItemsServiceLabelTooltipText = __("To enable booking for the specific service only, select the service below first, then uncheck the 'Service' box here.", 'motopress-appointment');
	    this.dependencyOfShowServiceToDefaultValueOfService();
	    this.toggleCategoryBasedOnService();
	    this.addListeners();
	  }
	  addListeners() {
	    this.$showItemsCategory.on('change', () => this.updateShowItemsCategoryProp());
	    this.$showItemsService.on('change', () => this.toggleCategoryBasedOnService());
	    this.$defaultValuesService.on('change', () => this.dependencyOfShowServiceToDefaultValueOfService());
	    this.$showItemsServiceLabel.on('click', () => this.makeFocusToServiceSelect());
	    this.$defaultValuesCategory.on('change', () => this.defaultValuesDependency());
	    this.$defaultValuesService.on('change', () => this.defaultValuesDependency());
	    this.$defaultValuesLocation.on('change', () => this.defaultValuesDependency());
	    this.$defaultValuesEmployee.on('change', () => this.defaultValuesDependency());
	  }
	  updateShowItemsCategoryProp() {
	    this.showItemsCategoryProp = this.$showItemsCategory.prop('checked');
	  }
	  toggleCategoryBasedOnService() {
	    if (this.$showItemsService.prop('checked') === false) {
	      this.$showItemsCategory.prop('disabled', true);
	      this.$showItemsCategory.prop('checked', false);
	      this.$showItemsCategoryLabel.toggleClass('mpa_tooltip', true);
	      this.$showItemsCategoryLabel.attr('data-tooltip', this.showItemsCategoryLabelTooltipText);
	    } else {
	      this.$showItemsCategory.prop('disabled', false);
	      this.$showItemsCategory.prop('checked', this.showItemsCategoryProp);
	      this.$showItemsCategoryLabel.toggleClass('mpa_tooltip', false);
	    }
	  }
	  dependencyOfShowServiceToDefaultValueOfService() {
	    if (!this.$defaultValuesService.val() || this.$defaultValuesService.val() === '0') {
	      this.$showItemsService.prop('disabled', true);
	      this.$showItemsService.prop('checked', true);
	      this.$showItemsServiceLabel.toggleClass('mpa_tooltip', true);
	      this.$showItemsServiceLabel.attr('data-tooltip', this.showItemsServiceLabelTooltipText);
	    } else {
	      this.$showItemsService.prop('disabled', false);
	      this.$showItemsServiceLabel.toggleClass('mpa_tooltip', false);
	      this.$showItemsServiceLabel.removeAttr('data-tooltip');
	    }
	  }
	  makeFocusToServiceSelect() {
	    if (this.$showItemsService.prop('disabled') === true) {
	      this.$defaultValuesService.focus();
	    }
	  }
	  defaultValuesDependency() {
	    const unselectedServiceTextVal = jQuery('#_mpa_label_unselected').val();
	    const unselectedServiceText = unselectedServiceTextVal ? unselectedServiceTextVal : __('— Select —', 'motopress-appointment');
	    const unselectedOptionTextVal = jQuery('#_mpa_label_option').val();
	    const unselectedOptionText = unselectedOptionTextVal ? unselectedOptionTextVal : __('— Any —', 'motopress-appointment');
	    const categorySlug = this.$defaultValuesCategory.val();
	    const serviceId = mpa_intval(this.$defaultValuesService.val());
	    const employeeId = mpa_intval(this.$defaultValuesEmployee.val());
	    const locationId = mpa_intval(this.$defaultValuesLocation.val());
	    const selectedService = this.availability.isAvailableService(serviceId) ? serviceId : 0;
	    const selectedCategory = this.availability.isAvailableServiceCategory(categorySlug) ? categorySlug : '';
	    const selectedLocation = this.availability.isAvailableLocation(locationId) ? locationId : 0;
	    const selectedEmployee = this.availability.isAvailableEmployee(employeeId) ? employeeId : 0;
	    const availableServiceCategories = this.availability.getAvailableServiceCategories();
	    const availableServices = this.availability.getAvailableServices(selectedCategory, selectedLocation, selectedEmployee);
	    const availableLocations = this.availability.getAvailableLocations(selectedService, selectedEmployee);
	    const availableEmployees = this.availability.getAvailableEmployees(selectedService, selectedLocation);
	    update_select_options(this.$defaultValuesCategory, {
	      '': unselectedOptionText
	    }, availableServiceCategories, availableServiceCategories.hasOwnProperty(selectedCategory) ? selectedCategory : '');
	    update_select_options(this.$defaultValuesService, {
	      0: unselectedServiceText
	    }, availableServices, availableServices.hasOwnProperty(selectedService) ? selectedService : 0);
	    update_select_options(this.$defaultValuesLocation, {
	      0: unselectedOptionText
	    }, availableLocations, availableLocations.hasOwnProperty(selectedLocation) ? selectedLocation : 0);
	    update_select_options(this.$defaultValuesEmployee, {
	      0: unselectedOptionText
	    }, availableEmployees, availableEmployees.hasOwnProperty(selectedEmployee) ? selectedEmployee : 0);
	  }
	}

	/**
	 * @since 1.2
	 */
	class Dropdown {
	  /**
	   * @param {Object} $element jQuery element.
	   *
	   * @since 1.2
	   */
	  constructor($element) {
	    this.$element = $element;
	    this.$toggle = $element.children('.dropdown-toggle');
	    this.$menu = $element.children('.dropdown-menu');
	    this.$menuItems = this.$menu.children('.dropdown-item');
	    this.isDoingClick = false;
	    this.addListeners();
	    this.setInited();
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  addListeners() {
	    this.$toggle.on('click', this.showMenu.bind(this));
	    this.$toggle.on('blur', this.onBlur.bind(this));

	    // Simple "click" event will not work, since it fires after the
	    // "blur" event, when the .dropdown-menu is already hidden (we
	    // end up clicking nothing). So prevent the "blur" event when
	    // doing clicks on dropdown items. See more details of the
	    // https://stackoverflow.com/a/56989191/3918377
	    this.$menuItems.on('mousedown', this.beforeClick.bind(this));
	    this.$menuItems.on('mouseup', this.afterClick.bind(this));
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  setInited() {
	    this.$element.addClass('inited');
	  }

	  /**
	   * @since 1.2
	   */
	  toggleMenu() {
	    this.$menu.toggleClass('show');
	  }

	  /**
	   * @since 1.2
	   */
	  showMenu() {
	    this.$menu.addClass('show');
	  }

	  /**
	   * @since 1.2
	   */
	  hideMenu() {
	    this.$menu.removeClass('show');
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  beforeClick() {
	    this.isDoingClick = true;
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  afterClick() {
	    this.isDoingClick = false;
	    this.hideMenu();
	  }

	  /**
	   * @param {Event} event
	   *
	   * @access protected
	   *
	   * @since 1.2
	   */
	  onBlur(event) {
	    if (this.isDoingClick) {
	      event.preventDefault();
	    } else {
	      this.hideMenu();
	    }
	  }
	}

	/**
	 * @param {String} propertyName
	 * @param {*} value
	 *
	 * @since 1.2
	 */
	function mpa_make_global(propertyName, value) {
	  if (window.MotoPress == undefined) {
	    window.MotoPress = {};
	  }
	  if (window.MotoPress.Appointment == undefined) {
	    window.MotoPress.Appointment = {};
	  }
	  window.MotoPress.Appointment[propertyName] = value;
	}

	/**
	 * @since 1.2
	 */
	class Bootstrap {
	  /**
	   * @since 1.2
	   */
	  constructor() {
	    this.setupComponents();
	    this.exportInstance();
	  }

	  /**
	   * @since 1.2
	   */
	  setupComponents() {
	    this.setupDropdowns();
	  }

	  /**
	   * @since 1.2
	   */
	  setupDropdowns() {
	    jQuery('.mpa-dropdown:not(.inited)').each(function (i, element) {
	      new Dropdown(jQuery(element));
	    });
	  }

	  /**
	   * @access protected
	   *
	   * @since 1.2
	   */
	  exportInstance() {
	    // See includes/admin-pages/traits/ShortcodeTitleActions.php
	    mpa_make_global('Bootstrap', this);
	  }
	}

	// Setup edit post page
	new Bootstrap();
	new EditPostPage();
	new EditShortcodeAppointmentForm();

})(wp.date, intlTelInput, mpaData);
