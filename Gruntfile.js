'use strict';
module.exports = function(grunt) {

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            less: 'assets/less',
            fonts: 'assets/fonts',
            images: 'assets/images',
            js: 'assets/js'
        },

        // Compile all .less files.
        less: {

            // one to one
            core: {
                src: '<%= dirs.less %>/style.less',
                dest: '<%= dirs.css %>/style.css',
            },
        },

        uglify: {
            minify: {
                expand: true,
                cwd: '<%= dirs.js %>',
                src: [
                    '*.js',
                ],
                dest: '<%= dirs.js %>/',
                ext: '.min.js'
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
            '<%= dirs.js %>/all.js': [
                '<%= dirs.js %>/admin.js',
                '<%= dirs.js %>/mytask.js',
                '<%= dirs.js %>/task.js',
                '<%= dirs.js %>/upload.js',
            ]
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    domainPath: '/languages/', // Where to save the POT file.
                    potFilename: 'my-plugin.pot', // Name of the POT file.
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://github.com/tareq1988/grunt-demo/issues',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                    }
                }
            }
        },

        watch: {
            less: {
                files: ['<%= dirs.less %>/*.less'],
                tasks: ['less:core', 'less:skins'],
                options: {
                    livereload: true
                }
            }
        }
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-contrib-jshint' );
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );

    grunt.registerTask( 'default', [
        'less',
        // 'uglify'
    ]);

    grunt.registerTask('release', [
        'makepot',
        // 'concat',
        // 'uglify'
    ]);
};