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
						<p class="pinster-single-kicker"><?php esc_html_e( 'Template detail', 'pinster' ); ?></p>
						<h1 class="pinster-single-title"><?php the_title(); ?></h1>
						<?php if ( ( $categories && ! is_wp_error( $categories ) ) || ( $styles && ! is_wp_error( $styles ) ) ) : ?>
							<ul class="pinster-single-meta" role="list">
								<?php
								if ( $categories && ! is_wp_error( $categories ) ) {
									foreach ( $categories as $category ) {
										echo '<li class="pinster-single-pill">' . esc_html__( 'Category', 'pinster' ) . ': ' . esc_html( $category->name ) . '</li>';
									}
								}
								if ( $styles && ! is_wp_error( $styles ) ) {
									foreach ( $styles as $style ) {
										echo '<li class="pinster-single-pill">' . esc_html__( 'Style', 'pinster' ) . ': ' . esc_html( $style->name ) . '</li>';
									}
								}
								?>
							</ul>
						<?php endif; ?>
					</header>
					<?php do_action( 'pinster_single_before_content' ); ?>

					<div class="pinster-single-content">
						<div class="pinster-single-preview">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="pinster-single-thumbnail">
									<?php the_post_thumbnail( 'large' ); ?>
								</div>
							<?php else : ?>
								<div class="pinster-single-thumbnail pinster-card-placeholder">
									<span aria-hidden="true"><?php esc_html_e( 'No preview', 'pinster' ); ?></span>
								</div>
							<?php endif; ?>
							<div class="pinster-single-content-text">
								<?php the_content(); ?>
							</div>
						</div>
						<aside class="pinster-single-body">
							<?php if ( $download_url ) : ?>
								<div id="download" class="pinster-single-download">
									<h2 class="pinster-single-download-title"><?php esc_html_e( 'Get this template', 'pinster' ); ?></h2>
									<p><?php esc_html_e( 'Instant access to ATS-friendly files in Word and PDF format.', 'pinster' ); ?></p>
									<?php if ( pinster_is_gated_download() ) : ?>
										<button type="button" class="pinster-btn pinster-btn-download pinster-btn-large pinster-gated-trigger" data-template-id="<?php echo esc_attr( (string) $post_id ); ?>" data-template-title="<?php echo esc_attr( get_the_title() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'pinster_gated_' . $post_id ) ); ?>">
											<?php esc_html_e( 'Download this template', 'pinster' ); ?>
										</button>
									<?php else : ?>
										<a href="<?php echo esc_url( $download_url ); ?>" class="pinster-btn pinster-btn-download pinster-btn-large" download>
											<?php esc_html_e( 'Download this template', 'pinster' ); ?>
										</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</aside>
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
