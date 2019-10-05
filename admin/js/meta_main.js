var frame;
;(function(jQuery){
	$(document).ready(function(){
		$('.MetaBox_dateUI').datepicker({
			changeMonth: true,
	    	changeYear: true
		});
		$('#MetaBox_image').on('click',function(){
			frame = wp.media({
				title: 'Select Images',
				button:{
					text:'Insert Images'
				},
				multiple: false

			});
			frame.open();
			return false;
		})
	});
})(jQuery);