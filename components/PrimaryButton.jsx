import React from 'react';
import PropTypes from 'prop-types';
import { ui } from '../src/styles/design';

export default function PrimaryButton({ children, className = '', ...props }) {
  return (
    <button className={`${ui.buttonPrimary} ${className}`} {...props}>
      {children}
    </button>
  );
}

PrimaryButton.propTypes = {
  children: PropTypes.node,
  className: PropTypes.string,
};
