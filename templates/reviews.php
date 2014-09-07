<?php
/**
 * Template Name: Dashboard - Reviews
 */

// dokan_redirect_login();
// dokan_redirect_if_not_seller();

$dokan_template_reviews = Dokan_Template_reviews::init();
$dokan_template_reviews->handle_status();

// get_header();
// dokan_frontend_dashboard_scripts();
?>

<?php dokan_get_template( dirname(__FILE__) . '/dashboard-nav.php', array( 'active_menu' => 'reviews' ) ); ?>

<div class="dokan-dashboard-content dokan-reviews-content">
    
    <article class="dokan-reviews-area">
        <header class="entry-header">
            <h1 class="entry-title"><?php _e( 'Reviews', 'dokan' ); ?></h1>
        </header><!-- .entry-header -->

        <?php $dokan_template_reviews->reviews_view(); ?>

    </article>

</div><!-- #primary .content-area -->