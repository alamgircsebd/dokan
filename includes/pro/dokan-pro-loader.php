<?php

/**
 * Dokan Pro Feature Loader
 *
 * Load all pro functionality in this class
 * if pro folder exist then autometically load this class file
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
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
        define( 'DOKAN_PRO_CLASS', dirname( __FILE__).'/classes' );
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
            require_once DOKAN_PRO_ADMIN_DIR . '/ajax.php';
            require_once DOKAN_PRO_ADMIN_DIR . '/announcement.php';
            require_once DOKAN_PRO_CLASS . '/update.php';
        }

        require_once DOKAN_PRO_INC . '/functions.php';
        require_once DOKAN_PRO_INC . '/widgets/store-location.php';
        require_once DOKAN_PRO_INC . '/widgets/store-contact.php';
        require_once DOKAN_PRO_INC . '/widgets/best-seller.php';
        require_once DOKAN_PRO_INC . '/widgets/feature-seller.php';
        require_once DOKAN_PRO_CLASS . '/store-seo.php';

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
            Dokan_Pro_Admin_Ajax::init();
            new Dokan_Pro_Admin_Settings();
            new Dokan_Announcement();
            new Dokan_Update();
        }

        Dokan_Pro_Ajax::init();
        Dokan_Pro_Shipping::init();

        if ( is_user_logged_in() ) {
            Dokan_Pro_Dashboard::init();
            Dokan_Pro_Products::init();
            Dokan_Pro_Coupons::init();
            Dokan_Pro_Reviews::init();
            Dokan_Pro_Reports::init();
            Dokan_Pro_Withdraws::init();
            Dokan_Pro_Settings::init();
            Dokan_Pro_Notice::init();
        }

        Dokan_Pro_Store::init();
    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 2.4
     *
     * @return void [description]
     */
    public function load_actions() {
        add_action( 'woocommerce_after_my_account', array( $this, 'dokan_account_migration_button' ) );
    }

    /**
     * Load all Filters Hook
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_filters() {
        add_filter( 'dokan_query_var_filter', array( $this, 'load_query_var' ), 10 );
        add_filter( 'woocommerce_locate_template', array( $this, 'account_migration_template' ) );
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
            $file_path =  DOKAN_PRO_CLASS . '/' . strtolower( $class_name ) . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }

    /**
     * Load Pro rewirite query vars
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return array
     */
    public function load_query_var( $query_vars ) {
        $query_vars[] = 'coupons';
        $query_vars[] = 'reports';
        $query_vars[] = 'reviews';
        $query_vars[] = 'announcement';
        $query_vars[] = 'single-announcement';
        $query_vars[] = 'account-migration';

        return $query_vars;
    }

    /**
     * Dokan Account Migration Button render
     *
     * @since 2.4
     *
     * @return void
     */
    function dokan_account_migration_button() {
        $user = wp_get_current_user();

        if ( dokan_is_user_customer( $user->ID ) ) {
            dokan_get_template_part( 'global/account-migration-btn', '', array( 'pro' => true ) );
        }
    }

    /**
     * Account migration template on my account
     *
     * @param string  $file path of the template
     *
     * @return string
     */
    function account_migration_template( $file ) {

        if ( get_query_var( 'account-migration' ) && dokan_is_user_customer( get_current_user_id() ) && basename( $file ) == 'my-account.php' ) {
            $file = dokan_locate_template( 'global/update-account.php', '', '', true );
        }

        return $file;
    }


}

new Dokan_Pro_Loader();