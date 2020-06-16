<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

defined( 'ABSPATH' ) || exit;

/**
 * Vendor Settings Class
 *
 * @since 1.0.0
 */
class VendorSettings {
    /**
     * Constructor method
     *
     * @return void
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize all the hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'dokan_settings_form_bottom', [ $this, 'dokan_live_chat_seller_settings' ], 15, 2 );
        add_action( 'dokan_store_profile_saved', [ $this, 'dokan_live_chat_save_seller_settings' ], 15 );
    }

    /**
     * Register live caht seller settings on seller dashboard
     *
     * @param  int $user_id
     *
     * @param  object $profile
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function dokan_live_chat_seller_settings( $user_id, $profile ) {
        if ( ! AdminSettings::is_enabled() ) {
            return;
        }

        $is_messenger = 'messenger' === AdminSettings::get_provider();
        $enable_chat  = isset( $profile['live_chat'] ) ? $profile['live_chat'] : 'no';
        $fb_page_id   = ! empty( $profile['fb_page_id'] ) ? $profile['fb_page_id'] : '';

        ?>
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label"><?php _e( 'Enable Live Chat' , 'dokan' ) ?></label>
            <div class="dokan-w5 dokan-text-left">
                <div class="checkbox">
                    <label>
                        <input type="hidden" name="live_chat" value="no">
                        <input type="checkbox" id="live_chat" name="live_chat" value="yes" <?php checked( $enable_chat, 'yes' ); ?>><?php  _e( 'Enable Live Chat', 'dokan'); ?>
                    </label>
                </div>
            </div>
        </div>
        <?php

        if ( $is_messenger ) {
            ?>
            <div class="dokan-form-group dokan-live-chat-settings">
                <label for="fb_page_id" class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Facebook Page ID' , 'dokan' ) ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input type="text" id="fb_page_id" name="fb_page_id" value=<?php echo esc_attr( $fb_page_id ); ?>>
                    <a
                        href="<?php echo esc_url( 'https://www.facebook.com/pages/create/' ); ?>"
                        style="font-style: italic;text-decoration: underline !important;color: gray;padding-left: 10px;"
                        target="_blank"
                        class="get-fb-page-id">
                        <?php esc_html_e( 'Get Facebook page id', 'dokan' ); ?>
                    </a>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Save dokan live chat seller settings
     *
     * @param  string $user_id
     *
     * @return void
     */
    public function dokan_live_chat_save_seller_settings( $user_id ) {
        if ( ! isset( $_POST['live_chat'] ) ) {
            return;
        }

        $store_info               = dokan_get_store_info( $user_id );
        $store_info['live_chat']  = wc_clean( $_POST['live_chat'] );

        if ( ! empty( $_POST['fb_page_id'] ) ) {
            $store_info['fb_page_id'] = wc_clean( $_POST['fb_page_id'] );
        }

        update_user_meta( $user_id, 'dokan_profile_settings', $store_info );
    }
}