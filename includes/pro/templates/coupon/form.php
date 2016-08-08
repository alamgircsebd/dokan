<?php
/**
 * Dashboard Coupon Form Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<form method="post" action="" class="dokan-form-horizontal coupons">
    <input type="hidden"  value="<?php echo $post_id; ?>" name="post_id">
    <?php wp_nonce_field('coupon_nonce','coupon_nonce_field'); ?>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Coupon Title', 'dokan' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="title" name="title" required value="<?php echo esc_attr( $post_title ); ?>" placeholder="<?php _e( 'Title', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="description"><?php _e( 'Description', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <textarea class="dokan-form-control" id="description" name="description"><?php echo esc_textarea( $description ); ?></textarea>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="discount_type"><?php _e( 'Discount Type', 'dokan' ); ?></label>

        <div class="dokan-w5 dokan-text-left">
            <select id="discount_type" name="discount_type" class="dokan-form-control">
                <option value="fixed_product"><?php _e( 'Product Discount', 'dokan' ); ?></option>
                <option value="percent_product" <?php echo $discount_type; ?> ><?php _e( 'Product % Discount', 'dokan' ); ?></option>
            </select>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="amount"><?php _e( 'Amount', 'dokan' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="amount" required value="<?php echo esc_attr( $amount ); ?>" name="amount" placeholder="<?php _e( 'Amount', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="email_restrictions"><?php _e( 'Email Restrictions', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="email_restrictions" value="<?php echo esc_attr( $customer_email ); ?>" name="email_restrictions" placeholder="<?php _e( 'Email restrictions', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="usage_limit"><?php _e( 'Usage Limit', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="usage_limit" value="<?php echo esc_attr( $usage_limit ); ?>" name="usage_limit" placeholder="<?php _e( 'Usage Limit', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="dokan-expire"><?php _e( 'Expire Date', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="dokan-expire" value="<?php echo esc_attr( $expire ); ?>" name="expire" placeholder="<?php _e( 'Expire Date', 'dokan' ); ?>" class="dokan-form-control input-md datepicker" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="product-dropdown"><?php _e( 'Product', '' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <select id="product-dropdown" name="product_drop_down[]" class="dokan-form-control" multiple data-placeholder="<?php _e( 'Select Some Product', 'dokan' ); ?>" required>
                <?php
                foreach ( $all_products as $key => $object ) {
                    if ( in_array( $object->ID, $products_id ) ) {
                        $select = 'selected';
                    } else {
                        $select = '';
                    }
                    ?>
                    <option <?php echo $select; ?>  value="<?php echo $object->ID; ?>"><?php echo $object->post_title; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="checkboxes"><?php _e( 'Exclude Sale Items', 'dokan' ); ?></label>
        <div class="dokan-w7 dokan-text-left">
            <div class="checkbox">
                <label for="checkboxes-2">
                    <input name="exclude_sale_items" <?php echo $exclide_sale_item; ?> id="checkboxes-2" value="yes" type="checkbox">
                    <?php _e( 'Check this box if the coupon should not apply to items on sale.', 'dokan' );?>
                </label>

                <div class="help">
                    <?php _e(' Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are no sale items in the cart.', 'dokan' ); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="minium_ammount"><?php _e( 'Minimum Amount', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="minium_ammount" value="<?php echo $minimum_amount; ?>" name="minium_ammount" placeholder="<?php esc_attr_e( 'Minimum Amount', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="product"><?php _e( 'Exclude products', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <select id="coupon_exclude_categories" name="exclude_product_ids[]" class="dokan-form-control" multiple data-placeholder="<?php _e( 'Select Some Product', 'dokan' ); ?>">
                <?php
                foreach ( $all_products as $key => $object ) {
                    if ( in_array( $object->ID, $exclude_products ) ) {
                        $select = 'selected';
                    } else {
                        $select = '';
                    }
                    ?>
                        <option <?php echo $select; ?>  value="<?php echo $object->ID; ?>"><?php _e( $object->post_title, 'dokan' ); ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="checkboxes"><?php _e( 'Show on store', 'dokan' ); ?></label>
        <div class="dokan-w7 dokan-text-left">
            <div class="checkbox">
                <label for="checkboxes-3">
                    <input name="show_on_store" <?php echo $show_on_store; ?> id="checkboxes-3" value="yes" type="checkbox">
                    <?php _e( 'Check this box if you want to show this coupon in store page.', 'dokan' );?>
                </label>
            </div>
        </div>
    </div>

    <div class="dokan-form-group">
        <div class="dokan-w5 ajax_prev dokan-text-left" style="margin-left:23%">
            <input type="submit" id="" name="coupon_creation" value="<?php echo $button_name; ?>" class="dokan-btn dokan-btn-danger dokan-btn-theme">
        </div>
    </div>

    </form>

    <script type="text/javascript">

    jQuery(function($){
        $("#product-dropdown").chosen({width: "95%"});
        $("#coupon_exclude_categories").chosen({width: "95%"});
    });

    </script>
