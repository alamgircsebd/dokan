<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;
use Elementor\Controls_Manager;

class StoreInfo extends TagBase {

    /**
     * Tag name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-info';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Info', 'dokan' );
    }

    /**
     * Render Tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function get_value() {
        $store_data = dokan_elementor()->get_store_data();

        $store_info = [
            [
                'key'         => 'address',
                'title'       => __( 'Address', 'dokan' ),
                'text'        => $store_data['address'],
                'icon'        => 'fa fa-map-marker',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ]
            ],
            [
                'key'         => 'phone',
                'title'       => __( 'Phone No', 'dokan' ),
                'text'        => $store_data['phone'],
                'icon'        => 'fa fa-mobile',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ]
            ],
            [
                'key'         => 'email',
                'title'       => __( 'Email', 'dokan' ),
                'text'        => $store_data['email'],
                'icon'        => 'fa fa-envelope-o',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ]
            ],
            [
                'key'         => 'rating',
                'title'       => __( 'Rating', 'dokan' ),
                'text'        => $store_data['rating'],
                'icon'        => 'fa fa-star',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ]
            ],
            [
                'key'         => 'open_close_status',
                'title'       => __( 'Open/Close Status', 'dokan' ),
                'text'        => $store_data['open_close'],
                'icon'        => 'fa fa-shopping-cart',
                'show'        => true,
                '__dynamic__' => [
                    'text' => $store_data['address'],
                ]
            ],
        ];

        /**
         * Filter to modify tag values
         *
         * @since DOKAN_PRO_SINCE
         *
         * @param array $store_info
         */
        return apply_filters( 'dokan_elementor_tags_store_info_value', $store_info );
    }

    protected function render() {
        echo json_encode( $this->get_value() );
    }
}
