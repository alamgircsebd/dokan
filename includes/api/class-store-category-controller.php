<?php

class Dokan_REST_Store_Category_Controller extends WP_REST_Terms_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'store-categories';

    /**
     * Taxonomy key.
     *
     * @since 4.7.0
     * @var string
     */
    protected $taxonomy = 'store_category';

    public function __construct() {
        parent::__construct( $this->taxonomy );
        $this->namespace = 'dokan/v1';
        $this->rest_base = $this->base;
    }
}

