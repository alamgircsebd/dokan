<?php
namespace DokanPro\Modules\Stripe;

use DokanPro\Modules\Stripe\Helper;

/**
 * The transaction class
 *
 * @since 2.9.13
 */
class Transaction {
    /**
     * Charge id holder
     *
     * @var string
     */
    protected $admin;

    /**
     * Amount holder
     *
     * @var float
     */
    protected $amount;

    /**
     * Connected vendor id holder
     *
     * @var string
     */
    protected $vendor;

    /**
     * Currecny holder
     *
     * @var string
     */
    protected $currency;

    /**
     * Amount to transfer
     *
     * @since  2.9.13
     *
     * @param  float $amount
     * @param  string $currency
     *
     * @return this
     */
    public function amount( $amount, $currency = 'usd' ) {
        $this->amount   = $amount;
        $this->currency = $currency;

        return $this;
    }

    /**
     * The transfer will be made from which account
     *
     * @since 2.9.13
     *
     * @param string $admin
     *
     * @return this
     */
    public function from( $admin ) {
        $this->admin = $admin;

        return $this;
    }

    /**
     * The transfer will be made to which account
     *
     * @since 2.9.13
     *
     * @param string $vendor
     *
     * @return this
     */
    public function to( $vendor ) {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Make the transer
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public function create() {
        return $this->transfer();
    }

    /**
     * Transer the fund
     *
     * @return boolean
     */
    public function transfer() {
        $transfer = \Stripe\Transfer::create( [
            'amount'             => $this->amount,
            'currency'           => $this->currency,
            'destination'        => $this->vendor,
            'source_transaction' => $this->admin
        ] );

        return is_wp_error( $transfer ) ? false : true;
    }
}
