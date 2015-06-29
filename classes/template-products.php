<?php

/**
*  Product Functionality for Product Handler
*
*  @since 2.4
*
*  @package dokan
*/
class Dokan_Template_Products {

    /**
     *  Load autometially when class initiate
     *
     *  @since 2.4
     *
     *  @uses actions
     *  @uses filters
     */
    function __construct() {

    }

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Template_Products();
        }

        return $instance;
    }



}