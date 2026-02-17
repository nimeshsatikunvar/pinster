<?php
/**
 * Default content template.
 *
 * @package Pinster
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'pinster-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="pinster-card-media">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
		</div>
	<?php endif; ?>
	<div class="pinster-card-body">
		<h2 class="pinster-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php the_excerpt(); ?>
	</div>
</article>
