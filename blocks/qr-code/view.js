/**
 * QR Code block: front end.
 *
 * @package Reusable_QR_Codes
 */

( function () {
	'use strict';

	/**
	 * Draw every QR Code block on the page and wire its download button.
	 */
	function init() {
		var boxes = document.querySelectorAll( '.rqrc-block-qr[data-rqrc]' );

		Array.prototype.forEach.call( boxes, function ( box ) {
			var cfg;
			try {
				cfg = JSON.parse( box.getAttribute( 'data-rqrc' ) );
			} catch ( e ) {
				return;
			}

			box.innerHTML = '';
			new window.QRCodeStyling( window.rqrcQr.buildOptions( cfg ) ).append( box );

			var figure = box.closest( 'figure' );
			var button = figure ? figure.querySelector( '.rqrc-block-download' ) : null;
			if ( button ) {
				button.addEventListener( 'click', function () {
					window.rqrcQr.download( cfg, 'png', cfg.name );
				} );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
