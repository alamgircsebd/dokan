;(function($) {

	$('#myTab a').on('click',function(){
		console.log(this);
		$(this).tab('show');
		localStorage.setItem("ie_active", $(this).attr('href') )
	})

	$('#myTab a[href='+localStorage.ie_active+']').tab('show');

})(jQuery);
