<?php

function dokan_save_variations( $post_id ) {
    global $woocommerce, $wpdb;

    $attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );
    update_post_meta( $post_id, '_create_variation', 'yes' );

    if ( isset( $_POST['variable_sku'] ) ) {

        $variable_post_id               = $_POST['variable_post_id'];
        $variable_sku                   = $_POST['variable_sku'];
        $variable_regular_price         = $_POST['variable_regular_price'];
        $variable_sale_price            = $_POST['variable_sale_price'];
        $upload_image_id                = $_POST['upload_image_id'];
        $variable_download_limit        = $_POST['variable_download_limit'];
        $variable_download_expiry       = $_POST['variable_download_expiry'];
        $variable_shipping_class        = $_POST['variable_shipping_class'];
        $variable_tax_class             = isset( $_POST['variable_tax_class'] ) ? $_POST['variable_tax_class'] : array();
        $variable_menu_order            = $_POST['variation_menu_order'];
        $variable_sale_price_dates_from = $_POST['variable_sale_price_dates_from'];
        $variable_sale_price_dates_to   = $_POST['variable_sale_price_dates_to'];

        $variable_weight          = isset( $_POST['variable_weight'] ) ? $_POST['variable_weight'] : array();
        $variable_length          = isset( $_POST['variable_length'] ) ? $_POST['variable_length'] : array();
        $variable_width           = isset( $_POST['variable_width'] ) ? $_POST['variable_width'] : array();
        $variable_height          = isset( $_POST['variable_height'] ) ? $_POST['variable_height'] : array();
        $variable_enabled         = isset( $_POST['variable_enabled'] ) ? $_POST['variable_enabled'] : array();
        $variable_is_virtual      = isset( $_POST['variable_is_virtual'] ) ? $_POST['variable_is_virtual'] : array();
        $variable_is_downloadable = isset( $_POST['variable_is_downloadable'] ) ? $_POST['variable_is_downloadable'] : array();

        $variable_manage_stock = isset( $_POST['variable_manage_stock'] ) ? $_POST['variable_manage_stock'] : array();
        $variable_stock        = isset( $_POST['variable_stock'] ) ? $_POST['variable_stock'] : array();
        $variable_backorders   = isset( $_POST['variable_backorders'] ) ? $_POST['variable_backorders'] : array();
        $variable_stock_status = isset( $_POST['variable_stock_status'] ) ? $_POST['variable_stock_status'] : array();

        $variable_description = isset( $_POST['variable_description'] ) ? $_POST['variable_description'] : array();

        $max_loop = max( array_keys( $_POST['variable_post_id'] ) );

        for ( $i = 0; $i <= $max_loop; $i ++ ) {

            if ( !isset( $variable_post_id[$i] ) ) {
                continue;
            }

            $variation_id = absint( $variable_post_id[$i] );

            // Checkboxes
            $is_virtual      = isset( $variable_is_virtual[$i] ) ? 'yes' : 'no';
            $is_downloadable = isset( $variable_is_downloadable[$i] ) ? 'yes' : 'no';
            $post_status     = isset( $variable_enabled[$i] ) ? 'publish' : 'private';
            $manage_stock    = isset( $variable_manage_stock[$i] ) ? 'yes' : 'no';

            // Update or Add post
            if ( !$variation_id ) {

                $variation = array(
                    'post_content' => '',
                    'post_status'  => $post_status,
                    'post_author'  => get_current_user_id(),
                    'post_parent'  => $post_id,
                    'post_type'    => 'product_variation',
                    'menu_order'   => $variable_menu_order[$i]
                );

                $variation_id = wp_insert_post( $variation );

                do_action( 'woocommerce_create_product_variation', $variation_id );
                do_action( 'dokan_create_product_variation', $variation_id );
            } else {

                $modified_date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

                $wpdb->update( $wpdb->posts, array(
                    'post_status'       => $post_status,
                    'menu_order'        => $variable_menu_order[$i],
                    'post_modified'     => $modified_date,
                    'post_modified_gmt' => get_gmt_from_date( $modified_date ),
                ), array( 'ID' => $variation_id ) );

                clean_post_cache( $variation_id );

                do_action( 'woocommerce_update_product_variation', $variation_id );
                do_action( 'dokan_update_product_variation', $variation_id );
            }

            // Only continue if we have a variation ID
            if ( !$variation_id ) {
                continue;
            }

            // Unique SKU
            $sku     = get_post_meta( $variation_id, '_sku', true );
            $new_sku = wc_clean( $variable_sku[$i] );

            if ( '' == $new_sku ) {
                update_post_meta( $variation_id, '_sku', '' );
            } elseif ( $new_sku !== $sku ) {
                if ( !empty( $new_sku ) ) {
                    $unique_sku = wc_product_has_unique_sku( $variation_id, $new_sku );

                    if ( !$unique_sku ) {
                        $woocommerce_errors[] = sprintf( __( '#%s &ndash; Variation SKU must be unique.', 'dokan' ), $variation_id );
                    } else {
                        update_post_meta( $variation_id, '_sku', $new_sku );
                    }
                } else {
                    update_post_meta( $variation_id, '_sku', '' );
                }
            }

            // Update post meta
            update_post_meta( $variation_id, '_thumbnail_id', absint( $upload_image_id[$i] ) );
            update_post_meta( $variation_id, '_virtual', wc_clean( $is_virtual ) );
            update_post_meta( $variation_id, '_downloadable', wc_clean( $is_downloadable ) );

            if ( isset( $variable_weight[$i] ) ) {
                update_post_meta( $variation_id, '_weight', ( '' === $variable_weight[$i] ) ? '' : wc_format_decimal( $variable_weight[$i] )  );
            }

            if ( isset( $variable_length[$i] ) ) {
                update_post_meta( $variation_id, '_length', ( '' === $variable_length[$i] ) ? '' : wc_format_decimal( $variable_length[$i] )  );
            }

            if ( isset( $variable_width[$i] ) ) {
                update_post_meta( $variation_id, '_width', ( '' === $variable_width[$i] ) ? '' : wc_format_decimal( $variable_width[$i] )  );
            }

            if ( isset( $variable_height[$i] ) ) {
                update_post_meta( $variation_id, '_height', ( '' === $variable_height[$i] ) ? '' : wc_format_decimal( $variable_height[$i] )  );
            }

            // Stock handling
            update_post_meta( $variation_id, '_manage_stock', $manage_stock );

            if ( 'yes' === $manage_stock ) {
                update_post_meta( $variation_id, '_backorders', wc_clean( $variable_backorders[$i] ) );
                wc_update_product_stock( $variation_id, wc_stock_amount( $variable_stock[$i] ) );
            } else {
                delete_post_meta( $variation_id, '_backorders' );
                wc_update_product_stock( $variation_id, '' );
            }

            // Only update stock status to user setting if changed by the user, but do so before looking at stock levels at variation level
            if ( !empty( $variable_stock_status[$i] ) ) {
                wc_update_product_stock_status( $variation_id, $variable_stock_status[$i] );
            }

            // Price handling
            dokan_save_product_price( $variation_id, $variable_regular_price[$i], $variable_sale_price[$i], $variable_sale_price_dates_from[$i], $variable_sale_price_dates_to[$i] );

            if ( isset( $variable_tax_class[$i] ) && 'parent' !== $variable_tax_class[$i] ) {
                update_post_meta( $variation_id, '_tax_class', wc_clean( $variable_tax_class[$i] ) );
            } else {
                delete_post_meta( $variation_id, '_tax_class' );
            }

            if ( $is_downloadable == 'yes' ) {
                update_post_meta( $variation_id, '_download_limit', wc_clean( $variable_download_limit[$i] ) );
                update_post_meta( $variation_id, '_download_expiry', wc_clean( $variable_download_expiry[$i] ) );

                $files         = array();
                $file_names    = isset( $_POST['_wc_variation_file_names'][$variation_id] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_names'][$variation_id] ) : array();
                $file_urls     = isset( $_POST['_wc_variation_file_urls'][$variation_id] ) ? array_map( 'esc_url_raw', array_map( 'trim', $_POST['_wc_variation_file_urls'][$variation_id] ) ) : array();
                $file_url_size = sizeof( $file_urls );

                for ( $ii = 0; $ii < $file_url_size; $ii ++ ) {
                    if ( !empty( $file_urls[$ii] ) )
                        $files[md5( $file_urls[$ii] )] = array(
                            'name' => $file_names[$ii],
                            'file' => $file_urls[$ii]
                        );
                }


                // grant permission to any newly added files on any existing orders for this product prior to saving
                do_action( 'dokan_process_file_download', $post_id, $variation_id, $files );
                update_post_meta( $variation_id, '_downloadable_files', $files );
            } else {
                update_post_meta( $variation_id, '_download_limit', '' );
                update_post_meta( $variation_id, '_download_expiry', '' );
                update_post_meta( $variation_id, '_downloadable_files', '' );
            }

            // Update variation description
            update_post_meta( $variation_id, '_variation_description', wp_kses_post( $variable_description[$i] ) );

            // Save shipping class
            $variable_shipping_class[$i] = !empty( $variable_shipping_class[$i] ) ? (int) $variable_shipping_class[$i] : '';
            wp_set_object_terms( $variation_id, $variable_shipping_class[$i], 'product_shipping_class' );

            // Update Attributes
            $updated_attribute_keys = array();
            foreach ( $attributes as $attribute ) {
                if ( $attribute['is_variation'] ) {
                    $attribute_key            = 'attribute_' . sanitize_title( $attribute['name'] );
                    $updated_attribute_keys[] = $attribute_key;

                    if ( $attribute['is_taxonomy'] ) {
                        // Don't use wc_clean as it destroys sanitized characters
                        $value = isset( $_POST[$attribute_key][$i] ) ? sanitize_title( stripslashes( $_POST[$attribute_key][$i] ) ) : '';
                    } else {
                        $value = isset( $_POST[$attribute_key][$i] ) ? wc_clean( stripslashes( $_POST[$attribute_key][$i] ) ) : '';
                    }

                    update_post_meta( $variation_id, $attribute_key, $value );
                }
            }

            // Remove old taxonomies attributes so data is kept up to date - first get attribute key names
            $delete_attribute_keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND meta_key NOT IN ( '" . implode( "','", $updated_attribute_keys ) . "' ) AND post_id = %d;", $variation_id ) );

            foreach ( $delete_attribute_keys as $key ) {
                delete_post_meta( $variation_id, $key );
            }

            do_action( 'woocommerce_save_product_variation', $variation_id, $i );
            do_action( 'dokan_save_product_variation', $variation_id, $i );
        }
    }

    // Update parent if variable so price sorting works and stays in sync with the cheapest child
    WC_Product_Variable::sync( $post_id );

    // Update default attribute options setting
    $default_attributes = array();

    foreach ( $attributes as $attribute ) {
        if ( $attribute['is_variation'] ) {
            $value = '';

            if ( isset( $_POST['default_attribute_' . sanitize_title( $attribute['name'] )] ) ) {
                if ( $attribute['is_taxonomy'] ) {
                    // Don't use wc_clean as it destroys sanitized characters
                    $value = sanitize_title( trim( stripslashes( $_POST['default_attribute_' . sanitize_title( $attribute['name'] )] ) ) );
                } else {
                    $value = wc_clean( trim( stripslashes( $_POST['default_attribute_' . sanitize_title( $attribute['name'] )] ) ) );
                }
            }

            if ( $value ) {
                $default_attributes[sanitize_title( $attribute['name'] )] = $value;
            }
        }
    }

    update_post_meta( $post_id, '_default_attributes', $default_attributes );
}

/**
 * Show the variable product options.
 *
 * @access public
 * @return void
 */
function dokan_variable_product_type_options() {
    global $post, $woocommerce;

    $attributes = maybe_unserialize( get_post_meta( $post->ID, '_product_attributes', true ) );

    // See if any are set
    $variation_attribute_found = false;
    if ( $attributes )
        foreach ( $attributes as $attribute ) {
            if ( isset( $attribute['is_variation'] ) ) {
                $variation_attribute_found = true;
                break;
            }
        }

    // Get tax classes
    if ( class_exists( 'WC_Tax' ) ) {
        $tax_classes = WC_Tax::get_tax_classes();
    } else {
        $tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
    }

    $tax_class_options     = [];
    $tax_class_options[''] = __( 'Standard', 'dokan' );

    if ( $tax_classes ) {
        foreach ( $tax_classes as $class ) {
            $tax_class_options[sanitize_title( $class )] = esc_attr( $class );
        }
    }
    ?>
    <div id="variable_product_options" class="wc-metaboxes-wrapper">
        <div id="variable_product_options_inner">

            <?php if ( !$variation_attribute_found ) : ?>

                <div id="message" class="inline woocommerce-message">
                    <div class="squeezer">
                        <h4><?php _e( 'Before adding variations, add and save some attributes on the <strong>Attributes</strong> tab.', 'dokan' ); ?></h4>

                        <p class="submit"><a class="button-primary" href="http://docs.woothemes.com/document/product-variations/" target="_blank"><?php _e( 'Learn more', 'dokan' ); ?></a></p>
                    </div>
                </div>

            <?php else : ?>

                <div class="woocommerce_variations wc-metaboxes">
                    <?php
                    // Get parent data
                    $parent_data = array(
                        'id'                => $post->ID,
                        'attributes'        => $attributes,
                        'tax_class_options' => $tax_class_options,
                        'sku'               => get_post_meta( $post->ID, '_sku', true ),
                        'weight'            => get_post_meta( $post->ID, '_weight', true ),
                        'length'            => get_post_meta( $post->ID, '_length', true ),
                        'width'             => get_post_meta( $post->ID, '_width', true ),
                        'height'            => get_post_meta( $post->ID, '_height', true ),
                        'tax_class'         => get_post_meta( $post->ID, '_tax_class', true )
                    );

                    if ( !$parent_data['weight'] )
                        $parent_data['weight'] = '0.00';

                    if ( !$parent_data['length'] )
                        $parent_data['length'] = '0';

                    if ( !$parent_data['width'] )
                        $parent_data['width'] = '0';

                    if ( !$parent_data['height'] )
                        $parent_data['height'] = '0';

                    // Get variations
                    $args       = array(
                        'post_type'   => 'product_variation',
                        'post_status' => array( 'private', 'publish' ),
                        'numberposts' => -1,
                        'orderby'     => 'menu_order',
                        'order'       => 'asc',
                        'post_parent' => $post->ID
                    );
                    $variations = get_posts( $args );
                    $loop       = 0;

                    if ( $variations ) {

                        foreach ( $variations as $variation ) {

                            $variation_id                        = absint( $variation->ID );
                            $variation_post_status               = esc_attr( $variation->post_status );
                            $variation_data                      = get_post_meta( $variation_id );
                            $variation_data['variation_post_id'] = $variation_id;

                            // Grab shipping classes
                            $shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
                            $shipping_class   = ( $shipping_classes && !is_wp_error( $shipping_classes ) ) ? current( $shipping_classes )->term_id : '';

                            $variation_fields = array(
                                '_sku',
                                '_stock',
                                '_manage_stock',
                                '_stock_status',
                                '_regular_price',
                                '_sale_price',
                                '_weight',
                                '_length',
                                '_width',
                                '_height',
                                '_download_limit',
                                '_download_expiry',
                                '_downloadable_files',
                                '_downloadable',
                                '_virtual',
                                '_thumbnail_id',
                                '_sale_price_dates_from',
                                '_sale_price_dates_to',
                                '_variation_description'
                            );

                            foreach ( $variation_fields as $field ) {
                                $$field = isset( $variation_data[$field][0] ) ? maybe_unserialize( $variation_data[$field][0] ) : '';
                            }

                            $_backorders = isset( $variation_data['_backorders'][0] ) ? $variation_data['_backorders'][0] : null;

                            $_tax_class = isset( $variation_data['_tax_class'][0] ) ? $variation_data['_tax_class'][0] : null;
                            $image_id   = absint( $_thumbnail_id );
                            $image      = $image_id ? wp_get_attachment_thumb_url( $image_id ) : '';

                            // Locale formatting
                            $_regular_price = wc_format_localized_price( $_regular_price );
                            $_sale_price    = wc_format_localized_price( $_sale_price );
                            $_weight        = wc_format_localized_decimal( $_weight );
                            $_length        = wc_format_localized_decimal( $_length );
                            $_width         = wc_format_localized_decimal( $_width );
                            $_height        = wc_format_localized_decimal( $_height );

                            // Stock BW compat
                            if ( '' !== $_stock ) {
                                $_manage_stock = 'yes';
                            }

                            include DOKAN_PRO_INC . '/woo-views/variation-admin-html.php';

                            $loop++;
                        }
                    }
                    ?>
                </div> <!-- .woocommerce_variations -->

                <p class="toolbar">

                    <button type="button" class="dokan-btn dokan-btn-sm dokan-btn-success button-primary add_variation" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Add Variation', 'dokan' ); ?></button>

                    <button type="button" class="dokan-btn dokan-btn-sm dokan-btn-default link_all_variations" <?php disabled( $variation_attribute_found, false ); ?>><?php _e( 'Link all variations', 'dokan' ); ?></button>

                    <strong><?php _e( 'Default selections:', 'dokan' ); ?></strong>
        <?php
        $default_attributes = maybe_unserialize( get_post_meta( $post->ID, '_default_attributes', true ) );
        foreach ( $attributes as $attribute ) {

            // Only deal with attributes that are variations
            if ( !$attribute['is_variation'] ) {
                continue;
            }

            // Get current value for variation (if set)
            $variation_selected_value = isset( $default_attributes[sanitize_title( $attribute['name'] )] ) ? $default_attributes[sanitize_title( $attribute['name'] )] : '';

            // Name will be something like attribute_pa_color
            echo '<select name="default_attribute_' . sanitize_title( $attribute['name'] ) . '" data-current="' . esc_attr( $variation_selected_value ) . '"><option value="">' . __( 'No default', 'dokan' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

            // Get terms for attribute taxonomy or value if its a custom attribute
            if ( $attribute['is_taxonomy'] ) {

                $post_terms = wp_get_post_terms( $post->ID, $attribute['name'] );

                foreach ( $post_terms as $term )
                    echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
            } else {
                $options = wc_get_text_attributes( $attribute['value'] );

                foreach ( $options as $option ) {
                    $selected = sanitize_title( $variation_selected_value ) === $variation_selected_value ? selected( $variation_selected_value, sanitize_title( $option ), false ) : selected( $variation_selected_value, $option, false );
                    echo '<option ' . $selected . ' value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
                }
            }

            echo '</select>';
        }
        ?>
                </p> <!-- .toolbar -->

                <?php endif; ?>
        </div>
    </div>
            <?php
            /**
             * Product Type Javascript
             */
            ob_start();
            ?>
    jQuery(function($){

    var variation_sortable_options = {
    items:'.woocommerce_variation',
    cursor:'move',
    axis:'y',
    handle: 'h3',
    scrollSensitivity:40,
    forcePlaceholderSize: true,
    helper: 'clone',
    opacity: 0.65,
    placeholder: 'wc-metabox-sortable-placeholder',
    start:function(event,ui){
    ui.item.css('background-color','#f6f6f6');
    },
    stop:function(event,ui){
    ui.item.removeAttr('style');
    variation_row_indexes();
    }
    };

    // Add a variation
    jQuery('#variable_product_options').on('click', 'button.add_variation', function(){

    jQuery('.woocommerce_variations').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

    var loop = jQuery('.woocommerce_variation').size();

    var data = {
    action: 'dokan_add_variation',
    post_id: <?php echo $post->ID; ?>,
    loop: loop,
    security: '<?php echo wp_create_nonce( "add-variation" ); ?>'
    };

    jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

    jQuery('.woocommerce_variations').append( response );
    jQuery(".tips").tooltip();

    jQuery('input.variable_is_downloadable, input.variable_is_virtual').change();

    jQuery('.woocommerce_variations').unblock();
    jQuery('#variable_product_options').trigger('woocommerce_variations_added');
    });

    return false;

    });

    jQuery('#variable_product_options').on('click', 'button.link_all_variations', function(){

    var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).', 'dokan' ) ); ?>');

    if (answer) {

    jQuery('#variable_product_options').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

    var data = {
    action: 'dokan_link_all_variations',
    post_id: <?php echo $post->ID; ?>,
    security: '<?php echo wp_create_nonce( "link-variations" ); ?>'
    };

    jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {

    var count = parseInt( response );

    if (count==1) {
    alert( count + ' <?php echo esc_js( __( "variation added", 'dokan' ) ); ?>');
    } else if (count==0 || count>1) {
    alert( count + ' <?php echo esc_js( __( "variations added", 'dokan' ) ); ?>');
    } else {
    alert('<?php echo esc_js( __( "No variations added", 'dokan' ) ); ?>');
    }

    if (count>0) {
    var this_page = window.location.toString();

    this_page = this_page.replace( 'post-new.php?', 'post.php?post=<?php echo $post->ID; ?>&action=edit&' );

    $('#variable_product_options').load( this_page + ' #variable_product_options_inner', function() {
    $('#variable_product_options').unblock();
    jQuery('#variable_product_options').trigger('woocommerce_variations_added');
    } );
    } else {
    $('#variable_product_options').unblock();
    }

    });
    }
    return false;
    });

    jQuery('#variable_product_options').on('click', 'button.remove_variation', function(e){
    e.preventDefault();
    var answer = confirm('<?php echo esc_js( __( 'Are you sure you want to remove this variation?', 'dokan' ) ); ?>');
    if (answer){

    var el = jQuery(this).parent().parent();

    var variation = jQuery(this).attr('rel');

    if (variation>0) {

    jQuery(el).block({ message: null, overlayCSS: { background: '#fff url(<?php echo $woocommerce->plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

    var data = {
    action: 'dokan_remove_variation',
    variation_ids: variation,
    security: '<?php echo wp_create_nonce( "delete-variations" ); ?>'
    };

    jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
    // Success
    jQuery(el).fadeOut('300', function(){
    jQuery(el).remove();
    });
    });

    } else {
    jQuery(el).fadeOut('300', function(){
    jQuery(el).remove();
    });
    }

    }
    return false;
    });

    jQuery('#variable_product_options').on('change', 'input.variable_is_downloadable', function(){

    jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_downloadable').hide();

    if (jQuery(this).is(':checked')) {
    jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_downloadable').show();
    }

    });

    jQuery('#variable_product_options').on('change', 'input.variable_manage_stock', function(){

    jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_manage_stock').hide();

    if (jQuery(this).is(':checked')) {
    jQuery(this).closest('.woocommerce_variation').find('.show_if_variation_manage_stock').show();
    }

    });

    jQuery('#variable_product_options').on('change', 'input.variable_is_virtual', function(){

    jQuery(this).closest('.woocommerce_variation').find('.hide_if_variation_virtual').show();

    if (jQuery(this).is(':checked')) {
    jQuery(this).closest('.woocommerce_variation').find('.hide_if_variation_virtual').hide();
    }

    });


    jQuery('input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock' ).change();

    // Ordering
    $('#variable_product_options').on( 'woocommerce_variations_added', function() {
    $('.woocommerce_variations').sortable( variation_sortable_options );
    } );

    $('.woocommerce_variations').sortable( variation_sortable_options );

    function variation_row_indexes() {
    $('.woocommerce_variations .woocommerce_variation').each(function(index, el){
    $('.variation_menu_order', el).val( parseInt( $(el).index('.woocommerce_variations .woocommerce_variation') ) );
    });
    };
    });
    <?php
    $javascript = ob_get_clean();
    wc_enqueue_js( $javascript );
}

/**
 * Adds default dokan store settings when a new seller registers
 *
 * @param int $user_id
 * @param array $data
 * @return void
 */
if ( !function_exists( 'dokan_user_update_to_seller' ) ) {

    function dokan_user_update_to_seller( $user, $data ) {
        if ( !dokan_is_user_customer( $user->ID ) ) {
            return;
        }

        $user_id = $user->ID;

        // Remove role
        $user->remove_role( 'customer' );

        // Add role
        $user->add_role( 'seller' );

        $user_id = wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $data['shopurl'] ) );
        update_user_meta( $user_id, 'first_name', $data['fname'] );
        update_user_meta( $user_id, 'last_name', $data['lname'] );

        if ( dokan_get_option( 'new_seller_enable_selling', 'dokan_selling', 'on' ) == 'off' ) {
            update_user_meta( $user_id, 'dokan_enable_selling', 'no' );
        } else {
            update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
        }

        $dokan_settings = array(
            'store_name'     => $data['shopname'],
            'social'         => array(),
            'payment'        => array(),
            'phone'          => $data['phone'],
            'show_email'     => 'no',
            'address'        => $data['address'],
            'location'       => '',
            'find_address'   => '',
            'dokan_category' => '',
            'banner'         => 0,
        );

        update_user_meta( $user_id, 'dokan_profile_settings', $dokan_settings );
        update_user_meta( $user_id, 'dokan_store_name', $dokan_settings['store_name'] );


        $publishing = dokan_get_option( 'product_status', 'dokan_selling' );
        //$percentage = dokan_get_option( 'seller_percentage', 'dokan_selling' );

        update_user_meta( $user_id, 'dokan_publishing', $publishing );
        //update_user_meta( $user_id, 'dokan_seller_percentage', $percentage );
        do_action( 'dokan_new_seller_created', $user_id, $dokan_settings );
    }

}

/**
 * Handles the become a seller form
 *
 * @return void
 */
if ( !function_exists( 'dokan_become_seller_handler' ) ) {

    function dokan_become_seller_handler() {
        if ( isset( $_POST['dokan_migration'] ) && wp_verify_nonce( $_POST['dokan_nonce'], 'account_migration' ) ) {
            $user   = get_userdata( get_current_user_id() );
            $errors = array();

            $checks = apply_filters( 'dokan_customer_migration_required_fields', array(
                'fname'    => __( 'Enter your first name', 'dokan' ),
                'shopname' => __( 'Enter your shop name', 'dokan' ),
                'address'  => __( 'Enter your shop address', 'dokan' ),
                'phone'    => __( 'Enter your phone number', 'dokan' ),
            ) );

            foreach ( $checks as $field => $error ) {
                if ( empty( $_POST[$field] ) ) {
                    $errors[] = $error;
                }
            }

            if ( ! $errors ) {
                dokan_user_update_to_seller( $user, $_POST );

                $url = dokan_get_navigation_url();

                if ( dokan_get_option( 'disable_welcome_wizard', 'dokan_selling', 'off' ) === 'off' ) {
                    $url = apply_filters( 'dokan_seller_setup_wizard_url', site_url( '?page=dokan-seller-setup' ) );
                }

                wp_redirect( apply_filters( 'dokan_customer_migration_redirect', $url ) );
                exit();
            }
        }
    }

}

add_action( 'template_redirect', 'dokan_become_seller_handler' );

/**
 * discount amount for lot quantity
 *
 * @return float
 */
function dokan_discount_for_lot_quantity() {
    $total_discount_amount_for_lot = 0;
    $flag_for_lot_discount         = false;

    foreach ( WC()->cart->get_cart() as $cart_data ) {
        $product_id                  = $cart_data['product_id'];
        $row_item_quantity           = $cart_data['quantity'];
        $line_total                  = $cart_data['line_total'];
        $is_lot_discount             = get_post_meta( $product_id, '_is_lot_discount', true );
        $is_enable_op_discount       = dokan_get_option( 'discount_edit', 'dokan_selling' );
        $is_product_discount_enabled = isset( $is_enable_op_discount['product-discount'] ) && $is_enable_op_discount['product-discount'] == 'product-discount';

        if ( $is_product_discount_enabled && $is_lot_discount == 'yes' ) {
            $lot_discount_percentage = get_post_meta( $product_id, '_lot_discount_amount', true );
            $lot_discount_quantity   = get_post_meta( $product_id, '_lot_discount_quantity', true );

            if ( $row_item_quantity >= $lot_discount_quantity ) { // if line quantity is greater than or equal to setting minimum quantity
                $total_discount_amount_for_lot = $total_discount_amount_for_lot + ( $line_total * $lot_discount_percentage / 100 );
                $flag_for_lot_discount         = true;
            }
        }
    }

    if ( $flag_for_lot_discount == false ) {
        $total_discount_amount_for_lot = 0;
    }

    return apply_filters( 'return_calculated_lot_discount', $total_discount_amount_for_lot );
}

/**
 * discount amount for minimum order quantity
 *
 * @return float
 */
function dokan_discount_for_minimum_order() {
    $total_discount_amount_for_min_order = 0;
    $flag_for_order_discount             = false;

    //make unique seller array
    $allsellerids      = [];
    $unique_seller_ids = [];

    foreach ( WC()->cart->get_cart() as $cart_data ) {
        $seller_id = get_post_field( 'post_author', dokan_get_prop( $cart_data['data'], 'id' ) );
        array_push( $allsellerids, $seller_id );
    }
    $unique_seller_ids  = array_unique( $allsellerids );
    //now sum up
    $total_order_amount = 0;
    foreach ( $unique_seller_ids as $u_seller_ids ) {
        foreach ( WC()->cart->get_cart() as $cart_data ) {
            $per_seller_id = get_post_field( 'post_author', dokan_get_prop( $cart_data['data'], 'id' ) );
            if ( $u_seller_ids == $per_seller_id ) {
                $total_order_amount = $total_order_amount + $cart_data['line_total'];
            }
        }

        $seller_info               = dokan_get_store_info( $u_seller_ids );
        $is_min_order_discount     = isset( $seller_info['show_min_order_discount'] ) ? $seller_info['show_min_order_discount'] : 'no';
        $is_enable_op_discount     = dokan_get_option( 'discount_edit', 'dokan_selling' );
        $is_order_discount_enabled = isset( $is_enable_op_discount['order-discount'] ) && $is_enable_op_discount['order-discount'] == 'order-discount';

        if ( $is_order_discount_enabled && $is_min_order_discount == 'yes' ) {
            $min_order_discount            = isset( $seller_info['setting_minimum_order_amount'] ) ? (float) $seller_info['setting_minimum_order_amount'] : 0;
            $min_order_discount_percentage = isset( $seller_info['setting_order_percentage'] ) ? (float) $seller_info['setting_order_percentage'] : 0;

            if ( $total_order_amount >= $min_order_discount ) {
                $total_discount_amount_for_min_order = $total_discount_amount_for_min_order + ($total_order_amount * $min_order_discount_percentage / 100);
                $flag_for_order_discount             = true;
            }
        }
        $total_order_amount = 0;
    }

    if ( $flag_for_order_discount == false ) {
        $total_discount_amount_for_min_order = 0;
    }
    return apply_filters( 'return_calculated_order_discount', $total_discount_amount_for_min_order );
}

/**
 * display discount amount for lot quantity
 *
 * @return void
 */
function dokan_display_quantity_discount() {
    ?>
    <?php $total_discount_amount_for_lot = dokan_discount_for_lot_quantity(); ?>
    <?php if ( $total_discount_amount_for_lot > 0 ) : ?>
        <tr class="cart-discount">
            <th><?php _e( 'Quantity Discount', 'dokan' ); ?></th>
            <td data-title="<?php _e( 'Quantity Discount', 'dokan' ); ?>"><?php echo wc_price( $total_discount_amount_for_lot ); ?></td>
        </tr>
    <?php endif; ?>
    <?php $total_discount_amount_for_order = dokan_discount_for_minimum_order(); ?>
    <?php if ( $total_discount_amount_for_order > 0 ) : ?>
        <tr class="cart-discount">
            <th><?php _e( 'Order Discount', 'dokan' ); ?></th>
            <td data-title="<?php _e( 'Order Discount', 'dokan' ); ?>"><?php echo wc_price( $total_discount_amount_for_order ); ?></td>
        </tr>
    <?php endif; ?>
    <?php
}

add_action( 'woocommerce_cart_totals_before_order_total', 'dokan_display_quantity_discount' );
add_action( 'woocommerce_review_order_before_order_total', 'dokan_display_quantity_discount' );
add_filter( 'woocommerce_get_order_item_totals', 'dokan_display_order_discounts', 10, 2 );
add_action( 'woocommerce_admin_order_totals_after_tax', 'dokan_display_order_discounts_on_wc_admin_order' );

/**
 * Display order discounts on orders
 *
 * @param array $table_rows
 * @param  WC_Order $order
 *
 * @since 2.9.13
 *
 * @return array
 */
function dokan_display_order_discounts( $table_rows, $order ) {
    $discounts = dokan_get_discount_by_order( $order );

    if ( ! empty( $discounts['quantity_discount'] ) ) {
        $table_rows = dokan_array_after( $table_rows, 'cart_subtotal', [
            'quantity_discount' => [
                'label' => __( 'Quantity Discount:', 'dokan' ),
                'value' => wc_price( $discounts['quantity_discount'] )
            ]
        ] );
    }

    if ( ! empty( $discounts['order_discount'] ) ) {
        $order_discount = [
            'label' => __( 'Order Discount:', 'dokan' ),
            'value' => wc_price( $discounts['order_discount'] )
        ];

        $table_rows = dokan_array_after( $table_rows, 'cart_subtotal', [
            'order_discount' => [
                'label' => __( 'Order Discount:', 'dokan' ),
                'value' => wc_price( $discounts['order_discount'] )
            ]
        ] );
    }

    return $table_rows;
}

/**
 * Display order discounts on wc admin order table
 *
 * @since 2.9.13
 * @param int $order_id
 *
 * @return void
 */
function dokan_display_order_discounts_on_wc_admin_order( $order_id ) {
    $discounts = dokan_get_discount_by_order( $order_id );

    if ( empty( $discounts['order_discount'] ) && empty( $discounts['quantity_discount'] ) ) {
        return;
    }

    $html = '';

    if ( ! empty( $discounts['order_discount'] ) ) {
        $html  = '<tr>';
        $html .= '<td class="label dokan-order-discount">' . __( 'Order Discount:', 'dokan' ) . '</td>';
        $html .= '<td class="dokan-hide" width="1%"></td>';
        $html .= '<td class="dokan-order-discount">' . wc_price( $discounts['order_discount'] )  . '</td>';
        $html .= '</tr>';
    }

    if ( ! empty( $discounts['quantity_discount'] ) ) {
        $html .= '<tr>';
        $html .= '<td class="label dokan-quantity-discount">' . __( 'Quantity Discount:', 'dokan' ) . '</td>';
        $html .= '<td class="dokan-hide" width="1%"></td>';
        $html .= '<td class="dokan-quantity-discount">' . wc_price( $discounts['quantity_discount'] )  . '</td>';
        $html .= '</tr>';
    }

    echo $html;
}

/**
 * Get discount by order
 *
 * @param WC_Order $order
 *
 * @since 2.9.13
 *
 * @return array
 */
function dokan_get_discount_by_order( $order ) {
    if ( ! $order instanceof WC_Order ) {
        $order = wc_get_order( $order );
    }

    $discounts = [];

    if ( ! $order->get_meta( 'has_sub_order') ) {
        $order_discount    = $order->get_meta( 'dokan_order_discount' );
        $quantity_discount = $order->get_meta( 'dokan_quantity_discount' );

        if ( $order_discount ) {
            $discounts['order_discount'] = $order_discount;
        }

        if ( $quantity_discount ) {
            $discounts['quantity_discount'] = $quantity_discount;
        }

        return apply_filters( 'dokan_get_discount_by_order', $discounts );
    }

    if ( $order->get_meta( 'has_sub_order') ) {
        $sub_orders = dokan_get_suborder_ids_by( $order->get_id() );
    }

    if ( empty( $sub_orders ) ) {
        return;
    }

    $order_discount    = 0;
    $quantity_discount = 0;

    foreach ( $sub_orders as $sub_order ) {
        $wc_sub_order = wc_get_order( $sub_order->ID );

        if ( ! $wc_sub_order instanceof WC_Order ) {
            continue;
        }

        $order_discount    += (float) $wc_sub_order->get_meta( 'dokan_order_discount' );
        $quantity_discount += (float) $wc_sub_order->get_meta( 'dokan_quantity_discount' );
    }

    if ( $order_discount ) {
        $discounts['order_discount'] = $order_discount;
    }

    if ( $quantity_discount ) {
        $discounts['quantity_discount'] = $quantity_discount;
    }

    return apply_filters( 'dokan_get_discount_by_order', $discounts );
}

/**
 * calculate final total after lot quantity discount
 *
 * @return float
 */
function dokan_calculate_totals( $total ) {
    $total_discount_amount_for_lot       = dokan_discount_for_lot_quantity();
    $total_discount_amount_for_min_order = dokan_discount_for_minimum_order();
    return $total - $total_discount_amount_for_lot - $total_discount_amount_for_min_order;
}

add_filter( 'woocommerce_calculated_total', 'dokan_calculate_totals' );

add_action( 'dokan_checkout_update_order_meta', 'set_discount_on_sub_orders', 5, 2 );
add_action( 'dokan_create_parent_order', 'set_discount_on_parent_order', 5, 2 );

/**
 * Set discount on sub orders
 *
 * @param int $order_id
 * @param int $vendor_id
 *
 * @since 2.9.10
 *
 * @return void
 */
function set_discount_on_sub_orders( $order_id, $vendor_id ) {
    if ( is_admin() || defined( 'REST_REQUEST' ) ) {
        return;
    }

    $is_enable_op_discount       = dokan_get_option( 'discount_edit', 'dokan_selling' );
    $is_product_discount_enabled = isset( $is_enable_op_discount['product-discount'] ) && $is_enable_op_discount['product-discount'] == 'product-discount';
    $is_order_discount_enabled   = isset( $is_enable_op_discount['order-discount'] ) && $is_enable_op_discount['order-discount'] == 'order-discount';

    if ( ! $is_product_discount_enabled && ! $is_order_discount_enabled ) {
        return;
    }

    $vendor_info                          = dokan_get_store_info( $vendor_id );
    $is_order_discount_enabled_for_vendor = isset( $vendor_info['show_min_order_discount'] ) && 'yes' === $vendor_info['show_min_order_discount'];

    $order = wc_get_order( $order_id );

    if ( ! $order instanceof WC_Order ) {
        return;
    }

    $order_total                   = $order->get_total();
    $discount_amount_for_lot       = $is_product_discount_enabled ? dokan_get_lot_discount_for_vendor( $vendor_id ) : 0;
    $discount_amount_for_min_order = $is_order_discount_enabled && $is_order_discount_enabled_for_vendor ? dokan_get_minimum_order_discount_for_vendor( $vendor_id ) : 0;

    if ( ! $discount_amount_for_lot && ! $discount_amount_for_min_order ) {
        return;
    }

    $discount_total = $discount_amount_for_lot + $discount_amount_for_min_order;
    $order_total    = $order_total - $discount_total;

    if ( ! empty( $discount_amount_for_lot ) ) {
        $order->update_meta_data( 'dokan_quantity_discount', $discount_amount_for_lot );
    }

    if ( ! empty( $discount_amount_for_min_order ) ) {
        $order->update_meta_data( 'dokan_order_discount', $discount_amount_for_min_order );
    }

    $order->set_total( $order_total );
    $order->save();
}

/**
 * Set discount on main order
 *
 * @since 2.9.13
 *
 * @param WC_Order $order
 *
 * @return void
 */
function set_discount_on_parent_order( $order ) {
    if ( is_admin() || defined( 'REST_REQUEST' ) ) {
        return;
    }

    $is_enable_op_discount       = dokan_get_option( 'discount_edit', 'dokan_selling' );
    $is_product_discount_enabled = isset( $is_enable_op_discount['product-discount'] ) && $is_enable_op_discount['product-discount'] == 'product-discount';
    $is_order_discount_enabled   = isset( $is_enable_op_discount['order-discount'] ) && $is_enable_op_discount['order-discount'] == 'order-discount';

    $discount_amount_for_lot       = $is_product_discount_enabled ? dokan_discount_for_lot_quantity() : 0;
    $discount_amount_for_min_order = $is_order_discount_enabled ? dokan_discount_for_minimum_order() : 0;

    if ( ! $discount_amount_for_lot && ! $discount_amount_for_min_order ) {
        return;
    }

    if ( ! empty( $discount_amount_for_lot ) ) {
        $order->update_meta_data( 'dokan_quantity_discount', $discount_amount_for_lot );
    }

    if ( ! empty( $discount_amount_for_min_order ) ) {
        $order->update_meta_data( 'dokan_order_discount', $discount_amount_for_min_order );
    }
}

/**
 * Get lot discount for vendor
 *
 * @param int $vendor_id
 *
 * @since 2.9.10
 *
 * @return float
 */
function dokan_get_lot_discount_for_vendor( $vendor_id ) {
    $is_enable_op_discount       = dokan_get_option( 'discount_edit', 'dokan_selling' );
    $is_product_discount_enabled = isset( $is_enable_op_discount['product-discount'] ) && $is_enable_op_discount['product-discount'] == 'product-discount';
    $discount_total              = 0;

    if ( ! $is_product_discount_enabled ) {
        return $discount_total;
    }

    foreach ( WC()->cart->get_cart() as $item ) {
        $product_id       = $item['data']->get_id();
        $product          = wc_get_product( $product_id );
        $product_discount = $product->get_meta( '_is_lot_discount', true );

        if ( $product_discount !== 'yes' ) {
            continue;
        }

        $vendor = dokan_get_vendor_by_product( $product_id );

        if ( $vendor_id !== $vendor->get_id() ) {
            continue;
        }

        $line_total              = $item['line_total'];
        $item_quantity           = $item['quantity'];
        $lot_discount_percentage = (float) $product->get_meta( '_lot_discount_amount', true );
        $lot_discount_quantity   = absint( $product->get_meta( '_lot_discount_quantity', true ) );

        if ( $item_quantity >= $lot_discount_quantity ) {
            $discount_total += ( $line_total * $lot_discount_percentage / 100 );
        }
    }

    return $discount_total;
}

/**
 * Get minimum order discount for vendor
 *
 * @param int $vendor_id
 *
 * @since 2.9.10
 *
 * @return float
 */
function dokan_get_minimum_order_discount_for_vendor( $vendor_id ) {
    $is_enable_op_discount                = dokan_get_option( 'discount_edit', 'dokan_selling' );
    $is_order_discount_enabled            = isset( $is_enable_op_discount['order-discount'] ) && $is_enable_op_discount['order-discount'] == 'order-discount';
    $vendor_info                          = dokan_get_store_info( $vendor_id );
    $is_order_discount_enabled_for_vendor = isset( $vendor_info['show_min_order_discount'] ) && 'yes' === $vendor_info['show_min_order_discount'];

    $discount_total = 0;
    $line_total     = 0;

    if ( ! $is_order_discount_enabled && ! $is_order_discount_enabled_for_vendor ) {
        return $discount_total;
    }

    foreach ( WC()->cart->get_cart() as $item ) {
        $product_id = $item['data']->get_id();
        $vendor     = dokan_get_vendor_by_product( $product_id );

        if ( $vendor_id !== $vendor->get_id() ) {
            continue;
        }

        $line_total += $item['line_total'];
    }

    $min_order_discount            = isset( $vendor_info['setting_minimum_order_amount'] ) ? (float) $vendor_info['setting_minimum_order_amount'] : 0;
    $min_order_discount_percentage = isset( $vendor_info['setting_order_percentage'] ) ? (float) $vendor_info['setting_order_percentage'] : 0;

    if ( $line_total >= $min_order_discount ) {
        $discount_total = ( $line_total * $min_order_discount_percentage / 100 );
    }

    return $discount_total;
}

/**
* Update author for variation product
*
* @since 2.6.2
*
* @return void
**/
function dokan_override_author_for_variations( $product, $seller_id ) {
    if ( $product->get_type() == 'variable' ) {
        $args = array(
            'post_parent' => $product->get_id(),
            'post_type'   => 'product_variation',
            'numberposts' => -1,
            'post_status' => 'any'
        );

        $variations = get_children( $args );

        foreach ( $variations as $key => $variation ) {
            wp_update_post( array(
                'ID'          => $variation->ID,
                'post_author' => $seller_id
            ) );
        }
    }
}

add_action( 'dokan_after_override_product_author', 'dokan_override_author_for_variations', 11, 2 );

add_action( 'product_cat_add_form_fields', 'dokan_add_category_commission_field' );
add_action( 'product_cat_edit_form_fields', 'dokan_edit_category_commission_field', 10 );
add_action( 'created_term','dokan_save_category_commission_field', 10, 3 );
add_action( 'edit_term', 'dokan_save_category_commission_field', 10, 3 );

/**
 * Render commission field on new product category
 *
 * @since 2.6.6
 *
 * @return void
 */
function dokan_add_category_commission_field() {
    if ( dokan_get_option( 'product_category_style', 'dokan_selling' ) !== 'single' ) {
        return;
    }
    ?>
    <div class="form-field term-display-type-wrap">
        <label for="per_category_admin_commission_type"><?php _e( 'Commission type', 'dokan' ); ?></label>
        <select id="per_category_admin_commission_type" name="per_category_admin_commission_type">
            <?php foreach ( dokan_commission_types() as $key => $value ) : ?>
                <option value="<?php echo wc_clean( $key ); ?>"><?php echo $value ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e( 'This is the commission type for admin fee', 'dokan' ); ?></p>
    </div>
    <div class="form-field term-display-type-wrap">
        <label for="per_category_admin_commission"><?php _e( 'Admin Commission from this category', 'dokan' ); ?></label>
        <input type="text" class="wc_input_price commission-filed" name="per_category_admin_commission">
        <span class="additional-fee dokan-hide">
            <?php echo esc_html( '% &nbsp;&nbsp; +'); ?>
            <input type="text" class="wc_input_price commission-filed" name="per_category_admin_additional_fee">
        </span>
        <p class="combine-commission-description"><?php _e( 'If set, it will override global admin commission rate for this category', 'dokan' ); ?></p>
    </div>

    <style type="text/css">
        .dokan-hide {
            display: none;
        }
        .commission-filed {
            width: 60px !important;
        }
    </style>

    <script type="text/javascript">
        // admin additional fee
        ;(function($) {
            $('#per_category_admin_commission_type').on('change', function() {
                var self = $(this),
                    val = self.val();

                if ( 'combine' === val ) {
                    $('span.additional-fee').removeClass('dokan-hide');
                    $('.combine-commission-description').text( dokan_admin.combine_commission_desc );
                } else {
                    $('span.additional-fee').addClass('dokan-hide');
                    $('.combine-commission-description').text( dokan_admin.combine_default_desc );
                }

                if ( 'flat' === val ) {
                    $('input[name="per_category_admin_commission"]').removeClass( 'wc_input_decimal' ).addClass( 'wc_input_price' );
                } else {
                    $('input[name="per_category_admin_commission"]').removeClass( 'wc_input_price' ).addClass( 'wc_input_decimal' );
                }

            }).trigger('change');
        })(jQuery);
    </script>
    <?php
}

/**
 * Render commission field on edit product category page
 *
 * @since 2.6.6
 *
 * @param WP_Term $term
 *
 * @return void
 */
function dokan_edit_category_commission_field( $term ) {
    if ( dokan_get_option( 'product_category_style', 'dokan_selling' ) !== 'single' ) {
        return;
    }

    $commission_type      = get_term_meta( $term->term_id, 'per_category_admin_commission_type', true );
    $admin_additional_fee = get_term_meta( $term->term_id, 'per_category_admin_additional_fee', true );
    $commission           = get_term_meta( $term->term_id, 'per_category_admin_commission', true );
    $commission           = 'flat' === $commission_type ? wc_format_localized_price( $commission ) : wc_format_localized_decimal( $commission );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label><?php _e( 'Admin Commission type', 'dokan' ); ?></label></th>
        <td>
            <select id="per_category_admin_commission_type" name="per_category_admin_commission_type" class="postform">
                <?php foreach ( dokan_commission_types() as $key => $value ) : ?>
                    <option value="<?php echo wc_clean( $key ); ?>" <?php selected( $commission_type, $key );  ?>>
                        <?php echo $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e( 'This is the commission type for admin fee', 'dokan' ); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label><?php _e( 'Admin commission', 'dokan' ); ?></label></th>
        <td>
            <input type="text" class="wc_input_price commission-filed" name="per_category_admin_commission" value="<?php echo esc_attr( $commission ); ?>">
            <span class="additional-fee dokan-hide">
                <?php echo esc_html( '% &nbsp;&nbsp; +'); ?>
                <input type="text" class="wc_input_price commission-filed" name="per_category_admin_additional_fee" value="<?php echo esc_attr( wc_format_localized_price( $admin_additional_fee ) ); ?>">
            </span>

            <p class="combine-commssion-description"><?php _e( 'If set, it will override global admin commission rate for this category', 'dokan' ) ?></p>
        </td>
    </tr>

    <style type="text/css">
        .dokan-hide {
            display: none;
        }
        .commission-filed {
            width: 60px !important;
        }
    </style>

    <script type="text/javascript">
        // admin additional fee
        ;(function($) {
            $('#per_category_admin_commission_type').on('change', function() {
                var self = $(this),
                    val = self.val();

                if ( 'combine' === val ) {
                    $('span.additional-fee').removeClass('dokan-hide');
                    $('.combine-commssion-description').text( dokan_admin.combine_commission_desc );
                    $('input[name=per_category_admin_commission]').attr('required', true);
                    $('input[name=per_category_admin_additional_fee]').attr('required', true);
                } else {
                    $('span.additional-fee').addClass('dokan-hide');
                    $('.combine-commssion-description').text( dokan_admin.default_commission_desc );
                    $('input[name=per_category_admin_commission]').removeAttr('required');
                    $('input[name=per_category_admin_additional_fee]').removeAttr('required');
                }

                if ( 'flat' === val ) {
                    $('input[name="per_category_admin_commission"]').removeClass( 'wc_input_decimal' ).addClass( 'wc_input_price' );
                } else {
                    $('input[name="per_category_admin_commission"]').removeClass( 'wc_input_price' ).addClass( 'wc_input_decimal' );
                }

            }).trigger('change');
        })(jQuery);
    </script>
    <?php
}

/**
 * Save category commission field
 *
 * @since 2.6.6
 *
 * @param int $term_id
 * @param int $tt_id
 * @param object $taxonomy
 *
 * @return void
 */
function dokan_save_category_commission_field( $term_id, $tt_id = '', $taxonomy = '' ){
    $post_data        = wp_unslash( $_POST );
    $commission_type  = '';
    $admin_commission = '';
    $additional_fee   = '';

    if ( isset( $post_data['per_category_admin_commission_type'] ) && 'product_cat' === $taxonomy ) {
        $commission_type = $post_data['per_category_admin_commission_type'];
        update_term_meta( $term_id, 'per_category_admin_commission_type', wc_clean( $commission_type ) );
    }

    if ( isset( $post_data['per_category_admin_commission'] ) ) {
        $admin_commission = wc_format_decimal( $post_data['per_category_admin_commission'] );
    }

    if ( isset( $post_data['per_category_admin_additional_fee'] ) ) {
        $additional_fee = wc_format_decimal( $post_data['per_category_admin_additional_fee'] );
    }

    if ( 'combine' === $commission_type && ( '' === $admin_commission || '' === $additional_fee ) ) {
        update_term_meta( $term_id, 'per_category_admin_commission', '' );
        update_term_meta( $term_id, 'per_category_admin_additional_fee', '' );
    } else {
        update_term_meta( $term_id, 'per_category_admin_commission', wc_clean( $admin_commission ) );
        update_term_meta( $term_id, 'per_category_admin_additional_fee', wc_clean( $additional_fee ) );
    }
}

add_filter( 'woocommerce_cart_shipping_packages', 'dokan_custom_split_shipping_packages' );

/**
 * Split shpping seller wise
 *
 * @param array $packages
 *
 * @return array
 */
function dokan_custom_split_shipping_packages( $packages ) {
    $cart_content = WC()->cart->get_cart();
    $seller_pack = array();
    $packages = array();

    foreach ( $cart_content as $key => $item ) {
        // If individual seller product shipping is disable then out from here
        if ( \WeDevs\DokanPro\Shipping\Methods\ProductShipping::is_product_disable_shipping( $item['product_id'] ) ) {
            continue;
        }

        $post_author = get_post_field( 'post_author', $item['data']->get_id() );
        $seller_pack[$post_author][$key] = $item;
    }

    foreach ( $seller_pack as $seller_id => $pack ) {
        $packages[] = array(
            'contents'        => $pack,
            'contents_cost'   => array_sum( wp_list_pluck( $pack, 'line_total' ) ),
            'applied_coupons' => WC()->cart->get_applied_coupons(),
            'user'            => array(
                'ID' => get_current_user_id(),
            ),
            'seller_id'       =>  $seller_id,
            'destination'     => array(
                'country'     => WC()->customer->get_shipping_country(),
                'state'       => WC()->customer->get_shipping_state(),
                'postcode'    => WC()->customer->get_shipping_postcode(),
                'city'        => WC()->customer->get_shipping_city(),
                'address'     => WC()->customer->get_shipping_address(),
                'address_2'   => WC()->customer->get_shipping_address_2()
            )
        );
    }

    return apply_filters( 'dokan_cart_shipping_packages', $packages );
}

add_filter( 'woocommerce_shipping_package_name', 'dokan_change_shipping_pack_name', 10, 3 );

/**
 * Set packagewise seller name
 *
 * @param string $title
 * @param integer $i
 * @param array $package
 *
 * @return string
 */
function dokan_change_shipping_pack_name( $title, $i, $package ) {

    $user_id = $package['seller_id'];

    if ( empty( $user_id ) ) {
        return $title;
    }

    if ( is_array( $user_id ) ) {
        $user_id = reset( $user_id );
    }

    $store_info   = dokan_get_store_info( $user_id );

    $shipping_label = sprintf( '%s %s', __( 'Shipping: ', 'dokan' ), !empty( $store_info['store_name'] ) ? $store_info['store_name'] : '' );

    return apply_filters( 'dokan_shipping_package_name', $shipping_label, $i, $package );
}

add_action( 'woocommerce_checkout_create_order_shipping_item', 'dokan_add_shipping_pack_meta', 10, 4 );

/**
 * Added shipping meta after order
 *
 * @param object $item
 * @param string $package_key
 * @param array $package
 * @param object $order
 *
 * @return void
 */
function dokan_add_shipping_pack_meta( $item, $package_key, $package, $order ) {
    $item->add_meta_data( 'seller_id', $package['seller_id'], true );
}

/**
 * Handles the social registration form
 *
 * @return void
 */
if ( !function_exists( 'dokan_social_reg_handler' ) ) {

    function dokan_social_reg_handler() {
        if ( isset( $_POST['dokan_social'] ) && wp_verify_nonce( $_POST['dokan_nonce'], 'account_migration' ) ) {
            $userdata  = get_userdata( get_current_user_id() );

            $userdata->first_name = sanitize_text_field( $_POST[ 'fname' ] );
            $userdata->last_name = sanitize_text_field( $_POST[ 'lname' ] );

            wp_update_user( $userdata );

            wp_redirect( dokan_get_page_url( 'dashboard', 'dokan' ) );
        }
    }
}

add_action( 'template_redirect', 'dokan_social_reg_handler' );

if ( function_exists( 'dokan_add_privacy_policy' ) ) {
    // show privacy policy text in product enquiry form
    add_action( 'dokan_product_enquiry_after_form', 'dokan_add_privacy_policy' );
}

add_filter( 'woocommerce_ajax_admin_get_variations_args', 'dokan_set_variations_args' );
add_filter( 'woocommerce_variable_children_args', 'dokan_set_variations_args' );

/**
 * Include pending product status into variation args
 *
 * @since 2.9.13
 *
 * @param array $args
 */
function dokan_set_variations_args( $args ) {
    if ( ! is_array( $args['post_status'] ) ) {
        return $args;
    }

    $args['post_status'] = array_merge( $args['post_status'], ['pending'] );

    return $args;
}

/**
 * Set variation product author to product vendor id
 *
 * @since 2.9.13
 *
 * @param int $variation_id
 *
 * @return void
 */
function dokan_override_variation_product_author( $variation_id ) {
    if ( ! is_admin() ) {
        return;
    }

    $variation_product = get_post( $variation_id );

    if ( ! $variation_product ) {
        return;
    }

    $product_id = $variation_product->post_parent;

    if ( ! $product_id ) {
        return;
    }

    $product = wc_get_product( $product_id );

    if ( ! $product ) {
        return;
    }

    $vendor    = dokan_get_vendor_by_product( $product );
    $vendor_id = $vendor->get_id();

    if ( ! $vendor || ! $vendor_id ) {
        return;
    }

    if ( absint( $vendor_id ) === absint( $variation_product->post_author ) ) {
        return;
    }

    wp_update_post( array(
        'ID'          => $variation_id,
        'post_author' => $vendor_id
    ) );

    do_action( 'dokan_after_override_variation_product_author', $product, $vendor_id );
}

add_action( 'woocommerce_save_product_variation', 'dokan_override_variation_product_author' );

/**
 * Dokan enabble single seller mode
 *
 * @since  2.9.16
 *
 * @param  bool $valid
 * @param  int $product_id
 *
 * @return bool
 */
function dokan_validate_cart_for_single_seller_mode( $valid, $product_id ) {
    $is_single_seller_mode = apply_filters_deprecated( 'dokan_signle_seller_mode', [ dokan_get_option( 'enable_single_seller_mode', 'dokan_general', 'off' ) ], '3.0.0', 'dokan_single_seller_mode' );
    $is_single_seller_mode = apply_filters( 'dokan_single_seller_mode', $is_single_seller_mode );

    if ( ! dokan_validate_boolean( $is_single_seller_mode ) ) {
        return $valid;
    }

    $products              = WC()->cart->get_cart();
    $products[$product_id] = ['product_id' => $product_id];

    if ( ! $products ) {
        return $valid;
    }

    $vendors = [];

    foreach ( $products as $key => $data ) {
        $product_id = isset( $data['product_id'] ) ? $data['product_id'] : 0;
        $vendor     = dokan_get_vendor_by_product( $product_id );
        $vendor_id  = $vendor && $vendor->get_id() ? $vendor->get_id() : 0;

        if ( ! $vendor_id ) {
            continue;
        }

        if ( ! in_array( $vendor_id, $vendors ) ) {
            array_push( $vendors, $vendor_id );
        }
    }

    if ( count( $vendors ) > 1 ) {
        wc_add_notice( __( 'Sorry, you can\'t add more than one vendor\'s product in the cart.', 'dokan' ), 'error' );
        $valid = false;
    }

    return $valid;
}

add_filter( 'woocommerce_add_to_cart_validation', 'dokan_validate_cart_for_single_seller_mode', 10, 2 );

/**
 * Dokan rest validate single seller mode
 *
 * @since  2.9.16
 *
 * @param  WC_Order $order
 * @param  WP_REST_Request
 * @param  bool $creating
 *
 * @return WC_Order|WP_REST_Response on failure
 */
function dokan_rest_validate_single_seller_mode( $order, $request, $creating ) {
    if ( ! $creating ) {
        return $order;
    }

    $is_single_seller_mode = apply_filters_deprecated( 'dokan_signle_seller_mode', [ dokan_get_option( 'enable_single_seller_mode', 'dokan_general', 'off' ) ], '3.0.0', 'dokan_single_seller_mode' );
    $is_single_seller_mode = apply_filters( 'dokan_single_seller_mode', $is_single_seller_mode );

    if ( ! dokan_validate_boolean( $is_single_seller_mode ) ) {
        return $order;
    }

    if ( $order->get_meta( 'has_sub_order' ) ) {
        return rest_ensure_response(
            new WP_Error(
                'dokan_single_seller_mode',
                __( 'Sorry, you can\'t purchase from multiple vendors at once.', 'dokan' ),
                [
                    'status' => 403
                ]
            )
        );
    }

    return $order;
}

add_filter( 'woocommerce_rest_pre_insert_shop_order_object', 'dokan_rest_validate_single_seller_mode', 15, 3 );

if ( ! function_exists( 'woocommerce_customer_available_downloads_modified' ) ) {

    /**
     * Dokan customer available downloads modified for sub orders
     *
     * @since  3.1.2
     *
     * @param  Array $downloads
     * @param  Int $customer_id
     *
     * @return Array $modified_downloads|$downloads
     */
    function dokan_woocommerce_customer_available_downloads_modified( $downloads, $customer_id ) {
        if ( empty( $downloads ) ) {
            return;
        }

        $modified_downloads = [];

        foreach ( $downloads as $download ) {
            $order_id = $download['order_id'];
            $order    = wc_get_order( $order_id );

            if ( empty( $order ) ) {
                continue;
            }

            if ( $order->get_meta( 'has_sub_order' ) ) {
                continue;
            }

            $modified_downloads[] = $download;
        }

        if ( ! empty( $modified_downloads ) ) {
            return $modified_downloads;
        }

        return $downloads;
    }

    add_filter( 'woocommerce_customer_available_downloads', 'dokan_woocommerce_customer_available_downloads_modified', 15, 2 );
}
