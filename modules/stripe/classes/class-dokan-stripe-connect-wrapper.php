<?php
namespace DokanPro\Modules\Stripe;

use DokanPro\Modules\Stripe\Transaction;

/**
 * DokanStripe wrapper class
 *
 * @since 2.9.13
 */
class DokanStripe {
    public static function transaction() {
        return new Transaction;
    }
}
