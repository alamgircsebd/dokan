// Store Listing JS
(function($){
    $(document).ready(function(){
        var timer = null;
        $('.dokan-seller-search-form').on('keyup', '#search', function() {
            var self = $(this),
                data = {
                    search_term: self.val(),
                    pagination_base: $('#pagination_base').val(),
                    action: 'dokan_seller_listing_search',
                    _wpnonce: $('#nonce').val()
                };

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function() {
                $.post(dokan_plugin.ajaxurl, data, function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#dokan-seller-listing-wrap').html(data);
                    }
                });
            }, 500);
        } );
    });
})(jQuery);