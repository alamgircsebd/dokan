<form method="post" action="" class="dokan-form-horizontal vendor-stuff">
    <input type="hidden"  value="<?php echo $is_edit; ?>" name="stuff_id">
    <input type="hidden"  value="<?php echo get_current_user_id(); ?>" name="vendor_id">

    <?php wp_nonce_field( 'vendor_stuff_nonce', 'vendor_stuff_nonce_field' ); ?>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'First Name', 'dokan' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="first_name" name="first_name" required value="<?php echo esc_attr( $first_name ); ?>" placeholder="<?php _e( 'First Name', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Last Name', 'dokan' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="last_name" name="last_name" required value="<?php echo esc_attr( $last_name ); ?>" placeholder="<?php _e( 'Last Name', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Email Address', 'dokan' ); ?><span class="required"> *</span></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="email" name="email" required value="<?php echo esc_attr( $email ); ?>" placeholder="<?php _e( 'Email', 'dokan' ); ?>" class="dokan-form-control input-md" type="email">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Phone Number', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="phone" name="phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="<?php _e( 'Phone', 'dokan' ); ?>" class="dokan-form-control input-md" type="number">
        </div>
    </div>

    <?php if ( $is_edit ): ?>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Password', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="password" name="password" value="" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="title"><?php _e( 'Confirm Password', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <input id="confirm_password" name="confirm_password" value="" class="dokan-form-control input-md" type="text">
        </div>
    </div>

    <?php endif ?>


    <div class="dokan-form-group">
        <div class="dokan-w5 dokan-text-left" style="margin-left:25%">
            <input type="submit" id="" name="stuff_creation" value="<?php echo $button_name; ?>" class="dokan-btn dokan-btn-danger dokan-btn-theme">
        </div>
    </div>

</form>
