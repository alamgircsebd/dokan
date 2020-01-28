<?php

namespace WeDevs\Dokan\Emails;

use WC_Email;

/**
 * Class VendorNewOrder file
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'VendorNewOrder' ) ) :

    /**
     * New Order Email.
     *
     * An email sent to the admin when a new order is received/paid for.
     *
     * @class       VendorNewOrder
     * @version     2.0.0
     * @package     WooCommerce/Classes/Emails
     * @extends     WC_Email
     */
    class VendorNewOrder extends WC_Email {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id             = 'dokan_vendor_new_order';
            $this->title          = __( 'Dokan Vendor New Order', 'woocommerce' );
            $this->description    = __( 'New order emails are sent to chosen recipient(s) when a new order is received.', 'woocommerce' );
            $this->template_html  = 'emails/vendor-new-order.php';
            $this->template_plain = 'emails/plain/vendor-new-order.php';
            $this->template_base  = DOKAN_DIR.'/templates/';

            $this->placeholders   = array(
                '{site_title}'   => $this->get_blogname(),
                '{order_date}'   => '',
                '{order_number}' => '',
            );

            // Triggers for this email.
            add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'custom_trigger_email_action' ), 10, 2 );
            add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
            add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
            add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
            add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
            add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );

            // Call parent constructor.
            parent::__construct();

            // Other settings.
            $this->recipient = 'vendor@ofthe.product';
        }

        /**
         * Get email subject.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_subject() {
            return __( '[{site_title}] New customer order ({order_number}) - {order_date}', 'woocommerce' );
        }

        /**
         * Get email heading.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'New Customer Order: #{order_number}', 'woocommerce' );
        }

        /**
         * Trigger the sending of this email.
         *
         * @param int            $order_id The order ID.
         * @param WC_Order|false $order Order object.
         */
        public function custom_trigger_email_action( $order_id, $order = false ) {
            if ( ! $this->is_enabled() ) {
                return;
            }

            $this->setup_locale();

            if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
                $order = wc_get_order( $order_id );
            }

            if ( is_a( $order, 'WC_Order' ) ) {
                $this->object                           = $order;
                $this->placeholders['{order_date}']     = wc_format_datetime( $this->object->get_date_created() );
                $this->placeholders['{order_number}']   = $this->object->get_order_number();
            }
            
            $sellers 	  = dokan_get_seller_id_by_order( $order_id );
            if ( empty( $sellers ) ) {
                return;
            }

            // check has sub order 
	        if ( get_post_meta( $order_id, 'has_sub_order', true ) ) {
	        	foreach ($sellers as $seller) {
	        		$seller_info       	= get_userdata( $seller );
			        $seller_email 		= $seller_info->user_email;
			        $this->order_info 	= dokan_get_vendor_order_details( $order_id, $seller );

    			    $this->send( $seller_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	        	}
	        }else{
	        	$seller_info       	= get_userdata( $sellers );
			    $seller_email 		= $seller_info->user_email;
	        	$this->order_info 	= dokan_get_vendor_order_details( $order_id, $sellers );

    	        $this->send( $seller_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	        }

            $this->restore_locale();
        }

        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html, array(
                    'order'         => $this->object,
                    'email_heading' => $this->get_heading(),
                    'sent_to_admin' => true,
                    'plain_text'    => false,
                    'email'         => $this,
                    'order_info'    => $this->order_info,
                ), 'dokan' ,$this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain, array(
                    'order'         => $this->object,
                    'email_heading' => $this->get_heading(),
                    'sent_to_admin' => true,
                    'plain_text'    => true,
                    'email'         => $this,
                    'order_info'    => $this->order_info,
                ), 'dokan/', $this->template_base
            );
        }

        /**
         * Initialise settings form fields.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled'    => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'woocommerce' ),
                    'default' => 'yes',
                ),
                'subject'    => array(
                    'title'       => __( 'Subject', 'woocommerce' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
                    'placeholder' => $this->get_default_subject(),
                    'default'     => '',
                ),
                'heading'    => array(
                    'title'       => __( 'Email heading', 'woocommerce' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
                    'placeholder' => $this->get_default_heading(),
                    'default'     => '',
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    'desc_tip'    => true,
                ),
            );
        }
    }

endif;

return new VendorNewOrder();