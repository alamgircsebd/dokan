<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package dokan
 */

if ( ! function_exists( 'dokan_content_nav' ) ) :

/**
 * Display navigation to next/previous pages when applicable
 */
function dokan_content_nav( $nav_id, $query = null ) {
    global $wp_query, $post;

    if ( $query ) {
        $wp_query = $query;
    }

    // Don't print empty markup on single pages if there's nowhere to navigate.
    if ( is_single() ) {
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next = get_adjacent_post( false, '', false );

        if ( !$next && !$previous )
            return;
    }

    // Don't print empty markup in archives if there's only one page.
    if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
        return;

    $nav_class = 'site-navigation paging-navigation';
    if ( is_single() )
        $nav_class = 'site-navigation post-navigation';
    ?>
    <nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
        <h1 class="assistive-text"><?php _e( 'Post navigation', 'dokan' ); ?></h1>

        <ul class="pager">
        <?php if ( is_single() ) : // navigation links for single posts  ?>

            <li class="previous">
                <?php previous_post_link( '%link', _x( '&larr;', 'Previous post link', 'dokan' ) . ' %title' ); ?>
            </li>
            <li class="next">
                <?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'dokan' ) ); ?>
            </li>

        <?php endif; ?>
        </ul>


        <?php if ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
            <?php dokan_page_navi( '', '', $wp_query ); ?>
        <?php endif; ?>

    </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
}

endif;

if ( ! function_exists( 'dokan_page_navi' ) ) :

function dokan_page_navi( $before = '', $after = '', $wp_query ) {

    $posts_per_page = intval( get_query_var( 'posts_per_page' ) );
    $paged = intval( get_query_var( 'paged' ) );
    $numposts = $wp_query->found_posts;
    $max_page = $wp_query->max_num_pages;
    if ( $numposts <= $posts_per_page ) {
        return;
    }
    if ( empty( $paged ) || $paged == 0 ) {
        $paged = 1;
    }
    $pages_to_show = 7;
    $pages_to_show_minus_1 = $pages_to_show - 1;
    $half_page_start = floor( $pages_to_show_minus_1 / 2 );
    $half_page_end = ceil( $pages_to_show_minus_1 / 2 );
    $start_page = $paged - $half_page_start;
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    $end_page = $paged + $half_page_end;
    if ( ($end_page - $start_page) != $pages_to_show_minus_1 ) {
        $end_page = $start_page + $pages_to_show_minus_1;
    }
    if ( $end_page > $max_page ) {
        $start_page = $max_page - $pages_to_show_minus_1;
        $end_page = $max_page;
    }
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }

    echo $before . '<div class="dokan-pagination-container"><ul class="dokan-pagination">' . "";
    if ( $paged > 1 ) {
        $first_page_text = "&laquo;";
        echo '<li class="prev"><a href="' . get_pagenum_link() . '" title="First">' . $first_page_text . '</a></li>';
    }

    $prevposts = get_previous_posts_link( '&larr; Previous' );
    if ( $prevposts ) {
        echo '<li>' . $prevposts . '</li>';
    } else {
        echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'dokan' ) . '</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ( $i == $paged ) {
            echo '<li class="active"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
        }
    }
    echo '<li class="">';
    next_posts_link( __('Next &rarr;', 'dokan') );
    echo '</li>';
    if ( $end_page < $max_page ) {
        $last_page_text = "&larr;";
        echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    }
    echo '</ul></div>' . $after . "";
}

endif;

function dokan_product_dashboard_errors() {
    $type = isset( $_GET['message'] ) ? $_GET['message'] : '';

    switch ($type) {
        case 'product_deleted':
            ?>
            <div class="dokan-alert dokan-alert-success">
                <?php echo __( 'Product has been deleted successfully!', 'dokan' ); ?>
            </div>
            <?php
            break;

        case 'error':
            ?>
            <div class="dokan-alert dokan-alert-danger">
                <?php echo __( 'Something went wrong!', 'dokan' ); ?>
            </div>
            <?php
            break;
    }
}

function dokan_product_listing_status_filter() {
    $permalink = dokan_get_navigation_url( 'products' );
    $status_class = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';
    $post_counts = dokan_count_posts( 'product', get_current_user_id() );
    ?>
    <ul class="dokan-listing-filter dokan-left subsubsub">
        <li<?php echo $status_class == 'all' ? ' class="active"' : ''; ?>>
            <a href="<?php echo $permalink; ?>"><?php printf( __( 'All (%d)', 'dokan' ), $post_counts->total ); ?></a>
        </li>
        <li<?php echo $status_class == 'publish' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'publish' ), $permalink ); ?>"><?php printf( __( 'Online (%d)', 'dokan' ), $post_counts->publish ); ?></a>
        </li>
        <li<?php echo $status_class == 'pending' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'pending' ), $permalink ); ?>"><?php printf( __( 'Pending Review (%d)', 'dokan' ), $post_counts->pending ); ?></a>
        </li>
        <li<?php echo $status_class == 'draft' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'post_status' => 'draft' ), $permalink ); ?>"><?php printf( __( 'Draft (%d)', 'dokan' ), $post_counts->draft ); ?></a>
        </li>
    </ul> <!-- .post-statuses-filter -->
    <?php
}

function dokan_order_listing_status_filter() {
    $orders_url = dokan_get_navigation_url( 'orders' );

    $status_class = isset( $_GET['order_status'] ) ? $_GET['order_status'] : 'all';
    $orders_counts = dokan_count_orders( get_current_user_id() );
    ?>

    <ul class="list-inline order-statuses-filter">
        <li<?php echo $status_class == 'all' ? ' class="active"' : ''; ?>>
            <a href="<?php echo $orders_url; ?>">
                <?php printf( __( 'All (%d)', 'dokan' ), $orders_counts->total ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-completed' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-completed' ), $orders_url ); ?>">
                <?php printf( __( 'Completed (%d)', 'dokan' ), $orders_counts->{'wc-completed'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-processing' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-processing' ), $orders_url ); ?>">
                <?php printf( __( 'Processing (%d)', 'dokan' ), $orders_counts->{'wc-processing'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-on-hold' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-on-hold' ), $orders_url ); ?>">
                <?php printf( __( 'On-hold (%d)', 'dokan' ), $orders_counts->{'wc-on-hold'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-pending' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-pending' ), $orders_url ); ?>">
                <?php printf( __( 'Pending (%d)', 'dokan' ), $orders_counts->{'wc-pending'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-canceled' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-cancelled' ), $orders_url ); ?>">
                <?php printf( __( 'Cancelled (%d)', 'dokan' ), $orders_counts->{'wc-cancelled'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-refunded' ? ' class="active"' : ''; ?>>
            <a href="<?php echo add_query_arg( array( 'order_status' => 'wc-refunded' ), $orders_url ); ?>">
                <?php printf( __( 'Refunded (%d)', 'dokan' ), $orders_counts->{'wc-refunded'} ); ?></span>
            </a>
        </li>
    </ul>
    <?php
}

function dokan_get_dashboard_nav() {
    $urls = array(
        'dashboard' => array(
            'title' => __( 'Dashboard', 'dokan'),
            'icon'  => '<i class="fa fa-tachometer"></i>',
            'url'   => dokan_get_navigation_url()
        ),
        'product' => array(
            'title' => __( 'Products', 'dokan'),
            'icon'  => '<i class="fa fa-briefcase"></i>',
            'url'   => dokan_get_navigation_url( 'products' )
        ),
        'order' => array(
            'title' => __( 'Orders', 'dokan'),
            'icon'  => '<i class="fa fa-shopping-cart"></i>',
            'url'   => dokan_get_navigation_url( 'orders' )
        ),
        'coupon' => array(
            'title' => __( 'Coupons', 'dokan'),
            'icon'  => '<i class="fa fa-gift"></i>',
            'url'   => dokan_get_navigation_url( 'coupons' )
        ),
        'report' => array(
            'title' => __( 'Reports', 'dokan'),
            'icon'  => '<i class="fa fa-line-chart"></i>',
            'url'   => dokan_get_navigation_url( 'reports' )
        ),
        'reviews' => array(
            'title' => __( 'Reviews', 'dokan'),
            'icon'  => '<i class="fa fa-comments-o"></i>',
            'url'   => dokan_get_navigation_url( 'reviews' )
        ),
        'withdraw' => array(
            'title' => __( 'Withdraw', 'dokan'),
            'icon'  => '<i class="fa fa-upload"></i>',
            'url'   => dokan_get_navigation_url( 'withdraw' )
        ),
        'settings' => array(
            'title' => __( 'Settings', 'dokan'),
            'icon'  => '<i class="fa fa-cog"></i>',
            'url'   => dokan_get_navigation_url( 'settings' )
        ),
    );

    return apply_filters( 'dokan_get_dashboard_nav', $urls );
}

function dokan_dashboard_nav( $active_menu ) {
    $urls = dokan_get_dashboard_nav();
    $menu = '<ul class="dokan-dashboard-menu">';

    foreach ($urls as $key => $item) {
        $class = ( $active_menu == $key ) ? ' class="active"' : '';
        $menu .= sprintf( '<li%s><a href="%s">%s %s</a></li>', $class, $item['url'], $item['icon'], $item['title'] );
    }
    $menu .= '</ul>';

    return $menu;
}


if ( ! function_exists( 'dokan_store_category_menu' ) ) :

/**
 * Store category menu for a store
 *
 * @param  int $seller_id
 * @return void
 */
function dokan_store_category_menu( $seller_id, $title = '' ) { ?>
    <aside class="widget dokan-category-menu">
        <h3 class="widget-title"><?php echo $title; ?></h3>
        <div id="cat-drop-stack">
            <?php
            global $wpdb;

            $categories = get_transient( 'dokan-store-category-'.$seller_id );

            if ( false === $categories ) {
                $sql = "SELECT t.term_id,t.name, tt.parent FROM $wpdb->terms as t
                        LEFT JOIN $wpdb->term_taxonomy as tt on t.term_id = tt.term_id
                        LEFT JOIN $wpdb->term_relationships AS tr on tt.term_taxonomy_id = tr.term_taxonomy_id
                        LEFT JOIN $wpdb->posts AS p on tr.object_id = p.ID
                        WHERE tt.taxonomy = 'product_cat'
                        AND p.post_type = 'product'
                        AND p.post_status = 'publish'
                        AND p.post_author = $seller_id GROUP BY t.term_id";

                $categories = $wpdb->get_results( $sql );
                set_transient( 'dokan-store-category-'.$seller_id , $categories );
            }

            $args = array(
                'taxonomy'      => 'product_cat',
                'selected_cats' => ''
            );

            $walker = new Dokan_Store_Category_Walker( $seller_id );
            echo "<ul>";
            echo call_user_func_array( array(&$walker, 'walk'), array($categories, 0, array()) );
            echo "</ul>";
            ?>
        </div>
    </aside>
<?php
}

endif;


function dokan_store_category_delete_transient( $post_id ) {

    $post_tmp = get_post($post_id);
    $seller_id = $post_tmp->post_author;
    //delete store category transient
    delete_transient( 'dokan-store-category-'.$seller_id );
}

add_action( 'deleted_post', 'dokan_store_category_delete_transient' );
add_action( 'save_post', 'dokan_store_category_delete_transient' );



function dokan_seller_reg_form_fields() {
    $role = isset( $_POST['role'] ) ? $_POST['role'] : 'customer';
    $role_style = ( $role == 'customer' ) ? ' style="display:none"' : '';
    ?>
    <div class="show_if_seller"<?php echo $role_style; ?>>

        <div class="split-row form-row-wide">
            <p class="form-row form-group">
                <label for="first-name"><?php _e( 'First Name', 'dokan' ); ?> <span class="required">*</span></label>
                <input type="text" class="input-text form-control" name="fname" id="first-name" value="<?php if ( ! empty( $_POST['fname'] ) ) echo esc_attr($_POST['fname']); ?>" required="required" />
            </p>

            <p class="form-row form-group">
                <label for="last-name"><?php _e( 'Last Name', 'dokan' ); ?> <span class="required">*</span></label>
                <input type="text" class="input-text form-control" name="lname" id="last-name" value="<?php if ( ! empty( $_POST['lname'] ) ) echo esc_attr($_POST['lname']); ?>" required="required" />
            </p>
        </div>

        <p class="form-row form-group form-row-wide">
            <label for="company-name"><?php _e( 'Shop Name', 'dokan' ); ?> <span class="required">*</span></label>
            <input type="text" class="input-text form-control" name="shopname" id="company-name" value="<?php if ( ! empty( $_POST['shopname'] ) ) echo esc_attr($_POST['shopname']); ?>" required="required" />
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="seller-url" class="pull-left"><?php _e( 'Shop URL', 'dokan' ); ?> <span class="required">*</span></label>
            <strong id="url-alart-mgs" class="pull-right"></strong>
            <input type="text" class="input-text form-control" name="shopurl" id="seller-url" value="<?php if ( ! empty( $_POST['shopurl'] ) ) echo esc_attr($_POST['shopurl']); ?>" required="required" />
            <small><?php echo home_url(); ?>/store/<strong id="url-alart"></strong></small>
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="seller-address"><?php _e( 'Address', 'dokan' ); ?><span class="required">*</span></label>
            <textarea type="text" id="seller-address" name="address" class="form-control input" required="required"><?php if ( ! empty( $_POST['address'] ) ) echo esc_textarea($_POST['address']); ?></textarea>
        </p>

        <p class="form-row form-group form-row-wide">
            <label for="shop-phone"><?php _e( 'Phone', 'dokan' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text form-control" name="phone" id="shop-phone" value="<?php if ( ! empty( $_POST['phone'] ) ) echo esc_attr($_POST['phone']); ?>" required="required" />
        </p>
        
        <?php  do_action( 'dokan_seller_registration_field_after' ); ?>

    </div>
    <div class="dokan-new-field-wrap" style="display: none;">
        <?php do_action( 'dokn_aditional_relation_filed' ); ?>
    </div>

    <p class="form-row form-group user-role">
        <label class="radio">
            <input type="radio" name="role" value="customer"<?php checked( $role, 'customer' ); ?>>
            <?php _e( 'I am a customer', 'dokan' ); ?>
        </label>

        <label class="radio">
            <input type="radio" name="role" value="seller"<?php checked( $role, 'seller' ); ?>>
            <?php _e( 'I am a seller', 'dokan' ); ?>
        </label>
        <?php do_action( 'dokan_registration_form_role', $role ); ?>
    </p>

    <?php
}

add_action( 'register_form', 'dokan_seller_reg_form_fields' );

function dokan_seller_not_enabled_notice() {
    ?>
        <div class="dokan-alert dokan-alert-warning">
            <strong><?php _e( 'Error!', 'dokan' ); ?></strong>
            <?php _e( 'Your account is not enabled for selling, please contact the admin', 'dokan' ); ?>
        </div>
    <?php
}

if ( !function_exists( 'dokan_header_user_menu' ) ) :

/**
 * User top navigation menu
 *
 * @return void
 */
function dokan_header_user_menu() {
    ?>
    <ul class="nav navbar-nav navbar-right">
        <li>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php printf( __( 'Cart %s', 'dokan' ), '<span class="dokan-cart-amount-top">(' . WC()->cart->get_cart_total() . ')</span>' ); ?> <b class="caret"></b></a>

            <ul class="dropdown-menu">
                <li>
                    <div class="widget_shopping_cart_content"></div>
                </li>
            </ul>
        </li>

        <?php if ( is_user_logged_in() ) { ?>

            <?php
            global $current_user;

            $user_id = $current_user->ID;
            if ( dokan_is_user_seller( $user_id ) ) {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php _e( 'Seller Dashboard', 'dokan' ); ?> <b class="caret"></b></a>

                    <ul class="dropdown-menu">
                        <li><a href="<?php echo dokan_get_store_url( $user_id ); ?>" target="_blank"><?php _e( 'Visit your store', 'dokan' ); ?> <i class="fa fa-external-link"></i></a></li>
                        <li class="divider"></li>
                        <?php
                        $nav_urls = dokan_get_dashboard_nav();

                        foreach ($nav_urls as $key => $item) {
                            printf( '<li><a href="%s">%s &nbsp;%s</a></li>', $item['url'], $item['icon'], $item['title'] );
                        }
                        ?>
                    </ul>
                </li>
            <?php } ?>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo esc_html( $current_user->display_name ); ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo dokan_get_page_url( 'my_orders' ); ?>"><?php _e( 'My Orders', 'dokan' ); ?></a></li>
                    <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'My Account', 'dokan' ); ?></a></li>
                    <li><a href="<?php echo wc_customer_edit_account_url(); ?>"><?php _e( 'Edit Account', 'dokan' ); ?></a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'billing', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Billing Address', 'dokan' ); ?></a></li>
                    <li><a href="<?php echo wc_get_endpoint_url( 'edit-address', 'shipping', get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Shipping Address', 'dokan' ); ?></a></li>
                </ul>
            </li>

            <li><?php wp_loginout( home_url() ); ?></li>

        <?php } else { ?>
            <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'Log in', 'dokan' ); ?></a></li>
            <li><a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>"><?php _e( 'Sign Up', 'dokan' ); ?></a></li>
        <?php } ?>
    </ul>
    <?php
}

endif;

function dokan_account_migration_button() {
    $user = wp_get_current_user();

    if ( dokan_is_user_customer( $user->ID ) ) {
        ?>
        <p>&nbsp;</p>
        <p>
            <a href="<?php echo dokan_get_page_url( 'myaccount', 'woocommerce' ); ?>account-migration/seller/" class="button button-primary"><?php _e( 'Become a Seller', 'dokan' ); ?></a>
        </p>
        <?php
    }
}

add_action( 'woocommerce_after_my_account', 'dokan_account_migration_button' );