<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

use WP_Error;

class Module {
    /**
     * Class instance holder
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    public $controller = [];

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
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function init_classes() {
        $this->controller['vendor_inbox']    = new VendorInbox();
        $this->controller['customer_inbox']  = new CustomerInbox();
        $this->controller['admin_settings']  = new AdminSettings();
        $this->controller['vendor_settings'] = new VendorSettings();
        $this->controller['chat']            = new Chat();
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 2.6.10
     *
     * @param $prop
     *
     * @return Class Instance
     */
    public function __get( $prop ) {
        if ( empty( $this->controller[ $prop ] ) ) {
            return new WP_Error(
                "{$prop}_not_found",
                sprintf( __( 'The %s is not found', 'dokan' ), $prop ),
                404
            );
        }

        return $this->controller[ $prop ];
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
