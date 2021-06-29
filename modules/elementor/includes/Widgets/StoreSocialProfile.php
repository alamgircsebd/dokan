<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
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
            'social',
            [
                'label'       => __( 'Icon', 'dokan' ),
                'type'        => Controls_Manager::ICON,
                'label_block' => true,
                'default'     => 'fa fa-wordpress',
                'include'     => [
                    'fa fa-facebook',
                    'fa fa-twitter',
                    'fa fa-pinterest',
                    'fa fa-linkedin',
                    'fa fa-youtube',
                    'fa fa-instagram',
                    'fa fa-flickr',
                    'fa fa-wordpress',
                ],
            ],
        );

        $repeater->add_control(
            'link',
            [
                'label'       => __( 'Link', 'dokan' ),
                'type'        => Controls_Manager::URL,
                'dynamic'     => [
                    'default' => '',
                    'active'  => true,
                ],
                'label_block' => true,
                'default'     => [
                    'is_external' => 'true',
                ],
                'placeholder' => __( 'https://your-link.com', 'dokan' ),
            ]
        );

        $this->update_control(
            'social_icon_list',
            [
                'fields'  => $repeater->get_controls(),
                'item_actions' => [
                    'add'       => false,
                    'duplicate' => false,
                ],
                'default' => [
                    [
                        'social_icon' => [
                            'value' => 'fa fa-facebook',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-facebook',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-twitter',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-twitter',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-pinterest',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-pinterest',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-linkedin',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-linkedin',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-youtube',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-youtube',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-instagram',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-instagram',
                    ],
                    [
                        'social_icon' => [
                            'value' => 'fa fa-flickr',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fa fa-flickr',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'box_shadow',
                'selector' => '{{WRAPPER}} .elementor-social-icon',
            ],
            [
                'position' => [ 'of' => 'icon_spacing' ],
            ]
        );

        $this->add_control(
            'store_social_links',
            [
                'type'    => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-social-profile-tag' ),
                    'active'  => true,
                ],
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

        $class_animation = '';

        if ( ! empty( $settings['hover_animation'] ) ) {
            $class_animation = ' elementor-animation-' . $settings['hover_animation'];
        }
        ?>
        <div class="elementor-social-icons-wrapper">
            <?php
            foreach ( $settings['social_icon_list'] as $index => $item ) {
                if ( dokan_is_store_page() && empty( $store_social_links[ $item['social'] ] ) ) {
                    continue;
                }

                $social = str_replace( 'fa fa-', '', $item['social'] );

                $link_key = 'link_' . $index;

                $this->add_render_attribute( $link_key, 'href', $store_social_links[ $item['social'] ] );

                if ( $item['link']['is_external'] ) {
                    $this->add_render_attribute( $link_key, 'target', '_blank' );
                }

                if ( $item['link']['nofollow'] ) {
                    $this->add_render_attribute( $link_key, 'rel', 'nofollow' );
                }
                ?>
                <a class="elementor-icon elementor-social-icon elementor-social-icon-<?php echo $social . $class_animation; ?>" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
                    <span class="elementor-screen-only"><?php echo ucwords( $social ); ?></span>
                    <i class="<?php echo $item['social']; ?>"></i>
                </a>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Elementor builder content template
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function _content_template() {
        parent::_content_template();
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
