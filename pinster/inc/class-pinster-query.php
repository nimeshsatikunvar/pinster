<?php
/**
 * Build WP_Query args for resume templates (filters + search).
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_Query
 */
class Pinster_Query {

	/**
	 * Get base query args for resume templates.
	 *
	 * @param array $override Override default args.
	 * @return array
	 */
	public static function get_resume_templates_args( $override = array() ) {
		$paged = 1;
		if ( get_query_var( 'paged' ) ) {
			$paged = absint( get_query_var( 'paged' ) );
		} elseif ( ! empty( $_GET['paged'] ) ) {
			$paged = max( 1, absint( $_GET['paged'] ) );
		}

		$args = array(
			'post_type'      => 'resume_template',
			'post_status'    => 'publish',
			'posts_per_page' => 18,
			'paged'          => $paged,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		$tax_query = array();
		$category  = isset( $_GET['resume_category'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_category'] ) ) : '';
		if ( '' !== $category && taxonomy_exists( 'resume_category' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'resume_category',
				'field'    => 'slug',
				'terms'    => $category,
			);
		}
		$style = isset( $_GET['resume_style'] ) ? sanitize_text_field( wp_unslash( $_GET['resume_style'] ) ) : '';
		if ( '' !== $style && taxonomy_exists( 'resume_style' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'resume_style',
				'field'    => 'slug',
				'terms'    => $style,
			);
		}
		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query']     = $tax_query;
		}

		return array_merge( $args, $override );
	}
}
