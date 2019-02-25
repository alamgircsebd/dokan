<?php

namespace DokanPro\Brands;

use Dokan\Traits\Singleton;

class Brands {

    use Singleton;

    /**
     * Is this feature active or not
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var bool
     */
    public $is_active = false;

    /**
     * Feature related admin settings
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    public $settings = [];

    /**
     * Executes after first class instantiation
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function boot() {
        add_filter( 'dokan_get_class_container', [ $this, 'add_dokan_container' ] );
        add_action( 'yith_wcbr_init', [ $this, 'init' ], 11 );
    }

    /**
     * Initiate functionalities
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function init() {
        $this->is_active = true;

        $this->settings = [
            'mode' => dokan_get_option( 'product_brands_mode', 'dokan_selling', 'single' ),
        ];

        $this->includes();
        $this->instances();
    }

    /**
     * Add `brands` container to WeDevs_Dokan class
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $container
     *
     * @return array
     */
    public function add_dokan_container( $container ) {
        $container['brands'] = Brands::instance();
        return $container;
    }

    /**
     * Include feature related files
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function includes() {
        require_once DOKAN_PRO_INC . '/brands/class-dokan-brands-admin-settings.php';
        require_once DOKAN_PRO_INC . '/brands/class-dokan-brands-form-fields.php';
    }

    /**
     * Feature related class instances
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    protected function instances() {
        new \DokanPro\Brands\AdminSettings();
        new \DokanPro\Brands\FormFields();
    }
}

Brands::instance();
