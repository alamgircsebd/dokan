<?php

/**
* Vendor stuff class
*/
class Dokan_Stuffs {

    private static $errors;

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'dokan_add_stuff_content', array( $this, 'display_errors' ), 10 );
        add_action( 'dokan_add_stuff_content', array( $this, 'add_stuff_content' ), 15 );
        add_action( 'template_redirect', array( $this, 'handle_stuff' ), 10 );
        add_action( 'init', array( $this, 'delete_stuff' ), 99 );
    }

    /**
     * Display all errors
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function display_errors() {
        if ( ! empty( self::$errors ) ) {
            foreach ( self::$errors as $key => $error ) {
                if ( is_wp_error( $error ) ) {
                    dokan_get_template_part('global/dokan-error', '', array( 'deleted' => true, 'message' => $error->get_error_message() ) );
                }
            }
        }
    }

    /**
     * Add stuff content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_stuff_content() {
        $is_edit = ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && ! empty( $_GET['stuff_id'] ) ) ? $_GET['stuff_id'] : 0;

        if ( ! $is_edit ) {
            $first_name  = '';
            $last_name   = '';
            $email       = '';
            $phone       = '';
            $button_name = __( 'Create Stuff', 'dokan' );
        } else {
            $user        = get_user_by( 'id', $_GET['stuff_id'] );
            $first_name  = $user->first_name;
            $last_name   = $user->last_name;
            $email       = $user->user_email;
            $phone       = get_user_meta( $user->ID, '_stuff_phone', true );
            $button_name = __( 'Update Stuff', 'dokan' );
        }

        include DOKAN_VENDOR_STUFF_DIR . '/templates/form.php';
    }

    /**
     * Hande form submission
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_stuff() {
        if ( ! isset( $_POST['stuff_creation'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['vendor_stuff_nonce_field'], 'vendor_stuff_nonce' ) ) {
            return;
        }

        $is_edit = ! empty( $_POST['stuff_id'] ) ? $_POST['stuff_id'] : false;
        $user_password = '';

        if ( empty( $_POST['first_name'] ) ) {
            self::$errors[] = new WP_Error( 'no-first-name', __( 'First Name must be required', 'dokan' ) );
        }

        if ( empty( $_POST['last_name'] ) ) {
            self::$errors[] = new WP_Error( 'no-last-name', __( 'Last Name must be required', 'dokan' ) );
        }

        if ( empty( $_POST['email'] ) ) {
            self::$errors[] = new WP_Error( 'no-email', __( 'Email must be required', 'dokan' ) );
        }

        if ( empty( $_POST['vendor_id'] ) ) {
            self::$errors[] = new WP_Error( 'no-vendor', __( 'No vendor found for assigning this stuff', 'dokan' ) );
        }

        if ( ! empty( $_POST['stuff_id'] ) ) {
            if ( ! empty( $_POST['password'] ) ) {
                $user_password = $_POST['password'];
            }
        }

        if ( ! $is_edit ) {
            $userdata = array(
                'user_email'   => $_POST['email'],
                'user_pass'    => wp_generate_password(),
                'user_login'   => $_POST['email'],
                'first_name'   => $_POST['first_name'],
                'last_name'    => $_POST['last_name'],
                'role'         => 'vendor_stuff',
                'display_name' => $_POST['first_name'] . ' ' . $_POST['last_name']
            );
        } else {
            $userdata = array(
                'ID'           => (int)$is_edit,
                'user_email'   => $_POST['email'],
                'user_login'   => $_POST['email'],
                'first_name'   => $_POST['first_name'],
                'last_name'    => $_POST['last_name'],
                'role'         => 'vendor_stuff',
                'display_name' => $_POST['first_name'] . ' ' . $_POST['last_name']
            );

            if ( ! empty( $user_password ) ) {
                $userdata['user_pass'] = wp_hash_password( $user_password );
            }
        }

        remove_filter( 'pre_user_display_name', 'dokan_seller_displayname' );
        $user = wp_insert_user( $userdata );
        add_filter( 'pre_user_display_name', 'dokan_seller_displayname' );

        if ( is_wp_error( $user ) ) {
            self::$errors[] = $user;
            return;
        }

        if ( ! $is_edit ) {
            wp_send_new_user_notifications( $user, 'user' );
        }

        update_user_meta( $user, '_vendor_id', sanitize_text_field( $_POST['vendor_id'] ) );
        update_user_meta( $user, '_stuff_phone', sanitize_text_field( $_POST['phone'] ) );
        wp_redirect( dokan_get_navigation_url( 'stuffs' ) );
        exit();
    }

    /**
     * Delete stuff
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete_stuff() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete_stuff' ) {
            if ( wp_verify_nonce( $_GET['_stuff_delete_nonce'], 'stuff_delete_nonce' ) ) {

                $user_id   = ! empty( $_GET['stuff_id'] ) ? $_GET['stuff_id'] : 0;
                $vendor_id = get_user_meta( $user_id, '_vendor_id', true );

                if ( $vendor_id == get_current_user_id() ) {
                    if ( $user_id ) {
                        require_once ABSPATH . 'wp-admin/includes/user.php';
                        wp_delete_user( $user_id );
                    }
                }
            }
        }
    }

}