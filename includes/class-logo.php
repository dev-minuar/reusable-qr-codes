<?php
/**
 * Logo resolution for QR codes.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Resolves which logo, if any, a QR code shows.
 */
class RQRC_Logo {

	/**
	 * Post meta key for the logo mode: none, site or custom.
	 */
	const MODE_KEY = '_rqrc_logo_mode';

	/**
	 * Post meta key for the custom logo attachment ID.
	 */
	const ID_KEY = '_rqrc_logo_id';

	/**
	 * Get the logo mode of a QR code.
	 *
	 * @param int $post_id QR code post ID.
	 * @return string One of none, site, custom.
	 */
	public static function get_mode( $post_id ) {
		$mode = get_post_meta( $post_id, self::MODE_KEY, true );
		return in_array( $mode, array( 'site', 'custom' ), true ) ? $mode : 'none';
	}

	/**
	 * Get the site logo attachment ID from the settings.
	 *
	 * @return int Attachment ID, or 0 when none is set.
	 */
	public static function get_site_logo_id() {
		$settings = get_option( 'rqrc_settings', array() );
		return isset( $settings['qr_logo_id'] ) ? absint( $settings['qr_logo_id'] ) : 0;
	}

	/**
	 * Get a drawable URL for an image attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string Image URL, or an empty string when it is missing or not an image.
	 */
	public static function image_url( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
			return '';
		}

		$src = wp_get_attachment_image_src( $attachment_id, 'large' );
		return $src ? $src[0] : '';
	}

	/**
	 * Get the logo URL a QR code should draw.
	 *
	 * @param int $post_id QR code post ID.
	 * @return string Logo URL, or an empty string for no logo.
	 */
	public static function get_logo_url( $post_id ) {
		switch ( self::get_mode( $post_id ) ) {
			case 'site':
				return self::image_url( self::get_site_logo_id() );
			case 'custom':
				return self::image_url( get_post_meta( $post_id, self::ID_KEY, true ) );
			default:
				return '';
		}
	}
}
