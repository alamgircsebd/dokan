<?php

class Dokan_Seller_Vacation_Update_Seller_Product_Status extends Abstract_Dokan_Background_Processes {

    const PRODUCT_LIMIT = 30;

    /**
     * Process action id
     *
     * @since 2.9.10
     *
     * @var string
     */
    protected $action = 'Dokan_Seller_Vacation_Update_Seller_Product_Status';

    /**
     * Following vendors
     *
     * @since 2.9.10
     *
     * @var array
     */
    private $vendors = array();

    /**
     * Perform task
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return array
     */
    public function task( $args ) {
        if ( empty( $args['vendors'] ) && empty( $args['vendor'] ) ) {
            return false;
        }

        $vendor      = $args['vendor'];
        $on_vacation = dokan_seller_vacation_is_seller_on_vacation( $vendor->get_id() );

        if ( $on_vacation ) {
            return $this->set_products_status_as_vacation( $args );
        } else {
            return $this->set_products_status_as_publish( $args );
        }
    }

    /**
     * Switch status from vacation to publish
     *
     * @since 2.9.10
     *
     * @param array $args
     */
    private function set_products_status_as_publish( $args ) {
        $args['current_status'] = 'vacation';
        $args['new_status']     = 'publish';
        return $this->update_products( $args );
    }

    /**
     * Switch status from publish to vacation
     *
     * @since 2.9.10
     *
     * @param array $args
     */
    private function set_products_status_as_vacation( $args ) {
        $args['current_status'] = 'publish';
        $args['new_status']     = 'vacation';
        return $this->update_products( $args );
    }

    /**
     * Update products
     *
     * @since 2.9.10
     *
     * @param array $args
     *
     * @return array
     */
    private function update_products( $args ) {
        $vendor = $args['vendor'];

        $products = wc_get_products( array(
            'author' => $vendor->get_id(),
            'status' => $args['current_status'],
            'limit'  => self::PRODUCT_LIMIT,
        ) );

        $count = count( $products );

        if ( empty( $products ) ) {
            $args['vendor'] = array_pop( $args['vendors'] );
            return $args;
        }

        foreach ( $products as $product ) {
            $product->set_status( $args['new_status'] );
            $product->save();
        }

        return $args;
    }
}
