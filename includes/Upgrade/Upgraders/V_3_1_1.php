<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_1_1_UpdateSubscriptionEnddate;

class V_3_1_1 extends DokanProUpgrader {

    /**
     * Update the missing shipping zone locations table data
     *
     * @since 3.0.7
     *
     * @return void
     */
    public static function update_subscription_product_pack_enddate() {
        $processor = new V_3_1_1_UpdateSubscriptionEnddate();

        // get all seller and add them to queue for further processing.
        $users = get_users(
            [
                'role__in'   => [ 'seller', 'administrator' ],
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            $processor->push_to_queue(
                [
                    'type' => 'vendor',
                    'id'   => $user->ID,
                ]
            );
        }

        $processor->dispatch_process();
    }
}
