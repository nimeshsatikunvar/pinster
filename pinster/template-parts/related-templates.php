<?php
/**
 * Related resume templates (same category/style) for single template page.
 *
 * @package Pinster
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! pinster_dm_active() ) {
	return;
}
$post_id = get_the_ID();
$related = pinster_get_related_templates( $post_id, 6 );
if ( ! $related->have_posts() ) {
	return;
}
?>
<section class="pinster-related" aria-labelledby="pinster-related-heading">
	<div class="pinster-container">
		<h2 id="pinster-related-heading" class="pinster-related-title"><?php esc_html_e( 'You might also like', 'pinster' ); ?></h2>
		<ul class="pinster-grid pinster-related-grid" role="list">
			<?php
			while ( $related->have_posts() ) {
				$related->the_post();
				$tid = get_the_ID();
				$dl_url = pinster_get_download_url( $tid );
				$cats = get_the_terms( $tid, 'resume_category' );
				$stys = get_the_terms( $tid, 'resume_style' );
				?>
				<li class="pinster-grid-item">
					<article class="pinster-card">
						<a href="<?php the_permalink(); ?>" class="pinster-card-link">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="pinster-card-image">
									<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
								</div>
							<?php else : ?>
								<div class="pinster-card-image pinster-card-placeholder">
									<span aria-hidden="true"><?php esc_html_e( 'No preview', 'pinster' ); ?></span>
								</div>
							<?php endif; ?>
							<div class="pinster-card-content">
								<h3 class="pinster-card-title"><?php the_title(); ?></h3>
								<?php if ( ( $cats && ! is_wp_error( $cats ) ) || ( $stys && ! is_wp_error( $stys ) ) ) : ?>
									<div class="pinster-card-badges">
										<?php
										if ( $cats && ! is_wp_error( $cats ) ) {
											foreach ( array_slice( $cats, 0, 2 ) as $t ) {
												echo '<span class="pinster-badge">' . esc_html( $t->name ) . '</span>';
											}
										}
										if ( $stys && ! is_wp_error( $stys ) ) {
											foreach ( array_slice( $stys, 0, 2 ) as $t ) {
												echo '<span class="pinster-badge">' . esc_html( $t->name ) . '</span>';
											}
										}
										?>
									</div>
								<?php endif; ?>
							</div>
						</a>
						<?php if ( $dl_url ) : ?>
							<div class="pinster-card-actions">
								<?php if ( pinster_is_gated_download() ) : ?>
									<a href="<?php echo esc_url( get_permalink() . '#download' ); ?>" class="pinster-btn pinster-btn-download"><?php esc_html_e( 'Download', 'pinster' ); ?></a>
								<?php else : ?>
									<a href="<?php echo esc_url( $dl_url ); ?>" class="pinster-btn pinster-btn-download" download><?php esc_html_e( 'Download', 'pinster' ); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>
				</li>
				<?php
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
