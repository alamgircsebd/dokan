<div id="bookings_resources" class="bookings_resources_tab dokan-edit-row dokan-clearfix">
    <div class="dokan-section-heading" data-togglehandler="bookings_resources">
        <h2><i class="fa fa-plus-square" aria-hidden="true"></i> <?php _e( 'Additional Resources', 'dokan-wc-booking' ) ?></h2>
        <p><?php _e( 'Set Available Resources', 'dokan-wc-booking' ) ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fa fa-sort-desc fa-flip-vertical" aria-hidden="true" style="margin-top: 9px;"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>
    <div class="dokan-section-content dokan-clearfix">
        <div class="dokan-form-group">
            <label for="_wc_booking_resource_label" class="form-label"><?php _e( 'Label', 'dokan-wc-booking' ); ?>
                <span class="dokan-tooltips-help tips" title="" data-original-title="<?php _e( 'The label shown on the frontend if the resource is customer defined.', 'dokan-wc-booking' ); ?>">
                    <i class="fa fa-question-circle"></i>
                </span>
            </label>
            <?php dokan_post_input_box( $post_id, '_wc_booking_resource_label', array( 'placeholder' => __( 'Type', 'dokan-wc-booking' ), 'value' => $booking_resource_label ), 'text' ); ?>
        </div>
        <div id="resource_options" class="dokan-input-group options_group">
            <label for="_wc_booking_resources_assignment" class="form-label"><?php _e( 'Resources are...', 'dokan-wc-booking' ); ?>
                <span class="dokan-tooltips-help tips" title="" data-original-title="<?php _e( 'Customer selected resources allow customers to choose one from the booking form.', 'dokan-wc-booking' ); ?>">
                    <i class="fa fa-question-circle"></i>
                </span>
            </label>
            <select name="_wc_booking_resources_assignment" id="_wc_booking_resources_assignment" class="dokan-form-control short" style="width: auto; margin-right: 7px;">
                <option value="customer" <?php selected( $booking_resource_assignment, 'customer' ); ?>><?php _e( 'Customer selected', 'dokan-wc-booking' ); ?></option>
                <option value="automatic" <?php selected( $booking_resource_assignment, 'automatic' ); ?>><?php _e( 'Automatically assigned', 'dokan-wc-booking' ); ?></option>
            </select>
        </div>

        <div class="options_group">

            <div class="toolbar">
                <h3><?php _e( 'Resources', 'dokan-wc-booking' ); ?></h3>
                <!--<span class="toolbar_links"><a href="#" class="close_all"><?php // _e( 'Close all', 'dokan-wc-booking' );   ?></a><a href="#" class="expand_all"><?php // _e( 'Expand all', 'dokan-wc-booking' );   ?></a></span>-->
            </div>

            <div class="woocommerce_bookable_resources wc-metaboxes">

                <div id="message" class="inline woocommerce-message updated" style="margin: 1em 0;">
                    <p><?php _e( 'Resources are used if you have multiple bookable items, e.g. room types, instructors or ticket types. Availability for resources is global across all bookable products.', 'dokan-wc-booking' ); ?></p>
                </div>

                <?php
                global $wpdb;

                $all_resources = get_posts( array(
                    'post_type'      => 'bookable_resource',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'menu_order',
                    'order'          => 'asc',
                    'author'         => get_current_user_id(),
                ) );
                
                if ( $post_id ) {
                    $product_resources = $wpdb->get_col( $wpdb->prepare( "SELECT resource_id FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d ORDER BY sort_order;", $post_id ) );
                    $loop              = 0;

                    if ( $product_resources ) {
                        $resource_base_costs  = get_post_meta( $post_id, '_resource_base_costs', true );
                        $resource_block_costs = get_post_meta( $post_id, '_resource_block_costs', true );

                        foreach ( $product_resources as $resource_id ) {
                            $resource            = get_post( $resource_id );
                            $resource_base_cost  = isset( $resource_base_costs[$resource_id] ) ? $resource_base_costs[$resource_id] : '';
                            $resource_block_cost = isset( $resource_block_costs[$resource_id] ) ? $resource_block_costs[$resource_id] : '';

                            include( DOKAN_WC_BOOKING_TEMPLATE_PATH.'booking/resources/html-booking-resource.php' );

                            $loop++;
                        }
                    }
                }
            ?>
            </div>
            <?php if( $all_resources ) {?>
            <p class="toolbar">
                <button type="button" class="button button-primary dokan-btn dokan-btn-theme add_resource"><?php _e( 'Add/link Resource', 'dokan-wc-booking' ); ?></button>
                <select name="add_resource_id" class="add_resource_id">
                    <!--<option value=""><?php // _e( 'New resource', 'dokan-wc-booking' ); ?></option>-->
                    <?php
                    if ( $all_resources ) {
                        foreach ( $all_resources as $resource ) {
                            echo '<option value="' . esc_attr( $resource->ID ) . '">#' . $resource->ID . ' - ' . esc_html( $resource->post_title ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <!--<a href="<?php //echo admin_url( 'edit.php?post_type=bookable_resource' );   ?>" target="_blank"><?php _e( 'Manage Resources', 'dokan-wc-booking' ); ?></a>-->
            </p>
            <?php } else { ?>
            <div class="dokan-alert dokan-alert-warning">
                <?php
                $resource_add_link = dokan_get_navigation_url( 'booking' ).'resources/';
                
                echo sprintf( __( "No existing resource was found, please <a href='%s'>add resource</a>" ), $resource_add_link ); 
                ?>
            </div>
            <?php } ?>
        </div>
    </div>
</div>