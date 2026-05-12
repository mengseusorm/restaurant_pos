const mix = require('laravel-mix');
const dotenv = require('dotenv');
const path = require('path');
const CaseSensitivePathsPlugin = require('case-sensitive-paths-webpack-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .postCss('resources/css/app.css', 'public/css', [require("tailwindcss")])
    .version();

dotenv.config();

const appUrl = new URL(process.env.APP_URL);

mix.options({
    hmrOptions: {
        host: appUrl.hostname,
        port: 8005
    },
    terser: {
        terserOptions: {
            compress: {
                drop_console: true
            }
        }
    }
});

// mix.webpackConfig({
//     devServer: {
//         port: '8005'
//     },
// });

mix.webpackConfig({
    resolve: {
        alias: {
            '@vuepic/vue-datepicker$': path.resolve(__dirname, 'resources/js/components/shared/DynamicDatepicker.vue')
        }
    },
    ignoreWarnings: [
        /Critical dependency: require function is used in a way in which dependencies cannot be statically extracted/,
    ],
    plugins: [
        new CaseSensitivePathsPlugin(),
    ]
});

if (!mix.inProduction()) {
    const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
    mix.webpackConfig({
        stats: {
            warnings: false
        },
        plugins: [
            new BundleAnalyzerPlugin({
                analyzerMode: "static",
                reportFilename: "bundle-report.html",
                openAnalyzer: false
            })
        ]
    });
}
