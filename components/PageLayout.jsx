import React from 'react';
import PropTypes from 'prop-types';
import SectionCard from './SectionCard';
import { ui } from '../src/styles/design';

export default function PageLayout({ title, subtitle, children }) {
  return (
    <div className={ui.page}>
      <nav className="max-w-6xl mx-auto mb-6">
        {/* Navbar placeholder - integrate your shared navbar here */}
      </nav>

      <main className="max-w-6xl mx-auto">
        <header className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900">{title}</h1>
          {subtitle && <p className="text-gray-500 mt-2">{subtitle}</p>}
        </header>

        <SectionCard>
          {children}
        </SectionCard>
      </main>

      {/* Floating chatbot placeholder */}
    </div>
  );
}

PageLayout.propTypes = {
  title: PropTypes.string,
  subtitle: PropTypes.string,
  children: PropTypes.node,
};
