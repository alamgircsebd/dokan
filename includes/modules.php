<?php

/**
 * Parses module file and retrieves module metadata
 *
 * @param  string $module_file Path to module file
 *
 * @return array
 */
function dokan_pro_get_module_data( $module_file ) {
    $default_headers = array(
        'name'        => 'Plugin Name',
        'description' => 'Description',
        'plugin_uri'  => 'Plugin URI',
        'thumbnail'   => 'Thumbnail Name',
        'class'       => 'Integration Class',
        'author'      => 'Author',
        'author_uri'  => 'Author URI',
        'version'     => 'Version',
    );

    $module_data = get_file_data( $module_file, $default_headers, 'dokan_pro_modules' );

    return $module_data;
}

/**
 * Gets all the available modules
 *
 * @return array
 */
function dokan_pro_get_modules() {
    $module_root  = dirname( __FILE__) . '/modules';
    $modules_dir  = @opendir( $module_root);
    $modules      = array();
    $module_files = array();

    if ( $modules_dir ) {

        while ( ( $file = readdir( $modules_dir ) ) !== false ) {

            if ( substr( $file, 0, 1 ) == '.' ) {
                continue;
            }

            if ( is_dir( $module_root . '/' . $file ) ) {
                $plugins_subdir = @opendir( $module_root . '/' . $file );

                if ( $plugins_subdir ) {

                    while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
                        if ( substr( $subfile, 0, 1 ) == '.' ) {
                            continue;
                        }

                        if ( substr($subfile, -4) == '.php' ) {
                            $module_files[] = "$file/$subfile";
                        }
                    }

                    closedir( $plugins_subdir );
                }
            }
        }

        closedir( $modules_dir );
    }

    if ( $module_files ) {

        foreach ( $module_files as $module_file ) {

            if ( ! is_readable( "$module_root/$module_file" ) ) {
                continue;
            }

            $module_data = dokan_pro_get_module_data( "$module_root/$module_file" );

            if ( empty ( $module_data['name'] ) ) {
                continue;
            }

            $file_base = wp_normalize_path( $module_file );


            $modules[ $file_base ] = $module_data;
        }
    }

    return $modules;
}

/**
 * Get a single module data
 *
 * @param  string $module
 *
 * @return WP_Error|Array
 */
function dokan_pro_get_module( $module ) {
    $module_root  = dirname( __FILE__) . '/modules';

    $module_data = dokan_pro_get_module_data( "$module_root/$module" );

    if ( empty ( $module_data['name'] ) ) {
        return new WP_Error( 'not-valid-plugin', __( 'This is not a valid plugin', 'dokan' ) );
    }

    return $module_data;
}

/**
 * Get the meta key to store the active module list
 *
 * @return string
 */
function dokan_pro_active_module_key() {
    return 'dokan_pro_active_modules';
}

/**
 * Get active modules
 *
 * @return array
 */
function dokan_pro_get_active_modules() {
    return get_option( dokan_pro_active_module_key(), array() );
}

/**
 * Check if a module is active
 *
 * @param  string $module basename
 *
 * @return boolean
 */
function dokan_pro_is_module_active( $module ) {
    return in_array( $module, dokan_pro_get_active_modules() );
}

/**
 * Check if a module is inactive
 *
 * @param  string $module basename
 *
 * @return boolean
 */
function dokan_pro_is_module_inactive( $module ) {
    return ! dokan_pro_is_module_active( $module );
}

/**
 * Activate a module
 *
 * @param  string $module basename of the module file
 *
 * @return WP_Error|null WP_Error on invalid file or null on success.
 */
function dokan_pro_activate_module( $module ) {
    $current = dokan_pro_get_active_modules();

    $module_root = dirname( __FILE__) . '/modules';
    $module_data = dokan_pro_get_module_data( "$module_root/$module" );

    if ( empty ( $module_data['name'] ) ) {
        return new WP_Error( 'invalid-module', __( 'The module is invalid', 'dokan' ) );
    }

    // activate if enactive
    if ( dokan_pro_is_module_inactive( $module ) ) {
        $current[] = $module;
        sort($current);

        update_option( dokan_pro_active_module_key(), $current);
    }

    return null;
}

/**
 * Deactivate a module
 *
 * @param  string $module basename of the module file
 *
 * @return boolean
 */
function dokan_pro_deactivate_module( $module ) {
    $current = dokan_pro_get_active_modules();

    if ( dokan_pro_is_module_active( $module ) ) {

        $key = array_search( $module, $current );

        if ( false !== $key ) {
            unset( $current[ $key ] );

            sort($current);
        }

        update_option( dokan_pro_active_module_key(), $current );

        return true;
    }

    return false;
}
