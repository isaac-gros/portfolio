var Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Webpack config
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .enableSassLoader()
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // Scripts
    .addEntry('app', './assets/scripts/app.js')
    .addEntry('editor', './assets/scripts/editor.js')
    .addEntry('explorer', './assets/scripts/explorer.js')

    // Styles
    .addStyleEntry('styles', './assets/styles/app.scss')
;

module.exports = Encore.getWebpackConfig();
