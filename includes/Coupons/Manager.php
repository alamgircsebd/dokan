<?php

namespace WeDevs\DokanPro\Coupons;

use WP_Query;
use WC_Coupon;
use WeDevs\Dokan\Exceptions\DokanException;

/**
* Coupon Manager class
*
* @since 3.0.0
*/
class Manager {

    /**
     * Get all coupons
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function all( $args = [] ) {
        $default = [
            'seller_id' => dokan_get_current_user_id(),
            'paged'     => 1,
            'limit'     => 10,
            'paginate'  => true
        ];

        $args   = wp_parse_args( $args, $default );

        $coupon_query = new WP_Query( apply_filters( 'dokan_get_vendor_coupons_args', [
            'post_type'              => 'shop_coupon',
            'post_status'            => ['publish'],
            'posts_per_page'         => $args['limit'],
            'author'                 => $args['seller_id'],
            'paged'                  => $args['paged'],
            'fields'                 => 'ids',
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ], $args ) );

        $coupons = $coupon_query->get_posts();

        $coupons = array_map( function( $coupon_id ) {
            return $this->get( $coupon_id );
        }, $coupons );

        if ( empty( $args['paginate'] ) ) {
            return $coupons;
        } else {
            return (object) array(
                'coupons'  => $coupons,
                'total'    => intval( $coupon_query->found_posts ),
                'per_page' => $args['limit']
            );
        }
    }

    /**
     * Get a coupon object
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function get( $id = 0 ) {
        return new WC_Coupon( $id );
    }

    /**
     * Delete a coupon
     *
     * @since 3.0.0
     *
     * @return Void
     */
    public function delete( $id, $force = true ) {
        if ( empty( $id ) ) {
            throw new DokanException( 'dokan_coupon_id_not_found', __( 'Coupon not found', 'dokan' ), 401 );
        }

        $coupon = $this->get( $id );

        if ( $coupon ) {
            $coupon->delete( [ 'force_delete' => $force ] );
        }

        return $coupon;
    }
}
