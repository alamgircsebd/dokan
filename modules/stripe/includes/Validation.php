<?php

namespace WeDevs\DokanPro\Modules\Stripe;

defined( 'ABSPATH' ) || exit;

class Validation {

    /**
     * Constructor method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    private function hooks() {
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'check_vendor_configure_stripe' ], 15, 2 );
    }

    /**
     * Validate checkout if vendor has configured stripe account
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function check_vendor_configure_stripe( $data, $errors ) {
        if ( ! Helper::is_enabled() || Helper::allow_non_connected_sellers() ) {
            return;
        }

        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id                                                          = $item['data']->get_id();
            $available_vendors[ get_post_field( 'post_author', $product_id ) ][] = $item['data'];
        }

        // if it's subscription product return early
        $subscription_product = wc_get_product( $product_id );

        if ( $subscription_product && 'product_pack' === $subscription_product->get_type() ) {
            return;
        }

        $vendor_names = [];

        foreach ( array_keys( $available_vendors ) as $vendor_id ) {
            $vendor       = dokan()->vendor->get( $vendor_id );
            $access_token = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );

            if ( empty( $access_token ) ) {
                $vendor_products = [];

                foreach ( $available_vendors[$vendor_id] as $product ) {
                    $vendor_products[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() );
                }

                $vendor_names[$vendor_id] = [
                    'name'     => sprintf( '<a href="%s">%s</a>', esc_url( $vendor->get_shop_url() ), $vendor->get_shop_name() ),
                    'products' => implode( ', ', $vendor_products )
                ];
            }
        }

        foreach ( $vendor_names as $vendor_id => $data ) {
            $errors->add( 'stipe-not-configured', sprintf( __( '<strong>Error!</strong> You cannot complete your purchase until <strong>%s</strong> has enabled Stripe as a payment gateway. Please remove %s to continue.', 'dokan' ), $data['name'], $data['products'] ) );
        }
    }
}
