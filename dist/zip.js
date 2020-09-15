const fs = require( 'fs-extra' );
const path = require( 'path' );
const { exec } = require( 'child_process' );
const util = require( 'util' );
const replace = require( 'replace-in-file' );
const chalk = require( 'chalk' );
const _ = require( 'lodash' );

const asyncExec = util.promisify( exec );

const pluginFiles = [
    'assets/',
    'includes/',
    'languages/',
    'templates/',
    'changelog.txt',
    'dokan-pro.php',
    'readme.txt',
    'composer.json',
    'composer.lock',
];

const removeFiles = [
    'assets/src',
    'composer.json',
    'composer.lock',
    'modules/follow-store/assets/src',
    'modules/geolocation/assets/src',
    'modules/report-abuse/assets/src',
    'modules/single-product-multiple-vendor/assets/src',
    'modules/store-reviews/assets/src',
    'modules/subscription/assets/src',
    'modules/wholesale/assets/src',
];

const allowedVendorFiles = {
    'hybridauth/hybridauth': [
        'src'
    ],
    'moip/moip-sdk-php': [
        'src'
    ],
    'paypal/adaptivepayments-sdk-php': [
        'lib'
    ],
    'paypal/sdk-core-php': [
        'lib'
    ],
    'rmccue/requests': [
        'library'
    ],
    'stripe/stripe-php': [
        'data', 'lib', 'init.php'
    ],
    'wikimedia/composer-merge-plugin': [],
}

const { version } = JSON.parse( fs.readFileSync( 'package.json' ) );
const dokanPlans = JSON.parse( fs.readFileSync( 'plans.json' ) );

fs.removeSync( 'dist/*.zip' );

exec( 'rm -rf plans && rm *.zip', {
    cwd: 'dist',
}, () => {
    Object.keys( dokanPlans ).forEach( util.promisify( ( plan ) => {
        const planDir = `dist/${plan}`;
        const dest = `${planDir}/dokan-pro`;

        fs.removeSync( planDir );

        const fileList = [ ...pluginFiles ];

        dokanPlans[ plan ].forEach( ( dokanModule ) => {
            fileList.push( `modules/${dokanModule}` )
        } );

        fs.mkdirp( dest );

        fileList.forEach( ( file ) => {
            fs.copySync( file, `${dest}/${file}` );
        } );

        console.log( `Finished copying ${plan} files.` );

        asyncExec( 'composer install --optimize-autoloader --no-dev', {
            cwd: dest,
        }, () => {
            console.log( `Installed composer packages in ${dest} directory.` );

            removeFiles.forEach( ( file ) => {
                fs.removeSync( `${dest}/${file}` );
            } );

            Object.keys( allowedVendorFiles ).forEach( ( composerPackage ) => {
                const packagePath = path.resolve( `${dest}/vendor/${composerPackage}` );

                if ( !fs.existsSync( packagePath ) ) {
                    return;
                }

                const list = fs.readdirSync( packagePath );
                const deletables = _.difference( list, allowedVendorFiles[ composerPackage ] );

                deletables.forEach( ( deletable ) => {
                    fs.removeSync( path.resolve( packagePath, deletable ) )
                } );
            } );

            replace( {
                files: `${dest}/dokan-pro.php`,
                from: `private $plan = 'dokan-pro';`,
                to: `private $plan = 'dokan-${plan}';`,
            } );

            const zipFile = `dokan-${plan}-${version}.zip`;

            console.log( `Making zip file ${zipFile}...` );

            asyncExec( `zip ../${zipFile} dokan-pro -rq`, {
                cwd: planDir,
            }, () => {
                fs.removeSync( planDir );
                console.log( chalk.green( `${zipFile} is ready.` ) );
            } ).catch( ( error ) => {
                console.log( chalk.red( `Could not make ${zipFile}.` ) );
                console.log( error );
            } );
        } ).catch( ( error ) => {
            console.log( chalk.red( `Could not install composer in ${dest} directory.` ) );
            console.log( error );
        } );
    } ) );
} );
