<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPL_Session {

	const COOKIE_NAME = 'SESScplsession';
	const STORAGE_KEY = 'cpl_session_key';
	const RELOAD_FLAG = 'cpl_cookie_sync_reload';
	const GUEST_PREFIX = 'guest_';
	const USER_PREFIX  = 'user_';

	public static function init() {
		add_action( 'init', [ __CLASS__, 'maybe_set_session_cookie' ], 1 );
		add_action( 'template_redirect', [ __CLASS__, 'maybe_disable_cache' ], 0 );

		if ( function_exists( 'pantheon_session_start' ) ) {
			pantheon_session_start();
		}
		
		// Iniciar sesión de Pantheon si está disponible
	}

	public static function maybe_set_session_cookie() {
		// Usar sesión de Pantheon primero
		$session_key = self::get_or_create_session_key();
		
		// Si el usuario está logueado
		if ( empty( $session_key ) ) {
			return;
		}
		
		// Usuario no logueado
	}

	public static function maybe_disable_cache() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( ! self::request_has_session_cookie() && empty( self::get_requested_session_key() ) && empty( self::get_php_session_key() ) && ! is_user_logged_in() ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		nocache_headers();
	}

	public static function get_session_key( $allow_request = false ) {
		// Prioridad: cookie > sesión de Pantheon > usuario logueado
		if ( is_user_logged_in() ) {
			return self::USER_PREFIX . get_current_user_id();
		}

		if ( $allow_request ) {
			$requested_key = self::get_requested_session_key();

			if ( ! empty( $requested_key ) ) {
				return $requested_key;
			}
		}

		$cookie_session_key = self::get_cookie_session_key();

		if ( ! empty( $cookie_session_key ) ) {
			return $cookie_session_key;
		}

		return self::get_php_session_key();
	}

	public static function get_or_create_session_key( $allow_request = false ) {
		$session_key = self::get_session_key( $allow_request );

		if ( empty( $session_key ) ) {
			$session_key = self::generate_session_key();
		}

		self::persist_session_key( $session_key );

		return $session_key;
	}

	public static function persist_session_key( $session_key ) {
		$session_key = self::normalize_session_key( $session_key );

		if ( empty( $session_key ) ) {
			return false;
		}

		self::set_php_session_key( $session_key );

		if ( self::get_cookie_session_key() === $session_key ) {
			return true;
		}

		self::set_session_cookie( $session_key );
		$_COOKIE[ self::COOKIE_NAME ] = $session_key;

		return true;
	}

	public static function get_cookie_session_key() {
		if ( ! self::has_session_key() ) {
			return '';
		}

		return self::normalize_session_key( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
	}

	public static function get_php_session_key() {
		if ( ! self::has_php_session() ) {
			return '';
		}

		$session_key = isset( $_SESSION[ self::COOKIE_NAME ] ) ? wp_unslash( $_SESSION[ self::COOKIE_NAME ] ) : '';

		return self::normalize_session_key( $session_key );
	}

	private static function has_session_key() {
		return ! empty(
			self::normalize_session_key(
				isset( $_COOKIE[ self::COOKIE_NAME ] ) ? wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) : ''
			)
		);
	}

	private static function generate_session_key() {
		return self::GUEST_PREFIX . wp_generate_uuid4();
	}

	private static function has_php_session() {
		return function_exists( 'session_status' ) && PHP_SESSION_ACTIVE === session_status();
	}

	private static function set_php_session_key( $session_key ) {
		if ( ! self::has_php_session() ) {
			return;
		}

		$_SESSION[ self::COOKIE_NAME ] = $session_key;
	}

	private static function set_session_cookie( $session_key ) {
		$expire = time() + ( 30 * DAY_IN_SECONDS );
		
		// Configuración para Pantheon
		$path = COOKIEPATH ? COOKIEPATH : '/';
		
		// En Pantheon, el dominio debe coincidir exactamente
		
		setcookie(
			self::COOKIE_NAME,
			$session_key,
			[
				'expires'  => $expire,
				'path'     => $path,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			]
		);
		
		// Forzar que la cookie esté disponible inmediatamente
	}

	private static function get_requested_session_key() {
		$requested_key = isset( $_REQUEST['visitor_key'] ) ? wp_unslash( $_REQUEST['visitor_key'] ) : '';
		$requested_key = self::normalize_session_key( $requested_key );

		if ( 0 !== strpos( $requested_key, self::GUEST_PREFIX ) ) {
			return '';
		}

		return $requested_key;
	}

	private static function request_has_session_cookie() {
		if ( empty( $_SERVER['HTTP_COOKIE'] ) ) {
			return false;
		}

		return false !== strpos( wp_unslash( $_SERVER['HTTP_COOKIE'] ), self::COOKIE_NAME . '=' );
	}

	private static function normalize_session_key( $session_key ) {
		$session_key = sanitize_text_field( $session_key );

		if ( preg_match( '/^' . preg_quote( self::USER_PREFIX, '/' ) . '[1-9][0-9]*$/', $session_key ) ) {
			return $session_key;
		}

		if ( preg_match( '/^' . preg_quote( self::GUEST_PREFIX, '/' ) . '[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $session_key ) ) {
			return strtolower( $session_key );
		}

		return '';
	}
}
