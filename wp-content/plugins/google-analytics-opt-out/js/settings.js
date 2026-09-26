(function () {
	"use strict";
	jQuery( document ).ready( function () {

		var $yoast  = jQuery( '#gaoop_options_yoast' ),
		    $banner = jQuery( '#gaoop_options_banner' );

		jQuery( '#gaoop_options_property' ).focus();

		if ( $yoast.is( ':checked' ) ) {
			jQuery( '.form-table tr:eq(1)' ).hide();
		} else {
			jQuery( '.form-table tr:eq(1)' ).show();
		}

		$yoast.click( function () {
			if ( jQuery( this ).is( ':checked' ) ) {
				jQuery( '.form-table tr:eq(1)' ).hide();
			} else {
				jQuery( '.form-table tr:eq(1)' ).show();
				jQuery( '#gaoop_options_property' ).focus();
			}
		} );

		function show_hide_banner_fields() {
			var use_banner = $banner.is( ':checked' );

			var $tr  = $banner.closest( 'tr' );
			var $trs = $tr.nextAll();

			if ( use_banner ) {
				$trs.show();
			} else {
				$trs.hide();
			}
		}

		show_hide_banner_fields();

		$banner.click( function () {
			show_hide_banner_fields();
		} );

	} );
})( jQuery );
