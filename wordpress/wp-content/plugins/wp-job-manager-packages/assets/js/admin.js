jQuery( function ( $ ) {

	$( '#package_type' ).change( function ( e ) {

		var type = $( 'option:selected', this ).data( 'type' );
		console.log( this.value, type );

		if ( type === 'job' ) {
			$( '.resume-type' ).hide();
			$( '.job-type-allow' ).show();
			$( '.job-type-allow-input' ).trigger( 'change' );
		} else {
			$( '.job-type' ).hide();
			$( '.resume-type-allow' ).show();
			$( '.resume-type-allow-input' ).trigger( 'change' );
			$( '.resume-type-limit' ).trigger( 'change' );
		}

	} );

	$( '.job-type-allow-input' ).change( function () {
		var that = $( this );
		var type = that.data( 'type' );

		if ( that.is( ':checked' ) ) {
			$( '.job-type-' + type ).show();
			$( '.job-type-limit-input' ).trigger( 'change' );
		} else {
			$( '.job-type-' + type ).hide();
		}

	} );

	$( '.resume-type-allow-input' ).change( function () {
		var that = $( this );
		var type = that.data( 'type' );

		if ( that.is( ':checked' ) ) {
			$( '.resume-type-' + type ).show();
			$( '.resume-type-limit-input' ).trigger( 'change' );
		} else {
			$( '.resume-type-' + type ).hide();
		}

	} );

	$( '.job-type-limit-input' ).change( function () {
		console.log( this.value );
		var type = $( this ).data( 'type' );

		if ( this.value > 0 ) {
			$( '.job-type-' + type + '-used' ).show();
			$( '.job-type-' + type + '-posts' ).show();
		} else {
			$( '.job-type-' + type + '-used' ).hide();
			$( '.job-type-' + type + '-posts' ).hide();
		}

	} );

	$( '.resume-type-limit-input' ).change( function () {
		console.log( $(this), this.value, $(this).val() );
		var type = $( this ).data( 'type' );

		if ( this.value > 0 ) {
			$( '.resume-type-' + type + '-used' ).show();
			$( '.resume-type-' + type + '-posts' ).show();
		} else {
			$( '.resume-type-' + type + '-used' ).hide();
			$( '.resume-type-' + type + '-posts' ).hide();
		}

	} );

	$( '#package-add-form' ).submit( function () {

		var pkg_type = $( '#package_type' ).find( ':selected' ).data( 'type' );

		if ( pkg_type === 'job' ) {
			$( '.resume-type' ).remove();
		} else {
			$( '.job-type' ).remove();
		}

	} );

	setTimeout( function () {
		$( '#package_type' ).trigger( 'change' );
	}, 200 );
} );