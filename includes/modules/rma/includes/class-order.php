<?php

/**
* Order Manage
*/
class Dokan_RMA_Order {

    use Dokan_RMA_Common;

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'order_item_meta' ], 10, 3 );
    }

    /**
     * Include add-ons line item meta.
     *
     * @since 1.0.0
     *
     * @param  WC_Order_Item_Product $item          Order item data.
     * @param  string                $cart_item_key Cart item key.
     * @param  array                 $values        Order item values.
     *
     * @return  void
     */
    public function order_item_meta( $item, $cart_item_key, $values ) {
        $_product       = $values['data'];
        $_prod_id       = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->get_id();
        $warranty       = $this->get_settings( $_prod_id );
        $warranty_label = $warranty['label'];

        if ( $warranty ) {
            $item_id = $item->save();

            if ( $warranty['type'] == 'addon_warranty' ) {
                $warranty_index = isset( $values['dokan_warranty_index'] ) ? $values['dokan_warranty_index'] : false;

                wc_add_order_item_meta( $item_id, '_dokan_item_warranty_selected', $warranty_index );
            }

            if ( 'no_warranty' !== $warranty['type'] ) {
                wc_add_order_item_meta( $item_id, '_dokan_item_warranty', $warranty );
            }
        }
    }
}
