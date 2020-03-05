const wpvuei18n = require( 'wp-vue-i18n' );

wpvuei18n.makepot( {
    exclude: [
        'assets/*',
        'build/*',
        'node_modules/*',
        'modules/.*/assets/js',
    ],
    mainFile: 'dokan-pro.php',
    domainPath: '/languages/',
    potFile: 'dokan.pot',
    type: 'wp-plugin',
    potHeaders: {
        'report-msgid-bugs-to': 'https://wedevs.com/account/tickets/',
        'language-team': 'LANGUAGE <EMAIL@ADDRESS>',
        'poedit': true,
        'x-poedit-keywordslist': true
    }
} );
