<?php
/**
 * Dokan Review Underscores Script Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<script type="text/template" id="dokan-edit-comment-row">
    <tr class="dokan-comment-edit-row">
        <td colspan="5">
            <table>
                <tr class="dokan-comment-edit-contact">
                    <td>
                        <label for="author"><?php _e( 'Name', 'dokan' ); ?></label>
                        <input type="text" class="dokan-cmt-author" value="<%= author %>" name="newcomment_author">
                    </td>
                    <td>
                        <label for="author-email"><?php _e( 'E-mail', 'dokan' ); ?></label>
                        <input type="text" class="dokan-cmt-author-email" value="<%= email %>" name="newcomment_author_email">
                    </td>
                    <td>
                        <label for="author-url"><?php _e( 'URL', 'dokan' ); ?></label>
                        <input type="text" class="dokan-cmt-author-url" value="<%= url %>" name="newcomment_author_url">
                    </td>
                </tr>
                <tr class="dokan-comment-edit-body">
                    <td colspan="3">
                        <textarea class="dokan-cmt-body" name="newcomment_body" cols="50" rows="8"><%= body %></textarea>
                        <input type="hidden" class="dokan-cmt-id" value="<%= id %>" >
                        <input type="hidden" class="dokan-cmt-status" value="<%= status %>" >
                        <input type="hidden" class="dokan-cmt-post-type" value="product">
                    </td>
                </tr>
                <tr class="dokan-comment-edit-actions">
                    <td colspan="3">
                        <button class="dokan-cmt-close-form btn btn-theme"><?php _e( 'Close', 'dokan' ); ?></button>
                        <button class="dokan-cmt-submit-form btn btn-theme"><?php _e( 'Update Comment', 'dokan' ); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</script>
