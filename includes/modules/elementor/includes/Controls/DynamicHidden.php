<?php

namespace DokanPro\Modules\Elementor\Controls;

use Elementor\Control_Hidden;

class DynamicHidden extends Control_Hidden {

    /**
     * Control type
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var string
     */
    const CONTROL_TYPE = 'dynamic_hidden';

    /**
     * Get repeater control type.
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_type() {
        return self::CONTROL_TYPE;
    }

    /**
     * Get default settings for the control
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    protected function get_default_settings() {
        $default_settings = parent::get_default_settings();

        $default_settings['dynamic'] = [];

        return $default_settings;
    }
}
