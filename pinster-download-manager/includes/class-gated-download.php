<?php
/**
 * Gated download: collect email, then send file via email.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Gated_Download
 */
class Pinster_DM_Gated_Download {

	/**
	 * Query var for gated download page.
	 *
	 * @var string
	 */
	const QVAR = 'pinster_gated';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Gated_Download|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Gated_Download
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
		add_action( 'template_redirect', array( $this, 'handle_gated_page' ), 0 );
		add_action( 'template_redirect', array( $this, 'handle_gated_submit' ), 0 );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'wp_ajax_pinster_gated_submit', array( $this, 'ajax_gated_submit' ) );
		add_action( 'wp_ajax_nopriv_pinster_gated_submit', array( $this, 'ajax_gated_submit' ) );
	}

	/**
	 * AJAX handler for gated download form (modal submit).
	 */
	public function ajax_gated_submit() {
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['template_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'pinster-download-manager' ) ) );
		}
		$template_id = absint( $_POST['template_id'] );
		if ( ! $template_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pinster_gated_' . $template_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'pinster-download-manager' ) ) );
		}

		// Honeypot (anti-bot): if filled, pretend success.
		$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
		if ( '' !== trim( $company ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'We have sent the template to your email. Please check your inbox and spam folder.', 'pinster-download-manager' ),
					'sent'    => true,
				)
			);
		}

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		if ( '' === trim( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter your name.', 'pinster-download-manager' ) ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'pinster-download-manager' ) ) );
		}
		$privacy = isset( $_POST['privacy'] ) ? absint( $_POST['privacy'] ) : 0;
		if ( 1 !== $privacy ) {
			wp_send_json_error( array( 'message' => __( 'Please accept the Privacy Policy to continue.', 'pinster-download-manager' ) ) );
		}

		// Basic rate limit to reduce abuse/spam (per IP + template).
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rate_key = 'pinster_dm_gate_' . md5( $ip . '|' . $template_id );
		if ( get_transient( $rate_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Please wait a minute before requesting again.', 'pinster-download-manager' ) ) );
		}
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

		$post = get_post( $template_id );
		if ( ! $post || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			wp_send_json_error( array( 'message' => __( 'Template not found.', 'pinster-download-manager' ) ) );
		}
		Pinster_DM_Subscribers::add( $email, $template_id, $name, true );
		$sent = Pinster_DM_Email_Sender::send_template_to_email( $email, $template_id );
		$handler = Pinster_DM_Download_Handler::instance();
		$handler->increment_count_public_callback( $template_id );
		wp_send_json_success( array(
			'message' => $sent
				? __( 'We have sent the template to your email. Please check your inbox and spam folder.', 'pinster-download-manager' )
				: __( 'There was a problem sending the email. Please try again later.', 'pinster-download-manager' ),
			'sent'    => $sent,
		) );
	}

	/**
	 * Add query var.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = self::QVAR;
		$vars[] = 'pinster_template_id';
		return $vars;
	}

	/**
	 * Redirect gated URL to single template page with #download (modal opens there).
	 */
	public function handle_gated_page() {
		$template_id = get_query_var( 'pinster_template_id' );
		$is_gated = get_query_var( self::QVAR );
		if ( ! $template_id || ! $is_gated ) {
			return;
		}
		$template_id = absint( $template_id );
		if ( ! $template_id ) {
			return;
		}
		$post = get_post( $template_id );
		if ( ! $post || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		wp_safe_redirect( get_permalink( $template_id ) . '#download' );
		exit;
	}

	/**
	 * Handle form submit: save subscriber, send email, show thank you.
	 */
	public function handle_gated_submit() {
		if ( ! isset( $_POST['pinster_gated_download'] ) || ! isset( $_POST['pinster_template_id'] ) || ! isset( $_POST['pinster_gated_nonce'] ) ) {
			return;
		}
		$template_id = absint( $_POST['pinster_template_id'] );
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pinster_gated_nonce'] ) ), 'pinster_gated_' . $template_id ) ) {
			wp_die( esc_html__( 'Invalid request.', 'pinster-download-manager' ), '', array( 'response' => 403 ) );
		}
		$name = isset( $_POST['pinster_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pinster_name'] ) ) : '';
		$email = isset( $_POST['pinster_email'] ) ? sanitize_email( wp_unslash( $_POST['pinster_email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			$this->render_form( $template_id, __( 'Please enter a valid email address.', 'pinster-download-manager' ) );
			exit;
		}
		$privacy = isset( $_POST['pinster_privacy'] ) ? absint( $_POST['pinster_privacy'] ) : 0;
		if ( '' === trim( $name ) || 1 !== $privacy ) {
			$this->render_form( $template_id, __( 'Please complete all required fields and accept the Privacy Policy.', 'pinster-download-manager' ) );
			exit;
		}
		Pinster_DM_Subscribers::add( $email, $template_id, $name, true );
		$sent = Pinster_DM_Email_Sender::send_template_to_email( $email, $template_id );
		$handler = Pinster_DM_Download_Handler::instance();
		$handler->increment_count_public_callback( $template_id );
		$this->render_thank_you( $template_id, $sent );
		exit;
	}

	/**
	 * Render email form.
	 *
	 * @param int         $template_id Template post ID.
	 * @param string|null $error       Optional error message.
	 */
	private function render_form( $template_id, $error = null ) {
		$action = add_query_arg( array( self::QVAR => 1, 'pinster_template_id' => $template_id ), home_url( '/' ) );
		$nonce = wp_nonce_field( 'pinster_gated_' . $template_id, 'pinster_gated_nonce', true, false );
		$title = get_the_title( $template_id );
		$privacy_url = function_exists( 'wp_privacy_policy_url' ) ? wp_privacy_policy_url() : '';
		if ( ! $privacy_url ) {
			$privacy_url = home_url( '/privacy-policy/' );
		}
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( sprintf( __( 'Download: %s', 'pinster-download-manager' ), $title ) ); ?></title>
			<?php wp_head(); ?>
			<style>
				.pinster-gated { max-width: 400px; margin: 4rem auto; padding: 2rem; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
				.pinster-gated h1 { font-size: 1.5rem; margin-bottom: 1rem; }
				.pinster-gated .pinster-gated-error { color: #c00; margin-bottom: 1rem; }
				.pinster-gated label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
				.pinster-gated input[type="email"] { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 6px; }
				.pinster-gated button { padding: 0.75rem 1.5rem; background: #e60023; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
			</style>
		</head>
		<body>
		<div class="pinster-gated">
			<h1><?php echo esc_html( sprintf( __( 'Download: %s', 'pinster-download-manager' ), $title ) ); ?></h1>
			<p><?php esc_html_e( 'Enter your email to receive this resume template. We will send the file to your inbox.', 'pinster-download-manager' ); ?></p>
			<?php if ( $error ) : ?>
				<p class="pinster-gated-error"><?php echo esc_html( $error ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( $action ); ?>">
				<?php echo $nonce; ?>
				<input type="hidden" name="pinster_gated_download" value="1" />
				<input type="hidden" name="pinster_template_id" value="<?php echo esc_attr( (string) $template_id ); ?>" />
				<label for="pinster_name"><?php esc_html_e( 'Your name', 'pinster-download-manager' ); ?></label>
				<input type="text" id="pinster_name" name="pinster_name" required value="<?php echo esc_attr( isset( $_POST['pinster_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pinster_name'] ) ) : '' ); ?>" />
				<label for="pinster_email"><?php esc_html_e( 'Email address', 'pinster-download-manager' ); ?></label>
				<input type="email" id="pinster_email" name="pinster_email" required value="<?php echo esc_attr( isset( $_POST['pinster_email'] ) ? sanitize_text_field( wp_unslash( $_POST['pinster_email'] ) ) : '' ); ?>" />
				<p style="margin:0 0 1rem;">
					<label>
						<input type="checkbox" name="pinster_privacy" value="1" required />
						<?php
						printf(
							wp_kses(
								__( 'I agree to the <a href="%s" target="_blank" rel="noopener">Privacy Policy</a>.', 'pinster-download-manager' ),
								array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
							),
							esc_url( $privacy_url )
						);
						?>
					</label>
				</p>
				<button type="submit"><?php esc_html_e( 'Send me the template', 'pinster-download-manager' ); ?></button>
			</form>
		</div>
		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Render thank you after submit.
	 *
	 * @param int  $template_id Template post ID.
	 * @param bool $sent        Whether email was sent.
	 */
	private function render_thank_you( $template_id, $sent ) {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Check your email', 'pinster-download-manager' ); ?></title>
			<?php wp_head(); ?>
			<style>
				.pinster-gated { max-width: 400px; margin: 4rem auto; padding: 2rem; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
				.pinster-gated h1 { font-size: 1.5rem; margin-bottom: 1rem; }
				.pinster-gated a { color: #e60023; }
			</style>
		</head>
		<body>
		<div class="pinster-gated">
			<h1><?php esc_html_e( 'Check your email', 'pinster-download-manager' ); ?></h1>
			<?php if ( $sent ) : ?>
				<p><?php esc_html_e( 'We have sent the resume template to your email address. Please check your inbox (and spam folder).', 'pinster-download-manager' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'There was a problem sending the email. Please try again later or contact us.', 'pinster-download-manager' ); ?></p>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( get_permalink( $template_id ) ); ?>"><?php esc_html_e( 'Back to template', 'pinster-download-manager' ); ?></a></p>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'pinster-download-manager' ); ?></a></p>
		</div>
		<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}
}
