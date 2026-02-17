<?php
/**
 * Hero section with search form.
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_value = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
if ( pinster_dm_active() && is_front_page() ) {
	$page_on_front = get_option( 'page_on_front' );
	$action_url = $page_on_front ? get_permalink( (int) $page_on_front ) : home_url( '/' );
} elseif ( pinster_dm_active() ) {
	$action_url = get_post_type_archive_link( 'resume_template' );
} else {
	$action_url = home_url( '/' );
}
if ( ! $action_url || is_wp_error( $action_url ) ) {
	$action_url = home_url( '/' );
}
?>
<section class="pinster-hero" aria-labelledby="pinster-hero-heading">
	<div class="pinster-container pinster-hero-inner">
		<h1 id="pinster-hero-heading" class="pinster-hero-title">
			<?php esc_html_e( 'Free Resume Templates', 'pinster' ); ?>
		</h1>
		<p class="pinster-hero-desc">
			<?php esc_html_e( 'Download professional resume templates for any job. PDF & Word.', 'pinster' ); ?>
		</p>
		<form class="pinster-hero-search" role="search" method="get" action="<?php echo esc_url( $action_url ); ?>">
			<label for="pinster-search-input" class="pinster-sr-only"><?php esc_html_e( 'Search templates', 'pinster' ); ?></label>
			<input type="search" id="pinster-search-input" class="pinster-search-input" name="s" value="<?php echo esc_attr( $search_value ); ?>" placeholder="<?php esc_attr_e( 'Search templates, roles, and styles...', 'pinster' ); ?>" />
			<button type="submit" class="pinster-search-submit"><?php esc_html_e( 'Find Template', 'pinster' ); ?></button>
		</form>
		<ul class="pinster-hero-meta" aria-label="<?php esc_attr_e( 'Platform highlights', 'pinster' ); ?>">
			<li><?php esc_html_e( 'Premium curated designs', 'pinster' ); ?></li>
			<li><?php esc_html_e( 'Instant Word & PDF downloads', 'pinster' ); ?></li>
			<li><?php esc_html_e( 'ATS-friendly layouts', 'pinster' ); ?></li>
		</ul>
	</div>
	<?php do_action( 'pinster_after_hero' ); ?>
</section>
