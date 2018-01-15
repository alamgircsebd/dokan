<?php
$permalink = get_permalink();
$parser = new Dokan_WXR_Parser();

?>

<div class="dokan-dashboard-wrap">
	<?php

        /**
         *  dokan_dashboard_content_before hook
         *  dokan_tools_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
        do_action( 'dokan_tools_content_before' );
    ?>

	<div class="dokan-dashboard-content dokan-withdraw-content">
		<?php

            /**
             *  dokan_tools_content_inside_before hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_tools_content_inside_before' );
        ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="dokan-dashboard-header">
			    <h1 class="entry-title"><?php _e( 'Tools', 'dokan' ); ?></h1>
			</header><!-- .entry-header -->

			<div id="tab-container">
				<ul class="dokan_tabs">
                    <?php if ( current_user_can( 'dokan_import_product' ) ): ?>
				  	     <li class="active"><a href="#import" data-toggle="tab"><?php _e( 'Import', 'dokan' ); ?></a></li>
                    <?php endif ?>

                    <?php if ( current_user_can( 'dokan_export_product' ) ): ?>
				  	    <li><a href="#export" data-toggle="tab"><?php _e( 'Export', 'dokan' ); ?></a></li>
                    <?php endif ?>
				</ul>

				<!-- Tab panes -->
				<div class="tabs_container">
                    <?php if ( current_user_can( 'dokan_import_product' ) ): ?>
    				  	<div class="import_div tab-pane active" id="import">
    					  	<header class="entry-header dokan-import-export-header">
    					    	<h1 class="entry-title"><?php _e( 'Import', 'dokan' ); ?></h1>
    					    </header>

    						<?php

    						if( isset( $_POST['import_xml'] ) ) {
    							if( empty( $_FILES['import'] ) ) {
    								echo __( "Please select a xml file", 'dokan' );
    							}else {
    								Dokan_Product_Importer::init()->import( $_FILES['import']['tmp_name'] );
    							}
    						}

    						?>
    					    <p><?php _e( 'Click Browse button and choose a XML file that you want to import.', 'dokan' ); ?></p>
    					    <form method='post' enctype='multipart/form-data' action="">
    				        	<p><input type='file' name='import' /></p>
    				        	<p><input type='submit' name='import_xml' value='<?php _e( 'Import', 'dokan' ); ?>' class="btn btn-danger" /></p>

    					    </form>
    				  	</div>
                    <?php endif ?>
                    <?php if ( current_user_can( 'dokan_export_product' ) ): ?>
    					<div class="export_div tab-pane" id="export">
    						<header class="entry-header dokan-import-export-header">
    							<h1 class="entry-title"><?php _e( 'Export', 'dokan' ); ?></h1>
    						</header>


    						<p><?php _e( 'Chose your type of product and click export button to export all data in XML form', 'dokan' ); ?></p>

    						<form action="" method="POST">
    							<p><input type="radio" name="content" value="all" id="export_all" checked="checked"> <label for="export_all"><?php _e( 'All', 'dokan' ); ?></label></p>
    							<p><input type="radio" name="content" value="product" id="export_product"> <label for="export_product"><?php _e( 'Product', 'dokan' ); ?></label></p>
    							<p><input type="radio" name="content" value="product_variation" id="export_variation_product"> <label for="export_variation_product"><?php _e( 'Variation', 'dokan' ); ?></label></p>
    							<p><input type="submit" name="export_xml" value="Export" class="btn btn-danger"></p>
    						</form>

    					</div>
                    <?php endif ?>

				</div>
			</div>

		</article>

		<?php

            /**
             *  dokan_tools_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_tools_content_inside_after' );
        ?>

    </div><!-- .dokan-dashboard-content -->

	 <?php
        /**
         *  dokan_dashboard_content_after hook
         *  dokan_tools_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
        do_action( 'dokan_tools_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->

<script>
    (function($){
        $(document).ready(function(){
            $('#tab-container').easytabs();
        });
    })(jQuery)
</script>