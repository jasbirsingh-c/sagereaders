jQuery(document).ready(function() {
	jQuery('input[name=pods_meta_media_type]').click(function() {
		if(jQuery(this).val() == 0)
		{
			jQuery('.pods-form-ui-row-name-video').show();
			jQuery('.pods-form-ui-row-name-audio').hide();
		}
		else
		{
			jQuery('.pods-form-ui-row-name-video').hide();
			jQuery('.pods-form-ui-row-name-audio').show();			
		}
	});
	
	if(jQuery('#pods-form-ui-pods-meta-media-type1').is(':checked'))
	{
		jQuery('.pods-form-ui-row-name-video').hide();
		jQuery('.pods-form-ui-row-name-audio').show();		
	}
	else if(jQuery('#pods-form-ui-pods-meta-media-type2').is(':checked'))
	{
		jQuery('.pods-form-ui-row-name-video').show();
		jQuery('.pods-form-ui-row-name-audio').hide();		
	}		
});