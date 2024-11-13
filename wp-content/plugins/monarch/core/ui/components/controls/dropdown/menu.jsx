// External dependencies
import React from 'react';
import * as ReactIs from 'react-is';
import PropTypes from 'prop-types';
import noop from 'lodash/noop';

// Internal dependencies
import './menu.scss';

class ETCoreMenu extends React.PureComponent {
  static propTypes = {
    selectedKey: PropTypes.string,
    onMouseEnter: PropTypes.func,
    onMouseLeave: PropTypes.func,
    customRender: PropTypes.func,
  };

  static defaultProps = {
    onMouseEnter: noop,
    onMouseLeave: noop,
    customRender: noop,
  };

  constructor(props) {
    super(props);

    this.mouseEntered = false;
    this.currentIndex = 0;
  }

  handleMouseEnter = () => {
    this.mouseEntered = true;

    this.props.onMouseEnter();
  };

  handleMouseLeave = () => {
    this.mouseEntered = false;

    this.props.onMouseLeave();
  };

  renderMenuItem = (item) => {
    if (!item) {
      return null;
    }

    if (ReactIs.isFragment(item)) {
      return React.Children.map(item.props.children, this.renderMenuItem)
    }

    const { key } = item;
    const {
      selectedKey,
      onSelect,
    } = this.props;

    const realKey    = key || this.currentIndex;
    const isSelected = realKey === selectedKey;

    this.currentIndex++;

    return React.cloneElement(item, {
      itemKey: realKey,
      isSelected,
      onSelect,
    });
  };

  renderMenuItems() {
    const { customRender, children } = this.props;

    this.currentIndex = 0;

    const menuItems = (
      <ul className='et-core-control-menu'>
        {React.Children.map(children, this.renderMenuItem)}
      </ul>
    );

    const customMenu = customRender ? customRender(menuItems) : menuItems;

    return customMenu;
  }

  render() {
    const { forwardedRef } = this.props;

    return (
      <div
        className='et-core-control-menu__wrapper'
        style={this.props.style}
        ref={forwardedRef}
        onMouseEnter={this.handleMouseEnter}
        onMouseLeave={this.handleMouseLeave}
      >
        {this.renderMenuItems()}
      </div>
    );
  }
}

export default React.forwardRef((props, ref) => <ETCoreMenu {...props} forwardedRef={ref}/>);
