<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Installer {

	public static function activate() {
		self::create_tables();
	}

	private static function create_tables() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'custom_product_lists';
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_key VARCHAR(191) NOT NULL,
			post_id BIGINT UNSIGNED NOT NULL,
			list_type VARCHAR(20) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY unique_selection (session_key, post_id, list_type),
			KEY idx_session_type (session_key, list_type),
			KEY idx_post_id (post_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}