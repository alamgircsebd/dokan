<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;

class ProductReportAbuse extends Widget_Base {

    /**
     * Widget name
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-report-abuse';
    }

    /**
     * Widget title
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Product Report Abuse', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-coding';
    }

    /**
     * Widget categories
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-product-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'report-abuse' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since DOKAN_PRO_SINCE
     * @access protected
     */
    protected function _register_controls() {
        parent::_register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Report Abuse', 'dokan' ),
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Label', 'dokan' ),
                'default'     => __( 'Report Abuse', 'dokan' ),
                'placeholder' => __( 'Report Abuse', 'dokan' ),
            ]
        );

        $this->add_control(
            'icon',
            [
                'label'   => __( 'Icon', 'dokan' ),
                'default' => 'fa fa-flag',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function render() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }
        ?>
        <div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
            <a href="#report-abuse" class="dokan-report-abuse-button">
                <i class="<?php echo $this->get_settings( 'icon' ); ?>"></i> <?php echo $this->get_settings( 'text' ); ?>
            </a>
        </div>
        <?php
    }
}
