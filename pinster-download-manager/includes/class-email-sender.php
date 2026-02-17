<?php
/**
 * Send template file via email (gated download).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Email_Sender
 */
class Pinster_DM_Email_Sender {

	/**
	 * Send resume template to recipient.
	 *
	 * Primary: email with attachment.
	 * Fallback: email with secure download link (24h expiry).
	 *
	 * @param string $email       Recipient email.
	 * @param int    $template_id Resume template post ID.
	 * @return bool True on success.
	 */
	public static function send_template_to_email( $email, $template_id ) {
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return false;
		}
		$post = get_post( $template_id );
		if ( ! $post || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}

		$file_path = Pinster_DM_Secure_Storage::get_secure_file_path( $template_id );
		if ( ! $file_path ) {
			$file_id = get_post_meta( $template_id, Pinster_DM_Meta_Box_File::META_KEY, true );
			$file_id = absint( $file_id );
			if ( $file_id ) {
				$file_path = get_attached_file( $file_id );
			}
		}

		$opts = get_option( 'pinster_dm_email', array() );
		$subject = isset( $opts['subject'] ) ? $opts['subject'] : __( 'Your resume template from {site_name}', 'pinster-download-manager' );
		$body = isset( $opts['body'] ) ? $opts['body'] : __( "Hello,\n\nPlease find your requested resume template attached.\n\nTemplate: {template_name}\n\n— {site_name}", 'pinster-download-manager' );

		$subject = str_replace(
			array( '{site_name}', '{template_name}' ),
			array( get_bloginfo( 'name' ), get_the_title( $template_id ) ),
			$subject
		);
		$body = str_replace(
			array( '{site_name}', '{template_name}' ),
			array( get_bloginfo( 'name' ), get_the_title( $template_id ) ),
			$body
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		if ( $file_path && file_exists( $file_path ) ) {
			$sent_with_attachment = wp_mail( $email, $subject, $body, $headers, array( $file_path ) );
			if ( $sent_with_attachment ) {
				return true;
			}
		}

		$download_link = Pinster_DM_Download_Handler::build_download_url( $template_id, DAY_IN_SECONDS );
		if ( '' === $download_link ) {
			return false;
		}

		$fallback_body = $body . "\n\n" . sprintf(
			/* translators: %s: secure download URL. */
			__( 'Direct download link (valid for 24 hours): %s', 'pinster-download-manager' ),
			$download_link
		);

		return wp_mail( $email, $subject, $fallback_body, $headers );
	}
}
<?php
/**
 * Send template file via email (gated download).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Email_Sender
 */
class Pinster_DM_Email_Sender {

	/**
	 * Send resume template file to email.
	 *
	 * @param string $email       Recipient email.
	 * @param int    $template_id Resume template post ID.
	 * @return bool True on success.
	 */
	public static function send_template_to_email( $email, $template_id ) {
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return false;
		}
		$post = get_post( $template_id );
		if ( ! $post || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}

		$file_path = Pinster_DM_Secure_Storage::get_secure_file_path( $template_id );
		if ( ! $file_path ) {
			$file_id = get_post_meta( $template_id, Pinster_DM_Meta_Box_File::META_KEY, true );
			$file_id = absint( $file_id );
			if ( $file_id ) {
				$file_path = get_attached_file( $file_id );
			}
		}
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		$opts = get_option( 'pinster_dm_email', array() );
		$subject = isset( $opts['subject'] ) ? $opts['subject'] : __( 'Your resume template from {site_name}', 'pinster-download-manager' );
		$body = isset( $opts['body'] ) ? $opts['body'] : __( "Hello,\n\nPlease find your requested resume template attached.\n\nTemplate: {template_name}\n\n— {site_name}", 'pinster-download-manager' );

		$subject = str_replace(
			array( '{site_name}', '{template_name}' ),
			array( get_bloginfo( 'name' ), get_the_title( $template_id ) ),
			$subject
		);
		$body = str_replace(
			array( '{site_name}', '{template_name}' ),
			array( get_bloginfo( 'name' ), get_the_title( $template_id ) ),
			$body
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$filename = basename( $file_path );
		$attachments = array( $file_path );

		return wp_mail( $email, $subject, $body, $headers, $attachments );
	}
}
