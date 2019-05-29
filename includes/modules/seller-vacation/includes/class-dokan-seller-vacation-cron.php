<?php

class Dokan_Seller_Vacation_Cron {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_action( 'check_daily_is_vacation_is_set_action', 'dokan_seller_vacation_update_product_status' );

        if ( ! wp_next_scheduled( 'check_daily_is_vacation_is_set_action' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'check_daily_is_vacation_is_set_action' );
        }
    }

    /**
     * Unschedule cron
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public static function unschedule_event() {
        $timestamp = wp_next_scheduled( 'check_daily_is_vacation_is_set_action' );
        wp_unschedule_event( $timestamp, 'check_daily_is_vacation_is_set_action' );
    }
}
