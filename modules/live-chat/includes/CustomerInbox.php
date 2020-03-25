<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

defined( 'ABSPATH' ) || exit;

/**
 * Customer inbox class
 */
class CustomerInbox extends Chat {

    /**
     * Constructor of this class
     *
     * @since 1.1
     */
    public function __construct() {
        $this->init_hooks();
        parent::__construct();
    }

    /**
     * Init all the hooks
     *
     * @since  1.1
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'woocommerce_account_menu_items', [ $this, 'add_customer_inbox' ] );
        add_action( 'init', [ $this, 'add_enpoint' ] );
        add_action( 'woocommerce_account_customer-inbox_endpoint', [ $this, 'render_content' ] );
    }

    /**
     * Add customer inbox menu
     *
     * @param array $menus
     *
     * @since 1.1
     *
     * @return array
     */
    public function add_customer_inbox( $menus ) {
        if ( $this->enabled ) {
            $menus['customer-inbox'] = __( 'Inbox', 'dokan' );
        }

        return $menus;
    }

    /**
     * Add endpoint
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function add_enpoint() {
        add_rewrite_endpoint( 'customer-inbox', EP_PAGES );
    }

    /**
     * Render content
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function render_content() {
        ?>
        <div id="customer-inbox">
            <?php echo do_shortcode( '[dokan-chat-inbox]' ); ?>
        </div>
        <?php
    }
}