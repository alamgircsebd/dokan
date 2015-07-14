<?php
/**
 * Dokan Review Status Filter Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<div id="dokan-comments_menu">
    <ul class="subsubsub list-inline">
        <li><a href="<?php echo $url; ?>"><?php _e( 'All', 'dokan' ); ?></a></li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'hold' ), $url ); ?>"><?php _e( 'Pending (', 'dokan' ); ?><span class="comments-menu-pending"><?php echo $pending; ?></span><?php _e( ')', 'dokan' ); ?></a>
        </li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'spam' ), $url ); ?>"><?php _e( 'Spam (', 'dokan' ); ?><span class="comments-menu-spam"><?php echo $spam; ?></span><?php _e( ')', 'dokan' ); ?></a>
        </li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'trash' ), $url ); ?>"><?php _e( 'Trash (', 'dokan' ); ?><span class="comments-menu-trash"><?php echo $trash; ?></span><?php _e( ')', 'dokan' ); ?></a>
        </li>
    </ul>
</div>
