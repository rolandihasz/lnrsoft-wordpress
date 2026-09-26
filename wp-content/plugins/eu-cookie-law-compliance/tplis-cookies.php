<?php

/**
 * Plugin Name: TP Cookies - EU Cookie Law Compliance
 * Plugin URI: https://wordpress.org/plugins/eu-cookie-law-compliance
 * Description: A simple way to show the Cookie Compliance with UK, Dutch and EU laws. Relevant and universal banner informs visitors about the acceptance of cookies.
 * Version: 1.0.3
 * Author: Damian Góra & mesmeriseme
 * Author URI: http://damiangora.com
 * Text Domain: tplis-cookies
 * Domain Path: /languages/
 * 
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'TPLIS_CL_Cookies_Law_Core' ) ) {

	final class TPLIS_CL_Cookies_Law_Core {

		private static $instance;
		private $tnow;
		public $settings;
		public $model;

		public static function get_instance() {
			if ( !isset( self::$instance ) && !( self::$instance instanceof TPLIS_CL_Cookies_Law_Core ) ) {
				self::$instance = new TPLIS_CL_Cookies_Law_Core;
				self::$instance->constants();
				
				if ( !self::$instance->check_requirements() ) {
					return;
				}
				
				self::$instance->includes();
				self::$instance->hooks();

				// Set up localisation
				self::$instance->load_textdomain();


				self::$instance->settings	 = new TPLIS_CL_Settings;
				self::$instance->model		 = new TPLIS_CL_Model;
			}
			self::$instance->tnow = time();

			return self::$instance;
		}

		/**
		 * Constructor Function
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Setup plugin constants
		 */
		private function constants() {

			$this->define( 'TPLIS_CL_VERSION', '1.0.3' );
			$this->define( 'TPLIS_CL_NAME', 'EU Cookie Law Compliance' );
			$this->define( 'TPLIS_CL_FILE', __FILE__ );
			$this->define( 'TPLIS_CL_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'TPLIS_CL_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'TPLIS_CL_DOMAIN', 'tplis-cookies' );

			$this->define( 'TPLIS_CL_SETTINGS_KEY', 'tplis_cl_settings' );
			
			$this->define( 'TPLIS_CL_COOKIE_NAME', 'tplis_cl_cookie_policy_accepted' );
			
			$this->define( 'TPLIS_CL_EVENT_ATTR', 'data-tplis-cl-event' );
			
			$this->define( 'TPLIS_CL_DEBUG', false );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		/*
		 * Check requirements
		 */

		private function check_requirements() {
			if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_php' ) );

				return false;
			}

			return true;
		}
		
		/*
		 * Notice: PHP version less than 5.3
		 */

		public function admin_notice_php() {
			?>
			<div class="error">
				<p>
					<?php
					_e( '<b>EU Cookie Law Compliance</b>: You need PHP version at least 5.3 to run this plugin. You are currently using PHP version ', DGWT_WCAS_DOMAIN );
					echo PHP_VERSION . '.';
					?>
				</p>
			</div>
			<?php
		}
		
		/**
		 * Include required core files.
		 */
		public function includes() {

			require_once TPLIS_CL_DIR . 'includes/functions.php';

			require_once TPLIS_CL_DIR . 'includes/install.php';

			require_once TPLIS_CL_DIR . 'includes/admin/settings/class-settings-api.php';
			require_once TPLIS_CL_DIR . 'includes/admin/settings/class-settings.php';
			
			require_once TPLIS_CL_DIR . 'includes/class-model.php';
			require_once TPLIS_CL_DIR . 'includes/class-cookie.php';
			
			require_once TPLIS_CL_DIR . 'includes/admin/admin-menus.php';
		}

		/**
		 * Actions and filters
		 */
		private function hooks() {

			add_action( 'admin_init', array( $this, 'admin_scripts' ) );
		}


		/*
		 * Enqueue admin sripts
		 */

		public function admin_scripts() {

			// Register CSS
			wp_register_style( 'tplis-cl-admin-style', TPLIS_CL_URL . 'assets/css/admin-style.css', array(), TPLIS_CL_VERSION );

			// Enqueue CSS            
			wp_enqueue_style( array(
				'tplis-cl-admin-style'
			) );
		}

		/*
		 * Register text domain
		 */

		private function load_textdomain() {
			$lang_dir = dirname( plugin_basename( TPLIS_CL_FILE ) ) . '/languages/';
			load_plugin_textdomain( TPLIS_CL_DOMAIN, false, $lang_dir );
		}

	}

}

// Init the plugin
function TPLIS_CL() {
	return TPLIS_CL_Cookies_Law_Core::get_instance();
}

add_action( 'init', 'TPLIS_CL' );
