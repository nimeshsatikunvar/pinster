<?php
/**
 * Dashboard widget: template count and total downloads.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Dashboard_Widget
 */
class Pinster_DM_Dashboard_Widget {

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Dashboard_Widget|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Dashboard_Widget
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
		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	/**
	 * Register dashboard widget.
	 */
	public function register_widget() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'pinster_dm_overview',
			__( 'Pinster Download Manager', 'pinster-download-manager' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Render widget content.
	 */
	public function render() {
		$templates_count = wp_count_posts( Pinster_DM_CPT_Resume_Template::POST_TYPE );
		$published = isset( $templates_count->publish ) ? (int) $templates_count->publish : 0;

		global $wpdb;
		$total_downloads = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = %s",
				Pinster_DM_Download_Handler::COUNT_META
			)
		);

		$archive_url = get_post_type_archive_link( Pinster_DM_CPT_Resume_Template::POST_TYPE );
		$list_url = admin_url( 'edit.php?post_type=' . Pinster_DM_CPT_Resume_Template::POST_TYPE );
		?>
		<ul style="margin:0; padding:0; list-style:none;">
			<li style="padding:8px 0; border-bottom:1px solid #eee;">
				<strong><?php esc_html_e( 'Published templates', 'pinster-download-manager' ); ?>:</strong>
				<?php echo esc_html( number_format_i18n( $published ) ); ?>
				<a href="<?php echo esc_url( $list_url ); ?>"><?php esc_html_e( 'Manage', 'pinster-download-manager' ); ?></a>
			</li>
			<li style="padding:8px 0;">
				<strong><?php esc_html_e( 'Total downloads', 'pinster-download-manager' ); ?>:</strong>
				<?php echo esc_html( number_format_i18n( $total_downloads ) ); ?>
			</li>
		</ul>
		<?php if ( $archive_url ) : ?>
			<p style="margin:12px 0 0;">
				<a href="<?php echo esc_url( $archive_url ); ?>" class="button button-secondary" target="_blank" rel="noopener"><?php esc_html_e( 'View archive', 'pinster-download-manager' ); ?></a>
			</p>
		<?php endif; ?>
		<?php
	}
}
