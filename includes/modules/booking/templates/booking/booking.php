<div class="dokan-dashboard-wrap">

<?php
    $current_page = get_query_var( 'booking' );
    /**
     *  dokan_dashboard_content_before hook
     *  dokan_dashboard_support_content_before
     *
     *  @hooked get_dashboard_side_navigation
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_dashboard_support_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-booking-wrapper dokan-product-edit">
        <?php

        $booking_url = dokan_get_navigation_url( 'booking' );
        $menus       = apply_filters( 'dokan_booking_menu', '' );
        $title       = apply_filters( 'dokan_booking_menu_title', $current_page );

        $template_args = array(
            'is_booking'  => true,
            'title'       => $title,
            'booking_url' => $booking_url
        );
        ?>

        <ul class="dokan_tabs">
            <?php
            foreach ( $menus as $key => $value) {
                $class = ( $current_page == $key ) ? ' class="active"' : '';
                if( $value[ 'tabs' ] !== false)
                printf( '<li%s><a href="%s">%s</a></li>', $class, $booking_url.$key, $value[ 'title' ] );
            }
            ?>
        </ul>
        <?php
            switch ( $current_page ) {
            case 'my-bookings':
                dokan_get_template_part( 'booking/my-bookings/all-bookings', '', $template_args );
                break;

            case 'booking-details':
                dokan_get_template_part( 'booking/my-bookings/booking-details', '', $template_args );
                break;

            case 'new-product':
                dokan_get_template_part( 'booking/new-product', '', $template_args );
                break;

            case 'edit':
                dokan_get_template_part( 'booking/new-product', '', $template_args );
                break;

            case 'calendar' :
                dokan_get_template_part( 'booking/calendar/calendar', '', $template_args );
                break;

            case 'resources' :
                dokan_get_template_part( 'booking/resources/resources', '', $template_args );
                break;

            case 'resources/edit' :
                dokan_get_template_part( 'booking/resources/edit-resource', '', $template_args );
                break;

            case '':
                dokan_get_template_part( 'booking/product-list', '', $template_args );
                break;

            default:
                do_action( 'dokan_booking_load_menu_template', $current_page, $template_args );
                break;
        }
        ?>
    </div><!-- .dokan-dashboard-content -->

    <?php

    /**
     *  dokan_dashboard_content_after hook
     *  dokan_dashboard_support_content_after hook
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_dashboard_support_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->