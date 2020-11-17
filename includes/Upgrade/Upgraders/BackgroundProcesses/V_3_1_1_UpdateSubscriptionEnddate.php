<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;
use DokanPro\Modules\Subscription\Helper;

class V_3_1_1_UpdateSubscriptionEnddate extends DokanBackgroundProcesses {

    /**
     * Sync the missing shipping locations data
     *
     * @since 3.1.1
     *
     * @param int $page
     *
     * @return int|bool
     */
    public function task( $subscription_data ) {
        if ( ! isset( $subscription_data['id'] ) ) {
            return false;
        }

        $user_id = absint( wp_unslash( $subscription_data['id'] ) );

        $vendor = dokan()->vendor->get( $user_id );

        // this is just to get code editor autocomplete/quick access support
        if ( ! $vendor instanceof \WeDevs\Dokan\Vendor\Vendor ) {
            return false;
        }

        // check this user is vendor, if not: do not process this user
        if ( ! $vendor->is_vendor() ) {
            return false;
        }

        // get vendor subscription.
        $vendor_subscription = dokan()->vendor->get( $user_id )->subscription;

        // if now subscription found for vendor skip this user, also this check will enable editor autocomplete/quick access support.
        if ( ! $vendor_subscription instanceof \DokanPro\Modules\Subscription\SubscriptionPack ) {
            return false;
        }

        // if user don't have subscription pack, do not process this user
        $has_subscription = $vendor_subscription->has_subscription();

        if ( ! $has_subscription ) {
            return false;
        }

        // check product type = product_pack, if not: do not process this user
        if ( ! Helper::is_subscription_product( $vendor_subscription->get_id() ) ) {
            return false;
        }

        update_user_meta( $user_id, 'product_pack_enddate', $vendor_subscription->get_product_pack_end_date() );

        return false;
    }
}
