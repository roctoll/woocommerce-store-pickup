jQuery(document).ready(function($) {
	
	if( $('#_wc_booking_store_pickup').attr('checked') ){
		$( '.bookings_store_pickup_tab' ).show();
	}
	$('#_wc_booking_store_pickup').change(function() {
		if ( $(this).is( ':checked' ) ) {
			$( '.bookings_store_pickup_tab' ).show();
		} else {
			$( '.bookings_store_pickup_tab' ).hide();
		}

		$('ul.wc-tabs li:visible').eq(0).find('a').click();
	});
});
