<?php
/**
 * Handles QR code redirects.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Redirect functionality for QR codes.
 */
class RQRC_Redirects {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Redirects
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Redirects
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
		add_action( 'template_redirect', array( $this, 'handle_redirect' ) );
		add_filter( 'template_include', array( $this, 'load_fallback_template' ) );
		add_filter( 'wp_robots', array( $this, 'add_noindex_robots' ) );
		add_filter( 'wp_sitemaps_post_types', array( $this, 'exclude_from_sitemaps' ) );
	}

	/**
	 * Handle redirect for QR code items.
	 */
	public function handle_redirect() {
		// Check if we're viewing a single rqrc_item post.
		if ( ! is_singular( 'rqrc_item' ) ) {
			return;
		}

		global $post;

		// Count the scan (published codes only; skips logged-in users, bots and non-GET requests).
		if ( 'publish' === $post->post_status && RQRC_Scans::should_count() ) {
			RQRC_Scans::record( $post->ID );
		}

		// Check if QR code is active.
		$is_active = get_post_meta( $post->ID, '_rqrc_is_active', true );

		// Default to active if not set (for backward compatibility).
		if ( '' === $is_active ) {
			$is_active = '1';
		}

		// If inactive, redirect to homepage.
		if ( '0' === $is_active ) {
			wp_redirect( home_url(), 302 ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Home URL of this site.
			exit;
		}

		// Get destination URL.
		$destination = get_post_meta( $post->ID, '_rqrc_destination_url', true );

		// If no destination is set, let the template display a message.
		if ( empty( $destination ) ) {
			return;
		}

		// Validate URL.
		$destination = esc_url_raw( $destination );
		if ( empty( $destination ) ) {
			return;
		}

		// Get redirect type from settings (302 temporary by default).
		$settings      = get_option( 'rqrc_settings', array() );
		$redirect_type = isset( $settings['redirect_type'] ) ? $settings['redirect_type'] : '302';
		$status_code   = ( '301' === $redirect_type ) ? 301 : 302;

		// Perform redirect.
		wp_redirect( $destination, $status_code ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Destinations are external sites by design.
		exit;
	}

	/**
	 * Load fallback template if no destination is set.
	 *
	 * @param string $template Template path.
	 * @return string Modified template path.
	 */
	public function load_fallback_template( $template ) {
		// Check if we're viewing a single rqrc_item post.
		if ( ! is_singular( 'rqrc_item' ) ) {
			return $template;
		}

		global $post;

		// Get destination URL.
		$destination = get_post_meta( $post->ID, '_rqrc_destination_url', true );

		// If destination is set, redirect will happen, so no template needed.
		if ( ! empty( $destination ) ) {
			return $template;
		}

		// Load our fallback template.
		$fallback_template = RQRC_PLUGIN_DIR . 'templates/single-rqrc_item.php';

		if ( file_exists( $fallback_template ) ) {
			return $fallback_template;
		}

		return $template;
	}

	/**
	 * Add noindex directive to QR code pages.
	 *
	 * Prevents search engines from indexing redirect URLs.
	 *
	 * @param array $robots Associative array of robots directives.
	 * @return array Modified robots directives.
	 */
	public function add_noindex_robots( $robots ) {
		// Only apply to single rqrc_item posts.
		if ( ! is_singular( 'rqrc_item' ) ) {
			return $robots;
		}

		// Add noindex and nofollow.
		$robots['noindex']  = true;
		$robots['nofollow'] = true;

		return $robots;
	}

	/**
	 * Remove QR code items from the core XML sitemaps.
	 *
	 * @param array $post_types Post types included in the sitemaps.
	 * @return array Modified post types.
	 */
	public function exclude_from_sitemaps( $post_types ) {
		unset( $post_types['rqrc_item'] );

		return $post_types;
	}
}
