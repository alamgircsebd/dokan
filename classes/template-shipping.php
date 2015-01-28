<?php
/**
 * Dokan Shipping Class
 *
 * @author weDves
 */

class Dokan_Template_Shipping {

    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Template_Shipping();
        }

        return $instance;
    }

    public function __construct() {

        add_action( 'woocommerce_shipping_init', array($this, 'include_shipping' ) );
        add_action( 'woocommerce_shipping_methods', array($this, 'register_shipping' ) );
        add_action( 'woocommerce_product_tabs', array($this, 'register_product_tab' ) );
        add_action( 'woocommerce_after_checkout_validation', array($this, 'validate_country' ) );
    }


    /**
     * Include main shipping integration
     *
     * @return void
     */
    function include_shipping() {
        require_once DOKAN_INC_DIR . '/shipping-gateway/shipping.php';
    }

    /**
     * Register shipping method
     *
     * @param array $methods
     * @return array
     */
    function register_shipping( $methods ) {
        $methods[] = 'Dokan_WC_Per_Product_Shipping';

        return $methods;
    }

    /**
     * Validate the shipping area
     *
     * @param  array $posted
     * @return void
     */
    function validate_country( $posted ) {
        // print_r($posted);

        $shipping_method = WC()->session->get( 'chosen_shipping_methods' );

        // per product shipping was not chosen
        if ( ! is_array( $shipping_method ) || !in_array( 'dokan_per_product', $shipping_method ) ) {
            wc_add_notice( __( 'shipping was not chosen', 'woocommerce' ), 'error' );
            return;
        }

        if ( isset( $posted['ship_to_different_address'] ) && $posted['ship_to_different_address'] == '1' ) {
            $shipping_country = $posted['shipping_country'];
        } else {
            $shipping_country = $posted['billing_country'];
        }

        // echo $shipping_country;
        $packages = WC()->shipping->get_packages();
        $packages = reset( $packages );

        if ( !isset( $packages['contents'] ) ) {
            return;
        }

        $products = $packages['contents'];
        $destination_country = isset( $packages['destination']['country'] ) ? $packages['destination']['country'] : '';
        $destination_state = isset( $packages['destination']['state'] ) ? $packages['destination']['state'] : '';


        $errors = array();
        foreach ( $products as $key => $product) {

            $seller_id         = get_post_field( 'post_author', $product['product_id'] );
            
            if ( ! Dokan_WC_Per_Product_Shipping::is_shipping_enabled_for_seller( $seller_id ) ) {
                continue;
            }

            $dps_country_rates = get_user_meta( $seller_id, '_dps_country_rates', true );
            $dps_state_rates   = get_user_meta( $seller_id, '_dps_state_rates', true ); 
            
            $has_found = false;

            if( isset( $dps_state_rates[$destination_country] ) ) {

                if( array_key_exists( $destination_state, $dps_state_rates[$destination_country] ) ) {
                    var_dump( $dps_state_rates[$destination_country][$destination_state] );
                    if( isset( $dps_state_rates[$destination_country][$destination_state] ) ) {
                        $has_found = true;
                    } else {
                        $has_found = true;
                    }

                } elseif ( array_key_exists( 'everywhere', $dps_state_rates[$destination_country] ) ) {
                    $has_found = true;
                } else {
                    $has_found = false;
                }
        
            } else {
                $has_found = false;
            }  

        
            if ( ! $has_found ) {
                $errors[] = sprintf( '<a href="%s">%s</a>', get_permalink( $product['product_id'] ), get_the_title( $product['product_id'] ) );
            }
        }

        var_dump( $errors );
        var_dump( $has_found );
        die();

        if ( $errors ) {
            if ( count( $errors ) == 1 ) {
                $message = sprintf( __( 'This product does not ship to your chosen location: %s'), implode( ', ', $errors ) );
            } else {
                $message = sprintf( __( 'These products do not ship to your chosen location.: %s'), implode( ', ', $errors ) );
            }

            wc_add_notice( $message, 'error' );
        }
    }


     /**
     * Adds a seller tab in product single page
     *
     * @param array $tabs
     * @return array
     */
    function register_product_tab( $tabs ) {
        global $post;
        $enabled = get_user_meta( $post->post_author, '_dps_shipping_enable', true );
        if ( $enabled != 'yes' ) {
            return $tabs;
        }

        $tabs['shipping'] = array(
            'title' => __( 'Shipping', 'dokan-shipping' ),
            'priority' => 12,
            'callback' => array($this, 'shipping_tab')
        );

        return $tabs;
    }

    function shipping_tab() {
        global $post;

        $from = get_user_meta( $post->post_author, '_dps_form_location', true );
        $dps_country_rates = get_user_meta( $post->post_author, '_dps_country_rates', true );
        $dps_state_rates = get_user_meta( $post->post_author, '_dps_state_rates', true );
        $shipping_policy = get_user_meta( $post->post_author, '_dps_ship_policy', true );
        $refund_policy = get_user_meta( $post->post_author, '_dps_refund_policy', true );

        $country_obj = new WC_Countries();
        $countries = $country_obj->countries;
        $states = $country_obj->states;

        ?>

        <?php if ( $dps_country_rates ) { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php _e( 'Ship To', 'dokan-shipping' ); ?></th>
                        <th><?php _e( 'Cost', 'dokan-shipping' ); ?></th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($dps_country_rates as $country => $cost ) { ?>
                    <tr>
                        <td>
                            <?php
                            if ( $country == 'everywhere' ) {
                                _e( 'Everywhere Else', 'dokan-shipping' );
                            } else {
                                echo $countries[$country];
                            }
                            ?>
                        </td>
                        <td><?php echo wc_price( $cost ); ?></td>
                    </tr>
                    <?php if ( $dps_state_rates ): ?>
                        <tr>
                            <td>
                                <table width="100%" class="table">
                                    <thead>
                                        <tr>
                                            <th><?php _e( 'Ship To state', 'dokan-shipping' ); ?></th>
                                            <th><?php _e( 'Cost', 'dokan-shipping' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dps_state_rates[$country] as $state_code => $state_cost ): ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                    if ( $state_code == 'everywhere' ) {
                                                        _e( 'Everywhere Else', 'dokan-shipping' );
                                                    } else {
                                                        if( isset( $states[$country][$state_code] ) ) {
                                                            echo $states[$country][$state_code];
                                                        } else {
                                                            echo $state_code;
                                                        }    
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo wc_price( $state_cost ); ?></td>

                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>  
                            </td>   
                        </tr>
                    <?php endif ?>
                <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

            <p>&nbsp;</p>

        <?php if ( $shipping_policy ) { ?>
            <strong><?php _e( 'Shipping Policy', 'dokan-shipping' ); ?></strong>
            <hr>

            <?php echo wpautop( $shipping_policy ); ?>
        <?php } ?>

        <p>&nbsp;</p>

        <?php if ( $refund_policy ) { ?>
            <strong><?php _e( 'Refund Policy', 'dokan-shipping' ); ?></strong>
            <hr>

            <?php echo wpautop( $refund_policy ); ?>
        <?php } ?>
        <?php
    }



    
}