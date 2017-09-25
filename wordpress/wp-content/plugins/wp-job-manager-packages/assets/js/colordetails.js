jQuery( function($){
	var table_color = $( '.jmpack-my-packages-main-table' ).css( 'border-top-color' );
	if ( ! table_color ) { table_color = '#d8d8d8'; }
	$( '.jmpack-package-details' ).css( 'background-color', table_color );
});