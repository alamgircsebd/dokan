<?php

namespace WeDevs\DokanPro\Modules\Stripe\Interfaces;

interface WebhookHandleable {

    /**
     * Handle the event
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function handle();
}