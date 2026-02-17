<?php
/**
 * Admin list columns: thumbnail, category, style, download count.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Admin_Columns
 */
class Pinster_DM_Admin_Columns {

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Admin_Columns|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Admin_Columns
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		add_filter( 'manage_' . Pinster_DM_CPT_Resume_Template::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Pinster_DM_CPT_Resume_Template::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . Pinster_DM_CPT_Resume_Template::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_by_download_count' ) );
	}

	/**
	 * Define columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		$new['cb'] = $columns['cb'];
		$new['pinster_thumb'] = __( 'Thumbnail', 'pinster-download-manager' );
		$new['title'] = $columns['title'];
		$new['resume_category'] = __( 'Category', 'pinster-download-manager' );
		$new['resume_style'] = __( 'Style', 'pinster-download-manager' );
		$new['pinster_downloads'] = __( 'Downloads', 'pinster-download-manager' );
		$new['date'] = $columns['date'];
		return $new;
	}

	/**
	 * Output column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'pinster_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 60, 60 ) );
				if ( $thumb ) {
					echo wp_kses_post( $thumb );
				} else {
					echo '<span class="pinster-dm-no-thumb">—</span>';
				}
				break;
			case 'resume_category':
				$terms = get_the_terms( $post_id, Pinster_DM_Taxonomy_Resume_Category::TAXONOMY );
				if ( $terms && ! is_wp_error( $terms ) ) {
					$names = wp_list_pluck( $terms, 'name' );
					echo esc_html( implode( ', ', $names ) );
				} else {
					echo '—';
				}
				break;
			case 'resume_style':
				$terms = get_the_terms( $post_id, Pinster_DM_Taxonomy_Resume_Style::TAXONOMY );
				if ( $terms && ! is_wp_error( $terms ) ) {
					$names = wp_list_pluck( $terms, 'name' );
					echo esc_html( implode( ', ', $names ) );
				} else {
					echo '—';
				}
				break;
			case 'pinster_downloads':
				echo esc_html( number_format_i18n( Pinster_DM_Download_Handler::get_download_count( $post_id ) ) );
				break;
		}
	}

	/**
	 * Sortable columns.
	 *
	 * @param array $columns Column keys.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['pinster_downloads'] = 'pinster_downloads';
		return $columns;
	}

	/**
	 * Order by download count when requested.
	 *
	 * @param \WP_Query $query Query object.
	 */
	public function sort_by_download_count( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || Pinster_DM_CPT_Resume_Template::POST_TYPE !== $screen->post_type ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		if ( 'pinster_downloads' !== $orderby ) {
			return;
		}
		$query->set( 'meta_key', Pinster_DM_Download_Handler::COUNT_META );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
