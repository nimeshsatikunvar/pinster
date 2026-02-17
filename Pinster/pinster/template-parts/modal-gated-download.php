<?php
/**
 * Gated download modal: email form (shown on single template page).
 *
 * @package Pinster
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$post_id = get_the_ID();
$nonce = wp_create_nonce( 'pinster_gated_' . $post_id );
$privacy_url = function_exists( 'wp_privacy_policy_url' ) ? wp_privacy_policy_url() : '';
if ( ! $privacy_url ) {
	$privacy_url = home_url( '/privacy-policy/' );
}
?>
<div id="pinster-gated-modal" class="pinster-modal" role="dialog" aria-modal="true" aria-labelledby="pinster-gated-modal-title" aria-hidden="true">
	<div class="pinster-modal-backdrop" data-close-modal></div>
	<div class="pinster-modal-content">
		<button type="button" class="pinster-modal-close" aria-label="<?php esc_attr_e( 'Close', 'pinster' ); ?>" data-close-modal>&times;</button>
		<h2 id="pinster-gated-modal-title" class="pinster-modal-title"><?php esc_html_e( 'Get this template by email', 'pinster' ); ?></h2>
		<p class="pinster-modal-desc"><?php esc_html_e( 'Enter your email and we’ll send the resume template to your inbox.', 'pinster' ); ?></p>
		<div class="pinster-modal-message" id="pinster-gated-message" hidden></div>
		<form id="pinster-gated-form" class="pinster-gated-form">
			<input type="hidden" name="template_id" id="pinster-gated-template-id" value="<?php echo esc_attr( (string) $post_id ); ?>" />
			<input type="hidden" name="nonce" id="pinster-gated-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
			<label for="pinster-gated-name"><?php esc_html_e( 'Your name', 'pinster' ); ?></label>
			<input type="text" id="pinster-gated-name" name="name" autocomplete="name" required />

			<label for="pinster-gated-email"><?php esc_html_e( 'Email address', 'pinster' ); ?></label>
			<input type="email" id="pinster-gated-email" name="email" required />

			<!-- Honeypot (anti-bot) -->
			<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
				<label for="pinster-gated-company"><?php esc_html_e( 'Company', 'pinster' ); ?></label>
				<input type="text" id="pinster-gated-company" name="company" tabindex="-1" autocomplete="off" />
			</div>

			<div class="pinster-gated-privacy">
				<input type="checkbox" id="pinster-gated-privacy" name="privacy" value="1" required />
				<label for="pinster-gated-privacy">
					<?php
					printf(
						/* translators: %s: privacy policy URL */
						wp_kses(
							__( 'I agree to the <a href="%s" target="_blank" rel="noopener">Privacy Policy</a>.', 'pinster' ),
							array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
						),
						esc_url( $privacy_url )
					);
					?>
				</label>
			</div>
			<button type="submit" class="pinster-btn pinster-btn-download"><?php esc_html_e( 'Send me the template', 'pinster' ); ?></button>
		</form>
	</div>
</div>
