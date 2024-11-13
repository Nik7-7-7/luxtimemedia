// External dependencies
import React from 'react';
import PropTypes from 'prop-types';
import { Motion, spring } from 'react-motion';

class ETCoreAnimationFold extends React.PureComponent {
  static propTypes = {
    enabled: PropTypes.bool,
    scale: PropTypes.number,
    children: PropTypes.element.isRequired,
  };

  static defaultProps = {
    enabled: true,
    scale: 0.6,
  };

  render() {
    const {children, enabled, scale} = this.props;

    const child = React.Children.only(children);

    return(
      <Motion
        defaultStyle={{
          scale,
          opacity: 0,
        }}
        style={{
          scale:   enabled ? spring( 1, { stiffness: 300, damping: 20 } ) : 1,
          opacity: enabled ? spring( 1, { stiffness: 300, damping: 20 } ): 1,
        }}
      >
        {interpolatingStyles =>
          React.cloneElement(child, {
            style: {
              ...child.props.style,
              opacity: interpolatingStyles.opacity,
              transform: `scaleY( ${interpolatingStyles.scale} )`,
              transformOrigin: 'top',
            }
          })
        }
      </Motion>
    );
  }
}

export default ETCoreAnimationFold;
