<?php
/*
Plugin Name: Google Analytics Opt-Out
Plugin URI: https://wp-buddy.com/products/plugins/google-analytics-opt-out
Description: Provides an Opt-Out functionality for Google Analytics
Version: 2.3.6
Author: WP-Buddy
Author URI: https://wp-buddy.com
License: GPL2
Text Domain: google-analytics-opt-out
Domain Path: /languages/

Copyright 2023 WP-Buddy  (email : info@wp-buddy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {

	# this is still needed @see https://wordpress.org/support/topic/this-plugin-is-not-properly-prepared-for-localization/#post-6885466
	load_plugin_textdomain( 'google-analytics-opt-out' );
} );

define( 'GAOOP_FILE', __FILE__ );
define( 'GAOOP_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'GAOOP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {

	add_action( 'admin_notices', 'wpb_gaoop_old_php_notice' );

	function wpb_gaoop_old_php_notice() {

		printf(
			'<div class="notice error"><p>%s</p></div>',
			sprintf( __( 'You are using PHP in version %s. This version is outdated and cannot be used with the Google Analytics Opt-Out plugin. Please update to the latest PHP version in order to use this plugin. You can ask your provider on how to do this.', 'google-analytics-opt-out' ), PHP_VERSION )
		);
	}

	return;
}

require_once GAOOP_PATH . 'inc/functions.php';
require_once GAOOP_PATH . 'inc/admin.php';
require_once GAOOP_PATH . 'inc/shortcodes.php';
require_once GAOOP_PATH . 'inc/scripts.php';
require_once GAOOP_PATH . 'inc/settings.php';
require_once GAOOP_PATH . 'inc/frontend.php';


