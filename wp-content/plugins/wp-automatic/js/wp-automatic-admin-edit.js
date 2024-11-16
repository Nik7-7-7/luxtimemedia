//Add duplicate button
jQuery(document).ready(function(){
	
	jQuery('.edit a').each(function(index,val){
		
		//get post id 
		var pid = jQuery(this).attr('href').split('post=')[1];

		//remove the extra part 88893&action=edit&classic-editor
		pid = pid.split('&')[0];

		//get the nonce value from the hidden field wp_automatic_nonce_88893
		var nonce = jQuery('input[name="wp_automatic_nonce_'+pid+'"]').val();
 
		//getting the id
		jQuery(this).parent().after('<span class="edit wp-automatic-duplicate"><a data-nonce="' + nonce + '" href="' + jQuery(this).attr('href') +  '" title="Duplicate this item">Duplicate</a> | </span>');
		
	});
	
	//click duplicate button
	jQuery('.row-actions').on('click', '.wp-automatic-duplicate a', function() { 
		
		console.log(jQuery(this).parent().parent());
		
		
		//getting input
		var campName = prompt("New Campaign Name", jQuery(this).parent().parent().parent().find('.row-title').text() + "-Copy");
		if (campName != null) {
			
			jQuery(this).parent().parent().append('<div class="spinner-duplicate spinner is-active"></div>');
 
			//get the nonce value from the hidden field 
			//<input type="hidden" name="wp_automatic_nonce_88893" value="840b8bfec1">
			var nonce =


			jQuery.ajax({
				url : ajaxurl,
				type : 'POST',

				data : {
					action : 'wp_automatic_campaign_duplicate',
					href : jQuery(this).attr('href'),
					campName: campName,
					nonce: jQuery(this).data('nonce')

				},
				
				success: function(data){
					
					jQuery('.spinner-duplicate').remove();
					
					alert(data);
				} 
			});

			
		}

		
		
		return false;
		
	}); 
	
});

