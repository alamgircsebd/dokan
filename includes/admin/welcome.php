<?php
/**
 * Dokan Admin Dashboard Welcome Template
 *
 * @since 2.4.3
 *
 * @package dokan
 */
?>

<div class="wrap about-wrap">
    <h1><?php echo __( 'Welcome to Dokan', 'dokan' ) . ' ' . DOKAN_PLUGIN_VERSION; ?></h1>

    <div class="about-text">
        <?php _e( 'Thank you for installing Dokan!', 'dokan' ); ?>
        <?php printf( __( 'Dokan %s makes it even easier to start your own multivendor marketplace.', 'dokan' ), DOKAN_PLUGIN_VERSION ); ?>
    </div>


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

        #progressbar {
            background-color: #EEE;
            border-radius: 13px; /* (height of inner div) / 2 + padding */
            padding: 3px;
            margin-bottom : 20px;
        }

        #regen-pro {
            background-color: #00A0D2;
            width: 0%; /* Adjust with JavaScript */
            height: 20px;
            border-radius: 10px;
            text-align: center;
            color:#FFF;
        }
    </style>
    <script type="text/javascript">
        jQuery(function($) {
            var total_orders = 0;
            $('form#regen-sync-table').on('submit', function(e) {
                e.preventDefault();

                var form = $(this),
                    submit = form.find('input[type=submit]'),
                    loader = form.find('.regen-sync-loader');
                    responseDiv = $('.regen-sync-response');


                    // ajaxdir = "<?php admin_url( 'admin-ajax.php'); ?>";

                submit.attr('disabled', 'disabled');
                loader.show();

                var s_data = {
                    data: form.serialize(),
                    action : 'regen_sync_table',
                    total_orders : total_orders
                };

                $.post( ajaxurl, s_data, function(resp) {

                    if ( resp.success ) {
                        if( resp.data.total_orders != 0 ){
                            total_orders = resp.data.total_orders;
                        }
                        completed = (resp.data.done*100)/total_orders;

                        completed = Math.round(completed);

                        $('#regen-pro').width(completed+'%');
                        if(!$.isNumeric(completed)){
                            $('#regen-pro').html('Finished');
                        }else{
                            $('#regen-pro').html(completed+'%');
                        }

                        $('#progressbar').show();


                        responseDiv.html( '<span>' + resp.data.message +'</span>' );

                        if ( resp.data.done != 'All' ) {
                            form.find('input[name="offset"]').val( resp.data.offset );
                            form.submit();
                            return;
                        } else {
                            submit.removeAttr('disabled');
                            loader.hide();
                            form.find('input[name="offset"]').val( 0 );
                            //responseDiv.html('');
                            // window.location.reload();
                        }
                    }
                });
            })
        });
    </script>

    <div class="metabox-holder">
        <div class="postbox">
            <h3 style="margin: 0;"><?php _e( 'Order Synchronization', 'dokan' ); ?></h3>

            <div class="inside">
                <p><?php _e( 'Seems like you have some unsynchronized orders. This tool will re-build Dokan\'s sync table.', 'dokan' ); ?></p>
                <p><?php _e( 'Don\'t worry, any existing orders will not be deleted.', 'dokan' ); ?></p>

                <div class="regen-sync-response"></div>
                <div id="progressbar" style="display: none"><div id="regen-pro">0</div></div>

                <form id="regen-sync-table" action="" method="post">
                    <?php wp_nonce_field( 'regen_sync_table' ); ?>
                    <input type="hidden" name="limit" value="<?php echo apply_filters( 'regen_sync_table_limit', 100 ); ?>">
                    <input type="hidden" name="offset" value="0">

                    <a href="<?php echo admin_url( 'admin.php?page=dokan' ); ?>" class="button"><?php _e( 'Skip', 'dokan' ); ?></a>
                    <input id="btn-rebuild" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Synchronize Orders', 'dokan' ); ?>" >
                    <span class="regen-sync-loader" style="display:none"></span>
                </form>
            </div>
        </div>
    </div>
</div>