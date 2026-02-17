<?php
/**
 * Footer template.
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'pinster_before_footer' ); ?>
<footer class="pinster-footer" role="contentinfo">
	<div class="pinster-container pinster-footer-inner">
		<p class="pinster-footer-credit">
			<?php
			printf(
				/* translators: %s: site name */
				esc_html__( '&copy; %s. Free resume templates for everyone.', 'pinster' ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
