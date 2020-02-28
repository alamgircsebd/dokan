<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

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

        if ( dokan_is_store_page() && class_exists( \WeDevs\DokanPro\Modules\LiveChat\Chat::class ) ) {
            if ( dokan_pro()->module->live_chat->chat->dokan_is_seller_online() ) {
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
