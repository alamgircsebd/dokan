<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;

defined( 'ABSPATH' ) || exit;

class Chat {

    /**
     * Hold provider class instance
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var null
     */
    public $provider = null;

    public function __construct() {
        if ( ! AdminSettings::is_enabled() ) {
            return;
        }

        $provider = ucwords( AdminSettings::get_provider() );

        if ( class_exists( __NAMESPACE__ . '\\' . $provider ) ) {
            $class = __NAMESPACE__ . '\\'. $provider;
            $this->provider = new $class();
        }
    }
}