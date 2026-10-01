/**
 * Shared QR code helpers used by the admin screens and the QR Code block.
 *
 * @package Reusable_QR_Codes
 */

( function ( window ) {
	'use strict';

	/**
	 * Build QRCodeStyling options from a plain config.
	 *
	 * @param {Object} cfg Config: data, size, color, bgColor, dotStyle, logoUrl, type.
	 * @return {Object} QRCodeStyling options.
	 */
	function buildOptions( cfg ) {
		var options = {
			width: cfg.size,
			height: cfg.size,
			type: cfg.type || 'canvas',
			margin: 10,
			data: cfg.data,
			dotsOptions: {
				color: cfg.color,
				type: cfg.dotStyle
			},
			backgroundOptions: {
				color: cfg.bgColor
			}
		};

		if ( cfg.logoUrl ) {
			options.image = cfg.logoUrl;
			options.imageOptions = {
				hideBackgroundDots: true,
				imageSize: 0.35,
				margin: 4,
				crossOrigin: 'anonymous'
			};
			options.qrOptions = {
				errorCorrectionLevel: 'H'
			};
		}

		return options;
	}

	/**
	 * Sanitize a title into a download filename.
	 *
	 * @param {string} filename Original title, possibly HTML-entity-encoded.
	 * @return {string} Sanitized filename.
	 */
	function sanitizeFilename( filename ) {
		if ( ! filename || filename === '' ) {
			return 'qr-code';
		}

		// Titles arrive HTML-entity-encoded (e.g. &#8217;), so decode them first.
		var decoder = document.createElement( 'textarea' );
		decoder.innerHTML = filename;

		// Strip accents, remove special characters and replace spaces with hyphens.
		var slug = decoder.value
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' )
			.toLowerCase()
			.replace( /[^a-z0-9\s-]/g, '' )
			.replace( /\s+/g, '-' )
			.replace( /-+/g, '-' )
			.substring( 0, 50 );

		return slug || 'qr-code';
	}

	/**
	 * Download a high-resolution QR code.
	 *
	 * @param {Object} cfg       Config as for buildOptions().
	 * @param {string} extension 'png' or 'svg'.
	 * @param {string} name      Title used for the filename.
	 */
	function download( cfg, extension, name ) {
		var options = buildOptions( Object.assign( {}, cfg, {
			size: 1024,
			type: extension === 'svg' ? 'svg' : 'canvas'
		} ) );

		new window.QRCodeStyling( options ).download( {
			name: sanitizeFilename( name ),
			extension: extension
		} );
	}

	window.rqrcQr = {
		buildOptions: buildOptions,
		sanitizeFilename: sanitizeFilename,
		download: download
	};
} )( window );
