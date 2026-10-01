<?php
/**
 * Plugin settings page.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Handles plugin settings.
 */
class RQRC_Settings {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Settings
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Settings
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
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );
	}

	/**
	 * Add settings page to WordPress admin menu.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=rqrc_item',
			__( 'QR Code Settings', 'reusable-qr-codes' ),
			__( 'Settings', 'reusable-qr-codes' ),
			'manage_options',
			'rqrc-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'rqrc_settings_group',
			'rqrc_settings',
			array( $this, 'sanitize_settings' )
		);

		// QR Code Appearance Section.
		add_settings_section(
			'rqrc_appearance_section',
			__( 'QR Code Appearance', 'reusable-qr-codes' ),
			array( $this, 'render_appearance_section' ),
			'rqrc-settings'
		);

		add_settings_field(
			'qr_color',
			__( 'QR Code Color', 'reusable-qr-codes' ),
			array( $this, 'render_color_field' ),
			'rqrc-settings',
			'rqrc_appearance_section',
			array( 'field' => 'qr_color', 'default' => '#000000' )
		);

		add_settings_field(
			'qr_bg_color',
			__( 'Background Color', 'reusable-qr-codes' ),
			array( $this, 'render_color_field' ),
			'rqrc-settings',
			'rqrc_appearance_section',
			array( 'field' => 'qr_bg_color', 'default' => '#ffffff' )
		);

		add_settings_field(
			'qr_dot_style',
			__( 'Dot Style', 'reusable-qr-codes' ),
			array( $this, 'render_dot_style_field' ),
			'rqrc-settings',
			'rqrc_appearance_section'
		);

		add_settings_field(
			'qr_size',
			__( 'Display Size (px)', 'reusable-qr-codes' ),
			array( $this, 'render_size_field' ),
			'rqrc-settings',
			'rqrc_appearance_section'
		);

		add_settings_field(
			'qr_logo_id',
			__( 'Site Logo', 'reusable-qr-codes' ),
			array( $this, 'render_logo_field' ),
			'rqrc-settings',
			'rqrc_appearance_section'
		);

		// Redirect Settings Section.
		add_settings_section(
			'rqrc_redirect_section',
			__( 'Redirect Settings', 'reusable-qr-codes' ),
			array( $this, 'render_redirect_section' ),
			'rqrc-settings'
		);

		add_settings_field(
			'redirect_type',
			__( 'Redirect Type', 'reusable-qr-codes' ),
			array( $this, 'render_redirect_type_field' ),
			'rqrc-settings',
			'rqrc_redirect_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize color fields.
		if ( isset( $input['qr_color'] ) ) {
			$sanitized['qr_color'] = sanitize_hex_color( $input['qr_color'] );
		}

		if ( isset( $input['qr_bg_color'] ) ) {
			$sanitized['qr_bg_color'] = sanitize_hex_color( $input['qr_bg_color'] );
		}

		// Sanitize dot style.
		$allowed_styles = array( 'square', 'dots', 'rounded', 'classy', 'classy-rounded' );
		if ( isset( $input['qr_dot_style'] ) && in_array( $input['qr_dot_style'], $allowed_styles, true ) ) {
			$sanitized['qr_dot_style'] = $input['qr_dot_style'];
		}

		// Sanitize size.
		if ( isset( $input['qr_size'] ) ) {
			$size = absint( $input['qr_size'] );
			$sanitized['qr_size'] = ( $size >= 128 && $size <= 512 ) ? $size : 256;
		}

		// Sanitize redirect type.
		if ( isset( $input['redirect_type'] ) && in_array( $input['redirect_type'], array( '301', '302' ), true ) ) {
			$sanitized['redirect_type'] = $input['redirect_type'];
		}

		// Sanitize site logo: must be an image attachment.
		if ( isset( $input['qr_logo_id'] ) ) {
			$logo_id                 = absint( $input['qr_logo_id'] );
			$sanitized['qr_logo_id'] = ( $logo_id && wp_attachment_is_image( $logo_id ) ) ? $logo_id : 0;
		}

		return $sanitized;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- How It Works - Collapsible Info Box -->
			<div class="rqrc-info-box">
				<button type="button" class="rqrc-info-toggle" aria-expanded="false">
					<span class="rqrc-info-icon">ℹ️</span>
					<strong><?php esc_html_e( 'How It Works', 'reusable-qr-codes' ); ?></strong>
					<span class="rqrc-toggle-indicator dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="rqrc-info-content" style="display: none;">
					<ol>
						<li><?php esc_html_e( 'Create a new QR Code and set a destination URL', 'reusable-qr-codes' ); ?></li>
						<li><?php esc_html_e( 'Download and print/share the QR code', 'reusable-qr-codes' ); ?></li>
						<li><?php esc_html_e( 'Visitors scan the QR code and get redirected to your destination', 'reusable-qr-codes' ); ?></li>
						<li><?php esc_html_e( 'Update the destination anytime without reprinting the QR code!', 'reusable-qr-codes' ); ?></li>
					</ol>
				</div>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'rqrc_settings_group' );
				do_settings_sections( 'rqrc-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render appearance section description.
	 */
	public function render_appearance_section() {
		?>
		<p><?php esc_html_e( 'These settings control the default appearance of all QR codes.', 'reusable-qr-codes' ); ?></p>
		<?php
	}

	/**
	 * Render redirect section description.
	 */
	public function render_redirect_section() {
		?>
		<p><?php esc_html_e( 'Configure how QR code redirects should behave.', 'reusable-qr-codes' ); ?></p>
		<?php
	}

	/**
	 * Render color picker field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_color_field( $args ) {
		$settings = get_option( 'rqrc_settings', array() );
		$value    = isset( $settings[ $args['field'] ] ) ? $settings[ $args['field'] ] : $args['default'];
		?>
		<input
			type="text"
			name="rqrc_settings[<?php echo esc_attr( $args['field'] ); ?>]"
			value="<?php echo esc_attr( $value ); ?>"
			class="rqrc-color-picker"
			data-default-color="<?php echo esc_attr( $args['default'] ); ?>"
		/>
		<?php
	}

	/**
	 * Render dot style field.
	 */
	public function render_dot_style_field() {
		$settings = get_option( 'rqrc_settings', array() );
		$value    = isset( $settings['qr_dot_style'] ) ? $settings['qr_dot_style'] : 'square';

		$styles = array(
			'square'         => __( 'Square', 'reusable-qr-codes' ),
			'dots'           => __( 'Dots', 'reusable-qr-codes' ),
			'rounded'        => __( 'Rounded', 'reusable-qr-codes' ),
			'classy'         => __( 'Classy', 'reusable-qr-codes' ),
			'classy-rounded' => __( 'Classy Rounded', 'reusable-qr-codes' ),
		);
		?>
		<select name="rqrc_settings[qr_dot_style]">
			<?php foreach ( $styles as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'The style of dots used in the QR code pattern.', 'reusable-qr-codes' ); ?>
		</p>
		<?php
	}

	/**
	 * Render size field.
	 */
	public function render_size_field() {
		$settings = get_option( 'rqrc_settings', array() );
		$value    = isset( $settings['qr_size'] ) ? $settings['qr_size'] : 256;
		?>
		<input
			type="number"
			name="rqrc_settings[qr_size]"
			value="<?php echo esc_attr( $value ); ?>"
			min="128"
			max="512"
			step="1"
		/>
		<p class="description">
			<?php esc_html_e( 'Size of the QR code preview in admin (128-512px). Downloads are always high resolution.', 'reusable-qr-codes' ); ?>
		</p>
		<?php
	}

	/**
	 * Render site logo field.
	 */
	public function render_logo_field() {
		$logo_id  = RQRC_Logo::get_site_logo_id();
		$logo_url = RQRC_Logo::image_url( $logo_id );
		?>
		<div class="rqrc-logo-field">
			<input type="hidden" name="rqrc_settings[qr_logo_id]" id="rqrc_site_logo_id" value="<?php echo esc_attr( $logo_url ? $logo_id : 0 ); ?>" />
			<div class="rqrc-logo-preview"<?php echo $logo_url ? '' : ' style="display:none;"'; ?>>
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="" />
			</div>
			<button type="button" class="button rqrc-logo-select"><?php esc_html_e( 'Select Logo', 'reusable-qr-codes' ); ?></button>
			<button type="button" class="button-link rqrc-logo-remove"<?php echo $logo_url ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'reusable-qr-codes' ); ?></button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Optional. Each QR code chooses whether to show this logo, a custom image or no logo. Square images work best.', 'reusable-qr-codes' ); ?>
		</p>
		<?php
	}

	/**
	 * Render redirect type field.
	 */
	public function render_redirect_type_field() {
		$settings = get_option( 'rqrc_settings', array() );
		$value    = isset( $settings['redirect_type'] ) ? $settings['redirect_type'] : '302';
		?>
		<fieldset>
			<label>
				<input
					type="radio"
					name="rqrc_settings[redirect_type]"
					value="302"
					<?php checked( $value, '302' ); ?>
				/>
				<?php esc_html_e( '302 Temporary (Recommended)', 'reusable-qr-codes' ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="rqrc_settings[redirect_type]"
					value="301"
					<?php checked( $value, '301' ); ?>
				/>
				<?php esc_html_e( '301 Permanent', 'reusable-qr-codes' ); ?>
			</label>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Use 302 (temporary) since you may change destinations. 301 redirects are cached by browsers.', 'reusable-qr-codes' ); ?>
		</p>
		<?php
	}

	/**
	 * Enqueue scripts for settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_settings_scripts( $hook ) {
		if ( 'rqrc_item_page_rqrc-settings' !== $hook ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'rqrc-admin',
			RQRC_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			RQRC_VERSION
		);

		// WordPress color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Media modal for the site logo.
		wp_enqueue_media();
		wp_enqueue_script(
			'rqrc-settings-logo',
			RQRC_PLUGIN_URL . 'admin/js/settings-logo.js',
			array( 'jquery' ),
			RQRC_VERSION,
			true
		);
		wp_localize_script(
			'rqrc-settings-logo',
			'rqrcLogoL10n',
			array(
				'title'  => __( 'Select QR Code Logo', 'reusable-qr-codes' ),
				'button' => __( 'Use this image', 'reusable-qr-codes' ),
			)
		);

		// Initialize color picker and info box toggle.
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(document).ready(function($) {
				$(".rqrc-color-picker").wpColorPicker();

				// Info box toggle
				$(".rqrc-info-toggle").on("click", function() {
					var $toggle = $(this);
					var $content = $(".rqrc-info-content");
					var isExpanded = $toggle.attr("aria-expanded") === "true";

					$toggle.attr("aria-expanded", !isExpanded);
					$content.slideToggle(200);
				});
			});'
		);
	}
}
