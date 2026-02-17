<?php
/**
 * Pinster theme functions and setup.
 *
 * @package Pinster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PINSTER_VERSION', '1.1.0' );

require_once get_template_directory() . '/inc/class-pinster-query.php';

/**
 * Theme setup.
 */
function pinster_setup() {
	load_theme_textdomain( 'pinster', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
}
add_action( 'after_setup_theme', 'pinster_setup' );

/**
 * Enqueue scripts and styles.
 */
function pinster_enqueue_scripts() {
	$uri = get_template_directory_uri();
	$ver = PINSTER_VERSION;

	wp_enqueue_style(
		'pinster-style',
		get_stylesheet_uri(),
		array(),
		$ver
	);
	wp_enqueue_style(
		'pinster-main',
		$uri . '/assets/css/main.css',
		array( 'pinster-style' ),
		$ver
	);

	wp_enqueue_script(
		'pinster-main',
		$uri . '/assets/js/main.js',
		array(),
		$ver,
		true
	);

	if ( is_singular( 'resume_template' ) && pinster_is_gated_download() ) {
		wp_enqueue_style(
			'pinster-modal',
			$uri . '/assets/css/modal.css',
			array( 'pinster-main' ),
			$ver
		);
		wp_enqueue_script(
			'pinster-gated',
			$uri . '/assets/js/gated-modal.js',
			array( 'pinster-main' ),
			$ver,
			true
		);
		wp_localize_script(
			'pinster-gated',
			'pinsterGated',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'pinster_enqueue_scripts' );

/**
 * Apply filters and search to archive main query for resume_template.
 *
 * @param WP_Query $query Query object.
 */
function pinster_archive_query_filters( $query ) {
	if ( ! pinster_dm_active() || ! $query->is_main_query() || ! is_post_type_archive( 'resume_template' ) ) {
		return;
	}
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	if ( '' !== $search ) {
		$query->set( 's', $search );
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
		$query->set( 'tax_query', $tax_query );
	}
	$query->set( 'posts_per_page', 18 );
}
add_action( 'pre_get_posts', 'pinster_archive_query_filters' );

/**
 * Check if Pinster Download Manager plugin is active.
 *
 * @return bool
 */
function pinster_dm_active() {
	return class_exists( 'Pinster_Download_Manager' );
}

/**
 * Get download URL for a resume template (requires plugin).
 *
 * @param int $post_id Resume template post ID.
 * @return string
 */
function pinster_get_download_url( $post_id ) {
	if ( ! pinster_dm_active() ) {
		return '';
	}
	return Pinster_Download_Manager::get_download_url( $post_id );
}

/**
 * Whether gated download (email form) is enabled.
 *
 * @return bool
 */
function pinster_is_gated_download() {
	if ( ! pinster_dm_active() ) {
		return false;
	}
	$opts = get_option( 'pinster_dm_settings', array() );
	return ! empty( $opts['gated_download'] );
}

/**
 * Get related resume templates (same category or style).
 *
 * @param int $post_id Current template post ID.
 * @param int $per_page Number to return.
 * @return WP_Query
 */
function pinster_get_related_templates( $post_id, $per_page = 6 ) {
	$cat_ids = array();
	$style_ids = array();
	$categories = get_the_terms( $post_id, 'resume_category' );
	$styles = get_the_terms( $post_id, 'resume_style' );
	if ( $categories && ! is_wp_error( $categories ) ) {
		$cat_ids = wp_list_pluck( $categories, 'term_id' );
	}
	if ( $styles && ! is_wp_error( $styles ) ) {
		$style_ids = wp_list_pluck( $styles, 'term_id' );
	}
	$tax_query = array();
	if ( ! empty( $cat_ids ) ) {
		$tax_query[] = array(
			'taxonomy' => 'resume_category',
			'field'    => 'term_id',
			'terms'    => $cat_ids,
		);
	}
	if ( ! empty( $style_ids ) ) {
		$tax_query[] = array(
			'taxonomy' => 'resume_style',
			'field'    => 'term_id',
			'terms'    => $style_ids,
		);
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'OR';
	}
	$args = array(
		'post_type'      => 'resume_template',
		'post_status'    => 'publish',
		'post__not_in'   => array( $post_id ),
		'posts_per_page' => $per_page,
		'orderby'        => 'rand',
		'no_found_rows'  => true,
	);
	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
	}
	return new WP_Query( $args );
}
