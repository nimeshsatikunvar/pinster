<?php
/**
 * Plugin settings: gated download, email template, secure storage.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Settings
 */
class Pinster_DM_Settings {

	/**
	 * Option key for main settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'pinster_dm_settings';

	/**
	 * Option key for email template.
	 *
	 * @var string
	 */
	const EMAIL_OPTION_KEY = 'pinster_dm_email';

	/**
	 * Single instance.
	 *
	 * @var Pinster_DM_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Pinster_DM_Settings
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
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings and submenu pages.
	 */
	public function add_menu() {
		add_submenu_page(
			Pinster_DM_Dashboard_Page::MENU_SLUG,
			__( 'Subscribers', 'pinster-download-manager' ),
			__( 'Subscribers', 'pinster-download-manager' ),
			'manage_options',
			'pinster-dm-subscribers',
			array( $this, 'render_subscribers_page' )
		);
		add_submenu_page(
			Pinster_DM_Dashboard_Page::MENU_SLUG,
			__( 'Settings', 'pinster-download-manager' ),
			__( 'Settings', 'pinster-download-manager' ),
			'manage_options',
			'pinster-dm-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings and email option.
	 */
	public function register_settings() {
		register_setting( 'pinster_dm_settings_group', self::OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );
		register_setting( 'pinster_dm_email_group', self::EMAIL_OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_email' ),
		) );
	}

	/**
	 * Sanitize main settings.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$out = array();
		$out['gated_download'] = ! empty( $input['gated_download'] );
		$out['secure_storage'] = ! empty( $input['secure_storage'] );
		return $out;
	}

	/**
	 * Sanitize email template.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize_email( $input ) {
		$out = array();
		$out['subject'] = isset( $input['subject'] ) ? sanitize_text_field( $input['subject'] ) : '';
		$out['body'] = isset( $input['body'] ) ? sanitize_textarea_field( $input['body'] ) : '';
		return $out;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opts = get_option( self::OPTION_KEY, array() );
		$gated = ! empty( $opts['gated_download'] );
		$secure = ! empty( $opts['secure_storage'] );
		$email_opts = get_option( self::EMAIL_OPTION_KEY, array() );
		$subject = isset( $email_opts['subject'] ) ? $email_opts['subject'] : __( 'Your resume template from {site_name}', 'pinster-download-manager' );
		$body = isset( $email_opts['body'] ) ? $email_opts['body'] : __( "Hello,\n\nPlease find your requested resume template attached.\n\nTemplate: {template_name}\n\n— {site_name}", 'pinster-download-manager' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Pinster Download Manager – Settings', 'pinster-download-manager' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'pinster_dm_settings_group' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Gated download', 'pinster-download-manager' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[gated_download]" value="1" <?php checked( $gated ); ?> />
								<?php esc_html_e( 'Require email before download; send file via email', 'pinster-download-manager' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Secure file storage', 'pinster-download-manager' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[secure_storage]" value="1" <?php checked( $secure ); ?> />
								<?php esc_html_e( 'Store template files outside the media library (not directly accessible by URL)', 'pinster-download-manager' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'New uploads will be copied to a secure directory. Existing templates keep current storage until re-saved.', 'pinster-download-manager' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Email template (for gated download)', 'pinster-download-manager' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Placeholders: {site_name}, {template_name}', 'pinster-download-manager' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'pinster_dm_email_group' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="pinster_email_subject"><?php esc_html_e( 'Subject', 'pinster-download-manager' ); ?></label></th>
						<td>
							<input type="text" id="pinster_email_subject" name="<?php echo esc_attr( self::EMAIL_OPTION_KEY ); ?>[subject]" value="<?php echo esc_attr( $subject ); ?>" class="large-text" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pinster_email_body"><?php esc_html_e( 'Body', 'pinster-download-manager' ); ?></label></th>
						<td>
							<textarea id="pinster_email_body" name="<?php echo esc_attr( self::EMAIL_OPTION_KEY ); ?>[body]" rows="8" class="large-text"><?php echo esc_textarea( $body ); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render subscribers list page.
	 */
	public function render_subscribers_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$per_page = 20;
		$page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$template_filter = isset( $_GET['template_id'] ) ? absint( $_GET['template_id'] ) : 0;
		$result = Pinster_DM_Subscribers::get_list( array(
			'per_page'   => $per_page,
			'page'       => $page,
			'template_id' => $template_filter,
		) );
		$total = $result['total'];
		$rows = $result['rows'];
		$total_pages = ceil( $total / $per_page );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Subscribers', 'pinster-download-manager' ); ?></h1>
			<p><?php echo esc_html( sprintf( __( 'Total: %s', 'pinster-download-manager' ), number_format_i18n( $total ) ) ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'pinster-download-manager' ); ?></th>
						<th><?php esc_html_e( 'Email', 'pinster-download-manager' ); ?></th>
						<th><?php esc_html_e( 'Template', 'pinster-download-manager' ); ?></th>
						<th><?php esc_html_e( 'Consent', 'pinster-download-manager' ); ?></th>
						<th><?php esc_html_e( 'Date', 'pinster-download-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No subscribers yet.', 'pinster-download-manager' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( isset( $row->name ) ? $row->name : '' ); ?></td>
								<td><?php echo esc_html( $row->email ); ?></td>
								<td>
									<?php
									$post = get_post( $row->template_id );
									if ( $post ) {
										echo esc_html( $post->post_title );
										echo ' <a href="' . esc_url( get_edit_post_link( $row->template_id ) ) . '">#' . (int) $row->template_id . '</a>';
									} else {
										echo '—';
									}
									?>
								</td>
								<td><?php echo ! empty( $row->consent ) ? esc_html__( 'Yes', 'pinster-download-manager' ) : esc_html__( 'No', 'pinster-download-manager' ); ?></td>
								<td><?php echo esc_html( $row->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<?php if ( $total_pages > 1 ) : ?>
				<p class="pagination-links">
					<?php
					for ( $i = 1; $i <= $total_pages; $i++ ) {
						$url = add_query_arg( 'paged', $i );
						if ( $template_filter ) {
							$url = add_query_arg( 'template_id', $template_filter, $url );
						}
						echo '<a href="' . esc_url( $url ) . '">' . (int) $i . '</a> ';
					}
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render statistics page.
	 */
	public function render_statistics_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		global $wpdb;
		$count_meta = Pinster_DM_Download_Handler::COUNT_META;
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, COALESCE(pm.meta_value, 0) AS download_count
				FROM $wpdb->posts p
				LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = %s AND p.post_status = 'publish'
				ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC",
				$count_meta,
				Pinster_DM_CPT_Resume_Template::POST_TYPE
			)
		);
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = %s",
				$count_meta
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Statistics', 'pinster-download-manager' ); ?></h1>
			<p><strong><?php esc_html_e( 'Total downloads', 'pinster-download-manager' ); ?>:</strong> <?php echo esc_html( number_format_i18n( $total ) ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Template', 'pinster-download-manager' ); ?></th>
						<th><?php esc_html_e( 'Downloads', 'pinster-download-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $posts ) ) : ?>
						<tr><td colspan="2"><?php esc_html_e( 'No templates yet.', 'pinster-download-manager' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $posts as $p ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></td>
								<td><?php echo esc_html( number_format_i18n( (int) $p->download_count ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
