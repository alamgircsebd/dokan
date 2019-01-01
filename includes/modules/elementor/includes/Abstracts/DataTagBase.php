<?php

namespace DokanPro\Modules\Elementor\Abstracts;

use DokanPro\Modules\Elementor\Module;
use Elementor\Core\DynamicTags\Data_Tag;

abstract class DataTagBase extends Data_Tag {

    /**
     * Tag group
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public function get_group() {
        return Module::DOKAN_GROUP;
    }

    /**
     * Tag categories
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return array
     */
    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }
}
