<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreName extends TagBase {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    /**
     * Tag name
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-name-tag';
    }

    /**
     * Tag title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Name', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render() {
        echo dokan_elementor()->default_store_data( 'store_name' );
    }
}
