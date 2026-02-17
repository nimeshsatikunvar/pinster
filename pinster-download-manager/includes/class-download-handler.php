<?php
/**
 * Handle download request: nonce, count, serve file.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Download_Handler
 */
class Pinster_DM_Download_Handler {

	/**
	 * Post meta key for download count.
	 *
	 * @var string
	 */
	const COUNT_META = '_pinster_download_count';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Download_Handler|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Download_Handler
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize: hook early to intercept request.
	 */
	public function init() {
		add_action( 'template_redirect', array( $this, 'maybe_serve_download' ), 1 );
	}

	/**
	 * If download request, verify and serve file; then exit.
	 */
	public function maybe_serve_download() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below with template_id.
		if ( empty( $_GET['pinster_download'] ) || empty( $_GET['template_id'] ) || empty( $_GET['nonce'] ) ) {
			return;
		}

		$template_id = absint( $_GET['template_id'] );
		$nonce       = sanitize_text_field( wp_unslash( $_GET['nonce'] ) );
		if ( ! $template_id || ! wp_verify_nonce( $nonce, 'pinster_download_' . $template_id ) ) {
			wp_die( esc_html__( 'Invalid download link.', 'pinster-download-manager' ), '', array( 'response' => 403 ) );
		}

		$expires = isset( $_GET['expires'] ) ? absint( $_GET['expires'] ) : 0;
		if ( $expires > 0 && time() > $expires ) {
			wp_die( esc_html__( 'This download link has expired. Please request a new one.', 'pinster-download-manager' ), '', array( 'response' => 403 ) );
		}

		$post = get_post( $template_id );
		if ( ! $post || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			wp_die( esc_html__( 'Template not found.', 'pinster-download-manager' ), '', array( 'response' => 404 ) );
		}

		$file_path = Pinster_DM_Secure_Storage::get_secure_file_path( $template_id );
		$mime = null;
		if ( $file_path ) {
			$mime = Pinster_DM_Secure_Storage::get_mime_for_path( $file_path );
		} else {
			$file_id = get_post_meta( $template_id, Pinster_DM_Meta_Box_File::META_KEY, true );
			$file_id = absint( $file_id );
			if ( ! $file_id ) {
				wp_die( esc_html__( 'No file available for this template.', 'pinster-download-manager' ), '', array( 'response' => 404 ) );
			}
			$file_path = get_attached_file( $file_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				wp_die( esc_html__( 'File not found.', 'pinster-download-manager' ), '', array( 'response' => 404 ) );
			}
			$mime = get_post_mime_type( $file_id );
			if ( ! in_array( $mime, Pinster_DM_Meta_Box_File::ALLOWED_MIMES, true ) ) {
				wp_die( esc_html__( 'Invalid file type.', 'pinster-download-manager' ), '', array( 'response' => 403 ) );
			}
		}

		$this->increment_count( $template_id );

		$filename = basename( $file_path );
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
		readfile( $file_path );
		exit;
	}

	/**
	 * Increment download count for a template.
	 *
	 * @param int $post_id Template post ID.
	 */
	private function increment_count( $post_id ) {
		$this->increment_count_public_callback( $post_id );
	}

	/**
	 * Public callback for incrementing count (used by gated download).
	 *
	 * @param int $post_id Template post ID.
	 */
	public function increment_count_public_callback( $post_id ) {
		$count = (int) get_post_meta( $post_id, self::COUNT_META, true );
		update_post_meta( $post_id, self::COUNT_META, $count + 1 );
	}

	/**
	 * Get download count for a template.
	 *
	 * @param int $post_id Template post ID.
	 * @return int
	 */
	public static function get_download_count( $post_id ) {
		return (int) get_post_meta( $post_id, self::COUNT_META, true );
	}


	/**
	 * Build a signed download URL.
	 *
	 * @param int $post_id Template post ID.
	 * @param int $ttl     Optional TTL in seconds (0 = no expiration).
	 * @return string
	 */
	public static function build_download_url( $post_id, $ttl = 0 ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$args = array(
			'pinster_download' => 1,
			'template_id'      => $post_id,
			'nonce'            => wp_create_nonce( 'pinster_download_' . $post_id ),
		);
		if ( $ttl > 0 ) {
			$args['expires'] = time() + absint( $ttl );
		}
		return add_query_arg( $args, home_url( '/' ) );
	}

}
