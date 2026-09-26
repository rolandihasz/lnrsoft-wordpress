<?php

/**
 * Installation related functions and actions.
 *
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class TPLIS_CL_Install {

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


		if ( !defined( 'TPLIS_CL_INSTALLING' ) ) {
			define( 'TPLIS_CL_INSTALLING', true );
		}

		self::create_options();

		$current_version = get_option( 'tplis_cl_version' );

		// Update plugin version
		update_option( 'tplis_cl_version', TPLIS_CL_VERSION );

	}
	
	/**
	 * Default options
	 */
	private static function create_options() {

		global $tplis_cl_settings;

		$sections = TPLIS_CL()->settings->settings_fields();

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

		update_option( TPLIS_CL_SETTINGS_KEY, $update_options );
	}
	
	/**
	 * Check version
	 */
	public static function check_version() {

		if ( !defined( 'IFRAME_REQUEST' ) ) {

			if ( get_option( 'tplis_cl_version' ) != TPLIS_CL_VERSION ) {
				self::install();
			}
		}
	}

}

TPLIS_CL_Install::init();
