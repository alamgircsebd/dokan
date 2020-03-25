<div class="updated" id="dokan-pro-installer-notice" style="padding: 1em; position: relative;">
    <h2><?php _e( 'Your Dokan Pro is almost ready!', 'dokan' ); ?></h2>

    <?php
    $plugin_file      = basename( DOKAN_PRO_DIR ) . '/dokan-pro.php';
    $core_plugin_file = 'dokan-lite/dokan.php';
    ?>
    <a href="<?php echo wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_' . $plugin_file ); ?>" class="notice-dismiss" style="text-decoration: none;" title="<?php _e( 'Dismiss this notice', 'dokan' ); ?>"></a>

    <?php if ( file_exists( WP_PLUGIN_DIR . '/' . $core_plugin_file ) && is_plugin_inactive( 'dokan-lite' ) ): ?>
        <p><?php echo sprintf( __( 'You just need to activate the <strong>%s</strong> to make it functional.', 'dokan' ), 'Dokan (Lite) - Multi-vendor Marketplace plugin' ); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $core_plugin_file . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $core_plugin_file ); ?>"  title="<?php _e( 'Activate this plugin', 'dokan' ); ?>"><?php _e( 'Activate', 'dokan' ); ?></a>
        </p>
    <?php else: ?>
        <p><?php echo sprintf( __( "You just need to install the %sCore Plugin%s to make it functional.", "dokan" ), '<a target="_blank" href="https://wordpress.org/plugins/dokan-lite/">', '</a>' ); ?></p>

        <p>
            <button id="dokan-pro-installer" class="button"><?php _e( 'Install Now', 'dokan' ); ?></button>
        </p>
    <?php endif ?>
</div>

<script type="text/javascript">
    ( function ( $ ) {
        $( '#dokan-pro-installer-notice #dokan-pro-installer' ).click( function ( e ) {
            e.preventDefault();
            $( this ).addClass( 'install-now updating-message' );
            $( this ).text( '<?php echo esc_js( 'Installing...', 'dokan' ); ?>' );

            var data = {
                action: 'dokan_pro_install_dokan_lite',
                _wpnonce: '<?php echo wp_create_nonce( 'dokan-pro-installer-nonce' ); ?>'
            };

            $.post( ajaxurl, data, function ( response ) {
                if ( response.success ) {
                    $( '#dokan-pro-installer-notice #dokan-pro-installer' ).attr( 'disabled', 'disabled' );
                    $( '#dokan-pro-installer-notice #dokan-pro-installer' ).removeClass( 'install-now updating-message' );
                    $( '#dokan-pro-installer-notice #dokan-pro-installer' ).text( '<?php echo esc_js( 'Installed', 'dokan' ); ?>' );
                    window.location.reload();
                }
            } );
        } );
    } )( jQuery );
</script>
