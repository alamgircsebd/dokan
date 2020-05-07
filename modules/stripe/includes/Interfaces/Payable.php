<?php

namespace WeDevs\DokanPro\Modules\Stripe\Interfaces;

interface Payable {

    /**
     * Make the payment
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function pay();
}