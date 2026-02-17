<?php
/**
 * Main template fallback.
 *
 * @package Pinster
 */

get_header();
?>

<main id="main" class="pinster-main" role="main">
	<div class="pinster-container">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );
			}
			the_posts_pagination();
		} else {
			get_template_part( 'template-parts/content', 'none' );
		}
		?>
	</div>
</main>

<?php
get_footer();
