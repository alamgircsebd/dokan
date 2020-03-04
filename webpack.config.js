const webpack = require('webpack');
const path = require('path');
const package = require('./package.json');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const OptimizeCSSPlugin = require('optimize-css-assets-webpack-plugin');

// Naming and path settings
var appName = 'app';

var exportPath = path.resolve(__dirname, './assets/js');

var entryPoints = {};

var rootEntryPoints = {
    'dokan-pro': './assets/src/js/dokan-pro.js',
    'dokan-pro-admin': './assets/src/js/dokan-pro-admin.js',
    'dokan-blocks-editor-script': './assets/src/js/dokan-blocks-editor-script.js',
    'dokan-tinymce-button': './assets/src/js/dokan-tinymce-button.js',
    'vue-pro-frontend-shipping': './src/frontend/shipping.js',
    'vue-pro-admin': './src/admin/main.js',
};

var moduleEntryPoints = {
    'geolocation': {
        'geolocation': 'geolocation.js',
        'dokan-geolocation-locations-map': 'locations-map.js',
        'dokan-geolocation-locations-map-google-maps': 'locations-map-google-maps.js',
        'dokan-geolocation-locations-map-mapbox': 'locations-map-mapbox.js',
        'dokan-geolocation-filters': 'filters.js',
        'dokan-geolocation-store-lists-filters': 'store-lists-filters.js',
        'geolocation-vendor-dashboard-product-google-maps': 'vendor-dashboard-product-google-maps.js',
        'geolocation-vendor-dashboard-product-mapbox': 'vendor-dashboard-product-mapbox.js',
    },

    'follow-store': {
        'follow-store': 'follow-store.js',
    },

    'subscription': {
        'style': 'style.js',
        'script': 'script.js',
        'admin-script': 'admin-script.js',
        'subscription': 'admin/main.js',
    },

    'store-reviews': {
        'admin': 'admin/main.js',
        'script': 'script.js',
        'style': 'style.js',
    },

    'wholesale': {
        'admin': 'admin/main.js',
        'scripts': 'scripts.js'
    },

    'report-abuse': {
        'dokan-report-abuse': 'frontend/main.js',
        'dokan-report-abuse-admin': 'admin/main.js',
        'dokan-report-abuse-admin-single-product': 'admin/single-product.js'
    },

    'single-product-multiple-vendor': {
        'dokan-spmv-products-admin': 'dokan-spmv-products-admin.js',
    }
};

Object.keys(rootEntryPoints).forEach(function (output) {
    entryPoints[ output ] = rootEntryPoints[output];
});

Object.keys(moduleEntryPoints).forEach(function (dokanModule) {
    var modulePath = `modules/${dokanModule}`;

    Object.keys(moduleEntryPoints[dokanModule]).forEach(function (moduleOutput) {
        entryPoints[ `../../${modulePath}/assets/js/${moduleOutput}` ] = `./${modulePath}/assets/src/js/${moduleEntryPoints[dokanModule][moduleOutput]}`;
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
                test: /\.(png|jpe?g|gif)$/i,
                loader: 'file-loader',
                options: {
                    name: '../images/dist/[name].[ext]',
                },
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
