<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

class Module {

    public static $instance;
    public static $version;

    /**
     * Constructor method for this class
     */
    public function __construct() {
        self::$version = '1.1';

        $this->define_constants();
        $this->include_files();

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
    public function define_constants() {
        define( 'DOKAN_LIVE_CHAT', dirname( __FILE__ ) );
        define( 'DOKAN_LIVE_CHAT_INC', DOKAN_LIVE_CHAT . '/includes' );
        define( 'DOKAN_LIVE_CHAT_ASSETS', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Includes necessary classes
     *
     * @since 1.0
     *
     * @return void
     */
    public function include_files() {
        if ( $this->request( 'admin' ) ) {
            require_once DOKAN_LIVE_CHAT_INC . '/admin/class-settings.php';
        }

        if ( $this->request( 'public_or_ajax' ) ) {
            require_once DOKAN_LIVE_CHAT_INC . '/public/class-settings.php';
            require_once DOKAN_LIVE_CHAT_INC . '/public/class-live-chat-start.php';
            require_once DOKAN_LIVE_CHAT_INC . '/public/class-seller-inbox.php';
            require_once DOKAN_LIVE_CHAT_INC . '/public/class-customer-inbox.php';
        }
    }

    /**
     * Know the request type
     *
     * @param  string $type
     *
     * @since 1.0
     *
     * @return boolean
     */
    public function request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'public':
                return ! is_admin() && ! wp_doing_ajax();
            case 'public_or_ajax':
                return ! is_admin() || wp_doing_ajax();
        }
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

    /**
     * Return single instance of this class
     *
     * @since 1.0
     *
     * @return object;
     */
    public static function init() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
