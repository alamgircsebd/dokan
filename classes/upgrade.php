<?php
/**
 * Dokan Upgrade class
 *
 * Performas upgrade dokan latest version
 *
 * @since 2.1
 *
 * @package Dokan
 */
class Dokan_Upgrade {

    /**
     * Constructor loader function
     *
     * Load autometically when class instantiate.
     *
     * @since 1.0
     */
    function __construct() {
        add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
        add_action( 'admin_init', array( $this, 'upgrade_action_perform' ) );
    }

    /**
     * Upgrade Notice display function
     *
     * @since 1.0
     *
     * @return void
     */
    public function upgrade_notice () {
        $installed_version = get_option( 'dokan_theme_version' );

        // may be it's the first install
        if ( ! $installed_version ) {
            return false;
        }

        if ( version_compare( $installed_version, DOKAN_PLUGIN_VERSION , '<' ) ) {
            ?>
                <div class="update-nag" style="width:94%;">
                    <p><?php _e( 'Please click the button below to enable the new features in this latest version of dokan plguin. The new features will not work untill you click this button.', 'dokan' ) ?></p>

                    <form action="" method="post">
                        <input type="submit" class="button button-default" name="dokan_upgrade_plugin" value="Upgrade">
                        <?php wp_nonce_field( 'dokan_upgrade_action', 'dokan_upgrade_action_nonce' ); ?>
                    </form>
                </div>
            <?php
        }
    }

    /**
     * Upgrade action
     *
     * @since 1.0
     *
     * @return void
     */
    function upgrade_action_perform() {

        if ( !is_admin() ) {
            return;
        }

        if( !isset( $_POST['dokan_upgrade_action_nonce'] ) ) {
            return;
        }

        if ( !wp_verify_nonce( $_POST['dokan_upgrade_action_nonce'], 'dokan_upgrade_action' ) ) {
            return;
        }

        if ( !isset( $_POST['dokan_upgrade_plugin'] ) ) {
            return;
        }

        $installed_version = get_option( 'dokan_theme_version' );

        if ( version_compare( $installed_version, DOKAN_PLUGIN_VERSION , '<' ) ) {

            $redirect_url = $_SERVER['HTTP_REFERER'];

            $dokan_installer = new Dokan_Installer();

            $dokan_installer->create_announcement_table();

            update_option( 'dokan_theme_version', DOKAN_PLUGIN_VERSION );

            wp_safe_redirect( $redirect_url );
        }

    }

}