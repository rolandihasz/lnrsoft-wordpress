<?php
/**
 * Uninstall Headers Security Advanced & HSTS WP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'hsts_max_age' );
delete_option( 'hsts_include_subdomains' );
delete_option( 'hsts_preload' );
delete_option( 'hsts_csp' );
delete_option( 'hsts_pp' );
delete_option( 'hsts_x_frame_options_url_field' );
delete_option( 'hsts_x_frame_options' );

delete_option( 'hsts_csp_report_uri' );

// Plugin state / migration bookkeeping.
delete_option( 'hsts_plugin_db_version' );
delete_option( 'hsts_show_migration_notice_v2' );
delete_option( 'hsts_show_migration_notice_v3' );
delete_option( 'hsts_htaccess_cleanup_pending' );

// 5.3.5 writer / probe state.
delete_option( 'hsts_htaccess_write_pending' );
delete_option( 'hsts_htaccess_write_failed' );
delete_option( 'hsts_htaccess_written_headers' );
delete_option( 'hsts_htaccess_confirmed' );
delete_option( 'hsts_probe_token' );
delete_option( 'hsts_backend_endpoint' );
delete_option( 'hsts_detected_server' );
delete_transient( 'hsts_probe_lock' );
delete_transient( 'hsts_run_check_throttle' );

// Stop the background probe if it is still scheduled.
$hsts_probe_event = wp_next_scheduled( 'hsts_probe_event' );
if ( $hsts_probe_event ) {
    wp_unschedule_event( $hsts_probe_event, 'hsts_probe_event' );
}

// Remove the managed .htaccess block (deactivation normally does this first;
// repeated here so an uninstall on a site that skipped a clean deactivate does
// not leave the block orphaned). Self-contained: the plugin code is not loaded
// during uninstall. Anchored strictly to our version-free markers, and matches
// legacy version-bearing markers too, so exactly one block is removed and the
// WordPress rewrite block / user rules are never touched.
if ( ! function_exists( 'WP_Filesystem' ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}
if ( function_exists( 'WP_Filesystem' ) && WP_Filesystem() ) {
    global $wp_filesystem;
    $hsts_home     = function_exists( 'get_home_path' ) ? get_home_path() : ABSPATH;
    $hsts_htaccess = $hsts_home . '.htaccess';
    if ( $wp_filesystem->exists( $hsts_htaccess )
        && $wp_filesystem->is_readable( $hsts_htaccess )
        && $wp_filesystem->is_writable( $hsts_htaccess ) ) {
        $hsts_contents = $wp_filesystem->get_contents( $hsts_htaccess );
        if ( is_string( $hsts_contents ) ) {
            $hsts_stripped = preg_replace(
                '/\R?[^\S\r\n]*#[^\S\r\n]*BEGIN[^\S\r\n]+(?:WordPress[^\S\r\n]+)?Headers Security Advanced & HSTS WP\b.*?#[^\S\r\n]*END[^\S\r\n]+(?:WordPress[^\S\r\n]+)?Headers Security Advanced & HSTS WP[^\r\n]*/is',
                '',
                $hsts_contents
            );
            if ( is_string( $hsts_stripped ) && $hsts_stripped !== $hsts_contents ) {
                $wp_filesystem->put_contents( $hsts_htaccess, $hsts_stripped );
            }
        }
    }
}

// Per-header suppression flags (5.3.4+ naming).
delete_option( 'hsts_disable_strict_transport_security' );
delete_option( 'hsts_disable_content_security_policy' );
delete_option( 'hsts_disable_permissions_policy' );
delete_option( 'hsts_disable_x_frame_options' );
delete_option( 'hsts_disable_x_content_type_options' );
delete_option( 'hsts_disable_referrer_policy' );
delete_option( 'hsts_disable_x_permitted_cross_domain_policies' );
delete_option( 'hsts_disable_cross_origin_opener_policy' );
delete_option( 'hsts_disable_cross_origin_resource_policy' );
delete_option( 'hsts_disable_access_control_allow_methods' );
delete_option( 'hsts_disable_access_control_allow_headers' );

// Per-header delivery mode (5.3.5+ tri-state: on|server|off).
delete_option( 'hsts_mode_strict_transport_security' );
delete_option( 'hsts_mode_content_security_policy' );
delete_option( 'hsts_mode_permissions_policy' );
delete_option( 'hsts_mode_x_frame_options' );
delete_option( 'hsts_mode_x_content_type_options' );
delete_option( 'hsts_mode_referrer_policy' );
delete_option( 'hsts_mode_x_permitted_cross_domain_policies' );
delete_option( 'hsts_mode_cross_origin_opener_policy' );
delete_option( 'hsts_mode_cross_origin_resource_policy' );
delete_option( 'hsts_mode_access_control_allow_methods' );
delete_option( 'hsts_mode_access_control_allow_headers' );

// Legacy suppression flags (<= 5.3.3) - remove any residue.
delete_option( 'disable_hsts_header' );
delete_option( 'disable_csp_header' );
delete_option( 'disable_x_content_type_options_header' );
delete_option( 'disable_x_frame_options_header' );



$pro_key = get_option( 'hsts_pro_license_key', '' );
if ( ! empty( $pro_key ) ) {
    
    $site_url = get_site_url();
    $salt     = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'hsts_pro_fallback_' . DB_NAME;
    $instance = substr( hash( 'sha256', $site_url . '|' . $salt ), 0, 32 );

    wp_remote_post( 'https://api.lemonsqueezy.com/v1/licenses/deactivate', array(
        'timeout' => 10,
        'headers' => array(
            'Accept'       => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
        'body'    => array(
            'license_key' => $pro_key,
            'instance_id' => $instance,
        ),
    ) );
}

// Pro options cleanup
$pro_options = array(
    'hsts_pro_license_key', 'hsts_pro_license_status', 'hsts_pro_license_expiry',
    'hsts_pro_license_token', 'hsts_pro_license_domain', 'hsts_pro_license_fail_count',
    'hsts_pro_license_last_check', 'hsts_pro_files_hash',
    'hsts_pro_scan_schedule', 'hsts_pro_alert_email', 'hsts_pro_last_scan',
    'hsts_pro_security_score', 'hsts_pro_scan_history', 'hsts_pro_csp_violations',
    'hsts_pro_previous_score', 'hsts_pro_previous_headers',
    'hsts_pro_generated_csp', 'hsts_pro_generated_csp_report_only',
    'hsts_pro_discovered_resources', 'hsts_pro_csp_report_only_active',
    'hsts_pro_csp_report_only_since',
    'hsts_pro_webhook_slack_url', 'hsts_pro_webhook_discord_url',
    'hsts_pro_webhook_teams_url', 'hsts_pro_webhook_custom_url',
    'hsts_pro_webhook_notify_on',
);
foreach ( $pro_options as $opt ) {
    delete_option( $opt );
}
wp_clear_scheduled_hook( 'hsts_pro_weekly_scan' );