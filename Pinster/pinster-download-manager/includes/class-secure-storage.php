<?php
/**
 * Store template files in secure directory (not web-accessible).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Secure_Storage
 */
class Pinster_DM_Secure_Storage {

	/**
	 * Meta key for secure file path (relative to secure base dir or absolute).
	 *
	 * @var string
	 */
	const META_KEY_PATH = '_pinster_secure_file_path';

	/**
	 * Copy attachment file to secure dir and return stored path.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $template_id   Resume template post ID.
	 * @return string|false Relative path (e.g. template-123/file.pdf) or false.
	 */
	public static function copy_to_secure( $attachment_id, $template_id ) {
		$template_id = absint( $template_id );
		$attachment_id = absint( $attachment_id );
		if ( ! $template_id || ! $attachment_id ) {
			return false;
		}
		$src = get_attached_file( $attachment_id );
		if ( ! $src || ! file_exists( $src ) ) {
			return false;
		}
		$mime = get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime, Pinster_DM_Meta_Box_File::ALLOWED_MIMES, true ) ) {
			return false;
		}
		Pinster_DM_Installer::ensure_secure_dir();
		$dir = Pinster_DM_Installer::get_secure_dir_for_template( $template_id );
		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}
		$filename = basename( $src );
		$dest = $dir . $filename;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.copy_copy
		if ( ! copy( $src, $dest ) ) {
			return false;
		}
		return 'template-' . $template_id . '/' . $filename;
	}

	/**
	 * Get full filesystem path for a template's secure file.
	 *
	 * @param int $template_id Post ID.
	 * @return string|null Full path or null if not in secure storage.
	 */
	public static function get_secure_file_path( $template_id ) {
		$rel = get_post_meta( $template_id, self::META_KEY_PATH, true );
		if ( ! is_string( $rel ) || '' === trim( $rel ) ) {
			return null;
		}
		$path = Pinster_DM_Installer::get_secure_base_dir() . $rel;
		return file_exists( $path ) ? $path : null;
	}

	/**
	 * Get MIME type from filename for secure file.
	 *
	 * @param string $path Full path.
	 * @return string
	 */
	public static function get_mime_for_path( $path ) {
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( 'pdf' === $ext ) {
			return 'application/pdf';
		}
		if ( 'docx' === $ext ) {
			return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
		}
		return 'application/octet-stream';
	}
}
