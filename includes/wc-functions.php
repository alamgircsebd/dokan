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
    $tax_classes           = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
    $tax_class_options     = array();
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
                                '_sale_price_dates_to'
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
 * Set More product from seller tab
 *
 * on Single Product Page
 *
 * @since 2.5
 * @param array $tabs
 * @return int
 */
function dokan_more_from_seller_tab( $tabs ) {
    if( check_more_seller_product_tab()){
        $tabs['more_seller_product'] = array(
                'title' 	=> __( 'More Product', 'dokan' ),
                'priority' 	=> 99,
                'callback' 	=> 'dokan_more_product_from_seller',
        );
    }
    return $tabs;

}
add_action( 'woocommerce_product_tabs', 'dokan_more_from_seller_tab', 10 );

/**
 *  Show more product from current seller
 *
 * @since 2.5
 * @global object $product
 * @global object $post
 * @param int $seller_id
 * @param int $posts_per_page
 */
function dokan_more_product_from_seller( $seller_id = 0, $posts_per_page = 6 ) {

    global $product, $post;

    if ( $seller_id == 0 ) {
        $seller_id = $post->post_author;
    }

    if ( !abs( $posts_per_page ) ) {
        $posts_per_page = 4;
    }
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'orderby'        => 'rand',
        'post__not_in'   => array( $post->ID ),
        'author'         => $seller_id
    );

    $products = new WP_Query( $args );

    if ( $products->have_posts() ) {
        woocommerce_product_loop_start();

        while ( $products->have_posts() ) {
            $products->the_post();
            wc_get_template_part( 'content', 'product' );
        }

        woocommerce_product_loop_end();
    } else {
        _e( 'No product has been found!', 'dokan' );
    }

    wp_reset_postdata();
}

/**
 * Adds default dokan store settings when a new seller registers
 *
 * @param int $user_id
 * @param array $data
 * @return void
 */
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

    Dokan_Email::init()->new_seller_registered_mail( $user_id );
}

/**
 * Handles the become a seller form
 *
 * @return void
 */
function dokan_become_seller_handler() {
    if ( isset( $_POST['dokan_migration'] ) && wp_verify_nonce( $_POST['dokan_nonce'], 'account_migration' ) ) {
        $user   = get_userdata( get_current_user_id() );
        $errors = array();

        $checks = array(
            'fname'    => __( 'Enter your first name', 'dokan' ),
            'lname'    => __( 'Enter your last name', 'dokan' ),
            'shopname' => __( 'Enter your shop name', 'dokan' ),
            'address'  => __( 'Enter your shop address', 'dokan' ),
            'phone'    => __( 'Enter your phone number', 'dokan' ),
        );

        foreach ( $checks as $field => $error ) {
            if ( empty( $_POST[$field] ) ) {
                $errors[] = $error;
            }
        }

        if ( !$errors ) {
            dokan_user_update_to_seller( $user, $_POST );

            wp_redirect( dokan_get_page_url( 'myaccount', 'dokan' ) );
        }
    }
}

add_action( 'template_redirect', 'dokan_become_seller_handler' );

/**
 * Flat Rate Shipping made compatible for Orders with multiple seller
 *
 * @since 2.4.3
 *
 * @param array $rates
 * @param array $package
 *
 * @return $rates
 */
function dokan_multiply_flat_rate_price_by_seller( $rates, $package ) {

    $flat_rate_array = preg_grep( "/^flat_rate:*/", array_keys( $rates ) );
    $flat_rate       = isset( $flat_rate_array[0] ) ? $flat_rate_array[0] : '';

    foreach ( $package['contents'] as $product ) {
        $sellers[] = get_post_field( 'post_author', $product['product_id'] );
    }

    $sellers = array_unique( $sellers );

    $selllers_count = count( $sellers );

    if ( isset( $rates[$flat_rate] ) && !is_null( $rates[$flat_rate] ) ) {

        $rates[$flat_rate]->cost = $rates[$flat_rate]->cost * $selllers_count;

        // we assumed taxes key will always be 1, if different condition appears in future, we'll update the script
        if ( isset( $rates[$flat_rate]->taxes[1] ) ) {
            $rates[$flat_rate]->taxes[1] = $rates[$flat_rate]->taxes[1] * $selllers_count;
        }
    } elseif ( isset( $rates['international_delivery'] ) && !is_null( $rates['international_delivery'] ) ) {

        $rates['international_delivery']->cost     = $rates['international_delivery']->cost * $selllers_count;
        // we assumed taxes key will always be 1, if different condition appears in future, we'll update the script
        $rates['international_delivery']->taxes[1] = $rates['international_delivery']->taxes[1] * $selllers_count;
    }

    return $rates;
}

add_filter( 'woocommerce_package_rates', 'dokan_multiply_flat_rate_price_by_seller', 1, 2 );

/**
 * discount amount for lot quantity
 *
 * @return float
 */
function dokan_discount_for_lot_quantity() {
    $total_discount_amount_for_lot = 0;
    $flag_for_lot_discount         = false;

    foreach ( WC()->cart->get_cart() as $cart_data ) {
        $product_id        = $cart_data['product_id'];
        $row_item_quantity = $cart_data['quantity'];
        $line_total        = $cart_data['line_total'];
        $is_lot_discount   = get_post_meta( $product_id, '_is_lot_discount', true );
        if ( $is_lot_discount == 'yes' ) {
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
        // error_log( print_r( dokan_get_prop( $cart_data['data'], 'id' ) , true ) );
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
        $seller_info           = dokan_get_store_info( $u_seller_ids );
        $is_min_order_discount = isset( $seller_info['show_min_order_discount'] ) ? $seller_info['show_min_order_discount'] : 'no';
        if ( $is_min_order_discount == "yes" ) {
            $min_order_discount            = isset( $seller_info['setting_minimum_order_amount'] ) ? $seller_info['setting_minimum_order_amount'] : 0;
            $min_order_discount_percentage = isset( $seller_info['setting_order_percentage'] ) ? $seller_info['setting_order_percentage'] : 0;
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
            <th><?php _e( 'Quantity discount', 'dokan' ); ?></th>
            <td><?php echo wc_price( $total_discount_amount_for_lot ); ?></td>
        </tr>
    <?php endif; ?>
    <?php $total_discount_amount_for_order = dokan_discount_for_minimum_order(); ?>
    <?php if ( $total_discount_amount_for_order > 0 ) : ?>
        <tr class="cart-discount">
            <th><?php _e( 'Order discount', 'dokan' ); ?></th>
            <td><?php echo wc_price( $total_discount_amount_for_order ); ?></td>
        </tr>
    <?php endif; ?>
    <?php
}

add_action( 'woocommerce_cart_totals_before_order_total', 'dokan_display_quantity_discount' );

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
