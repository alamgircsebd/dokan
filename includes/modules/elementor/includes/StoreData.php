<?php

namespace DokanPro\Modules\Elementor;

use Dokan\Traits\Singleton;

class StoreData {

    use Singleton;

    /**
     * Holds the store data for a real store
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    protected $store_data = [];

    /**
     * Default dynamic store data for widgets
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_data( $prop = null ) {
        $is_edit_mode    = dokan_elementor()->elementor()->editor->is_edit_mode();
        $is_preview_mode = dokan_elementor()->elementor()->preview->is_preview_mode();

        if ( empty( $is_edit_mode ) && empty( $is_preview_mode ) ) {
            if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['editor_post_id'] ) ) {
                $is_edit_mode = true;
            } else if ( ! empty( $_REQUEST['preview'] ) && $_REQUEST['preview'] && ! empty( $_REQUEST['theme_template_id'] ) ) {
                $is_preview_mode = true;
            }
        }

        if ( ! $is_edit_mode && ! $is_preview_mode ) {
            $data = $this->get_store_data();
        } else {
            $data = $this->get_store_data_for_editing();
        }

        return ( $prop && isset( $data[ $prop ] ) ) ? $data[ $prop ] : $data;
    }

    /**
     * Data for non-editing purpose
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    protected function get_store_data() {
        if ( ! empty( $this->store_data ) ) {
            return $this->store_data;
        }

        /**
         * Filter to modify default
         *
         * Defaults are intentionally skipped from translating
         *
         * @since DOKAN_PRO_SINCE
         *
         * @param array $data
         */
        $this->store_data = apply_filters( 'dokan_elementor_store_data_defaults', [
            'banner'          => [
                'id'  => 0,
                'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
            ],
            'name'            => '',
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ],
            'address'         => '',
            'phone'           => '',
            'email'           => '',
            'rating'          => '',
            'open_close'      => '',
        ] );


        $store = dokan()->vendor->get( get_query_var( 'author' ) );

        if ( $store->id ) {
            $banner_id = $store->get_info_part( 'banner' );

            if ( $banner_id ) {
                $this->store_data['banner'] = [
                    'id'  => $banner_id,
                    'url' => $store->get_banner(),
                ];
            }

            $this->store_data['name'] = $store->get_name();

            $profile_picture_id = $store->get_info_part( 'gravatar' );

            if ( $profile_picture_id ) {
                $this->store_data['profile_picture'] = [
                    'id'  => $profile_picture_id,
                    'url' => $store->get_avatar(),
                ];
            }

            $address = dokan_get_seller_short_address( $store->get_id(), false );

            if ( ! empty( $address ) ) {
                $this->store_data['address'] = $address;
            }

            $phone = $store->get_phone();

            if ( ! empty( $phone ) ) {
                $this->store_data['phone'] = $phone;
            }

            $email = $store->get_email();

            if ( ! empty( $email ) ) {
                $this->store_data['email'] = $store->show_email() ? $email : '';
            }

            $rating = $store->get_readable_rating( false );

            if ( ! empty( $rating ) ) {
                $this->store_data['rating'] = $rating;
            }

            $store_info               = $store->get_shop_info();
            $dokan_store_time_enabled = isset( $store_info['dokan_store_time_enabled'] ) ? $store_info['dokan_store_time_enabled'] : '';
            $store_open_notice        = isset( $store_info['dokan_store_open_notice'] ) && ! empty( $store_info['dokan_store_open_notice'] ) ? $store_info['dokan_store_open_notice'] : __( 'Store Open', 'dokan-lite' );
            $store_closed_notice      = isset( $store_info['dokan_store_close_notice'] ) && ! empty( $store_info['dokan_store_close_notice'] ) ? $store_info['dokan_store_close_notice'] : __( 'Store Closed', 'dokan-lite' );
            $show_store_open_close    = dokan_get_option( 'store_open_close', 'dokan_general', 'on' );

            if ( $show_store_open_close == 'on' && $dokan_store_time_enabled == 'yes') {
                if ( dokan_is_store_open( $store->get_id() ) ) {
                    $this->store_data['open_close'] = esc_attr( $store_open_notice );
                } else {
                    $this->store_data['open_close'] = esc_attr( $store_closed_notice );
                }
            }

            /**
             * Filter to modify store data
             *
             * @since DOKAN_PRO_SINCE
             *
             * @param array $this->store_data
             */
            $this->store_data = apply_filters( 'dokan_elementor_store_data', $this->store_data );
        }

        return $this->store_data;
    }

    /**
     * Data for editing/previewing purpose
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    protected function get_store_data_for_editing() {
        /**
         * Filter to modify default
         *
         * Defaults are intentionally skipped from translating
         *
         * @since DOKAN_PRO_SINCE
         *
         * @param array $this->store_data_editing
         */
        return apply_filters( 'dokan_elementor_store_data_defaults_for_editing', [
            'banner'          => [
                'id'  => 0,
                'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
            ],
            'name'            => 'Store Name',
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ],
            'address'         => 'New York, United States (US)',
            'phone'           => '123-456-7890',
            'email'           => 'mail@store.com',
            'rating'          => '5 rating from 100 reviews',
            'open_close'      => 'Store is open',
        ] );
    }
}
