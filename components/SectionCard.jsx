import React from 'react';
import PropTypes from 'prop-types';
import { ui } from '../src/styles/design';

export default function SectionCard({ children, className = '' }) {
  return (
    <div className={`${ui.card} p-6 ${className}`}>
      {children}
    </div>
  );
}

SectionCard.propTypes = {
  children: PropTypes.node,
  className: PropTypes.string,
};
