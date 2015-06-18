<?php

/**
*  Dokan Pro Feature Loader
*
*  Load all pro functionality in this class
*  if pro folder exist then autometically load this class file
*
*  @since 2.4
*
*  @author weDevs <info@wedevs.com>
*/

class Dokan_Pro_Loader {

    /**
     * Constructor for the Dokan_Pro_Loader class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {

        $this->defined();

        spl_autoload_register( array( $this, 'dokan_pro_autoload' ) );

        $this->includes();

        $this->inistantiate();

        $this->load_actions();

        $this->load_filters();


    }

    public function defined() {
        define( 'DOKAN_PRO_DIR', dirname( __FILE__) );
        define( 'DOKAN_PRO_ADMIN_DIR', dirname( __FILE__).'/admin' );
        define( 'DOKAN_PRO_INC', dirname( __FILE__).'/includes' );
    }

    /**
     * Load all includes file for pro
     *
     * @since 2.4
     *
     * @return void
     */
    public function includes() {

        if ( is_admin() ) {
            require_once DOKAN_PRO_ADMIN_DIR . '/admin.php';
            require_once DOKAN_PRO_ADMIN_DIR . '/announcement.php';
        }

        require_once DOKAN_PRO_INC . '/functions.php';
    }

    /**
     * Inistantiate all classes
     *
     * @since 2.4
     *
     * @return void
     */
    public function inistantiate() {

        if ( is_admin() ) {
            new Dokan_Pro_Admin_Settings();
            new Dokan_Announcement();
        }

        new Dokan_Pro_Dashboard();
        new Dokan_Pro_Coupons();
        new Dokan_Pro_Reviews();
        new Dokan_Pro_Withdraws();

    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 2.4
     *
     * @return void [description]
     */
    public function load_actions() {

    }

    /**
     * Load all Filters Hook
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_filters() {

    }

    /**
     * Required all class files inside Pro
     *
     * @since 2.4
     *
     * @param  string $class
     *
     * @return void
     */
    public function dokan_pro_autoload( $class ) {
        if ( stripos( $class, 'Dokan_Pro_' ) !== false ) {
            $class_name = str_replace( array( 'Dokan_Pro_', '_' ), array( '', '-' ), $class );
            $file_path =  DOKAN_PRO_DIR . '/classes/' . strtolower( $class_name ) . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }

}

new Dokan_Pro_Loader();