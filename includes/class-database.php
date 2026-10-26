<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Database {

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'custom_product_lists';
	}

	public static function add_selection( $session_key, $post_id, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$post_id     = absint( $post_id );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $post_id ) || empty( $list_type ) ) {
			return false;
		}

		$result = $wpdb->insert(
			self::get_table_name(),
			[
				'session_key' => $session_key,
				'post_id'     => $post_id,
				'list_type'   => $list_type,
				'created_at'  => current_time( 'mysql' ),
			],
			[
				'%s',
				'%d',
				'%s',
				'%s',
			]
		);

		if ( false === $result && self::selection_exists( $session_key, $post_id, $list_type ) ) {
			return true;
		}

		return false !== $result;
	}

	public static function remove_selection( $session_key, $post_id, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$post_id     = absint( $post_id );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $post_id ) || empty( $list_type ) ) {
			return false;
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			[
				'session_key' => $session_key,
				'post_id'     => $post_id,
				'list_type'   => $list_type,
			],
			[
				'%s',
				'%d',
				'%s',
			]
		);

		return false !== $result;
	}

	public static function remove_all_selections_by_type( $session_key, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $list_type ) ) {
			return false;
		}

		$result = $wpdb->delete(
			self::get_table_name(),
			[
				'session_key' => $session_key,
				'list_type'   => $list_type,
			],
			[
				'%s',
				'%s',
			]
		);

		return false !== $result;
	}

	public static function selection_exists( $session_key, $post_id, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$post_id     = absint( $post_id );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $post_id ) || empty( $list_type ) ) {
			return false;
		}

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM " . self::get_table_name() . " WHERE session_key = %s AND post_id = %d AND list_type = %s",
			$session_key,
			$post_id,
			$list_type
		);

		return (int) $wpdb->get_var( $sql ) > 0;
	}

	public static function get_post_ids_by_type( $session_key, $list_type, $limit = 0 ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$list_type   = self::sanitize_list_type( $list_type );
		$limit       = absint( $limit );

		if ( empty( $session_key ) || empty( $list_type ) ) {
			return [];
		}

		$sql = "SELECT post_id FROM " . self::get_table_name() . " WHERE session_key = %s AND list_type = %s ORDER BY id DESC";

		if ( $limit > 0 ) {
			$sql .= " LIMIT %d";
		}

		$sql = $wpdb->prepare(
			$sql,
			$limit > 0
				? [ $session_key, $list_type, $limit ]
				: [ $session_key, $list_type ]
		);

		$results = $wpdb->get_col( $sql );

		if ( empty( $results ) ) {
			return [];
		}

		return array_map( 'absint', $results );
	}

	public static function get_selection_count( $session_key, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $list_type ) ) {
			return 0;
		}

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) FROM " . self::get_table_name() . " WHERE session_key = %s AND list_type = %s",
			$session_key,
			$list_type
		);

		return (int) $wpdb->get_var( $sql );
	}

	public static function get_valid_post_ids_by_type( $session_key, $list_type, $limit = 0 ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$list_type   = self::sanitize_list_type( $list_type );
		$limit       = absint( $limit );

		if ( empty( $session_key ) || empty( $list_type ) ) {
			return [];
		}

		$sql = "
			SELECT selections.post_id
			FROM " . self::get_table_name() . " selections
			INNER JOIN {$wpdb->posts} posts ON posts.ID = selections.post_id
			WHERE selections.session_key = %s
				AND selections.list_type = %s
				AND posts.post_type = %s
				AND posts.post_status = %s
			ORDER BY selections.id DESC
		";

		$args = [
			$session_key,
			$list_type,
			'vehiculo',
			'publish',
		];

		if ( $limit > 0 ) {
			$sql    .= ' LIMIT %d';
			$args[] = $limit;
		}

		$results = $wpdb->get_col( $wpdb->prepare( $sql, $args ) );

		if ( empty( $results ) ) {
			return [];
		}

		return array_map( 'absint', $results );
	}

	public static function get_valid_selection_count( $session_key, $list_type ) {
		global $wpdb;

		$session_key = sanitize_text_field( $session_key );
		$list_type   = self::sanitize_list_type( $list_type );

		if ( empty( $session_key ) || empty( $list_type ) ) {
			return 0;
		}

		$sql = $wpdb->prepare(
			"
				SELECT COUNT(*)
				FROM " . self::get_table_name() . " selections
				INNER JOIN {$wpdb->posts} posts ON posts.ID = selections.post_id
				WHERE selections.session_key = %s
					AND selections.list_type = %s
					AND posts.post_type = %s
					AND posts.post_status = %s
			",
			$session_key,
			$list_type,
			'vehiculo',
			'publish'
		);

		return (int) $wpdb->get_var( $sql );
	}

	public static function toggle_selection( $session_key, $post_id, $list_type ) {
		if ( self::selection_exists( $session_key, $post_id, $list_type ) ) {
			$removed = self::remove_selection( $session_key, $post_id, $list_type );

			return [
				'success' => $removed,
				'action'  => 'removed',
			];
		}

		$added = self::add_selection( $session_key, $post_id, $list_type );

		return [
			'success' => $added,
			'action'  => 'added',
		];
	}

	private static function sanitize_list_type( $list_type ) {
		$list_type = sanitize_key( $list_type );

		$allowed = [ 'favorite', 'compare' ];

		if ( ! in_array( $list_type, $allowed, true ) ) {
			return '';
		}

		return $list_type;
	}
}
