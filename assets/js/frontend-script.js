// Store Listing JS
(function($){
    $(document).ready(function(){
        var form = $('.dokan-seller-search-form');
        var timer = null;

        form.on('keyup', '#search', function() {
            var self = $(this),
                data = {
                    search_term: self.val(),
                    pagination_base: form.find('#pagination_base').val(),
                    action: 'dokan_seller_listing_search',
                    _wpnonce: form.find('#nonce').val()
                };

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function() {
                form.find('.dokan-overlay').show();

                $.post(dokan_plugin.ajaxurl, data, function(response) {
                    if (response.success) {
                        form.find('.dokan-overlay').hide();

                        var data = response.data;
                        $('#dokan-seller-listing-wrap').html(data);
                    }
                });
            }, 500);
        } );
    });
})(jQuery);