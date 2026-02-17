<?php
/**
 * Header template.
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'pinster-site' ); ?>>
<?php wp_body_open(); ?>

<header class="pinster-header" role="banner">
	<div class="pinster-container pinster-header-inner">
		<div class="pinster-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pinster-logo-link" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<nav class="pinster-nav" aria-label="<?php esc_attr_e( 'Primary', 'pinster' ); ?>">
			<ul class="pinster-nav-list">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'pinster' ); ?></a></li>
				<?php if ( pinster_dm_active() ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'resume_template' ) ); ?>"><?php esc_html_e( 'All Templates', 'pinster' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</div>
</header>
