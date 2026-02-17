<?php
/**
 * Archive template for resume templates.
 *
 * @package Pinster
 */

get_header();

get_template_part( 'template-parts/hero-search' );
?>

<main id="main" class="pinster-main" role="main">
	<?php get_template_part( 'template-parts/filters' ); ?>
	<?php
	global $wp_query;
	get_template_part( 'template-parts/masonry-grid', null, array( 'query' => $wp_query ) );
	?>
</main>

<?php
get_footer();
