jQuery( function ( $ ) {

	function wpjmpack_cb_show( show ){

		if( ! show ){
			return;
		}


		if ( show.indexOf( ' ' ) > -1 ) {
			var show_a = show.split( ' ' );

			console.log( 'showing array ..', show_a );

			$.each( show_a, function ( key, value ) {
				$( '.' + value ).show();
			} );

		} else {
			console.log( 'showing ..', show );
			$( '.' + show ).show();

		}

	}

	function wpjmpack_cb_hide( hide ){

		if( ! hide ){
			return;
		}

		if ( hide.indexOf( ' ' ) > -1 ) {
			var hide_a = hide.split( ' ' );

			console.log( 'hiding array ..', hide_a );

			$.each( hide_a, function ( key, value ) {
				$( '.' + value ).hide();
			} );
		} else {
			console.log( 'hiding ..', hide );
			$( '.' + hide ).hide();
		}

	}

	function wpjmpack_cb_dynamic( elem ) {

		console.log( 'cb dynamic checking ', elem );

		var cshow = elem.data( 'cshow' );
		var chide = elem.data( 'chide' );
		var uchide = elem.data( 'uchide' );
		var ucshow = elem.data( 'ucshow' );
		var related = elem.data( 'related' );
		var check = elem.data( 'check' );

		console.log( 'cshow', cshow, 'chide', chide, 'uchide', uchide, 'ucshow', ucshow, 'related', related );
		
		var is_visible = elem.is( ':visible' );

		if ( elem.is( ':checked' ) ) {

			console.log( 'elem is checked ...', elem );

			if( cshow ){
				wpjmpack_cb_show( cshow );
			}

			if( chide ){
				wpjmpack_cb_hide( chide );
			}

			if( related && is_visible ){

				wpjmpack_cb_show( related );

				// Check other fields after showing related fields
				if ( check ) {
					// Other sub-fields to check
					$( '.' + check ).each( function ( index, value ) {

						if( $( this ).is( ':visible') ) {
							wpjmpack_cb_dynamic( $( this ) );
						}
					} );

				}
			}

		} else {

			console.log( 'elem not checked ...', elem );

			if ( ucshow && is_visible ) {
				wpjmpack_cb_show( ucshow );
			}

			if ( uchide ) {
				wpjmpack_cb_hide( uchide );
			}

			if( related ){
				wpjmpack_cb_hide( related );
			}
		}
	}

	var cb_dynamic = $( '.wpjmpack-cb_dynamic' );

	// Check root checkboxes on click
	cb_dynamic.click( function ( e ) {
		wpjmpack_cb_dynamic( $( this ) );
	});

	// Check root checkboxes when page is first loaded
	cb_dynamic.each( function ( index, value ) {
		wpjmpack_cb_dynamic( $( this ) );
	});

	// Prevent checkboxes required to be checked, from being unchecked
	// This shouldn't be necessary, but left here just in case (to prevent users from unchecking during submit)
	var redirect_cbs = $( '.wpjmpack-redirect-disable-uncheck' );
	redirect_cbs.on( 'click', function( e ){
		var checkbox = $( this );
		if( ! checkbox.is( ':checked' ) ){
			e.preventDefault();
			return false;
		}
	});

	// Remove disabled attribute from required checkboxes before form is submitted
	$( 'form' ).submit( function () {
		$( '.wpjmpack-redirect-disable-uncheck' ).removeAttr( "disabled" );
	});
} );