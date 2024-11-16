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
	typeof wp !== 'undefined' && wp.i18n && wp.i18n.sprintf ? wp.i18n.sprintf : localSprintf;

	({
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
	});

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

	class StripeSettings {
	  initSettings() {
	    this.initPaymentMethodsDependency();
	    this.initPaymentMethodConfigurationDependency();
	  }

	  /**
	   * PaymentRequestButton methods like apple_pay, google_pay, link must be used only with activated of card method.
	   *
	   * @access protected
	   */
	  initPaymentMethodsDependency() {
	    const $cardMethod = jQuery('#mpa_stripe_payment_gateway_payment_methods-card');
	    const $paymentRequestButtonMethods = jQuery('#mpa_stripe_payment_gateway_payment_methods-apple_pay, #mpa_stripe_payment_gateway_payment_methods-google_pay, #mpa_stripe_payment_gateway_payment_methods-link');
	    $cardMethod.click(event => {
	      if (!event.target.checked) {
	        $paymentRequestButtonMethods.prop('checked', false);
	      }
	    });
	    $paymentRequestButtonMethods.click(event => {
	      if (event.target.checked) {
	        $cardMethod.prop('checked', true);
	      }
	    });
	  }

	  /**
	   * @access protected
	   */
	  initPaymentMethodConfigurationDependency() {
	    const $paymentMethod = jQuery('#mpa_stripe_payment_gateway_payment_methods-payment');
	    const $paymentMethodLabel = $paymentMethod.closest('label');
	    const $paymentMethodConfigurationInput = jQuery('#mpa_stripe_payment_gateway_payment_method_configuration');
	    const $paymentMethodConfigurationBlock = $paymentMethodConfigurationInput.closest('.mpa-ctrl');
	    const $paymentMethodConfigurationWrapper = $paymentMethodConfigurationInput.closest('.mpa-ctrl-wrapper');
	    const $paymentMethodConfigurationLabel = $paymentMethodConfigurationWrapper.find('label');
	    $paymentMethodConfigurationBlock.prepend('<br>');
	    $paymentMethodConfigurationBlock.prepend($paymentMethodConfigurationLabel);
	    $paymentMethodConfigurationBlock.prepend('<br>');
	    $paymentMethodConfigurationBlock.detach().insertAfter($paymentMethodLabel);
	    $paymentMethodConfigurationWrapper.addClass('mpa-hide');
	  }
	}

	/**
	 * @since 1.1.0
	 */
	class SettingsPage {
	  /**
	   * @since 1.1.0
	   */
	  constructor() {
	    this.setupFields(jQuery('.mpa-ctrl:not([data-inited])'));
	    this.stripeSettings = new StripeSettings();
	    this.stripeSettings.initSettings();
	  }

	  /**
	   * @param {Object[]} $fields
	   *
	   * @access protected
	   *
	   * @since 1.1.0
	   */
	  setupFields($fields) {
	    $fields.each(function (i, element) {
	      let $element = jQuery(element);
	      let type = $element.attr('data-type');
	      switch (type) {
	        case 'color-picker':
	          new ColorPickerField($element);
	          break;
	        case 'image':
	          new ImageField($element);
	          break;
	      }
	    });
	  }
	}

	// Setup settings page
	new SettingsPage();

})(wp.date, intlTelInput, mpaData);
