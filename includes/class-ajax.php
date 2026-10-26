<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Ajax {

	public static function init() {
		add_action( 'wp_ajax_cpl_toggle_selection', [ __CLASS__, 'toggle_selection' ] );
		add_action( 'wp_ajax_nopriv_cpl_toggle_selection', [ __CLASS__, 'toggle_selection' ] );
		add_action( 'wp_ajax_cpl_clear_compare', [ __CLASS__, 'clear_compare' ] );
		add_action( 'wp_ajax_nopriv_cpl_clear_compare', [ __CLASS__, 'clear_compare' ] );
		add_action( 'wp_ajax_cpl_get_selections', [ __CLASS__, 'get_selections' ] );
		add_action( 'wp_ajax_nopriv_cpl_get_selections', [ __CLASS__, 'get_selections' ] );
		add_action( 'wp_ajax_cpl_get_compare_markup', [ __CLASS__, 'get_compare_markup' ] );
		add_action( 'wp_ajax_nopriv_cpl_get_compare_markup', [ __CLASS__, 'get_compare_markup' ] );
	}

	public static function toggle_selection() {
		check_ajax_referer( 'cpl_nonce', 'nonce' );

		$post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$list_type = isset( $_POST['list_type'] ) ? sanitize_key( wp_unslash( $_POST['list_type'] ) ) : '';

		if ( empty( $post_id ) || empty( $list_type ) ) {
			wp_send_json_error(
				[
					'message' => 'Datos incompletos.',
				],
				400
			);
		}

		if ( 'vehiculo' !== get_post_type( $post_id ) ) {
			wp_send_json_error(
				[
					'message' => 'El post no pertenece al tipo vehiculo.',
				],
				400
			);
		}

		$session_key = CPL_Session::get_or_create_session_key( true );

		if ( empty( $session_key ) ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo obtener la sesión del visitante.',
				],
				400
			);
		}

		if ( CPL_Database::selection_exists( $session_key, $post_id, $list_type ) ) {
			$removed = CPL_Database::remove_selection( $session_key, $post_id, $list_type );

			if ( ! $removed ) {
				wp_send_json_error(
					[
						'message' => 'No se pudo actualizar la selección.',
					],
					500
				);
			}

			wp_send_json_success(
				[
					'post_id'     => $post_id,
					'list_type'   => $list_type,
					'action'      => 'removed',
					'is_active'   => false,
					'session_key' => $session_key,
				]
			);
		}

		$current_count = CPL_Database::get_valid_selection_count( $session_key, $list_type );

		if ( $current_count >= CPL_Plugin::get_max_selections( $list_type ) ) {
			wp_send_json_error(
				[
					'message' => self::get_limit_message( $list_type ),
					'code'    => 'selection_limit_reached',
				],
				409
			);
		}

		$added = CPL_Database::add_selection( $session_key, $post_id, $list_type );

		if ( ! $added ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo actualizar la selección.',
				],
				500
			);
		}

		wp_send_json_success(
			[
				'post_id'     => $post_id,
				'list_type'   => $list_type,
				'action'      => 'added',
				'is_active'   => true,
				'session_key' => $session_key,
			]
		);
	}

	public static function get_selections() {
		check_ajax_referer( 'cpl_nonce', 'nonce' );

		$session_key = CPL_Session::get_or_create_session_key( true );

		if ( empty( $session_key ) ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo obtener la sesión del visitante.',
				],
				400
			);
		}

		wp_send_json_success(
			[
				'session_key' => $session_key,
				'favorite'    => CPL_Database::get_valid_post_ids_by_type( $session_key, 'favorite', CPL_Plugin::MAX_SELECTIONS ),
				'compare'     => CPL_Database::get_valid_post_ids_by_type( $session_key, 'compare', CPL_Plugin::MAX_COMPARE ),
			]
		);
	}

	public static function clear_compare() {
		check_ajax_referer( 'cpl_nonce', 'nonce' );

		$session_key = CPL_Session::get_or_create_session_key( true );

		if ( empty( $session_key ) ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo obtener la sesiÃ³n del visitante.',
				],
				400
			);
		}

		$cleared = CPL_Database::remove_all_selections_by_type( $session_key, 'compare' );

		if ( ! $cleared ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo limpiar el comparador.',
				],
				500
			);
		}

		wp_send_json_success(
			[
				'session_key' => $session_key,
				'favorite'    => CPL_Database::get_valid_post_ids_by_type( $session_key, 'favorite', CPL_Plugin::MAX_SELECTIONS ),
				'compare'     => [],
			]
		);
	}

	public static function get_compare_markup() {
		check_ajax_referer( 'cpl_nonce', 'nonce' );

		$session_key = CPL_Session::get_or_create_session_key( true );

		if ( empty( $session_key ) ) {
			wp_send_json_error(
				[
					'message' => 'No se pudo obtener la sesiÃ³n del visitante.',
				],
				400
			);
		}

		wp_send_json_success(
			[
				'session_key' => $session_key,
				'favorite'    => CPL_Database::get_valid_post_ids_by_type( $session_key, 'favorite', CPL_Plugin::MAX_SELECTIONS ),
				'compare'     => CPL_Database::get_valid_post_ids_by_type( $session_key, 'compare', CPL_Plugin::MAX_COMPARE ),
				'markup'      => CPL_Shortcodes::render_compare_shortcode_markup(),
			]
		);
	}

	private static function get_limit_message( $list_type ) {
		if ( 'favorite' === $list_type ) {
			return sprintf( 'Solo puedes agregar hasta %d vehículos a favoritos.', CPL_Plugin::MAX_SELECTIONS );
		}

		if ( 'compare' === $list_type ) {
			return sprintf( 'Solo puedes agregar hasta %d vehículos a comparar.', CPL_Plugin::MAX_COMPARE );
		}

		return sprintf( 'Solo puedes agregar hasta %d vehículos.', CPL_Plugin::MAX_SELECTIONS );
	}
}
