'use strict';
module.exports = function(grunt) {
    var pkg = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            images: 'assets/images',
            js: 'assets/js',
            devLessSrc: 'assets/src/less',
            devJsSrc: 'assets/src/js',
            modulesPath: 'modules'
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
                    '<%= dirs.css %>/style.css': '<%= dirs.devLessSrc %>/style.less',
                    '<%= dirs.modulesPath %>/geolocation/assets/css/geolocation.css': '<%= dirs.modulesPath %>/geolocation/assets/less/geolocation.less',
                    '<%= dirs.modulesPath %>/geolocation/assets/css/dokan-geolocation-locations-map.css': '<%= dirs.modulesPath %>/geolocation/assets/less/dokan-geolocation-locations-map.less',
                    '<%= dirs.modulesPath %>/geolocation/assets/css/dokan-geolocation-filters.css': '<%= dirs.modulesPath %>/geolocation/assets/less/dokan-geolocation-filters.less',
                    '<%= dirs.modulesPath %>/follow-store/assets/css/follow-store.css': '<%= dirs.modulesPath %>/follow-store/assets/less/follow-store.less',
                    '<%= dirs.modulesPath %>/single-product-multiple-vendor/assets/css/dokan-spmv-products-admin.css': '<%= dirs.modulesPath %>/single-product-multiple-vendor/assets/less/dokan-spmv-products-admin.less',
                }
            },

            admin: {
                files: {
                    '<%= dirs.css %>/admin.css': ['<%= dirs.devLessSrc %>/admin.less']
                }
            }
        },

        concat: {
            all_js: {
                files: {
                    '<%= dirs.js %>/dokan-pro.js': [
                        '<%= dirs.devJsSrc %>/*.js',
                        '!<%= dirs.devJsSrc %>/admin.js',
                        '!<%= dirs.devJsSrc %>/dokan-blocks-editor-script.js',
                    ],
                    '<%= dirs.js %>/dokan-blocks-editor-script.js': '<%= dirs.devJsSrc %>/dokan-blocks-editor-script.js'
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

        watch: {
            less: {
                files: [
                    '<%= dirs.devLessSrc %>/*.less',
                    '<%= dirs.modulesPath %>/geolocation/assets/less/*.less',
                    '<%= dirs.modulesPath %>/follow-store/assets/less/follow-store.less',
                    '<%= dirs.modulesPath %>/single-product-multiple-vendor/assets/less/dokan-spmv-products-admin.less'
                ],
                tasks: ['less:core', 'less:admin']
            },

            js: {
                files: ['<%= dirs.devJsSrc %>/*.js'],
                tasks: [ 'concat:all_js', 'concat:backend_js' ]
            }
        },
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );

    grunt.registerTask( 'default', [
        'watch',
    ]);

    grunt.registerTask( 'release', [
        'less',
        'concat',
    ]);
};
