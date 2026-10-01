/**
 * QR Code Generator for Admin
 *
 * @package Reusable_QR_Codes
 */

(function($) {
	'use strict';

	/**
	 * Generate QR code when document is ready.
	 */
	$(document).ready(function() {
		// Check if we have the container.
		if ($('#rqrc-qrcode').length === 0) {
			return;
		}

		// Don't generate if there's a placeholder (unpublished post).
		if ($('#rqrc-qrcode').find('.rqrc-placeholder').length > 0) {
			return;
		}

		// Check if we have the data.
		if (typeof rqrcData === 'undefined') {
			return;
		}

		generateQRCode();
	});

	/**
	 * Generate and display QR code.
	 */
	function generateQRCode() {
		// Clear existing QR code.
		$('#rqrc-qrcode').empty();

		// QR code options for display (using canvas/PNG for better sizing).
		var displayOptions = {
			width: rqrcData.qrSize,
			height: rqrcData.qrSize,
			type: 'canvas',
			margin: 10,
			data: rqrcData.permalink,
			dotsOptions: {
				color: rqrcData.qrColor,
				type: rqrcData.qrDotStyle
			},
			backgroundOptions: {
				color: rqrcData.qrBgColor
			}
		};

		// Create QR code for display.
		var qrCodeDisplay = new QRCodeStyling(displayOptions);
		qrCodeDisplay.append(document.getElementById('rqrc-qrcode'));

		// Setup download handlers.
		setupDownloadHandlers(displayOptions);
	}

	/**
	 * Setup download button handlers.
	 *
	 * @param {Object} baseOptions Base QR code options.
	 */
	function setupDownloadHandlers(baseOptions) {
		// PNG download (high resolution).
		$('#rqrc-download-png').off('click').on('click', function(e) {
			e.preventDefault();

			var qrCodePNG = new QRCodeStyling({
				...baseOptions,
				width: 1024,
				height: 1024,
				type: 'canvas'
			});

			qrCodePNG.download({
				name: sanitizeFilename(rqrcData.title),
				extension: 'png'
			});
		});

		// SVG download (vector).
		$('#rqrc-download-svg').off('click').on('click', function(e) {
			e.preventDefault();

			var qrCodeSVG = new QRCodeStyling({
				...baseOptions,
				width: 1024,
				height: 1024,
				type: 'svg'
			});

			qrCodeSVG.download({
				name: sanitizeFilename(rqrcData.title),
				extension: 'svg'
			});
		});
	}

	/**
	 * Sanitize filename for download.
	 *
	 * @param {string} filename Original filename.
	 * @return {string} Sanitized filename.
	 */
	function sanitizeFilename(filename) {
		if (!filename || filename === '') {
			return 'qr-code';
		}

		// Titles arrive HTML-entity-encoded (e.g. &#8217;), so decode them first.
		var decoder = document.createElement('textarea');
		decoder.innerHTML = filename;

		// Strip accents, remove special characters and replace spaces with hyphens.
		var slug = decoder.value
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.toLowerCase()
			.replace(/[^a-z0-9\s-]/g, '')
			.replace(/\s+/g, '-')
			.replace(/-+/g, '-')
			.substring(0, 50);

		return slug || 'qr-code';
	}

})(jQuery);
