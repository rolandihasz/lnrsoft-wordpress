<?php

/**
 * Installation related functions and actions.
 *
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DGWT_Euroscola_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {

		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Install 
	 */
	public static function install() {


		if ( !defined( 'DGWT_EQ_INSTALLING' ) ) {
			define( 'DGWT_EQ_INSTALLING', true );
		}

		self::create_options();
		self::create_tables();

		$current_version = get_option( 'tplis_cl_version' );

		// Update plugin version
		update_option( 'tplis_cl_version', DGWT_EQ_VERSION );

		// Flush rules after install
		flush_rewrite_rules();
	}
	
	/**
	 * Default options
	 */
	private static function create_options() {

		global $tplis_cl_settings;

		$sections = DGWT_EQ()->settings->settings_fields();

		$settings = array();

		if ( is_array( $sections ) && !empty( $sections ) ) {
			foreach ( $sections as $options ) {

				if ( is_array( $options ) && !empty( $options ) ) {

					foreach ( $options as $option ) {

						if ( isset( $option[ 'name' ] ) && !isset( $tplis_cl_settings[ $option[ 'name' ] ] ) ) {

							$settings[ $option[ 'name' ] ] = isset( $option[ 'default' ] ) ? $option[ 'default' ] : '';
						}
					}
				}
			}
		}

		$update_options = array_merge( $settings, $tplis_cl_settings );

		update_option( DGWT_EQ_SETTINGS_KEY, $update_options );
	}
	
	/**
	 * Set up the database tables
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		DGWT_EQ()->exams->create_table();
		DGWT_EQ()->answers->create_table();
	}

	/**
	 * Check version
	 */
	public static function check_version() {

		if ( !defined( 'IFRAME_REQUEST' ) ) {

			if ( get_option( 'tplis_cl_version' ) != DGWT_EQ_VERSION ) {
				self::install();
			}
		}
	}

}

DGWT_Euroscola_Install::init();
