<?php
/**
 * Single resume template view.
 *
 * @package Pinster
 */

get_header();

$single_post = null;
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$single_post = get_post();
		break;
	}
}
if ( ! $single_post && get_queried_object_id() ) {
	$single_post = get_post( get_queried_object_id() );
	if ( $single_post && 'resume_template' === $single_post->post_type && 'publish' === $single_post->post_status ) {
		global $wp_query;
		$wp_query->posts = array( $single_post );
		$wp_query->post_count = 1;
		$wp_query->queried_object = $single_post;
		$wp_query->queried_object_id = $single_post->ID;
	}
}
if ( ! $single_post || 'resume_template' !== $single_post->post_type ) {
	get_template_part( 'template-parts/content', 'none' );
	get_footer();
	return;
}
setup_postdata( $single_post );
$post_id      = $single_post->ID;
$download_url = pinster_get_download_url( $post_id );
$categories   = get_the_terms( $post_id, 'resume_category' );
$styles       = get_the_terms( $post_id, 'resume_style' );
?>
	<main id="main" class="pinster-main" role="main">
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
							<p class="pinster-single-download">
								<?php if ( pinster_is_gated_download() ) : ?>
									<button type="button" class="pinster-btn pinster-btn-download pinster-btn-large pinster-gated-trigger" data-template-id="<?php echo esc_attr( (string) $post_id ); ?>" data-template-title="<?php echo esc_attr( get_the_title() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'pinster_gated_' . $post_id ) ); ?>">
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
		<?php
		if ( pinster_is_gated_download() ) {
			get_template_part( 'template-parts/modal', 'gated-download' );
		}
		get_template_part( 'template-parts/related-templates' );
		?>
	</main>
<?php
wp_reset_postdata();

get_footer();
