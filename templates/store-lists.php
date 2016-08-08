<div class="dokan-seller-listing">

    <?php
        global $post;
        $pagination_base = str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) );

        if ( $search == 'yes' ) {
        $search_query = isset( $_GET['dokan_seller_search'] ) ? sanitize_text_field( $_GET['dokan_seller_search'] ) : '';

        if ( ! empty( $search_query ) ) {
            printf( '<h2>' . __( 'Search Results for: %s', 'dokan' ) . '</h2>', $search_query );
        }
    ?>

        <form role="search" method="get" class="dokan-seller-search-form" action="">
            <label>
                <span class="screen-reader-text"><?php _e( 'Search Seller', 'dokan' ); ?></span>
                <input type="search" id="search" class="search-field" placeholder="<?php esc_attr_e( 'Search seller &hellip;', 'dokan' ); ?>" value="<?php echo esc_attr( $search_query ); ?>" name="dokan_seller_search" title="<?php esc_attr_e( 'Search seller &hellip;', 'dokan' ); ?>" />
                <input type="hidden" id="pagination_base" name="pagination_base" value="<?php echo $pagination_base ?>" />
                <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce( 'dokan-seller-listing-search' ); ?>" />
            </label>
        </form>

    <?php } ?>

    <div id="dokan-seller-listing-wrap">
        <?php
            $template_args = array(
                'sellers'         => $sellers,
                'limit'           => $limit,
                'offset'          => $offset,
                'paged'           => $paged,
                'search_query'    => $search_query,
                'pagination_base' => $pagination_base,
            );

            echo dokan_get_template_part( 'store-lists-loop', false, $template_args );
        ?>
    </div>
</div>