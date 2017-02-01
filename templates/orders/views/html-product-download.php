<tr>
	<td class="sort"></td>
	<td class="file_name"><input type="text" class="input_text" placeholder="<?php _e( 'File Name', 'dokan-pro' ); ?>" name="_wc_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" /></td>
	<td class="file_url"><input type="text" class="input_text" placeholder="<?php _e( "http://", 'dokan-pro' ); ?>" name="_wc_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" /></td>
	<td class="file_url_choose" width="1%"><a href="#" class="button upload_file_button" data-choose="<?php _e( 'Choose file', 'dokan-pro' ); ?>" data-update="<?php _e( 'Insert file URL', 'dokan-pro' ); ?>"><?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'dokan-pro' ) ); ?></a></td>
	<td width="1%"><a href="#" class="delete"><?php _e( 'Delete', 'dokan-pro' ); ?></a></td>
</tr>