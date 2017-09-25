jQuery(document).ready(function($) {
	$('.job-manager-chosen-select').chosen();

	$('#widget-get-code').click(function(){
		var keywords   = $('#widget_keyword').val();
		var location   = $('#widget_location').val();
		var per_page   = $('#widget_per_page').val();
		var pagination = $('#widget_pagination').is(':checked') ? 1 : 0;
		var categories = $('#widget_categories').val();
		var job_types  = $('#widget_job_type').val();

		if ( categories ) {
			categories = categories.join();
		} else {
			categories = '';
		}

		if ( job_types ) {
			job_types = job_types.join();
		} else {
			job_types = '';
		}

		var embed_code = "<script type='text/javascript'>\n\
	var embeddable_job_widget_options = {\n\
		'script_url' : '" + embeddable_job_widget_form_args.script_url + "',\n\
		'keywords'   : '" + escape( keywords ) + "',\n\
		'location'   : '" + escape( location ) + "',\n\
		'categories' : '" + categories + "',\n\
		'job_types'  : '" + job_types + "',\n\
		'per_page'   : '" + parseInt( per_page ) + "',\n\
		'pagination' : '" + parseInt( pagination ) + "'\n\
	};\n\
</script>\n" + embeddable_job_widget_form_args.css + "\n" + embeddable_job_widget_form_args.code;

		$('#widget-code').val( embed_code ).focus().select();
		$('#widget-code-preview iframe').remove();
		var iframe = document.createElement('iframe');
		var html   = '<!doctype html><html><head></head><body style="margin:0; padding: 0;">' + embed_code + '</body></html>';
		$('#widget-code-preview').append( iframe );
		iframe.contentWindow.document.open();
		iframe.contentWindow.document.write( html );
		iframe.contentWindow.document.close();
		$('#widget-code-wrapper').slideDown();
	});
});