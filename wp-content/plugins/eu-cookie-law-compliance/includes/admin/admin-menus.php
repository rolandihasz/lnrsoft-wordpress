<?php

/**
 * Submenu page
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class TPLIS_CL_Admin_Menu {

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
	}

	/**
	 * Add meun items
	 */
	public function add_menu() {

		add_submenu_page( 'options-general.php', __( 'TP Cookies Law', TPLIS_CL_DOMAIN ), __( 'TP Cookies Law', TPLIS_CL_DOMAIN ), 'manage_options', 'tplis_cl_settings', array( $this, 'settings_page' ) );
	}

	/**
	 * Settings page
	 */
	public function settings_page() {
		TPLIS_CL_Settings::output();
	}

}

$admin_menu = new TPLIS_CL_Admin_Menu();

