const path = require( 'path' );
const rimraf = require( 'rimraf' );
const chalk = require( 'chalk' );

const deleteDirs = [
    'assets/css',
    'assets/js',
    'modules/follow-store/assets/css',
    'modules/follow-store/assets/js',
    'modules/geolocation/assets/css',
    'modules/geolocation/assets/js',
    'modules/report-abuse/assets/css',
    'modules/report-abuse/assets/js',
    'modules/single-product-multiple-vendor/assets/css',
    'modules/single-product-multiple-vendor/assets/js',
    'modules/store-reviews/assets/css',
    'modules/store-reviews/assets/js',
    'modules/subscription/assets/css',
    'modules/subscription/assets/js',
    'modules/wholesale/assets/css',
    'modules/wholesale/assets/js',
];

deleteDirs.forEach( ( dir ) => {
    rimraf( path.resolve( dir ), ( error ) => {
        if ( error ) {
            console.log( error );
        } else {
            console.log( chalk.green( `Deleted directory ${dir}.` ) );
        }
    } );
} );
