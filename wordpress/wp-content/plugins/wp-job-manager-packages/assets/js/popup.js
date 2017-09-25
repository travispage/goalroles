jQuery( function($){
	
	function jmpack_set_popup_callback(){

		// Handle updating listing_id in links or forms (if necessary)
		jmpack_popupdata.onCreate = function( e ){

			var hover_elem = $( e );
			var popup = $( this );
			var listing_id = hover_elem.data( 'listing_id' );

			console.log( 'popup created', hover_elem );

			// If valid listing_id in popover data, means it's probably a list page
			if( listing_id && listing_id > 0 ){

				// Update selection form with correct listing ID from hover_elem data value
				$( '#job_package_selection' ).on( 'submit', function ( e ) {
					$( '#job_manager_packages_form_listing_id' ).val( listing_id );
				});

				// Update any links in popover with correct listing ID
				popup.find('a').each(function(){

					var link_val = $( this ).attr( 'href' );

					if( link_val ){
						// Set updated href value with alisting_id=$listing_id&listing_id=
						var updated_link_val = link_val.replace( 'listing_id=', 'listing_id=' + listing_id + '&old_id=' );
						// Set new href value
						$( this ).attr( 'href', updated_link_val );
					}

				});
			}
		};

		// As long as we have valid data, setup popover/popup
		if ( jmpack_popupdata && jmpack_popupdata.html ) {
			$( '.jmpack-popup' ).popup( jmpack_popupdata );
		}
	}

	// Update popover and callbacks when resume page is updated
	$( 'div.resumes' ).on( 'updated_results', function ( event, page, append ) {
		console.log( 'resume results updated', event, page, append );
		jmpack_set_popup_callback();
	});

	// Initialize on first page load
	jmpack_set_popup_callback();
});