<?php
/**
 * Meta box: Template thumbnail (featured image) with explicit Set/Remove UI.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Meta_Box_Thumbnail
 */
class Pinster_DM_Meta_Box_Thumbnail {

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Meta_Box_Thumbnail|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Meta_Box_Thumbnail
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'wp_ajax_pinster_dm_set_thumbnail', array( $this, 'ajax_set_thumbnail' ) );
	}

	/**
	 * Add meta box.
	 */
	public function add_meta_box() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		add_meta_box(
			'pinster_dm_thumbnail',
			__( 'Template thumbnail (preview image)', 'pinster-download-manager' ),
			array( $this, 'render' ),
			Pinster_DM_CPT_Resume_Template::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function render( $post ) {
		$thumb_id = get_post_thumbnail_id( $post->ID );
		$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
		?>
		<div class="pinster-dm-thumbnail-box" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>">
			<div class="pinster-dm-thumbnail-preview" style="margin-bottom:10px;">
				<?php if ( $thumb_url ) : ?>
					<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" style="max-width:100%;height:auto;display:block;border:1px solid #ddd;" />
				<?php else : ?>
					<div style="background:#f0f0f0;border:1px dashed #ccc;padding:40px 20px;text-align:center;color:#666;">
						<?php esc_html_e( 'No thumbnail set. This image appears on the template card.', 'pinster-download-manager' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<p>
				<button type="button" class="button button-secondary pinster-dm-set-thumbnail"><?php esc_html_e( 'Set thumbnail', 'pinster-download-manager' ); ?></button>
				<?php if ( $thumb_id ) : ?>
					<button type="button" class="button button-link-delete pinster-dm-remove-thumbnail"><?php esc_html_e( 'Remove', 'pinster-download-manager' ); ?></button>
				<?php endif; ?>
			</p>
			<input type="hidden" id="pinster-dm-thumbnail-id" name="pinster_dm_thumbnail_id" value="<?php echo esc_attr( (string) $thumb_id ); ?>" />
		</div>
		<?php
	}

	/**
	 * AJAX: set post thumbnail.
	 */
	public function ajax_set_thumbnail() {
		if ( ! check_ajax_referer( 'pinster_dm_thumbnail', 'nonce', false ) || ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'pinster-download-manager' ) ) );
		}
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$attachment_id = isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0;
		if ( ! $post_id || get_post_type( $post_id ) !== Pinster_DM_CPT_Resume_Template::POST_TYPE ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post.', 'pinster-download-manager' ) ) );
		}
		if ( $attachment_id && ! wp_attachment_is_image( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select an image.', 'pinster-download-manager' ) ) );
		}
		if ( 0 === $attachment_id ) {
			delete_post_thumbnail( $post_id );
			wp_send_json_success( array( 'thumb_url' => '', 'attachment_id' => 0 ) );
		}
		$result = set_post_thumbnail( $post_id, $attachment_id );
		if ( false === $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to set thumbnail.', 'pinster-download-manager' ) ) );
		}
		$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
		wp_send_json_success( array( 'thumb_url' => $thumb_url, 'attachment_id' => $attachment_id ) );
	}
}
