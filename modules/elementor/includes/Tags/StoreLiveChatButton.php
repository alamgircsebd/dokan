<?php

namespace DokanPro\Modules\Elementor\Tags;

use DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreLiveChatButton extends TagBase {

    /**
     * Class constructor
     *
     * @since 2.9.11
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-live-chat-button-tag';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Live Chat Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        $online_indicator = '';

        if ( dokan_is_store_page() && class_exists( 'Dokan_Live_Chat_Start' ) ) {
            $live_chat = dokan_pro()->module->live_chat;

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
