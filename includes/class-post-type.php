<?php
/**
 * Custom Post Type registration.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Handles custom post type registration.
 */
class RQRC_Post_Type {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Post_Type
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Post_Type
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
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
		add_filter( 'manage_rqrc_item_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_rqrc_item_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_list_scripts' ) );
		add_action( 'wp_ajax_rqrc_toggle_status', array( $this, 'ajax_toggle_status' ) );
	}

	/**
	 * Register the rqrc_item custom post type.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'QR Codes', 'Post type general name', 'reusable-qr-codes' ),
			'singular_name'         => _x( 'QR Code', 'Post type singular name', 'reusable-qr-codes' ),
			'menu_name'             => _x( 'QR Codes', 'Admin Menu text', 'reusable-qr-codes' ),
			'name_admin_bar'        => _x( 'QR Code', 'Add New on Toolbar', 'reusable-qr-codes' ),
			'add_new'               => __( 'Add New', 'reusable-qr-codes' ),
			'add_new_item'          => __( 'Add New QR Code', 'reusable-qr-codes' ),
			'new_item'              => __( 'New QR Code', 'reusable-qr-codes' ),
			'edit_item'             => __( 'Edit QR Code', 'reusable-qr-codes' ),
			'view_item'             => __( 'View QR Code', 'reusable-qr-codes' ),
			'all_items'             => __( 'All QR Codes', 'reusable-qr-codes' ),
			'search_items'          => __( 'Search QR Codes', 'reusable-qr-codes' ),
			'parent_item_colon'     => __( 'Parent QR Codes:', 'reusable-qr-codes' ),
			'not_found'             => __( 'No QR codes found.', 'reusable-qr-codes' ),
			'not_found_in_trash'    => __( 'No QR codes found in Trash.', 'reusable-qr-codes' ),
			'featured_image'        => _x( 'QR Code Image', 'Overrides the "Featured Image" phrase', 'reusable-qr-codes' ),
			'set_featured_image'    => _x( 'Set QR code image', 'Overrides the "Set featured image" phrase', 'reusable-qr-codes' ),
			'remove_featured_image' => _x( 'Remove QR code image', 'Overrides the "Remove featured image" phrase', 'reusable-qr-codes' ),
			'use_featured_image'    => _x( 'Use as QR code image', 'Overrides the "Use as featured image" phrase', 'reusable-qr-codes' ),
			'archives'              => _x( 'QR Code archives', 'The post type archive label used in nav menus', 'reusable-qr-codes' ),
			'insert_into_item'      => _x( 'Insert into QR code', 'Overrides the "Insert into post" phrase', 'reusable-qr-codes' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this QR code', 'Overrides the "Uploaded to this post" phrase', 'reusable-qr-codes' ),
			'filter_items_list'     => _x( 'Filter QR codes list', 'Screen reader text for the filter links', 'reusable-qr-codes' ),
			'items_list_navigation' => _x( 'QR codes list navigation', 'Screen reader text for the pagination', 'reusable-qr-codes' ),
			'items_list'            => _x( 'QR codes list', 'Screen reader text for the items list', 'reusable-qr-codes' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'rqrc' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-smartphone',
			'supports'           => array( 'title' ),
			'show_in_rest'       => false,
		);

		register_post_type( 'rqrc_item', $args );
	}

	/**
	 * Custom update messages for the post type.
	 *
	 * @param array $messages Existing post update messages.
	 * @return array Modified post update messages.
	 */
	public function updated_messages( $messages ) {
		global $post;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display of a core revision notice, no state change.
		$revision_id = isset( $_GET['revision'] ) ? absint( $_GET['revision'] ) : 0;

		$messages['rqrc_item'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'QR Code updated successfully.', 'reusable-qr-codes' ),
			2  => __( 'Field updated.', 'reusable-qr-codes' ),
			3  => __( 'Field deleted.', 'reusable-qr-codes' ),
			4  => __( 'QR Code updated.', 'reusable-qr-codes' ),
			5  => $revision_id ? sprintf(
				/* translators: %s: Revision date */
				__( 'QR Code restored to revision from %s.', 'reusable-qr-codes' ),
				wp_post_revision_title( $revision_id, false )
			) : false,
			6  => __( 'QR Code published successfully. Your QR code is now ready to use!', 'reusable-qr-codes' ),
			7  => __( 'QR Code saved.', 'reusable-qr-codes' ),
			8  => __( 'QR Code submitted.', 'reusable-qr-codes' ),
			9  => isset( $post->post_date ) ? sprintf(
				/* translators: %s: Scheduled date */
				__( 'QR Code scheduled for: <strong>%s</strong>', 'reusable-qr-codes' ),
				date_i18n( __( 'M j, Y @ g:i a', 'reusable-qr-codes' ), strtotime( $post->post_date ) )
			) : false,
			10 => __( 'QR Code draft updated.', 'reusable-qr-codes' ),
		);

		return $messages;
	}

	/**
	 * Add custom columns to QR codes list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_custom_columns( $columns ) {
		// Remove date column.
		unset( $columns['date'] );

		// Add custom columns.
		$columns['rqrc_status']      = __( 'Status', 'reusable-qr-codes' );
		$columns['rqrc_destination'] = __( 'Destination', 'reusable-qr-codes' );
		$columns['rqrc_download']    = __( 'Download', 'reusable-qr-codes' );

		return $columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'rqrc_status':
				$is_active = get_post_meta( $post_id, '_rqrc_is_active', true );
				// Default to active if not set.
				if ( '' === $is_active ) {
					$is_active = '1';
				}
				?>
				<div class="rqrc-list-status-toggle">
					<label class="rqrc-toggle-switch rqrc-toggle-switch-small">
						<input
							type="checkbox"
							class="rqrc-status-toggle-input"
							data-post-id="<?php echo esc_attr( $post_id ); ?>"
							<?php checked( $is_active, '1' ); ?>
						/>
						<span class="rqrc-toggle-slider"></span>
					</label>
					<span class="rqrc-status-text">
						<?php echo '1' === $is_active ? esc_html__( 'Active', 'reusable-qr-codes' ) : esc_html__( 'Inactive', 'reusable-qr-codes' ); ?>
					</span>
				</div>
				<?php
				break;

			case 'rqrc_destination':
				$destination = get_post_meta( $post_id, '_rqrc_destination_url', true );
				if ( $destination ) {
					// Truncate long URLs.
					$display_url = strlen( $destination ) > 50 ? substr( $destination, 0, 47 ) . '...' : $destination;
					echo '<a href="' . esc_url( $destination ) . '" target="_blank" rel="noopener" title="' . esc_attr( $destination ) . '">';
					echo esc_html( $display_url );
					echo ' <span class="dashicons dashicons-external" style="font-size: 14px; vertical-align: middle;"></span>';
					echo '</a>';
				} else {
					echo '<span style="color: #999;">' . esc_html__( 'No destination set', 'reusable-qr-codes' ) . '</span>';
				}
				break;

			case 'rqrc_download':
				$permalink = get_permalink( $post_id );
				$title     = get_the_title( $post_id );
				?>
				<div class="rqrc-list-download-buttons">
					<button
						class="rqrc-download-list-png"
						data-permalink="<?php echo esc_attr( $permalink ); ?>"
						data-title="<?php echo esc_attr( $title ); ?>"
						data-logo="<?php echo esc_url( RQRC_Logo::get_logo_url( $post_id ) ); ?>"
						title="<?php esc_attr_e( 'Download PNG', 'reusable-qr-codes' ); ?>"
					>
						PNG
					</button>
					<button
						class="rqrc-download-list-svg"
						data-permalink="<?php echo esc_attr( $permalink ); ?>"
						data-title="<?php echo esc_attr( $title ); ?>"
						data-logo="<?php echo esc_url( RQRC_Logo::get_logo_url( $post_id ) ); ?>"
						title="<?php esc_attr_e( 'Download SVG', 'reusable-qr-codes' ); ?>"
					>
						SVG
					</button>
				</div>
				<?php
				break;
		}
	}

	/**
	 * Enqueue scripts for QR codes list page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_list_scripts( $hook ) {
		// Only load on QR codes list page.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( 'edit.php' !== $hook || ! $screen || 'edit-rqrc_item' !== $screen->id ) {
			return;
		}

		// Enqueue list download script.
		wp_enqueue_script(
			'rqrc-list-download',
			RQRC_PLUGIN_URL . 'admin/js/qr-list-download.js',
			array( 'jquery', 'rqrc-qr' ),
			RQRC_VERSION,
			true
		);

		// Get plugin settings.
		$settings = get_option( 'rqrc_settings', array() );

		// Pass settings to JavaScript.
		wp_localize_script(
			'rqrc-list-download',
			'rqrcListData',
			array(
				'qrColor'    => isset( $settings['qr_color'] ) ? $settings['qr_color'] : '#000000',
				'qrBgColor'  => isset( $settings['qr_bg_color'] ) ? $settings['qr_bg_color'] : '#ffffff',
				'qrDotStyle' => isset( $settings['qr_dot_style'] ) ? $settings['qr_dot_style'] : 'square',
			)
		);

		// Enqueue admin styles for list view.
		wp_enqueue_style(
			'rqrc-admin',
			RQRC_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			RQRC_VERSION
		);

		// Pass AJAX URL and nonce to JavaScript.
		wp_localize_script(
			'rqrc-list-download',
			'rqrcAjax',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'rqrc_toggle_status' ),
			)
		);
	}

	/**
	 * AJAX handler to toggle QR code status.
	 */
	public function ajax_toggle_status() {
		// Check nonce.
		check_ajax_referer( 'rqrc_toggle_status', 'nonce' );

		// Check if post ID is provided.
		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Post ID is required.', 'reusable-qr-codes' ) ) );
		}

		$post_id = absint( $_POST['post_id'] );

		// Rate limiting: Prevent rapid toggling (2 second cooldown per user per post).
		$transient_key = 'rqrc_toggle_' . get_current_user_id() . '_' . $post_id;
		if ( get_transient( $transient_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Please wait before toggling again.', 'reusable-qr-codes' ) ) );
		}
		set_transient( $transient_key, true, 2 ); // 2 seconds cooldown.

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this QR code.', 'reusable-qr-codes' ) ) );
		}

		// Verify post type.
		if ( 'rqrc_item' !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post type.', 'reusable-qr-codes' ) ) );
		}

		// Get current status.
		$current_status = get_post_meta( $post_id, '_rqrc_is_active', true );
		if ( '' === $current_status ) {
			$current_status = '1';
		}

		// Toggle status.
		$new_status = ( '1' === $current_status ) ? '0' : '1';

		// Update post meta.
		update_post_meta( $post_id, '_rqrc_is_active', $new_status );

		// Return success with new status.
		wp_send_json_success(
			array(
				'status'     => $new_status,
				'statusText' => ( '1' === $new_status ) ? __( 'Active', 'reusable-qr-codes' ) : __( 'Inactive', 'reusable-qr-codes' ),
			)
		);
	}
}
