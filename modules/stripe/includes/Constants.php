<?php

namespace WeDevs\DokanPro\Modules\Stripe;

defined( 'ABSPATH' ) || exit;

class Constants {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->load();
    }

    /**
     * Load plugin constants
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function load() {
        $path = DOKAN_PRO_MODULE_DIR . '/stripe/module.php';

        $this->define( 'DOKAN_STRIPE_FILE', $path );
        $this->define( 'DOKAN_STRIPE_PATH', dirname( $path ) );
        $this->define( 'DOKAN_STRIPE_ASSETS', plugin_dir_url( $path ) . 'assets/' );
        $this->define( 'DOKAN_STRIPE_TEMPLATE_PATH', dirname( $path ) . '/templates/' );
    }

    /**
     * Define constants
     *
     * @since 3.0.3
     *
     * @param string $name
     * @param  string $path
     *
     * @return void
     */
    private function define( $name, $path ) {
        defined( $name ) || define( $name, $path );
    }
}