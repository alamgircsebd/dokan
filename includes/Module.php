<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\Traits\ChainableContainer;

class Module {

    use ChainableContainer;

    /**
     * The wp option key which contains active module ids
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    const ACTIVE_MODULES_DB_KEY = 'dokan_pro_active_modules';

    /**
     * Active module ids
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    private $active_modules = [];

    /**
     * Contains all module informations
     *
     *  @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    private $dokan_pro_modules = [];

    /**
     * Tells us if modules activated or not
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var bool
     */
    private static $modules_activated = false;

    /**
     * Update db option containing active module ids
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $value
     *
     * @return bool
     */
    protected function update_db_option( $value ) {
        return update_option( self::ACTIVE_MODULES_DB_KEY, $value );
    }

    /**
     * Load active modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $newly_activated_modules Useful after module activation
     *
     * @return void
     */
    public function load_active_modules( $newly_activated_modules = [] ) {
        if ( self::$modules_activated ) {
            return;
        }

        $active_modules    = $this->get_active_modules();
        $dokan_pro_modules = $this->get_all_modules();

        foreach ( $active_modules as $module_id ) {
            $module = $dokan_pro_modules[ $module_id ];

            if ( ! isset( $this->container[ $module_id ] ) && file_exists( $module['module_file'] ) ) {
                require_once $module['module_file'];

                $module_class = $module['module_class'];
                $this->container[ $module_id ] = new $module_class();

                if ( in_array( $module_id, $newly_activated_modules ) ) {
                    /**
                     * Module activation hook
                     *
                     * @since DOKAN_PRO_SINCE
                     *
                     * @param object $module Module class instance
                     */
                    do_action( 'dokan_activated_module_' . $module_id, $this->container[ $module_id ] );
                }
            }
        }

        self::$modules_activated = true;
    }

    /**
     * List of Dokan Pro modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_all_modules() {
        if ( ! $this->dokan_pro_modules ) {
            $thumbnail_dir  = DOKAN_PRO_PLUGIN_ASSEST . '/images/modules';

            $this->dokan_pro_modules = apply_filters( 'dokan_pro_modules', [
                'booking' => [
                    'id'           => 'booking',
                    'name'         => __( 'WooCommerce Booking Integration', 'dokan' ),
                    'description'  => __( 'Integrates WooCommerce Booking with Dokan.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/booking.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/booking/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Booking\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'color_scheme_customizer' => [
                    'id'           => 'color_scheme_customizer',
                    'name'         => __( 'Color Scheme Customizer', 'dokan' ),
                    'description'  => __( 'A Dokan plugin Add-on to Customize Colors of Dokan Dashboard', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/color-scheme-customizer.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/color-scheme-customizer/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ColorSchemeCustomizer\Module',
                    'plan'         => [ 'starter', 'professional', 'business', 'enterprise', ]
                ],
                'elementor' => [
                    'id'           => 'elementor',
                    'name'         => __( 'Elementor', 'dokan' ),
                    'description'  => __( 'Elementor Page Builder widgets for Dokan', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/elementor.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/elementor/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Elementor\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'export_import' => [
                    'id'           => 'export_import',
                    'name'         => __( 'Vendor Product Importer and Exporter', 'dokan' ),
                    'description'  => __( 'This is simple product import and export plugin for vendor', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/import-export.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/export-import/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ExIm\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'follow_store' => [
                    'id'           => 'follow_store',
                    'name'         => __( 'Follow Store', 'dokan' ),
                    'description'  => __( 'Send emails to customers when their favorite store updates.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/follow-store.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/follow-store/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\FollowStore\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'geolocation' => [
                    'id'           => 'geolocation',
                    'name'         => __( 'Geolocation', 'dokan' ),
                    'description'  => __( 'Search Products and Vendors by geolocation.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/geolocation.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/geolocation/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Geolocation\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'live_chat' => [
                    'id'           => 'live_chat',
                    'name'         => __( 'Live Chat', 'dokan' ),
                    'description'  => __( 'Live Chat Between Vendor & Customer.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/live-chat.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/live-chat/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\LiveChat\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'live_search' => [
                    'id'           => 'live_search',
                    'name'         => __( 'Live Search', 'dokan' ),
                    'description'  => __( 'Live product search for WooCommerce store.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/ajax-live-search.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/live-search/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\LiveSearch\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'moip' => [
                    'id'           => 'moip',
                    'name'         => __( 'Wirecard', 'dokan' ),
                    'description'  => __( 'Wirecard payment gateway for Dokan.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/wirecard-connect.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/moip/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Moip\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'dokan_paypal_ap' => [
                    'id'           => 'dokan_paypal_ap',
                    'name'         => __( 'PayPal Adaptive Payment', 'dokan' ),
                    'description'  => __( 'Allows to send split payments to vendor via PayPal Adaptive Payment gateway.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/paypal-adaptive.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/paypal-adaptive-payments/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\PayPalAP\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'product_addon' => [
                    'id'           => 'product_addon',
                    'name'         => __( 'Product Addon', 'dokan' ),
                    'description'  => __( 'WooCommerce Product Addon support', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/product-addon.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/product-addon/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ProductAddon\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'product_enquiry' => [
                    'id'           => 'product_enquiry',
                    'name'         => __( 'Product Enquiry', 'dokan' ),
                    'description'  => __( 'Enquiry for a specific product to a seller.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/product-enquiry.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/product-enquiry/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ProductEnquiry\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'report_abuse' => [
                    'id'           => 'report_abuse',
                    'name'         => __( 'Report Abuse', 'dokan' ),
                    'description'  => __( 'Let customers report fraudulent or fake products.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/report-abuse.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/report-abuse/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ReportAbuse\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'rma' => [
                    'id'           => 'rma',
                    'name'         => __( 'Return and Warranty Request', 'dokan' ),
                    'description'  => __( 'Manage return and warranty from vendor end.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/rma.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/rma/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\RMA\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'seller_vacation' => [
                    'id'           => 'seller_vacation',
                    'name'         => __( 'Seller Vacation', 'dokan' ),
                    'description'  => __( 'Using this plugin seller can go to vacation by closing their stores.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/seller-vacation.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/seller-vacation/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\SellerVacation\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'shipstation' => [
                    'id'           => 'shipstation',
                    'name'         => __( 'ShipStation Integration', 'dokan' ),
                    'description'  => __( 'Adds ShipStation label printing support to Dokan. Requires server DomDocument support.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/shipstation.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/shipstation/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ShipStation\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'auction' => [
                    'id'           => 'auction',
                    'name'         => __( 'Auction Integration', 'dokan' ),
                    'description'  => __( 'A plugin that combined WooCommerce simple auction and Dokan plugin.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/auction.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/simple-auction/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Auction\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'spmv' => [
                    'id'           => 'spmv',
                    'name'         => __( 'Single Product Multiple Vendor', 'dokan' ),
                    'description'  => __( 'A module that offers multiple vendor to sell a single product.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/single-product-multivendor.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/single-product-multiple-vendor/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\SPMV\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'store_reviews' => [
                    'id'           => 'store_reviews',
                    'name'         => __( 'Store Reviews', 'dokan' ),
                    'description'  => __( 'A plugin that allows customers to rate the sellers.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/vendor-review.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/store-reviews/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\StoreReviews\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'store_support' => [
                    'id'           => 'store_support',
                    'name'         => __( 'Store Support', 'dokan' ),
                    'description'  => __( 'Enable vendors to provide support to customers from store page.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/store-support.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/store-support/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\StoreSupport\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'stripe' => [
                    'id'           => 'stripe',
                    'name'         => __( 'Stripe Connect', 'dokan' ),
                    'description'  => __( 'Accept credit card payments and allow your sellers to get automatic split payment in Dokan via Stripe.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/stripe.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/stripe/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Stripe\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'product_subscription' => [
                    'id'           => 'product_subscription',
                    'name'         => __( 'Vendor Subscription', 'dokan' ),
                    'description'  => __( 'Product subscription pack add-on for Dokan vendors.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/subscription.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/subscription/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\ProductSubscription\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                // 'vendor_analytics' => [
                //     'id'           => 'vendor_analytics',
                //     'name'         => __( 'Vendor Analytics', 'dokan' ),
                //     'description'  => __( 'A plugin for store and product analytics for vendor.', 'dokan' ),
                //     'thumbnail'    => $thumbnail_dir . '/analytics.png',
                //     'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-analytics/module.php',
                //     'module_class' => 'WeDevs\DokanPro\Modules\VendorAnalytics\Module',
                //     'plan'         => []
                // ],
                'vendor_staff' => [
                    'id'           => 'vendor_staff',
                    'name'         => __( 'Vendor Staff Manager', 'dokan' ),
                    'description'  => __( 'A plugin for manage store via vendor staffs.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/vendor-staff.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-staff/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\VendorStaff\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                // 'vsp'         => [
                //     'id'           => 'vsp',
                //     'name'         => __( 'Vendor Subscription Product', 'dokan' ),
                //     'description'  => __( 'WooCommerce Subscription integration for Dokan', 'dokan' ),
                //     'thumbnail'    => $thumbnail_dir . '/ajax-live-search.png',
                //     'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-subscription-product/module.php',
                //     'module_class' => 'WeDevs\DokanPro\Modules\VSP\Module',
                //     'plan'         => [ 'enterprise', ]
                // ],
                'vendor_verification' => [
                    'id'           => 'vendor_verification',
                    'name'         => __( 'Vendor Verification', 'dokan' ),
                    'description'  => __( 'Dokan add-on to verify sellers.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/vendor-verification.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-verification/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\VendorVerification\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'wholesale' => [
                    'id'           => 'wholesale',
                    'name'         => __( 'Wholesale', 'dokan' ),
                    'description'  => __( 'Offer any customer to buy product as a wholesale price from any vendors.', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/wholesale.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/wholesale/module.php',
                    'module_class' => 'WeDevs\DokanPro\Modules\Wholesale\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
            ] );
        }

        return $this->dokan_pro_modules;
    }

    /**
     * Set Dokan Pro modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $modules
     *
     * @return void
     */
    public function set_modules( $modules ) {
        $this->dokan_pro_modules = $modules;
    }

    /**
     * Get a list of module ids
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_all_module_ids() {
        static $module_ids = [];

        if ( ! $module_ids ) {
            $modules = $this->get_all_modules();
            $module_ids = array_keys( $modules );
        }

        return $module_ids;
    }

    /**
     * Get Dokan Pro active modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_active_modules() {
        if ( $this->active_modules ) {
            return $this->active_modules;
        }

        $this->active_modules = get_option( self::ACTIVE_MODULES_DB_KEY, [] );

        if ( empty( $this->active_modules ) ) {
            return [];
        } if ( isset( $this->active_modules[0] ) && preg_match( '/php$/', $this->active_modules[0] ) ) {
            $old_convention_name_map = $this->get_compatibility_naming_map();
            $mapped_active_modules   = [];
            $test = [];

            foreach ( $this->active_modules as $module_file_name ) {
                if ( isset( $old_convention_name_map[ $module_file_name ] ) ) {
                    $mapped_active_modules[] = $old_convention_name_map[ $module_file_name ];
                }
            }

            sort( $mapped_active_modules );

            $this->update_db_option( $mapped_active_modules );

            $this->active_modules = $mapped_active_modules;
        }

        return $this->active_modules;
    }

    /**
     * Get a list of available modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_available_modules() {
        $modules           = $this->get_all_modules();
        $available_modules = [];

        foreach ( $modules as $module_id => $module ) {
            if ( file_exists( $module['module_file'] ) ) {
                $available_modules[] = $module['id'];
            }
        }

        return $available_modules;
    }

    /**
     * Backward compatible module naming map
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_compatibility_naming_map() {
        return [
            'appearance/appearance.php'                                         => 'color_scheme_customizer',
            'booking/booking.php'                                               => 'booking',
            'elementor/elementor.php'                                           => 'elementor',
            'export-import/export-import.php'                                   => 'export_import',
            'follow-store/follow-store.php'                                     => 'follow_store',
            'geolocation/geolocation.php'                                       => 'geolocation',
            'live-chat/live-chat.php'                                           => 'live_chat',
            'live-search/live-search.php'                                       => 'live_search',
            'moip/moip.php'                                                     => 'moip',
            'paypal-adaptive-payments/dokan-paypal-ap.php'                      => 'dokan_paypal_ap',
            'product-enquiry/enquiry.php'                                       => 'product_enquiry',
            'report-abuse/report-abuse.php'                                     => 'report_abuse',
            'rma/rma.php'                                                       => 'rma',
            'seller-vacation/seller-vacation.php'                               => 'seller_vacation',
            'shipstation/shipstation.php'                                       => 'shipstation',
            'simple-auction/auction.php'                                        => 'auction',
            'single-product-multiple-vendor/single-product-multiple-vendor.php' => 'spmv',
            'store-reviews/store-reviews.php'                                   => 'store_reviews',
            'store-support/store-support.php'                                   => 'store_support',
            'stripe/gateway-stripe.php'                                         => 'stripe',
            'subscription/product-subscription.php'                             => 'product_subscription',
            'vendor-analytics/vendor-analytics.php'                             => 'vendor_analytics',
            'vendor-staff/vendor-staff.php'                                     => 'vendor_staff',
            'vendor-verification/vendor-verification.php'                       => 'vendor_verification',
            'wholesale/wholesale.php'                                           => 'wholesale',
        ];
    }

    /**
     * Activate Dokan Pro modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $modules
     *
     * @return array
     */
    public function activate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        $this->active_modules = array_unique( array_merge( $active_modules, $modules ) );

        $this->update_db_option( $this->active_modules );

        self::$modules_activated = false;

        $this->load_active_modules( $modules );

        return $this->active_modules;
    }

    /**
     * Deactivate Dokan Pro modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $modules
     *
     * @return array
     */
    public function deactivate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        foreach ( $modules as $module_id ) {
            $active_modules = array_diff( $active_modules, [ $module_id ] );
        }

        $active_modules = array_values( $active_modules );

        $this->active_modules = $active_modules;

        $this->update_db_option( $this->active_modules );

        add_action( 'shutdown', function () use ( $modules ) {
            foreach ( $modules as $module_id ) {
                /**
                 * Module deactivation hook
                 *
                 * @since DOKAN_PRO_SINCE
                 *
                 * @param object $module deactivated module class instance
                 */
                do_action( 'dokan_deactivated_module_' . $module_id, dokan_pro()->module->$module_id );
            }
        } );

        return $this->active_modules;
    }

    /**
     * Checks if a module is active or not
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $module_id
     *
     * @return bool
     */
    public function is_active( $module_id ) {
        $active_modules = $this->get_active_modules();

        if ( in_array( $module_id, $active_modules ) ) {
            return true;
        }

        return false;
    }
}
