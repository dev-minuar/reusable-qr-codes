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
		setupDownloadHandlers();
	});

	/**
	 * Current QR config for the preview and downloads.
	 *
	 * @return {Object} Config for rqrcQr.buildOptions().
	 */
	function currentConfig() {
		return {
			data: rqrcData.permalink,
			size: rqrcData.qrSize,
			color: rqrcData.qrColor,
			bgColor: rqrcData.qrBgColor,
			dotStyle: rqrcData.qrDotStyle
		};
	}

	/**
	 * Generate and display QR code.
	 */
	function generateQRCode() {
		$('#rqrc-qrcode').empty();

		var qrCodeDisplay = new QRCodeStyling(rqrcQr.buildOptions(currentConfig()));
		qrCodeDisplay.append(document.getElementById('rqrc-qrcode'));
	}

	/**
	 * Setup download button handlers.
	 */
	function setupDownloadHandlers() {
		$('#rqrc-download-png').off('click').on('click', function(e) {
			e.preventDefault();
			rqrcQr.download(currentConfig(), 'png', rqrcData.title);
		});

		$('#rqrc-download-svg').off('click').on('click', function(e) {
			e.preventDefault();
			rqrcQr.download(currentConfig(), 'svg', rqrcData.title);
		});
	}

})(jQuery);
