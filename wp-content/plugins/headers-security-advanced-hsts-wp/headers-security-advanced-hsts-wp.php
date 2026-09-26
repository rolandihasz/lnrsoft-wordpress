<?php
/**
 * Plugin Name: Headers Security Advanced & HSTS WP
 * Plugin URI: https://openheaders.org
 * Description: Headers Security Advanced & HSTS WP - Simple, Light and Fast. The plugin uses advanced security rules that provide huge levels of protection and it is important that your site uses it. This step is important to submit your website and/or domain to an approved HSTS list. Google officially compiles this list and it is used by Chrome, Firefox, Opera, Safari, IE11 and Edge. You can forward your site to the official HSTS preload directory. Cross Site Request Forgery (CSRF) is a common attack with the installation of Headers Security Advanced & HSTS WP will help you mitigate CSRF on your WordPress site.
 * Version: 5.3.5
 * Text Domain: headers-security-advanced-hsts-wp
 * Domain Path: /languages
 * Author: 🐙 Andrea Ferro
 * Author URI: https://www.linkedin.com/in/andrea-ferro-55046186/
 *          __
 *      ___( o)>
 *      \ <_. )
 *       `---'   irn3.com
 * ---------------------------------------------------------
 * Headers Security Advanced & HSTS WP plugin for GPL v2
 * ---------------------------------------------------------
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! function_exists( 'add_action' ) ) {
    die( 'Don\'t try to be smart with us, only real ninjas can enter here!' );
}

const HSTS_PLUGIN_VERSION = '5.3.5';
const HSTS_STANDARD_VALUE_CSP = 'upgrade-insecure-requests;';
const HSTS_STANDARD_VALUE_PERMISSIONS_POLICY = 'accelerometer=(), autoplay=(), camera=(), cross-origin-isolated=(), display-capture=(self), encrypted-media=(), fullscreen=*, geolocation=(self), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), payment=*, picture-in-picture=*, publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=*, usb=(), xr-spatial-tracking=(), gamepad=(), serial=()';

/**
 * DB schema version for the plugin options. Bumped when a migration is needed.
 *   1 (implicit) = <= 5.3.3, dual emission (PHP wp_headers filter + .htaccess block).
 *   2            = 5.3.4, single-source PHP emission, no .htaccess writing.
 *   3            = 5.3.5, .htaccess writer restored + probe token seeded. PHP
 *                  keeps emitting; it self-suppresses a header only on a fresh,
 *                  proven confirmation (see includes/htaccess-writer.php). The
 *                  suppression half ships behind a feature flag (default off).
 */
const HSTS_PLUGIN_DB_VERSION = 3;

// .htaccess writer + self-suppression diagnostic probe. Loaded early so its
// hooks (init cron sync, option-change re-sync) register during plugin load.
require_once __DIR__ . '/includes/htaccess-writer.php';

/**
 * Canonical list of the security headers this plugin manages.
 *
 * Single source of truth: this drives both emission paths - the runtime PHP
 * dispatcher (below) and the .htaccess writer. On Apache/LiteSpeed the block is
 * written to .htaccess so cached and static responses are covered too; PHP
 * remains the source on dynamic pages. Duplicate headers are resolved by the
 * plugin itself (automatic self-suppression, plus the per-header delivery mode).
 *
 * Each entry: header name => [ 'value' => callable|string, 'option' => suppression option key ].
 * The per-header delivery mode (on|server|off) is derived from that option key;
 * 'off' means the plugin does not emit the header at all.
 *
 * Intentionally removed in 5.3.4:
 *   - X-Content-Security-Policy  (deprecated IE-only header, hard removal)
 *   - Cross-Origin-Embedder-Policy + -Report-Only (COEP off by default)
 *   - Cross-Origin-Opener-Policy-Report-Only (invalid report-to='default' syntax)
 *
 * @return array<string, array{value: callable|string, option: string}>
 */
function hsts_plugin_managed_header_map(): array {
    return array(
        'Strict-Transport-Security'          => array( 'value' => 'hsts_plugin_get_hsts_header',              'option' => 'hsts_disable_strict_transport_security' ),
        'Content-Security-Policy'            => array( 'value' => 'hsts_plugin_get_csp_header',               'option' => 'hsts_disable_content_security_policy' ),
        'Permissions-Policy'                 => array( 'value' => 'hsts_plugin_get_permissions_policy_header', 'option' => 'hsts_disable_permissions_policy' ),
        'X-Frame-Options'                    => array( 'value' => 'hsts_plugin_get_x_frame_options_header',    'option' => 'hsts_disable_x_frame_options' ),
        'X-Content-Type-Options'             => array( 'value' => 'nosniff',                                  'option' => 'hsts_disable_x_content_type_options' ),
        'Referrer-Policy'                    => array( 'value' => 'strict-origin-when-cross-origin',          'option' => 'hsts_disable_referrer_policy' ),
        'X-Permitted-Cross-Domain-Policies'  => array( 'value' => 'none',                                     'option' => 'hsts_disable_x_permitted_cross_domain_policies' ),
        'Cross-Origin-Opener-Policy'         => array( 'value' => 'unsafe-none',                              'option' => 'hsts_disable_cross_origin_opener_policy' ),
        'Cross-Origin-Resource-Policy'       => array( 'value' => 'cross-origin',                             'option' => 'hsts_disable_cross_origin_resource_policy' ),
        'Access-Control-Allow-Methods'       => array( 'value' => 'GET,POST',                                 'option' => 'hsts_disable_access_control_allow_methods' ),
        'Access-Control-Allow-Headers'       => array( 'value' => 'Content-Type, Authorization',             'option' => 'hsts_disable_access_control_allow_headers' ),
    );
}

/**
 * The tri-state option key for a header, derived from its legacy disable-flag
 * key: hsts_disable_<slug> -> hsts_mode_<slug>.
 */
function hsts_plugin_header_mode_option( string $disable_option ): string {
    return 'hsts_mode_' . substr( $disable_option, strlen( 'hsts_disable_' ) );
}

/**
 * Effective per-header delivery mode: 'on' | 'server' | 'off'.
 *
 *   on     - emit from PHP (+ .htaccess where supported). Default.
 *   server - keep writing it to .htaccess but drop the plugin's PHP copy, so
 *            the server layer is the single source (manual de-duplication).
 *   off    - remove entirely (neither PHP nor .htaccess).
 *
 * Reads the explicit tri-state option when set to a known value; otherwise
 * falls back to the legacy per-header disable flag so a box ticked BEFORE this
 * version keeps its original meaning (ticked = fully off) and never silently
 * becomes "server only".
 */
function hsts_plugin_header_mode( string $disable_option ): string {
    $mode = get_option( hsts_plugin_header_mode_option( $disable_option ), '' );
    if ( 'on' === $mode || 'server' === $mode || 'off' === $mode ) {
        return $mode;
    }
    return get_option( $disable_option ) ? 'off' : 'on';
}

/**
 * Resolve the tri-state mode from a header NAME (as emitted). Headers with no
 * toggle (e.g. the CSP report-only channel) are always 'on'.
 */
function hsts_plugin_header_mode_by_name( string $name ): string {
    $map = hsts_plugin_managed_header_map();
    if ( isset( $map[ $name ]['option'] ) ) {
        return hsts_plugin_header_mode( $map[ $name ]['option'] );
    }
    return 'on';
}

/**
 * Build the ordered name => value map of headers to emit, honouring the
 * per-header modes. Only 'off' headers are dropped here; 'server' headers are
 * kept (the writer needs them in .htaccess, and send_headers decides whether to
 * also emit the PHP copy). Empty values are skipped by the dispatcher.
 *
 * @return array<string, string>
 */
function hsts_plugin_get_managed_headers(): array {
    $headers = array();

    foreach ( hsts_plugin_managed_header_map() as $name => $def ) {
        if ( 'off' === hsts_plugin_header_mode( $def['option'] ) ) {
            continue; // Fully off: neither PHP nor .htaccess.
        }
        $value          = is_callable( $def['value'] ) ? call_user_func( $def['value'] ) : $def['value'];
        $headers[ $name ] = (string) $value;
    }

    // Optional CSP report-only channel, only when a report URI is configured
    // and the CSP header itself is not turned off.
    $report_uri = get_option( 'hsts_csp_report_uri' );
    if ( ! empty( $report_uri ) && 'off' !== hsts_plugin_header_mode( 'hsts_disable_content_security_policy' ) ) {
        $headers['Content-Security-Policy-Report-Only'] = (string) hsts_plugin_get_csp_report_only_header();
    }

    return $headers;
}

/**
 * Single emission dispatcher with a per-request guard.
 *
 * Registered on several early hooks so dynamic responses across contexts
 * (front-end, admin, login, REST) all receive the headers - matching the
 * coverage the old .htaccess block used to provide for dynamic pages. The
 * static guard guarantees the headers are emitted at most once per request,
 * so no context can double-emit. There is no longer a wp_headers filter.
 *
 * Note: static assets served directly by the web server (images, CSS/JS,
 * uploads) are not processed by PHP and therefore do not receive these
 * headers in this single-source model. This is a deliberate trade-off to
 * eliminate duplicate headers everywhere; .htaccess coverage for static
 * files may return as an explicit opt-in in a future release.
 *
 * Content-Security-Policy is intentionally not emitted in the admin/login
 * context. A user's restrictive front-end CSP would otherwise reach wp-admin
 * (which it historically did not, because the old .htaccess block sat on the
 * front-end rewrite path) and could break the block editor or login screen.
 * WordPress core also sets its own admin CSP (frame-ancestors 'self'). All
 * other security headers are still emitted on admin/login.
 */
function hsts_plugin_send_headers(): void {
    static $already_sent = false;

    if ( $already_sent ) {
        return;
    }
    $already_sent = true;

    if ( headers_sent() ) {
        return;
    }

    // No-emit self-check branch. This is the only way to obtain a page without
    // the security headers, and it is how the diagnostic probe measures what
    // the server layer (.htaccess) emits on its own (n_off). It arms only for a
    // loopback request carrying the per-site secret (see the locks in
    // hsts_plugin_is_selfcheck_request), so it is unreachable from outside.
    // Kept available regardless of the suppression feature flag so that "Run
    // check now" works even while passive suppression is off.
    if ( hsts_plugin_is_selfcheck_request() ) {
        header( 'X-Hsts-Selfcheck: 1' ); // sentinel: proves the branch ran on our vhost
        header( 'Cache-Control: no-store' ); // never cache a header-less response
        return;
    }

    $admin_context = hsts_plugin_is_admin_context();
    $suppress      = hsts_plugin_suppression_enabled();

    // Learn the backend's loopback port so the probe can reach it directly (see
    // hsts_plugin_capture_backend_endpoint). No context guard here: the capture
    // itself only records when SERVER_ADDR is a loopback literal, which is
    // exactly the request class served through the .htaccess-reading backend and
    // never the explicit-.php-to-FPM path (public address). Running it on every
    // dynamic hit - front-end, admin, login, REST - maximises the chance of
    // learning the port on heavily cached sites where front-end PHP rarely runs.
    if ( function_exists( 'hsts_plugin_capture_backend_endpoint' ) ) {
        hsts_plugin_capture_backend_endpoint();
    }

    // Record the server family from this real request so the .htaccess write
    // decision (which also runs in php-cli cron / WP-CLI, where SERVER_SOFTWARE
    // is unset) stays aligned with the server actually serving the site.
    if ( function_exists( 'hsts_plugin_remember_server' ) ) {
        hsts_plugin_remember_server();
    }

    foreach ( hsts_plugin_headers_for_context( hsts_plugin_get_managed_headers(), $admin_context ) as $name => $value ) {
        // (1) Automatic suppression - the primary mechanism. Drop PHP's own copy
        // only on a fresh, proven confirmation that the server layer already
        // emits this header on the dynamic response class.
        if ( $suppress && hsts_plugin_htaccess_confirmed( $name ) ) {
            continue;
        }
        // (2) Manual "server only" - the fallback for stacks where the probe
        // cannot arm. The admin chose to drop the PHP copy and let the server
        // serve it; honour that only when the server actually covers this header
        // (Apache/LiteSpeed with our block present). Otherwise emit from PHP so
        // the header is never silently lost - fail toward emit.
        if ( 'server' === hsts_plugin_header_mode_by_name( $name )
            && function_exists( 'hsts_plugin_server_covers_header' )
            && hsts_plugin_server_covers_header( $name ) ) {
            continue;
        }
        header( sprintf( '%s: %s', $name, $value ), true );
    }
}

/**
 * Whether the current request is the admin or login context, where the
 * plugin's CSP is intentionally not applied (see hsts_plugin_send_headers).
 */
function hsts_plugin_is_admin_context(): bool {
    return is_admin()
        || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] );
}

/**
 * Filter the managed-header map for the current context: drop empty values,
 * and drop Content-Security-Policy (+ report-only) in admin/login. Pure and
 * side-effect free so it is directly testable.
 *
 * @param array<string,string> $headers       name => value.
 * @param bool                 $admin_context  True on wp-admin / wp-login.
 * @return array<string,string>
 */
function hsts_plugin_headers_for_context( array $headers, bool $admin_context ): array {
    $out = array();
    foreach ( $headers as $name => $value ) {
        if ( '' === $value ) {
            continue;
        }
        if ( $admin_context
            && ( 'Content-Security-Policy' === $name || 'Content-Security-Policy-Report-Only' === $name ) ) {
            continue;
        }
        $out[ $name ] = $value;
    }
    return $out;
}
add_action( 'send_headers', 'hsts_plugin_send_headers' );
add_action( 'login_init', 'hsts_plugin_send_headers' );
add_action( 'admin_init', 'hsts_plugin_send_headers' );
add_action( 'rest_api_init', 'hsts_plugin_send_headers' );

/**
 * Back-compat shim.
 *
 * hsts_plugin_get_headers() was a public function for years and third-party
 * code may call it (or expect it as a wp_headers filter callback). It is kept
 * as a deprecated wrapper around the new single source of truth rather than
 * removed outright. It merges the managed headers into any array passed in,
 * so it still behaves as a wp_headers filter if someone re-hooks it.
 *
 * @deprecated 5.3.4 Use hsts_plugin_get_managed_headers() instead.
 *
 * @param array $headers Existing headers (when used as a filter).
 * @return array
 */
function hsts_plugin_get_headers( $headers = array() ) {
    if ( function_exists( '_deprecated_function' ) ) {
        _deprecated_function( __FUNCTION__, '5.3.4', 'hsts_plugin_get_managed_headers()' );
    }

    if ( ! is_array( $headers ) ) {
        $headers = array();
    }

    return array_merge( $headers, hsts_plugin_get_managed_headers() );
}

function hsts_plugin_settings(): void {
    add_options_page(
        __( 'Headers Security Advanced & HSTS WP', 'headers-security-advanced-hsts-wp' ),
        __( 'Headers Security Advanced & HSTS WP', 'headers-security-advanced-hsts-wp' ),
        'manage_options',
        'headers-security-advanced-hsts-wp-plugin',
        'hsts_plugin_settings_page'
    );
}
add_action( 'admin_menu', 'hsts_plugin_settings' );

function hsts_plugin_add_admin_style(): void {
    wp_register_style( 'hsts-plugin-admin', plugins_url( '/assets/css/style-dist.css', __FILE__ ), array(), HSTS_PLUGIN_VERSION );
    wp_enqueue_style( 'hsts-plugin-admin' );
}
add_action( 'admin_init', 'hsts_plugin_add_admin_style' );

add_action( 'init', function() {
    load_plugin_textdomain( 'headers-security-advanced-hsts-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

function hsts_plugin_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'headers-security-advanced-hsts-wp' ) );
    } ?>
<div class="wrap HeaderSecurityAdvancedHSTSWPROSHUEbkgsuccess">
    <?php
    printf( esc_html__( '%1$sYour website is finally safe! 🚀%2$s Implement enhanced security headers, HSTS preload for optimum website protection.', 'headers-security-advanced-hsts-wp' ), '<strong>', '</strong>' );
    ?>
</div>

<div class="wrap HeaderSecurityAdvancedHSTSWPROSHUEbkgpadw">
    <div class="HeaderSecurityAdvancedHSTSWPROSHUEbub1459bk">
        <div class="HeaderSecurityAdvancedHSTSWPROSHUEbub14591"></div>
        <div class="HeaderSecurityAdvancedHSTSWPROSHUEbub14592"></div>
    </div>

    <h2 class="HeaderSecurityAdvancedHSTSWPROSHUEhero2112">
        <?php esc_html_e( 'The security of your website is our priority! 🛸', 'headers-security-advanced-hsts-wp' ); ?>
    </h2>

    <div class="HeaderSecurityAdvancedHSTSWPROSHUEgridgap1546">
        <div class="HeaderSecurityAdvancedHSTSWPROSHUEtxgrh1">
        <?php
            printf( esc_html__( '%1$sHeaders Security Advanced & HSTS WP%2$s adds 10+ security headers to protect your WordPress site from phishing, data theft, clickjacking, XSS, and protocol downgrade attacks. Easy to configure, compatible with all servers and caching plugins.', 'headers-security-advanced-hsts-wp' ), '<b>', '</b>' );
        ?>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-top:20px;">
                <div style="background:linear-gradient(135deg,#0f135e,#5b06b0); padding:20px; border-radius:14px; color:#fff; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:-15px; right:-15px; width:60px; height:60px; background:rgba(255,255,255,.08); border-radius:50%;"></div>
                    <div style="font-size:11px; color:rgba(255,255,255,.7); margin-bottom:6px; letter-spacing:.5px; text-transform:uppercase;"><?php esc_html_e( 'Security Headers', 'headers-security-advanced-hsts-wp' ); ?></div>
                    <div style="font-size:32px; font-weight:800; line-height:1; position:relative; z-index:1;">10+</div>
                </div>
                <div style="background:#fff; padding:20px; border-radius:14px; border:1px solid #e8e0f3; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:-15px; right:-15px; width:60px; height:60px; background:#f1e3ff; border-radius:50%; opacity:.5;"></div>
                    <div style="font-size:11px; color:#575ba3; margin-bottom:6px; letter-spacing:.5px; text-transform:uppercase;"><?php esc_html_e( 'Active Sites', 'headers-security-advanced-hsts-wp' ); ?></div>
                    <div style="font-size:32px; font-weight:800; color:#0f135e; line-height:1; position:relative; z-index:1;">100K+</div>
                </div>
                <div style="background:#fff; padding:20px; border-radius:14px; border:1px solid #c8e6c9; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:-15px; right:-15px; width:60px; height:60px; background:#d4edda; border-radius:50%; opacity:.5;"></div>
                    <div style="font-size:11px; color:#575ba3; margin-bottom:6px; letter-spacing:.5px; text-transform:uppercase;"><?php esc_html_e( 'Top Grade', 'headers-security-advanced-hsts-wp' ); ?></div>
                    <div style="font-size:32px; font-weight:800; color:#28a745; line-height:1; position:relative; z-index:1;">A+</div>
                </div>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px;">
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">HSTS Preload</span>
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">CSP</span>
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">Permissions Policy</span>
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">X-Frame-Options</span>
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">Cross-Origin</span>
                <span style="display:inline-block; padding:6px 14px; background:#f1e3ff; color:#5b06b0; border-radius:100px; font-size:12px; font-weight:500;">Referrer-Policy</span>
            </div>
        </div>
        <div class="HeaderSecurityAdvancedHSTSWPROSHUEtxgrh1">
            <?php
                esc_html_e( 'I create projects available to everyone for free and I like to create simple but functional projects. Every feature this plugin offers today will remain completely free, forever. That is a promise I intend to keep.', 'headers-security-advanced-hsts-wp' );
            ?>
            <br /><br />
            <?php
                printf( esc_html__( 'Maintaining a plugin trusted by 100,000+ sites takes constant work: security patches, WordPress updates, new browser standards, support. To fund this effort, I built %1$sShield%2$s — a set of brand-new advanced tools for professionals who need more. Nothing existing moves behind a paywall. Your support through Shield directly funds free updates for everyone.', 'headers-security-advanced-hsts-wp' ), '<b>', '</b>' );
            ?>
            <br /><br />
            <table border="0px">
                <tr>
                    <td><a class="HeaderSecurityAdvancedHSTSWPROSHUEbksnack" href="https://www.buymeacoffee.com/tentacleplugins" target="_blank"><?php esc_attr_e( 'Buy me a coffee', 'headers-security-advanced-hsts-wp' ); ?></a></td>
                    <td><a class="HeaderSecurityAdvancedHSTSWPROSHUEbksnack" href="https://www.paypal.com/donate/?hosted_button_id=M72GQUM8CWTZS" target="_blank"><?php esc_attr_e( 'Donate via PayPal', 'headers-security-advanced-hsts-wp' ); ?></a></td>
                </tr>
            </table>
        </div>
        <a href="https://openheaders.org" class="HeaderSecurityAdvancedHSTSWPROSHUEbkg">
            <div class="HeaderSecurityAdvancedHSTSWPROSHUErw1">
                <?php
                    printf( esc_html__( '%1$sDo you need help with the Headers Security Advanced & HSTS WP plugin?%2$s Don\'t worry, I\'ve got it covered!', 'headers-security-advanced-hsts-wp' ), '<strong>', '</strong>' );
                ?>
            </div>
        </a>
        <a href="https://forms.gle/d8PYXYaKZJXRQzeh9" class="HeaderSecurityAdvancedHSTSWPROSHUEbkg">
            <div class="HeaderSecurityAdvancedHSTSWPROSHUErw1">
                <?php
                    printf( esc_html__( '%1$sHelp me decide what the next features of this plugin will be!%2$s Take part in a nerdy survey!', 'headers-security-advanced-hsts-wp' ), '<strong>', '</strong>' );
                ?>
            </div>
        </a>
    </div>

    <?php
    /**
     * Hook: hsts_pro_before_settings
     */
    do_action( 'hsts_pro_before_settings' );
    ?>

    <div id="hsts-panel-settings" class="hsts-pro-panel">
    <div class="HeaderSecurityAdvancedHSTSWPROSHUEgrid_settings HeaderSecurityAdvancedHSTSWPROSHUEselspace1128t">
        <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1440"><?php esc_html_e( 'Quick selection:', 'headers-security-advanced-hsts-wp' ); ?></h4>
        <div class="HeaderSecurityAdvancedHSTSWPROSHUEselspeed1127 HeaderSecurityAdvancedHSTSWPROSHUEselspace1128">
            <div><span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge1950"><a href="#HSTSPRELOADLISTSUB"><?php esc_html_e( 'Strict Transport Security (HSTS)', 'headers-security-advanced-hsts-wp' ); ?></a></span></div>
            <div><span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge1950"><a href="#HSTSCSP"><?php esc_html_e( 'Content Security Policy (CSP)', 'headers-security-advanced-hsts-wp' ); ?></a></span></div>
            <div><span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge1950"><a href="#HSTSPERMISSIONS"><?php esc_html_e( 'Permissions Policy', 'headers-security-advanced-hsts-wp' ); ?></a></span></div>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields( 'hsts-plugin-settings-group' ); ?>
            <?php do_settings_sections( 'hsts-plugin-settings-group' ); ?>
            <table class="form-table" id="HSTSPRELOADLISTSUB">
                <tr>
                    <th class="HeaderSecurityAdvancedHSTSWPROSHUEcltab1636">
                        <label for="HeaderSecurityAdvancedHSTSWPROSHUErunFieldId">
                            <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e( 'Max-Age: ', 'headers-security-advanced-hsts-wp' ); ?></h4>
                        </label>
                        <input type="text" name="hsts_max_age" value="<?php echo esc_attr( get_option( 'hsts_max_age' ) ); ?>" />
                        <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php
                                    esc_html_e(
                                        'The Max-Age parameter specifies the period of time (in seconds) for which the browser should store the HSTS information. During this time period, the browser will always use HTTPS to communicate with the website, even if the visitor has entered "http" or an HTTP link in the address bar. This helps protect the website and its visitors from man-in-the-middle (MITM) attacks and other security threats.',
                                        'headers-security-advanced-hsts-wp',
                                    );
                                ?>
                            </span>
                        </p>
                        <div class="HeaderSecurityAdvancedHSTSWPROSHUEbox333">
                            <p>
                                <span class="HeaderSecurityAdvancedHSTSWPROSHUEtxexttextSize">
                                    <?php
                                        esc_html_e(
                                            'It is advisable to set "max-age" to a high value, such as a full year (31536000 seconds). This ensures that browsers continue to store security information for a long period of time, which helps protect users from man-in-the-middle attacks. However, it is important to keep in mind that setting the value too high could cause problems if you need to change your site\'s SSL configuration in the future. Therefore, it is important to carefully consider your usage and security needs before setting the value.',
                                            'headers-security-advanced-hsts-wp',
                                        );
                                    ?>
                                </span>
                            </p>
                        </div>
                    </th>
                    <th class="HeaderSecurityAdvancedHSTSWPROSHUEcltab1636">
                        <label for="HeaderSecurityAdvancedHSTSWPROSHUErunFieldId">
                            <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e( 'Include Subdomains: ', 'headers-security-advanced-hsts-wp' ); ?></h4>
                        </label>
                        <input type="checkbox" name="hsts_include_subdomains" value="1" <?php checked( 1, get_option( 'hsts_include_subdomains' ), true ); ?> />
                        <label for="hsts_preload_1">Enable include subdomains</label>
                        <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php
                                    esc_html_e(
                                        'The "includeSubDomains" flag specifies that the effect of the header should also be applied to subdomains of the domain. When this directive is present, all requests to any subdomains of your domain are automatically redirected to the HTTPS protocol, providing enhanced security for the website and the users who visit it.',
                                        'headers-security-advanced-hsts-wp',
                                    );
                                ?>
                            </span>
                        </p>
                        <div class="HeaderSecurityAdvancedHSTSWPROSHUEbox333">
                            <p>
                                <span class="HeaderSecurityAdvancedHSTSWPROSHUEtxexttextSize">
                                    <?php
                                        esc_html_e(
                                            'We recommend enabling the "includeSubDomains" option in the HSTS header to ensure that all subsections of your site (subdomains) are only loaded via HTTPS. However, before enabling this flag, it is important to ensure that all subdomains, resources and web services working under your domain are available via HTTPS and that there are no compatibility issues with any external services used by your site.',
                                            'headers-security-advanced-hsts-wp',
                                        );
                                    ?>
                                </span>
                            </p>
                        </div>
                    </th>

                    <th class="HeaderSecurityAdvancedHSTSWPROSHUEcltab1636">
                        <label for="HeaderSecurityAdvancedHSTSWPROSHUErunFieldId">
                            <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e( 'Preload: ', 'headers-security-advanced-hsts-wp' ); ?></h4>
                        </label>
                        <input type="checkbox" name="hsts_preload" value="1" <?php checked( 1, get_option( 'hsts_preload' ), true ); ?> />
                        <label for="hsts_preload_2">Enable preload</label>
                        <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php
                                printf(
                                    esc_html__(
                                        'The "preload" flag allows the website to be included in the %1$sHSTS preload list%2$s, which instructs browsers to always use HTTPS connection for the site and its subdomains, without ever making insecure HTTP requests.',
                                        'headers-security-advanced-hsts-wp',
                                    ),
                                    '<a href="https://hstspreload.org/">',
                                    '</a>',
                                );
                                ?>
                            </span>
                        </p>
                        <div class="HeaderSecurityAdvancedHSTSWPROSHUEbox333">
                            <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEtxexttextSize">
                                <?php
                                printf(
                                    esc_html__(
                                        'Enabling preload further helps prevent any potential man-in-the-middle attacks, thus improving connection security as far as it concerns HSTS. Please note that even if this flag is enabled, your website still needs to be manually submitted to the list. Please also note that inclusion in the preload list has permanent consequences and is not easy to undo, so you should only enable this flag and submit your website after making sure that all of the resources and services within your domain (and its subdomains, if includeSubDomains is also enabled) are indeed accessible and functional via HTTPS. %1$sLearn more%2$s.',
                                        'headers-security-advanced-hsts-wp'
                                    ),
                                    '<a href="https://hstspreload.org/#removal/">',
                                    '</a>'
                                );
                                ?>
                            </span>
                            </p>
                        </div>
                    </th>
                </tr>
                <tr id="HSTSCSP">
    <th scope="row">
        <label for="hsts_csp">
            <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e('CSP Header Contents', 'headers-security-advanced-hsts-wp'); ?></h4>
        </label>
        <p>
            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                <?php
                printf(
                    esc_html__(
                        'HTTP Content-Security-Policy header controls website resources, reducing XSS risk by specifying allowed server origins and script endpoints.',
                        'headers-security-advanced-hsts-wp',
                    ),
                );
                ?>
            </span>
        </p>
    </th>
    <td>
        <textarea id="hsts_csp" name="hsts_csp" class="HeaderSecurityAdvancedHSTSWPROSHUE_textarea" rows="5" cols="50" placeholder="if not customized the value of the plugin is used"><?php echo esc_textarea(get_option('hsts_csp')); ?></textarea>
    </td>
</tr>


<tr id="HSTSCSPReportURI">
    <th scope="row">
        <label for="hsts_csp_report_uri">
            <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e('CSP Report URI', 'headers-security-advanced-hsts-wp'); ?></h4>
        </label>
        <p>
            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                <?php esc_html_e('Enter your custom URL (Sentry, URIports, Datadog, and Report URI) for CSP violation reports.', 'headers-security-advanced-hsts-wp'); ?>
            </span>
        </p>
    </th>
    <td>
        <input type="text" id="hsts_csp_report_uri" class="HeaderSecurityAdvancedHSTSWPROSHUE_textarea" name="hsts_csp_report_uri" value="<?php echo esc_attr(get_option('hsts_csp_report_uri')); ?>" placeholder="Enter custom url CSP" />
    </td>
</tr>

              <tr id="HSTSPERMISSIONS">
                    <th scope="row">
                            <label for="hsts_pp">
                                <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e('Permissions Policy Contents', 'headers-security-advanced-hsts-wp'); ?></h4>
                            </label>
                        <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php
                                printf(
                                    esc_html__(
                                        'The HTTP Permissions-Policy header provides a mechanism to allow and deny the use of browser features in a document or within any <iframe> elements in the document.',
                                        'headers-security-advanced-hsts-wp',
                                    ),
                                );
                                ?>
                            </span>
                            <div class="HeaderSecurityAdvancedHSTSWPROSHUEbox333">
                            <p>
                                <span class="HeaderSecurityAdvancedHSTSWPROSHUEtxexttextSize">
                                    Enter one or more directives separated by commas. Use double quotation marks for URLs and leave the parentheses empty to disable a feature. Do not add Permissions-Policy: as it is inserted automatically.</span>
                            </p>
                        </div>
                        </p>
                    </th>
                    <td>
                        <textarea id="hsts_pp" name="hsts_pp" class="HeaderSecurityAdvancedHSTSWPROSHUE_textarea" rows="5" cols="50" placeholder="if not customized the value of the plugin is used"><?php echo esc_textarea(get_option('hsts_pp')); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                             <label for="hsts_pp">
                                <h4 class="HeaderSecurityAdvancedHSTSWPROSHUEtboxy1348"><?php esc_html_e('X-Frame-Options', 'headers-security-advanced-hsts-wp'); ?></h4>
                            </label>
                            <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php
                                printf(
                                    esc_html__(
                                        'The X-Frame-Options HTTP response header can be used to indicate whether or not a browser should be allowed to render a page in a <frame>, <iframe>, <embed> or <object>. Sites can use this to avoid click-jacking attacks, by ensuring that their content is not embedded into other sites.',
                                        'headers-security-advanced-hsts-wp',
                                    ),
                                );
                                ?>
                            </span>
                        </p>
                    </th>
                    <td>
                    <select id="hsts_x_frame_options" name="hsts_x_frame_options">
                        <option value="DENY" <?php selected('DENY', get_option('hsts_x_frame_options')); ?>>DENY</option>
                        <option value="SAMEORIGIN" <?php selected('SAMEORIGIN', get_option('hsts_x_frame_options')); ?>>SAMEORIGIN</option>
                        <option value="ALLOW-FROM" <?php selected('ALLOW-FROM', get_option('hsts_x_frame_options')); ?>>ALLOW-FROM</option>
                    </select>
                    <br /><br />
                    <div id="hsts_x_frame_options_url_field" style="display: none;">
                        <input type="text" name="hsts_x_frame_options_allow_from_url" value="<?php echo esc_attr(get_option('hsts_x_frame_options_allow_from_url')); ?>" placeholder="https://example.com">
                    </div>

                    </td>
                    <br />
                    <details class="HeaderSecurityAdvancedHSTSWPROSHUEadvanced" style="margin-top:10px;">
                        <summary style="cursor:pointer; font-weight:600;"><?php esc_html_e( 'Advanced: per-header delivery', 'headers-security-advanced-hsts-wp' ); ?></summary>
                        <p>
                            <span class="HeaderSecurityAdvancedHSTSWPROSHUEctd3">
                                <?php esc_html_e( 'Leave every header on "On" unless you actually have a duplicate. On Apache and LiteSpeed the plugin writes these headers to your .htaccess so they cover cached and static responses, and also emits them from PHP on dynamic pages, removing any duplicate automatically. If a duplicate still remains on your server, set that header to "Server only": the plugin keeps the .htaccess copy and drops its own PHP copy, so the header is served exactly once and is never lost. "Off" removes the header entirely. If your server does not read .htaccess (nginx, IIS), "Server only" safely behaves like "On" and the header keeps coming from PHP.', 'headers-security-advanced-hsts-wp' ); ?>
                            </span>
                        </p>
                        <?php
                        foreach ( hsts_plugin_managed_header_map() as $hsts_header_name => $hsts_header_def ) :
                            $hsts_mode_key = hsts_plugin_header_mode_option( $hsts_header_def['option'] );
                            $hsts_mode_cur = hsts_plugin_header_mode( $hsts_header_def['option'] );
                            ?>
                            <div class="HeaderSecurityAdvancedHSTSWPROSHUEbadge2450" style="display:flex; align-items:center; gap:10px; margin:5px 0; flex-wrap:wrap;">
                                <label for="<?php echo esc_attr( $hsts_mode_key ); ?>" style="min-width:320px;"><code><?php echo esc_html( $hsts_header_name ); ?></code></label>
                                <select id="<?php echo esc_attr( $hsts_mode_key ); ?>" name="<?php echo esc_attr( $hsts_mode_key ); ?>">
                                    <option value="on" <?php selected( 'on', $hsts_mode_cur ); ?>><?php esc_html_e( 'On (default)', 'headers-security-advanced-hsts-wp' ); ?></option>
                                    <option value="server" <?php selected( 'server', $hsts_mode_cur ); ?>><?php esc_html_e( 'Server only (de-duplicate)', 'headers-security-advanced-hsts-wp' ); ?></option>
                                    <option value="off" <?php selected( 'off', $hsts_mode_cur ); ?>><?php esc_html_e( 'Off', 'headers-security-advanced-hsts-wp' ); ?></option>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </details>
                </tr>

            </table>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var selectElement = document.getElementById('hsts_x_frame_options');
                    var urlField = document.getElementById('hsts_x_frame_options_url_field');

                    function toggleUrlField() {
                        if (selectElement.value === 'ALLOW-FROM') {
                            urlField.style.display = '';
                        } else {
                            urlField.style.display = 'none';
                        }
                    }

                    toggleUrlField();

                    selectElement.addEventListener('change', toggleUrlField);
                });
            </script>

            <?php
            submit_button();
            ?>
        </form>
    </div>
    </div><!-- /hsts-panel-settings -->
        <?php
        /**
         * Hook: hsts_settings_after_form
         */
        do_action( 'hsts_settings_after_form' );
        ?>
</div>
<?php
}
function hsts_plugin_settings_init(): void {
    register_setting( 'hsts-plugin-settings-group', 'hsts_max_age' );
    register_setting( 'hsts-plugin-settings-group', 'hsts_include_subdomains' );
    register_setting( 'hsts-plugin-settings-group', 'hsts_preload' );
    register_setting( 'hsts-plugin-settings-group', 'hsts_csp', 'sanitize_text_field');
    register_setting( 'hsts-plugin-settings-group', 'hsts_pp', 'sanitize_text_field');
    register_setting( 'hsts-plugin-settings-group', 'hsts_x_frame_options');
    register_setting( 'hsts-plugin-settings-group', 'hsts_x_frame_options_allow_from_url');
    register_setting( 'hsts-plugin-settings-group', 'hsts_csp_report_uri', 'sanitize_text_field');

    // Per-header delivery mode (tri-state: on | server | off). Replaces the
    // old boolean disable flag; the legacy flag is still read as a fallback in
    // hsts_plugin_header_mode() so a box ticked before this version keeps its
    // original meaning (ticked = off).
    foreach ( hsts_plugin_managed_header_map() as $def ) {
        register_setting(
            'hsts-plugin-settings-group',
            hsts_plugin_header_mode_option( $def['option'] ),
            array( 'sanitize_callback' => 'hsts_plugin_sanitize_header_mode' )
        );
    }

}

/**
 * Whitelist the per-header mode to a known value; anything else is coerced to
 * the safe default ('on' = emit), so a malformed POST can never disable or
 * mangle a header.
 */
function hsts_plugin_sanitize_header_mode( $value ): string {
    $value = is_string( $value ) ? $value : '';
    return in_array( $value, array( 'on', 'server', 'off' ), true ) ? $value : 'on';
}
add_action( 'admin_init', 'hsts_plugin_settings_init' );

function hsts_plugin_get_hsts_header(): string {
    $max_age            = get_option( 'hsts_max_age' );
    $include_subdomains = get_option( 'hsts_include_subdomains' );
    $preload            = get_option( 'hsts_preload' );

    $header_tokens = array( "max-age={$max_age}" );
    if ( $include_subdomains ) {
        $header_tokens[] = 'includeSubDomains';
    }
    if ( $preload ) {
        $header_tokens[] = 'preload';
    }

    return implode( '; ', $header_tokens );
}

// 
function hsts_plugin_get_csp_header(): string {
    $csp        = get_option('hsts_csp');
    $report_uri = get_option('hsts_csp_report_uri');

    if (!empty($csp)) {
        $csp = str_replace(
            array('‘', '’'),
            "'",
            $csp
        );

        $csp = str_replace(
            array('“', '”'),
            '"',
            $csp
        );

        if (strpos($csp, '"') !== false) {
            $csp = str_replace('"', "'", $csp);
        }

        $stripped = trim($csp, " \t\n\r\0\x0B'\";");
        if ($stripped === '') {
            $csp = '';
        }
    }

    if (!empty($csp) && !empty($report_uri)) {
        $report_to  = "report-to {$report_uri}";
        $report_uri = "report-uri {$report_uri}";
        $csp       .= " {$report_to}; {$report_uri};";
    }

    return empty($csp) ? HSTS_STANDARD_VALUE_CSP : $csp;
}

function hsts_plugin_get_csp_report_only_header(): string {
    $csp = get_option('hsts_csp');
    $report_uri = get_option('hsts_csp_report_uri');

    if (!empty($report_uri)) {
        $report_to = "report-to {$report_uri}";
        $report_uri = "report-uri {$report_uri}";
        $csp .= " {$report_to}; {$report_uri};";
    }

    return $csp;
}

function hsts_plugin_get_permissions_policy_header(): string {
    $pp = get_option('hsts_pp');
    return empty($pp) ? HSTS_STANDARD_VALUE_PERMISSIONS_POLICY : $pp;
}

function hsts_plugin_get_x_frame_options_header(): string {
    $x_frame_options = get_option('hsts_x_frame_options', 'SAMEORIGIN');
    if ($x_frame_options === 'ALLOW-FROM') {
        $allow_from_url = get_option('hsts_x_frame_options_allow_from_url', '');
        if (!empty($allow_from_url)) {
            return "ALLOW-FROM $allow_from_url";
        }
        return "ALLOW-FROM"; 
    }
    return $x_frame_options ?: 'SAMEORIGIN';
}

/**
 * Legacy shim. 5.3.4 removed the .htaccess block; 5.3.5 restores it through the
 * dedicated writer in includes/htaccess-writer.php. This wrapper only strips any
 * block so callers that expected the old "cleanup" contract keep working; it is
 * safe to call repeatedly (idempotent).
 */
function hsts_plugin_update_htaccess(): bool {
    return hsts_plugin_cleanup_htaccess();
}

function hsts_plugin_maybe_migrate_legacy_csp(): void {
    $csp = get_option('hsts_csp');

    if (empty($csp)) {
        return;
    }

    $search  = array('‘', '’', '“', '”');
    $replace = array("'", "'", '"', '"');
    $csp_normalized = str_replace($search, $replace, $csp);

    $legacy_csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; frame-src 'self' https:; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests";

    $legacy_csp_normalized = str_replace($search, $replace, $legacy_csp);

    $normalize_spaces = static function (string $value): string {
        $value = trim($value);
        $value = rtrim($value, ';');
        $value = preg_replace('/\s+/', ' ', $value);
        return $value;
    };

    if ($normalize_spaces($csp_normalized) === $normalize_spaces($legacy_csp_normalized)) {
        update_option('hsts_csp', HSTS_STANDARD_VALUE_CSP);
    }
}

function hsts_plugin_activate(): void {
    // Detect a brand-new install BEFORE we add any default options: no stored
    // schema version and no long-standing option (hsts_max_age has shipped for
    // years). A fresh install has no legacy behaviour to migrate from and
    // nothing to warn about, so it must skip the upgrade-only migration notice.
    $is_fresh_install = ( false === get_option( 'hsts_plugin_db_version', false ) )
        && ( false === get_option( 'hsts_max_age', false ) );

    if ( ! get_option( 'hsts_max_age' ) ) {
        add_option( 'hsts_max_age', '63072000' );
    }
    if ( ! get_option( 'hsts_include_subdomains' ) ) {
        add_option( 'hsts_include_subdomains', '0' );
    }
    if ( ! get_option( 'hsts_preload' ) ) {
        add_option( 'hsts_preload', '0' );
    }
    if ( ! get_option( 'hsts_csp' ) ) {
        add_option( 'hsts_csp', HSTS_STANDARD_VALUE_CSP );
    }
    if ( ! get_option( 'hsts_pp' ) ) {
        add_option( 'hsts_pp', HSTS_STANDARD_VALUE_PERMISSIONS_POLICY );
    }
    if ( ! get_option( 'hsts_x_frame_options' ) ) {
        add_option( 'hsts_x_frame_options', 'SAMEORIGIN' );
    }

    if ( $is_fresh_install ) {
        // Mark the schema current so the upgrade-only migrations (and their
        // one-time admin notice) never run for a site that started life on this
        // version. The managed .htaccess block is still written below.
        update_option( 'hsts_plugin_db_version', HSTS_PLUGIN_DB_VERSION );
    }

    hsts_plugin_maybe_migrate_legacy_csp();

    // Run the full data migration on activation too, so a fresh activate over
    // an existing 5.3.x install migrates immediately rather than waiting for
    // the next page load.
    hsts_plugin_run_migrations();

    // Always (re)write the managed block on activation. run_migrations only
    // writes when crossing into v3, so a plain reactivation (already v3, block
    // removed on the prior deactivation) would otherwise come back headerless
    // at the server layer until the next settings save.
    hsts_plugin_write_htaccess();
    hsts_plugin_schedule_probe();
}
register_activation_hook( __FILE__, 'hsts_plugin_activate' );

register_activation_hook(__FILE__, 'hsts_plugin_flush_rewrite_rules');

/**
 * Attempt to remove any legacy plugin block from .htaccess.
 *
 * @return bool True when the file was cleaned OR there was nothing to clean
 *              (definitively done). False when the filesystem/file was not
 *              available so the caller should retry later.
 */
function hsts_plugin_cleanup_htaccess(): bool {
    $filesystem = hsts_plugin_get_filesystem();
    if ( null === $filesystem ) {
        return false; // Filesystem unavailable (e.g. FTP creds) - retry later.
    }

    $htaccess_file = get_home_path() . '.htaccess';

    // Distinguish "no file" from "file present but locked". get_writable path
    // helpers collapse both to null, which would let us wrongly declare the
    // cleanup done on a site whose .htaccess is merely unreadable/unwritable -
    // leaving a legacy block orphaned forever with no further retry.
    if ( ! $filesystem->exists( $htaccess_file ) ) {
        // No .htaccess at all (nginx, or never created): nothing to clean, and
        // nothing can appear later that we'd need to strip. Definitively done.
        if ( function_exists( 'hsts_plugin_set_written_headers' ) ) {
            hsts_plugin_set_written_headers( null );
        }
        return true;
    }

    if ( ! $filesystem->is_readable( $htaccess_file ) || ! $filesystem->is_writable( $htaccess_file ) ) {
        // File exists but is locked right now: it may still contain a legacy
        // block. Keep the pending flag alive and retry on a later request.
        return false;
    }

    $htaccess_contents = $filesystem->get_contents( $htaccess_file );
    if ( false === $htaccess_contents ) {
        return false; // Could not read - retry later.
    }

    $cleaned = hsts_plugin_strip_htaccess_block( $htaccess_contents );

    // Only write when something actually changed, so this stays a genuine
    // no-op on sites that never had a block.
    if ( $cleaned !== $htaccess_contents ) {
        if ( ! $filesystem->put_contents( $htaccess_file, $cleaned ) ) {
            return false; // Write failed - retry later.
        }
        // Removing a very old (5.0.01) block that lived INSIDE the WordPress
        // rewrite block can leave WordPress's cached rewrite_rules stale, so
        // schedule a regeneration. We do this via a soft, LAZY flush - never a
        // direct WP_Rewrite::flush_rules() here: this runs on plugins_loaded,
        // before init, so a direct flush would rebuild rules while Polylang,
        // custom post types and taxonomies have not yet registered their
        // filters, breaking multilingual/CPT routing (reported with Polylang
        // Pro). Deleting the cached option makes WordPress rebuild the rules
        // lazily on a later request, after init, when everything is present.
        hsts_plugin_schedule_soft_rewrite_flush();
    }

    // The managed block is gone: drop the coverage record so a "server only"
    // header falls back to PHP emission instead of being silently lost.
    if ( function_exists( 'hsts_plugin_set_written_headers' ) ) {
        hsts_plugin_set_written_headers( null );
    }

    return true;
}

/**
 * Request a lazy rewrite-rules regeneration without a hard flush.
 *
 * Clearing the cached option is enough: WordPress transparently rebuilds the
 * rules on the next request that needs them, after init has run and all
 * plugins have registered their rewrite tags/rules. This never rewrites the
 * .htaccess WordPress block (a hard flush_rules(true) would).
 */
function hsts_plugin_schedule_soft_rewrite_flush(): void {
    delete_option( 'rewrite_rules' );
}

/**
 * Remove any legacy plugin header block from .htaccess contents.
 *
 * Two passes, both anchored strictly to OUR markers and safe against
 * neighbouring content (WordPress rewrite block, other plugins):
 *
 *   1. PRIMARY: modern + uppercase-legacy blocks that carry a "BEGIN" word.
 *      Non-greedy and DOTALL so header values containing "#" (legit in CSP)
 *      no longer break matching as the old [^#]+ regex did.
 *
 *   2. LEGACY (no "BEGIN"): very old blocks (5.0.01 "- x", 5.0.20 " x") that
 *      opened with just "# Headers Security Advanced & HSTS WP <version>".
 *      Line-anchored with a MANDATORY version number so a casual mention of
 *      the plugin name in a comment is never matched.
 *
 * Both passes are terminated by our "# END ... HSTS WP" marker, tolerate CRLF,
 * and are idempotent (running again over cleaned content changes nothing).
 *
 * Known accepted residue: 5.0.20's buggy updater could append a bare
 * "Header set Strict-Transport-Security" line with no marker. It is
 * unmatchable by any marker-anchored regex and is deliberately left in place
 * (a structural pass keyed on "Header set" would risk eating other plugins'
 * directives).
 */
function hsts_plugin_strip_htaccess_block( string $contents ): string {
    $primary = '/\R?[^\S\r\n]*#[^\S\r\n]*BEGIN[^\S\r\n]+(?:WordPress[^\S\r\n]+)?Headers Security Advanced & HSTS WP\b.*?#[^\S\r\n]*END[^\S\r\n]+(?:WordPress[^\S\r\n]+)?Headers Security Advanced & HSTS WP[^\r\n]*/is';
    $out     = preg_replace( $primary, '', $contents );
    if ( null === $out ) {
        return $contents; // Regex failure: never destroy the file.
    }

    $legacy = '/^[^\S\r\n]*#[^\S\r\n]*Headers Security Advanced & HSTS WP[^\S\r\n]+(?:Version[^\S\r\n]+|-[^\S\r\n]+)?\d+\.\d+(?:\.\d+)?.*?^[^\S\r\n]*#[^\S\r\n]*END[^\S\r\n]+(?:WordPress[^\S\r\n]+)?Headers Security Advanced & HSTS WP[^\r\n]*\R?/ims';
    $out2   = preg_replace( $legacy, '', $out );
    if ( null === $out2 ) {
        return $out;
    }

    return $out2;
}

function hsts_plugin_flush_rewrite_rules(): void {
    global $wp_rewrite;
    if ( $wp_rewrite instanceof WP_Rewrite ) {
        $wp_rewrite->flush_rules();
    }
}

function hsts_plugin_delete_old_options(): void {
    
    delete_option( 'HEADERS_SECURITY_ADVANCED_HSTS_WP_PLUGIN_VERSION' );
}

/**
 * Version-gated, idempotent data migration.
 *
 * Runs on every load via plugins_loaded but does real work only when the
 * stored DB version is behind. This is deliberately not driven by
 * upgrader_process_complete: that hook runs with the old code loaded and does
 * not fire for every update path (manual upload, WP-CLI, staging sync, etc.).
 * A version option checked at runtime covers all of them exactly once.
 *
 * Migration to DB v2 (5.3.4):
 *   - Strip any leftover .htaccess header block (dual-emission source), with
 *     retry via hsts_htaccess_cleanup_pending if the filesystem is unavailable.
 *   - Reset (delete, do not copy forward) all 4 legacy disable_* flags: their
 *     semantics changed (they used to disable only the PHP side while .htaccess
 *     kept emitting; now a single source honours them fully). The mis-named
 *     disable_csp_header actually gated Permissions-Policy, so copying it would
 *     silently suppress that header. Clearing them + a one-time admin notice
 *     prevents any site from silently losing a header it thought was merely
 *     "de-duplicated"; the new per-header boxes start unchecked.
 */
function hsts_plugin_run_migrations(): void {
    $stored = (int) get_option( 'hsts_plugin_db_version', 1 );

    if ( $stored < 2 ) {
        hsts_plugin_migrate_options_to_v2();
        // Option migrations are done in the DB; safe to bump the version now.
        // The .htaccess cleanup is tracked separately (it may need retries)
        // and must not gate the version bump, or option migrations would run
        // forever on sites where the filesystem is unavailable.
        update_option( 'hsts_plugin_db_version', 2 );
        update_option( 'hsts_htaccess_cleanup_pending', 1 );
    }

    // Retry the .htaccess cleanup until it definitively succeeds, so a site
    // whose filesystem was unavailable at update time never keeps an orphan
    // block. Cleared as soon as cleanup reports done. (v2 removed the block;
    // v3 below re-writes a fresh one, so this runs first and is a no-op once
    // the v3 write has landed.)
    if ( get_option( 'hsts_htaccess_cleanup_pending' ) ) {
        if ( hsts_plugin_cleanup_htaccess() ) {
            delete_option( 'hsts_htaccess_cleanup_pending' );
        }
    }

    if ( $stored < 3 ) {
        hsts_plugin_migrate_options_to_v3();
        update_option( 'hsts_plugin_db_version', 3 );
        // (Re)write the managed block now, and keep retrying on later loads if
        // the filesystem is not available yet. The write is a no-op on nginx /
        // IIS (server does not read .htaccess) and on any locked filesystem.
        update_option( 'hsts_htaccess_write_pending', 1 );
    }

    if ( get_option( 'hsts_htaccess_write_pending' ) ) {
        $result = hsts_plugin_write_htaccess();
        // Stop retrying once we either wrote successfully, confirmed there is
        // nothing to write (server unsupported / all headers disabled), or a
        // roll-back fired. Keep the flag only for transient filesystem issues.
        $done = array( 'written', 'written_unverified', 'unchanged', 'no_headers', 'unsupported_server' );
        if ( $result['ok'] && in_array( $result['reason'], $done, true ) ) {
            delete_option( 'hsts_htaccess_write_pending' );
        } elseif ( ! $result['ok'] && 'verify_failed_rolled_back' === $result['reason'] ) {
            delete_option( 'hsts_htaccess_write_pending' );
        }
    }
}
add_action( 'plugins_loaded', 'hsts_plugin_run_migrations' );

/**
 * Self-heal: on a real admin request (SERVER_SOFTWARE reliable), if we believe a
 * block was written but this server does not read .htaccess, remove the stray
 * block. Covers sites where an earlier build wrote it from a php-cli context.
 * Cheap: gated on the autoloaded coverage record, so no work on healthy sites.
 */
function hsts_plugin_heal_stray_htaccess_block(): void {
    $written = get_option( 'hsts_htaccess_written_headers' );
    if ( empty( $written ) ) {
        return;
    }
    if ( ! function_exists( 'hsts_plugin_server_supports_htaccess' )
        || hsts_plugin_server_supports_htaccess() ) {
        return;
    }
    hsts_plugin_write_htaccess(); // unsupported-server path strips the block + clears the record
}
add_action( 'admin_init', 'hsts_plugin_heal_stray_htaccess_block' );

function hsts_plugin_migrate_options_to_v2(): void {
    // Reset all legacy disable_* flags. We deliberately do not copy any value
    // forward: the old flags only disabled the PHP side while .htaccess kept
    // emitting, so their meaning has changed. A user who ticked the (mis-named)
    // "CSP" box to de-duplicate would otherwise silently lose Permissions-Policy
    // entirely. Clearing them + an admin notice makes the change visible.
    $legacy_flags = array(
        'disable_hsts_header',
        'disable_csp_header',                    // actually gated Permissions-Policy
        'disable_x_content_type_options_header',
        'disable_x_frame_options_header',
    );
    $any_was_set = false;
    foreach ( $legacy_flags as $flag ) {
        if ( get_option( $flag ) ) {
            $any_was_set = true;
        }
        delete_option( $flag );
    }
    if ( $any_was_set ) {
        update_option( 'hsts_show_migration_notice_v2', 1 );
    }

    // Drop the very old plugin-version option if still present.
    delete_option( 'HEADERS_SECURITY_ADVANCED_HSTS_WP_PLUGIN_VERSION' );
}

/**
 * Migration to DB v3 (5.3.5): seed the per-site probe secret used by the
 * loopback self-check. The .htaccess (re)write itself is driven separately via
 * hsts_htaccess_write_pending so it can retry if the filesystem is not yet
 * available. Any confirmation state from an interrupted run is cleared so the
 * fresh writer starts from emit-everything.
 */
function hsts_plugin_migrate_options_to_v3(): void {
    hsts_plugin_get_probe_token();          // generates + stores on first call
    delete_option( 'hsts_htaccess_confirmed' ); // start from a clean, emit-all state

    // Carry each legacy per-header disable flag forward to the new tri-state as
    // 'off' - the exact meaning it had when the user ticked it. Never map a
    // ticked box to 'server only'. Only seeds a mode that is not already set,
    // so a value chosen on this version is never overwritten.
    foreach ( hsts_plugin_managed_header_map() as $def ) {
        $mode_key = hsts_plugin_header_mode_option( $def['option'] );
        if ( '' === (string) get_option( $mode_key, '' ) && get_option( $def['option'] ) ) {
            update_option( $mode_key, 'off' );
        }
    }

    // Re-announce the delivery change with corrected wording. 5.3.5 restores
    // .htaccess writing, so the old v2 banner (which said headers are no longer
    // written to .htaccess) is now false. A new key means anyone who dismissed
    // the v2 notice still sees this one. Runs only on the <3 -> 3 crossing, and
    // fresh installs never reach here (activation seeds db_version current), so
    // brand-new sites are not shown an upgrade notice.
    delete_option( 'hsts_show_migration_notice_v2' ); // retire the stale banner
    update_option( 'hsts_show_migration_notice_v3', 1 );
}

/**
 * Dismissible admin notice explaining the 5.3.4 semantic change to the
 * "Resolve duplicate headers" checkboxes. Shown once, on the plugin's own
 * settings page, until dismissed.
 */
function hsts_plugin_migration_notice(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! get_option( 'hsts_show_migration_notice_v3' ) ) {
        return;
    }

    $dismiss_url = wp_nonce_url(
        add_query_arg( 'hsts_dismiss_notice', 'v3' ),
        'hsts_dismiss_notice_v3'
    );
    ?>
    <div class="notice notice-warning is-dismissible">
        <p>
            <strong><?php esc_html_e( 'Headers Security Advanced & HSTS WP', 'headers-security-advanced-hsts-wp' ); ?></strong> &mdash;
            <?php esc_html_e( 'This update restores server-level delivery: on Apache and LiteSpeed the security headers are again written to your .htaccess, so they also cover cached and static responses, while PHP stays the source on dynamic pages. The plugin takes care of any duplicate headers itself. A previous update also reset the old "Resolve duplicate headers" checkboxes, so please review your settings and re-check any header you intentionally want disabled.', 'headers-security-advanced-hsts-wp' ); ?>
            <a href="<?php echo esc_url( admin_url( 'options-general.php?page=headers-security-advanced-hsts-wp-plugin' ) ); ?>"><?php esc_html_e( 'Open settings', 'headers-security-advanced-hsts-wp' ); ?></a>
            &middot;
            <a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'headers-security-advanced-hsts-wp' ); ?></a>
        </p>
    </div>
    <?php
}
add_action( 'admin_notices', 'hsts_plugin_migration_notice' );

function hsts_plugin_maybe_dismiss_notice(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! isset( $_GET['hsts_dismiss_notice'] ) || 'v3' !== $_GET['hsts_dismiss_notice'] ) {
        return;
    }
    check_admin_referer( 'hsts_dismiss_notice_v3' );
    delete_option( 'hsts_show_migration_notice_v3' );
}
add_action( 'admin_init', 'hsts_plugin_maybe_dismiss_notice' );

function hsts_plugin_add_dashboard_widget(): void {
    wp_add_dashboard_widget(
        'wpexplorer_dashboard_widget',
        esc_html__( 'Headers Security Advanced & HSTS WP', 'headers-security-advanced-hsts-wp' ),
        'hsts_plugin_get_dashboard_widget_contents'
    );
}
add_action( 'wp_dashboard_setup', 'hsts_plugin_add_dashboard_widget' );

function hsts_plugin_get_dashboard_widget_contents(): void {
    ?>
        <h2>
            <span style="color:#0f135e;font-weight: 800;"><?php esc_html_e( 'Congratulations, you are safe!', 'headers-security-advanced-hsts-wp' ); ?></span> 🦖
        </h2>
        <p>
            <?php
                printf(
                    esc_html__(
                        '%1$sThe Headers Security Advanced & HSTS WP%2$s project implements HTTP response headers that your site can use to increase the security of your website. The plug-in will automatically set up all Best Practices (you don’t have to think about anything). You will also be able to change the headers simply in the plugin settings.',
                        'headers-security-advanced-hsts-wp',
                    ),
                    '<b>',
                    '</b>',
                );
            ?>
        </p>
        <table border="0px">
            <tr>
                <td>
                    <a class="HeaderSecurityAdvancedHSTSWPROSHUEbksnacka" href="https://www.buymeacoffee.com/tentacleplugins" target="_blank"><?php esc_attr_e( 'Buy me a coffee', 'headers-security-advanced-hsts-wp' ); ?></a>
                </td>
                <td>
                    <a class="HeaderSecurityAdvancedHSTSWPROSHUEbksnacka" href="https://www.paypal.com/donate/?hosted_button_id=M72GQUM8CWTZS" target="_blank"><?php esc_attr_e( 'Donate via PayPal', 'headers-security-advanced-hsts-wp' ); ?></a>
                </td>
            </tr>
        </table>
        <p>
            <span class="SentinelHeadersUnlimitedExtensionSHUEtxextSizeCenter">
                <?php
                    printf(
                        esc_html__(
                            'Security is a right, not a privilege. Plugin version %s.',
                            'headers-security-advanced-hsts-wp',
                        ),
                        esc_html( HSTS_PLUGIN_VERSION ),
                    );
                ?>
            </span>
        </p>
    <?php
}

function hsts_plugin_add_plugin_action_links( $links, $file ) {
    
    if ( ! is_array( $links ) ) {
        $links = array();
    }

    static $this_plugin;

    if ( ! $this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }

    if ( $file === $this_plugin ) {
        $settings_url = admin_url( 'options-general.php?page=headers-security-advanced-hsts-wp-plugin' );

        $donate_hstswp_link  = '<a href="https://www.paypal.com/donate/?hosted_button_id=M72GQUM8CWTZS" target="_blank"><b>Donate a coffee</b></a>';
        $setting_hstswp_link = '<a href="' . esc_url( $settings_url ) . '">Settings</a>';
        $support_hstswp_link = '<a href="https://openheaders.org" target="_blank">Support</a>';

        array_unshift( $links, $support_hstswp_link );
        array_unshift( $links, $setting_hstswp_link );
        array_unshift( $links, $donate_hstswp_link );
    }
    
    return $links;
}
add_filter( 'plugin_action_links', 'hsts_plugin_add_plugin_action_links', 10, 2 );



function hsts_plugin_deactivate(): void {
    hsts_plugin_cleanup_htaccess();
    hsts_plugin_delete_old_options();

    // Stop the background probe and drop transient suppression state so the
    // plugin leaves nothing scheduled or half-decided behind it.
    $probe_event = wp_next_scheduled( 'hsts_probe_event' );
    if ( $probe_event ) {
        wp_unschedule_event( $probe_event, 'hsts_probe_event' );
    }
    delete_option( 'hsts_htaccess_confirmed' );
    delete_option( 'hsts_htaccess_write_pending' );
    delete_option( 'hsts_htaccess_written_headers' );
    delete_transient( 'hsts_probe_lock' );
}
register_deactivation_hook( __FILE__, 'hsts_plugin_deactivate' );

register_deactivation_hook(__FILE__, 'hsts_plugin_flush_rewrite_rules');

// Note: migration is no longer tied to upgrader_process_complete (that hook
// runs with the old code and misses several update paths). It is handled by
// the version-gated hsts_plugin_run_migrations() on plugins_loaded instead.

function hsts_plugin_get_filesystem(): ?WP_Filesystem_Base {
    // WP_Filesystem() lives in wp-admin/includes/file.php, which is not loaded
    // on front-end requests. Migrations run on plugins_loaded for every
    // request, so we must load it ourselves or the first post-update front-end
    // hit fatals on an undefined function.
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if ( true !== WP_Filesystem() ) {
        return null;
    }

    global $wp_filesystem;

    return $wp_filesystem;
}

function hsts_plugin_get_writable_htaccess_path( WP_Filesystem_Base $filesystem ): ?string {
    $htaccess_file = get_home_path() . '.htaccess';

    if ( ! $filesystem->exists( $htaccess_file ) || ! $filesystem->is_readable( $htaccess_file ) || ! $filesystem->is_writable( $htaccess_file ) ) {
        return null;
    }

    return $htaccess_file;
}

/**
 * 
 */
$hsts_pro_loader = plugin_dir_path( __FILE__ ) . 'pro/loader.php';
if ( file_exists( $hsts_pro_loader ) ) {
    require_once $hsts_pro_loader;
}