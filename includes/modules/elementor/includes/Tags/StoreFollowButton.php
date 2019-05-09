<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreFollowButton extends TagBase {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    /**
     * Tag name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-follow-store-button-tag';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Follow Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function render() {
        // Executes only in template builder
        echo __( 'Follow', 'dokan' );
    }
}
