<?php
/**
 * Front page: hero, search, filters, masonry grid.
 *
 * @package Pinster
 */

get_header();

get_template_part( 'template-parts/hero-search' );
?>

<main id="main" class="pinster-main" role="main">
	<?php do_action( 'pinster_before_template_grid' ); ?>
	<?php if ( pinster_dm_active() ) : ?>
		<?php get_template_part( 'template-parts/filters' ); ?>
		<?php
		$query = new WP_Query( Pinster_Query::get_resume_templates_args() );
		get_template_part( 'template-parts/masonry-grid', null, array( 'query' => $query ) );
		wp_reset_postdata();
		?>
	<?php else : ?>
		<div class="pinster-container pinster-notice-wrap">
			<p class="pinster-notice">
				<?php esc_html_e( 'Install and activate Pinster Download Manager to display resume templates.', 'pinster' ); ?>
			</p>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
