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
		// Check if we have the data.
		if (typeof rqrcData === 'undefined') {
			return;
		}

		// The logo box works before the post is published.
		setupLogoChoice();

		// Check if we have the container.
		if ($('#rqrc-qrcode').length === 0) {
			return;
		}

		// Don't generate if there's a placeholder (unpublished post).
		if ($('#rqrc-qrcode').find('.rqrc-placeholder').length > 0) {
			return;
		}

		generateQRCode();
		setupDownloadHandlers();
	});

	/**
	 * Logo URL for the currently selected logo choice.
	 *
	 * @return {string} URL, or empty for no logo.
	 */
	function currentLogoUrl() {
		var mode = $('input[name="rqrc_logo_mode"]:checked').val();

		if (mode === 'site') {
			return rqrcData.siteLogoUrl || '';
		}
		if (mode === 'custom') {
			return $('#rqrc_logo_id').attr('data-url') || '';
		}
		return '';
	}

	/**
	 * Wire the logo choice box: radio changes and the custom image picker.
	 */
	function setupLogoChoice() {
		var frame;
		var $custom = $('.rqrc-logo-custom');

		$('input[name="rqrc_logo_mode"]').on('change', function() {
			$custom.toggle($(this).val() === 'custom');
			redraw();
		});

		$custom.on('click', '.rqrc-logo-select', function(e) {
			e.preventDefault();

			if (!frame) {
				frame = wp.media({
					title: rqrcData.logoTitle,
					button: { text: rqrcData.logoButton },
					library: { type: 'image' },
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					var url = attachment.sizes && attachment.sizes.large ? attachment.sizes.large.url : attachment.url;

					$('#rqrc_logo_id').val(attachment.id).attr('data-url', url);
					$custom.find('.rqrc-logo-preview img').attr('src', url);
					$custom.find('.rqrc-logo-preview, .rqrc-logo-remove').show();
					redraw();
				});
			}

			frame.open();
		});

		$custom.on('click', '.rqrc-logo-remove', function(e) {
			e.preventDefault();
			$('#rqrc_logo_id').val(0).attr('data-url', '');
			$custom.find('.rqrc-logo-preview img').attr('src', '');
			$custom.find('.rqrc-logo-preview, .rqrc-logo-remove').hide();
			redraw();
		});
	}

	/**
	 * Redraw the preview if the post is published.
	 */
	function redraw() {
		if ($('#rqrc-qrcode').length && $('#rqrc-qrcode').find('.rqrc-placeholder').length === 0) {
			generateQRCode();
		}
	}

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
			dotStyle: rqrcData.qrDotStyle,
			logoUrl: currentLogoUrl()
		};
	}

	/**
	 * Generate and display QR code.
	 */
	function generateQRCode() {
		rqrcQr.draw(currentConfig(), document.getElementById('rqrc-qrcode'));
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
