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
     * [$dokan_pro_modules description]
     *
     * @var array
     */
    private $dokan_pro_modules = [];

    /**
     * Class container
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        $this->load_active_modules();
    }

    /**
     * Load active modules
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function load_active_modules() {
        $active_modules    = $this->get_active_modules();
        $dokan_pro_modules = $this->get_all_modules();

        foreach ( $active_modules as $module_id ) {
            $module = $dokan_pro_modules[ $module_id ];

            if ( file_exists( $module['module_file'] ) ) {
                require_once $module['module_file'];

                $module_class = $module['module_class'];
                $this->container[ $module_id ] = new $module_class();
            }
        }
    }

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
                    'module_class' => 'WeDevs\DokanPro\Module\Booking\Module',
                    'plan'         => [ 'business', 'enterprise', ]
                ],
                'color_scheme_customizer' => [
                    'id'           => 'color_scheme_customizer',
                    'name'         => __( 'Color Scheme Customizer', 'dokan' ),
                    'description'  => __( 'A Dokan plugin Add-on to Customize Colors of Dokan Dashboard', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/color-scheme-customizer.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/color-scheme-customizer/module.php',
                    'module_class' => 'WeDevs\DokanPro\Module\ColorSchemeCustomizer\Module',
                    'plan'         => [ 'starter', 'professional', 'business', 'enterprise', ]
                ],
                'elementor' => [
                    'id'           => 'elementor',
                    'name'         => __( 'Elementor', 'dokan' ),
                    'description'  => __( 'Elementor Page Builder widgets for Dokan', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/elementor.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/elementor/module.php',
                    'module_class' => 'WeDevs\DokanPro\Module\Elementor\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
                ],
                'rma' => [
                    'id'           => 'rma',
                    'name'         => __( 'Return and Warranty Request', 'dokan' ),
                    'description'  => __( 'Manage return and warranty from vendor end', 'dokan' ),
                    'thumbnail'    => $thumbnail_dir . '/rma.png',
                    'module_file'  => DOKAN_PRO_MODULE_DIR . '/rma/module.php',
                    'module_class' => 'WeDevs\DokanPro\Module\RMA\Module',
                    'plan'         => [ 'professional', 'business', 'enterprise', ]
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
            // 'export-import/export-import.php'                                   => '',
            // 'follow-store/follow-store.php'                                     => '',
            // 'geolocation/geolocation.php'                                       => '',
            // 'live-chat/live-chat.php'                                           => '',
            // 'live-search/live-search.php'                                       => '',
            // 'moip/moip.php'                                                     => '',
            // 'paypal-adaptive-payments/dokan-paypal-ap.php'                      => '',
            // 'product-enquiry/enquiry.php'                                       => '',
            // 'report-abuse/report-abuse.php'                                     => '',
            'rma/rma.php'                                                       => 'rma',
            // 'seller-vacation/seller-vacation.php'                               => '',
            // 'shipstation/shipstation.php'                                       => '',
            // 'simple-auction/auction.php'                                        => '',
            // 'single-product-multiple-vendor/single-product-multiple-vendor.php' => '',
            // 'store-reviews/store-reviews.php'                                   => '',
            // 'store-support/store-support.php'                                   => '',
            // 'stripe/gateway-stripe.php'                                         => '',
            // 'subscription/product-subscription.php'                             => '',
            // 'vendor-analytics/vendor-analytics.php'                             => '',
            // 'vendor-staff/vendor-staff.php'                                     => '',
            // 'vendor-verification/vendor-verification.php'                       => '',
            // 'wholesale/wholesale.php'                                           => '',
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

        return $this->active_modules;
    }
}
