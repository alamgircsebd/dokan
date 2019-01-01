<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\DataTagBase;
use Elementor\Controls_Manager;

class StoreBanner extends DataTagBase {

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
        return 'dokan-store-banner';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Banner', 'dokan' );
    }

    /**
     * Store profile picture
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function get_value( array $options = [] ) {
        return dokan_elementor()->default_store_data( 'banner' );
    }

    /**
     * Register tag controls
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function _register_controls() {
        $this->add_control(
            'fallback',
            [
                'label' => __( 'Fallback', 'dokan' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
                ]
            ]
        );
    }
}
