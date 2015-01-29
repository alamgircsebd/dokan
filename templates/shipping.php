<?php 

$country_obj = new WC_Countries();
$countries   = $country_obj->countries;
$states      = $country_obj->states;
$user_id     = get_current_user_id();

$dps_enable_shipping     = get_user_meta( $user_id, '_dps_shipping_enable', true );
$dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
$dps_additional_product  = get_user_meta( $user_id, '_dps_additional_product', true );
$dps_additional_qty      = get_user_meta( $user_id, '_dps_additional_qty', true );
$dps_form_location       = get_user_meta( $user_id, '_dps_form_location', true );
$dps_country_rates       = get_user_meta( $user_id, '_dps_country_rates', true );
$dps_state_rates         = get_user_meta( $user_id, '_dps_state_rates', true );
$dps_shipping_policy     = get_user_meta( $user_id, '_dps_ship_policy', true );
$dps_refund_policy       = get_user_meta( $user_id, '_dps_refund_policy', true );


?>
<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array( 'active_menu' => 'shipping' ) ); ?>

    <div class="dokan-dashboard-content dokan-settings-content">
        <article class="dokan-settings-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title">
                    <?php _e( 'Shipping', 'dokan' ); ?>
                </h1>
            </header><!-- .dokan-dashboard-header -->
            
            <?php
            if ( isset( $_GET['message'] ) && $_GET['message'] == 'shipping_saved' ) {
                ?>
                <div class="dokan-message">
                    <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                    <strong><?php _e('Shipping options saved successfully','dokan'); ?></strong>
                </div>
                <?php
            }
            ?>

            <form method="post" id="shipping-form"  action="" class="dokan-form-horizontal">

                <?php  wp_nonce_field( 'dokan_shipping_form_field', 'dokan_shipping_form_field_nonce' ); ?>

                <?php do_action( 'dokan_shipping_form_top' ); ?>
                
                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="dps_enable_shipping" style="margin-top:9px"><?php _e( 'Enable Shipping', 'dokan' ); ?></label>
                    <div class="dokan-w5 dokan-text-left">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="dps_enable_shipping" value="no">
                                <input type="checkbox" name="dps_enable_shipping" value="yes" <?php checked( 'yes', $dps_enable_shipping, true ); ?>> <?php _e( 'Enable shipping functionality', 'dokan' ); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="dokan-shipping-wrapper">
     
                    <div class="dokan-form-group dokan-shipping-price dokan-shipping-type-price">
                        <label class="dokan-w3 dokan-control-label" for="shipping_type_price"><?php _e( 'Default Shipping Price', 'dokan' ); ?></label>

                        <div class="dokan-w5 dokan-text-left">
                            <input id="shipping_type_price" value="<?php echo $dps_shipping_type_price; ?>" name="dps_shipping_type_price" placeholder="9.99" class="dokan-form-control" type="number" step="any" min="0">
                        </div>
                    </div>

                    <div class="dokan-form-group dokan-shipping-price dokan-shipping-add-product">
                        <label class="dokan-w3 dokan-control-label" for="dps_additional_product"><?php _e( 'Per Product Additional Price', 'dokan' ); ?></label>

                        <div class="dokan-w5 dokan-text-left">
                            <input id="additional_product" value="<?php echo $dps_additional_product; ?>" name="dps_additional_product" placeholder="9.99" class="dokan-form-control" type="number" step="any" min="0">
                        </div>
                    </div>

                    <div class="dokan-form-group dokan-shipping-price dokan-shipping-add-qty">
                        <label class="dokan-w3 dokan-control-label" for="dps_additional_qty"><?php _e( 'Per Qty Additional Price', 'dokan' ); ?></label>

                        <div class="dokan-w5 dokan-text-left">
                            <input id="additional_qty" value="<?php echo $dps_additional_qty; ?>" name="dps_additional_qty" placeholder="9.99" class="dokan-form-control" type="number" step="any" min="0">
                        </div>
                    </div>

                    <div class="dokan-form-group">
                        <label class="dokan-w3 dokan-control-label" for="_dps_ship_policy"><?php _e( 'Shipping Policy', 'dokan' ); ?></label>
                        <div class="dokan-w6 dokan-text-left">
                            <textarea name="dps_ship_policy" id="" class="dokan-form-control"><?php echo $dps_shipping_policy; ?></textarea>
                        </div>
                    </div>

                    <div class="dokan-form-group">
                        <label class="dokan-w3 dokan-control-label" for="_dps_refund_policy"><?php _e( 'Refund Policy', 'dokan' ); ?></label>
                        <div class="dokan-w6 dokan-text-left">
                            <textarea name="dps_refund_policy" id="" class="dokan-form-control"><?php echo $dps_refund_policy; ?></textarea>
                        </div>
                    </div>

                    <div class="dokan-form-group">
                        <label class="dokan-w3 dokan-control-label" for="dps_form_location"><?php _e( 'Ships from:', 'dokan' ); ?></label>

                        <div class="dokan-w5">
                            <select name="dps_form_location" class="dokan-form-control">
                                <?php country_dropdown( $countries, $dps_form_location ); ?>
                            </select>
                        </div>
                    </div>

                    <div class="dokan-form-group">

                        <div class="dokan-w8 dps-main-wrapper">
                            <div class="dokan-shipping-location-wrapper">
                            
                            <?php if ( $dps_country_rates ) : ?>
                            
                                <?php foreach ( $dps_country_rates as $country => $country_rate ) : ?>
                            
                                    <div class="dps-shipping-location-content">

                                        <table class="dps-shipping-table">
                                            <tbody>

                                                <tr class="dps-shipping-location">
                                                    <td width="40%">
                                                        <label for=""><?php _e( 'Ship to', 'dokan' ); ?></label>
                                                        <select name="dps_country_to[]" class="dokan-form-control dps_country_selection" id="dps_country_selection">
                                                            <?php country_dropdown( $countries, $country, '' ); ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <label for=""><?php _e( 'Cost', 'dokan' ); ?></label>
                                                        <div class="dokan-input-group">
                                                            <span class="dokan-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                            <input type="text" placeholder="9.99" class="form-control" name="dps_country_to_price[]" value="<?php echo esc_attr( $country_rate ); ?>">
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr class="dps-shipping-states-wrapper">
                                                    <table class="dps-shipping-states">
                                                        <tbody>
                                                           <?php if ( $dps_state_rates ): ?>
                                                                <?php if ( isset( $dps_state_rates[$country] ) ): ?>
                                                                
                                                                    <?php foreach ( $dps_state_rates[$country] as $state => $state_rate ): ?>
                                                                        
                                                                        <?php if ( isset( $states[$country] ) && !empty( $states[$country] ) ): ?>
                                                                            
                                                                            <tr>
                                                                                <td>
                                                                                    <label for=""><?php _e( 'State', 'dokan' ) ?></label>
                                                                                    <select name="dps_state_to[<?php echo $country ?>][]" class="dokan-form-control" id="dps_state_selection">
                                                                                        <?php state_dropdown( $states[$country], $state, true ); ?>
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <label for=""><?php _e( 'Cost', 'dokan' ); ?></label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                                                        <input type="text" placeholder="9.99" value="<?php echo $state_rate; ?>" class="form-control" name="dps_state_to_price[<?php echo $country; ?>][]">
                                                                                    </div>
                                                                                </td>

                                                                                <td width="15%">
                                                                                    <label for=""></label>
                                                                                    <div>
                                                                                        <a class="dps-add" href="#"><i class="fa fa-plus-circle fa-2x"></i></a>
                                                                                        <a class="dps-remove" href="#"><i class="fa fa-minus-circle fa-2x"></i></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>  
                                                                        
                                                                        <?php else: ?>

                                                                            <tr>
                                                                                <td>
                                                                                    <label for=""><?php _e( 'State', 'dokan' ); ?></label>
                                                                                    <input type="text" name="dps_state_to[<?php echo $country ?>][]" class="dokan-form-control" placeholder="State name" value="<?php echo $state; ?>">
                                                                                </td>
                                                                                <td>
                                                                                    <label for=""><?php _e( 'Cost', 'dokan' ); ?></label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                                                        <input type="text" placeholder="9.99" class="form-control" name="dps_state_to_price[<?php echo $country; ?>][]" value="<?php echo $state_rate; ?>">
                                                                                    </div>
                                                                                </td>
                                                                                
                                                                                <td width="14%">
                                                                                    <label for=""></label>
                                                                                    <div>
                                                                                        <a class="dps-add" href="#"><i class="fa fa-plus-circle fa-2x"></i></a>
                                                                                        <a class="dps-remove" href="#"><i class="fa fa-minus-circle fa-2x"></i></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>     

                                                                        <?php endif ?>

                                                                    <?php endforeach ?>
                                                                
                                                                <?php endif ?>
                                                           
                                                            <?php endif ?> 
                                                        </tbody>
                                                    </table>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <a href="#" class="dokan-btn dokan-btn-default dps-shipping-remove"><?php _e( 'X', 'dokan' ); ?></a>
                                    </div>

                                <?php endforeach; ?>
                            
                            <?php else: ?>
                    
                                <div class="dps-shipping-location-content">
                                    <table class="dps-shipping-table">
                                        <tbody>                                    
                                            <tr class="dps-shipping-location">
                                                <td>
                                                    <label for=""><?php _e( 'Ship to', 'dokan' ); ?></label>
                                                    <select name="dps_country_to[]" class="dokan-form-control dps_country_selection" id="dps_country_selection">
                                                        <?php country_dropdown( $countries, '', false ); ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <label for=""><?php _e( 'Cost', 'dokan' ); ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                        <input type="text" placeholder="9.99" class="form-control" name="dps_country_to_price[]">
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="dps-shipping-states-wrapper">
                                                <table class="dps-shipping-states">
                                                    <tbody></tbody>
                                                </table>
                                            </tr>

                                        </tbody>
                                    </table>
                                    <a href="#" class="dokan-btn dokan-btn-default dps-shipping-remove"><?php _e( 'X', 'dokan' ); ?></a>
                                </div>
                            <?php endif; ?>    
                            
                            </div>
                            <a href="#" class="dokan-btn dokan-btn-default dps-shipping-add"><?php _e( 'Add Location', 'dokan' ); ?></a>
                        </div>
                    </div>

                </div>

                <?php do_action( 'dokan_shipping_form_bottom' ); ?>

                <div class="dokan-form-group">

                    <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left:23%;">
                        <input type="submit" name="dokan_update_shipping_options" class="btn btn-primary" value="<?php esc_attr_e( 'Save Settings', 'dokan' ); ?>">
                    </div>
                </div>

            </form>
        </article>
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->

<!-- Added for Render content via Jquery -->

<div class="dps-shipping-location-content" id="dps-shipping-hidden-lcoation-content">
    <table class="dps-shipping-table">
        <tbody>
     
            <tr class="dps-shipping-location">
                <td>
                    <label for=""><?php _e( 'Ship to', 'dokan' ); ?></label>
                    <select name="dps_country_to[]" class="dokan-form-control dps_country_selection" id="dps_country_selection">
                        <?php country_dropdown( $countries, '', false ); ?>
                    </select>
                </td>
                <td>
                    <label for=""><?php _e( 'Cost', 'dokan' ); ?></label>
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                        <input type="text" placeholder="9.99" class="form-control" name="dps_country_to_price[]">
                    </div>
                </td>
            </tr>
            <tr class="dps-shipping-states-wrapper">
                <table class="dps-shipping-states">
                    <tbody></tbody>
                </table>
            </tr>
        </tbody>
    </table>
    <a href="#" class="dokan-btn dokan-btn-default dps-shipping-remove"><?php _e( 'X', 'dokan' ); ?></a>
</div>  

<!-- End of render content via jquery -->




















