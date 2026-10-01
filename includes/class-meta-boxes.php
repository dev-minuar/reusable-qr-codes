<?php
/**
 * Meta boxes for QR Code post type.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Handles meta boxes and custom fields.
 */
class RQRC_Meta_Boxes {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Meta_Boxes
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Meta_Boxes
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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_rqrc_item', array( $this, 'save_destination_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Register meta boxes.
	 */
	public function add_meta_boxes() {
		// Destination URL meta box (high priority, right after title).
		add_meta_box(
			'rqrc_destination',
			__( 'Destination URL', 'reusable-qr-codes' ),
			array( $this, 'render_destination_meta_box' ),
			'rqrc_item',
			'normal',
			'high'
		);

		// Notes meta box.
		add_meta_box(
			'rqrc_notes',
			__( 'Notes', 'reusable-qr-codes' ),
			array( $this, 'render_notes_meta_box' ),
			'rqrc_item',
			'normal',
			'default'
		);

		// QR Code display meta box (sidebar).
		add_meta_box(
			'rqrc_display',
			__( 'QR Code Preview', 'reusable-qr-codes' ),
			array( $this, 'render_qr_display_meta_box' ),
			'rqrc_item',
			'side',
			'high'
		);

		// Logo meta box (sidebar).
		add_meta_box(
			'rqrc_logo',
			__( 'Logo', 'reusable-qr-codes' ),
			array( $this, 'render_logo_meta_box' ),
			'rqrc_item',
			'side',
			'default'
		);
	}

	/**
	 * Render destination URL meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_destination_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'rqrc_save_destination', 'rqrc_destination_nonce' );

		// Get current values.
		$destination = get_post_meta( $post->ID, '_rqrc_destination_url', true );
		$is_active   = get_post_meta( $post->ID, '_rqrc_is_active', true );

		// Default to active if not set.
		if ( '' === $is_active ) {
			$is_active = '1';
		}

		?>
		<div class="rqrc-destination-field">
			<p>
				<label for="rqrc_destination_url">
					<?php esc_html_e( 'Enter the URL where this QR code should redirect:', 'reusable-qr-codes' ); ?>
				</label>
			</p>
			<p>
				<input
					type="url"
					id="rqrc_destination_url"
					name="rqrc_destination_url"
					value="<?php echo esc_url( $destination ); ?>"
					class="large-text"
					placeholder="https://example.com"
					required
				/>
			</p>
			<p class="description">
				<?php esc_html_e( 'This is where visitors will be redirected after scanning the QR code. You can change this anytime without reprinting the QR code.', 'reusable-qr-codes' ); ?>
			</p>

			<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
					<label class="rqrc-toggle-switch">
						<input
							type="checkbox"
							id="rqrc_is_active"
							name="rqrc_is_active"
							value="1"
							<?php checked( $is_active, '1' ); ?>
						/>
						<span class="rqrc-toggle-slider"></span>
					</label>
					<label for="rqrc_is_active" style="cursor: pointer;">
						<strong><?php esc_html_e( 'QR Code is Active', 'reusable-qr-codes' ); ?></strong>
					</label>
				</div>
				<p class="description">
					<?php esc_html_e( 'When inactive, this QR code will redirect to your homepage instead of the destination URL above.', 'reusable-qr-codes' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render notes meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_notes_meta_box( $post ) {
		// Get current notes.
		$notes = get_post_meta( $post->ID, '_rqrc_notes', true );
		?>
		<div class="rqrc-notes-field">
			<p>
				<label for="rqrc_notes">
					<?php esc_html_e( 'Internal notes about this QR code (not visible to visitors):', 'reusable-qr-codes' ); ?>
				</label>
			</p>
			<textarea
				id="rqrc_notes"
				name="rqrc_notes"
				rows="5"
				class="large-text"
				placeholder="<?php esc_attr_e( 'e.g., Location: Museum entrance, Printed: 2024-10-23, Purpose: Exhibition info...', 'reusable-qr-codes' ); ?>"
			><?php echo esc_textarea( $notes ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Add any notes to help you remember where this QR code is used, when it was printed, or any other relevant information.', 'reusable-qr-codes' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render QR code display meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_qr_display_meta_box( $post ) {
		if ( 'publish' !== $post->post_status ) {
			?>
			<div id="rqrc-qr-container">
				<div id="rqrc-qrcode" class="rqrc-empty">
					<div class="rqrc-placeholder">
						<div class="rqrc-placeholder-icon">⏳</div>
						<div class="rqrc-placeholder-text">
							<strong><?php esc_html_e( 'Not Published Yet', 'reusable-qr-codes' ); ?></strong>
							<?php esc_html_e( 'Your QR code will be generated after you publish this post.', 'reusable-qr-codes' ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		$permalink = get_permalink( $post->ID );
		?>
		<div id="rqrc-qr-container">
			<div id="rqrc-qrcode"></div>
			<div class="rqrc-download-buttons">
				<a href="#" id="rqrc-download-png" class="button">
					<?php esc_html_e( 'Download PNG', 'reusable-qr-codes' ); ?>
				</a>
				<a href="#" id="rqrc-download-svg" class="button">
					<?php esc_html_e( 'Download SVG', 'reusable-qr-codes' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render logo meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_logo_meta_box( $post ) {
		$mode          = RQRC_Logo::get_mode( $post->ID );
		$custom_id     = absint( get_post_meta( $post->ID, RQRC_Logo::ID_KEY, true ) );
		$custom_url    = RQRC_Logo::image_url( $custom_id );
		$site_logo_url = RQRC_Logo::image_url( RQRC_Logo::get_site_logo_id() );
		$settings_url  = admin_url( 'edit.php?post_type=rqrc_item&page=rqrc-settings' );
		?>
		<div class="rqrc-logo-choice">
			<p>
				<label>
					<input type="radio" name="rqrc_logo_mode" value="none" <?php checked( $mode, 'none' ); ?> />
					<?php esc_html_e( 'No logo', 'reusable-qr-codes' ); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="radio" name="rqrc_logo_mode" value="site" <?php checked( $mode, 'site' ); ?> <?php disabled( '' === $site_logo_url && 'site' !== $mode ); ?> />
					<?php esc_html_e( 'Site logo', 'reusable-qr-codes' ); ?>
				</label>
				<?php if ( '' === $site_logo_url ) : ?>
					<br /><span class="description">
						<?php
						printf(
							/* translators: %s: link to the settings page */
							esc_html__( 'No site logo set. Add one in %s.', 'reusable-qr-codes' ),
							'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'reusable-qr-codes' ) . '</a>'
						);
						?>
					</span>
				<?php endif; ?>
			</p>
			<p>
				<label>
					<input type="radio" name="rqrc_logo_mode" value="custom" <?php checked( $mode, 'custom' ); ?> />
					<?php esc_html_e( 'Custom image', 'reusable-qr-codes' ); ?>
				</label>
			</p>
			<div class="rqrc-logo-field rqrc-logo-custom"<?php echo 'custom' === $mode ? '' : ' style="display:none;"'; ?>>
				<input type="hidden" name="rqrc_logo_id" id="rqrc_logo_id" value="<?php echo esc_attr( $custom_url ? $custom_id : 0 ); ?>" data-url="<?php echo esc_url( $custom_url ); ?>" />
				<div class="rqrc-logo-preview"<?php echo $custom_url ? '' : ' style="display:none;"'; ?>>
					<img src="<?php echo esc_url( $custom_url ); ?>" alt="" />
				</div>
				<button type="button" class="button rqrc-logo-select"><?php esc_html_e( 'Select Image', 'reusable-qr-codes' ); ?></button>
				<button type="button" class="button-link rqrc-logo-remove"<?php echo $custom_url ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'reusable-qr-codes' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Save destination URL meta data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_destination_meta( $post_id, $post ) {
		// Check nonce.
		if ( ! isset( $_POST['rqrc_destination_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rqrc_destination_nonce'] ) ), 'rqrc_save_destination' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save destination URL.
		if ( isset( $_POST['rqrc_destination_url'] ) ) {
			$destination = esc_url_raw( wp_unslash( $_POST['rqrc_destination_url'] ) );
			update_post_meta( $post_id, '_rqrc_destination_url', $destination );
		} else {
			delete_post_meta( $post_id, '_rqrc_destination_url' );
		}

		// Save active status.
		$is_active = isset( $_POST['rqrc_is_active'] ) ? '1' : '0';
		update_post_meta( $post_id, '_rqrc_is_active', $is_active );

		// Save logo choice.
		$logo_mode = isset( $_POST['rqrc_logo_mode'] ) ? sanitize_key( wp_unslash( $_POST['rqrc_logo_mode'] ) ) : 'none';
		if ( ! in_array( $logo_mode, array( 'none', 'site', 'custom' ), true ) ) {
			$logo_mode = 'none';
		}
		update_post_meta( $post_id, RQRC_Logo::MODE_KEY, $logo_mode );

		$logo_id = isset( $_POST['rqrc_logo_id'] ) ? absint( $_POST['rqrc_logo_id'] ) : 0;
		if ( 'custom' === $logo_mode && $logo_id && wp_attachment_is_image( $logo_id ) ) {
			update_post_meta( $post_id, RQRC_Logo::ID_KEY, $logo_id );
		} else {
			delete_post_meta( $post_id, RQRC_Logo::ID_KEY );
		}

		// Save notes.
		if ( isset( $_POST['rqrc_notes'] ) ) {
			$notes = sanitize_textarea_field( wp_unslash( $_POST['rqrc_notes'] ) );
			update_post_meta( $post_id, '_rqrc_notes', $notes );
		} else {
			delete_post_meta( $post_id, '_rqrc_notes' );
		}
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on post edit screen for our post type.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		global $post;
		if ( ! $post || 'rqrc_item' !== $post->post_type ) {
			return;
		}

		wp_enqueue_media();

		// Enqueue our generator script.
		wp_enqueue_script(
			'rqrc-generator',
			RQRC_PLUGIN_URL . 'admin/js/qr-generator.js',
			array( 'jquery', 'rqrc-qr' ),
			RQRC_VERSION,
			true
		);

		// Get plugin settings.
		$settings = get_option( 'rqrc_settings', array() );

		// Pass data to JavaScript.
		wp_localize_script(
			'rqrc-generator',
			'rqrcData',
			array(
				'permalink'   => get_permalink( $post->ID ),
				'title'       => get_the_title( $post->ID ),
				'qrColor'     => isset( $settings['qr_color'] ) ? $settings['qr_color'] : '#000000',
				'qrBgColor'   => isset( $settings['qr_bg_color'] ) ? $settings['qr_bg_color'] : '#ffffff',
				'qrDotStyle'  => isset( $settings['qr_dot_style'] ) ? $settings['qr_dot_style'] : 'square',
				'qrSize'      => isset( $settings['qr_size'] ) ? (int) $settings['qr_size'] : 256,
				'logoUrl'     => RQRC_Logo::get_logo_url( $post->ID ),
				'siteLogoUrl' => RQRC_Logo::image_url( RQRC_Logo::get_site_logo_id() ),
				'logoTitle'   => __( 'Select QR Code Logo', 'reusable-qr-codes' ),
				'logoButton'  => __( 'Use this image', 'reusable-qr-codes' ),
			)
		);

		// Enqueue admin styles.
		wp_enqueue_style(
			'rqrc-admin',
			RQRC_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			RQRC_VERSION
		);
	}
}
