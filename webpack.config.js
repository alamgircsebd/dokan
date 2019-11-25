const webpack = require('webpack');
const path = require('path');
const package = require('./package.json');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const OptimizeCSSPlugin = require('optimize-css-assets-webpack-plugin');
const BrowserSyncPlugin = require( 'browser-sync-webpack-plugin' );

const config = require( './config.json' );

// Naming and path settings
var appName = 'app';

var exportPath = path.resolve(__dirname, './assets/js');

var entryPoints = {};

var rootEntryPoints = {
    // 'vue-pro-frontend': './src/frontend/main.js',
    'vue-pro-frontend-shipping': './src/frontend/shipping.js',
    'vue-pro-admin': './src/admin/main.js',
    // 'vue-pro-admin': './includes/modules/subscription/src/main.js',
    // style: './less/style.less',
};

var moduleEntryPoints = {
    'subscription': {
        'subscription': 'main.js',
    },

    'store-reviews': {
        'admin': 'main.js',
    },

    'wholesale': {
        'admin': 'main.js'
    },

    'report-abuse': {
        'dokan-report-abuse': 'js/frontend/main.js',
        'dokan-report-abuse-admin': 'js/admin/main.js',
        'dokan-report-abuse-admin-single-product': 'js/admin/single-product.js'
    }
};

Object.keys(rootEntryPoints).forEach(function (output) {
    entryPoints[ output ] = rootEntryPoints[output];
});

Object.keys(moduleEntryPoints).forEach(function (dokanModule) {
    var modulePath = `includes/modules/${dokanModule}`;

    Object.keys(moduleEntryPoints[dokanModule]).forEach(function (moduleOutput) {
        entryPoints[ `../../${modulePath}/assets/js/${moduleOutput}` ] = `./${modulePath}/src/${moduleEntryPoints[dokanModule][moduleOutput]}`;
    });
});

// Enviroment flag
var plugins = [];
var env = process.env.WEBPACK_ENV;

function isProduction() {
    return process.env.WEBPACK_ENV === 'production';
}

// extract css into its own file
const extractCss = new ExtractTextPlugin({
    filename(getPath) {
        return getPath('../css/[name].css').replace('assets/js', 'assets/css');
    }
});

plugins.push( extractCss );

// Extract all 3rd party modules into a separate 'vendor' chunk
// plugins.push(new webpack.optimize.CommonsChunkPlugin({
//     name: 'vendor',
//     minChunks: ({ resource }) => /node_modules/.test(resource),
// }));

// plugins.push(new BrowserSyncPlugin( {
//     proxy: {
//         target: config.proxyURL
//     },
//     files: [
//         '**/*.php'
//     ],
//     cors: true,
//     reloadDelay: 0
// } ));

// Generate a 'manifest' chunk to be inlined in the HTML template
// plugins.push(new webpack.optimize.CommonsChunkPlugin('manifest'));

// Compress extracted CSS. We are using this plugin so that possible
// duplicated CSS from different components can be deduped.
plugins.push(new OptimizeCSSPlugin({
    cssProcessorOptions: {
        safe: true,
        map: {
            inline: false
        }
    }
}));

// Differ settings based on production flag
if ( isProduction() ) {

    plugins.push(new UglifyJsPlugin({
        sourceMap: true,
    }));

    plugins.push(new webpack.DefinePlugin({
        'process.env': env
    }));

    appName = '[name].min.js';
} else {
    appName = '[name].js';
}

plugins.push(new webpack.ProvidePlugin({
    $: 'jquery'
}));

module.exports = {
    entry: entryPoints,
    output: {
        path: exportPath,
        filename: appName
    },

    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            '@': path.resolve('./src/'),
            'frontend': path.resolve('./src/frontend/'),
            'admin': path.resolve('./src/admin/'),
        },
        modules: [
            path.resolve('./node_modules'),
            path.resolve(path.join(__dirname, 'assets/src/')),
        ]
    },
    externals: {
        jquery: 'jQuery'
    },

    plugins,

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                query: {
                    presets: ['es2015']
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    extractCSS: true
                }
            },
            {
                test: /\.less$/,
                use: extractCss.extract({
                    use: [{
                        loader: "css-loader"
                    }, {
                        loader: "less-loader"
                    }]
                })
            },
            {
                test: /\.css$/,
                use: [ 'style-loader', 'css-loader' ]
            }
        ]
    },
}
