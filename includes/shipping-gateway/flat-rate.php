<?php

/**
* Dokan Flat rate shipping
*/
class Dokan_Flat_Rate extends WC_Shipping_Method {

    /**
     * Constructor for your shipping class
     *
     * @access public
     *
     * @return void
     */
    public function __construct() {
        $this->id                 = 'dokan_product_shipping';
        $this->method_title       = __( 'Dokan Shipping' );
        $this->method_description = __( 'Enable vendors to set shipping cost per product and per country' );

        $this->enabled      = $this->get_option( 'enabled' );
        $this->title        = $this->get_option( 'title' );
        $this->tax_status   = $this->get_option( 'tax_status' );

        $this->init();
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init() {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Checking is gateway enabled or not
     *
     * @return boolean [description]
     */
    public function is_method_enabled() {
        return $this->enabled == 'yes';
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable Shipping', 'dokan' ),
                'default'       => 'yes'
            ),
            'title' => array(
                'title'         => __( 'Method Title', 'dokan' ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'       => __( 'Regular Shipping', 'dokan' ),
                'desc_tip'      => true,
            ),
            'tax_status' => array(
                'title'         => __( 'Tax Status', 'dokan' ),
                'type'          => 'select',
                'default'       => 'taxable',
                'options'       => array(
                    'taxable'   => __( 'Taxable', 'dokan' ),
                    'none'      => _x( 'None', 'Tax status', 'dokan' )
                ),
            ),

        );
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     *
     * @param mixed $package
     *
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
        $products = $package['contents'];
        $destination_country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';
        $destination_state = isset( $package['destination']['state'] ) ? $package['destination']['state'] : '';

        $amount = 0.0;

        if ( $products ) {
            $amount = $this->calculate_per_seller( $products, $destination_country, $destination_state );
        }

        $tax_rate = ( $this->tax_status == 'none' ) ? false : '';

        $rate = array(
            'id'    => $this->id,
            'label' => $this->title,
            'cost'  => $amount,
            'taxes' => $tax_rate
        );

        // Register the rate
        $this->add_rate( $rate );
    }

}