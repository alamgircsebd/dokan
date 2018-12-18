<?php

class Dokan_Follow_Store_Follow_Button {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_seller_listing_footer_content', array( $this, 'add_follow_button' ) );
        add_action( 'dokan_after_store_tabs', array( $this, 'add_follow_button_after_store_tabs' ), 99 );
    }

    /**
     * Add follow store button
     *
     * @since 1.0.0
     *
     * @param WP_User $vendor
     * @param array   $button_classes
     *
     * @return void
     */
    public function add_follow_button( $vendor, $button_classes = array() ) {
        if ( ! get_current_user_id() ) {
            return;
        }

        $btn_labels = dokan_follow_store_button_labels();

        $customer_id = get_current_user_id();

        $status = null;

        if ( dokan_follow_store_is_following_store( $vendor->ID, $customer_id ) ) {
            $label_current = $btn_labels['following'];
            $status = 'following';
        } else {
            $label_current = $btn_labels['follow'];
        }

        $button_classes = array_merge(
            array( 'dokan-btn', 'dokan-btn-theme', 'dokan-follow-store-button' ),
            $button_classes
        );

        $args = array(
            'label_current'  => $label_current,
            'label_unfollow' => $btn_labels['unfollow'],
            'vendor_id'      => $vendor->ID,
            'status'         => $status,
            'button_classes' => implode( ' ', $button_classes ),
        );

        dokan_follow_store_get_template( 'follow-button', $args );
    }

    /**
     * Add follow button in single store tabs
     *
     * @since 1.0.0
     *
     * @param void $vendor_id
     *
     * @return void
     */
    public function add_follow_button_after_store_tabs( $vendor_id ) {
        if ( ! get_current_user_id() ) {
            return;
        }

        $vendor = dokan()->vendor->get( $vendor_id );

        ob_start();
        $this->add_follow_button( $vendor->data, array( 'dokan-btn-sm' ) );
        $button = ob_get_clean();

        $args = array(
            'button' => $button,
        );

        dokan_follow_store_get_template( 'follow-button-after-store-tabs', $args );
    }
}
