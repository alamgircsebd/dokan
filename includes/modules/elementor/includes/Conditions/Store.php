<?php

namespace DokanPro\Modules\Elementor\Conditions;

use ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base;

class Store extends Condition_Base {

    /**
     * Type of condition
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_type() {
        return 'store';
    }

    /**
     * Condition name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'store';
    }

    /**
     * Condition label
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_label() {
        return __( 'Single Store', 'dokan' );
    }

    /**
     * Condition label for all items
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_all_label() {
        return __( 'All Stores', 'dokan' );
    }

    /**
     * Check if proper conditions are met
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $args
     *
     * @return bool
     */
    public function check( $args ) {
        return dokan_is_store_page();
    }
}
