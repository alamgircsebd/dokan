<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreLiveChatButton extends TagBase {

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
        return 'dokan-store-live-chat-button-tag';
    }

    /**
     * Tag title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Support Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function render() {
        $online_indicator = '';

        if ( dokan_is_store_page() && class_exists( 'Dokan_Live_Chat_Start' ) ) {
            $live_chat = \Dokan_Live_Chat_Start::init();

            if ( $live_chat->dokan_is_seller_online() ) {
                $online_indicator = '<i class="fa fa-circle" aria-hidden="true"></i>';
            }
        }

        printf(
            '%s%s',
            $online_indicator,
            __( 'Chat Now', 'dokan' )
        );
    }
}
