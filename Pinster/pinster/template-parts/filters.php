<?php
/**
 * Filter UI: category and style (chips/dropdowns).
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! pinster_dm_active() || ! taxonomy_exists( 'resume_category' ) ) {
	return;
}

// Use current page URL as base so filters work on both front page and archive (stay on same page).
if ( is_post_type_archive( 'resume_template' ) ) {
	$base_url = get_post_type_archive_link( 'resume_template' );
} elseif ( is_front_page() ) {
	$page_on_front = get_option( 'page_on_front' );
	$base_url = $page_on_front ? get_permalink( (int) $page_on_front ) : home_url( '/' );
} else {
	$base_url = home_url( '/' );
}
if ( ! $base_url || is_wp_error( $base_url ) ) {
	$base_url = home_url( '/' );
}
$base_url = remove_query_arg( array( 's', 'resume_category', 'resume_style', 'paged' ), $base_url );
$current_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$current_cat    = isset( $_GET['resume_category'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_category'] ) ) : '';
$current_style = isset( $_GET['resume_style'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_style'] ) ) : '';

$categories = get_terms( array(
	'taxonomy'   => 'resume_category',
	'hide_empty' => true,
) );
$styles = get_terms( array(
	'taxonomy'   => 'resume_style',
	'hide_empty' => true,
) );
$has_filters = ( $categories && ! is_wp_error( $categories ) ) || ( $styles && ! is_wp_error( $styles ) );
if ( ! $has_filters ) {
	return;
}
?>
<nav class="pinster-filters" aria-label="<?php esc_attr_e( 'Filter templates', 'pinster' ); ?>">
	<div class="pinster-container pinster-filters-inner">
		<div class="pinster-filter-group">
			<span class="pinster-filter-label"><?php esc_html_e( 'Category', 'pinster' ); ?></span>
			<ul class="pinster-filter-list">
				<li>
					<a class="pinster-filter-chip <?php echo '' === $current_cat ? 'pinster-filter-chip-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 's' => $current_search, 'resume_style' => $current_style ), $base_url ) ); ?>">
						<?php esc_html_e( 'All', 'pinster' ); ?>
					</a>
				</li>
				<?php
				if ( $categories && ! is_wp_error( $categories ) ) {
					foreach ( $categories as $term ) {
						$is_active = $current_cat === $term->slug;
						$url = add_query_arg( array(
							's'               => $current_search,
							'resume_category' => $term->slug,
							'resume_style'    => $current_style,
						), $base_url );
						?>
						<li>
							<a class="pinster-filter-chip <?php echo $is_active ? 'pinster-filter-chip-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<div class="pinster-filter-group">
			<span class="pinster-filter-label"><?php esc_html_e( 'Style', 'pinster' ); ?></span>
			<ul class="pinster-filter-list">
				<li>
					<a class="pinster-filter-chip <?php echo '' === $current_style ? 'pinster-filter-chip-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 's' => $current_search, 'resume_category' => $current_cat ), $base_url ) ); ?>">
						<?php esc_html_e( 'All', 'pinster' ); ?>
					</a>
				</li>
				<?php
				if ( $styles && ! is_wp_error( $styles ) ) {
					foreach ( $styles as $term ) {
						$is_active = $current_style === $term->slug;
						$url = add_query_arg( array(
							's'               => $current_search,
							'resume_category' => $current_cat,
							'resume_style'    => $term->slug,
						), $base_url );
						?>
						<li>
							<a class="pinster-filter-chip <?php echo $is_active ? 'pinster-filter-chip-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $term->name ); ?>
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	</div>
</nav>
