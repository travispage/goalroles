jQuery( function ( $ ) {


	$( '.jmpack-type-detail-toggle' ).click( function(e){
		e.preventDefault();

		var status = $(this).data('status');
		var type = $(this).data('type');

		var details_row = $(this).closest( 'tr' ).next( '.jmpack-package-details' );
		var type_table = details_row.find( '.jmpack-' + type + '-detail-table' );

		var nonce = 'theWPnonce123456';
		if( status === 'up' ){
			$(this).children( 'i' ).removeClass( 'down' ).addClass( 'up' );
			$(this).data( 'status', 'down' );

			type_table.fadeIn();
			details_row.fadeIn();
		} else {
			$( this ).children( 'i' ).removeClass( 'up' ).addClass( 'down' );
			$( this ).data( 'status', 'up' );

			type_table.fadeOut( 400, function(){

				var total_showing = details_row.find( '.jmpack-detail-table:visible' ).length;

				console.log( 'Total Showing', total_showing );

				if ( total_showing < 1 ) {
					details_row.hide();
				}

			});

		}

	});

});