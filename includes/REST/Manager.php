<?php

namespace WeDevs\DokanPro\REST;

class Manager {

    /**
     * Register Dokan Pro REST Controllers
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $class_map
     *
     * @return array
     */
    public static function register_rest_routes( $class_map ) {
        $pro_class_map = [
            DOKAN_PRO_DIR . '/includes/api/class-store-category-controller.php'    => 'Dokan_REST_Store_Category_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-coupon-controller.php'            => 'Dokan_REST_Coupon_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-reports-controller.php'           => 'Dokan_REST_Reports_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-reviews-controller.php'           => 'Dokan_REST_Reviews_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-product-variation-controller.php' => 'Dokan_REST_Product_Variation_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-store-controller.php'             => 'Dokan_Pro_REST_Store_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-modules-controller.php'           => 'Dokan_REST_Modules_Controller',
            DOKAN_PRO_DIR . '/includes/api/class-announcement-controller.php'      => 'Dokan_REST_Announcement_Controller',
            DOKAN_PRO_INC . '/REST/RefundController.php'                           => 'WeDevs\DokanPro\REST\RefundController',
            DOKAN_PRO_DIR . '/includes/api/class-logs-controller.php'              => 'Dokan_REST_Logs_Controller',
        ];

        return array_merge( $class_map, $pro_class_map );
    }
}
