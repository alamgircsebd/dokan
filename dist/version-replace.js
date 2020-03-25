const fs = require( 'fs-extra' );
const replace = require( 'replace-in-file' );

const pluginFiles = [
    'assets/**/*',
    'includes/**/*',
    'templates/**/*',
    'modules/**/*',
    'dokan-pro.php',
];

const { version } = JSON.parse( fs.readFileSync( 'package.json' ) );

replace( {
    files: pluginFiles,
    from: /DOKAN_PRO_SINCE/g,
    to: version,
} );
