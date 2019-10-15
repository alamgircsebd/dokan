<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce dokan-pa-all-addons">
	<a class="dokan-btn dokan-btn-theme dokan-pa-create-btn" href="<?php echo add_query_arg( 'add', true, dokan_get_navigation_url( 'settings/product-addon' ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Create New addon', 'woocommerce-product-addons' ); ?></a>

	<table id="global-addons-table" class="dokan-table" cellspacing="0">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'woocommerce-product-addons' ); ?></th>
				<th><?php esc_html_e( 'Priority', 'woocommerce-product-addons' ); ?></th>
				<th><?php esc_html_e( 'Product Categories', 'woocommerce-product-addons' ); ?></th>
				<th><?php esc_html_e( 'Number of Fields', 'woocommerce-product-addons' ); ?></th>
			</tr>
		</thead>
		<tbody id="the-list">
			<?php
			$global_addons = WC_Product_Addons_Groups::get_all_global_groups();

			if ( $global_addons ) {
				foreach ( $global_addons as $global_addon ) {
					?>
					<tr>
						<td><a href="<?php echo add_query_arg( 'edit', $global_addon['id'], admin_url( 'edit.php?post_type=product&page=addons' ) ); ?>"><?php echo $global_addon['name']; ?></a>
							<div class="row-actions">
                                <span class="edit"><a href="<?php echo esc_url( add_query_arg( 'edit', $global_addon['id'], dokan_get_navigation_url( 'settings/product-addon' ) ) ); ?>"><?php esc_html_e( 'Edit', 'woocommerce-product-addons' ); ?></a> | </span>
                                <span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'delete', $global_addon['id'], dokan_get_navigation_url( 'settings/product-addon' ) ), 'delete_addon' ) ); ?>"><?php esc_html_e( 'Delete', 'woocommerce-product-addons' ); ?></a></span>
                            </div>
						</td>
						<td><?php echo $global_addon['priority']; ?></td>
						<td>
						<?php
						$all_products = '1' === get_post_meta( $global_addon['id'], '_all_products', true ) ? true : false;
						$restrict_to_categories = $global_addon['restrict_to_categories'];

						if ( $all_products ) {
							esc_html_e( 'All Products', 'woocommerce-product-addons' );
						} elseif ( 0 === count( $restrict_to_categories ) ) {
							esc_html_e( 'No Products Assigned', 'woocommerce-product-addons' );
						} else {
							$objects = array_keys( $restrict_to_categories );
							$term_names = array_values( $restrict_to_categories );
							$term_names = apply_filters( 'woocommerce_product_addons_global_display_term_names', $term_names, $objects );
							echo implode( ', ', $term_names );
						}
						?>
						</td>
						<td><?php echo sizeof( $global_addon['fields'] ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No add-ons found.', 'woocommerce-product-addons' ); ?> <a href="<?php echo add_query_arg( 'add', true, admin_url( 'edit.php?post_type=product&page=addons' ) ); ?>"><?php esc_html_e( 'Create add-ons.', 'woocommerce-product-addons' ); ?></a></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>
