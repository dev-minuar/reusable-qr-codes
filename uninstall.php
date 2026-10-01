<?php
/**
 * Uninstall script for Reusable QR Codes.
 *
 * Fired when the plugin is uninstalled.
 *
 * @package Reusable_QR_Codes
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all rqrc_item posts and their meta data.
 */
function rqrc_delete_all_posts() {
	global $wpdb;

	// Get all rqrc_item posts.
	$posts = get_posts(
		array(
			'post_type'      => 'rqrc_item',
			'posts_per_page' => -1,
			'post_status'    => array_keys( get_post_stati() ),
			'fields'         => 'ids',
		)
	);

	// Delete each post and its meta.
	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}

/**
 * Delete plugin options.
 */
function rqrc_delete_options() {
	delete_option( 'rqrc_settings' );
}

/**
 * Main uninstall function.
 */
function rqrc_uninstall() {
	// Check if user wants to keep data (future enhancement).
	// For now, we'll delete everything.

	// Delete all posts.
	rqrc_delete_all_posts();

	// Delete options.
	rqrc_delete_options();

	// Flush rewrite rules.
	flush_rewrite_rules();
}

// Run uninstall.
rqrc_uninstall();
