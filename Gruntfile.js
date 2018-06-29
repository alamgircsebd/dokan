'use strict';
module.exports = function(grunt) {
    var pkg = grunt.file.readJSON('package.json');
    var packs = grunt.file.readJSON('pack.json');

    var cleanPack = {};
    var compressPack = {};
    var replacePack = {};
    var copyPack = {};

    cleanPack.main = {
        src : ['build/']
    }

    compressPack.main = {
        options: {
            mode: 'zip',
            archive: './build/dokan-pro-v' + pkg.version + '.zip'
        },
        expand: true,
        cwd: 'build/',
        src: ['**/*'],
        dest: 'dokan-pro'
    }

    copyPack.main = {
        src: [
            '**',
            '!node_modules/**',
            '!build/**',
            '!bin/**',
            '!.git/**',
            '!Gruntfile.js',
            '!package.json',
            '!pack.json',
            '!dist/**',
            '!package-lock.json',
            '!debug.log',
            '!phpunit.xml',
            '!config.json',
            '!phpcs.xml.dist',
            '!webpack.config.js',
            '!.gitignore',
            '!.gitmodules',
            '!npm-debug.log',
            '!secret.json',
            '!plugin-deploy.sh',
            '!assets/less/**',
            '!tests/**',
            '!**/Gruntfile.js',
            '!**/package.json',
            '!**/README.md',
            '!**/*~'
        ],
        dest: 'build/'
    }

    Object.keys( packs ).forEach( function( val, index ) {
        var cleanPackages = packs[val].map( function( module ) {
            return "!build/includes/modules/" + module
        });

        cleanPackages.unshift( "build/includes/modules/*" );

        cleanPack[val] = {
            src : cleanPackages
        };

        copyPack[val] = {
            src : './build/dokan-'+ val + '-' + pkg.version + '.zip',
            dest : 'dist/',
            flatten: true,
            expand: true
        }

        compressPack[val] = {
            options: {
                mode: 'zip',
                archive: './build/dokan-'+ val + '-' + pkg.version + '.zip'
            },
            expand: true,
            cwd: 'build/',
            src: ['**/*', '!src/**'],
            dest: 'dokan-pro'
        };

        replacePack[val] = {
            src: [ 'build/dokan-pro.php' ],
            overwrite: true,
            replacements: [
                {
                    from: "Plugin Name: Dokan Pro",
                    to: "Plugin Name: Dokan Pro - " + val
                },

                {
                    from: "private $plan = 'dokan-pro';",
                    to: "private $plan = 'dokan-" + val + "';"
                }
            ]
        };

    });

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            images: 'assets/images',
            js: 'assets/js',
            devLessSrc: 'assets/src/less',
            devJsSrc: 'assets/src/js'
        },

        // Compile all .less files.
        less: {

            // one to one
            core: {
                options: {
                    sourceMap: false,
                    sourceMapFilename: '<%= dirs.css %>/style.css.map',
                    sourceMapURL: 'style.css.map',
                    sourceMapRootpath: '../../'
                },
                files: {
                    '<%= dirs.css %>/style.css': '<%= dirs.devLessSrc %>/style.less'
                }
            },

            admin: {
                files: {
                    '<%= dirs.css %>/admin.css': ['<%= dirs.devLessSrc %>/admin.less']
                }
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: [
                'Gruntfile.js',
                '<%= dirs.js %>/*.js',
                '!<%= dirs.js %>/*.min.js',
            ]
        },

        concat: {
            all_js: {
                files: {
                    '<%= dirs.js %>/dokan-pro.js': [
                        '<%= dirs.devJsSrc %>/*.js',
                        '!<%= dirs.devJsSrc %>/admin.js',
                    ],
                },
            },

            backend_js: {
                files: {
                    '<%= dirs.js %>/dokan-pro-admin.js': [
                        '<%= dirs.devJsSrc %>/admin.js'
                    ]
                }
            }
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    cwd: 'build',
                    exclude: ['build/.*', 'node_modules/*', 'assets/*', 'tests/*', 'bin/*'],
                    mainFile: 'dokan-pro.php',
                    domainPath: '/languages/', // Where to save the POT file.
                    potFilename: 'dokan.pot', // Name of the POT file.
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://wedevs.com/account/tickets/',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>',
                        poedit: true,
                        'x-poedit-keywordslist': true
                    }
                }
            }
        },

        watch: {
            less: {
                files: '<%= dirs.devLessSrc %>/*.less',
                tasks: ['less:core', 'less:admin']
            },

            js: {
                files: '<%= dirs.devJsSrc %>/*.js',
                tasks: [ 'concat:all_js', 'concat:backend_js' ]
            }
        },

        // Clean up build directory
        clean: cleanPack,

        // Copy the plugin into the build directory
        copy: copyPack,

        //Compress build directory into <name>.zip and <name>-<version>.zip
        compress: compressPack,

        replace: replacePack,

        //secret: grunt.file.readJSON('secret.json'),
        sshconfig: {
            "myhost": {
                host: '<%= secret.host %>',
                username: '<%= secret.username %>',
                agent: process.env.SSH_AUTH_SOCK,
                agentForward: true
            }
        },
        sftp: {
            upload: {
                files: {
                    "./": 'build/dokan-pro-v' + pkg.version + '.zip'
                },
                options: {
                    path: '<%= secret.path %>',
                    config: 'myhost',
                    showProgress: true,
                    srcBasePath: "build/"
                }
            }
        },
        sshexec: {
            updateVersion: {
                command: '<%= secret.updateFiles %> ' + pkg.version + ' --allow-root',
                options: {
                    config: 'myhost'
                }
            },

            uptime: {
                command: 'uptime',
                options: {
                    config: 'myhost'
                }
            },
        }
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-contrib-jshint' );
    grunt.loadNpmTasks( 'grunt-wpvue-i18n' );
    grunt.loadNpmTasks( 'grunt-text-replace' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-contrib-clean' );
    grunt.loadNpmTasks( 'grunt-contrib-copy' );
    grunt.loadNpmTasks( 'grunt-contrib-compress' );
    grunt.loadNpmTasks( 'grunt-ssh' );

    grunt.registerTask( 'default', [
        // 'less',
        // 'concat',
    ]);

    grunt.registerTask( 'release', [
        'less',
        'concat',
    ]);

    grunt.registerTask( 'zip', [
        'clean:main',
        'copy:main',
        'clean:starter',
        'compress:main'
    ]);

    Object.keys( packs ).forEach( function( val, index ) {
        grunt.registerTask( 'zip-' + val, [
            'clean:main', 'copy:main', 'replace:' + val, 'clean:' + val, 'makepot', 'compress:' + val, 'copy:' + val
        ]);
    });

    grunt.registerTask( 'deploy', [
        'sftp:upload', 'sshexec:updateVersion'
    ]);
};