<?php
/**
 * Taxonomy: Resume Style (flat, e.g. Modern, Minimal).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Taxonomy_Resume_Style
 */
class Pinster_DM_Taxonomy_Resume_Style {

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	const TAXONOMY = 'resume_style';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Taxonomy_Resume_Style|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Taxonomy_Resume_Style
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register taxonomy.
	 */
	public function register() {
		$labels = array(
			'name'                       => _x( 'Resume Styles', 'taxonomy general name', 'pinster-download-manager' ),
			'singular_name'              => _x( 'Resume Style', 'taxonomy singular name', 'pinster-download-manager' ),
			'search_items'               => __( 'Search Styles', 'pinster-download-manager' ),
			'all_items'                  => __( 'All Styles', 'pinster-download-manager' ),
			'edit_item'                  => __( 'Edit Style', 'pinster-download-manager' ),
			'update_item'                => __( 'Update Style', 'pinster-download-manager' ),
			'add_new_item'               => __( 'Add New Style', 'pinster-download-manager' ),
			'new_item_name'              => __( 'New Style Name', 'pinster-download-manager' ),
			'menu_name'                  => __( 'Styles', 'pinster-download-manager' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'resume-style' ),
		);

		register_taxonomy( self::TAXONOMY, array( Pinster_DM_CPT_Resume_Template::POST_TYPE ), $args );
	}
}
