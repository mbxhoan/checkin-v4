import path from 'path'
import fs from 'fs'
import { fileURLToPath } from 'url'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { viteStaticCopy } from 'vite-plugin-static-copy'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const resourcePath = (subPath) => path.resolve(__dirname, subPath)
const staticCopyTargets = [
  { src: 'resources/fonts', dest: '' },
  { src: 'resources/images', dest: '' },
  { src: 'resources/js', dest: '' },
  { src: 'resources/libs', dest: '' },
]

if (fs.existsSync(resourcePath('resources/json'))) {
  staticCopyTargets.push({ src: 'resources/json', dest: '' })
}

export default defineConfig({
  css: {
    preprocessorOptions: {
      scss: {
        loadPaths: [path.resolve(__dirname, 'node_modules')],
      },
    },
  },
  build: {
    manifest: true,
    rtl: true,
    outDir: 'public/build/',
    cssCodeSplit: true,
    rollupOptions: {
      output: {
        assetFileNames: (css) => {
          if (css.name.split('.').pop() === 'css') {
            return 'css/[name].min.css'
          } else {
            return 'icons/' + css.name
          }
        },
        entryFileNames: 'js/[name].js',
      },
    },
  },

  plugins: [
    laravel({
      input: [
        // your new SCSS files
        'resources/scss/bootstrap.scss',
        'resources/scss/icons.scss',
        'resources/scss/app.scss',

        // existing SCSS
        'resources/sass/scan.scss',
        'resources/sass/app.scss',
        'resources/sass/admin.scss',
        'resources/sass/web.scss',

        // existing JS
        'resources/js/app.js',
        'resources/js/admin.js',
        'resources/js/web.js',
        'resources/js/scan.js',
        'resources/js/admin/dashboard/detail.js',
        'resources/js/admin/labels/detail.js',
        'resources/js/admin/cards/detail.js',
        'resources/js/admin/cards/aim.js',
        'resources/js/admin/campaigns/detail.js',
        'resources/js/admin/emails/history.js',
        'resources/js/admin/companys/detail.js',
        'resources/js/admin/clients/detail.js',
        'resources/js/admin/clients/index.js',
        'resources/js/admin/clients/import.js',
        'resources/js/admin/events/detail.js',
        'resources/js/admin/events/index.js',
        'resources/js/admin/reports/detail.js',
        'resources/js/admin/users/index.js',
        'resources/js/admin/users/detail.js',
        'resources/js/admin/checkins/config.js',
        'resources/js/admin/landing_pages/detail.js',
        'resources/js/admin/lucky_draws/detail.js',
        'resources/js/admin/email_templates/detail.js',
        'resources/js/web/landing_pages/register.js',
        'resources/js/web/landing_pages/success.js',
        'resources/js/scan/scan.js',

        /* reports */
        'resources/js/admin/reports/apexcharts.js',
        'resources/js/admin/reports/echarts.js',
        'resources/js/admin/reports/jquery-knob.js',
      ],
      refresh: true,
    }),

    viteStaticCopy({
      targets: staticCopyTargets,
    }),
  ],
})
