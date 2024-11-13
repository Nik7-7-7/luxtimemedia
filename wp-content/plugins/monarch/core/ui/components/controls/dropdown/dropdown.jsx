// External dependencies
import React from 'react';
import PropTypes from 'prop-types';
import noop from 'lodash/noop';
import classnames from 'classnames';

// Local dependencies
import { ANIMATION_FOLD, ANIMATION_POPUP, ETCorePopup } from '@core-ui/components/popups/popup';
import { composeRef } from '@core-ui/utils/utils';

import './dropdown.scss';

const CLICK_MODE = 'click';
const HOVER_MODE = 'hover';

const getDefaultDocument = () => window.document;

class ETCoreDropdown extends React.PureComponent {
  static propTypes = {
    animation:       PropTypes.oneOf([ANIMATION_FOLD, ANIMATION_POPUP]),
    closeOnClick:    PropTypes.bool,
    disabled:        PropTypes.bool,
    getDocument:     PropTypes.func,
    menu:            PropTypes.element,
    offsetH:         PropTypes.number,
    offsetV:         PropTypes.number,
    onClick:         PropTypes.func,
    onMouseEnter:    PropTypes.func,
    onMouseLeave:    PropTypes.func,
    onVisibleChange: PropTypes.func,
    onSelect:        PropTypes.func,
    open:            PropTypes.bool,
    trigger:         PropTypes.oneOf([CLICK_MODE, HOVER_MODE]),
  };

  static defaultProps = {
    animation:       ANIMATION_FOLD,
    closeOnClick:    false,
    disabled:        false,
    getDocument:     getDefaultDocument,
    menu:            false,
    offsetH:         0,
    offsetV:         0,
    onClick:         noop,
    onMouseEnter:    noop,
    onMouseLeave:    noop,
    onVisibleChange: noop,
    trigger:         CLICK_MODE,
  };

  constructor(props) {
    super(props);

    this.triggerRef = React.createRef();

    this.state = {
      isVisible: false,
    };
  }

  get visible() {
    const { open } = this.props;

    return undefined !== open ? open : this.state.isVisible;
  }

  setVisible(isVisible) {
    const { open, onVisibleChange } = this.props;

    if (undefined === open) {
      this.setState({
        isVisible,
      });
    }

    onVisibleChange(isVisible);
  }

  handleTriggerClick = (e) => {
    const {
      trigger,
      onClick,
      open,
      closeOnClick,
    } = this.props;

    if (CLICK_MODE === trigger) {
      if (closeOnClick) {
        const opened = undefined !== open ? open : this.state.isVisible;
        this.setVisible(!opened);
      } else {
        this.setVisible(true);
      }
    }

    onClick(e);
  };

  handleMouseEnter = (e) => {
    const {
      trigger,
      onMouseEnter,
    } = this.props;

    if (HOVER_MODE === trigger) {
      this.setVisible(true);
    }

    onMouseEnter(e);
  };

  handleMouseLeave = (e) => {
    const {
      trigger,
      onMouseLeave,
    } = this.props;

    if (HOVER_MODE === trigger) {
      this.setVisible(false);
    }

    onMouseLeave(e);
  };

  onSelect = (value) => {
    const { onSelect } = this.props;

    this.setVisible(false);

    onSelect(value);
  };

  close = () => {
    this.setVisible(false);
  };

  renderMenu() {
    if (!this.visible) {
      return null;
    }

    const { menu, animation, offsetV, getDocument } = this.props;

    const triggerWidth = this.triggerRef.current ? this.triggerRef.current.offsetWidth : 0;

    const child      = React.Children.only(menu);
    const menuCloned = React.cloneElement(child, {
      style: {
        ...child.props.style,
        width: child.props.style && child.props.style.width ? child.props.style.width : triggerWidth
      },
      onSelect: this.onSelect,
    });

    return (
      <ETCorePopup
        anchor={this.triggerRef}
        animation={animation}
        getDocument={getDocument}
        visible={this.visible}
        offsetV={offsetV}
        onClose={this.close}
      >
        {menuCloned}
      </ETCorePopup>
    )
  }

  render() {
    const {
      children,
      disabled,
    } = this.props;

    const child = React.Children.only(children);
    const trigger = React.cloneElement(child, {
      className:    classnames(child.props.className, 'et-core-control-dropdown'),
      onClick:      this.handleTriggerClick,
      onMouseEnter: this.handleMouseEnter,
      onMouseLeave: this.handleMouseLeave,
      ref:          composeRef(child.ref, this.triggerRef),
      disabled,
    });

    const menu = this.renderMenu();

    return (
      <React.Fragment>
        {trigger}
        {menu}
      </React.Fragment>
    );
  }
}

export default ETCoreDropdown;
