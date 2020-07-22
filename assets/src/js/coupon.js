// Coupon
(function($) {

    $.validator.setDefaults({ ignore: ":hidden" });

    var validatorError = function(error, element) {
        var form_group = $(element).closest('.form-group');
        form_group.addClass('has-error').append(error);
    };

    var validatorSuccess = function(label, element) {
        $(element).closest('.form-group').removeClass('has-error');
    };

    var Dokan_Coupons = {
        init: function() {
            var self = this;
            this.couponsValidation(self);

            $( 'select#discount_type' )
                .on( 'change', this.type_options )
                .change();
        },

        couponsValidation: function(self) {
            $("form.coupons").validate({
                errorElement: 'span',
                errorClass: 'error',
                errorPlacement: validatorError,
                success: validatorSuccess
            });
        },

        type_options: function() {
            // Get value
            var select_val = $( this ).val();

            if ( select_val.indexOf('percent') >= 0 ) {
                $( '#coupon_amount' ).removeClass( 'wc_input_price' ).addClass( 'wc_input_decimal' );
            } else {
                $( '#coupon_amount' ).removeClass( 'wc_input_decimal' ).addClass( 'wc_input_price' );
            }
        }

    };

    Dokan_Coupons.init();

})(jQuery);
