<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\DataTagBase;
use Elementor\Controls_Manager;

class StoreInfo extends DataTagBase {

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
        return 'dokan-store-info';
    }

    /**
     * Tag title
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Info', 'dokan' );
    }

    /**
     * Store profile picture
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function get_value( array $options = [] ) {
        $store_data = dokan_elementor()->default_store_data();

        $store_info = [
            [
                'title' => __( 'Address', 'dokan' ),
                'text' => $store_data['address'],
                'icon' => 'fa fa-map-marker',
                'show' => true,
            ],
            [
                'title' => __( 'Phone No', 'dokan' ),
                'text' => $store_data['phone'],
                'icon' => 'fa fa-mobile',
                'show' => true,
            ],
            [
                'title' => __( 'Email', 'dokan' ),
                'text' => $store_data['email'],
                'icon' => 'fa fa-envelope-o',
                'show' => true,
            ],
            [
                'title' => __( 'Rating', 'dokan' ),
                'text' => $store_data['rating'],
                'icon' => 'fa fa-star',
                'show' => true,
            ],
            [
                'title' => __( 'Open/Close Status', 'dokan' ),
                'text' => $store_data['open_close'],
                'icon' => 'fa fa-shopping-cart',
                'show' => true,
            ],
        ];


        /**
         * Filter to modify tag values
         *
         * @since 1.0.0
         *
         * @param array $store_info
         */
        return apply_filters( 'dokan_elementor_tags_store_info_value', $store_info );
    }
}
