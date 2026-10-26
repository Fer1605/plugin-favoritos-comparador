<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Query {

	public static function init() {
		add_action( 'elementor/query/favorite_products', [ __CLASS__, 'filter_favorite_products' ] );
		add_action( 'elementor/query/compare_products', [ __CLASS__, 'filter_compare_products' ] );
	}

	public static function filter_favorite_products( $query ) {
		self::apply_favorite_products_query( $query );
	}

	public static function filter_compare_products( $query ) {
		self::apply_compare_products_query( $query );
	}

	private static function apply_favorite_products_query( $query ) {
		$session_key = CPL_Session::get_session_key();

		if ( empty( $session_key ) ) {
			self::set_empty_query( $query );
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		nocache_headers();

		$post_ids = CPL_Database::get_valid_post_ids_by_type( $session_key, 'favorite', CPL_Plugin::MAX_SELECTIONS );

		if ( empty( $post_ids ) ) {
			self::set_empty_query( $query );
			return;
		}

		$query->set( 'post_type', 'vehiculo' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'post__in', $post_ids );
		$query->set( 'orderby', 'post__in' );
		$query->set( 'posts_per_page', count( $post_ids ) );
		$query->set( 'ignore_sticky_posts', true );
	}

	private static function apply_compare_products_query( $query ) {
		$session_key = CPL_Session::get_session_key();

		if ( empty( $session_key ) ) {
			self::set_empty_query( $query );
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		nocache_headers();

		$post_ids = CPL_Database::get_valid_post_ids_by_type( $session_key, 'compare', CPL_Plugin::MAX_COMPARE );

		if ( empty( $post_ids ) ) {
			self::set_empty_query( $query );
			return;
		}

		$query->set( 'post_type', 'vehiculo' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'post__in', $post_ids );
		$query->set( 'orderby', 'post__in' );
		$query->set( 'posts_per_page', count( $post_ids ) );
		$query->set( 'ignore_sticky_posts', true );
	}

	private static function set_empty_query( $query ) {
		$query->set( 'post__in', [ 0 ] );
	}
}
