<?php
namespace DokanPro\Modules\Stripe;

use DokanPro\Modules\Stripe\Transaction;

/**
 * DokanStripe wrapper class
 *
 * @since DOKAN_PRO_SINCE
 */
class DokanStripe {
    public static function transaction() {
        return new Transaction;
    }
}