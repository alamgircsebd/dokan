<?php
$permalink = get_permalink();
$parser = new Dokan_WXR_Parser();

?>

<div class="dokan-dashboard-wrap">
    <?php dokan_get_template( 'dashboard-nav.php', array('active_menu' => 'tools') ); ?>

	<div class="dokan-dashboard-content dokan-withdraw-content">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="dokan-dashboard-header">
			    <h1 class="entry-title"><?php _e( 'Tools', 'dokan' ); ?></h1>
			</header><!-- .entry-header -->

			<div id="tab-container">
				<ul class="dokan_tabs">
				  	<li class="active"><a href="#import" data-toggle="tab"><?php _e( 'Import', 'dpi_plugin' ); ?></a></li>
				  	<li><a href="#export" data-toggle="tab"><?php _e( 'Export', 'dpi_plugin' ); ?></a></li>
				</ul>

				<!-- Tab panes -->
				<div class="tabs_container">
				  	<div class="import_div tab-pane active" id="import">
					  	<header class="entry-header dokan-import-export-header">
					    	<h1 class="entry-title"><?php _e( 'Import', 'dpi_plugin' ); ?></h1>
					    </header>

						<?php

						if( isset( $_POST['import_xml'] ) ) {
							if( empty( $_FILES['import'] ) ) {
								echo __( "Please select a xml file", 'dpi_plugin' );
							}else {
								Dokan_Product_Importer::init()->import( $_FILES['import']['tmp_name'] );
							}
						}

						?>
					    <p><?php _e( 'Click Browse button and choose a XML file that you want to import.', 'dpi_plugin' ); ?></p>
					    <form method='post' enctype='multipart/form-data' action="">
				        	<p><input type='file' name='import' /></p>
				        	<p><input type='submit' name='import_xml' value='<?php _e( 'Import', 'dpi_plugin' ); ?>' class="btn btn-danger" /></p>

					    </form>
				  	</div>
					<div class="export_div tab-pane" id="export">
						<header class="entry-header dokan-import-export-header">
							<h1 class="entry-title"><?php _e( 'Export', 'dpi_plugin' ); ?></h1>
						</header>


						<p><?php _e( 'Chose your type of product and click export button to export all data in XML form', 'dpi_plugin' ); ?></p>

						<form action="" method="POST">
							<p><input type="radio" name="content" value="all" id="export_all" checked="checked"> <label for="export_all"><?php _e( 'All', 'dpi_plugin' ); ?></label></p>
							<p><input type="radio" name="content" value="product" id="export_product"> <label for="export_product"><?php _e( 'Product', 'dpi_plugin' ); ?></label></p>
							<p><input type="radio" name="content" value="product_variation" id="export_variation_product"> <label for="export_variation_product"><?php _e( 'Variation', 'dpi_plugin' ); ?></label></p>
							<p><input type="submit" name="export_xml" value="Export" class="btn btn-danger"></p>
						</form>

					</div>
				</div>
			</div>


		</article>
    </div><!-- .dokan-dashboard-content -->
</div><!-- .dokan-dashboard-wrap -->

<script>
    (function($){
        $(document).ready(function(){
            $('#tab-container').easytabs();
        });
    })(jQuery)
</script>