This project uses a minimal JS component system for UI consistency.

Files added:
- `src/styles/design.js` — design tokens (utilities) export `ui` object
- `components/SectionCard.jsx` — reusable card wrapper
- `components/PrimaryButton.jsx` — primary button component
- `components/PageLayout.jsx` — basic page layout wrapper
- `components/index.js` — exports for convenience

Notes:
- The app is primarily PHP; these components are small React-friendly building blocks you can adopt progressively.
- To use them in a React build, import from `components` and ensure `prop-types` is installed.
- If you prefer plain PHP templating, we can convert the same tokens into CSS classes and a PHP include system instead.
