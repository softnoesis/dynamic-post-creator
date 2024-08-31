(function($){
	var file_frame;
	
	jQuery('.media-uploader-button').click(function(e) {
		e.preventDefault();

		if ( file_frame ) {
			file_frame.open();
			return;
		}

		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Add Icon',
			button: {
				text: 'Select icon',
			},
			multiple: false
		});

		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();
			console.log(attachment);
			if ( attachment ) {

				var url;

				if ( attachment.sizes.sndpc_icon ) {
					url = attachment.sizes.sndpc_icon.url;
				} else {
					url = attachment.url;
				}
				jQuery('#sndpc_icon_url').val( url );
				jQuery('.current-sndpc-icon').html('<img src="'+url+'" height="16" width="16" />');
				jQuery('.remove-sndpc-icon').show();
				jQuery('.media-uploader-button').html('Edit icon');

			}
		});

		file_frame.open();
		return false;
	});

	jQuery('.remove-sndpc-icon').click(function(e) {
		e.preventDefault();
		jQuery('#sndpc_icon_url').val('');
		jQuery('.current-sndpc-icon').html('');
		jQuery('.remove-sndpc-icon').hide();
		jQuery('.media-uploader-button').html('Add icon');
	});
})(jQuery);
