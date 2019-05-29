<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreVacationMessage extends TagBase {

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
        return 'dokan-store-vacation-message';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Vacation Message', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function render() {
        if ( ! class_exists( 'Dokan_Seller_Vacation' ) ) {
            echo __( 'Dokan Seller Vacation module is not active', 'dokan' );
            return;
        }

        if ( dokan_is_store_page() ) {
            $seller_vacation = \Dokan_Seller_Vacation::instance();
            $store           = dokan()->vendor->get( get_query_var( 'author' ) );
            $shop_info       = $store->get_shop_info();

            $seller_vacation->show_vacation_message( $store->data, $shop_info, true );

        } else {
            echo esc_html_e( 'Store vacation message set in vendor dashboard will show here.', 'dokan' );
        }
    }
}
