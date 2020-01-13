<div class="store-ratings item">
    <label>
        <?php esc_html_e( 'Rating:' ); ?>
    </label>

    <p class="stars">
        <?php
            foreach ( range( 1, 5 ) as $count ) {
                printf( '<a href="#" class="star-%1$s" data-rating="%2$s"></a>', $count, $count );
            }
        ?>

        <span class="up">& <?php esc_html_e( 'Up' ); ?></span>
    </p>
</div>