/**
 * Scan counter reset on the QR code edit screen.
 *
 * @package Reusable_QR_Codes
 */

(function($) {
	'use strict';

	$(document).on('click', '.rqrc-scans-reset', function(e) {
		e.preventDefault();

		var $box = $(this).closest('.rqrc-scans');

		// eslint-disable-next-line no-alert
		if (!window.confirm(rqrcScans.confirm)) {
			return;
		}

		$.post(rqrcScans.ajaxUrl, {
			action: 'rqrc_reset_scans',
			nonce: rqrcScans.nonce,
			post_id: $box.data('post-id')
		}).done(function(response) {
			if (response && response.success) {
				$box.find('.rqrc-scans-count strong').text(response.data.count);
				$box.find('.rqrc-scans-last').text(response.data.last);
			} else {
				window.alert(response && response.data && response.data.message ? response.data.message : rqrcScans.failed);
			}
		}).fail(function() {
			window.alert(rqrcScans.failed);
		});
	});

})(jQuery);
