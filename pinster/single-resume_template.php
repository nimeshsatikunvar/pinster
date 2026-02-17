<?php
/**
 * Single resume template view.
 *
 * @package Pinster
 */

get_header();
?>

<main id="main" class="pinster-main" role="main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php
			$post_id      = get_the_ID();
			$download_url = pinster_get_download_url( $post_id );
			$categories   = get_the_terms( $post_id, 'resume_category' );
			$styles       = get_the_terms( $post_id, 'resume_style' );
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'pinster-single-template' ); ?>>
				<div class="pinster-container pinster-single-inner">
					<header class="pinster-single-header">
						<h1 class="pinster-single-title"><?php the_title(); ?></h1>
						<?php if ( ( $categories && ! is_wp_error( $categories ) ) || ( $styles && ! is_wp_error( $styles ) ) ) : ?>
							<div class="pinster-single-meta">
								<?php
								if ( $categories && ! is_wp_error( $categories ) ) {
									$cat_names = wp_list_pluck( $categories, 'name' );
									echo '<span class="pinster-meta-label">' . esc_html__( 'Category', 'pinster' ) . ':</span> ' . esc_html( implode( ', ', $cat_names ) );
								}
								if ( $styles && ! is_wp_error( $styles ) ) {
									$style_names = wp_list_pluck( $styles, 'name' );
									echo ' <span class="pinster-meta-label">' . esc_html__( 'Style', 'pinster' ) . ':</span> ' . esc_html( implode( ', ', $style_names ) );
								}
								?>
							</div>
						<?php endif; ?>
					</header>

					<?php do_action( 'pinster_single_before_content' ); ?>

					<div class="pinster-single-content">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="pinster-single-thumbnail">
								<?php the_post_thumbnail( 'large' ); ?>
							</div>
						<?php endif; ?>

						<div class="pinster-single-body">
							<?php the_content(); ?>
							<?php if ( $download_url ) : ?>
								<p id="download" class="pinster-single-download">
									<?php if ( pinster_is_gated_download() ) : ?>
										<button
											type="button"
											class="pinster-btn pinster-btn-download pinster-btn-large pinster-gated-trigger"
											data-template-id="<?php echo esc_attr( (string) $post_id ); ?>"
											data-template-title="<?php echo esc_attr( get_the_title() ); ?>"
											data-nonce="<?php echo esc_attr( wp_create_nonce( 'pinster_gated_' . $post_id ) ); ?>"
										>
											<?php esc_html_e( 'Download this template', 'pinster' ); ?>
										</button>
									<?php else : ?>
										<a href="<?php echo esc_url( $download_url ); ?>" class="pinster-btn pinster-btn-download pinster-btn-large" download>
											<?php esc_html_e( 'Download this template', 'pinster' ); ?>
										</a>
									<?php endif; ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</article>
		<?php endwhile; ?>

		<?php
		if ( pinster_is_gated_download() ) {
			get_template_part( 'template-parts/modal', 'gated-download' );
		}
		get_template_part( 'template-parts/related-templates' );
		?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</main>

<?php
get_footer();
