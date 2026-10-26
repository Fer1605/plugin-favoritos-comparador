<?php
/**
 * Plugin Name: Custom Product Lists
 * Description: Plugin para marcar productos del post type vehiculo como favoritos o comparar, guardando la selección en una tabla custom e integrándolo con Elementor Loop Grid.
 * Version: 1.0.111
 * Author: Fer Santizo
 * Author URI: https://github.com/Fer1605
 * Plugin URI: https://github.com/Fer1605/plugin-favoritos-comparador
 * License: GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: custom-product-lists
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CPL_VERSION', '1.0.111' );
define( 'CPL_FILE', __FILE__ );
define( 'CPL_PATH', plugin_dir_path( __FILE__ ) );
define( 'CPL_URL', plugin_dir_url( __FILE__ ) );
define( 'CPL_BASENAME', plugin_basename( __FILE__ ) );

require_once CPL_PATH . 'includes/class-installer.php';

final class CPL_Plugin {
	const MAX_SELECTIONS    = 8;
	const MAX_COMPARE       = 4;
	const COMPARE_SHORTCODE = 'cpl_compare';

	/**
	 * Límite de vehículos según el tipo de lista.
	 *
	 * @param string $list_type favorite|compare.
	 * @return int
	 */
	public static function get_max_selections( $list_type ) {
		return 'compare' === $list_type ? self::MAX_COMPARE : self::MAX_SELECTIONS;
	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {

		// Verifica que Elementor esté activo antes de continuar.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
			return;
		}

		// Aquí iremos cargando los archivos y funcionalidades del plugin.
		$this->includes();
		$this->hooks();

        CPL_Session::init();
        CPL_Ajax::init();
        CPL_Query::init();
        CPL_Shortcodes::init();
	}

	private function includes() {
		// Más adelante cargaremos aquí:
		require_once CPL_PATH . 'includes/class-session.php';
		require_once CPL_PATH . 'includes/class-database.php';
        require_once CPL_PATH . 'includes/class-ajax.php';
		require_once CPL_PATH . 'includes/class-query.php';
		require_once CPL_PATH . 'includes/class-shortcodes.php';
	}

	private function hooks() {
		// Más adelante registraremos aquí:
		// widgets de Elementor
		// scripts y estilos
		// hooks del query para Loop Grid
        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_assets' ] );
        add_action( 'wp_head', [ $this, 'output_cookie_sync_script' ], 1 );
        add_action( 'wp_head', [ $this, 'output_compare_pending_guard' ], 2 );
        add_action( 'wp_head', [ $this, 'output_favorite_grid_pending_guard' ], 2 );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	public static function is_elementor_editor_context() {
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		$elementor       = \Elementor\Plugin::$instance;
		$is_edit_mode    = isset( $elementor->editor ) && method_exists( $elementor->editor, 'is_edit_mode' ) && $elementor->editor->is_edit_mode();
		$is_preview_mode = isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode();

		return $is_edit_mode || $is_preview_mode;
	}

	private function get_reload_on_remove_list_type() {
		if ( is_page( 'comparar' ) ) {
			return 'compare';
		}

		if ( is_page( 'mis-favoritos' ) ) {
			return 'favorite';
		}

		return '';
	}

	public function register_frontend_assets() {
		wp_register_style(
			'cpl-frontend',
			CPL_URL . 'assets/css/frontend.css',
			[],
			CPL_VERSION
		);

		wp_enqueue_style( 'cpl-frontend' );

		// Mantiene el hover visible en Elementor, pero evita toda interacciÃ³n del plugin.
		$is_editor_context = self::is_elementor_editor_context();

		wp_register_script(
			'cpl-frontend',
			CPL_URL . 'assets/js/frontend.js',
			[ 'jquery' ],
			CPL_VERSION,
			true
		);

		$script_data = [
			'ajax_url'              => '',
			'nonce'                 => '',
			'session_key'           => '',
			'cookie_name'           => CPL_Session::COOKIE_NAME,
			'visitor_storage_key'   => CPL_Session::STORAGE_KEY,
			'selection_storage_key' => 'cpl_selection_state',
			'is_editor_context'     => $is_editor_context,
			'reload_on_remove'      => $this->get_reload_on_remove_list_type(),
			'favorite_page_url'     => home_url( '/mis-favoritos/' ),
			'compare_page_url'      => home_url( '/comparar/' ),
			'max_selections'        => self::MAX_SELECTIONS,
			'max_compare'           => self::MAX_COMPARE,
		];

		if ( ! $is_editor_context ) {
			$script_data['ajax_url'] = admin_url( 'admin-ajax.php' );
			$script_data['nonce']    = wp_create_nonce( 'cpl_nonce' );

			if ( is_user_logged_in() ) {
				$script_data['session_key'] = CPL_Session::get_or_create_session_key();
			}
		}

		wp_localize_script(
			'cpl-frontend',
			'CPL_Data',
			$script_data
		);

		wp_enqueue_script( 'cpl-frontend' );
	}

	public function output_cookie_sync_script() {
		if ( is_admin() || self::is_elementor_editor_context() ) {
			return;
		}
		?>
		<script>
		(function () {
			var storageKey = <?php echo wp_json_encode( CPL_Session::STORAGE_KEY ); ?>;
			var cookieName = <?php echo wp_json_encode( CPL_Session::COOKIE_NAME ); ?>;
			var reloadFlag = <?php echo wp_json_encode( CPL_Session::RELOAD_FLAG ); ?>;

			try {
				var visitorKey = window.localStorage.getItem(storageKey);

				if (!visitorKey) {
					return;
				}

				var hasCookie = document.cookie.split(';').some(function (item) {
					return item.trim().indexOf(cookieName + '=') === 0;
				});

				if (hasCookie) {
					window.sessionStorage.removeItem(reloadFlag);
					return;
				}

				var cookie = [
					cookieName + '=' + encodeURIComponent(visitorKey),
					'path=/',
					'max-age=' + (30 * 24 * 60 * 60),
					'SameSite=Lax'
				];

				if (window.location.protocol === 'https:') {
					cookie.push('Secure');
				}

				document.cookie = cookie.join('; ');

				if (window.sessionStorage.getItem(reloadFlag) !== '1') {
					window.sessionStorage.setItem(reloadFlag, '1');
					window.location.reload();
					return;
				}

				window.sessionStorage.removeItem(reloadFlag);
			} catch (error) {
				// No interrumpir el render si el navegador bloquea storage.
			}
		})();
		</script>
		<?php
	}

	public function output_compare_pending_guard() {
		if ( is_admin() || self::is_elementor_editor_context() || ! is_page( 'comparar' ) ) {
			return;
		}
		?>
		<style id="cpl-compare-pending-guard">
		html.cpl-compare-pending [data-cpl-compare-host],
		html.cpl-compare-pending [data-cpl-selection-count="compare"]{
			visibility:hidden !important;
		}
		</style>
		<script>
		document.documentElement.classList.add('cpl-compare-pending');
		</script>
		<?php
	}

	public function output_favorite_grid_pending_guard() {
		if ( is_admin() || self::is_elementor_editor_context() || ! is_page( 'mis-favoritos' ) ) {
			return;
		}
		?>
		<style id="cpl-favorite-grid-pending-guard">
		html.cpl-favorite-grid-pending .elementor-loop-container,
		html.cpl-favorite-grid-pending .e-loop,
		html.cpl-favorite-grid-pending .elementor-posts-container{
			visibility:hidden !important;
		}
		</style>
		<script>
		document.documentElement.classList.add('cpl-favorite-grid-pending');
		</script>
		<?php
	}

    public function register_widgets( $widgets_manager ) {
        	require_once CPL_PATH . 'elementor/class-widget-favorite-toggle.php';
	        require_once CPL_PATH . 'elementor/class-widget-compare-toggle.php';

	        $widgets_manager->register( new \CPL_Widget_Favorite_Toggle() );
	        $widgets_manager->register( new \CPL_Widget_Compare_Toggle() );
    }

	public function elementor_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong>Custom Product Lists</strong> requiere que <strong>Elementor</strong> esté instalado y activo.
			</p>
		</div>
		<?php
	}
}

register_activation_hook( CPL_FILE, [ 'CPL_Installer', 'activate' ] );

new CPL_Plugin();
