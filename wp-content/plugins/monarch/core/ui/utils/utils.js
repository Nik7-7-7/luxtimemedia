// External Dependencies
import isEqual from 'lodash/isEqual';
import get from 'lodash/get';

const Utils = {

  toString(value) {
    if (value && typeof value.toString === 'function') {
      return value.toString();
    }

    if (Array.isArray(value)) {
      return value.join(',');
    }

    if (typeof value === 'undefined' || value === null) {
      return '';
    }

    return "" + value;
  },

  decodeHtmlEntities(value) {
    return Utils.toString(value).replace(/&#(\d+);/g, (match, dec) => String.fromCharCode(dec));
  },

  shouldComponentUpdate(component, nextProps, nextState) {
    return ! isEqual(nextProps, component.props) || ! isEqual(nextState, component.state);
  },

  isScriptExcluded(node) {
    const { allowlist, blocklist } = window.ET_Builder.Preboot.scripts;
    const { nodeName, innerHTML, src, className } = node;

    if (nodeName !== 'SCRIPT') {
      return false;
    }

    if (className) {
      return blocklist.className.test(className);
    }

    if (innerHTML) {
      // Allowlist has precendence over blocklist
      return !allowlist.innerHTML.test(innerHTML) && blocklist.innerHTML.test(innerHTML);
    }

    return blocklist.src.test(src);
  },

  isScriptTopOnly(node) {
    const { topOnly } = window.ET_Builder.Preboot.scripts;
    const { nodeName, src } = node;

    if (nodeName !== 'SCRIPT') {
      return false;
    }

    return topOnly.src.test(src);
  },

  /**
   * Whether an element should be excluded from the app window (iframe) and top window.
   *
   * Different with isScriptExcluded() that has very specific checks on `SCRIPT`, this
   * method can be used to target more general HTML elements such as `LINK`, `STYLE`,
   * `DIV`, etc.
   *
   * @since 4.9.3
   *
   * @param {HTMLElement} node
   * @param {boolean}     checkBuilderType
   *
   * @return {boolean}
   */
  isElementExcluded(node, checkBuilderType = false) {
    const isBFB = get(window, 'et_fb_preboot.is_BFB', false);
    const isTB  = get(window, 'et_fb_preboot.is_TB', false);
    const isVB  = ! isBFB && ! isTB;

    const blocklist       = get(window, 'window.ET_Builder.Preboot.elements.blocklist', {});
    const iframeBlocklist = get(window, 'window.ET_Builder.Preboot.elements.iframeBlocklist', {});

    const { className } = node;

    if (className) {
      // Iframe's blocked list elements should be checked only on iframe window. We
      // shouldn't check it when this method is being used to watch DOM change on
      // top/parent/original window. In this case, it's VB.
      const checkIframe          = checkBuilderType ? ! isVB : true;
      const isClassBlocked       = blocklist.className ? blocklist.className.test(className) : false;
      const isClassIframeBlocked = checkIframe && iframeBlocklist.className ? iframeBlocklist.className.test(className) : false;
      return isClassBlocked || isClassIframeBlocked;
    }

    return false;
  },

  /**
   * Check if the given element contains the target descendant element
   *
   * @param {HTMLElement} element
   * @param {HTMLElement} target
   *
   * @return {boolean}
   */
  doesDomElementContain(element, target) {
    let node = target;

    while (node) {
      if (node === element) {
        return true;
      }

      node = node.parentNode;
    }

    return false;
  },

  /**
   * Help to compose several React refs allowing to have multiple refs to a single element
   *
   * @param refs
   *
   * @return {function(...[*]=)}
   */
  composeRef(...refs) {
    return element => {
      refs.forEach(ref => {
        if (!ref) {
          return;
        }

        if (typeof ref === 'function') {
          ref(element);

          return;
        }

        ref.current = element;
      });
    };
  },

  validateRefType(props, propName, componentName, location, propFullName) {
    const propValue = props[propName];

    if (propValue === null) {
      return null;
    }

    if (propValue === undefined) {
      return new Error(`The prop \`${propFullName}\` is marked as required in \`${componentName}\`.`);
    }

    if (propValue.nodeType !== 1) {
      const className = propValue.constructor.name;
      return new Error(`Invalid prop \`${propFullName}\` of type \`${className}\` supplied to \`${componentName}\`, expected instance of \`HTMLElement\``);
    }

    return null;
  },
};

const {
  toString,
  decodeHtmlEntities,
  shouldComponentUpdate,
  isScriptExcluded,
  isScriptTopOnly,
  isElementExcluded,
  doesDomElementContain,
  composeRef,
  validateRefType,
} = Utils;

export {
  toString,
  decodeHtmlEntities,
  shouldComponentUpdate,
  isScriptExcluded,
  isScriptTopOnly,
  isElementExcluded,
  doesDomElementContain,
  composeRef,
  validateRefType,
}

export default Utils;
