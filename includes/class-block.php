<?php
/**
 * QR Code block.
 *
 * @package Reusable_QR_Codes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers and renders the QR Code block.
 */
class RQRC_Block {

	/**
	 * Single instance of the class.
	 *
	 * @var RQRC_Block
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return RQRC_Block
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
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize_editor' ) );
	}

	/**
	 * Register block assets and the block type.
	 */
	public function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'rqrc-block-editor',
			RQRC_PLUGIN_URL . 'blocks/qr-code/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'rqrc-qr' ),
			RQRC_VERSION,
			true
		);

		wp_register_script(
			'rqrc-block-view',
			RQRC_PLUGIN_URL . 'blocks/qr-code/view.js',
			array( 'rqrc-qr' ),
			RQRC_VERSION,
			true
		);

		wp_register_style(
			'rqrc-block',
			RQRC_PLUGIN_URL . 'blocks/qr-code/style.css',
			array(),
			RQRC_VERSION
		);

		register_block_type(
			RQRC_PLUGIN_DIR . 'blocks/qr-code',
			array( 'render_callback' => array( $this, 'render' ) )
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'rqrc-block-editor', 'reusable-qr-codes' );
		}
	}

	/**
	 * Get the appearance settings shared by every QR code.
	 *
	 * @return array Color, background color and dot style.
	 */
	private function appearance() {
		$settings = get_option( 'rqrc_settings', array() );

		return array(
			'color'    => isset( $settings['qr_color'] ) ? $settings['qr_color'] : '#000000',
			'bgColor'  => isset( $settings['qr_bg_color'] ) ? $settings['qr_bg_color'] : '#ffffff',
			'dotStyle' => isset( $settings['qr_dot_style'] ) ? $settings['qr_dot_style'] : 'square',
		);
	}

	/**
	 * Pass the published QR codes and appearance settings to the editor script.
	 */
	public function localize_editor() {
		$posts = get_posts(
			array(
				'post_type'      => 'rqrc_item',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$codes = array();
		foreach ( $posts as $post ) {
			$codes[] = array(
				'id'        => $post->ID,
				'title'     => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
				'permalink' => get_permalink( $post ),
				'logoUrl'   => RQRC_Logo::get_logo_url( $post->ID ),
			);
		}

		wp_localize_script(
			'rqrc-block-editor',
			'rqrcBlockData',
			array_merge( array( 'codes' => $codes ), $this->appearance() )
		);
	}

	/**
	 * Render the block on the front end.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML, or an empty string when the QR code is not published.
	 */
	public function render( $attributes ) {
		$qr_id = isset( $attributes['qrId'] ) ? absint( $attributes['qrId'] ) : 0;
		$post  = $qr_id ? get_post( $qr_id ) : null;

		if ( ! $post || 'rqrc_item' !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$size  = isset( $attributes['size'] ) ? absint( $attributes['size'] ) : 200;
		$size  = min( 600, max( 100, $size ) );
		$title = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );

		$config = array_merge(
			array(
				'data'    => get_permalink( $post ),
				'size'    => $size,
				'logoUrl' => RQRC_Logo::get_logo_url( $post->ID ),
				'name'    => $title,
			),
			$this->appearance()
		);

		wp_enqueue_script( 'rqrc-block-view' );

		$caption = '';
		if ( ! empty( $attributes['showCaption'] ) ) {
			$caption = ( isset( $attributes['caption'] ) && '' !== trim( $attributes['caption'] ) ) ? $attributes['caption'] : $title;
		}

		$wrapper = function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes( array( 'style' => 'width:' . $size . 'px;' ) )
			: 'class="wp-block-reusable-qr-codes-qr-code" style="width:' . $size . 'px;"';

		ob_start();
		?>
		<figure <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by get_block_wrapper_attributes(). ?>>
			<div class="rqrc-block-qr" data-rqrc="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
				<noscript><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( $title ); ?></a></noscript>
			</div>
			<?php if ( '' !== $caption ) : ?>
				<figcaption><?php echo esc_html( $caption ); ?></figcaption>
			<?php endif; ?>
			<?php if ( ! empty( $attributes['showDownload'] ) ) : ?>
				<button type="button" class="rqrc-block-download"><?php esc_html_e( 'Download QR code', 'reusable-qr-codes' ); ?></button>
			<?php endif; ?>
		</figure>
		<?php
		return ob_get_clean();
	}
}
