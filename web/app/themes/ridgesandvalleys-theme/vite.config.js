import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin'

if (! process.env.APP_URL) {
  process.env.APP_URL = 'http://example.test'
}

// Theme public path. Defaults to Bedrock (web/app/themes/…) — the main DDEV dev
// site. The no-Docker native server exports VITE_BASE for a vanilla WP install
// (/wp-content/themes/…). Build-mode asset URLs are resolved from the WordPress
// theme URI at runtime, so this only needs to match for Vite hot-reload.
const base = process.env.VITE_BASE || '/app/themes/ridgesandvalleys-theme/public/build/'

export default defineConfig({
  base,
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.ts',
        'resources/js/tools.ts',
        'resources/css/editor.css',
        'resources/js/editor.ts',
      ],
      refresh: true,
      assets: ['resources/images/**', 'resources/fonts/**'],
    }),
    wordpressPlugin(),
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
      disableTailwindBorderRadius: false,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
})
