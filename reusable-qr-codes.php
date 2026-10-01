<?php
/**
 * Plugin Name: Reusable QR Codes
 * Plugin URI: https://minuar.com/reusable-qr-codes
 * Description: Create reusable QR codes with changeable destinations. Perfect for museums, retail, events, and any place where physical QR codes need to stay relevant over time.
 * Version: 1.0.2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Minuar
 * Author URI: https://minuar.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reusable-qr-codes
 * Domain Path: /languages
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'RQRC_VERSION', '1.0.2' );

/**
 * Plugin root file.
 */
define( 'RQRC_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory path.
 */
define( 'RQRC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'RQRC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'RQRC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Translations are automatically loaded by WordPress based on the
 * Text Domain and Domain Path headers in the plugin file.
 * Manual loading is no longer required since WordPress 4.6+.
 */

/**
 * Load required files.
 */
require_once RQRC_PLUGIN_DIR . 'includes/class-plugin.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-logo.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-post-type.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-redirects.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-scans.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-block.php';
require_once RQRC_PLUGIN_DIR . 'includes/class-settings.php';

/**
 * Initialize the plugin.
 */
function rqrc_init() {
	RQRC_Plugin::get_instance();
	RQRC_Post_Type::get_instance();
	RQRC_Meta_Boxes::get_instance();
	RQRC_Redirects::get_instance();
	RQRC_Scans::get_instance();
	RQRC_Block::get_instance();
	RQRC_Settings::get_instance();
}
add_action( 'plugins_loaded', 'rqrc_init' );

/**
 * Activation hook.
 */
function rqrc_activate() {
	// Register post type.
	RQRC_Post_Type::register_post_type();

	// Flush rewrite rules.
	flush_rewrite_rules();

	// Set default options.
	$defaults = array(
		'qr_color'      => '#000000',
		'qr_bg_color'   => '#ffffff',
		'qr_dot_style'  => 'square',
		'qr_size'       => 256,
		'redirect_type' => '302',
	);

	if ( ! get_option( 'rqrc_settings' ) ) {
		add_option( 'rqrc_settings', $defaults );
	}
}
register_activation_hook( __FILE__, 'rqrc_activate' );

/**
 * Deactivation hook.
 */
function rqrc_deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rqrc_deactivate' );
