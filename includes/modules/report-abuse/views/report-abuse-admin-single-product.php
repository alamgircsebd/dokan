<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Reason', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Reported by', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Reported at', 'dokan' ); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php if ( empty( $reports ) ): ?>
            <tr>
                <td colspan="3">
                    <?php esc_html_e( 'This product has no abuse report', 'dokan' ); ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach( $reports as $report ): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html( $report['reason'] ); ?></strong>
                        <?php if ( $report['description'] ): ?>
                            <p><em><small><?php echo esc_html( $report['description'] ); ?></small></em></p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $report['reported_by']['admin_url'] ): ?>
                            <a href="<?php echo esc_url( $report['reported_by']['admin_url'] ); ?>" target="_blank">
                                <?php echo esc_html( $report['reported_by']['name'] ); ?>
                            </a>
                        <?php else: ?>
                            <?php echo esc_html( $report['reported_by']['name'] ); ?> &lt;<?php echo esc_html( $report['reported_by']['email'] ); ?>&gt;
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            echo date( $date_format . ' ' . $time_format, strtotime( $report['reported_at'] ) );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>

    <tfoot>
        <tr>
            <th><?php esc_html_e( 'Reason', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Reported by', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Reported at', 'dokan' ); ?></th>
        </tr>
    </tfoot>
</table>
