<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreSupportButton extends TagBase {

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
        return 'dokan-store-support-button-tag';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Support Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function render() {
        if ( ! class_exists( 'Dokan_Store_Support' ) ) {
            echo __( 'Dokan Store Support module is not active', 'dokan' );
            return;
        }

        $text = __( 'Get Support', 'dokan' );

        if ( dokan_is_store_page() ) {
            $id = dokan_elementor()->get_store_data( 'id' );

            if ( $id ) {
                $store_support  = \Dokan_Store_Support::init();
                $support_button = $store_support->get_support_button( $id );

                if ( $support_button['show'] ) {
                    $text = $support_button['text'];
                }
            }
        }

        echo $text;
    }
}
