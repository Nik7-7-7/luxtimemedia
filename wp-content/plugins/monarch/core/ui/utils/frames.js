// External Dependencies
// Do not use `import { a, b } from 'lodash'` syntax here to avoid
// lot of unused code being included in the boot.js bundle.
import defaults from 'lodash/defaults';
import isEmpty from 'lodash/isEmpty';
import get from 'lodash/get';
import includes from 'lodash/includes';
import forEach from 'lodash/forEach';
import uniq from 'lodash/uniq';
import { isScriptExcluded, isScriptTopOnly, isElementExcluded } from './utils';
import $ from 'jquery';

// Internal Dependencies

const IS_YARN_START = 'development' === process.env.NODE_ENV && ! process.env.DEV_SERVER;

class ETCoreFrames {

  /**
   * Instances of this class.
   *
   * @since 3.18
   *
   * @type {Object.<string, ETCoreFrames>}
   */
  static _instances = {};

  /**
   * jQuery object for the base window.
   *
   * @since 3.18
   *
   * @type {function(string, string=): jQuery}
   */
  $base;

  /**
   * jQuery object for the target window.
   *
   * @since 3.18
   *
   * @type {function(string, string=): jQuery}
   */
  $target;

  /**
   * Frames that are currently in use.
   *
   * @since 3.18
   *
   * @type {Object.<string, jQuery>}
   */
  active_frames = {};

  /**
   * Regex instances that match scripts that should not be put into frames.
   *
   * @since 3.18
   *
   * @type {RegExp}
   */
  exclude_scripts = /document\.location *=|apex\.live|(crm\.zoho|hotjar|googletagmanager|maps\.googleapis)\.com/i;

  /**
   * Cached frames available for use.
   *
   * @since 3.18
   *
   * @type {jQuery[]}
   */
  frames = [];

  /**
   * ETCoreFrames constructor
   *
   * @since 3.18
   *
   * @param {string}  [base_window]   Path to get Window from which to get styles and scripts.
   * @param {string}  [target_window] Path to get Window into which the frame should be inserted.
   */
  constructor(base_window = 'self', target_window = 'self') {
    this.base_window   = get(window, base_window);
    this.target_window = get(window, target_window);
    this.$base         = this.base_window.jQuery;
    this.$target       = this.target_window.jQuery;
  }

  _appendChildSafely(parent, child) {
    try {
      parent.appendChild(child);
    } catch(err) {
      console.error(err);
    }
  }

  _copyResourcesToFrame = $iframe => {
    const $html           = this.$base('html');
    const $body           = $html.find('body');
    const $resources      = $body.find('style, link');
    const $head_resources = $html.find('head').find('style, link');
    const $scripts        = $body.find('_script');

    const iframe_window = this.getFrameWindow($iframe);

    defaults(iframe_window, this.base_window);

    const $iframe_body = $iframe.contents().find('body');

    $iframe_body.parent().addClass('et-core-frame__html');

    $head_resources.each(function() {
      $iframe_body.prev().append(jQuery(this).clone());
    });

    $resources.each(function() {
      $iframe_body.append(jQuery(this).clone());
    });

    $scripts.each(function() {
      const script = iframe_window.document.createElement('script');

      script.src = jQuery(this).attr('src');

      iframe_window.document.body.appendChild(script);
    });
  };

  _createElement = (base_element, target_document) => {
    // Exclude current element from the iframe if it's on blocked list.
    if (isElementExcluded(base_element)) {
      return;
    }

    this._filterBaseElementContent(base_element);

    const element    = target_document.importNode(base_element, true);
    const $resources = jQuery(element).find('link, script, style');

    jQuery(element).find('#et-fb-app-frame, #et-bfb-app-frame, #wpadminbar').remove();

    this._filterElementContent(element);

    // Browsers will not load a resource node when it's imported from another document
    // because it was loaded already. Thus, we need to create new nodes for any resources
    // found nested inside this element.
    $resources.each((i, node) => {
      const $node    = jQuery(node);
      const $parent  = $node.parent();
      const new_node = this._createResourceElement(node, target_document);

      $node.remove();
      new_node && this._appendChildSafely($parent[0], new_node);
    });

    return element;
  };

  _createFrame = (id, move_dom = false, parent = 'body') => {
    const $iframe = this.$target('<iframe>');

    $iframe
      .addClass('et-core-frame')
      .attr('id', id)
      .appendTo(this.$target(parent))
      .parents()
      .addClass('et-fb-root-ancestor');

    // Add .et-fb-iframe-ancestor classname from app frame until body
    $iframe
      .parentsUntil('body')
      .addClass('et-fb-iframe-ancestor');

    // We do the following after the iframe is in the DOM to avoid double load event that can occur
    // with Chrome and Safari in some cases
    $iframe.on('load', () => {
      this._enableSalvattoreInVB();

      if (move_dom) {
        this._moveDOMToFrame($iframe);
      } else {
        this._copyResourcesToFrame($iframe);
      }
    });

    $iframe[0].src = `javascript:'<!DOCTYPE html><html><body></body></html>'`;

    return $iframe;
  };

  _createResourceElement = (base_element, target_document) => {
    const { id, nodeName: name, href, rel, type } = base_element;

    const attrs = ['id', 'className', 'href', 'type', 'rel', 'innerHTML', 'media', 'screen', 'crossorigin', 'data-et-type'];

    if ('et-fb-top-window-css' === id) {
      return;
    }

    if ('et-frontend-builder-css' === id && IS_YARN_START) {
      return;
    }

    if (isScriptExcluded(base_element) || isScriptTopOnly(base_element) || isElementExcluded(base_element)) {
      return;
    }

    const element = target_document.createElement(name);

    // Use a custom src for the app window if the element defines one.
    const appSrc = base_element.getAttribute('data-et-vb-app-src');

    if (appSrc) {
      element.src = appSrc;
    } else {
      attrs.push('src');
    }

    const src = appSrc ? appSrc : base_element.src;

    if ((src || (href && type !== 'text/less')) && ('LINK' !== name || 'stylesheet' === rel)) {
      this.loading.push(this._resourceLoadAsPromise(element));
    }

    if ('SCRIPT' === name) {
      element.async = element.defer = false;
    }

    forEach(attrs, attr => {
      if (base_element[attr]) {
        element[attr] = base_element[attr];
        return;
      }

      if (base_element.getAttribute(attr)) {
        element.setAttribute(attr, base_element.getAttribute(attr));
        return;
      }
    });

    return element;
  };

  _maybeCreateFrame = () => {
    if (isEmpty(this.frames)) {
      requestAnimationFrame(() => {
        this.frames.push(this._createFrame());
      });
    }
  };

  /**
   * Filter element before node is imported (base element).
   *
   * @since 4.9.3 Renamed from `_filterElementContent` into `_filterBaseElementContent` for better meaning.
   *
   * @param {HTMLElement} node
   */
  _filterBaseElementContent = (node) => {
    if (node.id === 'page-container') {
      const $mobileMenu = jQuery(node).find('#mobile_menu');
      if ($mobileMenu.length > 0) {
        $mobileMenu.remove();
      }
    }

    // Filter all blocked child elements under the original (base) element.
    const blocklistSelectors = get(window, 'ET_Builder.Preboot.elements.blocklist.selectors');
    if (blocklistSelectors) {
      $(node).find(blocklistSelectors).remove();
    }
  };

  /**
   * Filter element after node is imported (element).
   *
   * @since 4.9.3
   *
   * @param {HTMLElement} node
   */
  _filterElementContent = (node) => {
    // Filter all blocked child elements under the imported element.
    const blocklistSelectors = get(window, 'ET_Builder.Preboot.elements.iframeBlocklist.selectors');
    if (blocklistSelectors) {
      $(node).find(blocklistSelectors).remove();
    }
  };

  _moveDOMToFrame = $iframe => {
    const base_head       = this.base_window.document.head;
    const body_children   = this.$base('body').contents().not('iframe, #wpadminbar').get();

    const target_window   = this.getFrameWindow($iframe);
    const target_document = $iframe.contents()[0];
    const target_head     = $iframe.contents()[0].head;
    const target_body     = $iframe.contents()[0].body;

    const resource_nodes  = ['LINK', 'SCRIPT', 'STYLE'];
    const loading         = [];

    this.loading = [];

    forEach(base_head.childNodes, child => {
      const is_resource = includes(resource_nodes, child.nodeName);

      let element = is_resource
        ? this._createResourceElement(child, target_document)
        : this._createElement(child, target_document);

      if (! element) {
        return; // continue
      }

      this._appendChildSafely(target_head, element);
    });

    target_body.className = this.base_window.ET_Builder.Misc.original_body_class;

    forEach(body_children, child => {
      const is_resource = includes(resource_nodes, child.nodeName);

      let element = is_resource
        ? this._createResourceElement(child, target_document)
        : this._createElement(child, target_document);

      if (! element) {
        return; // continue
      }

      this._appendChildSafely(target_body, element);
    });

    const documentWrites = uniq(get(window, 'ET_Builder.Preboot.writes', []));
    if (documentWrites.length > 0) {
      // The only `document.write`s we care about are the ones used to load scripts
      // like WP polyfills, anything else will just break the builder or show content
      // in the wrong place, hence we wrap them in an hidden container.
      try {
        jQuery(target_body).append(`<div style="display: none">${documentWrites.join(' ')}</div>`);
      } catch(e) {
        // We don't wanna log the exception here since, polyfills excluded, whatever is using `document.write`
        // would hardly care about handling errors. E.g. checking if DOM element exists before trying to use it....
      }
    }

    Promise.all(this.loading).then(() => {
      // Fire events again since browser fired before we added content to the frame
      const frame_document = $iframe[0].contentDocument;
      const frame_window   = $iframe[0].contentWindow;

      let dom_content_event;
      let load_event;

      if ('function' !== typeof(Event)) {
        dom_content_event = document.createEvent('Event');
        load_event        = document.createEvent('Event');

        dom_content_event.initEvent('DOMContentLoaded', true, true);
        load_event.initEvent('load', true, true);

      } else {
        dom_content_event = new Event('DOMContentLoaded');
        load_event        = new Event('load');
      }

      // Add small delay before firiing the events to give some Extra time to attach event handlers
      // Otherwise it may fire to early and event handlers attachment will fail.
      setTimeout(() => {
        frame_document.dispatchEvent(dom_content_event);
        frame_window.dispatchEvent(load_event);
      }, 0);
    }).catch(err => console.error(err));
  };

  _resourceLoadAsPromise(resource) {
    return new Promise((resolve) => {
      resource.addEventListener('load', resolve);
      resource.addEventListener('error', resolve);
    });
  }

  _enableSalvattoreInVB() {
    jQuery('[data-et-vb-columns]').each(function () {
      const $this = jQuery(this);
      $this
          .attr('data-columns', $this.attr('data-et-vb-columns'))
          .removeAttr('data-et-vb-columns');
    });
  }

  /**
   * Gets a frame if it exists, creates a new one otherwise.
   *
   * @since 3.18
   *
   * @param {Object}  options                    Options
   * @param {string}  options.id                 Unique identifier for the frame.
   * @param {Object}  [options.classnames]       CSS classes
   * @param {string}  [options.classnames.frame] CSS classes for the frame.
   * @param {string}  [options.classnames.body]  CSS classes for the frame's body element.
   * @param {boolean} [options.move_dom]         Whether or not to move the entire DOM from base window to the frame.
   * @param {string}  [options.parent]           CSS selector for the frame's parent element.
   *
   * @return {jQuery}
   */
  get({ id = '', classnames = { frame: '', body: '' }, move_dom = false, parent = 'body' }) {
    if (this.active_frames[id]) {
      return this.active_frames[id];
    }

    if (move_dom) {
      this.active_frames[id] = this._createFrame(id, move_dom, parent);
    } else {
      this.active_frames[id] = this.frames.pop() || this._createFrame(id, move_dom, parent);
    }

    const iframe_window = this.getFrameWindow(this.active_frames[id]);

    iframe_window.name = id;

    return this.active_frames[id];
  }

  /**
   * Gets an iframe's {@see window} object;
   *
   * @param {jQuery} $iframe
   *
   * @return {Window}
   */
  getFrameWindow($iframe) {
    return $iframe[0].contentWindow || $iframe[0].contentDocument;
  }

  static instance(id, base_window = 'self', target_window = 'self') {
    if (! ETCoreFrames._instances[id]) {
      ETCoreFrames._instances[id] = new ETCoreFrames(base_window, target_window);
    }

    return ETCoreFrames._instances[id];
  }

  release(id) {
    setTimeout(() => {
      const $frame = this.get({ id });

      if (! $frame) {
        return;
      }

      $frame[0].className = 'et-core-frame';

      $frame.removeAttr('id');
      $frame.removeAttr('style');

      this.frames.push($frame);

      delete this.active_frames[id];
    }, 250);
  }
}


export default ETCoreFrames;
