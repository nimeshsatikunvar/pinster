<?php
/**
 * Main plugin class.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_Download_Manager
 */
final class Pinster_Download_Manager {

	/**
	 * Single instance.
	 *
	 * @var Pinster_Download_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_Download_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->hook_actions();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies() {
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-cpt-resume-template.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-taxonomy-resume-category.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-taxonomy-resume-style.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-meta-box-file.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-meta-box-thumbnail.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-download-handler.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-admin-columns.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-dashboard-widget.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-settings.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-single-template-fix.php';
		require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-dashboard-page.php';
	}

	/**
	 * Register hooks.
	 */
	private function hook_actions() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Initialize plugin components.
	 */
	public function init() {
		Pinster_DM_CPT_Resume_Template::instance()->register();
		Pinster_DM_Taxonomy_Resume_Category::instance()->register();
		Pinster_DM_Taxonomy_Resume_Style::instance()->register();
		Pinster_DM_Meta_Box_File::instance()->init();
		Pinster_DM_Meta_Box_Thumbnail::instance()->init();
		Pinster_DM_Download_Handler::instance()->init();
		Pinster_DM_Gated_Download::instance()->init();
		Pinster_DM_Single_Template_Fix::init();
		if ( is_admin() ) {
			Pinster_DM_Dashboard_Page::init();
			Pinster_DM_Admin_Columns::instance()->init();
			Pinster_DM_Dashboard_Widget::instance()->init();
			Pinster_DM_Settings::instance()->init();
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		$screen = get_current_screen();
		if ( ! $screen || 'resume_template' !== $screen->post_type ) {
			return;
		}

		if ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
			wp_enqueue_media();
			wp_enqueue_script(
				'pinster-dm-meta-box',
				PINSTER_DM_PLUGIN_URL . 'admin/js/meta-box-file.js',
				array( 'jquery' ),
				PINSTER_DM_VERSION,
				true
			);
			wp_localize_script(
				'pinster-dm-meta-box',
				'pinsterDmMetaBox',
				array(
					'title'  => __( 'Select or upload template file', 'pinster-download-manager' ),
					'button' => __( 'Use this file', 'pinster-download-manager' ),
					'allowedTypes' => array( 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
				)
			);
			wp_enqueue_script(
				'pinster-dm-meta-box-thumbnail',
				PINSTER_DM_PLUGIN_URL . 'admin/js/meta-box-thumbnail.js',
				array( 'jquery', 'wp-media' ),
				PINSTER_DM_VERSION,
				true
			);
			wp_localize_script(
				'pinster-dm-meta-box-thumbnail',
				'pinsterDmThumbnail',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'pinster_dm_thumbnail' ),
				)
			);
		}
	}

	/**
	 * Get download URL for a template (direct or gated depending on settings).
	 *
	 * @param int $post_id Resume template post ID.
	 * @return string URL.
	 */
	public static function get_download_url( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$opts = get_option( 'pinster_dm_settings', array( 'secure_storage' => 1 ) );
		$gated = ! empty( $opts['gated_download'] );
		if ( $gated ) {
			return add_query_arg(
				array(
					Pinster_DM_Gated_Download::QVAR => 1,
					'pinster_template_id' => $post_id,
				),
				home_url( '/' )
			);
		}
		$nonce = wp_create_nonce( 'pinster_download_' . $post_id );
		return add_query_arg(
			array(
				'pinster_download' => 1,
				'template_id'      => $post_id,
				'nonce'            => $nonce,
			),
			home_url( '/' )
		);
	}
}
