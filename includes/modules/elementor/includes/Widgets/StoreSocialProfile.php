<?php

namespace DokanPro\Modules\Elementor\Widgets;

use DokanPro\Modules\Elementor\Controls\DynamicHidden;
use DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Social_Icons;

class StoreSocialProfile extends Widget_Social_Icons {

    use PositionControls;

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-social-profile';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Social Profile', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-social-icons';
    }

    /**
     * Widget categories
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'social', 'profile', 'icons' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function _register_controls() {
        parent::_register_controls();


        $repeater = new Repeater();

        $repeater->add_control(
            'social_icon',
            [
                'label' => __( 'Icon', 'dokan' ),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'social',
                'label_block' => true,
                'default' => [
                    'value' => 'fab fa-wordpress',
                    'library' => 'fa-brands',
                ],
                'recommended' => [
                    'fa-brands' => [
                        'android',
                        'apple',
                        'behance',
                        'bitbucket',
                        'codepen',
                        'delicious',
                        'deviantart',
                        'digg',
                        'dribbble',
                        'elementor',
                        'facebook',
                        'flickr',
                        'foursquare',
                        'free-code-camp',
                        'github',
                        'gitlab',
                        'globe',
                        'google-plus',
                        'houzz',
                        'instagram',
                        'jsfiddle',
                        'linkedin',
                        'medium',
                        'meetup',
                        'mixcloud',
                        'odnoklassniki',
                        'pinterest',
                        'product-hunt',
                        'reddit',
                        'shopping-cart',
                        'skype',
                        'slideshare',
                        'snapchat',
                        'soundcloud',
                        'spotify',
                        'stack-overflow',
                        'steam',
                        'stumbleupon',
                        'telegram',
                        'thumb-tack',
                        'tripadvisor',
                        'tumblr',
                        'twitch',
                        'twitter',
                        'viber',
                        'vimeo',
                        'vk',
                        'weibo',
                        'weixin',
                        'whatsapp',
                        'wordpress',
                        'xing',
                        'yelp',
                        'youtube',
                        '500px',
                    ],
                    'fa-solid' => [
                        'envelope',
                        'link',
                        'rss',
                    ],
                ],
            ]
        );

        $repeater->add_control(
            'item_icon_color',
            [
                'label' => __( 'Color', 'dokan' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __( 'Official Color', 'dokan' ),
                    'custom' => __( 'Custom', 'dokan' ),
                ],
            ]
        );

        $repeater->add_control(
            'item_icon_primary_color',
            [
                'label' => __( 'Primary Color', 'dokan' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'item_icon_color' => 'custom',
                ],
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $repeater->add_control(
            'item_icon_secondary_color',
            [
                'label' => __( 'Secondary Color', 'dokan' ),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'item_icon_color' => 'custom',
                ],
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} {{CURRENT_ITEM}} svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->update_control(
            'social_icon_list',
            [
                'fields'  => $repeater->get_controls(),
            ]
        );

        $this->add_control(
            'store_social_links',
            [
                'type'    => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-social-profile-tag' ),
                    'active'  => true,
                ]
            ],
            [
                'position' => [ 'of' => 'social_icon_list' ],
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Set wrapper classes
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' dokan-store-social-profile elementor-widget-' . parent::get_name();
    }

    /**
     * Frontend render method
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $store_social_links = json_decode( $settings['store_social_links'], true );

        if ( dokan_is_store_page() && empty( $store_social_links ) ) {
            echo '<div></div>';
            return;
        }

        $fallback_defaults = [
            'fa fa-facebook',
            'fa fa-twitter',
            'fa fa-google-plus',
        ];

        $class_animation = '';

        if ( ! empty( $settings['hover_animation'] ) ) {
            $class_animation = ' elementor-animation-' . $settings['hover_animation'];
        }

        $migration_allowed = Icons_Manager::is_migration_allowed();

        ?>
        <div class="elementor-social-icons-wrapper">
            <?php
            foreach ( $settings['social_icon_list'] as $index => $item ) {
                if ( dokan_is_store_page() ) {
                    if ( empty( $item['social_icon']['value'] ) ) {
                        continue;
                    }

                    if ( is_array( $item['social_icon']['value'] ) ) {
                        continue;
                    }

                    if ( ! isset( $store_social_links[ $item['social_icon']['value'] ] ) ) {
                        if ( ! empty( $item['social'] ) && isset( $store_social_links[ $item['social'] ] ) ) {
                            $item['social_icon']['value'] = $item['social'];
                        } else {
                            continue;
                        }
                    }
                }

                $migrated = isset( $item['__fa4_migrated']['social_icon'] );
                $is_new = empty( $item['social'] ) && $migration_allowed;
                $social = '';

                // add old default
                if ( empty( $item['social'] ) && ! $migration_allowed ) {
                    $item['social'] = isset( $fallback_defaults[ $index ] ) ? $fallback_defaults[ $index ] : 'fa fa-wordpress';
                }

                if ( ! empty( $item['social'] ) ) {
                    $social = str_replace( 'fa fa-', '', $item['social'] );
                }

                if ( ( $is_new || $migrated ) && 'svg' !== $item['social_icon']['library'] ) {
                    $social = explode( ' ', $item['social_icon']['value'], 2 );
                    if ( empty( $social[1] ) ) {
                        $social = '';
                    } else {
                        $social = str_replace( 'fa-', '', $social[1] );
                    }
                }
                if ( 'svg' === $item['social_icon']['library'] ) {
                    $social = '';
                }

                $link_key = 'link_' . $index;

                $link = dokan_is_store_page() ? $store_social_links[ $item['social_icon']['value'] ] : '#';

                $this->add_render_attribute( $link_key, 'href', $link );

                $this->add_render_attribute( $link_key, 'class', [
                    'elementor-icon',
                    'elementor-social-icon',
                    'elementor-social-icon-' . $social . $class_animation,
                    'elementor-repeater-item-' . $item['_id'],
                ] );

                $this->add_render_attribute( $link_key, 'target', '_blank' );
                $this->add_render_attribute( $link_key, 'rel', 'nofollow' );
                ?>
                <a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
                    <span class="elementor-screen-only"><?php echo ucwords( $social ); ?></span>
                    <?php
                    if ( $is_new || $migrated ) {
                        Icons_Manager::render_icon( $item['social_icon'] );
                    } else { ?>
                        <i class="<?php echo esc_attr( $item['social'] ); ?>"></i>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Render widget plain content
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render_plain_content() {}
}
