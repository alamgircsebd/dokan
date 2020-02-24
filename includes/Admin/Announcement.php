<?php

namespace WeDevs\DokanPro\Admin;

use WeDevs\DokanPro\Admin\AnnouncementBackgroundProcess;

/**
 *  Dokan Announcement class for Admin
 *
 *  Announcement for seller
 *
 *  @since 2.1
 *
 *  @author weDevs <info@wedevs.com>
 */
class Announcement {

    /**
     * Hold the announment backgroud class
     *
     * @var Object
     */
    protected $processor;

    /**
     *  Load automatically all actions
     */
    public function __construct() {
        $this->processor = new AnnouncementBackgroundProcess();
    }

    /**
     * Trigger mail
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function trigger_mail( $post_id ) {
        $data = get_post( $post_id );

        if ( ! $data ) {
            return;
        }

        if ( 'publish' !== $data->post_status ) {
            return;
        }

        $sender_type = get_post_meta( $post_id, '_announcement_type', true );
        $vendor_ids  = [];

        if ( 'all_seller' === $sender_type ) {
            $users   = new \WP_User_Query( array( 'role' => 'seller' ) );
            $vendors = $users->get_results();

            if ( $vendors ) {
                foreach ( $vendors as $vendor ) {
                    array_push( $vendor_ids, $vendor->ID );
                }
            }
        } else {
            $vendor_ids = get_post_meta( $post_id, '_announcement_selected_user', true );
        }

        $payload = [];

        foreach ( $vendor_ids as $vendor_id ) {
            $payload = array(
                'post_id'   => $post_id,
                'sender_id' => $vendor_id
            );

            $this->processor->push_to_queue( $payload );
        }

        $this->processor->save()->dispatch();
    }

    /**
     * Proce seller announcement data
     *
     * @since  2.1
     *
     * @param  array $announcement_seller
     * @param  integer $post_id
     *
     * @return void
     */
    public function process_seller_announcement_data( $announcement_seller, $post_id ) {

        $inserted_seller_id = $this->get_assign_seller( $post_id );

        if ( !empty( $inserted_seller_id ) ) {
            foreach ( $inserted_seller_id as $key => $value) {
                $db[] = $value['user_id'];
            }
        } else {
            $db = array();
        }

        $sellers         = $announcement_seller;
        $existing_seller = $new_seller = $del_seller = array();

        foreach( $sellers as $seller ) {
            if ( in_array( $seller, $db ) ) {
                $existing_seller[] = $seller;
            } else {
                $new_seller[] = $seller;
            }
        }

        $del_seller = array_diff( $db, $existing_seller );

        if ( $del_seller ) {
            $this->delete_assign_seller( $del_seller, $post_id );
        }

        if ( $new_seller ) {
            $this->insert_assign_seller( $new_seller, $post_id );
        }
    }

    /**
     * Get assign seller
     *
     * @since  2.1
     *
     * @param  integer $post_id
     *
     * @return array
     */
    public function get_assign_seller( $post_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix.'dokan_announcement';

        $sql = "SELECT `user_id` FROM {$table_name} WHERE `post_id`= $post_id";

        $results = $wpdb->get_results( $sql, ARRAY_A );

        if ( $results ) {
            return $results;
        } else {
            return array();
        }
    }

    /**
     * Insert assing seller
     *
     * @since 2.1
     *
     * @param  array $seller_array
     * @param  integer $post_id
     *
     * @return void
     */
    public function insert_assign_seller( $seller_array, $post_id ) {
        global $wpdb;

        $values     = '';
        $table_name = $wpdb->prefix.'dokan_announcement';
        $i          = 0;

        foreach ( $seller_array as $key => $seller_id ) {
            $sep    = ( $i==0 ) ? '':',';
            $values .= sprintf( "%s ( %d, %d, '%s')", $sep, $seller_id, $post_id, 'unread' );

            $i++;
        }

        $sql = "INSERT INTO {$table_name} (`user_id`, `post_id`, `status` ) VALUES $values";
        $wpdb->query( $sql );
    }

    /**
     * Delete assign seller
     *
     * @since  2.1
     *
     * @param  array $seller_array
     * @param  integer $post_id
     *
     * @return void
     */
    public function delete_assign_seller( $seller_array, $post_id ) {
        if ( ! is_array( $seller_array ) ) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix.'dokan_announcement';
        $values     = '';
        $i          = 0;

        foreach ( $seller_array as $key => $seller_id ) {
            $sep    = ( $i == 0 ) ? '' : ',';
            $values .= sprintf( "%s( %d, %d )", $sep, $seller_id, $post_id );

            $i++;
        }

        // $sellers = implode( ',', $seller_array );
        $sql = "DELETE FROM {$table_name} WHERE (`user_id`, `post_id` ) IN ($values)";

        if ( $values ) {
            $wpdb->query( $sql );
        }
    }
}
