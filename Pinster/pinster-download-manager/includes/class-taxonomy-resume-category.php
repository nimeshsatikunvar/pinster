<?php
/**
 * Taxonomy: Resume Category (hierarchical).
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Taxonomy_Resume_Category
 */
class Pinster_DM_Taxonomy_Resume_Category {

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	const TAXONOMY = 'resume_category';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Taxonomy_Resume_Category|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Taxonomy_Resume_Category
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
			'name'              => _x( 'Resume Categories', 'taxonomy general name', 'pinster-download-manager' ),
			'singular_name'     => _x( 'Resume Category', 'taxonomy singular name', 'pinster-download-manager' ),
			'search_items'      => __( 'Search Categories', 'pinster-download-manager' ),
			'all_items'         => __( 'All Categories', 'pinster-download-manager' ),
			'parent_item'       => __( 'Parent Category', 'pinster-download-manager' ),
			'parent_item_colon' => __( 'Parent Category:', 'pinster-download-manager' ),
			'edit_item'         => __( 'Edit Category', 'pinster-download-manager' ),
			'update_item'       => __( 'Update Category', 'pinster-download-manager' ),
			'add_new_item'      => __( 'Add New Category', 'pinster-download-manager' ),
			'new_item_name'     => __( 'New Category Name', 'pinster-download-manager' ),
			'menu_name'         => __( 'Categories', 'pinster-download-manager' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'resume-category' ),
		);

		register_taxonomy( self::TAXONOMY, array( Pinster_DM_CPT_Resume_Template::POST_TYPE ), $args );
	}
}
