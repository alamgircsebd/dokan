<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\SubscriptionPack;
use DokanPro\Modules\Subscription\Helper;
use WeDevs\Dokan\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * DPS Shortcode Class
 */
class Shortcode {

    use Singleton;

    /**
     * Boot method
     */
    public function boot() {
        $this->init_hooks();
    }

    /**
     * Init all hooks
     *
     * @return void
     */
    private function init_hooks() {
        add_shortcode( 'dps_product_pack', [ __CLASS__, 'create_subscription_package_shortcode' ] );
        add_action( 'dokan_after_saving_settings', [ __CLASS__, 'insert_shortcode_into_page' ], 10, 2 );
    }

    /**
     * Create subscription package shortcode
     *
     * @return void
     */
    public static function create_subscription_package_shortcode() {
        global $post;

        $user_id            = dokan_get_current_user_id();
        $subscription_packs = dokan()->subscription->all();

        ob_start();
        ?>

        <div class="dokan-subscription-content">
            <?php
                $subscription = dokan()->vendor->get( $user_id )->subscription;
            ?>

            <?php if ( $subscription && $subscription->has_pending_subscription() ) : ?>
                <div class="seller_subs_info">
                    <?php printf(
                            __( 'The intend <span>%s</span> subscription is inactive due to payment failure. Please <a href="?add-to-cart=%s">Pay Now</a> to active it again.', 'dokan' ),
                            $subscription->get_package_title(),
                            $subscription->get_id()
                        );
                    ?>
                </div>
            <?php elseif ( $subscription && $subscription->can_post_product() ) : ?>
                <div class="seller_subs_info">
                    <p>
                        <?php
                            if ( $subscription->is_trial() ) {
                                $trial_title = $subscription->get_trial_range() . ' ' . $subscription->get_trial_period_types();

                                printf( __( 'Your are using <span>%s (%s trial)</span> package.', 'dokan' ), $subscription->get_package_title(), $trial_title  );
                            } else {
                                printf( __( 'Your are using <span>%s</span> package.', 'dokan' ), $subscription->get_package_title() );
                            }
                        ?>
                    </p>
                    <p>
                        <?php
                            $no_of_product = '-1' !== $subscription->get_number_of_products() ? $subscription->get_number_of_products() : __( 'unlimited', 'dokan' );

                            if ( $subscription->is_recurring() ) {
                                printf( __( 'You can add <span>%s</span> products', 'dokan' ), $no_of_product );
                            } elseif ( $subscription->get_pack_end_date() === 'unlimited' ) {
                                printf( __( 'You can add <span>%s</span> product(s) for <span> unlimited days</span> days.', 'dokan' ), $no_of_product );
                            } else {
                                printf( __( 'You can add <span>%s</span> product(s) for <span>%s</span> days.', 'dokan' ), $no_of_product, $subscription->get_pack_valid_days() );
                            }
                        ?>
                    </p>
                    <p>
                        <?php
                            if ( $subscription->has_active_cancelled_subscrption() ) {
                                $date   = date_i18n( get_option( 'date_format' ), strtotime( $subscription->get_pack_end_date() ) );
                                $notice = sprintf( __( 'Your subscription has been cancelled! However it\'s is still active till %s', 'dokan' ), $date );
                                printf( "<span>{$notice}</span>" );
                            } else {
                                if ( $subscription->is_trial() ) {
                                    // don't show any text
                                } elseif ( $subscription->is_recurring() ) {
                                    echo sprintf( __( 'You will be charged in every %d', 'dokan' ), $subscription->get_recurring_interval() ) . ' ' . Helper::recurring_period( $subscription->get_period_type() );
                                } elseif ( $subscription->get_pack_end_date() === 'unlimited' ) {
                                    printf( __( 'You have a lifetime package.', 'dokan' ) );
                                } else {
                                    printf( __( 'Your package will expire on <span>%s</span>', 'dokan' ), date_i18n( get_option( 'date_format' ), strtotime( $subscription->get_pack_end_date() ) ) );
                                }
                            }
                        ?>
                    </p>

                    <?php
                        if ( ! ( ! $subscription->is_recurring() && $subscription->has_active_cancelled_subscrption() ) ) {
                            ?>
                            <p>
                                <form action="" method="post">
                                    <?php
                                        $maybe_reactivate = $subscription->is_recurring() && $subscription->has_active_cancelled_subscrption();
                                        $notice           = $maybe_reactivate ? __( 'activate', 'dokan' ) : __( 'cancel', 'dokan' );
                                        $nonce            = $maybe_reactivate ? 'dps-sub-activate' : 'dps-sub-cancel';
                                        $input_name       = $maybe_reactivate ? 'dps_activate_subscription' : 'dps_cancel_subscription';
                                        $btn_class        = $maybe_reactivate ? 'btn-success' : 'btn-danger';
                                        $again            = $maybe_reactivate ? __( 'again', 'dokan' ) : '';
                                    ?>

                                    <label><?php _e( "To {$notice} your subscription {$again} click here &rarr;", "dokan" ); ?></label>

                                    <?php wp_nonce_field( $nonce ); ?>
                                    <input type="submit" name="<?php echo esc_attr( $input_name ); ?>" class="<?php echo esc_attr( "btn btn-sm {$btn_class}" ); ?>" value="<?php echo esc_attr( ucfirst( $notice ) ); ?>">
                                </form>
                            </p>
                            <?php
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ( $subscription_packs->have_posts() ) {
                ?>

                <?php if ( isset( $_GET['msg'] ) && 'dps_sub_cancelled' === $_GET['msg'] ) : ?>
                    <div class="dokan-message">
                        <?php
                            if ( $subscription && $subscription->has_active_cancelled_subscrption() ) {
                                $date   = date_i18n( get_option( 'date_format' ), strtotime( $subscription->get_pack_end_date() ) );
                                $notice = sprintf( __( 'Your subscription has been cancelled! However the it\'s is still active till %s', 'dokan' ), $date );
                            } else {
                                $notice = __( 'Your subscription has been cancelled!', 'dokan' );
                            }
                        ?>

                        <p><?php printf( $notice ); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ( isset( $_GET['msg'] ) && 'dps_sub_activated' === $_GET['msg'] ) : ?>
                    <div class="dokan-message">
                        <?php
                            esc_html_e( 'Your subscription has been re-activated!', 'dokan' );
                        ?>
                    </div>
                <?php endif; ?>

                <div class="pack_content_wrapper">

                <?php
                while ( $subscription_packs->have_posts() ) {
                    $subscription_packs->the_post();

                    // get individual subscriptoin pack details
                    $sub_pack           = dokan()->subscription->get( get_the_ID() );
                    $is_recurring       = $sub_pack->is_recurring();
                    $recurring_interval = $sub_pack->get_recurring_interval();
                    $recurring_period   = $sub_pack->get_period_type();
                    ?>

                    <div class="product_pack_item <?php echo ( Helper::is_vendor_subscribed_pack( get_the_ID() ) || Helper::pack_renew_seller( get_the_ID() ) ) ? 'current_pack ' : ''; ?><?php echo ( $sub_pack->is_trial() && Helper::has_used_trial_pack( get_current_user_id(), get_the_id() ) ) ? 'fp_already_taken' : ''; ?>">
                        <div class="pack_price">

                            <span class="dps-amount">
                                <?php echo wc_price( $sub_pack->get_price() ); ?>
                            </span>

                            <?php if ( $is_recurring && $recurring_interval === 1 ) { ?>
                                <span class="dps-rec-period">
                                    <span class="sep">/</span><?php echo Helper::recurring_period( $recurring_period ); ?>
                                </span>
                            <?php } ?>
                        </div><!-- .pack_price -->

                        <div class="pack_content">
                            <h2><?php echo $sub_pack->get_package_title(); ?></h2>

                            <?php the_content();

                            $no_of_product = $sub_pack->get_number_of_products();

                            if ( '-1' === $no_of_product ) {
                                printf( __( '<div class="pack_data_option"><strong>Unlimited</strong> Products <br />', 'dokan' ) );
                            } else {
                                printf( __( '<div class="pack_data_option"><strong>%d</strong> Products <br />', 'dokan' ), $no_of_product );
                            }

                            ?>

                            <?php if ( $is_recurring && $sub_pack->is_trial() && Helper::has_used_trial_pack( get_current_user_id() ) ) : ?>
                                <span class="dps-rec-period">
                                    <?php printf( __( 'In every %d %s(s)</div>', 'dokan' ), $recurring_interval, Helper::recurring_period( $recurring_period ) ); ?>
                                </span>
                            <?php elseif ( $is_recurring && $sub_pack->is_trial() ) : ?>
                                <span class="dps-rec-period">
                                    <?php printf( __( 'In every %d %s(s) <p class="trail-details">%d %s(s) trial </p> </div>', 'dokan' ), $recurring_interval, Helper::recurring_period( $recurring_period ), $sub_pack->get_trial_range(), Helper::recurring_period( $sub_pack->get_trial_period_types() ) ); ?>
                                </span>
                            <?php elseif ( $is_recurring && $recurring_interval >= 1) : ?>
                                <span class="dps-rec-period">
                                    <?php printf( __( 'In every %d %s(s)</div>', 'dokan' ), $recurring_interval, Helper::recurring_period( $recurring_period ) ); ?>
                                </span>
                            <?php else :
                                if ( $sub_pack->get_pack_valid_days() == 0 ) {
                                    printf( __( 'For<br /><strong>Unlimited</strong> Days</div>', 'dokan' ) );
                                } else {
                                    $pack_validity = $sub_pack->get_pack_valid_days();
                                    printf( __( 'For<br /><strong>%s</strong> Days</div>', 'dokan' ), $pack_validity );
                                }
                            endif; ?>

                        </div>

                        <div class="buy_pack_button">
                            <?php if ( Helper::is_vendor_subscribed_pack( get_the_ID() ) ): ?>

                                <a href="<?php echo get_permalink( get_the_ID() ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Your Pack', 'dokan' ); ?></a>

                            <?php elseif ( Helper::pack_renew_seller( get_the_ID() ) ): ?>

                                <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Renew', 'dokan' ); ?></a>

                            <?php else: ?>

                                <?php if ( $sub_pack->is_trial() && Helper::vendor_has_subscription( dokan_get_current_user_id() ) && Helper::has_used_trial_pack( dokan_get_current_user_id() ) ): ?>
                                    <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Switch Plan', 'dokan' ); ?></a>
                                <?php elseif ( $sub_pack->is_trial() && Helper::has_used_trial_pack( dokan_get_current_user_id() ) ) : ?>
                                    <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Buy Now', 'dokan' ); ?></a>

                                <?php elseif ( ! Helper::vendor_has_subscription( dokan_get_current_user_id() ) ) : ?>
                                        <?php if ( $sub_pack->is_trial() ) : ?>
                                            <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack trial_pack"><?php _e( 'Start Free Trial', 'dokan' ); ?></a>
                                        <?php else: ?>
                                            <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Buy Now', 'dokan' ); ?></a>
                                        <?php endif; ?>

                                <?php else: ?>
                                    <a href="<?php echo do_shortcode( '[add_to_cart_url id="' . get_the_ID() . '"]' ); ?>" class="dokan-btn dokan-btn-theme buy_product_pack"><?php _e( 'Switch Plan', 'dokan' ); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<h3>' . __( 'No subscription pack has been found!', 'dokan' ) . '</h3>';
            }

            wp_reset_postdata();
            ?>
            <div class="clearfix"></div>
            </div>
        </div>
        <?php

        $contents = ob_get_clean();

        return apply_filters( 'dokan_sub_shortcode', $contents, $subscription_packs );
    }

    /**
     * Insert subscription shortcode into specefied page
     *
     * @param  string $option
     * @param  array $value
     *
     * @return void
     */
    public static function insert_shortcode_into_page( $option, $value ) {
        if ( ! $option || 'dokan_product_subscription' !== $option ) {
            return;
        }

        $page_id = isset( $value['subscription_pack'] ) ? $value['subscription_pack'] : null;

        if ( ! $page_id ) {
            return;
        }

        $content = [
            'ID'           => $page_id,
            'post_content' => '[dps_product_pack]'
        ];

        $insert = wp_update_post( $content );

        if ( is_wp_error( $insert ) ) {
            return wp_send_json_error( $insert->get_error_message() );
        }
    }
}

Shortcode::instance();
