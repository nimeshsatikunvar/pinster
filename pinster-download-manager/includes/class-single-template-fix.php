<?php
/**
 * Ensure single resume_template URLs resolve correctly (fix 404 / nothing found).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Single_Template_Fix
 */
class Pinster_DM_Single_Template_Fix {

	/**
	 * Initialize: hook pre_get_posts to fix single template query.
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'fix_single_query' ), 1 );
		add_filter( 'posts_results', array( __CLASS__, 'ensure_single_result' ), 10, 2 );
	}

	/**
	 * Get slug from request URI for resume-templates.
	 *
	 * @return string|null Slug or null.
	 */
	private static function get_single_slug_from_request() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return null;
		}
		$uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$uri = strtok( $uri, '?' );
		if ( ! $uri ) {
			return null;
		}
		$prefix = '/resume-templates/';
		if ( strpos( $uri, $prefix ) !== 0 ) {
			return null;
		}
		$slug = trim( substr( $uri, strlen( $prefix ) ), '/' );
		return ( '' !== $slug && false === strpos( $slug, '/' ) ) ? $slug : null;
	}

	/**
	 * Fix main query for single resume_template when URL is /resume-templates/{slug}.
	 *
	 * @param \WP_Query $query Query.
	 */
	public static function fix_single_query( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}
		$slug = self::get_single_slug_from_request();
		if ( null === $slug ) {
			return;
		}
		$post = get_page_by_path( $slug, OBJECT, Pinster_DM_CPT_Resume_Template::POST_TYPE );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}
		$query->set( 'post_type', Pinster_DM_CPT_Resume_Template::POST_TYPE );
		$query->set( 'name', $slug );
		$query->set( 'post_status', 'publish' );
		$query->set( 'posts_per_page', 1 );
		$query->set( 'pinster_single_id', $post->ID );
		$query->is_single = true;
		$query->is_singular = true;
		$query->is_404 = false;
	}

	/**
	 * Ensure the resolved post is in the results.
	 *
	 * @param array     $posts Posts array.
	 * @param \WP_Query $query Query.
	 * @return array
	 */
	public static function ensure_single_result( $posts, $query ) {
		$fix_id = $query->get( 'pinster_single_id' );
		if ( ! $fix_id || ! $query->is_main_query() ) {
			return $posts;
		}
		$post = get_post( $fix_id );
		if ( $post && Pinster_DM_CPT_Resume_Template::POST_TYPE === $post->post_type && 'publish' === $post->post_status ) {
			return array( $post );
		}
		return $posts;
	}
}
