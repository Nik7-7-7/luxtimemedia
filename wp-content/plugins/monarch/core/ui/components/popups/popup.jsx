// External dependencies
import React from 'react';
import PropTypes from 'prop-types';
import noop from 'lodash/noop';
import { alignElement } from 'dom-align';

// Internal dependencies
import ETCoreAnimationPopup from '@core-ui/components/animations/popup';
import ETCoreAnimationFold from '@core-ui/components/animations/fold';
import { doesDomElementContain, validateRefType } from '@core-ui/utils/utils';

import './popup.scss'

const ANIMATION_POPUP = 'popup';
const ANIMATION_FOLD  = 'fold';

const AlignConfig = {
  [ANIMATION_FOLD]: {
    points: ['tl', 'bl'], // align top left point of the source element with bottom left point of the target element
  },
  [ANIMATION_POPUP]: {
    points: ['tl', 'tl'], // align top left point of the source element with top left point of the target element
    offset: ['50%', '50%'],
  },
};

const getDefaultDocument = () => window.document;

/**
 * @typedef {Object} RemovableListener
 * @property {function} remove - The function used to remove the attached listener
 */

/**
 * Wrapper to avoid multiple state updates
 *
 * @param {HTMLElement} target - The target element
 * @param {string} eventType   - The DOM event type
 * @param {function} callback  - The callback
 *
 * @return {RemovableListener}
 */
const addEventListener = (target, eventType, callback) => {
  // Make the callback batched by React if possible to avoid multiple state updates
  const wrappedCallback = ReactDOM.unstable_batchedUpdates ?
    (e) => {
      ReactDOM.unstable_batchedUpdates(callback, e);
    }
    : callback;

  if (target.addEventListener) {
    target.addEventListener(eventType, wrappedCallback);
  }

  return {
    remove: () => {
      target.removeEventListener(eventType, wrappedCallback);
    }
  }
};

class ETCorePopup extends React.PureComponent {
  static propTypes = {
    anchor:               PropTypes.shape({ current: validateRefType }),
    animation:            PropTypes.oneOf([ANIMATION_POPUP, ANIMATION_FOLD]),
    animationEnabled:     PropTypes.bool,
    getDocument:          PropTypes.func,
    ignoreDocumentClicks: PropTypes.bool,
    offsetH:              PropTypes.number,
    offsetV:              PropTypes.number,
    onClose:              PropTypes.func,
    onMouseEnter:         PropTypes.func,
    onMouseLeave:         PropTypes.func,
    style:                PropTypes.object,
    visible:              PropTypes.bool,
  };

  static defaultProps = {
    animation:            ANIMATION_POPUP,
    animationEnabled:     true,
    getDocument:          getDefaultDocument,
    ignoreDocumentClicks: false,
    offsetH:              0,
    offsetV:              0,
    onClose:              noop,
    onMouseEnter:         noop,
    onMouseLeave:         noop,
    style:                {},
    visible:              true,
  };

  constructor(props) {
    super(props);

    this.handlerOutsideClick = null;
    this.handlerOutsideTouch = null;

    this.popupRef = React.createRef();
  }

  componentDidMount() {
    const document = this.props.getDocument();

    this.handlerOutsideClick = addEventListener(document, 'mousedown', this.handleDocumentClick);
    this.handlerOutsideTouch = addEventListener(document, 'touchstart', this.handleDocumentClick);

    this.alignPopup();
  }

  componentWillUnmount() {
    this.handlerOutsideClick.remove();
    this.handlerOutsideTouch.remove();
  }

  componentDidUpdate(prevProps) {
    if (prevProps.visible !== this.props.visible) {
      this.alignPopup();
    }
  }

  handleDocumentClick = (e) => {
    const { target } = e;

    if (this.props.ignoreDocumentClicks || doesDomElementContain(this.props.anchor.current, target) || doesDomElementContain(this.popupRef.current, target)) {
      return;
    }

    this.props.onClose();
  };

  alignPopup() {
    const { animation, anchor, offsetH, offsetV, visible } = this.props;

    if (!visible) {
      return;
    }

    const alignConfig = {
      ...AlignConfig[animation],
      targetOffset: [-offsetH, -offsetV],
    };

    alignElement(this.popupRef.current, anchor.current, alignConfig);
  }

  renderPopup() {
    const { anchor, style, onMouseEnter, onMouseLeave, children } = this.props;
    const width = anchor.current ? anchor.current.offsetWidth : 0;

    return (
      <div
        className="et-core-popup__wrapper"
        ref={this.popupRef}
        onMouseEnter={onMouseEnter}
        onMouseLeave={onMouseLeave}
        style={{
          width,
          ...style
        }}
      >
        {children}
      </div>
    );
  }

  render() {
    const { animation, animationEnabled, visible } = this.props;

    if (!visible) {
      return null;
    }

    const popup = this.renderPopup();

    return animation === ANIMATION_POPUP
      ? <ETCoreAnimationPopup enabled={animationEnabled}>
          {popup}
        </ETCoreAnimationPopup>
      : <ETCoreAnimationFold enabled={animationEnabled}>
          {popup}
        </ETCoreAnimationFold>;
  }
}

export {
  ANIMATION_FOLD,
  ANIMATION_POPUP,
  ETCorePopup,
}

export default ETCorePopup;
