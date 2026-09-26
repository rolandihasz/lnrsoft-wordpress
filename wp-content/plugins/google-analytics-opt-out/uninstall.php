<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! WP_UNINSTALL_PLUGIN ) {
	exit();
}

/**
 * @var wpdb $wpdb
 */
global $wpdb;

if ( is_a( $wpdb, 'wpdb' ) ) {
	// Delete options
	$wpdb->query( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "gaoo_%"' );
}