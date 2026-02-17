<?php
/**
 * Default content template.
 *
 * @package Pinster
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'pinster-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="pinster-post-card-media">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
		</div>
	<?php endif; ?>
	<div class="pinster-post-card-body">
		<h2 class="pinster-post-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="pinster-post-card-excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
