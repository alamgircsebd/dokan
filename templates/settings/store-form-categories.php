<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_store_categories"><?php _e( 'Categories', 'dokan' ); ?></label>

    <div class="dokan-w5 dokan-text-left">
        <select name="dokan_store_categories[]" id="dokan_store_categories" multiple>
            <option value=""><?php esc_html_e( 'Select categories', 'dokan' ); ?></option>
            <?php foreach ( $categories as $category ): ?>
                <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo in_array( $category->term_id, $store_categories ) ? 'selected' : ''; ?>>
                    <?php echo esc_html( $category->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
