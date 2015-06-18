<?php
/**
 * Show users all comments and process all bulk action
 *
 * @author Asaquzzaman
 */

class Dokan_Pro_Reviews {

    private $limit = 15;
    private $pending;
    private $spam;
    private $trash;
    private $post_type;

    /**
     * Load autometically when class inistantiate
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->quick_edit = ( dokan_get_option( 'review_edit', 'dokan_selling', 'off' ) == 'on' ) ? true : false;

        add_filter( 'dokan_get_seller_dashboard_nav', array( $this, 'add_review_menu' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_review_template' ) );

        add_action( 'dokan_review_content_area_header', array( $this, 'dokan_review_header_render' ), 10 );
        add_action( 'dokan_review_content', array( $this, 'dokan_review_content_render' ), 10 );
        add_action( 'dokan_review_content_status_filter', array( $this, 'dokan_review_status_filter' ), 10, 2);
        add_action( 'dokan_review_content_listing', array( $this, 'dokan_review_content_listing' ), 10, 2 );
        add_action( 'dokan_review_listing_table_body', array( $this, 'dokan_render_listing_table_body' ), 10 );
        add_action( 'dokan_review_content_inside_after', array( $this, 'dokan_render_listing_table_script_template' ), 10 );
        add_action( 'template_redirect', array( $this, 'handle_status' ), 10 );
    }

    /**
     * Inistantiate the Dokan_Pro_Coupons class
     *
     * @since 2.4
     *
     * @return object
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new Dokan_Pro_Reviews();
        }

        return $instance;
    }

    /**
     * Add Review menu
     *
     * @param array $urls
     *
     * @since 2.4
     *
     * @return array $urls
     */
    public function add_review_menu( $urls ) {

        $urls['reviews'] = array(
            'title' => __( 'Reviews', 'dokan'),
            'icon'  => '<i class="fa fa-comments-o"></i>',
            'url'   => dokan_get_navigation_url( 'reviews' ),
            'pos'   => 75
        );

        return $urls;
    }

    /**
     * Load Review template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void [require once template]
     */
    public function load_review_template( $query_vars ) {

        if ( isset( $query_vars['reviews'] ) ) {
            dokan_get_template_part( 'review/reviews', '', array( 'pro'=>true ) );
            return;
        }
    }

    /**
     * Render Review Template Header content
     *
     * @since 2.4
     *
     * @return void
     */
    public function dokan_review_header_render() {
        dokan_get_template_part( 'review/header', '', array( 'pro' => true ) );
    }

    public function dokan_review_content_render() {
        $this->reviews_view();
    }

    /**
     * Hanlde Ajax Comment Status
     *
     * @since 2.4
     *
     * @return josn
     */
    function ajax_comment_status() {

        if ( ! wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) && !is_user_logged_in() ) {
            wp_send_json_error();
        }

        $comment_id  = $_POST['comment_id'];
        $action      = $_POST['comment_status'];
        $post_type   = $_POST['post_type'];
        $page_status = $_POST['curr_page'];

        if ( $action == 'delete' && isset( $comment_id ) ) {
            wp_delete_comment( $comment_id );
        }

        if ( isset( $comment_id ) && isset( $action ) ) {
            wp_set_comment_status( $comment_id, $action );
        }

        $comment = get_comment( $comment_id );

        $this->get_count( $post_type );

        ob_start();
        $this->render_row( $comment, $post_type  );
        $html = array(
            'pending' => $this->pending,
            'spam'    => $this->spam,
            'trash'   => $this->trash,
            'content' => ob_get_clean()
        );

        wp_send_json_success( $html );
    }

    /**
     * Reviews View
     *
     * Reviews Comments this shortcode activation function
     *
     * @since 2.4
     *
     * @return void
     */
    function reviews_view() {

        global $wpdb, $current_user;

        if ( is_user_logged_in() ) {

            // initialize
            $this->post_type = 'product';
            $post_type       = 'product';

            $counts        = dokan_count_comments( $post_type, $current_user->ID );
            $this->pending = $counts->moderated;
            $this->spam    = $counts->spam;
            $this->trash   = $counts->trash;

            dokan_get_template_part( 'review/content', '', array(
                'pro' => true,
                'post_type' => $post_type,
                'counts' => $counts
            ) );
        }
    }

    /**
     * Dokan Render Review Status filter
     *
     * @since 2.4
     *
     * @param  string $post_type
     * @param  object $counts
     *
     * @return void
     */
    public function dokan_review_status_filter( $post_type, $counts ) {
        $this->review_comments_menu( $post_type, $counts );
    }

    /**
     * Render Review Listing content
     *
     * @since 2.4
     *
     * @param  string $post_type
     * @param  object $counts
     *
     * @return void
     */
    public function dokan_review_content_listing( $post_type, $counts ) {
        $this->show_comment_table( $post_type, $counts );
    }

    /**
     * Show all comments in this form
     *
     * @since 2.4
     *
     * @param string  $post_type
     *
     * @return void
     */
    function show_comment_table( $post_type, $counts ) {
        global $current_user;

        $comment_status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : 'all';

        dokan_get_template_part( 'review/listing', '', array(
            'pro' => true,
            'post_type' => $post_type,
            'comment_status' => $comment_status,
        ) );
        echo $this->pagination( $post_type );
    }

    /**
     * Render Reviews Edit underscores template
     *
     * @since 2,4
     *
     * @return void
     */
    public function dokan_render_listing_table_script_template() {
        dokan_get_template_part( 'review/tmpl-review-script', '', array(
            'pro' => true,
        ) );
    }

    /**
     * Pagination
     *
     * @since 2.4
     *
     * @param string  $post_type
     *
     * @return string
     */
    function pagination( $post_type ) {
        global $wpdb, $current_user;

        $status = $this->page_status();

        if ( $status == '1' ) {
            $query = "$wpdb->comments.comment_approved IN ('1','0') AND";
        } else {
            $query = "$wpdb->comments.comment_approved='$status' AND";
        }

        $total = $wpdb->get_var(
            "SELECT COUNT(*)
            FROM $wpdb->comments, $wpdb->posts
            WHERE   $wpdb->posts.post_author='$current_user->ID' AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
            $query
            $wpdb->posts.post_type='$post_type'"
        );

        $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $num_of_pages = ceil( $total / $this->limit );
        $base_url = dokan_get_navigation_url( 'reviews' );

        $page_links = paginate_links( array(
                'base'      => $base_url. '%_%',
                'format'    => '?pagenum=%#%',
                'add_args'  => false,
                'prev_text' => __( '&laquo;', 'aag' ),
                'next_text' => __( '&raquo;', 'aag' ),
                'total'     => $num_of_pages,
                'type'      => 'array',
                'current'   => $pagenum
            ) );

        if ( $page_links ) {
            $pagination_links  = '<div class="pagination-wrap">';
            $pagination_links .= '<ul class="pagination"><li>';
            $pagination_links .= join( "</li>\n\t<li>", $page_links );
            $pagination_links .= "</li>\n</ul>\n";
            $pagination_links .= '</div>';

            return $pagination_links;
        }
    }


    /**
     * Review Pagination
     *
     * @since 2.4
     *
     * @param int     $id
     * @param string  $post_type
     * @param int     $limit
     * @param string  $status
     *
     * @return string
     */
    function review_pagination( $id, $post_type, $limit, $status ) {
        global $wpdb;
        // $status = $this->page_status();

        if ( $status == '1' ) {
            $query = "$wpdb->comments.comment_approved IN ('1','0') AND";
        } else {
            $query = "$wpdb->comments.comment_approved='$status' AND";
        }

        $total = $wpdb->get_var(
            "SELECT COUNT(*)
            FROM $wpdb->comments, $wpdb->posts
            WHERE   $wpdb->posts.post_author='$id' AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
            $query
            $wpdb->posts.post_type='$post_type'"
        );

        $pagenum = max( get_query_var( 'paged' ), 1 );
        $num_of_pages = ceil( $total / $limit );

        $page_links = paginate_links( array(
            'base'      => dokan_get_store_url( $id ) . 'reviews/%_%',
            'format'    => 'page/%#%',
            'prev_text' => __( '&laquo;', 'aag' ),
            'next_text' => __( '&raquo;', 'aag' ),
            'total'     => $num_of_pages,
            'type'      => 'array',
            'current'   => $pagenum
        ) );

        if ( $page_links ) {
            $pagination_links  = '<div class="pagination-wrap">';
            $pagination_links .= '<ul class="pagination"><li>';
            $pagination_links .= join( "</li>\n\t<li>", $page_links );
            $pagination_links .= "</li>\n</ul>\n";
            $pagination_links .= '</div>';

            return $pagination_links;
        }
    }

    /**
     * Render Page status
     *
     * Return current page status.
     * Is it panding, spam, trash or all
     *
     * @since 2.4
     *
     * @return string
     */
    public function page_status() {
        $status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';

        if ( $status == 'hold' ) {
            return '0';
        } else if ( $status == 'spam' ) {
                return 'spam';
            } else if ( $status == 'trash' ) {
                return 'trash';
            } else {
            return '1';
        }
    }

    /**
     * Get Comment Status
     *
     * @since 2.4
     *
     * @param  string $status
     *
     * @return string
     */
    function get_comment_status( $status ) {
        switch ( $status ) {
        case '1':
            return 'approved';

        case '0':
            return 'pending';

        default:
            return $status;
        }
    }

    /**
     * Return all comments by comments status
     *
     * @since 2.4
     *
     * @global object $current_user
     * @global object $wpdb
     *
     * @param string  $post_type
     *
     * @return string
     */
    function dokan_render_listing_table_body( $post_type ) {
        global $current_user;

        $status   = $this->page_status();
        $limit    = $this->limit;
        $comments = $this->comment_query( $current_user->ID, $post_type, $limit, $status );

        dokan_get_template_part( 'review/listing-table-body', '', array(
            'pro' => true,
            'comments' => $comments,
            'post_type' => $post_type
        ) );
    }

    /**
     * Comment Query
     *
     * @since 2.4
     *
     * @param  integer $id
     * @param  string $post_type
     * @param  integer $limit
     * @param  string $status
     *
     * @return object
     */
    function comment_query( $id, $post_type, $limit, $status ) {
        global $wpdb;

        $page_number = isset( $_GET['pagenum'] ) ? $_GET['pagenum'] : 0 ;
        $pagenum     = max( 1, $page_number );
        $offset      = ( $pagenum - 1 ) * $limit;

        if ( $status == '1' ) {
            $query = "c.comment_approved IN ('1','0') AND";
        } else {
            $query = "c.comment_approved='$status' AND";
        }

        $comments = $wpdb->get_results(
            "SELECT c.comment_content, c.comment_ID, c.comment_author,
                c.comment_author_email, c.comment_author_url,
                p.post_title, c.user_id, c.comment_post_ID, c.comment_approved,
                c.comment_date
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author='$id' AND
                p.post_status='publish' AND
                c.comment_post_ID=p.ID AND
                $query
                p.post_type='$post_type'  ORDER BY c.comment_ID DESC
            LIMIT $offset,$limit"
        );

        return $comments;
    }

    /**
     * Render Comments Row
     *
     * @since 2.4
     *
     * @param  object $comment
     * @param  string $post_type
     *
     * @return void
     */
    public function render_row( $comment, $post_type ) {

        $comment_date       = get_comment_date( 'Y/m/d \a\t g:i a', $comment->comment_ID );
        $comment_author_img = get_avatar( $comment->comment_author_email, 32 );
        $eidt_post_url      = get_edit_post_link( $comment->comment_post_ID );
        $permalink          = get_comment_link( $comment );
        $comment_status     =  $this->get_comment_status( $comment->comment_approved );

        $page_status = $this->page_status();

        dokan_get_template_part( 'review/listing-table-tr', '', array(
            'pro' => true,
            'comment' => $comment,
            'comment_date' => $comment_date,
            'comment_author_img' => $comment_author_img,
            'eidt_post_url' => $eidt_post_url,
            'permalink' => $permalink,
            'page_status' => $page_status,
            'post_type' => $post_type,
            'comment_status' => $comment_status,
            'quick_edit' => $this->quick_edit,
        ) );
    }

    /**
     * Manupulate comment row actions
     *
     * @since 2.4
     *
     * @param  object $comment
     * @param  string $post_type
     *
     * @return void
     */
    function row_action( $comment, $post_type ) {

    }

    /**
     * Update Comment via Ajax
     *
     * @since 2.4
     *
     * @return josn
     */
    function ajax_update_comment() {

        if ( ! $this->quick_edit ) {
            wp_send_json_error( __( 'You can not edit reviews!', 'dokan' ) );
        }

        if ( !wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) ) {
            wp_send_json_error();
        }



        $comment_id = absint( $_POST['comment_id'] );
        $commentarr = array(
            'comment_ID'           => $comment_id,
            'comment_content'      => $_POST['content'],
            'comment_author'       => $_POST['author'],
            'comment_author_email' => $_POST['email'],
            'comment_author_url'   => $_POST['url'],
            'comment_approved'     => $_POST['status'],
        );

        wp_update_comment( $commentarr );
        $comment = get_comment( $comment_id );

        ob_start();
        $this->render_row( $comment, $_POST['post_type'] );
        $html = ob_get_clean();

        wp_send_json_success( $html );
    }

    /**
     * Process bulk action
     *
     * @since 2.4
     */
    function handle_status() {

        if ( !isset( $_POST['comt_stat_sub'] ) ) {
            return;
        }

        if ( !wp_verify_nonce( $_POST['dokan_comment_nonce'], 'dokan_comment_nonce_action' ) && !is_user_logged_in() ) {
            return;
        }

        $action = $_POST['comment_status'];

        if ( !count( $_POST['commentid'] ) ) {
            return;
        }

        foreach ( $_POST['commentid'] as $commentid ) {
            if ( $action == 'delete' ) {
                wp_delete_comment( $commentid );
            } else {
                wp_set_comment_status( $commentid, $action );
            }
        }

        $current_status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';
        $redirect_to = add_query_arg( array( 'comment_status' => $current_status ), dokan_get_navigation_url('reviews') );
        wp_redirect( $redirect_to );

    }

    /**
     * Show Comment filter menu
     *
     * @since 2.4
     *
     * @param string  $post_type
     *
     * @return void
     */
    function review_comments_menu( $post_type, $counts ) {
        $url     = dokan_get_navigation_url( 'reviews' );
        $pending = isset( $counts->moderated ) ? $counts->moderated : 0;
        $spam    = isset( $counts->spam ) ? $counts->spam : 0;
        $trash   = isset( $counts->trash ) ? $counts->trash : 0;

        dokan_get_template_part( 'review/status-filter', '', array(
            'pro' => true,
            'url' => $url,
            'pending' => $pending,
            'spam' => $spam,
            'trash' => $trash,
        ) );
    }

    /**
     * Count all, pending, spam, trash Comments
     *
     * @since 2.4
     *
     * @param string $post_type
     * @param string $status
     *
     * @return object
     */
    function count_status( $post_type, $status ) {
        global $wpdb, $current_user;

        return $totalcomments = $wpdb->get_var(
            "SELECT count($wpdb->comments.comment_ID)
            FROM $wpdb->comments, $wpdb->posts
            WHERE $wpdb->posts.post_author=$current_user->ID AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=wp_posts.ID AND
            $wpdb->comments.comment_approved='$status' AND
            $wpdb->posts.post_type='$post_type'"
        );
    }

}
