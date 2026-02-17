<?php
/**
 * Meta box for template file (PDF/DOCX).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Meta_Box_File
 */
class Pinster_DM_Meta_Box_File {

	/**
	 * Meta key for attachment ID.
	 *
	 * @var string
	 */
	const META_KEY = '_pinster_file_id';

	/**
	 * Allowed MIME types.
	 *
	 * @var string[]
	 */
	const ALLOWED_MIMES = array(
		'application/pdf',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Meta_Box_File|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Meta_Box_File
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
		add_action( 'save_post_' . Pinster_DM_CPT_Resume_Template::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Add meta box.
	 */
	public function add_meta_box() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		add_meta_box(
			'pinster_dm_file',
			__( 'Template File (PDF / DOCX)', 'pinster-download-manager' ),
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
		wp_nonce_field( 'pinster_dm_save_file', 'pinster_dm_file_nonce' );
		$file_id = get_post_meta( $post->ID, self::META_KEY, true );
		$file_id = $file_id ? absint( $file_id ) : 0;
		$secure_path = get_post_meta( $post->ID, Pinster_DM_Secure_Storage::META_KEY_PATH, true );
		$name = '';
		$url = '';
		if ( $secure_path && is_string( $secure_path ) ) {
			$name = basename( $secure_path );
		} elseif ( $file_id ) {
			$url = wp_get_attachment_url( $file_id );
			$att_file = get_attached_file( $file_id );
			$name = $att_file ? basename( $att_file ) : '';
		}
		$has_file = ( $file_id && $name ) || ( $secure_path && $name );
		if ( ! $has_file && $secure_path ) {
			$name = basename( $secure_path );
			$has_file = true;
		}
		?>
		<div class="pinster-dm-file-meta-box">
			<p>
				<input type="hidden" id="pinster-dm-file-id" name="pinster_dm_file_id" value="<?php echo esc_attr( (string) $file_id ); ?>" />
				<button type="button" class="button button-secondary" id="pinster-dm-upload-file">
					<?php esc_html_e( 'Select or upload file', 'pinster-download-manager' ); ?>
				</button>
				<button type="button" class="button button-link-delete" id="pinster-dm-remove-file" <?php echo ( ! $has_file ) ? ' style="display:none;"' : ''; ?>>
					<?php esc_html_e( 'Remove', 'pinster-download-manager' ); ?>
				</button>
			</p>
			<p id="pinster-dm-file-info" class="description">
				<?php
				if ( $has_file ) {
					echo esc_html( $name );
					if ( $secure_path ) {
						echo ' <span class="pinster-dm-secure-badge">(' . esc_html__( 'Stored securely', 'pinster-download-manager' ) . ')</span>';
					} elseif ( $url ) {
						echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'View', 'pinster-download-manager' ) . '</a>';
					}
				} else {
					esc_html_e( 'No file selected. Allowed: PDF, DOCX. Enable "Secure storage" in Settings to keep files out of the media library.', 'pinster-download-manager' );
				}
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save( $post_id, $post ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['pinster_dm_file_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pinster_dm_file_nonce'] ) ), 'pinster_dm_save_file' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$file_id = isset( $_POST['pinster_dm_file_id'] ) ? absint( $_POST['pinster_dm_file_id'] ) : 0;
		if ( 0 === $file_id ) {
			delete_post_meta( $post_id, self::META_KEY );
			$old_path = Pinster_DM_Secure_Storage::get_secure_file_path( $post_id );
			if ( $old_path && file_exists( $old_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				@unlink( $old_path );
			}
			delete_post_meta( $post_id, Pinster_DM_Secure_Storage::META_KEY_PATH );
			return;
		}

		$mime = get_post_mime_type( $file_id );
		if ( ! in_array( $mime, self::ALLOWED_MIMES, true ) ) {
			return;
		}
		update_post_meta( $post_id, self::META_KEY, $file_id );

		$opts = get_option( 'pinster_dm_settings', array( 'secure_storage' => 1 ) );
		$use_secure = ! empty( $opts['secure_storage'] );
		if ( $use_secure ) {
			$old_path = Pinster_DM_Secure_Storage::get_secure_file_path( $post_id );
			if ( $old_path && file_exists( $old_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				@unlink( $old_path );
			}
			$rel = Pinster_DM_Secure_Storage::copy_to_secure( $file_id, $post_id );
			if ( false !== $rel ) {
				update_post_meta( $post_id, Pinster_DM_Secure_Storage::META_KEY_PATH, $rel );
				wp_delete_attachment( $file_id, true );
				update_post_meta( $post_id, self::META_KEY, 0 );
			}
		} else {
			delete_post_meta( $post_id, Pinster_DM_Secure_Storage::META_KEY_PATH );
		}
	}
}
