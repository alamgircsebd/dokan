<?php

namespace WeDevs\DokanPro\Modules\SellerVacation;

class Module {

    /**
     * Constructor for the Dokan_Seller_Vacation class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 2.9.10
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->instances();

        add_action( 'init', array( $this, 'custom_post_status_vacation' ) );

        add_filter( 'dokan_product_listing_query', array( $this, 'modified_product_listing_query' ) );
        add_filter( 'dokan_get_post_status', array( $this, 'show_vacation_status_listing' ), 12 );
        add_filter( 'dokan_get_post_status_label_class', array( $this, 'show_vacation_status_listing_label' ), 12 );

        add_action( 'dokan_product_listing_status_filter', array( $this, 'add_vacation_product_listing_filter'), 10, 2 );
        add_action( 'dokan_store_profile_frame_after', array( $this, 'show_vacation_message' ), 10, 2 );
        add_action( 'template_redirect', array( $this, 'remove_product_from_cart_for_closed_store' ) );
    }

    /**
     * Module constants
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_SELLER_VACATION_FILE' , __FILE__ );
        define( 'DOKAN_SELLER_VACATION_PATH' , dirname( DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_INCLUDES' , DOKAN_SELLER_VACATION_PATH . '/includes' );
        define( 'DOKAN_SELLER_VACATION_URL' , plugins_url( '', DOKAN_SELLER_VACATION_FILE ) );
        define( 'DOKAN_SELLER_VACATION_ASSETS' , DOKAN_SELLER_VACATION_URL . '/assets' );
        define( 'DOKAN_SELLER_VACATION_VIEWS', DOKAN_SELLER_VACATION_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/functions.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-install.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-store-settings.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-ajax.php';
        require_once DOKAN_SELLER_VACATION_INCLUDES . '/class-dokan-seller-vacation-cron.php';
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.10
     *
     * @return void
     */
    private function instances() {
        new \Dokan_Seller_Vacation_Install();
        new \Dokan_Seller_Vacation_Store_Settings();
        new \Dokan_Seller_Vacation_Ajax();
        new \Dokan_Seller_Vacation_Cron();
    }

    /**
     * Register custom post status "vacation"
     * @return void
     */
    public function custom_post_status_vacation() {
        register_post_status( 'vacation', array(
            'label'                     => _x( 'Vacation', 'dokan' ),
            'public'                    => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Vacation <span class="count">(%s)</span>', 'Vacation <span class="count">(%s)</span>' )
        ) );
    }

    /**
     * Show Vacation message in store page
     * @param  array $store_user
     * @param  array $store_info
     * @return void
     */
    public function show_vacation_message( $store_user, $store_info, $raw_output = false ) {
        $vendor = dokan()->vendor->get( $store_user->ID );

        if ( dokan_seller_vacation_is_seller_on_vacation( $vendor->get_id() ) ) {
            $shop_info = $vendor->get_shop_info();

            $message = '';

            if ( 'datewise' !== $shop_info['settings_closing_style'] ) {
                $message = $store_info['setting_vacation_message'];
            } else {
                $schedules    = dokan_seller_vacation_get_vacation_schedules( $shop_info );
                $current_time = date( 'Y-m-d', current_time( 'timestamp' ) );

                foreach ( $schedules as $schedule ) {
                    $from = $schedule['from'];
                    $to   = $schedule['to'];

                    if ( $from <= $current_time && $current_time <= $to ) {
                        $message = $schedule['message'];
                        break;
                    }
                }
            }

            if ( $raw_output ) {
                echo esc_html( $message );
            } else {
                dokan_seller_vacation_get_template( 'vacation-message', array(
                    'message' => $message,
                ) );
            }
        }
    }

    /**
     * Add vacation link in product listing filter
     * @param string $status_class
     * @param object $post_counts
     */
    public function add_vacation_product_listing_filter( $status_class, $post_counts ) {
        ?>
        <li<?php echo $status_class == 'vacation' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'vacation' ), get_permalink() ); ?>"><?php printf( __( 'Vacation (%d)', 'dokan' ), $post_counts->vacation ); ?></a>
        </li>
        <?php
    }

    /**
     * Show Vacation status with product in product listing
     * @param  string $value
     * @param  string $status
     * @return string
     */
    public function show_vacation_status_listing( $status ) {
        $status['vacation'] = __( 'In vacation', 'dokan' );
        return $status;
    }

    /**
    * Get vacation status label
    *
    * @since 1.2
    *
    * @return void
    **/
    public function show_vacation_status_listing_label( $labels ) {
        $labels['vacation'] = 'dokan-label-info';
        return $labels;
    }

    /**
     * Modified Porduct query
     * @param  array $args
     * @return array
     */
    public function modified_product_listing_query( $args ) {

        if ( isset( $_GET['post_status'] ) && $_GET['post_status'] == 'vacation' ) {
            $args['post_status'] = $_GET['post_status'];
            return $args;
        }

        if ( is_array( $args['post_status'] ) ) {
            $args['post_status'][] = 'vacation';
            return $args;
        }
        return $args;
    }

    /**
     * Remove product from cart for closed store
     * @param  null
     * @return void 
     */    
    public function remove_product_from_cart_for_closed_store() {
        if ( is_cart() || is_checkout() ) {

            foreach( WC()->cart->cart_contents as $item ) {
                $product_id = ( isset( $item['variation_id'] ) && $item['variation_id'] != 0 ) ? $item['variation_id'] : $item['product_id'];

                if ( empty( $product_id ) ) {
                    continue;
                }

                $vendor_id  = get_post_field( 'post_author', $product_id );

                if ( empty( $vendor_id ) ) {
                    continue;
                }

                if ( dokan_seller_vacation_is_seller_on_vacation( $vendor_id ) ) {
                    $product_cart_id = isset( $item['key'] ) ? $item['key'] : WC()->cart->generate_cart_id( $product_id );
                    WC()->cart->remove_cart_item( $product_cart_id );
                }
            }

        }
    }
}
