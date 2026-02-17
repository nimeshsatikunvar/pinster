<?php
/**
 * Masonry-style grid of resume templates.
 *
 * @package Pinster
 *
 * @var WP_Query $args['query'] Query object (optional). Defaults to global $wp_query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : $GLOBALS['wp_query'];

$current_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$current_cat    = isset( $_GET['resume_category'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_category'] ) ) : '';
$current_style  = isset( $_GET['resume_style'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_style'] ) ) : '';

$active_filters = array();
if ( '' !== $current_cat && taxonomy_exists( 'resume_category' ) ) {
	$category_term = get_term_by( 'slug', $current_cat, 'resume_category' );
	if ( $category_term && ! is_wp_error( $category_term ) ) {
		$active_filters[] = sprintf(
			/* translators: %s: selected category name. */
			esc_html__( 'Category: %s', 'pinster' ),
			esc_html( $category_term->name )
		);
	}
}

if ( '' !== $current_style && taxonomy_exists( 'resume_style' ) ) {
	$style_term = get_term_by( 'slug', $current_style, 'resume_style' );
	if ( $style_term && ! is_wp_error( $style_term ) ) {
		$active_filters[] = sprintf(
			/* translators: %s: selected style name. */
			esc_html__( 'Style: %s', 'pinster' ),
			esc_html( $style_term->name )
		);
	}
}
?>

<div class="pinster-container pinster-grid-wrap">
	<header class="pinster-results-head">
		<div>
			<h2 class="pinster-results-title"><?php esc_html_e( 'Browse Templates', 'pinster' ); ?></h2>
			<p class="pinster-results-summary">
				<?php
				printf(
					/* translators: %d: number of found templates. */
					esc_html__( '%d templates found', 'pinster' ),
					intval( $query->found_posts )
				);
				?>
			</p>
		</div>
		<?php if ( '' !== $current_search ) : ?>
			<p class="pinster-results-search">
				<?php
				printf(
					/* translators: %s: searched keyword. */
					esc_html__( 'Search: "%s"', 'pinster' ),
					esc_html( $current_search )
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( ! empty( $active_filters ) ) : ?>
		<ul class="pinster-active-filters" role="list">
			<?php foreach ( $active_filters as $active_filter ) : ?>
				<li><?php echo esc_html( $active_filter ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $query->have_posts() ) : ?>
		<ul class="pinster-grid" role="list">
			<?php
			$rendered_ids = array();
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id      = get_the_ID();
				if ( in_array( $post_id, $rendered_ids, true ) ) {
					continue;
				}
				$rendered_ids[] = $post_id;
				$download_url = pinster_get_download_url( $post_id );
				$categories   = get_the_terms( $post_id, 'resume_category' );
				$styles       = get_the_terms( $post_id, 'resume_style' );
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
								<?php if ( ( $categories && ! is_wp_error( $categories ) ) || ( $styles && ! is_wp_error( $styles ) ) ) : ?>
									<div class="pinster-card-badges">
										<?php
										if ( $categories && ! is_wp_error( $categories ) ) {
											foreach ( array_slice( $categories, 0, 2 ) as $term ) {
												echo '<span class="pinster-badge">' . esc_html( $term->name ) . '</span>';
											}
										}
										if ( $styles && ! is_wp_error( $styles ) ) {
											foreach ( array_slice( $styles, 0, 2 ) as $term ) {
												echo '<span class="pinster-badge">' . esc_html( $term->name ) . '</span>';
											}
										}
										?>
									</div>
								<?php endif; ?>
							</div>
						</a>
						<?php if ( $download_url ) : ?>
							<div class="pinster-card-actions">
								<?php if ( pinster_is_gated_download() ) : ?>
									<a href="<?php echo esc_url( get_permalink() . '#download' ); ?>" class="pinster-btn pinster-btn-download">
										<?php esc_html_e( 'Download', 'pinster' ); ?>
									</a>
								<?php else : ?>
									<a href="<?php echo esc_url( $download_url ); ?>" class="pinster-btn pinster-btn-download" download>
										<?php esc_html_e( 'Download', 'pinster' ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		$total = $query->max_num_pages;
		if ( $total > 1 ) {
			$big = 999999999;
			echo '<nav class="pinster-pagination" aria-label="' . esc_attr__( 'Templates pagination', 'pinster' ) . '">';
			echo paginate_links(
				array(
					'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'    => '?paged=%#%',
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => $total,
					'prev_text' => __( 'Previous', 'pinster' ),
					'next_text' => __( 'Next', 'pinster' ),
				)
			);
			echo '</nav>';
		}
		?>
	<?php else : ?>
		<p class="pinster-no-results"><?php esc_html_e( 'No resume templates match your filters.', 'pinster' ); ?></p>
	<?php endif; ?>
</div>
