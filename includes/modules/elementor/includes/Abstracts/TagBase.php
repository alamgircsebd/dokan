<?php

namespace DokanPro\Modules\Elementor\Abstracts;

use DokanPro\Modules\Elementor\Module;
use Elementor\Core\DynamicTags\Tag;

abstract class TagBase extends Tag {

    /**
     * Tag group
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_group() {
        return Module::DOKAN_GROUP;
    }

    /**
     * Tag categories
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_categories() {
        return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
    }
}
