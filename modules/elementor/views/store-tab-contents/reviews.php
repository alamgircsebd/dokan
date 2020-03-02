<?php
$store_user             = get_userdata( get_query_var( 'author' ) );
$dokan_template_reviews = dokan_pro()->review;
$id                     = $store_user->ID;
$post_type              = 'product';
$limit                  = 20;
$status                 = '1';
$comments               = $dokan_template_reviews->comment_query( $id, $post_type, $limit, $status );
?>
<div id="reviews">
    <div id="comments">

      <?php do_action( 'dokan_review_tab_before_comments' ); ?>

        <h2 class="headline"><?php _e( 'Vendor Review', 'dokan' ); ?></h2>

        <ol class="commentlist">
            <?php echo $dokan_template_reviews->render_store_tab_comment_list( $comments , $store_user->ID); ?>
        </ol>

    </div>
</div>

<?php echo $dokan_template_reviews->review_pagination( $id, $post_type, $limit, $status ); ?>
