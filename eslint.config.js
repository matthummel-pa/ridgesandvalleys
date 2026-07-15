// ESLint flat config for the theme's hand-written, no-build-step JS
// (resources/js/*-editor.js block editor files, resources/js/app.js, etc).
// Run `npm install` first (eslint is a devDependency) then `npm run lint:js`.
export default [
  {
    ignores: ['public/**', 'node_modules/**', 'vendor/**'],
  },
  {
    files: ['resources/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'script',
      globals: {
        // Browser
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        localStorage: 'readonly',
        fetch: 'readonly',
        MutationObserver: 'readonly',
        IntersectionObserver: 'readonly',
        CustomEvent: 'readonly',
        // WordPress block editor globals these files rely on (no import step)
        wp: 'readonly',
        // Theme-localized data objects (wp_localize_script)
        mhSocialBlock: 'readonly',
        mhBarBlocks: 'readonly',
      },
    },
    rules: {
      'no-unused-vars': ['warn', { args: 'none' }],
      'no-undef': 'error',
      eqeqeq: ['warn', 'smart'],
      'no-var': 'warn',
      'prefer-const': 'warn',
    },
  },
];
