<?php
/**
  Plugin Name: Dokan Pro
  Plugin URI: https://wedevs.com/dokan/
  Description: An e-commerce marketplace plugin for WordPress. Powered by WooCommerce and weDevs.
  Version: 2.9.19
  Author: weDevs
  Author URI: https://wedevs.com/
  WC requires at least: 3.0
  WC tested up to: 3.9.1
  License: GPL2
  TextDomain: dokan
 */

/**
 * Dokan Pro Feature Loader
 *
 * Load all pro functionality in this class
 * if pro folder exist then automatically load this class file
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
 */
class Dokan_Pro {
    /**
     * Plan type
     *
     * @var string
     */
    private $plan = 'dokan-pro';

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '2.9.19';

    /**
     * Databse version key
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    private $db_version_key = 'dokan_pro_version';

    /**
     * Holds various class instances
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    private $container = [];

    /**
     * Initializes the WeDevs_Dokan() class
     *
     * Checks for an existing WeDevs_WeDevs_Dokan() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Pro();
        }

        return $instance;
    }

    /**
     * Constructor for the Dokan_Pro class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {
        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        add_action( 'plugins_loaded', [ $this, 'check_dokan_lite_exist' ] );
        add_action( 'dokan_loaded', [ $this, 'init_plugin' ] );

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        trigger_error( sprintf( 'Undefined property: %s', self::class . '::$' . $prop ) );
    }

    public function __isset( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return true;
        }

        return false;
    }

    /**
     * Define all pro module constant
     *
     * @since  2.6
     *
     * @return void
     */
    public function define_constants() {
        define( 'DOKAN_PRO_PLUGIN_VERSION', $this->version );
        define( 'DOKAN_PRO_FILE', __FILE__ );
        define( 'DOKAN_PRO_DIR', dirname( DOKAN_PRO_FILE ) );
        define( 'DOKAN_PRO_TEMPLATE_DIR', DOKAN_PRO_DIR . '/templates' );
        define( 'DOKAN_PRO_INC', DOKAN_PRO_DIR . '/includes' );
        define( 'DOKAN_PRO_ADMIN_DIR', DOKAN_PRO_INC . '/Admin' );
        define( 'DOKAN_PRO_CLASS', DOKAN_PRO_DIR . '/classes' );
        define( 'DOKAN_PRO_PLUGIN_ASSEST', plugins_url( 'assets', DOKAN_PRO_FILE ) );
        define( 'DOKAN_PRO_MODULE_DIR', DOKAN_PRO_DIR . '/modules' );
        define( 'DOKAN_PRO_MODULE_URL', plugins_url( 'modules', DOKAN_PRO_FILE ) );
    }

    /**
     * Get Dokan db version key
     *
     * @since DOKAN_LITE_SINCE
     *
     * @return string
     */
    public function get_db_version_key() {
        return $this->db_version_key;
    }

    /**
     * Placeholder for activation function
     */
    public function activate() {
        $installer = new \WeDevs\DokanPro\Install\Installer();
        $installer->do_install();
    }

    /**
     * Check is dokan lite active or not
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function check_dokan_lite_exist() {
        if ( ! class_exists( 'WeDevs_Dokan' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', array( $this, 'activation_notice' ) );
            add_action( 'wp_ajax_dokan_pro_install_dokan_lite', array( $this, 'install_dokan_lite' ) );
        }
    }

    /**
     * Load all things
     *
     * @since 2.7.3
     *
     * @return void
     */
    public function init_plugin() {
        spl_autoload_register( array( $this, 'dokan_pro_autoload' ) );

        $this->includes();
        $this->load_actions();
        $this->load_filters();

        $modules = new \WeDevs\DokanPro\Module();

        $modules->load_active_modules();

        $this->container['module'] = $modules;
    }

    /**
     * Dokan main plugin activation notice
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function activation_notice() {
        $plugin_file      = basename( dirname( __FILE__ ) ) . '/dokan-pro.php';
        $core_plugin_file = 'dokan-lite/dokan.php';

        include_once DOKAN_PRO_TEMPLATE_DIR . '/dokan-lite-activation-notice.php';
    }

    /**
     * Install dokan lite
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function install_dokan_lite() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'dokan-pro-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'dokan-lite';
        $api    = plugins_api( 'plugin_information', [ 'slug' => $plugin, 'fields' => [ 'sections' => false ] ] );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        activate_plugin( 'dokan-lite/dokan.php' );

        wp_send_json_success();
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
            // require_once DOKAN_PRO_ADMIN_DIR . '/shortcode-button.php';
        }

        require_once DOKAN_PRO_INC . '/functions.php';
        require_once DOKAN_PRO_INC . '/function-orders.php';
        require_once DOKAN_PRO_INC . '/functions-reports.php';
        require_once DOKAN_PRO_INC . '/functions-wc.php';
    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_actions() {
         // init the classes
        add_action( 'init', [ $this, 'localization_setup' ] );

        add_action( 'init', [ $this, 'init_classes' ], 10 );
        add_action( 'init', [ $this, 'register_scripts' ], 10 );

        add_action( 'woocommerce_after_my_account', [ $this, 'dokan_account_migration_button' ] );

        add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
        add_action( 'dokan_enqueue_admin_scripts', [ $this, 'admin_enqueue_scripts' ] );
        add_action( 'dokan_enqueue_admin_dashboard_script', [ $this, 'admin_dashboad_enqueue_scripts' ] );

        if ( function_exists( 'register_block_type' ) ) {
            new \WeDevs\DokanPro\BlockEditorBlockTypes();
        }
    }

    /**
     * Load all Filters Hook
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_filters() {
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
        add_filter( 'dokan_is_pro_exists', [ $this, 'set_as_pro' ], 99 );
        add_filter( 'dokan_query_var_filter', [ $this, 'load_query_var' ], 10 );
        add_filter( 'woocommerce_locate_template', [ $this, 'account_migration_template' ] );
        add_filter( 'woocommerce_locate_template', [ $this, 'dokan_registration_template' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_pro_templates' ], 10, 3 );
        add_filter( 'dokan_widgets', [ $this, 'register_widgets' ] );

        //Dokan Email filters for WC Email
        add_filter( 'woocommerce_email_classes', [ $this, 'load_dokan_emails' ], 36 );
        add_filter( 'dokan_email_list', [ $this, 'set_email_template_directory' ], 15 );
        add_filter( 'dokan_email_actions', [ $this, 'register_email_actions' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dokan', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Instantiate all classes
     *
     * @since 2.4
     *
     * @return void
     */
    public function init_classes() {
        new WeDevs\DokanPro\Refund\Hooks();
        new WeDevs\DokanPro\Brands\Hooks();
        new \WeDevs\DokanPro\Shipping\Hooks();

        new \WeDevs\DokanPro\StoreCategory();

        if ( is_admin() ) {
            new \WeDevs\DokanPro\Admin\Admin();
            new \WeDevs\DokanPro\Admin\Pointers();
            new \WeDevs\DokanPro\Admin\Ajax();
            new \WeDevs\DokanPro\Admin\Promotion();
            new \WeDevs\DokanPro\Admin\ShortcodesButton();
        }

        new \WeDevs\DokanPro\Admin\Announcement();
        new \WeDevs\DokanPro\Update( $this->get_plan() );
        new \WeDevs\DokanPro\EmailVerification();
        new \WeDevs\DokanPro\SocialLogin();

        $this->container['store']       = new \WeDevs\DokanPro\Store();
        $this->container['store_seo']   = new \WeDevs\DokanPro\StoreSeo();
        $this->container['store_share'] = new \WeDevs\DokanPro\StoreShare();
        $this->container['products']    = new \WeDevs\DokanPro\Products();
        $this->container['review']      = new \WeDevs\DokanPro\Review();
        $this->container['notice']      = new \WeDevs\DokanPro\Notice();
        $this->container['refund']      = new \WeDevs\DokanPro\Refund\Manager();
        $this->container['brands']      = new \WeDevs\DokanPro\Brands\Manager();

        $this->container = apply_filters( 'dokan_pro_get_class_container', $this->container );

        if ( is_user_logged_in() ) {
            new \WeDevs\DokanPro\Dashboard();
            new \WeDevs\DokanPro\Coupons();
            new WeDevs\DokanPro\Reports();
            new WeDevs\DokanPro\Withdraws();
            new WeDevs\DokanPro\Settings();
        }

        new \WeDevs\DokanPro\Assets();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\DokanPro\Ajax();
        }
    }

    /**
     * Register all scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function register_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register all js
        wp_register_script( 'serializejson', WC()->plugin_url() . '/assets/js/jquery-serializejson/jquery.serializejson' . $suffix . '.js', [ 'jquery' ], '2.6.1' );
        wp_register_script( 'dokan-product-shipping', plugins_url( 'assets/js/single-product-shipping.js', __FILE__ ), false, null, true );
    }

    /**
     * Register widgets
     *
     * @since 2.8
     *
     * @return void
     */
    public function register_widgets( $widgets ) {
        $widgets['best_seller'] = \WeDevs\DokanPro\Widgets\BestSeller::class;
        $widgets['feature_seller'] = \WeDevs\DokanPro\Widgets\FeatureSeller::class;

        return $widgets;
    }

    /**
     * Dokan Account Migration Button render
     *
     * @since 2.4
     *
     * @return void
     */
    public function dokan_account_migration_button() {
        $user = wp_get_current_user();

        if ( dokan_is_user_customer( $user->ID ) ) {
            dokan_get_template_part( 'global/account-migration-btn', '', [ 'pro' => true ] );
        }
    }

    /**
     * Enqueue scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function enqueue_scripts() {
        if (
            ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) )
            || dokan_is_store_page()
            || dokan_is_store_review_page()
            || is_account_page()
            || dokan_is_store_listing()
            || apply_filters( 'dokan_forced_load_scripts', false )
            ) {
            // wp_enqueue_style( 'dokan-pro-style' );
            wp_enqueue_style( 'dokan-pro-style', DOKAN_PRO_PLUGIN_ASSEST . '/css/style.css', false, time(), 'all' );

            // Load accounting scripts
            wp_enqueue_script( 'serializejson' );
            wp_enqueue_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', [ 'jquery' ], null, true );

            //localize script for refund and dashboard image options
            $dokan_refund = dokan_get_refund_localize_data();
            wp_localize_script( 'dokan-script', 'dokan_refund', $dokan_refund );
            wp_enqueue_script( 'dokan-pro-script', DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-pro.js', [ 'jquery', 'dokan-script' ], DOKAN_PRO_PLUGIN_VERSION, true );
        }

        // Load in Single product pages only
        if ( is_singular( 'product' ) && !get_query_var( 'edit' ) ) {
            wp_enqueue_script( 'dokan-product-shipping' );
        }

        if ( get_query_var( 'account-migration' ) ) {
            wp_enqueue_script( 'dokan-vendor-registration' );
        }
    }


    /**
     * Admin scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function admin_enqueue_scripts() {
        wp_enqueue_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', [ 'jquery' ], null, true );
        wp_enqueue_script( 'dokan_pro_admin', DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-pro-admin.js', [ 'jquery', 'jquery-blockui' ] );

        $dokan_refund = dokan_get_refund_localize_data();
        $dokan_admin  = apply_filters( 'dokan_admin_localize_param', [
            'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
            'nonce'                   => wp_create_nonce( 'dokan-admin-nonce' ),
            'activating'              => __( 'Activating', 'dokan' ),
            'deactivating'            => __( 'Deactivating', 'dokan' ),
            'combine_commission_desc' => __( 'Amount you will get from sales in both percentage and fixed fee', 'dokan' ),
            'default_commission_desc' => __( 'It will override the default commission admin gets from each sales', 'dokan' ),
        ] );

        wp_localize_script( 'dokan_slider_admin', 'dokan_refund', $dokan_refund );
        wp_localize_script( 'dokan_pro_admin', 'dokan_admin', $dokan_admin );
    }

    /**
     * Load admin dashboard scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function admin_dashboad_enqueue_scripts() {
        wp_enqueue_style( 'dokan-pro-admin-dash', DOKAN_PRO_PLUGIN_ASSEST . '/css/admin.css' );
    }

    /**
     * Initialize pro rest api class
     *
     * @param array $class_map
     *
     * @return array
     */
    public function rest_api_class_map( $class_map ) {
        return \WeDevs\DokanPro\REST\Manager::register_rest_routes( $class_map );
    }

    /**
     * Set plugin in pro mode
     *
     * @since 2.6
     *
     * @param boolean $is_pro
     *
     * @return boolean
     */
    public function set_as_pro( $is_pro ) {
        return true;
    }

    /**
     * Load Pro rewrite query vars
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
        $query_vars[] = 'dokan-registration';

        return $query_vars;
    }

    /**
     * Account migration template on my account
     *
     * @param string  $file path of the template
     *
     * @return string
     */
    public function account_migration_template( $file ) {
        if ( get_query_var( 'account-migration' ) && dokan_is_user_customer( get_current_user_id() ) && basename( $file ) == 'my-account.php' ) {
            $file = dokan_locate_template( 'global/update-account.php', '', DOKAN_PRO_DIR . '/templates/', true );
        }

        return $file;
    }

    /**
     *
     * @param type $file
     * @return type
     */
    public function dokan_registration_template( $file ) {
        if ( get_query_var( 'dokan-registration' ) && dokan_is_user_customer( get_current_user_id() ) && basename( $file ) == 'my-account.php' ) {
            $file = dokan_locate_template( 'global/dokan-registration.php', '', DOKAN_PRO_DIR . '/templates/', true );
        }
        return $file;
    }

    /**
     * Load dokan pro templates
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function load_pro_templates( $template_path, $template, $args ) {
        if ( isset( $args['pro'] ) && $args['pro'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Add Dokan Email classes in WC Email
     *
     * @since 2.6.6
     *
     * @param array $wc_emails
     *
     * @return $wc_emails
     */
    public function load_dokan_emails( $wc_emails ) {
        $wc_emails['Dokan_Email_Announcement']    = new \WeDevs\DokanPro\Emails\Announcement();
        $wc_emails['Dokan_Email_Updated_Product'] = new \WeDevs\DokanPro\Emails\UpdatedProduct();
        $wc_emails['Dokan_Email_Refund_Request']  = new \WeDevs\DokanPro\Emails\RefundRequest();
        $wc_emails['Dokan_Email_Refund_Vendor']   = new \WeDevs\DokanPro\Emails\RefundVendor();
        $wc_emails['Dokan_Email_Vendor_Enable']   = new \WeDevs\DokanPro\Emails\VendorEnable();
        $wc_emails['Dokan_Email_Vendor_Disable']  = new \WeDevs\DokanPro\Emails\VendorEnable();

        return $wc_emails;
    }

    /**
     * Set template override directory for Dokan Emails
     *
     * @since 2.6.6
     *
     * @param array $dokan_emails
     *
     * @return $dokan_emails
     */
    public function set_email_template_directory( $dokan_emails ) {
        $dokan_pro_emails = [
            'product-updated-pending',
            'announcement',
            'refund-seller-mail',
            'refund_request',
        ];

        return array_merge( $dokan_pro_emails, $dokan_emails );
    }

    /**
     * Register Dokan Email actions for WC
     *
     * @since 2.6.6
     *
     * @param array $actions
     *
     * @return $actions
     */
    public function register_email_actions( $actions ) {
        $actions[] = 'dokan_edited_product_pending_notification';
        $actions[] = 'dokan_after_announcement_saved';
        $actions[] = 'dokan_refund_request_notification';
        $actions[] = 'dokan_refund_processed_notification';

        return $actions;
    }

    /**
     * Get plan id
     *
     * @since 2.8.4
     *
     * @return void
     */
    public function get_plan() {
        return $this->plan;
    }

    /**
     * List of Dokan Pro plans
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_dokan_pro_plans() {
        return [
            [
                'name'        => 'starter',
                'title'       => __( 'Starter', 'dokan' ),
                'price_index' => 1,
            ],
            [
                'name'        => 'professional',
                'title'       => __( 'Professional', 'dokan' ),
                'price_index' => 2,
            ],
            [
                'name'        => 'business',
                'title'       => __( 'Business', 'dokan' ),
                'price_index' => 3,
            ],
            [
                'name'        => 'enterprise',
                'title'       => __( 'Enterprise', 'dokan' ),
                'price_index' => 4,
            ],
        ];
    }

    /**
     * Get plugin path
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
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
            $file_path  = DOKAN_PRO_CLASS . '/' . strtolower( $class_name ) . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }
}

/**
 * Load pro plugin for dokan
 *
 * @since 2.5.3
 *
 * @return void
 * */
function dokan_pro() {
    return Dokan_Pro::init();
}

dokan_pro();
