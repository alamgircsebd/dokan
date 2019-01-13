<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreSocialProfile extends TagBase {

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
        return 'dokan-store-social-profile-tag';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Social Profile', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function render() {
        $links = [];

        if ( dokan_is_store_page() ) {
            $store       = dokan()->vendor->get( get_query_var( 'author' ) );
            $social_info = $store->get_social_profiles();
            $network_map = dokan_elementor()->get_social_networks_map();

            foreach ( $network_map as $dokan_name => $elementor_name ) {
                if ( ! empty( $social_info[ $dokan_name ] ) ) {
                    $links[ $elementor_name ] = $social_info[ $dokan_name ];
                }
            }
        }

        echo json_encode( $links );
    }
}
