<?php $_sold_individually = get_post_meta( $post_id, '_sold_individually', true ); ?>
<div class="dokan-form-horizontal">
    <div class="dokan-form-group">
        <label class="dokan-w4 dokan-control-label" for="_purchase_note"><?php _e( 'Purchase Note', 'dokan-pro' ); ?></label>
        <div class="dokan-w6 dokan-text-left">
            <?php dokan_post_input_box( $post->ID, '_purchase_note', array(), 'textarea' ); ?>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w4 dokan-control-label" for="_enable_reviews"><?php _e( 'Reviews', 'dokan-pro' ); ?></label>
        <div class="dokan-w4 dokan-text-left">
            <?php $_enable_reviews = ( $post->comment_status == 'open' ) ? 'yes' : 'no'; ?>
            <?php dokan_post_input_box( $post->ID, '_enable_reviews', array('value' => $_enable_reviews, 'label' => __( 'Enable Reviews', 'dokan-pro' ) ), 'checkbox' ); ?>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w4 dokan-control-label" for="_purchase_note"><?php _e( 'Visibility', 'dokan-pro' ); ?></label>
        <div class="dokan-w6 dokan-text-left">
            <?php dokan_post_input_box( $post->ID, '_visibility', array( 'options' => array(
                'visible' => __( 'Catalog or Search', 'dokan-pro' ),
                'catalog' => __( 'Catalog', 'dokan-pro' ),
                'search' => __( 'Search', 'dokan-pro' ),
                'hidden' => __( 'Hidden', 'dokan ')
            ) ), 'select' ); ?>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w4 dokan-control-label" for="_sold_individually"><?php _e( 'Sold Individually', 'dokan-pro' ); ?></label>
        <div class="dokan-w7 dokan-text-left">
            <input name="_sold_individually" id="_sold_individually" value="yes" type="checkbox" <?php checked( $_sold_individually, 'yes' ); ?>>
            <?php _e( 'Allow only one quantity of this product to be bought in a single order', 'dokan-pro' ) ?>
        </div>
    </div>

</div> <!-- .form-horizontal -->