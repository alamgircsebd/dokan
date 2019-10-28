<?php

class Dokan_ShipStation_Api_ShipNotify extends Dokan_ShipStation_Api_Request {

    /**
     * Dokan Seller object
     *
     * @since 1.0.0
     *
     * @var null|WP_User_Query
     */
    public $seller = null;

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @param bool          $authenticated
     * @param WP_User_Query $seller
     */
    public function __construct( $authenticated, $seller ) {
        if ( ! $authenticated ) {
            exit;
        }

        $this->seller = $seller;
    }

    /**
     * Handling the request.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function request() {
        $this->validate_input( array( 'order_number', 'carrier' ) );

        $shipped_status = get_user_meta( $this->seller->ID, 'shipstation_shipped_status', true );

        if ( empty( $shipped_status ) ) {
            $shipped_status = 'wc-completed';
        }

        $timestamp          = current_time( 'timestamp' );
        $shipstation_xml    = $this->get_raw_post_data();
        $shipped_items      = array();
        $shipped_item_count = 0;
        $order_shipped      = false;
        $xml_order_id       = 0;

        $can_parse_xml = true;

        if ( empty( $shipstation_xml ) ) {
            $can_parse_xml = false;
            $this->log( __( 'Missing ShipNotify XML input.', 'dokan' ) );

            // For unknown reason raw post data can be empty. Log all requests
            // information might help figuring out the culprit.
            //
            // @see https://github.com/woocommerce/woocommerce-shipstation/issues/80.
            $this->log( '$_REQUEST: ' . print_r( $_REQUEST, true ) );
        }

        if ( ! function_exists( 'simplexml_import_dom' ) ) {
            $can_parse_xml = false;
            $this->log( __( 'Missing SimpleXML extension for parsing ShipStation XML.', 'dokan' ) );
        }

        // Try to parse XML first since it can contain the real OrderID.
        if ( $can_parse_xml ) {
            $this->log( __( 'ShipNotify XML: ', 'dokan' ) . print_r( $shipstation_xml, true ) );

            $xml = $this->get_parsed_xml( $shipstation_xml );

            if ( ! $xml ) {
                $this->log( __( 'Cannot parse XML', 'dokan' ) );
                status_header( 500 );
            }

            if ( isset( $xml->ShipDate ) ) {
                $timestamp = strtotime( (string) $xml->ShipDate );
            }

            if ( isset( $xml->OrderID ) && $_GET['order_number'] !== (string) $xml->OrderID ) {
                $xml_order_id = (int) $xml->OrderID;
            }
        }

        // Get real order ID from XML otherwise try to convert it from the order number.
        $order_id        = ! $xml_order_id ? $this->get_order_id( wc_clean( $_GET['order_number'] ) ) : $xml_order_id;
        $tracking_number = empty( $_GET['tracking_number'] ) ? '' : wc_clean( $_GET['tracking_number'] );
        $carrier         = empty( $_GET['carrier'] ) ? '' : wc_clean( $_GET['carrier'] );
        $order           = wc_get_order( $order_id );

        if ( false === $order || ! is_object( $order ) ) {
            $this->log( sprintf( __( 'Order %s can not be found.', 'dokan' ), $order_id ) );
            exit;
        }

        // Get real order ID from order object.
        $order_id = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();
        if ( empty( $order_id ) ) {
            $this->log( sprintf( __( 'Invalid order ID: %s', 'dokan' ), $order_id ) );
            exit;
        }

        // Maybe parse items from posted XML (if exists).
        if ( $can_parse_xml && isset( $xml->Items ) ) {
            $items = $xml->Items;
            if ( $items ) {
                foreach ( $items->Item as $item ) {
                    $this->log( __( 'ShipNotify Item: ', 'dokan' ) . print_r( $item, true ) );

                    $item_sku    = wc_clean( (string) $item->SKU );
                    $item_name   = wc_clean( (string) $item->Name );
                    $qty_shipped = absint( $item->Quantity );

                    if ( $item_sku ) {
                        $item_sku = ' (' . $item_sku . ')';
                    }

                    $item_id = wc_clean( (int) $item->LineItemID );
                    if ( ! $this->is_shippable_item( $order, $item_id ) ) {
                        $this->log( sprintf( __( 'Item %s is not shippable product. Skipping.', 'dokan' ), $item_name ) );
                        continue;
                    }

                    $shipped_item_count += $qty_shipped;
                    $shipped_items[] = $item_name . $item_sku . ' x ' . $qty_shipped;
                }
            }
        }

        // Number of items in WC order.
        $total_item_count = $this->order_items_to_ship_count( $order );

        // If we have a list of shipped items, we can customise the note + see
        // if the order is not yet complete.
        if ( sizeof( $shipped_items ) > 0 ) {
            $order_note = sprintf(
                /* translators: 1) shipped items 2) carrier's name 3) shipped date, 4) tracking number */
                __( '%1$s shipped via %2$s on %3$s with tracking number %4$s.', 'dokan' ),
                esc_html( implode( ', ', $shipped_items ) ),
                esc_html( $carrier ),
                date_i18n( get_option( 'date_format' ), $timestamp ),
                $tracking_number
            );

            $current_shipped_items = max( (int) get_post_meta( $order_id, '_shipstation_shipped_item_count', true ), 0 );

            if ( ( $current_shipped_items + $shipped_item_count ) >= $total_item_count ) {
                $order_shipped = true;
            }

            $this->log(
                sprintf(
                    /* translators: 1) number of shipped items 2) total shipped items 3) order ID */
                    __( 'Shipped %1$d out of %2$d items in order %3$s', 'dokan' ),
                    $shipped_item_count,
                    $total_item_count,
                    $order_id
                )
            );

            update_post_meta( $order_id, '_shipstation_shipped_item_count', $current_shipped_items + $shipped_item_count );

        } else {
            // If we don't have items from SS and order items in WC, or cannot parse
            // the XML, just complete the order as a whole.
            $order_shipped = 0 === $total_item_count || ! $can_parse_xml;

            $order_note = sprintf(
                /* translators: 1) carrier's name 2) shipped date, 3) tracking number */
                __( 'Items shipped via %1$s on %2$s with tracking number %3$s (ShipStation).', 'dokan' ),
                esc_html( $carrier ),
                date_i18n( get_option( 'date_format' ), $timestamp ),
                $tracking_number
            );

            $this->log( sprintf( __( 'No items found - shipping entire order %d.', 'dokan' ), $order_id ) );
        }

        // Tracking information - WC Shipment Tracking extension.
        if ( class_exists( 'WC_Shipment_Tracking' ) ) {
            if ( function_exists( 'wc_st_add_tracking_number' ) ) {
                wc_st_add_tracking_number( $order_id, $tracking_number, strtolower( $carrier ), $timestamp );
            } else {
                // You're using Shipment Tracking < 1.4.0. Please update!
                update_post_meta( $order_id, '_tracking_provider', strtolower( $carrier ) );
                update_post_meta( $order_id, '_tracking_number', $tracking_number );
                update_post_meta( $order_id, '_date_shipped', $timestamp );
            }

            $is_customer_note = 0;
        } else {
            $is_customer_note = 1;
        }

        $order->add_order_note( $order_note, $is_customer_note );

        // Update order status.
        if ( $order_shipped ) {
            $order->update_status( $shipped_status );

            /* translators: 1) order ID 2) shipment status */
            $this->log( sprintf( __( 'Updated order %1$s to status %2$s', 'dokan' ), $order_id, $shipped_status ) );
        }

        // Trigger action for other integrations.
        $shipstation_data = array(
            'tracking_number' => $tracking_number,
            'carrier'         => $carrier,
            'ship_date'       => $timestamp,
            'xml'             => $shipstation_xml,
        );

        do_action( 'dokan_shipstation_shipnotify', $order, $shipstation_data );

        status_header( 200 );
    }

    /**
     * See how many items in the order need shipping.
     *
     * @since 1.0.0
     *
     * @param WC_Order $order Order object.
     *
     * @return int
     */
    private function order_items_to_ship_count( $order ) {
        $needs_shipping = 0;

        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $order->get_product_from_item( $item );

            if ( is_a( $product, 'WC_Product' ) && $product->needs_shipping() ) {
                $needs_shipping += $item['qty'];
            }
        }

        return $needs_shipping;
    }

    /**
     * Check whether a given item ID is shippable item.
     *
     * @since 1.0.0
     *
     * @param WC_Order $order   Order object.
     * @param int      $item_id Item ID.
     *
     * @return bool Returns true if item is shippable product.
     */
    private function is_shippable_item( $order, $item_id ) {
        if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
            $item = $order->get_item( $item_id );
            if ( ! is_callable( array( $item, 'get_product' ) ) ) {
                return false;
            }

            $product = $item->get_product();
        } else {
            $items = $order->get_items();
            if ( ! isset( $items[ $item_id ] ) ) {
                return false;
            }

            $product = $order->get_product_from_item( $items[ $item_id ] );
        }

        if ( ! $product ) {
            return false;
        }

        return $product->needs_shipping();
    }

    /**
     * Get the order ID from the order number.
     *
     * @since 1.0.0
     *
     * @param string $order_number Order number.
     *
     * @return integer
     */
    private function get_order_id( $order_number ) {
        // Try to match an order number in brackets.
        preg_match( '/\((.*?)\)/', $order_number, $matches );
        if ( is_array( $matches ) && isset( $matches[1] ) ) {
            $order_id = $matches[1];

        // Try to convert number for Sequential Order Number.
        } elseif ( function_exists( 'wc_sequential_order_numbers' ) ) {
            $order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_number );

        // Try to convert number for Sequential Order Number Pro.
        } elseif ( function_exists( 'wc_seq_order_number_pro' ) ) {
            $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_number );

        // Default to not converting order number.
        } else {
            $order_id = $order_number;
        }

        if ( 0 === $order_id ) {
            $order_id = $order_number;
        }

        return apply_filters( 'dokan_shipstation_get_order_id', absint( $order_id ) );
    }

    /**
     * Retrieves the raw request data (body).
     *
     * `$HTTP_RAW_POST_DATA` is deprecated in PHP 5.6 and removed in PHP 5.7,
     * it's used here for server that has issue with reading `php://input`
     * stream.
     *
     * @since 1.0.0
     *
     * @return string Raw request data.
     */
    private function get_raw_post_data() {
        global $HTTP_RAW_POST_DATA;

        if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
            $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
        }

        return $HTTP_RAW_POST_DATA;
    }

    /**
     * Get Parsed XML response.
     *
     * @since 1.0.0
     *
     * @param  string $xml XML.
     *
     * @return string|bool
     */
    private function get_parsed_xml( $xml ) {
        if ( ! class_exists( 'Dokan_Safe_DOMDocument' ) ) {
            include_once( DOKAN_SHIPSTATION_INCLUDES . '/class-dokan-safe-domdocument.php' );
        }

        libxml_use_internal_errors( true );

        $dom     = new Dokan_Safe_DOMDocument;
        $success = $dom->loadXML( $xml );

        if ( ! $success ) {
            $this->log( 'wpcom_safe_simplexml_load_string(): Error loading XML string' );
            return false;
        }

        if ( isset( $dom->doctype ) ) {
            $this->log( 'wpcom_safe_simplexml_import_dom(): Unsafe DOCTYPE Detected' );
            return false;
        }

        return simplexml_import_dom( $dom, 'SimpleXMLElement' );
    }
}
