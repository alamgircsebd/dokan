<?php

/**
 * Adds WC_Product_Day widget.
 */
class Dokan_BestSelling_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'Dokan_BestSellig', // Base ID
            __( 'Dokan Best Selling Widget', 'dokan'), // Name
            array( 'description' => __( 'A Widget for displaying Best Selling Products', 'dokan' ), ) // Args
        );

        self::$order_by_options = array(
            'title'    => 'Title',
            'date'     => 'Date',
            'rand'     => 'Random' 
        );

        // self::$order_options = array(
        //     'ASC' => 'Ascending', 
        //     'DESC'    => 'Descending' 
        // );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        extract( $instance );

        $query_args = array(
            'posts_per_page' => $no_of_product,
            'post_status'    => 'publish',
            'post_type'      => 'product', 
            'orderby'        => $order_by,
            'order'          => ( $order_by == 'post__in' ) ? 'none' : $order,
            'post__in'       => array( 53, 83, 9, 73, 70, 67, 56, 50, 405 ) // TODO: Set products id by Days 
        );
        
        $query_args['meta_query'] = WC()->query->get_meta_query();

        $r = new WP_Query( $query_args );

        if ( $r->have_posts() ) {

            echo $args['before_widget'];
            if ( ! empty( $title ) ) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            echo '<ul class="nc_product_day_widget product_list_widget">';

            while ( $r->have_posts()) {
                $r->the_post();

                global $product;
                ?>
                    <li>
                        <?php if( isset( $show_thumb ) && $show_thumb == '1' ):  ?>
                            <div class="nc_widget_thumb">
                                <?php echo $product->get_image(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="nc_product_details">
                            <?php if ( isset( $show_title ) && $show_title == '1' ): ?>
                                <a href="<?php echo get_permalink( $product->ID ); ?>"><?php echo $product->get_title(); ?></a>
                            <?php endif; ?>
                            <?php if ( isset( $show_rating ) && $show_rating == 1 ): ?>
                                <?php echo $product->get_rating_html(); ?>
                            <?php endif; ?>
                            <?php if ( isset( $show_price ) && $show_price == '1' ): ?>
                                <?php echo $product->get_price_html(); ?> 
                            <?php endif; ?>
                            <?php if ( isset( $show_cart_button ) && $show_cart_button == '1' ): ?>
                                <p><?php woocommerce_template_loop_add_to_cart(); ?></p>
                            <?php endif; ?>
                        </div>
                    </li>

                <?php
            }

            echo '</ul>';

            echo $args['after_widget'];
        }

        wp_reset_postdata();
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = esc_attr( $instance[ 'title' ] );
            $no_of_product = esc_attr( intval( $instance[ 'no_of_product' ] ) );
            $order_by = esc_attr( $instance['order_by'] );
            $order = esc_attr( $instance['order'] );
            $show_thumb = esc_attr( $instance['show_thumb'] );
            $show_cart_button = esc_attr( $instance['show_cart_button'] );
            $show_price = esc_attr( $instance['show_price'] );
            $show_title  = esc_attr( $instance['show_title'] );
            $show_rating = esc_attr( $instance['show_rating'] );
        }  else {
            $title = __( 'Product of the Day', 'nc_wcpotd' );
            $no_of_product = '';
            $order_by = 'post__in';
            $order = 'ASC';
            $show_thumb = '1';
            $show_cart_button = '0';
            $show_price = '1';
            $show_title = '1';
            $show_rating = '0';
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'no_of_product' ); ?>"><?php _e( 'No of Product:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'no_of_product' ); ?>" name="<?php echo $this->get_field_name( 'no_of_product' ); ?>" type="text" value="<?php echo ( $no_of_product == '-1' ) ? '' : $no_of_product; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By', 'nc_wcpotd' ); ?></label>
            <select name="<?php echo $this->get_field_name('order_by'); ?>" id="<?php echo $this->get_field_id('order_by'); ?>" class="widefat">
                <?php foreach ( self::$order_by_options as $key=>$option ) : ?>
                    <option value="<?php echo $key ?>" <?php selected( $key, $order_by ); ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order', 'nc_wcpotd'); ?></label>
            <select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>" class="widefat">
                <?php foreach (self::$order_options as $key=>$option ) : ?>
                    <option value="<?php echo $key ?>" <?php selected( $key, $order ); ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" type="checkbox" value="1" <?php checked( '1', $show_thumb ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Show Thumbnail', 'nc_wcpotd' ); ?></label>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_cart_button' ); ?>" name="<?php echo $this->get_field_name( 'show_cart_button' ); ?>" type="checkbox" value="1" <?php checked( '1', $show_cart_button ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_cart_button' ); ?>"><?php _e( 'Show Add to Cart Button', 'nc_wcpotd' ); ?></label>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id( 'show_price' ); ?>" name="<?php echo $this->get_field_name( 'show_price' ); ?>" type="checkbox" value="1" <?php checked( '1', $show_price ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_price' ); ?>"><?php _e( 'Show Product Price', 'nc_wcpotd' ); ?></label>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="checkbox" value="1" <?php checked( '1', $show_title ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Product Title', 'nc_wcpotd' ); ?></label>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_rating' ); ?>" name="<?php echo $this->get_field_name( 'show_rating' ); ?>" type="checkbox" value="1" <?php checked( '1', $show_rating ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_rating' ); ?>"><?php _e( 'Show Product Rating', 'nc_wcpotd' ); ?></label>
        </p>

        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['no_of_product'] = ( ! empty( $new_instance['no_of_product'] ) && is_numeric( $new_instance['no_of_product'] ) && $new_instance['no_of_product'] > 0 ) ? strip_tags( intval( $new_instance['no_of_product'] ) ) : '-1';
        $instance['order_by'] = ( ! empty( $new_instance['order_by'] ) ) ? strip_tags( $new_instance['order_by'] ) : 'post__in';
        $instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : 'ASC';
        $instance['show_thumb'] = ( ! empty( $new_instance['show_thumb'] ) ) ? strip_tags( $new_instance['show_thumb'] ) : '';
        $instance['show_cart_button'] = ( ! empty( $new_instance['show_cart_button'] ) ) ? strip_tags( $new_instance['show_cart_button'] ) : '';
        $instance['show_price'] = ( ! empty( $new_instance['show_price'] ) ) ? strip_tags( $new_instance['show_price'] ) : '';
        $instance['show_title'] = ( ! empty( $new_instance['show_title'] ) ) ? strip_tags( $new_instance['show_title'] ) : '';
        $instance['show_rating'] = ( ! empty( $new_instance['show_rating'] ) ) ? strip_tags( $new_instance['show_rating'] ) : '';

        return $instance;
    }

} // class WC_Product_Day