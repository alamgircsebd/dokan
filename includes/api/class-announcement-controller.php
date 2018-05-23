<?php

/**
* Announcement Controller class
*/
class Dokan_REST_Announcement_Controller extends Dokan_REST_Controller {

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
    protected $base = 'announcement';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_type = 'dokan_announcement';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_status = array( 'publish' );

    /**
     * Register all announcement route
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_announcements' ),
                'args'                => array_merge( $this->get_collection_params(),  array(
                    'status' => array(
                        'type'        => 'string',
                        'description' => __( 'Announcement status', 'dokan' ),
                        'required'    => false,
                    ),
                ) ),
                'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_announcement' ),
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => array( $this, 'create_announcement_permissions_check' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan-lite' ),
                    'type'        => 'integer',
                ),
            ),

            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_announcement' ),
                'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
            ),

            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_announcement' ),
                'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
            ),

            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_withdraw' ),
                'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
            ),

        ) );

    }

    /**
     * Get single object
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_object( $id ) {
        return get_post( $id );
    }

    /**
     * Get all announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcements( $request ) {
        $status = ! empty( $request['status'] ) ? $request['status'] : 'any';

        $limit = $request['per_page'];
        $offset = ( $request['page'] - 1 ) * $request['page'];

        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => $limit,
            'paged'          => $offset,
            'post_status'    => $status
        );

        $query = new WP_Query( $args );

        $data = array();
        if ( $query->posts ) {
            foreach ( $query->posts as $key => $value ) {
                $resp   = $this->prepare_response_for_object( $value, $request );
                $data[] = $this->prepare_response_for_collection( $resp );
            }
        }

        $response = rest_ensure_response( $data );

        $response = $this->format_collection_response( $response, $request, $query->found_posts );
        return $response;
    }

    /**
     * Get single announcement object
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcement( y$request ) {
        $announcement_id = $request['id'];

        if ( empty( $announcement_id ) ) {
            return new WP_Error( 'no_announcement_found', __( 'No announcement found', 'dokan-lite' ), array( 'status' => 404 ) );
        }

        $data     = $this->prepare_response_for_object( $this->get_object( $announcement_id ), $requesty );
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Create announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function create_announcement( $request ) {

        if (  empty( trim( $request['title'] ) ) ) {
            return new WP_Error( 'no_title', __( 'Announcement title mus be required', 'dokan-lite' ), array( 'status' => 404 ) );
        }

        $status = !empty( $request['status'] ) ? $request['status'] : 'pending';

        $data = array(
            'title'        => sanitize_text_field( $request['title'] ),
            'content'      => sanitize_textarea_field( $request['content'] );,
            'status'       => $status,
        );

        $post_id = wp_insert_post( $data );

        if (  is_wp_error( $post_id ) ) {
            return new WP_Error( $post_id->get_error_message() );
        }

        update_post_meta( $post_id, '_announcement_type', $request['sender_type'] );
        update_post_meta( $post_id, '_announcement_selected_user', $request['sender_type'] );

    }

    /**
     * get_announcement_permissions_check
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcement_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * create_announcement_permissions_check
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function create_announcement_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Prepare data for response
     *
     * @since 2.8.0
     *
     * @return data
     */
    public function prepare_response_for_object( $object, $request ) {
        $methods = dokan_withdraw_get_methods();
        $data = array(
            'id'           => $object->ID,
            'title'        => $object->post_title,
            'content'      => $object->post_content,
            'status'       => $object->post_status,
            'created_at'   => mysql_to_rfc3339( $object->post_date ),
            'sender_type'  => get_post_meta( $object->ID, '_announcement_type', true ),
            'sender_ids'   => get_post_meta( $object->ID, '_announcement_selected_user', true )
        );

        $response      = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $object, $request ) );

        return apply_filters( 'dokan_rest_prepare_announcement_object', $response, $object, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param WC_Data         $object  Object data.
     * @param WP_REST_Request $request Request object.
     *
     * @return array                   Links for the given post.
     */
    protected function prepare_links( $object, $request ) {
        $links = array(
            'self' => array(
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->ID ) ),
            ),
            'collection' => array(
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
            ),
        );

        return $links;
    }



}