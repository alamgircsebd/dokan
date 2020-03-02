const fs = require( 'fs-extra' );
const path = require( 'path' );
const { exec } = require( 'child_process' );
const util = require( 'util' );
const replace = require( 'replace-in-file' );
const chalk = require( 'chalk' );

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
];

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

        asyncExec( 'composer install -vvv && composer dump -o', {
            cwd: dest,
        }, () => {
            console.log( `Installed composer packages in ${dest} directory.` );

            removeFiles.forEach( ( file ) => {
                fs.removeSync( `${dest}/${file}` );
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
            }, (error) => {
                fs.removeSync( planDir );
                console.log( chalk.green( `${zipFile} is ready.` ) );
            } );
        } );
    } ) );
} );

