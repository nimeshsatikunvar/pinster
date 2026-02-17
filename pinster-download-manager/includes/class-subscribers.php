<?php
/**
 * Subscriber (email) storage for gated downloads.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Subscribers
 */
class Pinster_DM_Subscribers {

	/**
	 * Table name with prefix.
	 *
	 * @var string
	 */
	private static $table;

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function get_table() {
		if ( null === self::$table ) {
			global $wpdb;
			self::$table = $wpdb->prefix . Pinster_DM_Installer::TABLE_SUBSCRIBERS;
		}
		return self::$table;
	}

	/**
	 * Insert a subscriber (name + email + template_id + consent).
	 *
	 * @param string $email       Email address.
	 * @param int    $template_id Resume template post ID.
	 * @param string $name        Name (optional).
	 * @param bool   $consent     Consent accepted.
	 * @return int|false Insert id or false.
	 */
	public static function add( $email, $template_id, $name = '', $consent = false ) {
		$name = sanitize_text_field( $name );
		if ( '' !== $name ) {
			$name = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 120 ) : substr( $name, 0, 120 );
		}
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return false;
		}
		$template_id = absint( $template_id );
		if ( ! $template_id ) {
			return false;
		}
		$consent = (bool) $consent;

		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( '' !== $user_agent ) {
			$user_agent = function_exists( 'mb_substr' ) ? mb_substr( $user_agent, 0, 255 ) : substr( $user_agent, 0, 255 );
		}
		global $wpdb;
		$result = $wpdb->insert(
			self::get_table(),
			array(
				'name'        => $name,
				'email'       => $email,
				'template_id' => $template_id,
				'consent'     => $consent ? 1 : 0,
				'ip'          => $ip,
				'user_agent'  => $user_agent,
			),
			array( '%s', '%s', '%d', '%d', '%s', '%s' )
		);
		if ( false === $result ) {
			return false;
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Get subscribers (paginated).
	 *
	 * @param array $args Optional: per_page, page, template_id, orderby, order.
	 * @return array Rows and total.
	 */
	public static function get_list( $args = array() ) {
		global $wpdb;
		$t = self::get_table();
		$per_page = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 20;
		$page = isset( $args['page'] ) ? max( 1, absint( $args['page'] ) ) : 1;
		$offset = ( $page - 1 ) * $per_page;
		$orderby = isset( $args['orderby'] ) && in_array( $args['orderby'], array( 'id', 'name', 'email', 'template_id', 'consent', 'created_at' ), true )
			? $args['orderby'] : 'created_at';
		$order = isset( $args['order'] ) && 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$template_id = isset( $args['template_id'] ) ? absint( $args['template_id'] ) : 0;

		$where = '1=1';
		if ( $template_id ) {
			$where .= $wpdb->prepare( ' AND template_id = %d', $template_id );
		}
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $t WHERE $where" );
		$sql = $wpdb->prepare(
			"SELECT * FROM $t WHERE $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);
		$rows = $wpdb->get_results( $sql );
		return array( 'rows' => $rows, 'total' => $total );
	}
}
