const path = require( 'path' );
const TerserJSPlugin = require( 'terser-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const VueLoaderPlugin = require( 'vue-loader/lib/plugin' );

const entryPoints = {};

const rootEntryPoints = {
    'dokan-pro': './assets/src/js/dokan-pro.js',
    'dokan-pro-admin': './assets/src/js/dokan-pro-admin.js',
    'dokan-blocks-editor-script': './assets/src/js/dokan-blocks-editor-script.js',
    'dokan-tinymce-button': './assets/src/js/dokan-tinymce-button.js',
    'dokan-single-product-shipping': './assets/src/js/dokan-single-product-shipping.js',
    'vue-pro-frontend-shipping': './src/frontend/shipping.js',
    'vue-pro-admin': './src/admin/main.js',
};

const moduleEntryPoints = {
    'follow-store': {
        'follow-store': 'follow-store.js',
    },

    geolocation: {
        geolocation: 'geolocation.js',
        'dokan-geolocation-locations-map': 'locations-map.js',
        'dokan-geolocation-locations-map-google-maps': 'locations-map-google-maps.js',
        'dokan-geolocation-locations-map-mapbox': 'locations-map-mapbox.js',
        'dokan-geolocation-filters': 'filters.js',
        'dokan-geolocation-store-lists-filters': 'store-lists-filters.js',
        'geolocation-vendor-dashboard-product-google-maps': 'vendor-dashboard-product-google-maps.js',
        'geolocation-vendor-dashboard-product-mapbox': 'vendor-dashboard-product-mapbox.js',
    },

    'report-abuse': {
        'dokan-report-abuse': 'frontend/main.js',
        'dokan-report-abuse-admin': 'admin/main.js',
        'dokan-report-abuse-admin-single-product': 'admin/single-product.js',
    },

    'single-product-multiple-vendor': {
        'dokan-spmv-products-admin': 'dokan-spmv-products-admin.js',
    },

    'store-reviews': {
        admin: 'admin/main.js',
        script: 'script.js',
        style: 'style.js',
    },

    subscription: {
        style: 'style.js',
        script: 'script.js',
        'admin-script': 'admin-script.js',
        subscription: 'admin/main.js',
    },

    wholesale: {
        admin: 'admin/main.js',
        scripts: 'scripts.js',
    },
};

Object.keys( rootEntryPoints ).forEach( function( output ) {
    entryPoints[ output ] = rootEntryPoints[ output ];
} );

Object.keys( moduleEntryPoints ).forEach( function( dokanModule ) {
    const modulePath = `modules/${ dokanModule }`;

    Object.keys( moduleEntryPoints[ dokanModule ] ).forEach( function(
        moduleOutput
    ) {
        entryPoints[
            `../../${ modulePath }/assets/js/${ moduleOutput }`
        ] = `./${ modulePath }/assets/src/js/${ moduleEntryPoints[ dokanModule ][ moduleOutput ] }`;
    } );
} );

const plugins = [
    new MiniCssExtractPlugin( {
        moduleFilename: ( { name } ) => {
            if ( name.match( /\/modules\// ) ) {
                return process.env.NODE_ENV === 'production' ? `${ name.replace( '/js/', '/css/' ) }.min.css` : `${ name.replace( '/js/', '/css/' ) }.css`;
                //return `${ name.replace( '/js/', '/css/' ) }.css`;
            }
            return process.env.NODE_ENV === 'production' ? '../css/[name].min.css' : '../css/[name].css';
        },
    } ),

    new VueLoaderPlugin(),
];

module.exports = {
    mode: process.env.NODE_ENV,
    entry: entryPoints,
    output: {
        path: path.resolve( __dirname, './assets/js' ),
        filename: process.env.NODE_ENV === 'production' ? '[name].min.js' : '[name].js'
        //filename: '[name].js',
    },

    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm.js',
            '@': path.resolve( './src/' ),
            frontend: path.resolve( './src/frontend/' ),
            admin: path.resolve( './src/admin/' ),
        },
    },

    externals: {
        jquery: 'jQuery',
        $: 'jquery',
    },

    optimization: {
        minimizer: [ new TerserJSPlugin(), new OptimizeCSSAssetsPlugin() ],
    },

    plugins,

    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
            {
                test: /\.less$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'less-loader',
                ],
            },
            {
                test: /\.css$/,
                use: [ MiniCssExtractPlugin.loader, 'css-loader' ],
            },
            {
                test: /\.(png|jpe?g|gif)$/i,
                loader: 'file-loader',
                options: {
                    name: '../images/dist/[name].[ext]',
                },
            },
        ],
    },
};
