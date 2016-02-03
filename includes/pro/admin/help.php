<div class="wrap">
    <h1><?php _e( 'Dokan Help', 'dokan' ); ?> <a href="http://docs.wedevs.com/docs/dokan/" target="_blank" class="page-title-action"><?php _e( 'View all Documentations', 'dokan' ); ?></a></h1>

    <?php
    $help_docs = get_transient( 'dokan_help_docs' );

    if ( false === $help_docs ) {
        $help_url  = 'https://api.bitbucket.org/2.0/snippets/wedevs/oErMz/files/dokan-help.json';
        $response  = wp_remote_get( $help_url, array('timeout' => 15) );
        $help_docs = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            $help_docs = '[]';
        }

        set_transient( 'dokan_help_docs', $help_docs, 12 * HOUR_IN_SECONDS );
    }

    $help_docs    = json_decode( $help_docs );
    $sections     = count( $help_docs );

    if ( $sections ) {
        $left_column  = array_slice( $help_docs, 0, $sections / 2);
        $right_column = array_slice( $help_docs, $sections / 2);
        ?>

        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container">
                    <div class="meta-box-sortables">

                        <?php
                        if ( $left_column ) {
                            foreach ($left_column as $section) {
                                ?>

                                <div class="postbox">
                                    <h2 class="hndle"><?php echo esc_html( $section->title ); ?></h2>

                                    <?php if ( $section->questions ) { ?>
                                        <div class="dokan-help-questions">
                                            <ul>
                                                <?php foreach ($section->questions as $question) { ?>
                                                    <li><a href="<?php echo esc_url( $question->link ); ?>" target="_blank"><?php echo esc_html( $question->title ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="postbox-container">
                    <div class="meta-box-sortables">

                        <?php
                        if ( $right_column ) {
                            foreach ($right_column as $section) {
                                ?>

                                <div class="postbox">
                                    <h2 class="hndle"><?php echo esc_html( $section->title ); ?></h2>

                                    <?php if ( $section->questions ) { ?>
                                        <div class="dokan-help-questions">
                                            <ul>
                                                <?php foreach ($section->questions as $question) { ?>
                                                    <li><a href="<?php echo esc_url( $question->link ); ?>" target="_blank"><?php echo esc_html( $question->title ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>

        <div class="updated error">
            <p><?php printf( __( '<strong>Error:</strong> No help documents found, or error fetching the documents. Please refresh the page again or <a href="%s" target="_blank">let us know</a> about the problem.', 'dokan' ), 'https://wedevs.com/contact/' ); ?></p>
        </div>

    <?php } ?>
</div>

<style type="text/css" media="screen">
.dokan-help-questions li {
    margin: 0;
    border-bottom: 1px solid #eee;
}

.dokan-help-questions li a {
    padding: 10px 15px;
    display: block;
}

.dokan-help-questions li a:hover {
    background-color: #F5F5F5;
}

.dokan-help-questions li:last-child {
    border-bottom: none;
}

.dokan-help-questions li .dashicons {
    float: right;
    color: #ccc;
    margin-top: -3px;
}
</style>
