jQuery( function($){

	setTimeout(function(){

		var jmpack_woonotice = $( '.jmpack-woonotice' );

		if( jmpack_woonotice ){

			var wc_message = jmpack_woonotice.closest( '.woocommerce-message' );

			if( wc_message ){

				setTimeout( function(){
					wc_message.fadeOut();
				}, 5000);

			}
		}

	}, 2000 );

});