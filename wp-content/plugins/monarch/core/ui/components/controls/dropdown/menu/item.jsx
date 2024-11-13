// External dependencies
import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';

// Internal dependencies
import ETBuilderIcon from '../../../icon/icon';

import styles from './item.scss';

class ETCoreMenuItem extends React.PureComponent {
  static propTypes = {
    className: PropTypes.string,
    itemKey: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number,
    ]),
    value: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.number,
    ]),
    isSelected: PropTypes.bool,
    tooltip: PropTypes.string,
  };

  static defaultProps = {
    isSelected: false,
  };

  constructor(props) {
    super(props);

    this.state = {
      hovered: false,
    };
  }

  handleMouseEnter = () => {
    this.setState({
      hovered: true,
    });
  };

  handleMouseLeave = () => {
    this.setState({
      hovered: false,
    });
  };

  handleMouseDown = (e) => {
    if (e.nativeEvent.which === 1) {
      const { onSelect, itemKey, value } = this.props;

      onSelect && onSelect({
        key: itemKey,
        value,
      });
    }
  };

  render() {
    const { hovered }    = this.state;
    const { isSelected, className, itemKey, tooltip } = this.props;

    const classes = classnames(
      {
        'et-core-control-menu__item': true,
        'et-core-control-menu__item--hovered': hovered,
        'et-core-control-menu__item--selected': isSelected,
      },
      className
    );

    const checkMarkColor = hovered ? styles.checkMarkColorHovered : styles.checkMarkColor;

    return (
      <li
        key={itemKey}
        className={classes}
        onMouseEnter={this.handleMouseEnter}
        onMouseLeave={this.handleMouseLeave}
        onMouseDown={this.handleMouseDown}
        onTouchStart={this.handleMouseDown}
        data-tip={tooltip}
      >
        <div className="et-core-control-menu__item__content">
          {this.props.children}
        </div>
        {isSelected
        && <div className="et-core-control-menu__item__check-mark">
             <ETBuilderIcon
               icon="check"
               color={checkMarkColor}
             />
           </div>
        }
      </li>
    );
  }
}

export default ETCoreMenuItem;
