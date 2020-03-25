const fs = require( 'fs-extra' );
const path = require( 'path' );
const request = require( 'request' );
const chalk = require( 'chalk' );

function logError( log ) {
    console.log( chalk.red( log ) );
}

if ( ! fs.pathExistsSync( 'env.json' ) ) {
    logError( 'env.json file does not exists!' );
    return;
}

try {
    env = JSON.parse( fs.readFileSync( 'env.json' ) );
} catch( error ) {
    logError( 'Could not read env.json' );
    console.log( error );
    return;
}

if ( ! env.bitbucket || ! env.bitbucket.access_token ) {
    logError( 'BitBucket access_token not found in your env.json.' );
    return;
}

if ( ! env.bitbucket.repo ) {
    logError( 'BitBucket repo is missing env.json.' );
    return;
}

const { repo, access_token } = env.bitbucket;

const { version } = JSON.parse( fs.readFileSync( 'package.json' ) );
const dokanPlans = Object.keys( JSON.parse( fs.readFileSync( 'plans.json' ) ) );

dokanPlans.forEach( ( plan ) => {
    const filename = `dokan-${plan}-${version}.zip`;
    const filepath = path.resolve( `dist/${filename}` );

    const options = {
        'method': 'POST',
        'url': `https://api.bitbucket.org/2.0/repositories/${ repo }/downloads`,
        'headers': {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Authorization': `Bearer ${ access_token }`
        },
        formData: {
            'files': {
                'value': fs.createReadStream( filepath ),
                'options': {
                    'filename': filename,
                    'contentType': null
                }
            },
        }
    };

    console.log( `Uploading ${ filename }...` );

    request( options, ( error, response, body ) => {
        if ( error ) {
            logError( `Error uploading zip ${ filename }` );
            console.log( error );
            return;
        }

        if ( response.statusCode !== 201 ) {
            logError( `Error uploading zip ${ filename }` );
            logError( `Response StatusCode: ${ response.statusCode }, StatusMessage: ${ response.statusMessage }` );
            return;
        }

        console.log( chalk.green( `${filename} has been uploaded to ${ repo }` ) );
    } );
} );
