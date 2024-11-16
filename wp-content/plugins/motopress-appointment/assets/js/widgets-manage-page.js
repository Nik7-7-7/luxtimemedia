(function (date, intlTelInput, mpaData) {
	'use strict';

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
	 * @since 1.19.0
	 */
	class AppointmentFormWidgetSetting {
	  /**
	   * @param widget DOMElement
	   * @param availability AvailabilityService
	   */
	  constructor(widget, availability) {
	    this.availability = availability;

	    // Use loaded values in render
	    this.availability.load().finally(() => {
	      this.defaultValuesDependency();
	    });
	    this.widgetName = 'appointment_form';
	    this.$widget = jQuery(widget);
	    this.widgetId = this.$widget.find('input[name="widget-id"]').val();
	    this.widgetInstanceNumber = this.widgetId.split('-').pop();
	    this.widgetFieldSelectorTemplate = '#widget-' + this.widgetName + '-' + this.widgetInstanceNumber + '-';
	    this.$showItemsCategory = this.findWidgetFieldElement('show_items-category');
	    this.$showItemsCategoryLabel = this.$showItemsCategory.parent();
	    this.$showItemsService = this.findWidgetFieldElement('show_items-service');
	    this.$showItemsServiceLabel = this.$showItemsService.parent();
	    this.$defaultCategory = this.findWidgetFieldElement('default_category');
	    this.$defaultService = this.findWidgetFieldElement('default_service');
	    this.$defaultLocation = this.findWidgetFieldElement('default_location');
	    this.$defaultEmployee = this.findWidgetFieldElement('default_employee');
	    this.showItemsCategoryProp = this.$showItemsCategory.prop('checked');
	    this.showItemsCategoryLabelTooltipText = sprintf(
	    // Translators: %s: Checkbox label.
	    __("To enable this option, you need to check the '%s' box.", 'motopress-appointment'), __('Service', 'motopress-appointment'));
	    this.showItemsServiceLabelTooltipText = __("To enable booking for the specific service only, select the service below first, then uncheck the 'Service' box here.", 'motopress-appointment');
	    this.dependencyOfShowServiceToDefaultValueOfService();
	    this.toggleCategoryBasedOnService();
	    this.initColorPicker();
	    this.addListeners();
	  }

	  /**
	   * @since 1.19.1
	   *
	   * @param field
	   * @param isLocked
	   */
	  toggleLockCheckbox(field, isLocked) {
	    const labelForField = jQuery(`label[for="${field.attr('id')}"]`);
	    if (isLocked) {
	      field.on('click.preventClick', function (e) {
	        e.preventDefault();
	      });
	      field.data('locked', true);
	      field.css({
	        'opacity': 0.6,
	        'cursor': 'not-allowed'
	      });
	      labelForField.css('opacity', 0.6);
	    } else {
	      field.off('click.preventClick');
	      field.removeData('locked');
	      field.css({
	        'opacity': 1,
	        'cursor': 'default'
	      });
	      labelForField.css('opacity', 1);
	    }
	  }

	  /**
	   * @since 1.19.1
	   *
	   * @param field
	   * @return {boolean}
	   */
	  isLockedCheckbox(field) {
	    return !!field.data('locked');
	  }
	  findWidgetFieldElement(field) {
	    return this.$widget.find(this.widgetFieldSelectorTemplate + field);
	  }
	  addListeners() {
	    this.$showItemsCategory.on('change', () => this.updateShowItemsCategoryProp());
	    this.$showItemsService.on('change', () => this.toggleCategoryBasedOnService());
	    this.$defaultService.on('change', () => this.dependencyOfShowServiceToDefaultValueOfService());
	    this.$showItemsServiceLabel.on('click', () => this.makeFocusToServiceSelect());
	    this.$defaultCategory.on('change', () => this.defaultValuesDependency());
	    this.$defaultService.on('change', () => this.defaultValuesDependency());
	    this.$defaultLocation.on('change', () => this.defaultValuesDependency());
	    this.$defaultEmployee.on('change', () => this.defaultValuesDependency());
	  }
	  updateShowItemsCategoryProp() {
	    this.showItemsCategoryProp = this.$showItemsCategory.prop('checked');
	  }
	  toggleCategoryBasedOnService() {
	    if (this.$showItemsService.prop('checked') === false) {
	      this.toggleLockCheckbox(this.$showItemsCategory, true);
	      this.$showItemsCategory.prop('checked', false);
	      this.$showItemsCategoryLabel.toggleClass('mpa_tooltip', true);
	      this.$showItemsCategoryLabel.attr('data-tooltip', this.showItemsCategoryLabelTooltipText);
	    } else {
	      this.toggleLockCheckbox(this.$showItemsCategory, false);
	      this.$showItemsCategory.prop('checked', this.showItemsCategoryProp);
	      this.$showItemsCategoryLabel.toggleClass('mpa_tooltip', false);
	    }
	  }
	  dependencyOfShowServiceToDefaultValueOfService() {
	    if (!this.$defaultService.val() || this.$defaultService.val() === '0') {
	      this.toggleLockCheckbox(this.$showItemsService, true);
	      this.$showItemsService.prop('checked', true);
	      this.$showItemsServiceLabel.toggleClass('mpa_tooltip', true);
	      this.$showItemsServiceLabel.attr('data-tooltip', this.showItemsServiceLabelTooltipText);
	    } else {
	      this.toggleLockCheckbox(this.$showItemsService, false);
	      this.$showItemsServiceLabel.toggleClass('mpa_tooltip', false);
	      this.$showItemsServiceLabel.removeAttr('data-tooltip');
	    }
	  }
	  makeFocusToServiceSelect() {
	    if (this.isLockedCheckbox(this.$showItemsService) === true) {
	      this.$defaultService.focus();
	    }
	  }
	  defaultValuesDependency() {
	    const unselectedServiceTextVal = jQuery('#_mpa_label_unselected').val();
	    const unselectedServiceText = unselectedServiceTextVal ? unselectedServiceTextVal : __('— Select —', 'motopress-appointment');
	    const unselectedOptionTextVal = jQuery('#_mpa_label_option').val();
	    const unselectedOptionText = unselectedOptionTextVal ? unselectedOptionTextVal : __('— Any —', 'motopress-appointment');
	    const categorySlug = this.$defaultCategory.val();
	    const serviceId = mpa_intval(this.$defaultService.val());
	    const employeeId = mpa_intval(this.$defaultEmployee.val());
	    const locationId = mpa_intval(this.$defaultLocation.val());
	    const selectedService = this.availability.isAvailableService(serviceId) ? serviceId : 0;
	    const selectedCategory = this.availability.isAvailableServiceCategory(categorySlug) ? categorySlug : '';
	    const selectedLocation = this.availability.isAvailableLocation(locationId) ? locationId : 0;
	    const selectedEmployee = this.availability.isAvailableEmployee(employeeId) ? employeeId : 0;
	    const availableServiceCategories = this.availability.getAvailableServiceCategories();
	    const availableServices = this.availability.getAvailableServices(selectedCategory, selectedLocation, selectedEmployee);
	    const availableLocations = this.availability.getAvailableLocations(selectedService, selectedEmployee);
	    const availableEmployees = this.availability.getAvailableEmployees(selectedService, selectedLocation);
	    update_select_options(this.$defaultCategory, {
	      '': unselectedOptionText
	    }, availableServiceCategories, availableServiceCategories.hasOwnProperty(selectedCategory) ? selectedCategory : '');
	    update_select_options(this.$defaultService, {
	      0: unselectedServiceText
	    }, availableServices, availableServices.hasOwnProperty(selectedService) ? selectedService : 0);
	    update_select_options(this.$defaultLocation, {
	      0: unselectedOptionText
	    }, availableLocations, availableLocations.hasOwnProperty(selectedLocation) ? selectedLocation : 0);
	    update_select_options(this.$defaultEmployee, {
	      0: unselectedOptionText
	    }, availableEmployees, availableEmployees.hasOwnProperty(selectedEmployee) ? selectedEmployee : 0);
	  }
	  initColorPicker() {
	    const $fields = this.$widget.find('.mpa-color-picker-ctrl');
	    $fields.each(function (i, element) {
	      let $element = jQuery(element);
	      new ColorPickerField($element);
	    });
	  }
	}

	jQuery(document).ready(function () {
	  const availability = new AvailabilityService();
	  jQuery('.widget[id*="appointment_form"]').not('[id*="__i__"]').each(function () {
	    const widget = this;
	    new AppointmentFormWidgetSetting(widget, availability);
	  });
	  jQuery(document).on('widget-added widget-updated', function (event, widget) {
	    const widgetIdBase = widget.find('input[name="id_base"]').val();
	    if (widgetIdBase !== 'appointment_form') {
	      return;
	    }
	    new AppointmentFormWidgetSetting(widget, availability);
	  });
	});

})(wp.date, intlTelInput, mpaData);
