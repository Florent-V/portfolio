const Encore = require('@symfony/webpack-encore')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const { GenerateSW } = require('workbox-webpack-plugin')
const path = require('path')

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev')
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or subdirectory deploy
  //.setManifestKeyPrefix('build/')
  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[hash:8].[ext]',
  })
  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('app', './assets/app.js')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  .enableVueLoader()
  .enableVueLoader(() => {}, { runtimeCompilerBuild: false })

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge('./assets/controllers.json')

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  // Copier les fichiers nécessaires pour la PWA
  .addPlugin(
    new CopyWebpackPlugin({
      patterns: [
        {
          from: './assets/pwa/manifest.json',
          to: '../manifest.json', // Sortie dans public/manifest.json
        },
        {
          from: './assets/pwa/icons',
          to: '../pwa/icons', // Sortie dans public/pwa/icons
        },
        {
          from: './assets/pwa/screenshots',
          to: '../pwa/screenshots', // Sortie dans public/pwa/icons
        },
      ],
    }),
  )

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // configure Babel
  // .configureBabel((config) => {
  //     config.plugins.push('@babel/a-babel-plugin');
  // })

  // enables and configure @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage'
    config.corejs = '3.38'
  })

  .configureDevServerOptions((options) => {
    options.setupMiddlewares = (middlewares) => {
      return middlewares.filter(
        (middleware) => middleware.name !== 'cross-origin-header-check',
      )
    } // related to https://github.com/webpack/webpack-dev-server/issues/5446
    options.liveReload = true
    options.static = {
      watch: false,
    }
    options.watchFiles = {
      paths: ['assets/locales/*', 'src/**/*.php', 'templates/**/*'],
    }
    options.allowedHosts = 'all'
  })
  .enablePostCssLoader()
  // enables Sass/SCSS support
  .enableSassLoader()

// Register the service worker plugin only in production
if (Encore.isProduction()) {
  Encore.addPlugin(
    new GenerateSW({
      swDest: path.resolve(__dirname, 'public/service-worker.js'),
      clientsClaim: true,
      skipWaiting: true,
      sourcemap: false,
      runtimeCaching: [
        {
          urlPattern: /\.(?:js|css)$/,
          handler: 'CacheFirst',
          options: {
            cacheName: 'static-resources',
          },
        },
        {
          urlPattern: /\.(?:png|jpg|jpeg|svg|gif|woff|woff2|eot|ttf|otf)$/,
          handler: 'CacheFirst',
          options: {
            cacheName: 'images',
          },
        },
        {
          urlPattern: /^\/.*/, // pour toutes les pages Symfony
          handler: 'NetworkFirst',
          options: {
            cacheName: 'pages',
          },
        },
      ],
    }),
  )
}

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()

module.exports = Encore.getWebpackConfig()
