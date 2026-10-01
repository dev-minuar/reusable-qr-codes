<?php
/**
 * Main plugin class.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main plugin class - handles core functionality.
 */
class RQRC_Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Plugin
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Plugin
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
		// Register shared scripts early so every screen and the block can depend on them.
		add_action( 'init', array( $this, 'register_scripts' ), 5 );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . RQRC_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Register the QR library and the shared QR helper.
	 */
	public function register_scripts() {
		wp_register_script(
			'rqrc-qrcode-styling',
			RQRC_PLUGIN_URL . 'assets/vendor/QrCodeStyling.min.js',
			array(),
			RQRC_VERSION,
			true
		);

		wp_register_script(
			'rqrc-qr',
			RQRC_PLUGIN_URL . 'assets/js/rqrc-qr.js',
			array( 'rqrc-qrcode-styling' ),
			RQRC_VERSION,
			true
		);
	}

	/**
	 * Add settings link to plugin actions.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=rqrc_item&page=rqrc-settings' ) ),
			esc_html__( 'Settings', 'reusable-qr-codes' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}
