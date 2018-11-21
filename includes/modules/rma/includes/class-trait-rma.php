<?php

/**
* Trail Class for RMA
*/
trait Dokan_RMA_Common {

    /**
     * Transform post request for rma settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    function transform_rma_settings( $request = [] ) {
        $data = [];

        if ( ! empty( $request ) ) {
            $data = [
                'label'           => !empty( $request['warranty_label'] ) ? $request['warranty_label'] : __( 'Warranty', 'dokan' ),
                'type'            => !empty( $request['warranty_type'] ) ? $request['warranty_type'] : 'no_warranty',
                'policy'          => ! empty( $request['warranty_policy'] ) ? $request['warranty_policy'] : '',
                'reasons'         => ! empty( $request['warranty_reason'] ) ? $request['warranty_reason'] : [],
                'length'          => '',
                'length_value'    => '',
                'length_duration' => '',
                'addon_settings'  => [],
            ];

            if ( 'included_warranty' == $data['type'] ) {
                $data['length']          = $request['warranty_length'];
                $data['length_value']    = $request['warranty_length_value'];
                $data['length_duration'] = $request['warranty_length_duration'];
                $data['addon_settings']  = [];

                if ( 'lifetime' == $data['length'] ) {
                    $data['length_value']    = '';
                    $data['length_duration'] = '';
                }
            }

            if ( 'addon_warranty' == $data['type'] ) {
                $addon_settings = [];

                if ( ! empty( $request['warranty_addon_price'] ) ) {
                    foreach ( $request['warranty_addon_price'] as $key => $price ) {
                        $addon_settings[] = [
                            'price'    => $price,
                            'length'   => !empty( $request['warranty_addon_length'][$key] ) ? $request['warranty_addon_length'][$key] : '',
                            'duration' => !empty( $request['warranty_addon_duration'][$key] ) ? $request['warranty_addon_duration'][$key] : '',
                        ];
                    }
                }

                $data['addon_settings'] = $addon_settings;
            }
        }

        return $data;
    }

    /**
     * Get setting value
     *
     * @since 1.0.0
     *
     * @return void
     */
    function get_settings( $product_id = 0 ) {
        $rma_settings   = [];
        $admin_settings = dokan_get_option( 'rma_policy', 'dokan_rma', '' );
        $default        = [
            'from'            => 'store',
            'label'           => __( 'Warranty', 'dokan' ),
            'type'            => 'no_warranty',
            'policy'          => $admin_settings,
            'reasons'         => [],
            'length'          => '',
            'length_value'    => '',
            'length_duration' => '',
            'addon_settings'  => []
        ];

        if ( $product_id ) {

            /**
             * has product ID and get product rma settings if have. If not set in product then
             * return those product store owner default settings
             */

            $override_default = get_post_meta( $product_id, '_dokan_rma_override_product', true );

            if ( 'yes' == $override_default ) {
                $rma_settings         = get_post_meta( $product_id, '_dokan_rma_settings', true );
                $rma_settings['from'] = 'product';
            } else {
                $seller_id            = get_post_field( 'post_author', $product_id );
                $rma_settings         = get_user_meta( $seller_id, '_dokan_rma_settings', true );
                $rma_settings['from'] = 'store';
            }

        } else {
            // return default store settings if user logged id as a seller
            $user_id = dokan_get_current_user_id();

            // Not found any user so return $default settings
            if ( ! $user_id ) {
                return $default;
            }

            $rma_settings         = get_user_meta( $user_id, '_dokan_rma_settings', true );
            $rma_settings['from'] = 'store';
        }

        return $rma_settings;
    }

    /**
     * Check required add-ons.
     *
     * @param int $product_id Product ID.
     * @return bool
     */
    function check_required_warranty( $product_id ) {
        $warranty       = $this->get_settings( $product_id );

        if ( $warranty['type'] == 'addon_warranty' && ! isset( $_REQUEST['dokan_warranty'] ) ) {
            return true;
        }

        return false;
    }
}
