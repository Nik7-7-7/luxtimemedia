(function () {
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

	let $featuredImage = jQuery('.mpa-meta-featured-image > td').first();
	if ($featuredImage.length > 0) {
	  $featuredImage.addClass('mpa-media-ctrl');
	  new ImageField($featuredImage);
	}

})();
