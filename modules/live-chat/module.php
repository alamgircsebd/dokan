<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;
use WeDevs\Dokan\Traits\ChainableContainer;

class Module {

    use ChainableContainer;

    /**
     * Constructor method for this class
     */
    public function __construct() {
        $this->define_constants();
        $this->init_classes();

        add_action( 'dokan_activated_module_live_chat', array( self::class, 'activate' ) );
        add_action( 'dokan_deactivated_module_live_chat', array( self::class, 'deactivate' ) );
    }

    /**
     * Define all the constants
     *
     * @since 1.0
     *
     * @return string
     */
    private function define_constants() {
        define( 'DOKAN_LIVE_CHAT', dirname( __FILE__ ) );
        define( 'DOKAN_LIVE_CHAT_INC', DOKAN_LIVE_CHAT . '/includes' );
        define( 'DOKAN_LIVE_CHAT_ASSETS', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_LIVE_CHAT_TEMPLATE', __DIR__ . '/templates' );
    }

    /**
     * Init classes
     *
     * @since 3.0.0
     *
     * @return void
     */
    private function init_classes() {
        $this->container['admin_settings']  = new AdminSettings();
        $this->container['vendor_settings'] = new VendorSettings();
        $this->container['chat']            = new Chat();
        $this->container['customer_inbox']  = new CustomerInbox();
        $this->container['vendor_inbox']    = new VendorInbox();
    }

    /**
     * Add permission on activation
     *
     * @since 1.0
     *
     * @return void
     */
    public static function activate() {
        set_transient( 'dokan-live-chat', true );

        $role = get_role( 'seller' );
        $role->add_cap( 'dokan_view_inbox_menu', true );
    }

    /**
     * Remove permission on deactivation
     *
     * @since 1.0
     *
     * @return void
     */
    public static function deactivate() {
        $role = get_role( 'seller' );
        $role->remove_cap( 'dokan_view_inbox_menu' );
    }
}
