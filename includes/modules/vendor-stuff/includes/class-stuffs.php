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
            $first_name = '';
            $last_name = '';
            $email = '';
            $phone = '';
            $button_name = __( 'Create Stuff', 'dokan' );
        } else {
            $user = get_user_by( 'id', $_GET['stuff_id'] );
            $first_name = $user->first_name;
            $last_name = $user->last_name;
            $email = $user->user_email;
            $phone = get_user_meta( $user->ID, '_stuff_phone', true );
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
            if ( isset( $_POST['password'] ) && isset( $_POST['confirm_password'] ) ) {
                if ( $_POST['password'] !== $_POST['confirm_password'] ) {
                    self::$errors[] = new WP_Error( 'no-password-match', __( 'Password not matched', 'dokan' ) );
                }
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
                'ID'           => $is_edit,
                'user_email'   => $_POST['email'],
                'first_name'   => $_POST['first_name'],
                'last_name'    => $_POST['last_name'],
                'role'         => 'vendor_stuff',
                'display_name' => $_POST['first_name'] . ' ' . $_POST['last_name']
            );

            if ( ! empty( $user_password ) ) {
                $userdata['user_pass'] = $user_password;
            }
        }

        $user = wp_insert_user( $userdata );


        if ( is_wp_error( $user ) ) {
            self::$errors[] = $user;
            return;
        }

        update_user_meta( $user, '_vendor_id', sanitize_text_field( $_POST['vendor_id'] ) );
        update_user_meta( $user, '_stuff_phone', sanitize_text_field( $_POST['phone'] ) );
        wp_redirect( dokan_get_navigation_url( 'stuffs' ) );
        exit();
    }

}