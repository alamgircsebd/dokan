<?php
/**
 * Dokan Dashbaord
 * variation table content
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<script type="text/html" id="tmpl-dokan-variations">
    <# if ( data.variation_item.length ) { #>
        <input type="hidden" name="dokan_create_new_variations" value="yes">
        <table class="dokan-table">
            <thead>
                <tr>
                    <th></th>
                    <th><?php _e( 'Variant', 'dokan' ) ?></th>
                    <th><?php _e( 'Price', 'dokan' ) ?></th>
                    <th><?php _e( 'SKU', 'dokan' ) ?></th>
                </tr>
            </thead>
            <tbody>
            <# _.each( data.variation_item, function( el, i ) { #>
                <tr>
                    <td>
                        <input type="checkbox" name="variable_enabled[{{i}}]" value="yes" checked>
                    </td>
                    <td>
                        {{ el.join(' - ') }}
                        <# _.each( data.variation_title, function( title, index ) { #>
                            <input type="hidden" name="attribute_{{ title.replace(' ','_').toLowerCase() }}[{{i}}]" value="{{el[index].toLowerCase()}}">
                            <input type="hidden" name="variation_menu_order[{{i}}]" value="{{i}}">
                        <# }); #>
                    </td>
                    <td><input type="number" name="variable_regular_price[{{i}}]" placeholder="<?php _e( '0.00', 'dokan' ) ?>" class="dokan-form-control"/ min="0" step="any"></td>
                    <td><input type="text" name="variable_sku[{{i}}]" placeholder="SKU" class="dokan-form-control"/></td>
                </tr>
            <# }); #>
            </tbody>
        </table>
    <# } #>
</script>
