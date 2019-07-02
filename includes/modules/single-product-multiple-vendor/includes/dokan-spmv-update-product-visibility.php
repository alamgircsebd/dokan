<?php

/**
 * Update vendor and product geolocation data
 *
 * @since DOKAN_PRO_SINCE
 */
class Dokan_SPMV_Update_Product_Visibility extends Abstract_Dokan_Background_Processes {

    /**
     * Action
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    protected $action = 'Dokan_SPMV_Update_Product_Visibility';

    /**
     * Perform updates
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param mixed $item
     *
     * @return mixed
     */
    public function task( $item ) {
        global $wpdb;

        if ( empty( $item['map_ids'] ) ) {
            return false;
        }

        $map_id = absint( array_pop( $item['map_ids'] ) );

        dokan_spmv_update_clone_visibilities( $map_id );

        return $item;
    }
}
