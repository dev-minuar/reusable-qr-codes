/**
 * Site logo picker on the settings page.
 *
 * @package Reusable_QR_Codes
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var frame;
		var $field = $('.rqrc-logo-field');

		$field.on('click', '.rqrc-logo-select', function(e) {
			e.preventDefault();

			if (!frame) {
				frame = wp.media({
					title: rqrcLogoL10n.title,
					button: { text: rqrcLogoL10n.button },
					library: { type: 'image' },
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					var url = attachment.sizes && attachment.sizes.large ? attachment.sizes.large.url : attachment.url;

					$('#rqrc_site_logo_id').val(attachment.id);
					$field.find('.rqrc-logo-preview img').attr('src', url);
					$field.find('.rqrc-logo-preview, .rqrc-logo-remove').show();
				});
			}

			frame.open();
		});

		$field.on('click', '.rqrc-logo-remove', function(e) {
			e.preventDefault();
			$('#rqrc_site_logo_id').val(0);
			$field.find('.rqrc-logo-preview img').attr('src', '');
			$field.find('.rqrc-logo-preview, .rqrc-logo-remove').hide();
		});
	});

})(jQuery);
