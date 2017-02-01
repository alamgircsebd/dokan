<?php
/*
Plugin Name: Dokan - Multi-vendor Marketplace (pro)
Plugin URI: https://wedevs.com/products/plugins/dokan/
Description: An e-commerce marketplace plugin for WordPress. Powered by WooCommerce and weDevs.
Version: 2.5.1
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
TextDomain: dokan-pro
*/

/**
 * Dokan Pro Feature Loader
 *
 * Load all pro functionality in this class
 * if pro folder exist then autometically load this class file
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
 */

class Dokan_Pro {

    /**
     * Constructor for the Dokan_Pro class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {
        if ( ! class_exists( 'WeDevs_Dokan' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            add_action( 'admin_notices', array( $this, 'activation_notice' ) );
            add_action( 'wp_ajax_dokan_pro_install_dokan_lite', array( $this, 'install_dokan_lite' ) );
            return;
        }

        $this->defined();

        spl_autoload_register( array( $this, 'dokan_pro_autoload' ) );

        $this->includes();

        $this->inistantiate();

        $this->load_actions();

        $this->load_filters();
    }

        /**
    * Dokan main plugin activation notice
    *
    * @since 2.5.2
    *
    * @return void
    **/
    public function activation_notice() {
        ?>
        <div class="updated" id="dokan-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php _e( 'Your Dokan Pro is almost ready!', 'dokan-pro' ); ?></h2>

            <?php
                $plugin_file = 'dokan-pro/dokan-pro.php';
                $core_plugin_file = 'dokan-lite/dokan.php';
            ?>
            <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'dokan-pro' ); ?>"></a>

            <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'dokan-lite' ) ): ?>
                <p><?php _e( 'You just need to activate the Dokan lite plugin to make it functional.', 'dokan-pro' ); ?></p>
                <p>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php _e( 'Activate this plugin', 'dokan-pro' ); ?>"><?php _e( 'Activate', 'dokan-pro' ); ?></a>
                </p>
            <?php else: ?>
                <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "dokan-pro" ), '<a target="_blank" href="https://wordpress.org/plugins/dokan-lite/">', '</a>' ); ?></p>

                <p>
                    <button id="dokan-pro-installer" class="button"><?php _e( 'Install Now', 'dokan-pro' ); ?></button>
                </p>
            <?php endif ?>
        </div>

        <script type="text/javascript">
            (function ($) {
                $('#dokan-pro-installer-notice #dokan-pro-installer').click( function (e) {
                    e.preventDefault();
                    $(this).addClass('install-now updating-message');
                    $(this).text('<?php echo esc_js( 'Installing...', 'dokan-pro' ); ?>');

                    var data = {
                        action: 'dokan_pro_install_dokan_lite',
                        _wpnonce: '<?php echo wp_create_nonce('dokan-pro-installer-nonce'); ?>'
                    };

                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            $('#dokan-pro-installer-notice #dokan-pro-installer').attr('disabled', 'disabled');
                            $('#dokan-pro-installer-notice #dokan-pro-installer').removeClass('install-now updating-message');
                            $('#dokan-pro-installer-notice #dokan-pro-installer').text('<?php echo esc_js( 'Installed', 'dokan-pro' ); ?>');
                            window.location.reload();
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
    * Install dokan lite
    *
    * @since 2.5.2
    *
    * @return void
    **/
    public function install_dokan_lite() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'dokan-pro-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan-pro' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'dokan-lite';
        $api    = plugins_api( 'plugin_information', array( 'slug' => $plugin, 'fields' => array( 'sections' => false ) ) );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        activate_plugin( 'dokan-lite/dokan.php' );

        wp_send_json_success();
    }


    public function defined() {
        define( 'DOKAN_PRO_DIR', dirname( __FILE__) );
        define( 'DOKAN_PRO_ADMIN_DIR', dirname( __FILE__).'/admin' );
        define( 'DOKAN_PRO_INC', dirname( __FILE__).'/includes' );
        define( 'DOKAN_PRO_CLASS', dirname( __FILE__).'/classes' );
    }

    /**
     * Load all includes file for pro
     *
     * @since 2.4
     *
     * @return void
     */
    public function includes() {

        if ( is_admin() ) {
            require_once DOKAN_PRO_ADMIN_DIR . '/admin.php';
            require_once DOKAN_PRO_ADMIN_DIR . '/ajax.php';
            require_once DOKAN_PRO_ADMIN_DIR . '/announcement.php';
            require_once DOKAN_PRO_ADMIN_DIR . '/shortcode-button.php';
            require_once DOKAN_PRO_CLASS . '/update.php';
        }

        require_once DOKAN_PRO_INC . '/functions.php';
        require_once DOKAN_PRO_INC . '/widgets/best-seller.php';
        require_once DOKAN_PRO_INC . '/widgets/feature-seller.php';
        require_once DOKAN_PRO_CLASS . '/store-seo.php';

    }

    /**
     * Inistantiate all classes
     *
     * @since 2.4
     *
     * @return void
     */
    public function inistantiate() {

        if ( is_admin() ) {
            Dokan_Pro_Admin_Ajax::init();
            new Dokan_Pro_Admin_Settings();
            new Dokan_Announcement();
            new Dokan_Update();
        }

        Dokan_Pro_Ajax::init();
        Dokan_Pro_Shipping::init();

        if ( is_user_logged_in() ) {
            Dokan_Pro_Dashboard::init();
            Dokan_Pro_Products::init();
            Dokan_Pro_Coupons::init();
            Dokan_Pro_Reviews::init();
            Dokan_Pro_Reports::init();
            Dokan_Pro_Withdraws::init();
            Dokan_Pro_Settings::init();
            Dokan_Pro_Notice::init();
            Dokan_Pro_Refund::init();
        }

        Dokan_Pro_Store::init();
    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 2.4
     *
     * @return void [description]
     */
    public function load_actions() {
        add_action( 'woocommerce_after_my_account', array( $this, 'dokan_account_migration_button' ) );
    }

    /**
     * Load all Filters Hook
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_filters() {
        add_filter( 'dokan_query_var_filter', array( $this, 'load_query_var' ), 10 );
        add_filter( 'woocommerce_locate_template', array( $this, 'account_migration_template' ) );
    }

    /**
     * Required all class files inside Pro
     *
     * @since 2.4
     *
     * @param  string $class
     *
     * @return void
     */
    public function dokan_pro_autoload( $class ) {
        if ( stripos( $class, 'Dokan_Pro_' ) !== false ) {
            $class_name = str_replace( array( 'Dokan_Pro_', '_' ), array( '', '-' ), $class );
            $file_path =  DOKAN_PRO_CLASS . '/' . strtolower( $class_name ) . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }

    /**
     * Load Pro rewirite query vars
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return array
     */
    public function load_query_var( $query_vars ) {
        $query_vars[] = 'coupons';
        $query_vars[] = 'reports';
        $query_vars[] = 'reviews';
        $query_vars[] = 'announcement';
        $query_vars[] = 'single-announcement';
        $query_vars[] = 'account-migration';

        return $query_vars;
    }

    /**
     * Dokan Account Migration Button render
     *
     * @since 2.4
     *
     * @return void
     */
    function dokan_account_migration_button() {
        $user = wp_get_current_user();

        if ( dokan_is_user_customer( $user->ID ) ) {
            dokan_get_template_part( 'global/account-migration-btn', '', array( 'pro' => true ) );
        }
    }

    /**
     * Account migration template on my account
     *
     * @param string  $file path of the template
     *
     * @return string
     */
    function account_migration_template( $file ) {

        if ( get_query_var( 'account-migration' ) && dokan_is_user_customer( get_current_user_id() ) && basename( $file ) == 'my-account.php' ) {
            $file = dokan_locate_template( 'global/update-account.php', '', '', true );
        }

        return $file;
    }


}

new Dokan_Pro();