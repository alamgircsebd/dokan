<?php

use WeDevs\Dokan\Abstracts\DokanRESTAdminController;

/**
 * REST API Modules controller
 *
 * Handles requests to the /admin/modules endpoint.
 *
 * @author   Dokan
 * @category API
 * @package  Dokan/API
 * @since    2.8
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Dokan_REST_Modules_Controller extends DokanRESTAdminController {

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'modules';

    /**
     * Register all routes related with modules
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/activate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'activate_modules' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                =>  $this->module_toggle_request_args(),
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/deactivate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'deactivate_modules' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                =>  $this->module_toggle_request_args(),
            ]
        ] );
    }

    /**
     * Activation/deactivation request args
     *
     * @return array
     */
    public function module_toggle_request_args() {
        return [
            'module' => [
                'description'       => __( 'Basename of the module as array', 'dokan' ),
                'required'          => true,
                'type'              => 'array',
                'validate_callback' => [ $this, 'validate_modules' ],
                'items'             => [
                    'type' => 'string'
                ]
            ],
        ];
    }

    /**
     * Validate module ids
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $modules
     *
     * @return bool|\WP_Error
     */
    public function validate_modules( $modules ) {
        if ( ! is_array( $modules ) ) {
            return new WP_Error( 'dokan_pro_rest_error', __( 'module parameter must be an array of id of Dokan Pro modules.', 'dokan' ) );
        }

        if ( empty( $modules ) ) {
            return new WP_Error( 'dokan_pro_rest_error', 'module parameter is empty', 'dokan' );
        }

        $available_modules = dokan_pro()->module->get_available_modules();

        foreach ( $modules as $module ) {
            if ( ! in_array( $module, $available_modules ) ) {
                return new WP_Error( 'dokan_pro_rest_error', sprintf( __( '%s module is not available in your system.', 'dokan' ), $module ) );
            }
        }

        return true;
    }

    /**
     * Get all modules
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        $data             = [];
        $modules          = dokan_pro()->module->get_all_modules();
        $activate_modules = dokan_pro()->module->get_active_modules();

        foreach ( $modules as $module ) {
            $data[] = [
                'id'           => $module['id'],
                'name'         => $module['name'],
                'description'  => $module['description'],
                'thumbnail'    => $module['thumbnail'],
                'plan'         => $module['plan'],
                'active'       => in_array( $module['id'], $activate_modules ),
                'available'    => file_exists( $module['module_file'] )
            ];
        }

        $response = rest_ensure_response( $data );

        $dokan_pro_current_plan = dokan_pro()->get_plan();
        $dokan_pro_plans        = json_encode( dokan_pro()->get_dokan_pro_plans() );

        $response->header( 'X-DokanPro-Current-Plan', $dokan_pro_current_plan );
        $response->header( 'X-DokanPro-Plans', $dokan_pro_plans );

        return $response;
    }

    /**
     * Activate modules
     *
     * @param  WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function activate_modules( $request ) {
        $modules        = $request['module'];
        dokan_pro()->module->activate_modules( $modules );
        dokan_pro()->module->set_modules( [] );

        return $this->get_items( $request );
    }

    /**
     * Deactivate modules
     *
     * @param  WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function deactivate_modules( $request ) {
        $modules = $request['module'];
        dokan_pro()->module->deactivate_modules( $modules );
        dokan_pro()->module->set_modules( [] );

        return $this->get_items( $request );
    }
}
