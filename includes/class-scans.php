<?php
/**
 * Privacy-friendly scan counter.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Counts QR code scans: one number and one date per QR code, no personal data.
 */
class RQRC_Scans {

	/**
	 * Post meta key for the scan count.
	 */
	const COUNT_KEY = '_rqrc_scan_count';

	/**
	 * Post meta key for the last scan time (UTC, Y-m-d H:i:s).
	 */
	const LAST_KEY = '_rqrc_last_scan';

	/**
	 * Default user-agent pattern for bots, link previews and scripts.
	 */
	const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|facebot|whatsapp|telegram|slack|discord|skype|linkedin|embedly|preview|pinterest|vkshare|w3c_validator|curl|wget|python|go-http|java\/|headless|lighthouse|monitor/i';

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Scans
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Scans
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_ajax_rqrc_reset_scans', array( $this, 'ajax_reset' ) );
	}

	/**
	 * Whether the current request should count as a scan.
	 *
	 * @return bool
	 */
	public static function should_count() {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
		if ( 'GET' !== $method || is_user_logged_in() ) {
			return false;
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( '' === $user_agent ) {
			return false;
		}

		/**
		 * Filter the regular expression that marks a user agent as a bot.
		 *
		 * @param string $pattern PCRE pattern including delimiters.
		 */
		$pattern = apply_filters( 'rqrc_bot_user_agent_pattern', self::BOT_PATTERN );

		return ! preg_match( $pattern, $user_agent );
	}

	/**
	 * Add one scan to a QR code.
	 *
	 * @param int $post_id QR code post ID.
	 */
	public static function record( $post_id ) {
		global $wpdb;

		add_post_meta( $post_id, self::COUNT_KEY, 0, true );

		// Atomic increment so concurrent scans are never lost.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Atomic counter; the meta cache is cleared below.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
				$post_id,
				self::COUNT_KEY
			)
		);
		wp_cache_delete( $post_id, 'post_meta' );

		update_post_meta( $post_id, self::LAST_KEY, gmdate( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Get the scan count of a QR code.
	 *
	 * @param int $post_id QR code post ID.
	 * @return int
	 */
	public static function get_count( $post_id ) {
		return absint( get_post_meta( $post_id, self::COUNT_KEY, true ) );
	}

	/**
	 * Get the last scan time of a QR code.
	 *
	 * @param int $post_id QR code post ID.
	 * @return string UTC datetime, or an empty string when never scanned.
	 */
	public static function get_last( $post_id ) {
		return (string) get_post_meta( $post_id, self::LAST_KEY, true );
	}

	/**
	 * Human-readable "last scan" text for admin screens.
	 *
	 * @param int $post_id QR code post ID.
	 * @return string
	 */
	public static function get_last_label( $post_id ) {
		$last = self::get_last( $post_id );
		if ( '' === $last ) {
			return __( 'Never scanned', 'reusable-qr-codes' );
		}

		return sprintf(
			/* translators: %s: time difference, e.g. "5 mins" */
			__( 'Last: %s ago', 'reusable-qr-codes' ),
			human_time_diff( strtotime( $last . ' UTC' ), time() )
		);
	}

	/**
	 * AJAX handler to reset a QR code's scan counter.
	 */
	public function ajax_reset() {
		check_ajax_referer( 'rqrc_reset_scans', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || 'rqrc_item' !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid QR code.', 'reusable-qr-codes' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this QR code.', 'reusable-qr-codes' ) ) );
		}

		update_post_meta( $post_id, self::COUNT_KEY, 0 );
		delete_post_meta( $post_id, self::LAST_KEY );

		wp_send_json_success(
			array(
				'count' => 0,
				'last'  => self::get_last_label( $post_id ),
			)
		);
	}
}
