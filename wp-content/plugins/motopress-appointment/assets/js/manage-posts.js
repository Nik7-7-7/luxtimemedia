(function () {
	'use strict';

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

	// Setup manage posts page
	new Bootstrap();

})();
