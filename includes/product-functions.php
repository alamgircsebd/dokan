<?php
/**
 * Load all product related functions
 *
 * @since 2.5.1
 *
 * @package dokan
 */

/**
 * Dokan insert new product
 *
 * @since  2.5.1
 *
 * @param  array  $args
 *
 * @return integer|boolean
 */
function dokan_insert_product( $args ) {

    $defaults = array(
        'post_title' => '',
        'post_content' => '',
        'post_excerpt' => '',
        'post_status' => '',
        'post_type' => 'product',
    );

    $data = wp_parse_args( $args, $defaults );

    if ( empty( $data['post_title'] ) ) {
        return new WP_Error( 'no-title', __( 'Please enter product title', 'dokan' ) );
    }

    if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
        $product_cat    = intval( $data['product_cat'] );
        if ( $product_cat < 0 ) {
            return new WP_Error( 'no-category', __( 'Please select a category', 'dokan' ) );
        }
    } else {
        if( ! isset( $data['product_cat'] ) && empty( $data['product_cat'] ) ) {
            return new WP_Error( 'no-category', __( 'Please select AT LEAST ONE category', 'dokan' ) );
        }
    }

    $post_status = ! empty( $data['post_status'] ) ? $data['post_status'] : dokan_get_new_post_status();

    $post_data = apply_filters( 'dokan_insert_product_post_data', array(
        'post_type'    => $data['post_type'],
        'post_status'  => $post_status,
        'post_title'   => $data['post_title'],
        'post_content' => $data['post_content'],
        'post_excerpt' => $data['post_excerpt'],
    ) );

    $product_id = wp_insert_post( $post_data );

    if ( $product_id ) {

        /** set images **/
        if ( $data['feat_image_id'] ) {
            set_post_thumbnail( $product_id, $data['feat_image_id'] );
        }

        if ( isset( $data['product_tag'] ) && !empty( $data['product_tag'] ) ) {
            $tags_ids = array_map( 'intval', (array)$data['product_tag'] );
            wp_set_object_terms( $product_id, $tags_ids, 'product_tag' );
        }

        /** set product category * */
        if( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) == 'single' ) {
            wp_set_object_terms( $product_id, (int) $data['product_cat'], 'product_cat' );
        } else {
            if ( isset( $data['product_cat'] ) && !empty( $data['product_cat'] ) ) {
                $cat_ids = array_map( 'intval', (array)$data['product_cat'] );
                wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
            }
        }
        if ( isset( $data['product_type'] ) ) {
            wp_set_object_terms( $product_id, $data['product_type'], 'product_type' );
        } else {
            wp_set_object_terms( $product_id, 'simple', 'product_type' );
        }

        if ( isset( $data['_regular_price'] ) ) {
            update_post_meta( $product_id, '_regular_price', ( $data['_regular_price'] === '' ) ? '' : wc_format_decimal( $data['_regular_price'] ) );
        }

        if ( isset( $data['_sale_price'] ) ) {
            update_post_meta( $product_id, '_sale_price', ( $data['_sale_price'] === '' ? '' : wc_format_decimal( $data['_sale_price'] ) ) );
        }

        $date_from = isset( $data['_sale_price_dates_from'] ) ? $data['_sale_price_dates_from'] : '';
        $date_to   = isset( $data['_sale_price_dates_to'] ) ? $data['_sale_price_dates_to'] : '';

        // Dates
        if ( $date_from ) {
            update_post_meta( $product_id, '_sale_price_dates_from', strtotime( $date_from ) );
        } else {
            update_post_meta( $product_id, '_sale_price_dates_from', '' );
        }

        if ( $date_to ) {
            update_post_meta( $product_id, '_sale_price_dates_to', strtotime( $date_to ) );
        } else {
            update_post_meta( $product_id, '_sale_price_dates_to', '' );
        }

        if ( $date_to && ! $date_from ) {
            update_post_meta( $product_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
        }

        // Update price if on sale
        if ( '' !== $data['_sale_price'] && '' == $date_to && '' == $date_from ) {
            update_post_meta( $product_id, '_price', wc_format_decimal( $data['_sale_price'] ) );
        } else {
            update_post_meta( $product_id, '_price', ( $data['_regular_price'] === '' ) ? '' : wc_format_decimal( $data['_regular_price'] ) );
        }

        if ( '' !== $data['_sale_price'] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
            update_post_meta( $product_id, '_price', wc_format_decimal( $data['_sale_price'] ) );
        }

        if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
            update_post_meta( $product_id, '_price', ( $data['_regular_price'] === '' ) ? '' : wc_format_decimal( $data['_regular_price'] ) );
            update_post_meta( $product_id, '_sale_price_dates_from', '' );
            update_post_meta( $product_id, '_sale_price_dates_to', '' );
        }

        update_post_meta( $product_id, '_visibility', 'visible' );

        do_action( 'dokan_new_product_added', $product_id, $data );

        if ( dokan_get_option( 'product_add_mail', 'dokan_general', 'on' ) == 'on' ) {
            Dokan_Email::init()->new_product_added( $product_id, $post_status );
        }

        return $product_id;
    }

    return false;
}

/**
 * Show options for the variable product type.
 *
 * @since 2.6
 *
 * @return void
 */
function dokan_product_output_variations() {
    global $post, $wpdb;

    // Get attributes
    $attributes = maybe_unserialize( get_post_meta( $post->ID, '_product_attributes', true ) );

    // See if any are set
    $variation_attribute_found = false;

    if ( $attributes ) {
        foreach ( $attributes as $attribute ) {
            if ( ! empty( $attribute['is_variation'] ) ) {
                $variation_attribute_found = true;
                break;
            }
        }
    }

    $variations_count       = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'product_variation' AND post_status IN ('publish', 'private')", $post->ID ) ) );
    $variations_per_page    = absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) );
    $variations_total_pages = ceil( $variations_count / $variations_per_page );
    ?>
    <div id="dokan-variable-product-options" class="">
        <div id="dokan-variable-product-options-inner">

        <?php if ( ! $variation_attribute_found ) : ?>

            <div id="dokan-info-message" class="dokan-alert dokan-alert-info">
                <p>
                    <?php _e( 'Before you can add a variation you need to add some variation attributes on the <strong>Attributes</strong> tab.', 'woocommerce' ); ?>
                    <a class="button-primary" href="<?php echo esc_url( apply_filters( 'woocommerce_docs_url', 'https://docs.woocommerce.com/document/variable-product/', 'product-variations' ) ); ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a>
                </p>
            </div>

        <?php else : ?>

            <div class="dokan-variation-top-toolbar">
                <div class="dokan-variation-label content-half-part">
                    <label for="" class="form-label"><?php _e( 'Variations panel', 'dokan' ) ?></label>
                </div>

                <div class="dokan-variation-default-toolbar content-half-part">

                    <div class="variations-defaults">
                        <span class="dokan-variation-default-label dokan-left float-none"><i class="fa fa-question-circle tips" title="<?php _e( 'Default Form Values: These are the attributes that will be pre-selected on the frontend.', 'woocommerce' ); ?>" aria-hidden="true"></i></span>
                        <?php
                            $default_attributes = maybe_unserialize( get_post_meta( $post->ID, '_default_attributes', true ) );

                            foreach ( $attributes as $attribute ) {

                                // Only deal with attributes that are variations
                                if ( ! $attribute['is_variation'] ) {
                                    continue;
                                }

                                echo '<div class="dokan-variation-default-select dokan-w5 float-none">';

                                // Get current value for variation (if set)
                                $variation_selected_value = isset( $default_attributes[ sanitize_title( $attribute['name'] ) ] ) ? $default_attributes[ sanitize_title( $attribute['name'] ) ] : '';

                                // Name will be something like attribute_pa_color
                                echo '<select class="dokan-form-control" name="default_attribute_' . sanitize_title( $attribute['name'] ) . '" data-current="' . esc_attr( $variation_selected_value ) . '"><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                                // Get terms for attribute taxonomy or value if its a custom attribute
                                if ( $attribute['is_taxonomy'] ) {
                                    $post_terms = wp_get_post_terms( $post->ID, $attribute['name'] );

                                    foreach ( $post_terms as $term ) {
                                        echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
                                    }

                                } else {
                                    $options = wc_get_text_attributes( $attribute['value'] );

                                    foreach ( $options as $option ) {
                                        $selected = sanitize_title( $variation_selected_value ) === $variation_selected_value ? selected( $variation_selected_value, sanitize_title( $option ), false ) : selected( $variation_selected_value, $option, false );
                                        echo '<option ' . $selected . ' value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) )  . '</option>';
                                    }

                                }

                                echo '</select>';
                                echo '</div>';

                            }
                        ?>
                        <div class="dokan-clearfix"></div>

                    </div>

                </div>
                <div class="dokan-clearfix"></div>
            </div>

            <div class="dokan-variations-container wc-metaboxes" data-attributes="<?php
                // esc_attr does not double encode - htmlspecialchars does
                echo htmlspecialchars( json_encode( $attributes ) );
            ?>" data-total="<?php echo $variations_count; ?>" data-total_pages="<?php echo $variations_total_pages; ?>" data-page="1" data-edited="false">
                <div class="dokan-product-variation-itmes woocommerce_variation wc-metabox closed">
                    <h3 class="variation-topbar-heading">
                        <a href="#" class="remove_variation delete" rel="153">Remove</a>
                        <i class="fa fa-bars tips" data-tile="<?php _e( 'Drag and drop, or click to set admin variation order', 'dokan' ); ?>" aria-hidden="true" ></i><div class="tips" data-tip=""></div>
                        <strong>#153 </strong>
                        <select name="attribute_color[0]" class="dokan-form-control">
                            <option value="">Any Color…</option>
                            <option selected="selected" value="Red">Red</option>
                            <option value="sabbir">sabbir</option><option value="Mishu">Mishu</option>
                        </select>
                        <select name="attribute_pa_size[0]" class="dokan-form-control">
                            <option value="">Any Size…</option>
                            <option selected="selected" value="midium">Midium</option>
                            <option value="small">Small</option>
                        </select>
                        <input type="hidden" name="variable_post_id[0]" value="153">
                        <input type="hidden" class="variation_menu_order" name="variation_menu_order[0]" value="0">
                    </h3>

                    <div class="woocommerce_variable_attributes wc-metabox-content" style="display: none;">
                        <div class="data">
                            <p class="form-row form-row-first upload_image">
                                <a href="#" class="upload_image_button tips " data-tip="Upload an image" rel="153"><img src="http://localhost/dokan/wp-content/plugins/woocommerce/assets/images/placeholder.png"><input type="hidden" name="upload_image_id[0]" class="upload_image_id" value="0"></a>
                            </p>
                            <p class="sku form-row form-row-last">
                                <label>SKU <span class="woocommerce-help-tip" data-tip="Enter a SKU for this variation or leave blank to use the parent product SKU."></span></label>
                                <input type="text" size="5" name="variable_sku[0]" value="" placeholder="">
                            </p>

                            <p class="form-row form-row-full options">
                                <label><input type="checkbox" class="checkbox" name="variable_enabled[0]" checked="checked"> Enabled</label>
                                <label><input type="checkbox" class="checkbox variable_is_downloadable" name="variable_is_downloadable[0]"> Downloadable <span class="woocommerce-help-tip" data-tip="Enable this option if access is given to a downloadable file upon purchase of a product"></span></label>
                                <label><input type="checkbox" class="checkbox variable_is_virtual" name="variable_is_virtual[0]"> Virtual <span class="woocommerce-help-tip" data-tip="Enable this option if a product is not shipped or there is no shipping cost"></span></label>
                                <label><input type="checkbox" class="checkbox variable_manage_stock" name="variable_manage_stock[0]"> Manage stock? <span class="woocommerce-help-tip" data-tip="Enable this option to enable stock management at variation level"></span></label>
                            </p>

                            <div class="variable_pricing">
                                <p class="form-row form-row-first">
                                    <label>Regular price ($)</label>
                                    <input type="text" size="5" name="variable_regular_price[0]" value="10" class="wc_input_price" placeholder="Variation price (required)">
                                </p>
                                <p class="form-row form-row-last">
                                    <label>Sale price ($) <a href="#" class="sale_schedule">Schedule</a><a href="#" class="cancel_sale_schedule" style="display:none">Cancel schedule</a></label>
                                    <input type="text" size="5" name="variable_sale_price[0]" value="" class="wc_input_price">
                                </p>

                                <div class="sale_price_dates_fields" style="display: none">
                                    <p class="form-row form-row-first">
                                        <label>Sale start date</label>
                                        <input type="text" class="sale_price_dates_from" name="variable_sale_price_dates_from[0]" value="" placeholder="From… YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                                    </p>
                                    <p class="form-row form-row-last">
                                        <label>Sale end date</label>
                                        <input type="text" class="sale_price_dates_to" name="variable_sale_price_dates_to[0]" value="" placeholder="To… YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                                    </p>
                                </div>

                            </div>

                            <div class="show_if_variation_manage_stock" style="display: none;">
                                <p class="form-row form-row-first">
                                    <label>Stock quantity <span class="woocommerce-help-tip" data-tip="Enter a quantity to enable stock management at variation level, or leave blank to use the parent product's options."></span></label>
                                    <input type="number" size="5" name="variable_stock[0]" value="0" step="any">
                                </p>
                                <p class="form-row form-row-last">
                                    <label>Allow backorders?</label>
                                    <select name="variable_backorders[0]">
                                        <option value="no">Do not allow</option>
                                        <option value="notify">Allow, but notify customer</option>
                                        <option value="yes">Allow</option>
                                    </select>
                                </p>
                            </div>

                            <div class="">
                                <p class="form-row form-row-full">
                                    <label>Stock status <span class="woocommerce-help-tip" data-tip="Controls whether or not the product is listed as &quot;in stock&quot; or &quot;out of stock&quot; on the frontend."></span></label>
                                    <select name="variable_stock_status[0]">
                                        <option value="" selected="selected">In stock</option>
                                        <option value="outofstock">Out of stock</option>
                                    </select>
                                </p>
                            </div>

                            <div>
                                <p class="form-row hide_if_variation_virtual form-row-first">
                                    <label>Weight (lbs) <span class="woocommerce-help-tip" data-tip="Enter a weight for this variation or leave blank to use the parent product weight."></span></label>
                                    <input type="text" size="5" name="variable_weight[0]" value="" placeholder="0" class="wc_input_decimal">
                                </p>
                                <p class="form-row dimensions_field hide_if_variation_virtual form-row-last">
                                    <label for="product_length">Dimensions (L×W×H) (in)</label>
                                    <input id="product_length" class="input-text wc_input_decimal" size="6" type="text" name="variable_length[0]" value="" placeholder="0">
                                    <input class="input-text wc_input_decimal" size="6" type="text" name="variable_width[0]" value="" placeholder="0">
                                    <input class="input-text wc_input_decimal last" size="6" type="text" name="variable_height[0]" value="" placeholder="0">
                                </p>

                            </div>
                            <div>
                                <p class="form-row hide_if_variation_virtual form-row-full">
                                    <label>Shipping class</label>
                                    <select name="variable_shipping_class[0]" id="variable_shipping_class[0]" class="postform">
                                        <option value="-1" selected="selected">Same as parent</option>
                                    </select>
                                </p>

                                <p class="form-row form-row-full">
                                    <label>Tax class</label>
                                    <select name="variable_tax_class[0]">
                                        <option value="parent" selected="selected">Same as parent</option>
                                        <option value="">Standard</option><option value="reduced-rate">Reduced Rate</option>
                                        <option value="zero-rate">Zero Rate</option>
                                    </select>
                                </p>

                            </div>
                            <div>
                                <p class="form-row form-row-full">
                                    <label>Variation description</label>
                                    <textarea name="variable_description[0]" rows="3" style="width:100%;"></textarea>
                                </p>
                            </div>
                            <div class="show_if_variation_downloadable" style="display: none;">
                                <div class="form-row form-row-full downloadable_files">
                                    <label>Downloadable files</label>
                                    <div></div>
                                    <div></div>

                                    <table class="widefat">
                                        <thead>
                                            <tr>
                                                <th>Name <span class="woocommerce-help-tip" data-tip="This is the name of the download shown to the customer."></span></th>
                                                <th colspan="2">File URL <span class="woocommerce-help-tip" data-tip="This is the URL or absolute path to the file which customers will get access to. URLs entered here should already be encoded."></span></th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr><th colspan="4"><a href="#" class="button insert" data-row="<tr>
                                                    <td class=&quot;file_name&quot;><input type=&quot;text&quot; class=&quot;input_text&quot; placeholder=&quot;File Name&quot; name=&quot;_wc_variation_file_names[153][]&quot; value=&quot;&quot; /></td>
                                                    <td class=&quot;file_url&quot;><input type=&quot;text&quot; class=&quot;input_text&quot; placeholder=&quot;http://&quot; name=&quot;_wc_variation_file_urls[153][]&quot; value=&quot;&quot; /></td>
                                                    <td class=&quot;file_url_choose&quot; width=&quot;1%&quot;><a href=&quot;#&quot; class=&quot;button upload_file_button&quot; data-choose=&quot;Choose file&quot; data-update=&quot;Insert file URL&quot;>Choose&nbsp;file</a></td>
                                                    <td width=&quot;1%&quot;><a href=&quot;#&quot; class=&quot;delete&quot;>Delete</a></td>
                                                </tr>">Add File</a>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="show_if_variation_downloadable" style="display: none;">
                                <p class="form-row form-row-first">
                                    <label>Download limit <span class="woocommerce-help-tip" data-tip="Leave blank for unlimited re-downloads."></span></label>
                                    <input type="number" size="5" name="variable_download_limit[0]" value="" placeholder="Unlimited" step="1" min="0">
                                </p>
                                <p class="form-row form-row-last">
                                    <label>Download expiry <span class="woocommerce-help-tip" data-tip="Enter the number of days before a download link expires, or leave blank."></span></label>
                                    <input type="number" size="5" name="variable_download_expiry[0]" value="" placeholder="Unlimited" step="1" min="0">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="dokan-variation-action-toolbar">
                <select id="field_to_edit" class="variation-actions dokan-form-control dokan-w5">
                    <option data-global="true" value="add_variation"><?php _e( 'Add variation', 'woocommerce' ); ?></option>
                    <option data-global="true" value="link_all_variations"><?php _e( 'Create variations from all attributes', 'woocommerce' ); ?></option>
                    <option value="delete_all"><?php _e( 'Delete all variations', 'woocommerce' ); ?></option>
                    <optgroup label="<?php esc_attr_e( 'Status', 'woocommerce' ); ?>">
                        <option value="toggle_enabled"><?php _e( 'Toggle &quot;Enabled&quot;', 'woocommerce' ); ?></option>
                        <option value="toggle_downloadable"><?php _e( 'Toggle &quot;Downloadable&quot;', 'woocommerce' ); ?></option>
                        <option value="toggle_virtual"><?php _e( 'Toggle &quot;Virtual&quot;', 'woocommerce' ); ?></option>
                    </optgroup>
                    <optgroup label="<?php esc_attr_e( 'Pricing', 'woocommerce' ); ?>">
                        <option value="variable_regular_price"><?php _e( 'Set regular prices', 'woocommerce' ); ?></option>
                        <option value="variable_regular_price_increase"><?php _e( 'Increase regular prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                        <option value="variable_regular_price_decrease"><?php _e( 'Decrease regular prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                        <option value="variable_sale_price"><?php _e( 'Set sale prices', 'woocommerce' ); ?></option>
                        <option value="variable_sale_price_increase"><?php _e( 'Increase sale prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                        <option value="variable_sale_price_decrease"><?php _e( 'Decrease sale prices (fixed amount or percentage)', 'woocommerce' ); ?></option>
                        <option value="variable_sale_schedule"><?php _e( 'Set scheduled sale dates', 'woocommerce' ); ?></option>
                    </optgroup>
                    <optgroup label="<?php esc_attr_e( 'Inventory', 'woocommerce' ); ?>">
                        <option value="toggle_manage_stock"><?php _e( 'Toggle &quot;Manage stock&quot;', 'woocommerce' ); ?></option>
                        <option value="variable_stock"><?php _e( 'Stock', 'woocommerce' ); ?></option>
                    </optgroup>
                    <optgroup label="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>">
                        <option value="variable_length"><?php _e( 'Length', 'woocommerce' ); ?></option>
                        <option value="variable_width"><?php _e( 'Width', 'woocommerce' ); ?></option>
                        <option value="variable_height"><?php _e( 'Height', 'woocommerce' ); ?></option>
                        <option value="variable_weight"><?php _e( 'Weight', 'woocommerce' ); ?></option>
                    </optgroup>
                    <optgroup label="<?php esc_attr_e( 'Downloadable products', 'woocommerce' ); ?>">
                        <option value="variable_download_limit"><?php _e( 'Download limit', 'woocommerce' ); ?></option>
                        <option value="variable_download_expiry"><?php _e( 'Download expiry', 'woocommerce' ); ?></option>
                    </optgroup>
                    <?php do_action( 'woocommerce_variable_product_bulk_edit_actions' ); ?>
                </select>
                <a class="dokan-btn dokan-btn-default do_variation_action"><?php _e( 'Go', 'woocommerce' ); ?></a>
                <button class="dokan-btn dokan-btn-default"><?php _e( 'Save Variations', 'dokan' ) ?></button>

                <div class="variations-pagenav dokan-right">
                    <span class="displaying-num"><?php printf( _n( '%s item', '%s items', $variations_count, 'woocommerce' ), $variations_count ); ?></span>
                    <span class="expand-close">
                        (<a href="#" class="expand_all"><?php _e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php _e( 'Close', 'woocommerce' ); ?></a>)
                    </span>
                    <span class="pagination-links">
                        <a class="first-page disabled" title="<?php esc_attr_e( 'Go to the first page', 'woocommerce' ); ?>" href="#">&laquo;</a>
                        <a class="prev-page disabled" title="<?php esc_attr_e( 'Go to the previous page', 'woocommerce' ); ?>" href="#">&lsaquo;</a>
                        <span class="paging-select">
                            <label for="current-page-selector-1" class="screen-reader-text"><?php _e( 'Select Page', 'woocommerce' ); ?></label>
                            <select class="page-selector" id="current-page-selector-1" title="<?php esc_attr_e( 'Current page', 'woocommerce' ); ?>">
                                <?php for ( $i = 1; $i <= $variations_total_pages; $i++ ) : ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                             <?php _ex( 'of', 'number of pages', 'woocommerce' ); ?> <span class="total-pages"><?php echo $variations_total_pages; ?></span>
                        </span>
                        <a class="next-page" title="<?php esc_attr_e( 'Go to the next page', 'woocommerce' ); ?>" href="#">&rsaquo;</a>
                        <a class="last-page" title="<?php esc_attr_e( 'Go to the last page', 'woocommerce' ); ?>" href="#">&raquo;</a>
                    </span>
                </div>

                <div class="dokan-clearfix"></div>
            </div>
            <div class="dokan-clearfix"></div>
        <?php endif; ?>

        </div>

    </div>
    <?php
}















