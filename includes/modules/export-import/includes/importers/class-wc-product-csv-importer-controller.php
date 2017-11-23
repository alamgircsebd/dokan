<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WP_Importer' ) ) {
    return;
}

/**
 * Product importer controller - handles file upload and forms in admin.
 *
 * @author      Automattic
 * @category    Admin
 * @package     WooCommerce/Admin/Importers
 * @version     3.1.0
 */
class WC_Product_CSV_Importer_Controller {

    /**
     * The path to the current file.
     *
     * @var string
     */
    protected $file = '';

    /**
     * The current import step.
     *
     * @var string
     */
    protected $step = '';

    /**
     * Progress steps.
     *
     * @var array
     */
    protected $steps = array();

    /**
     * Errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * The current delimiter for the file being read.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Whether to skip existing products.
     *
     * @var bool
     */
    protected $update_existing = false;

    /**
     * Get importer instance.
     *
     * @param  string $file File to import.
     * @param  array  $args Importer arguments.
     * @return WC_Product_CSV_Importer
     */
    public static function get_importer( $file, $args = array() ) {
        $importer_class = apply_filters( 'woocommerce_product_csv_importer_class', 'WC_Product_CSV_Importer' );
        return new $importer_class( $file, $args );
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->steps           = array(
            'upload'  => array(
                'name'    => __( 'Upload CSV file', 'dpi_plugin' ),
                'view'    => array( $this, 'upload_form' ),
                'handler' => array( $this, 'upload_form_handler' ),
            ),
            'mapping' => array(
                'name'    => __( 'Column mapping', 'dpi_plugin' ),
                'view'    => array( $this, 'mapping_form' ),
                'handler' => '',
            ),
            'import'  => array(
                'name'    => __( 'Import', 'dpi_plugin' ),
                'view'    => array( $this, 'import' ),
                'handler' => '',
            ),
            'done'    => array(
                'name'    => __( 'Done!', 'dpi_plugin' ),
                'view'    => array( $this, 'done' ),
                'handler' => '',
            ),
        );
        $this->step            = isset( $_REQUEST['step'] ) ? sanitize_key( $_REQUEST['step'] ) : current( array_keys( $this->steps ) );
        $this->file            = isset( $_REQUEST['file'] ) ? wc_clean( $_REQUEST['file'] ) : '';
        $this->update_existing = isset( $_REQUEST['update_existing'] ) ? (bool) $_REQUEST['update_existing'] : false;
        $this->delimiter       = !empty( $_REQUEST['delimiter'] ) ? wc_clean( $_REQUEST['delimiter'] ) : ',';
    }

    /**
     * Get the URL for the next step's screen.
     * @param string step   slug (default: current step)
     * @return string       URL for next step if a next step exists.
     *                      Admin URL if it's the last step.
     *                      Empty string on failure.
     */
    public function get_next_step_link( $step = '' ) {
        if ( !$step ) {
            $step = $this->step;
        }

        $keys = array_keys( $this->steps );

        if ( end( $keys ) === $step ) {
            return admin_url();
        }

        $step_index = array_search( $step, $keys );

        if ( false === $step_index ) {
            return '';
        }

        $params = array(
            'step'            => $keys[$step_index + 1],
            'file'            => str_replace( DIRECTORY_SEPARATOR, '/', $this->file ),
            'delimiter'       => $this->delimiter,
            'update_existing' => $this->update_existing,
            '_wpnonce'        => wp_create_nonce( 'woocommerce-csv-importer' ), // wp_nonce_url() escapes & to &amp; breaking redirects.
        );

        return add_query_arg( $params );
    }

    /**
     * Output header view.
     */
    protected function output_header() {
        include( dirname( __FILE__ ) . '/views/html-csv-import-header.php' );
    }

    /**
     * Output steps view.
     */
    protected function output_steps() {
        include( dirname( __FILE__ ) . '/views/html-csv-import-steps.php' );
    }

    /**
     * Output footer view.
     */
    protected function output_footer() {
        include( dirname( __FILE__ ) . '/views/html-csv-import-footer.php' );
    }

    /**
     * Add error message.
     */
    protected function add_error( $error ) {
        $this->errors[] = $error;
    }

    /**
     * Add error message.
     */
    protected function output_errors() {
        if ( $this->errors ) {
            foreach ( $this->errors as $error ) {
                echo '<div class="error inline"><p>' . esc_html( $error ) . '</p></div>';
            }
        }
    }

    /**
     * Dispatch current step and show correct view.
     */
    public function dispatch() {
        if ( !empty( $_POST['save_step'] ) && !empty( $this->steps[$this->step]['handler'] ) ) {
            call_user_func( $this->steps[$this->step]['handler'], $this );
        }
        $this->output_header();
        $this->output_steps();
        $this->output_errors();
        call_user_func( $this->steps[$this->step]['view'], $this );
        $this->output_footer();
    }

    /**
     * Output information about the uploading process.
     */
    protected function upload_form() {
        $bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
        $size       = size_format( $bytes );
        $upload_dir = wp_upload_dir();

        include( dirname( __FILE__ ) . '/views/html-product-csv-import-form.php' );
    }

    /**
     * Handle the upload form and store options.
     */
    public function upload_form_handler() {
        check_admin_referer( 'woocommerce-csv-importer' );

        $file_handler = ABSPATH . '/wp-admin/includes/file.php';

        require $file_handler;

        $file = $this->handle_upload();

        if ( is_wp_error( $file ) ) {
            $this->add_error( $file->get_error_message() );
            return;
        } else {
            $this->file = $file;
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Handles the CSV upload and initial parsing of the file to prepare for
     * displaying author import options.
     *
     * @return string|WP_Error
     */
    public function handle_upload() {
        $valid_filetypes = apply_filters( 'woocommerce_csv_product_import_valid_filetypes', array( 'csv' => 'text/csv', 'txt' => 'text/plain' ) );

        if ( empty( $_POST['file_url'] ) ) {
            if ( !isset( $_FILES['import'] ) ) {
                return new WP_Error( 'woocommerce_product_csv_importer_upload_file_empty', __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.', 'dpi_plugin' ) );
            }

            $filetype = wp_check_filetype( $_FILES['import']['name'], $valid_filetypes );
            if ( !in_array( $filetype['type'], $valid_filetypes ) ) {
                return new WP_Error( 'woocommerce_product_csv_importer_upload_file_invalid', __( 'Invalid file type. The importer supports CSV and TXT file formats.', 'dpi_plugin' ) );
            }

            $overrides = array( 'test_form' => false, 'mimes' => $valid_filetypes );
            $upload    = wp_handle_upload( $_FILES['import'], $overrides );

            if ( isset( $upload['error'] ) ) {
                return new WP_Error( 'woocommerce_product_csv_importer_upload_error', $upload['error'] );
            }

            // Construct the object array.
            $object = array(
                'post_title'     => basename( $upload['file'] ),
                'post_content'   => $upload['url'],
                'post_mime_type' => $upload['type'],
                'guid'           => $upload['url'],
                'context'        => 'import',
                'post_status'    => 'private',
            );

            // Save the data.
            $id = wp_insert_attachment( $object, $upload['file'] );

            /*
             * Schedule a cleanup for one day from now in case of failed
             * import or missing wp_import_cleanup() call.
             */
            wp_schedule_single_event( time() + DAY_IN_SECONDS, 'importer_scheduled_cleanup', array( $id ) );

            return $upload['file'];
        } elseif ( file_exists( ABSPATH . $_POST['file_url'] ) ) {
            $filetype = wp_check_filetype( ABSPATH . $_POST['file_url'], $valid_filetypes );
            if ( !in_array( $filetype['type'], $valid_filetypes ) ) {
                return new WP_Error( 'woocommerce_product_csv_importer_upload_file_invalid', __( 'Invalid file type. The importer supports CSV and TXT file formats.', 'dpi_plugin' ) );
            }

            return ABSPATH . $_POST['file_url'];
        }

        return new WP_Error( 'woocommerce_product_csv_importer_upload_invalid_file', __( 'Please upload or provide the link to a valid CSV file.', 'dpi_plugin' ) );
    }

    /**
     * Mapping step.
     */
    protected function mapping_form() {
        $args = array(
            'lines'     => 1,
            'delimiter' => $this->delimiter,
        );

        $importer     = self::get_importer( $this->file, $args );
        $headers      = $importer->get_raw_keys();
        $mapped_items = $this->auto_map_columns( $headers );
        $sample       = current( $importer->get_raw_data() );

        if ( empty( $sample ) ) {
            $this->add_error( __( 'The file is empty, please try again with a new file.', 'dpi_plugin' ) );
            return;
        }

        include_once( dirname( __FILE__ ) . '/views/html-csv-import-mapping.php' );
    }

    /**
     * Import the file if it exists and is valid.
     */
    public function import() {

        if ( !is_file( $this->file ) ) {
            $this->add_error( __( 'The file does not exist, please try again.', 'dpi_plugin' ) );
            return;
        }

        if ( !empty( $_POST['map_to'] ) ) {
            $mapping_from = wp_unslash( $_POST['map_from'] );
            $mapping_to   = wp_unslash( $_POST['map_to'] );
        } else {
            wp_redirect( esc_url_raw( $this->get_next_step_link( 'upload' ) ) );
            exit;
        }

        wp_localize_script( 'wc-product-import', 'wc_product_import_params', array(
            'import_nonce'    => wp_create_nonce( 'wc-product-import' ),
            'mapping'         => array(
                'from' => $mapping_from,
                'to'   => $mapping_to,
            ),
            'file'            => $this->file,
            'update_existing' => $this->update_existing,
            'delimiter'       => $this->delimiter,
        ) );

        wp_enqueue_script( 'wc-product-import' );

        include_once( dirname( __FILE__ ) . '/views/html-csv-import-progress.php' );
    }

    /**
     * Done step.
     */
    protected function done() {
        $imported = isset( $_GET['products-imported'] ) ? absint( $_GET['products-imported'] ) : 0;
        $updated  = isset( $_GET['products-updated'] ) ? absint( $_GET['products-updated'] ) : 0;
        $failed   = isset( $_GET['products-failed'] ) ? absint( $_GET['products-failed'] ) : 0;
        $skipped  = isset( $_GET['products-skipped'] ) ? absint( $_GET['products-skipped'] ) : 0;
        $errors   = array_filter( (array) get_user_option( 'product_import_error_log' ) );

        include_once( dirname( __FILE__ ) . '/views/html-csv-import-done.php' );
    }

    /**
     * Auto map column names.
     *
     * @param  array $raw_headers Raw header columns.
     * @param  bool  $num_indexes If should use numbers or raw header columns as indexes.
     * @return array
     */
    protected function auto_map_columns( $raw_headers, $num_indexes = true ) {
        $weight_unit    = get_option( 'woocommerce_weight_unit' );
        $dimension_unit = get_option( 'woocommerce_dimension_unit' );

        include( dirname( __FILE__ ) . '/mappings/mappings.php' );

        /**
         * @hooked wc_importer_generic_mappings - 10
         * @hooked wc_importer_wordpress_mappings - 10
         */
        $default_columns = apply_filters( 'woocommerce_csv_product_import_mapping_default_columns', array(
            __( 'ID', 'dpi_plugin' )                                      => 'id',
            __( 'Type', 'dpi_plugin' )                                    => 'type',
            __( 'SKU', 'dpi_plugin' )                                     => 'sku',
            __( 'Name', 'dpi_plugin' )                                    => 'name',
            __( 'Published', 'dpi_plugin' )                               => 'published',
            __( 'Is featured?', 'dpi_plugin' )                            => 'featured',
            __( 'Visibility in catalog', 'dpi_plugin' )                   => 'catalog_visibility',
            __( 'Short description', 'dpi_plugin' )                       => 'short_description',
            __( 'Description', 'dpi_plugin' )                             => 'description',
            __( 'Date sale price starts', 'dpi_plugin' )                  => 'date_on_sale_from',
            __( 'Date sale price ends', 'dpi_plugin' )                    => 'date_on_sale_to',
            __( 'Tax status', 'dpi_plugin' )                              => 'tax_status',
            __( 'Tax class', 'dpi_plugin' )                               => 'tax_class',
            __( 'In stock?', 'dpi_plugin' )                               => 'stock_status',
            __( 'Stock', 'dpi_plugin' )                                   => 'stock_quantity',
            __( 'Backorders allowed?', 'dpi_plugin' )                     => 'backorders',
            __( 'Sold individually?', 'dpi_plugin' )                      => 'sold_individually',
            sprintf( __( 'Weight (%s)', 'dpi_plugin' ), $weight_unit )    => 'weight',
            sprintf( __( 'Length (%s)', 'dpi_plugin' ), $dimension_unit ) => 'length',
            sprintf( __( 'Width (%s)', 'dpi_plugin' ), $dimension_unit )  => 'width',
            sprintf( __( 'Height (%s)', 'dpi_plugin' ), $dimension_unit ) => 'height',
            __( 'Allow customer reviews?', 'dpi_plugin' )                 => 'reviews_allowed',
            __( 'Purchase note', 'dpi_plugin' )                           => 'purchase_note',
            __( 'Sale price', 'dpi_plugin' )                              => 'sale_price',
            __( 'Regular price', 'dpi_plugin' )                           => 'regular_price',
            __( 'Categories', 'dpi_plugin' )                              => 'category_ids',
            __( 'Tags', 'dpi_plugin' )                                    => 'tag_ids',
            __( 'Shipping class', 'dpi_plugin' )                          => 'shipping_class_id',
            __( 'Images', 'dpi_plugin' )                                  => 'images',
            __( 'Download limit', 'dpi_plugin' )                          => 'download_limit',
            __( 'Download expiry days', 'dpi_plugin' )                    => 'download_expiry',
            __( 'Parent', 'dpi_plugin' )                                  => 'parent_id',
            __( 'Upsells', 'dpi_plugin' )                                 => 'upsell_ids',
            __( 'Cross-sells', 'dpi_plugin' )                             => 'cross_sell_ids',
            __( 'Grouped products', 'dpi_plugin' )                        => 'grouped_products',
            __( 'External URL', 'dpi_plugin' )                            => 'product_url',
            __( 'Button text', 'dpi_plugin' )                             => 'button_text',
        ) );

        $special_columns = $this->get_special_columns( apply_filters( 'woocommerce_csv_product_import_mapping_special_columns', array(
            __( 'Attribute %d name', 'dpi_plugin' )     => 'attributes:name',
            __( 'Attribute %d value(s)', 'dpi_plugin' ) => 'attributes:value',
            __( 'Attribute %d visible', 'dpi_plugin' )  => 'attributes:visible',
            __( 'Attribute %d global', 'dpi_plugin' )   => 'attributes:taxonomy',
            __( 'Attribute %d default', 'dpi_plugin' )  => 'attributes:default',
            __( 'Download %d name', 'dpi_plugin' )      => 'downloads:name',
            __( 'Download %d URL', 'dpi_plugin' )       => 'downloads:url',
            __( 'Meta: %s', 'dpi_plugin' )              => 'meta:',
        )
        ) );

        $headers = array();
        foreach ( $raw_headers as $key => $field ) {
            $index             = $num_indexes ? $key : $field;
            $headers[$index] = $field;

            if ( isset( $default_columns[$field] ) ) {
                $headers[$index] = $default_columns[$field];
            } else {
                foreach ( $special_columns as $regex => $special_key ) {
                    if ( preg_match( $regex, $field, $matches ) ) {
                        $headers[$index] = $special_key . $matches[1];
                        break;
                    }
                }
            }
        }

        return apply_filters( 'woocommerce_csv_product_import_mapped_columns', $headers, $raw_headers );
    }

    /**
     * Sanitize special column name regex.
     *
     * @param  string $value Raw special column name.
     * @return string
     */
    protected function sanitize_special_column_name_regex( $value ) {
        return '/' . str_replace( array( '%d', '%s' ), '(.*)', quotemeta( $value ) ) . '/';
    }

    /**
     * Get special columns.
     *
     * @param  array $columns Raw special columns.
     * @return array
     */
    protected function get_special_columns( $columns ) {
        $formatted = array();

        foreach ( $columns as $key => $value ) {
            $regex = $this->sanitize_special_column_name_regex( $key );

            $formatted[$regex] = $value;
        }

        return $formatted;
    }

    /**
     * Get mapping options.
     *
     * @param  string $item Item name
     * @return array
     */
    protected function get_mapping_options( $item = '' ) {
        // Get index for special column names.
        $index = $item;

        if ( preg_match( '/\d+$/', $item, $matches ) ) {
            $index = $matches[0];
        }

        // Properly format for meta field.
        $meta = str_replace( 'meta:', '', $item );

        // Available options.
        $weight_unit    = get_option( 'woocommerce_weight_unit' );
        $dimension_unit = get_option( 'woocommerce_dimension_unit' );
        $options        = array(
            'id'                 => __( 'ID', 'dpi_plugin' ),
            'type'               => __( 'Type', 'dpi_plugin' ),
            'sku'                => __( 'SKU', 'dpi_plugin' ),
            'name'               => __( 'Name', 'dpi_plugin' ),
            'published'          => __( 'Published', 'dpi_plugin' ),
            'featured'           => __( 'Is featured?', 'dpi_plugin' ),
            'catalog_visibility' => __( 'Visibility in catalog', 'dpi_plugin' ),
            'short_description'  => __( 'Short description', 'dpi_plugin' ),
            'description'        => __( 'Description', 'dpi_plugin' ),
            'price'              => array(
                'name'    => __( 'Price', 'dpi_plugin' ),
                'options' => array(
                    'regular_price'     => __( 'Regular price', 'dpi_plugin' ),
                    'sale_price'        => __( 'Sale price', 'dpi_plugin' ),
                    'date_on_sale_from' => __( 'Date sale price starts', 'dpi_plugin' ),
                    'date_on_sale_to'   => __( 'Date sale price ends', 'dpi_plugin' ),
                ),
            ),
            'tax_status'         => __( 'Tax status', 'dpi_plugin' ),
            'tax_class'          => __( 'Tax class', 'dpi_plugin' ),
            'stock_status'       => __( 'In stock?', 'dpi_plugin' ),
            'stock_quantity'     => _x( 'Stock', 'Quantity in stock', 'dpi_plugin' ),
            'backorders'         => __( 'Backorders allowed?', 'dpi_plugin' ),
            'sold_individually'  => __( 'Sold individually?', 'dpi_plugin' ),
            /* translators: %s: weight unit */
            'weight'             => sprintf( __( 'Weight (%s)', 'dpi_plugin' ), $weight_unit ),
            'dimensions'         => array(
                'name'    => __( 'Dimensions', 'dpi_plugin' ),
                'options' => array(
                    /* translators: %s: dimension unit */
                    'length' => sprintf( __( 'Length (%s)', 'dpi_plugin' ), $dimension_unit ),
                    /* translators: %s: dimension unit */
                    'width'  => sprintf( __( 'Width (%s)', 'dpi_plugin' ), $dimension_unit ),
                    /* translators: %s: dimension unit */
                    'height' => sprintf( __( 'Height (%s)', 'dpi_plugin' ), $dimension_unit ),
                ),
            ),
            'category_ids'       => __( 'Categories', 'dpi_plugin' ),
            'tag_ids'            => __( 'Tags', 'dpi_plugin' ),
            'shipping_class_id'  => __( 'Shipping class', 'dpi_plugin' ),
            'images'             => __( 'Images', 'dpi_plugin' ),
            'parent_id'          => __( 'Parent', 'dpi_plugin' ),
            'upsell_ids'         => __( 'Upsells', 'dpi_plugin' ),
            'cross_sell_ids'     => __( 'Cross-sells', 'dpi_plugin' ),
            'grouped_products'   => __( 'Grouped products', 'dpi_plugin' ),
            'external'           => array(
                'name'    => __( 'External product', 'dpi_plugin' ),
                'options' => array(
                    'product_url' => __( 'External URL', 'dpi_plugin' ),
                    'button_text' => __( 'Button text', 'dpi_plugin' ),
                ),
            ),
            'downloads'          => array(
                'name'    => __( 'Downloads', 'dpi_plugin' ),
                'options' => array(
                    'downloads:name' . $index => __( 'Download name', 'dpi_plugin' ),
                    'downloads:url' . $index  => __( 'Download URL', 'dpi_plugin' ),
                    'download_limit'          => __( 'Download limit', 'dpi_plugin' ),
                    'download_expiry'         => __( 'Download expiry days', 'dpi_plugin' ),
                ),
            ),
            'attributes'         => array(
                'name'    => __( 'Attributes', 'dpi_plugin' ),
                'options' => array(
                    'attributes:name' . $index     => __( 'Attribute name', 'dpi_plugin' ),
                    'attributes:value' . $index    => __( 'Attribute value(s)', 'dpi_plugin' ),
                    'attributes:taxonomy' . $index => __( 'Is a global attribute?', 'dpi_plugin' ),
                    'attributes:visible' . $index  => __( 'Attribute visibility', 'dpi_plugin' ),
                    'attributes:default' . $index  => __( 'Default attribute', 'dpi_plugin' ),
                ),
            ),
            'reviews_allowed'    => __( 'Allow customer reviews?', 'dpi_plugin' ),
            'purchase_note'      => __( 'Purchase note', 'dpi_plugin' ),
            'meta:' . $meta      => __( 'Import as meta', 'dpi_plugin' ),
        );

        return apply_filters( 'woocommerce_csv_product_import_mapping_options', $options, $item );
    }

}
