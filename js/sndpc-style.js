(function($){
	$('#wpbody .wrap').wrapInner('<div id="sndpc-col-left" />');
	$('#wpbody .wrap').wrapInner('<div id="sndpc-cols" />');
	$('#sndpc-col-right').removeClass('hidden').prependTo('#sndpc-cols');
	$('#sndpc-cpt-overview').removeClass('hidden').insertBefore('#sndpc-col-left #ajax-response');

	$('#sndpc-col-left > .icon32').insertBefore('#sndpc-cols');
	$('#sndpc-col-left > h2').insertBefore('#sndpc-cols');
})(jQuery);