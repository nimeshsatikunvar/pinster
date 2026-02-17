<?php
/**
 * Unified dashboard page: stats, templates, subscribers, email, settings.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Dashboard_Page
 */
class Pinster_DM_Dashboard_Page {

	const MENU_SLUG = 'pinster-dm';

	/**
	 * Initialize: register menu and dashboard as main page.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 9 );
	}

	/**
	 * Register top-level Pinster menu and dashboard; CPT will add under it.
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'Pinster Download Manager', 'pinster-download-manager' ),
			__( 'Pinster', 'pinster-download-manager' ),
			'edit_posts',
			self::MENU_SLUG,
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-media-document',
			20
		);
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'pinster-download-manager' ),
			__( 'Dashboard', 'pinster-download-manager' ),
			'edit_posts',
			self::MENU_SLUG,
			array( __CLASS__, 'render_dashboard' )
		);
	}

	/**
	 * Render the full dashboard (stats, templates, subscribers, email, settings).
	 */
	public static function render_dashboard() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		global $wpdb;
		$count_meta = Pinster_DM_Download_Handler::COUNT_META;
		$templates_count = wp_count_posts( Pinster_DM_CPT_Resume_Template::POST_TYPE );
		$published = isset( $templates_count->publish ) ? (int) $templates_count->publish : 0;
		$total_downloads = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(meta_value), 0) FROM $wpdb->postmeta WHERE meta_key = %s",
				$count_meta
			)
		);
		$subscribers_table = $wpdb->prefix . Pinster_DM_Installer::TABLE_SUBSCRIBERS;
		$subscribers_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $subscribers_table" );
		$opts = get_option( Pinster_DM_Settings::OPTION_KEY, array() );
		if ( empty( $opts ) ) {
			$opts = array( 'secure_storage' => true );
		}
		$gated = ! empty( $opts['gated_download'] );
		$secure = ! empty( $opts['secure_storage'] );
		$email_opts = get_option( Pinster_DM_Settings::EMAIL_OPTION_KEY, array() );
		$email_subject = isset( $email_opts['subject'] ) ? $email_opts['subject'] : __( 'Your resume template from {site_name}', 'pinster-download-manager' );
		$email_body = isset( $email_opts['body'] ) ? $email_opts['body'] : __( "Hello,\n\nPlease find your requested resume template attached.\n\nTemplate: {template_name}\n\n— {site_name}", 'pinster-download-manager' );
		$recent_templates = get_posts( array(
			'post_type'      => Pinster_DM_CPT_Resume_Template::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		$subscribers_result = Pinster_DM_Subscribers::get_list( array( 'per_page' => 10, 'page' => 1 ) );
		$top_templates = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, COALESCE(pm.meta_value, 0) AS cnt FROM $wpdb->posts p
				LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = %s AND p.post_status = 'publish'
				ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC LIMIT 10",
				$count_meta,
				Pinster_DM_CPT_Resume_Template::POST_TYPE
			)
		);
		?>
		<div class="wrap pinster-dm-dashboard">
			<h1><?php esc_html_e( 'Dashboard', 'pinster-download-manager' ); ?></h1>

			<div class="pinster-dm-stats">
				<div class="pinster-dm-stat-box">
					<span class="pinster-dm-stat-number"><?php echo esc_html( number_format_i18n( $published ) ); ?></span>
					<span class="pinster-dm-stat-label"><?php esc_html_e( 'Templates', 'pinster-download-manager' ); ?></span>
				</div>
				<div class="pinster-dm-stat-box">
					<span class="pinster-dm-stat-number"><?php echo esc_html( number_format_i18n( $total_downloads ) ); ?></span>
					<span class="pinster-dm-stat-label"><?php esc_html_e( 'Total downloads', 'pinster-download-manager' ); ?></span>
				</div>
				<div class="pinster-dm-stat-box">
					<span class="pinster-dm-stat-number"><?php echo esc_html( number_format_i18n( $subscribers_count ) ); ?></span>
					<span class="pinster-dm-stat-label"><?php esc_html_e( 'Subscribers', 'pinster-download-manager' ); ?></span>
				</div>
			</div>

			<div class="pinster-dm-dashboard-grid">
				<div class="pinster-dm-section">
					<h2><?php esc_html_e( 'Templates', 'pinster-download-manager' ); ?></h2>
					<p>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Pinster_DM_CPT_Resume_Template::POST_TYPE ) ); ?>" class="button"><?php esc_html_e( 'All templates', 'pinster-download-manager' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Pinster_DM_CPT_Resume_Template::POST_TYPE ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add new', 'pinster-download-manager' ); ?></a>
					</p>
					<table class="wp-list-table widefat fixed striped">
						<thead><tr><th><?php esc_html_e( 'Title', 'pinster-download-manager' ); ?></th><th><?php esc_html_e( 'Downloads', 'pinster-download-manager' ); ?></th><th></th></tr></thead>
						<tbody>
							<?php if ( empty( $recent_templates ) ) : ?>
								<tr><td colspan="3"><?php esc_html_e( 'No templates yet.', 'pinster-download-manager' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $recent_templates as $p ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $p->post_title ); ?></strong></td>
										<td><?php echo esc_html( number_format_i18n( (int) get_post_meta( $p->ID, $count_meta, true ) ) ); ?></td>
										<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php esc_html_e( 'Edit', 'pinster-download-manager' ); ?></a> | <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'pinster-download-manager' ); ?></a></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<div class="pinster-dm-section">
					<h2><?php esc_html_e( 'Statistics (top downloads)', 'pinster-download-manager' ); ?></h2>
					<table class="wp-list-table widefat fixed striped">
						<thead><tr><th><?php esc_html_e( 'Template', 'pinster-download-manager' ); ?></th><th><?php esc_html_e( 'Downloads', 'pinster-download-manager' ); ?></th></tr></thead>
						<tbody>
							<?php if ( empty( $top_templates ) ) : ?>
								<tr><td colspan="2"><?php esc_html_e( 'No data yet.', 'pinster-download-manager' ); ?></td></tr>
							<?php else : ?>
								<?php foreach ( $top_templates as $p ) : ?>
									<tr>
										<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></td>
										<td><?php echo esc_html( number_format_i18n( (int) $p->cnt ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<div class="pinster-dm-section pinster-dm-subscribers-section">
				<h2><?php esc_html_e( 'Subscribers', 'pinster-download-manager' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead><tr><th><?php esc_html_e( 'Name', 'pinster-download-manager' ); ?></th><th><?php esc_html_e( 'Email', 'pinster-download-manager' ); ?></th><th><?php esc_html_e( 'Template', 'pinster-download-manager' ); ?></th><th><?php esc_html_e( 'Date', 'pinster-download-manager' ); ?></th></tr></thead>
					<tbody>
						<?php if ( empty( $subscribers_result['rows'] ) ) : ?>
							<tr><td colspan="4"><?php esc_html_e( 'No subscribers yet.', 'pinster-download-manager' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $subscribers_result['rows'] as $row ) : ?>
								<tr>
									<td><?php echo esc_html( isset( $row->name ) ? $row->name : '' ); ?></td>
									<td><?php echo esc_html( $row->email ); ?></td>
									<td><?php
										$post = get_post( $row->template_id );
										echo $post ? esc_html( $post->post_title ) : '—';
									?></td>
									<td><?php echo esc_html( $row->created_at ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php if ( $subscribers_result['total'] > 10 ) : ?>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pinster-dm-subscribers' ) ); ?>"><?php esc_html_e( 'View all subscribers', 'pinster-download-manager' ); ?></a></p>
				<?php endif; ?>
			</div>

			<div class="pinster-dm-section">
				<h2><?php esc_html_e( 'Email template (gated download)', 'pinster-download-manager' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Placeholders: {site_name}, {template_name}', 'pinster-download-manager' ); ?></p>
				<form method="post" action="options.php">
					<?php settings_fields( 'pinster_dm_email_group' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Subject', 'pinster-download-manager' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( Pinster_DM_Settings::EMAIL_OPTION_KEY ); ?>[subject]" value="<?php echo esc_attr( $email_subject ); ?>" class="large-text" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Body', 'pinster-download-manager' ); ?></th>
							<td><textarea name="<?php echo esc_attr( Pinster_DM_Settings::EMAIL_OPTION_KEY ); ?>[body]" rows="6" class="large-text"><?php echo esc_textarea( $email_body ); ?></textarea></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save email template', 'pinster-download-manager' ) ); ?>
				</form>
			</div>

			<div class="pinster-dm-section">
				<h2><?php esc_html_e( 'Settings', 'pinster-download-manager' ); ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields( 'pinster_dm_settings_group' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Gated download', 'pinster-download-manager' ); ?></th>
							<td>
								<label><input type="checkbox" name="<?php echo esc_attr( Pinster_DM_Settings::OPTION_KEY ); ?>[gated_download]" value="1" <?php checked( $gated ); ?> /> <?php esc_html_e( 'Require email and send file via email', 'pinster-download-manager' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Secure file storage', 'pinster-download-manager' ); ?></th>
							<td>
								<label><input type="checkbox" name="<?php echo esc_attr( Pinster_DM_Settings::OPTION_KEY ); ?>[secure_storage]" value="1" <?php checked( $secure ); ?> /> <?php esc_html_e( 'Store files outside media library (recommended)', 'pinster-download-manager' ); ?></label>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Save settings', 'pinster-download-manager' ) ); ?>
				</form>
			</div>
		</div>
		<style>
			.pinster-dm-stats { display: flex; gap: 1rem; margin: 1.5rem 0; flex-wrap: wrap; }
			.pinster-dm-stat-box { background: #fff; border: 1px solid #c3c4c7; padding: 1rem 1.5rem; min-width: 140px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
			.pinster-dm-stat-number { display: block; font-size: 1.75rem; font-weight: 600; }
			.pinster-dm-stat-label { color: #646970; font-size: 0.9rem; }
			.pinster-dm-dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
			@media (max-width: 782px) { .pinster-dm-dashboard-grid { grid-template-columns: 1fr; } }
			.pinster-dm-section { background: #fff; border: 1px solid #c3c4c7; padding: 1rem 1.5rem; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 1rem; }
			.pinster-dm-section h2 { margin-top: 0; padding-bottom: 0.5rem; border-bottom: 1px solid #eee; }
		</style>
		<?php
	}
}
