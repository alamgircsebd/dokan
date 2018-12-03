<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// if ( ! class_exists( 'Dokan_Send_Coupon_Email' ) ) :

/**
 * New Product Published Email to vendor.
 *
 * An email sent to the vendor when a pending Product is published by admin.
 *
 * @class       Dokan_Auction_Email
 * @version     2.6.8
 * @author      weDevs
 * @extends     WC_Email
 */
class Dokan_Send_Coupon_Email extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'Dokan_Send_Coupon_Email';
        $this->title          = __( 'Send Coupon To Customer', 'dokan' );
        $this->description    = __( 'This email send to customer when customer send return request and vendor send a store credit to customer', 'dokan' );

        $this->template_base  = DOKAN_RMA_DIR . '/templates/';
        $this->template_html  = 'emails/send-coupon.php';
        $this->template_plain = 'emails/plain/send-coupon.php';
        $this->customer_email = true;

        // Triggers for this email
        add_action( 'dokan_send_coupon_to_customer', [ $this, 'trigger' ], 30, 2 );

        // Call parent constructor
        parent::__construct();

        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @since  3.1.0
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_name}] A new auction product is added by ({seller_name}) - {product_title}', 'dokan' );
    }

    /**
     * Get email heading.
     *
     * @since  3.1.0
     * @return string
     */
    public function get_default_heading() {
        return __( 'New auction product is added by Vendor', 'dokan' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $product_id The product ID.
     * @param array $postdata.
     */
    public function trigger( $coupon, $data ) {
        $this->object = $coupon;


        $this->setup_locale();
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        $this->restore_locale();
    }


    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'couopn'       => $this->object,
            'email_heading' => $this->get_heading(),
            'plain_text'    => false,
            'email'         => $this,
            'data'          => $this->replace
        ), 'dokan/', $this->template_base );
        return ob_get_clean();
    }

    /**
     * Get content plain.
     *
     * @access public
     * @return string
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'coupon'       => $this->object,
            'email_heading' => $this->get_heading(),
            'plain_text'    => true,
            'email'         => $this,
            'data'          => $this->replace
        ), 'dokan/', $this->template_base );
        return ob_get_clean();
    }


    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable this email notification', 'dokan' ),
                'default'       => 'yes',
            ),
            'recipient' => array(
                'title'         => __( 'Recipient(s)', 'dokan' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'dokan' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder'   => '',
                'default'       => '',
                'desc_tip'      => true,
            ),
            'subject' => array(
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{blogname}</code>' ),
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __( 'Email heading', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{product_title}</code>' ),
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'dokan' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'dokan' ),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'       => $this->get_email_type_options(),
                'desc_tip'      => true,
            ),
        );
    }
}

// endif;

return new Dokan_Send_Coupon_Email();
