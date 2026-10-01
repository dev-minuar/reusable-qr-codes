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

	var logoLoads = {};

	/**
	 * Load a logo URL once. Resolves true when it loads, false on error or after 8 s.
	 *
	 * @param {string} url Logo URL.
	 * @return {Promise<boolean>} Load result.
	 */
	function loadLogo( url ) {
		if ( ! logoLoads[ url ] ) {
			logoLoads[ url ] = new Promise( function ( done ) {
				var img = new Image();
				var timer = setTimeout( function () {
					done( false );
				}, 8000 );
				img.crossOrigin = 'anonymous';
				img.onload = function () {
					clearTimeout( timer );
					done( true );
				};
				img.onerror = function () {
					clearTimeout( timer );
					done( false );
				};
				img.src = url;
			} );
		}
		return logoLoads[ url ];
	}

	/**
	 * Check that the logo loads. Without a working logo, return a config without it.
	 *
	 * @param {Object} cfg Config as for buildOptions().
	 * @return {Promise<Object>} Config that is safe to draw.
	 */
	function resolve( cfg ) {
		if ( ! cfg.logoUrl ) {
			return Promise.resolve( cfg );
		}
		return loadLogo( cfg.logoUrl ).then( function ( ok ) {
			return ok ? cfg : Object.assign( {}, cfg, { logoUrl: '' } );
		} );
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
		resolve( cfg ).then( function ( resolved ) {
			var options = buildOptions( Object.assign( {}, resolved, {
				size: 1024,
				type: extension === 'svg' ? 'svg' : 'canvas'
			} ) );

			new window.QRCodeStyling( options ).download( {
				name: sanitizeFilename( name ),
				extension: extension
			} );
		} );
	}

	/**
	 * Draw a QR code into a container, replacing its content.
	 *
	 * A newer call on the same container cancels an older one that is still loading.
	 *
	 * @param {Object}      cfg       Config as for buildOptions().
	 * @param {HTMLElement} container Element that receives the canvas.
	 * @return {Promise} Resolves once the code is drawn or superseded.
	 */
	function draw( cfg, container ) {
		var token = ( container.rqrcDrawToken || 0 ) + 1;
		container.rqrcDrawToken = token;

		return resolve( cfg ).then( function ( resolved ) {
			if ( container.rqrcDrawToken !== token ) {
				return;
			}
			container.innerHTML = '';
			new window.QRCodeStyling( buildOptions( resolved ) ).append( container );
		} );
	}

	window.rqrcQr = {
		buildOptions: buildOptions,
		sanitizeFilename: sanitizeFilename,
		resolve: resolve,
		draw: draw,
		download: download
	};
} )( window );
