<li class="product-attribute-list <?php echo esc_attr( implode( ' ', $metabox_class ) ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
    <div class="dokan-product-attribute-heading">
        <span><i class="fa fa-bars" aria-hidden="true"></i>&nbsp;&nbsp;<strong><?php echo ! empty( $attribute_label ) ? esc_html( $attribute_label ) : _e( 'Attribute Name', 'dokan' ) ; ?></strong></span>
        <a href="#" class="dokan-product-remove-attribute"><?php _e( 'Remove', 'dokan' ); ?></a>
        <a href="#" class="dokan-product-toggle-attribute">
            <i class="fa fa-sort-desc fa-flip-horizointal" aria-hidden="true"></i>
        </a>
    </div>
    <div class="dokan-product-attribute-item dokan-clearfix dokan-hide">
        <div class="content-half-part">
            <label class="form-label" for="">Name</label>
            <?php if ( $attribute['is_taxonomy'] ) : ?>
				<strong><?php echo esc_html( $attribute_label ); ?></strong>
				<input type="hidden" name="attribute_names[]" value="<?php echo esc_attr( $taxonomy ); ?>" />
			<?php else : ?>
            	<input type="text" class="attribute_name dokan-form-control dokan-product-attribute-name" name="attribute_names[]" value="<?php echo esc_attr( $attribute['name'] ); ?>">
			<?php endif; ?>

			<input type="hidden" name="attribute_position[]" class="attribute_position" value="" />
			<input type="hidden" name="attribute_is_taxonomy[]" value="<?php echo $attribute['is_taxonomy'] ? 1 : 0; ?>" />

			<label class="checkbox-item form-label">
				<input type="checkbox" <?php checked( $attribute['is_visible'], 1 ); ?> name="attribute_visibility[]" value="1" /> <?php _e( 'Visible on the product page', 'dokan' ); ?>
			</label>

			<label class="checkbox-item form-label">
				<input type="checkbox" <?php checked( $attribute['is_variation'], 1 ); ?> name="attribute_variation[]" value="1" /> <?php _e( 'Used for variations', 'dokan' ); ?>
			</label>
        </div>

        <div class="content-half-part">
            <label for="" class="form-label"><?php _e( 'Value(s)', 'dokan' ); ?></label>

			<?php if ( $attribute['is_taxonomy'] ) : ?>
				<?php if ( 'select' === $attribute_taxonomy->attribute_type ) : ?>

					<select multiple="multiple" style="width:100%" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>" class="dokan_attribute_values dokan-select2" name="attribute_values[][]">
						<?php
						$args = array(
							'orderby'    => 'name',
							'hide_empty' => 0
						);
						$all_terms = get_terms( $taxonomy, apply_filters( 'dokan_product_attribute_terms', $args ) );
						if ( $all_terms ) {
							foreach ( $all_terms as $term ) {
								echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy, $thepostid ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
							}
						}
						?>
					</select>
					<div class="dokan-pre-defined-attribute-btn-group">
						<button class="dokan-btn dokan-btn-default plus dokan-select-all-attributes"><?php _e( 'Select all', 'dokan' ); ?></button>
						<button class="dokan-btn dokan-btn-default minus dokan-select-no-attributes"><?php _e( 'Select none', 'dokan' ); ?></button>
						<!-- <button class="dokan-btn dokan-btn-default fr plus dokan-add-new-attribute"><?php _e( 'Add new', 'dokan' ); ?></button> -->
					</div>
				<?php elseif ( 'text' == $attribute_taxonomy->attribute_type ) : ?>

					<input type="text" class="dokan-form-control" name="attribute_values[]" value="<?php
						// Text attributes should list terms pipe separated
						echo esc_attr( implode( ' ' . WC_DELIMITER . ' ', wp_get_post_terms( $thepostid, $taxonomy, array( 'fields' => 'names' ) ) ) );

					?>" placeholder="<?php echo esc_attr( sprintf( __( '"%s" separate terms', 'dokan' ), WC_DELIMITER ) ); ?>" />

				<?php endif; ?>

				<?php do_action( 'dokan_product_option_terms', $attribute_taxonomy, $i ); ?>

			<?php else : ?>
            	<select name="attribute_values[<?php echo $i; ?>]" id="" multiple style="width:100%" class="dokan-select2" data-placeholder="<?php echo esc_attr( sprintf( __( 'Enter some text, or some attributes by "%s" separating values.', 'doakn' ), WC_DELIMITER ) ); ?>" data-tags="true" data-allow-clear="true" data-token-separators="['|']"></select>
			<?php endif; ?>

        </div>
    </div>
</li>
