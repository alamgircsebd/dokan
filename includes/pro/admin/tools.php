<?php
/**
 * Dokan Admin Dashboard Tools Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<div class="wrap">
    <h2><?php _e( 'Dokan Tools', 'dokan' ); ?></h2>

    <?php
    $msg = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
    $text = '';

    switch ($msg) {
        case 'page_installed':
            $text = __( 'Pages have been created and installed!', 'dokan' );
            break;

        case 'regenerated':
            $text = __( 'Order sync table has been regenerated!', 'dokan' );
            break;
    }

    if ( $text ) {
        ?>
        <div class="updated">
            <p>
                <?php echo $text; ?>
            </p>
        </div>

    <?php } ?>

    <style type="text/css">
        .regen-sync-response span {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid transparent;
            border-radius: 4px;
            display: block;
        }
        .regen-sync-loader {
            background: url('<?php echo admin_url( 'images/spinner-2x.gif') ?>') no-repeat;
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: cover;
        }
    </style>
    <script type="text/javascript">
        jQuery(function($) {
            $('form#regen-sync-table').on('submit', function(e) {
                e.preventDefault();
                var form = $(this),
                    submit = form.find('input[type=submit]'),
                    loader = form.find('.regen-sync-loader');
                    responseDiv = $('.regen-sync-response');

                    // ajaxdir = "<?php admin_url( 'admin-ajax.php'); ?>";

                submit.attr('disabled', 'disabled');
                loader.show();

                $.post( ajaxurl, form.serialize(), function(resp) {
                    if ( resp.success ) {
                        responseDiv.html( '<span>' + resp.data.message + '</span>' );
                        if ( resp.data.done != 'All' ) {
                            form.find('input[name="offset"]').val( resp.data.offset );
                            form.submit();
                            return;
                        } else {
                            submit.removeAttr('disabled');
                            loader.hide();
                            responseDiv.html('');
                            // window.location.reload();
                        }
                    }
                });
            })
        });
    </script>

    <div class="metabox-holder">
        <div class="postbox">
            <h3><?php _e( 'Page Installation', 'dokan' ); ?></h3>

            <div class="inside">
                <p><?php _e( 'Clicking this button will create required pages for the plugin.', 'dokan' ); ?></p>
                <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'dokan_action' => 'dokan_install_pages' ), 'admin.php?page=dokan-tools' ), 'dokan-tools-action' ); ?>"><?php _e( 'Install Dokan Pages', 'dokan' ); ?></a>
            </div>
        </div>

        <div class="postbox">
            <h3><?php _e( 'Regenerate Order Sync Table', 'dokan' ); ?></h3>

            <div class="inside">
                <p><?php _e( 'This tool will delete all orders from the Dokan\'s sync table and re-build it.', 'dokan' ); ?></p>
                <div class="regen-sync-response"></div>
                <form id="regen-sync-table" action="" method="post">
                    <?php wp_nonce_field( 'regen_sync_table' ); ?>
                    <input type="hidden" name="limit" value="<?php echo apply_filters( 'regen_sync_table_limit', 100 ); ?>">
                    <input type="hidden" name="offset" value="0">
                    <input type="hidden" name="action" value="regen_sync_table">
                    <input type="submit" class="button button-primary" value="<?php _e( 'Re-build', 'dokan' ); ?>" >
                    <span class="regen-sync-loader" style="display:none"></span>
                </form>
            </div>
        </div>
    </div>
</div>