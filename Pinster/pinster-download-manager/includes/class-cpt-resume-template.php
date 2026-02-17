<?php
/**
 * Custom post type: Resume Template.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_CPT_Resume_Template
 */
class Pinster_DM_CPT_Resume_Template {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'resume_template';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_CPT_Resume_Template|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_CPT_Resume_Template
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register post type.
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Resume Templates', 'post type general name', 'pinster-download-manager' ),
			'singular_name'         => _x( 'Resume Template', 'post type singular name', 'pinster-download-manager' ),
			'menu_name'             => _x( 'Resume Templates', 'admin menu', 'pinster-download-manager' ),
			'name_admin_bar'        => _x( 'Resume Template', 'add new on admin bar', 'pinster-download-manager' ),
			'add_new'               => _x( 'Add New', 'resume template', 'pinster-download-manager' ),
			'add_new_item'          => __( 'Add New Resume Template', 'pinster-download-manager' ),
			'new_item'              => __( 'New Resume Template', 'pinster-download-manager' ),
			'edit_item'             => __( 'Edit Resume Template', 'pinster-download-manager' ),
			'view_item'             => __( 'View Resume Template', 'pinster-download-manager' ),
			'all_items'             => __( 'All Resume Templates', 'pinster-download-manager' ),
			'search_items'          => __( 'Search Resume Templates', 'pinster-download-manager' ),
			'not_found'             => __( 'No resume templates found.', 'pinster-download-manager' ),
			'not_found_in_trash'    => __( 'No resume templates found in Trash.', 'pinster-download-manager' ),
			'featured_image'        => __( 'Template Thumbnail', 'pinster-download-manager' ),
			'set_featured_image'    => __( 'Set template thumbnail', 'pinster-download-manager' ),
			'remove_featured_image'  => __( 'Remove template thumbnail', 'pinster-download-manager' ),
			'use_featured_image'    => __( 'Use as template thumbnail', 'pinster-download-manager' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'pinster-dm',
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'resume-templates', 'with_front' => false ),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-media-document',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
