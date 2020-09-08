<?php

namespace WeDevs\DokanPro\Modules\VSP;

class Module {

    /**
     * The plugins which are dependent for this plugin
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $depends_on = array();

    /**
     * Displa dependency error if not present
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $dependency_error = array();

    /**
     * Constructor for the Dokan_VSP class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        $this->depends_on['WC_Subscriptions'] = array(
            'name'   => 'WC_Subscriptions',
            'notice' => sprintf( __( '<b>Dokan Vendor Subscription Product Addon </b> requires %sWooCommerce Subscriptions plugin%s to be installed & activated first !' , 'dokan' ), '<a target="_blank" href="https://woocommerce.com/products/woocommerce-subscriptions/">', '</a>' ),
        );

        if ( ! $this->check_if_has_dependency() ) {
            add_action( 'admin_notices', array ( $this, 'dependency_notice' ) );
            return;
        }

        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();
    }

    /**
     * hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_VSP_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_VSP_DIR_INC_DIR', DOKAN_VSP_DIR . '/includes' );
        define( 'DOKAN_VSP_DIR_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
    * Get plugin path
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        // Load all helper functions
        require_once DOKAN_VSP_DIR_INC_DIR . '/functions.php';

        // Load classes
        require_once DOKAN_VSP_DIR_INC_DIR . '/class-vendor-product.php';
        require_once DOKAN_VSP_DIR_INC_DIR . '/class-user-subscription.php';
    }

    /**
     * Initiate all classes
     *
     * @return void
     */
    public function initiate() {
        new \Dokan_VSP_Product();
        new \Dokan_VSP_User_Subscription();
    }

     /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_subcription_product_templates' ], 10, 3 );
        add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'order_needs_processing' ), 10, 2 );
        add_filter( 'dokan_get_product_types', [ $this, 'add_subscription_type_product' ] );
    }

    /**
     * Tell WC that we don't need any processing
     *
     * @param  bool $needs_processing
     * @param  array $product
     * @return bool
     */
    function order_needs_processing( $needs_processing, $product ) {

        if ( $product->get_type() == 'subscription' || $product->get_type() == 'variable-subscription' || $product->get_type() == 'subscription_variation' ) {
            $needs_processing = false;
        }

        return $needs_processing;
    }

    /**
     * Add subscription product for vendor subscription allowd categories
     *
     * @since 3.0.8
     *
     * @param $product_type
     *
     * @return array
     */
    public function add_subscription_type_product( $product_type ) {
        $product_type['subscription']          = __( 'Simple Subscription Product', 'dokan' );
        $product_type['variable-subscription'] = __( 'Variable Subscription Product', 'dokan' );

        return $product_type;
    }

    /**
     * Load global scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_scripts() {
        global $wp;

        // Vendor product edit page when product already publish
        if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
            $this->enqueue_scripts();
        }

        // Vendor product edit page when product is pending review
        if ( isset( $wp->query_vars['products'] ) && ! empty( $_GET['product_id'] ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
            $this->enqueue_scripts();
        }

        if ( isset( $wp->query_vars['user-subscription'] ) && ! empty( $_GET['subscription_id'] ) ) {
            $this->enqueue_scripts();
        }

        if ( isset( $wp->query_vars['coupons'] ) && ! empty( $_GET['post'] ) ) {
            $this->enqueue_scripts();
        }
    }

    /**
     * Print error notice if dependency not active
     *
     * @since 1.0.0
     */
    function dependency_notice(){
        $errors = '';
        $error = '';
        foreach ( $this->dependency_error as $error ) {
            $errors .= '<p>' . $error . '</p>';
        }
        $message = '<div class="error">' . $errors . '</div>';

        echo $message;
    }

    /**
     * Check whether is their has any dependency or not
     *
     * @return boolean
     */
    function check_if_has_dependency() {
        $res = true;

        foreach ( $this->depends_on as $class ) {
            if ( ! class_exists( $class['name'] ) ) {
                $this->dependency_error[] = $class['notice'];
                $res = false;
            }
        }

        return $res;
    }

    /**
     * Enqueue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-vsp-style', DOKAN_VSP_DIR_ASSETS_DIR . '/css/style.css', false , DOKAN_PLUGIN_VERSION, 'all' );
        wp_enqueue_script( 'dokan-vsp-script', DOKAN_VSP_DIR_ASSETS_DIR . '/js/scripts.js', array( 'jquery' ), DOKAN_PLUGIN_VERSION, true );

        $billing_period_strings = \WC_Subscriptions_Synchroniser::get_billing_period_ranges();

        $params = [
            'productType'               => \WC_Subscriptions::$name,
            'trialPeriodSingular'       => wcs_get_available_time_periods(),
            'trialPeriodPlurals'        => wcs_get_available_time_periods( 'plural' ),
            'subscriptionLengths'       => wcs_get_subscription_ranges(),
            'subscriptionLengths'       => wcs_get_subscription_ranges(),
            'syncOptions'                           => [
                'week'  => $billing_period_strings['week'],
                'month' => $billing_period_strings['month'],
            ]
        ];

        wp_localize_script( 'jquery', 'dokanVPS', apply_filters( 'wc_vps_params', $params ) );
    }

    /**
     * Set subscription html templates directory
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_subcription_product_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_subscription_product'] ) && $args['is_subscription_product'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;

    }
}
