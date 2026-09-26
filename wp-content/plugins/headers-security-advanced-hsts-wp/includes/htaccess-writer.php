<?php
/**
 * .htaccess writer + self-suppression diagnostic probe.
 *
 * Writes the security-header block to the site's root .htaccess so cached HTML
 * and static files served directly by the web server carry the headers, without
 * producing a duplicate where the server layer appends its own copy (LiteSpeed).
 * Duplicates are avoided by a single decision point: PHP suppresses its own copy
 * of a header only when a recent, positive probe has proven the server layer
 * already emits that header on the dynamic response class. The default in every
 * error or unknown path is to emit (fail toward emit).
 *
 * See docs/RELEASE-5.3.5-PLAN.md for the full rationale and the two-read table.
 *
 * Passive suppression sits behind a feature flag (hsts_plugin_suppression_enabled)
 * that ships off: the writer runs and PHP always emits, so the writer can be
 * validated before the suppression half is enabled. The loopback self-check
 * branch and the on-demand probe ("Run check now") are always available, so the
 * diagnostic works regardless of the flag.
 *
 * @package Headers_Security_Advanced_HSTS_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Stable, version-free .htaccess marker name. The version lives as a comment
 * inside the block, never on the BEGIN/END lines, so insert/replace keys on a
 * marker that stays constant across releases and blocks cannot stack.
 */
if ( ! defined( 'HSTS_HTACCESS_MARKER' ) ) {
    define( 'HSTS_HTACCESS_MARKER', 'Headers Security Advanced & HSTS WP' );
}

/**
 * Hard cap on how long a suppression confirmation is trusted. WP-Cron fires on
 * traffic, not on a clock, so this - not the probe interval - is the real
 * exposure bound: a header can stay suppressed for at most this long after the
 * .htaccess silently stops covering it. Conservative on purpose (48h).
 */
if ( ! defined( 'HSTS_CONFIRM_TTL' ) ) {
    define( 'HSTS_CONFIRM_TTL', 172800 );
}

/**
 * Whether PHP may suppress its own header copy on a positive probe confirmation.
 * Ships off: the writer is active and PHP always emits. Flip with
 * `define( 'HSTS_PLUGIN_ENABLE_SUPPRESSION', true )` or the filter below. Only
 * the consuming half is gated; writing and probing are not.
 */
function hsts_plugin_suppression_enabled(): bool {
    $default = defined( 'HSTS_PLUGIN_ENABLE_SUPPRESSION' ) ? (bool) HSTS_PLUGIN_ENABLE_SUPPRESSION : false;
    return (bool) apply_filters( 'hsts_plugin_suppression_enabled', $default );
}

/* -------------------------------------------------------------------------
 * Server detection
 * ---------------------------------------------------------------------- */

/**
 * Best-effort web-server family: apache | litespeed | nginx | iis | unknown.
 */
function hsts_plugin_detect_server(): string {
    $sw = isset( $_SERVER['SERVER_SOFTWARE'] ) ? (string) $_SERVER['SERVER_SOFTWARE'] : '';

    if ( false !== stripos( $sw, 'litespeed' ) ) {
        return 'litespeed';
    }
    if ( false !== stripos( $sw, 'nginx' ) ) {
        return 'nginx';
    }
    if ( false !== stripos( $sw, 'apache' ) ) {
        return 'apache';
    }
    if ( false !== stripos( $sw, 'microsoft-iis' ) || false !== stripos( $sw, 'iis' ) ) {
        return 'iis';
    }

    global $is_nginx, $is_IIS, $is_apache;
    if ( ! empty( $is_nginx ) ) {
        return 'nginx';
    }
    if ( ! empty( $is_IIS ) ) {
        return 'iis';
    }
    if ( ! empty( $is_apache ) ) {
        return 'apache';
    }

    return 'unknown';
}

/**
 * Remember the server family seen on a genuine web request, where
 * SERVER_SOFTWARE is reliable. The write decision runs on plugins_loaded for
 * every request - including php-cli wp-cron and WP-CLI, where SERVER_SOFTWARE is
 * unset and live detection degrades to "unknown". Persisting the value observed
 * on a real request keeps the write decision aligned with what the admin panel
 * shows. Only definitive families are stored; "unknown" never overwrites.
 */
function hsts_plugin_remember_server(): void {
    if ( 'cli' === PHP_SAPI ) {
        return;
    }
    if ( empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
        return;
    }
    $family = hsts_plugin_detect_server();
    if ( 'unknown' === $family ) {
        return;
    }
    if ( (string) get_option( 'hsts_detected_server', '' ) !== $family ) {
        update_option( 'hsts_detected_server', $family, true );
    }
}

/**
 * Server family to base the write decision on. Prefers live detection; when the
 * current request carries no SERVER_SOFTWARE (php-cli cron / WP-CLI), falls back
 * to the family a real web request recorded, so the write path and the panel
 * agree on the same server.
 */
function hsts_plugin_effective_server(): string {
    $live = hsts_plugin_detect_server();
    if ( 'unknown' !== $live ) {
        return $live;
    }
    $remembered = (string) get_option( 'hsts_detected_server', '' );
    return '' !== $remembered ? $remembered : 'unknown';
}

/**
 * Whether this server reads .htaccess so writing it is meaningful. Apache and
 * LiteSpeed do; nginx/IIS do not (no plugin can change that - hence the manual
 * snippet). When the server is genuinely unknown - no SERVER_SOFTWARE on this
 * request and no family recorded from an earlier one - we do not write. The
 * presence of a "# BEGIN WordPress" block is not evidence: nginx sites carry
 * that block too, inert. Not writing is the safe outcome: PHP keeps emitting on
 * dynamic pages; the only thing forgone is header coverage on server-cached
 * static responses, which is better than writing blindly into the root
 * .htaccess of a server that ignores it.
 */
function hsts_plugin_server_supports_htaccess(): bool {
    $server = hsts_plugin_effective_server();
    return 'apache' === $server || 'litespeed' === $server;
}

/**
 * Human label for a server slug. Shared by the panel and the probe result so
 * the two never drift.
 */
function hsts_plugin_server_label( string $slug ): string {
    $map = array(
        'apache'    => 'Apache',
        'litespeed' => 'LiteSpeed',
        'nginx'     => 'nginx',
        'iis'       => 'IIS',
        'unknown'   => __( 'unknown', 'headers-security-advanced-hsts-wp' ),
    );
    return isset( $map[ $slug ] ) ? $map[ $slug ] : $slug;
}

/* -------------------------------------------------------------------------
 * Backend loopback endpoint discovery (for the direct-to-backend probe)
 * ---------------------------------------------------------------------- */

/**
 * The only destinations the probe is allowed to connect to. The probe must
 * reach the *local* backend; permitting anything else would turn a writable
 * option (or a stray filter) into an SSRF primitive. Host names are refused on
 * purpose (no DNS, no rebinding) - only the two loopback literals are accepted.
 */
function hsts_plugin_is_loopback_target( string $host ): bool {
    $host = trim( $host, '[]' );
    return '127.0.0.1' === $host || '::1' === $host;
}

/**
 * Learn the backend's own port + scheme from the request environment so the
 * probe can reach Apache/LiteSpeed directly over loopback, bypassing any
 * reverse proxy (e.g. nginx). Behind such a proxy the proxy's X-Forwarded-For
 * makes REMOTE_ADDR non-loopback, so the self-check no-emit branch never arms
 * and the two reads become indistinguishable.
 *
 * Security: only the port (validated integer) and the scheme are persisted.
 * SERVER_ADDR is kept for display and is never used as a connection target -
 * the probe always dials a hardcoded loopback literal. SERVER_PORT / SERVER_ADDR
 * come from the request environment and, depending on proxy config, could in
 * principle be nudged (e.g. an honoured X-Forwarded-Port). That is harmless
 * here: a wrong port just yields a refused or mismatched loopback connection
 * with no sentinel, so the decision falls toward emit. It cannot redirect the
 * probe off-box, because the destination is loopback by construction and
 * re-verified before every request (hsts_plugin_probe_fetch).
 *
 * Called only from genuine front-end page views (not admin/login/REST/AJAX/
 * cron); on a reverse-proxied stack those are exactly the requests served
 * through the .htaccess-reading backend.
 */
function hsts_plugin_capture_backend_endpoint(): void {
    // Learn only from a request that actually reached the local backend over
    // loopback. On a reverse-proxied stack (nginx -> Apache) the pretty-URL
    // front-end path is proxied to the backend's own loopback port, so Apache
    // computes SERVER_ADDR from its listening socket (127.0.0.1/::1) and
    // SERVER_PORT is exactly the backend port the probe must dial. The
    // explicit-.php path (wp-cron / admin-ajax / xmlrpc straight to PHP-FPM)
    // instead carries the box's public address and the edge port (e.g. 443);
    // learning from it would aim the probe at the proxy rather than the backend.
    // Gating on a loopback SERVER_ADDR is the precise discriminator and keeps the
    // value trustworthy: a port observed on a public-facing address is never
    // recorded. SERVER_ADDR could in principle be nudged by proxy config, but
    // that only ever makes us record less (skip), never point the probe off-box
    // (the probe dials a hardcoded loopback literal, re-verified in
    // hsts_plugin_probe_fetch).
    $addr = isset( $_SERVER['SERVER_ADDR'] ) ? (string) $_SERVER['SERVER_ADDR'] : '';
    if ( ! hsts_plugin_is_loopback_target( $addr ) ) {
        return;
    }
    $port = isset( $_SERVER['SERVER_PORT'] ) ? (int) $_SERVER['SERVER_PORT'] : 0;
    if ( $port < 1 || $port > 65535 ) {
        return;
    }
    $https = ( function_exists( 'is_ssl' ) && is_ssl() );

    $stored = get_option( 'hsts_backend_endpoint' );
    if ( is_array( $stored )
        && (int) ( isset( $stored['port'] ) ? $stored['port'] : 0 ) === $port
        && (bool) ( isset( $stored['https'] ) ? $stored['https'] : false ) === $https
        && hsts_plugin_is_loopback_target( (string) ( isset( $stored['addr'] ) ? $stored['addr'] : '' ) ) ) {
        return; // Unchanged and already loopback-sourced: no write churn.
    }

    // Autoloaded (default) because it is read on every front-end request; the
    // value is a tiny array. Kept out of the .htaccess re-sync trigger by the
    // ignore list in hsts_plugin_on_option_change().
    update_option( 'hsts_backend_endpoint', array( 'port' => $port, 'https' => $https, 'addr' => $addr ) );
}

/**
 * Read the learned backend endpoint. Returns only a validated port and scheme
 * (and addr for display); the connection host is never taken from here.
 *
 * @return array{port:int,https:bool,addr:string}
 */
function hsts_plugin_get_backend_endpoint(): array {
    $ep   = get_option( 'hsts_backend_endpoint' );
    $addr = is_array( $ep ) && isset( $ep['addr'] ) ? (string) $ep['addr'] : '';
    $port = is_array( $ep ) && isset( $ep['port'] ) ? (int) $ep['port'] : 0;
    if ( $port < 1 || $port > 65535 ) {
        $port = 0;
    }
    // Defensively drop a port that was learned before the loopback gate existed
    // (an older build could store the public edge port with a public addr). Any
    // stored entry with a non-loopback addr is treated as unknown so the probe
    // falls back to the static candidate list instead of dialling a wrong port.
    if ( '' !== $addr && ! hsts_plugin_is_loopback_target( $addr ) ) {
        $port = 0;
    }
    return array(
        'port'  => $port,
        'https' => is_array( $ep ) && ! empty( $ep['https'] ),
        'addr'  => $addr,
    );
}

/* -------------------------------------------------------------------------
 * Header set to write / suppress
 * ---------------------------------------------------------------------- */

/**
 * The subset of managed headers the writer places in .htaccess (and the same
 * subset the probe may suppress): every enabled managed header except
 * Content-Security-Policy and its report-only channel, because
 *
 *   - a static .htaccess CSP would also apply to wp-admin/wp-login, which the
 *     plugin deliberately keeps CSP out of (a restrictive CSP breaks the block
 *     editor), and .htaccess cannot cleanly scope to the front-end only;
 *   - a CSP carrying a per-request nonce cannot be frozen into a static file.
 *
 * CSP therefore stays PHP-only on the front-end. All other headers are
 * static-safe and benefit from server-level coverage on cached and static
 * responses.
 *
 * @return array<string,string> name => value (already honouring disable flags).
 */
function hsts_plugin_htaccess_managed_headers(): array {
    $exclude = array( 'Content-Security-Policy', 'Content-Security-Policy-Report-Only' );

    $out = array();
    foreach ( hsts_plugin_get_managed_headers() as $name => $value ) {
        if ( in_array( $name, $exclude, true ) ) {
            continue;
        }
        if ( '' === (string) $value ) {
            continue;
        }
        $out[ $name ] = (string) $value;
    }
    return $out;
}

/**
 * Escape a header value for an Apache `Header set X "..."` directive: collapse
 * any CR/LF, then backslash-escape backslashes and double quotes.
 */
function hsts_plugin_htaccess_escape_value( string $value ): string {
    $value = preg_replace( '/[\r\n]+/', ' ', $value );
    $value = str_replace( '\\', '\\\\', $value );
    $value = str_replace( '"', '\\"', $value );
    return trim( $value );
}

/**
 * Build the full marker-wrapped block. `Header set` (never `add`, never
 * `always`): `always` writes to err_headers_out and would duplicate on normal
 * 200s. On LiteSpeed `set` appends (the case the probe/suppression targets);
 * on Apache it replaces.
 */
function hsts_plugin_build_htaccess_block( array $headers ): string {
    $lines   = array();
    $lines[] = '# BEGIN ' . HSTS_HTACCESS_MARKER;
    $lines[] = '# Managed automatically by ' . HSTS_HTACCESS_MARKER . ' v' . HSTS_PLUGIN_VERSION . '.';
    $lines[] = '# Do not edit between these markers; changes are overwritten. Adjust values from';
    $lines[] = '# the plugin settings. The version above is a comment only - the BEGIN/END lines';
    $lines[] = '# never change, so upgrades replace this block in place instead of stacking.';
    $lines[] = '<IfModule mod_headers.c>';
    foreach ( $headers as $name => $value ) {
        $lines[] = 'Header set ' . $name . ' "' . hsts_plugin_htaccess_escape_value( (string) $value ) . '"';
    }
    $lines[] = '</IfModule>';
    $lines[] = '# END ' . HSTS_HTACCESS_MARKER;

    return implode( "\n", $lines ) . "\n";
}

/**
 * Pure: strip any existing managed block (any version, via the tested
 * version-agnostic stripper) and prepend the fresh block. Prepending keeps our
 * Header directives independent of, and ahead of, the WordPress rewrite block.
 */
function hsts_plugin_set_managed_block( string $contents, string $block ): string {
    $stripped = hsts_plugin_strip_htaccess_block( $contents );
    $stripped = ltrim( $stripped, "\r\n" );
    $block    = rtrim( $block, "\r\n" );

    if ( '' === trim( $stripped ) ) {
        return $block . "\n";
    }
    return $block . "\n\n" . $stripped;
}

/* -------------------------------------------------------------------------
 * Writer (with backup + verify + rollback)
 * ---------------------------------------------------------------------- */

/**
 * Write / refresh the managed block in the root .htaccess.
 *
 * Safe by construction (the block is wrapped in `<IfModule mod_headers.c>`, so
 * it cannot 500 a server that lacks mod_headers), plus a belt-and-suspenders
 * net: back up the prior contents, write, HTTP-verify over loopback, and roll
 * back if the site starts returning >= 500. If loopback cannot be reached we
 * keep the write (can't prove breakage; the block is guarded) and flag it.
 *
 * @return array{ok:bool,reason:string,code?:int}
 */
function hsts_plugin_write_htaccess(): array {
    if ( ! hsts_plugin_server_supports_htaccess() ) {
        // Server does not read .htaccess: no coverage to advertise. If a stray
        // block was written earlier (e.g. by a php-cli cron before this build
        // learned to skip that context), remove it so the file matches reality.
        $fs = hsts_plugin_get_filesystem();
        if ( null !== $fs ) {
            $file = get_home_path() . '.htaccess';
            if ( $fs->exists( $file ) && $fs->is_readable( $file ) && $fs->is_writable( $file ) ) {
                $existing = (string) $fs->get_contents( $file );
                $cleaned  = hsts_plugin_strip_htaccess_block( $existing );
                if ( $cleaned !== $existing ) {
                    $fs->put_contents( $file, $cleaned, defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : false );
                }
            }
        }
        hsts_plugin_set_written_headers( null );
        return array( 'ok' => false, 'reason' => 'unsupported_server' );
    }

    $fs = hsts_plugin_get_filesystem();
    if ( null === $fs ) {
        // Cannot confirm the block: fail toward emit (PHP keeps sending).
        hsts_plugin_set_written_headers( null );
        return array( 'ok' => false, 'reason' => 'no_filesystem' );
    }

    $file  = get_home_path() . '.htaccess';
    $chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : false;

    $existing = '';
    if ( $fs->exists( $file ) ) {
        if ( ! $fs->is_readable( $file ) || ! $fs->is_writable( $file ) ) {
            hsts_plugin_set_written_headers( null );
            return array( 'ok' => false, 'reason' => 'not_writable' );
        }
        $existing = (string) $fs->get_contents( $file );
    } elseif ( ! $fs->is_writable( get_home_path() ) ) {
        hsts_plugin_set_written_headers( null );
        return array( 'ok' => false, 'reason' => 'dir_not_writable' );
    }

    $headers = hsts_plugin_htaccess_managed_headers();

    // Everything disabled: make sure our block is gone rather than write empty.
    if ( empty( $headers ) ) {
        $cleaned = hsts_plugin_strip_htaccess_block( $existing );
        if ( $cleaned !== $existing ) {
            $fs->put_contents( $file, $cleaned, $chmod );
        }
        hsts_plugin_set_written_headers( null );
        return array( 'ok' => true, 'reason' => 'no_headers' );
    }

    $block   = hsts_plugin_build_htaccess_block( $headers );
    $updated = hsts_plugin_set_managed_block( $existing, $block );

    if ( $updated === $existing ) {
        // Already in the file exactly as we would write it.
        hsts_plugin_set_written_headers( array_keys( $headers ) );
        return array( 'ok' => true, 'reason' => 'unchanged' );
    }

    if ( ! $fs->put_contents( $file, $updated, $chmod ) ) {
        hsts_plugin_set_written_headers( null );
        return array( 'ok' => false, 'reason' => 'write_failed' );
    }

    $verify = hsts_plugin_verify_site();
    if ( $verify['verified'] && $verify['broken'] ) {
        // Roll back to exactly what was there before (or remove our block if
        // there was no file to begin with).
        if ( '' === $existing ) {
            $fs->put_contents( $file, hsts_plugin_strip_htaccess_block( $updated ), $chmod );
        } else {
            $fs->put_contents( $file, $existing, $chmod );
        }
        update_option( 'hsts_htaccess_write_failed', (int) $verify['code'] );
        hsts_plugin_set_written_headers( null ); // block no longer present
        return array( 'ok' => false, 'reason' => 'verify_failed_rolled_back', 'code' => (int) $verify['code'] );
    }

    delete_option( 'hsts_htaccess_write_failed' );
    hsts_plugin_set_written_headers( array_keys( $headers ) );

    return array(
        'ok'     => true,
        'reason' => $verify['verified'] ? 'written' : 'written_unverified',
        'code'   => (int) $verify['code'],
    );
}

/**
 * Loopback GET of the home URL to confirm the site still serves (< 500).
 *
 * @return array{verified:bool,broken:bool,code:int} verified=false means we
 *         could not reach loopback at all (do not treat as broken).
 */
function hsts_plugin_verify_site(): array {
    $resp = wp_remote_get(
        add_query_arg( 'hsts_verify', (string) time(), home_url( '/' ) ),
        array(
            'timeout'     => (int) apply_filters( 'hsts_plugin_probe_timeout', 5 ),
            'redirection' => 0,
            'sslverify'   => false,
            'headers'     => array( 'Cache-Control' => 'no-cache' ),
        )
    );

    if ( is_wp_error( $resp ) ) {
        return array( 'verified' => false, 'broken' => false, 'code' => 0 );
    }

    $code = (int) wp_remote_retrieve_response_code( $resp );
    return array( 'verified' => true, 'broken' => ( $code >= 500 ), 'code' => $code );
}

/**
 * Remove the managed block from .htaccess (deactivation / all-disabled).
 */
function hsts_plugin_remove_htaccess_block(): bool {
    return hsts_plugin_cleanup_htaccess();
}

/* -------------------------------------------------------------------------
 * Block status (for the diagnostic panel)
 * ---------------------------------------------------------------------- */

/**
 * Inspect the current .htaccess for our block.
 *
 * @return array{present:bool,headers:string[],raw:string}
 */
function hsts_plugin_htaccess_block_status(): array {
    $out = array( 'present' => false, 'headers' => array(), 'raw' => '' );

    $fs = hsts_plugin_get_filesystem();
    if ( null === $fs ) {
        return $out;
    }
    $file = get_home_path() . '.htaccess';
    if ( ! $fs->exists( $file ) || ! $fs->is_readable( $file ) ) {
        return $out;
    }

    $contents = (string) $fs->get_contents( $file );
    $marker   = preg_quote( HSTS_HTACCESS_MARKER, '/' );
    if ( preg_match( '/#\s*BEGIN\s+' . $marker . '.*?#\s*END\s+' . $marker . '[^\r\n]*/is', $contents, $m ) ) {
        $out['present'] = true;
        $out['raw']     = $m[0];
        if ( preg_match_all( '/Header\s+set\s+([^\s"]+)/i', $m[0], $hm ) ) {
            $out['headers'] = $hm[1];
        }
    }

    return $out;
}

/**
 * Whether the server layer will emit this header on its own for the current
 * response class: the server must actually read .htaccess (Apache/LiteSpeed)
 * and our managed block must currently contain the header. Used to decide, for
 * a user-chosen "server only" header, whether it is safe to drop the PHP copy;
 * if either is false the caller keeps emitting from PHP (fail toward emit).
 *
 * Reads the header list the writer records at write time
 * (hsts_htaccess_written_headers, autoloaded), so there is no filesystem read on
 * front-end requests. The option is (re)written on every successful write and
 * cleared whenever the block is removed, a write fails or rolls back, or the
 * filesystem is unavailable - so an absent or stale-empty value degrades to
 * "emit from PHP", never to silent loss.
 */
function hsts_plugin_server_covers_header( string $name ): bool {
    if ( ! hsts_plugin_server_supports_htaccess() ) {
        return false;
    }
    $written = get_option( 'hsts_htaccess_written_headers' );
    return is_array( $written ) && in_array( $name, $written, true );
}

/**
 * Record / clear the list of header names currently in the managed .htaccess
 * block. Called by the writer at each terminal outcome so the fast coverage
 * check (hsts_plugin_server_covers_header) never has to read the file.
 *
 * @param string[]|null $names Header names now in the block, or null to clear.
 */
function hsts_plugin_set_written_headers( $names ): void {
    if ( empty( $names ) || ! is_array( $names ) ) {
        delete_option( 'hsts_htaccess_written_headers' );
        return;
    }
    update_option( 'hsts_htaccess_written_headers', array_values( $names ), true );
}

/* -------------------------------------------------------------------------
 * Self-check no-emit branch + suppression state
 * ---------------------------------------------------------------------- */

/**
 * Per-site secret used by the loopback self-check. Generated once with a CSPRNG
 * and stored. Never leaves the server except as a request header the probe
 * itself sends to itself.
 */
function hsts_plugin_get_probe_token(): string {
    $token = get_option( 'hsts_probe_token' );
    if ( is_string( $token ) && '' !== $token ) {
        return $token;
    }

    try {
        $token = bin2hex( random_bytes( 32 ) );
    } catch ( \Exception $e ) {
        $token = wp_generate_password( 64, false, false );
    }
    update_option( 'hsts_probe_token', $token, false );

    return $token;
}

/**
 * Is this request the probe's loopback self-check? All of the following are
 * required:
 *   - origin is loopback, read from REMOTE_ADDR alone and never from
 *     X-Forwarded-For / X-Real-IP (those are attacker-controlled and would open
 *     the no-emit branch to the world);
 *   - a custom request header carries the per-site secret (kept out of query
 *     strings and access logs);
 *   - constant-time comparison.
 * If REMOTE_ADDR is a non-loopback proxy IP (some real_ip configs) this returns
 * false and PHP emits normally - fail toward emit.
 */
function hsts_plugin_is_selfcheck_request(): bool {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    if ( '127.0.0.1' !== $ip && '::1' !== $ip ) {
        return false;
    }
    $sent = isset( $_SERVER['HTTP_X_HSTS_SELFCHECK'] ) ? (string) $_SERVER['HTTP_X_HSTS_SELFCHECK'] : '';
    if ( '' === $sent ) {
        return false;
    }
    return hash_equals( hsts_plugin_get_probe_token(), $sent );
}

/**
 * The single suppression decision. Suppress PHP's own copy of a header only
 * when a stored confirmation is present, positive, and within the TTL.
 * Everything else returns false and PHP emits (fail toward emit).
 */
function hsts_plugin_htaccess_confirmed( string $name ): bool {
    $state = get_option( 'hsts_htaccess_confirmed' );
    if ( ! is_array( $state ) || empty( $state[ $name ] ) ) {
        return false;
    }
    $ts = (int) $state[ $name ];
    if ( $ts <= 0 ) {
        return false;
    }
    if ( ( time() - $ts ) > HSTS_CONFIRM_TTL ) {
        return false;
    }
    return true;
}

/**
 * Pure two-read decision: suppress iff the server layer covers this response
 * class on its own (n_off >= 1) and the server appends a real duplicate
 * (n_on >= 2). See the table in docs/RELEASE-5.3.5-PLAN.md.
 */
function hsts_plugin_should_confirm( int $n_on, int $n_off ): bool {
    return ( $n_off >= 1 ) && ( $n_on >= 2 );
}

/* -------------------------------------------------------------------------
 * The probe (two reads over loopback)
 * ---------------------------------------------------------------------- */

/**
 * Count occurrences of a header in a wp_remote response, tolerant of both
 * appended duplicates (array) and comma-folded duplicates ("v, v").
 */
function hsts_plugin_count_header( $resp, string $name ): int {
    $val = wp_remote_retrieve_header( $resp, strtolower( $name ) );

    if ( is_array( $val ) ) {
        return count( array_filter( array_map( 'trim', $val ), 'strlen' ) );
    }
    $val = (string) $val;
    if ( '' === $val ) {
        return 0;
    }
    return count( array_filter( array_map( 'trim', explode( ',', $val ) ), 'strlen' ) );
}

/**
 * One probe read against a loopback target.
 *
 * @param string $url           Must resolve to a loopback host (re-checked here).
 * @param string $host          Real vhost for the Host header (from home_url()).
 * @param array  $extra_headers Extra request headers (e.g. the self-check token).
 */
function hsts_plugin_probe_fetch( string $url, string $host, array $extra_headers ) {
    // SSRF guard. The URL host is built from a hardcoded loopback allowlist and
    // never from a stored value, but we re-check right before dialling so no
    // future caller or filter can point the probe off-box.
    $target = (string) wp_parse_url( $url, PHP_URL_HOST );
    if ( ! hsts_plugin_is_loopback_target( $target ) ) {
        return new WP_Error( 'hsts_probe_target', 'probe target is not a loopback address' );
    }

    $headers = array( 'Cache-Control' => 'no-cache' );
    if ( '' !== $host ) {
        // The real vhost, from home_url() (a stored option) and not from the
        // incoming request's Host header, so the backend selects the right
        // vhost when we connect straight to its loopback port.
        $headers['Host'] = $host;
    }

    return wp_remote_get(
        $url,
        array(
            'timeout'     => (int) apply_filters( 'hsts_plugin_probe_timeout', 5 ),
            'redirection' => 0,
            'sslverify'   => false,
            'headers'     => array_merge( $headers, $extra_headers ),
        )
    );
}

/**
 * Ordered list of loopback probe targets: base URL + real Host header.
 *
 * Every destination is a hardcoded loopback literal (127.0.0.1 / ::1). Only the
 * port and scheme vary - the learned backend port first (most likely correct on
 * a reverse-proxied stack), then a short list of common Apache/front ports.
 * Connecting to the backend's loopback port directly means REMOTE_ADDR is a
 * genuine 127.0.0.1/::1 and the self-check branch can arm. The public host is
 * deliberately not a candidate (that is the request path whose proxy rewrote
 * REMOTE_ADDR in the first place, and it would violate the loopback-only rule).
 *
 * @return array<int,array{url:string,host:string}>
 */
function hsts_plugin_probe_targets(): array {
    $host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
    if ( '' === $host ) {
        return array();
    }

    $out  = array();
    $seen = array();
    $add  = function ( $ip, $port, $https ) use ( &$out, &$seen, $host ) {
        $port = (int) $port;
        if ( $port < 1 || $port > 65535 || ! hsts_plugin_is_loopback_target( $ip ) ) {
            return;
        }
        $bracket = ( false !== strpos( $ip, ':' ) ) ? '[' . $ip . ']' : $ip;
        $url     = ( $https ? 'https' : 'http' ) . '://' . $bracket . ':' . $port . '/';
        if ( isset( $seen[ $url ] ) ) {
            return;
        }
        $seen[ $url ] = true;
        $out[]        = array( 'url' => $url, 'host' => $host );
    };

    $ep = hsts_plugin_get_backend_endpoint();
    if ( $ep['port'] > 0 ) {
        $add( '127.0.0.1', $ep['port'], $ep['https'] );
        $add( '::1', $ep['port'], $ep['https'] );
    }
    // Common Plesk/cPanel backend ports + the standard front ports on loopback.
    $add( '127.0.0.1', 7081, true );
    $add( '127.0.0.1', 443, true );
    $add( '127.0.0.1', 7080, false );
    $add( '127.0.0.1', 80, false );

    return array_slice( $out, 0, (int) apply_filters( 'hsts_plugin_probe_max_targets', 6 ) );
}

/**
 * Run the two-read probe against the site's own dynamic response class and
 * (optionally) persist the resulting confirmations.
 *
 * Two reads of the same pretty URL, both cache-busted so both hit freshly
 * generated PHP rather than a cached copy: n_on normal, n_off with the
 * self-check header so PHP self-suppresses and only the server layer is left.
 * n_off is trusted only when the sentinel response header proves the branch
 * actually ran on our vhost. Not trustworthy -> confirm nothing (fail toward
 * emit).
 *
 * @param bool $persist Write confirmations to the option (cron true; button true).
 * @return array Structured result for display / storage.
 */
function hsts_plugin_probe( bool $persist ): array {
    $status = hsts_plugin_htaccess_block_status();

    $result = array(
        'ok'                => false,
        'trustworthy'       => false,
        'server'            => hsts_plugin_detect_server(),
        'app_server'        => hsts_plugin_detect_server(),
        'supports_htaccess' => hsts_plugin_server_supports_htaccess(),
        'suppression'       => hsts_plugin_suppression_enabled(),
        'block_present'     => $status['present'],
        'block_headers'     => $status['headers'],
        'status_on'         => 0,
        'status_off'        => 0,
        'sentinel'          => false,
        'probed_url'        => '',
        'server_header'     => '',
        'backend'           => '',
        'endpoint'          => hsts_plugin_get_backend_endpoint(),
        'targets_total'     => 0,
        'targets_tried'     => 0,
        'attempts'          => array(),
        'per_header'        => array(),
        'note'              => '',
    );

    $token   = hsts_plugin_get_probe_token();
    $targets = hsts_plugin_probe_targets();

    $result['targets_total'] = count( $targets );

    if ( empty( $targets ) ) {
        $result['note'] = 'no loopback probe target could be built';
        if ( $persist ) {
            update_option( 'hsts_htaccess_confirmed', array() ); // fail toward emit
        }
        return $result;
    }

    // Stage 1 - find a target where the no-emit self-check actually arms. That
    // requires reaching the backend directly over loopback so REMOTE_ADDR is a
    // genuine 127.0.0.1/::1; a reverse proxy would rewrite it and the branch
    // would never fire. Trust a target only on 200 + our sentinel: the sentinel
    // is emitted solely by our plugin holding this site's secret, so it also
    // proves we hit the right vhost rather than a default one.
    $chosen = null;
    $off    = null;
    foreach ( $targets as $t ) {
        $result['targets_tried']++;
        $cb      = substr( hash( 'sha256', $token . microtime( true ) . $t['url'] . 'off' ), 0, 12 );
        $url_off = add_query_arg( 'hsts_cb', $cb, $t['url'] );
        $resp    = hsts_plugin_probe_fetch( $url_off, $t['host'], array( 'X-Hsts-Selfcheck' => $token ) );

        // Keep the first attempt on record for the diagnostic even if it fails.
        if ( '' === $result['probed_url'] ) {
            $result['probed_url'] = $url_off;
        }

        // Per-candidate diagnostic so a screenshot distinguishes "could not
        // connect" (error) from "connected but no sentinel" (proxy/wrong vhost).
        $attempt = array(
            'url'      => $t['url'],
            'code'     => 0,
            'error'    => '',
            'sentinel' => false,
        );
        if ( is_wp_error( $resp ) ) {
            $attempt['error']    = $resp->get_error_message();
            $result['attempts'][] = $attempt;
            continue;
        }
        $code               = (int) wp_remote_retrieve_response_code( $resp );
        $sentinel           = '1' === trim( (string) wp_remote_retrieve_header( $resp, 'x-hsts-selfcheck' ) );
        $attempt['code']     = $code;
        $attempt['sentinel'] = $sentinel;
        $result['attempts'][] = $attempt;
        if ( 200 === $code && $sentinel ) {
            $chosen               = $t;
            $off                  = $resp;
            $result['probed_url'] = $url_off;
            $result['backend']    = $t['url'];
            $result['sentinel']   = true;
            $result['status_off'] = $code;
            break;
        }
    }

    if ( null === $chosen ) {
        $result['note'] = 'the self-check did not arm on any loopback target: the probe could not reach the backend directly (REMOTE_ADDR was not loopback). PHP keeps emitting on this stack - the safe default. If a server-level cache serves HTML without PHP, add the equivalent rules to your front-proxy config.';
        if ( $persist ) {
            update_option( 'hsts_htaccess_confirmed', array() ); // fail toward emit
        }
        return $result;
    }

    // Stage 2 - the on-read against the same target so the counts are directly
    // comparable.
    $cb2    = substr( hash( 'sha256', $token . microtime( true ) . $chosen['url'] . 'on' ), 0, 12 );
    $url_on = add_query_arg( 'hsts_cb', $cb2, $chosen['url'] );
    $on     = hsts_plugin_probe_fetch( $url_on, $chosen['host'], array() );

    if ( is_wp_error( $on ) ) {
        $result['note'] = 'on-read failed: ' . $on->get_error_message();
        if ( $persist ) {
            update_option( 'hsts_htaccess_confirmed', array() ); // fail toward emit
        }
        return $result;
    }

    $result['status_on']     = (int) wp_remote_retrieve_response_code( $on );
    $result['server_header'] = trim( (string) wp_remote_retrieve_header( $on, 'server' ) );

    $trustworthy = ( 200 === $result['status_on'] )
        && ( 200 === $result['status_off'] )
        && $result['sentinel'];
    $result['trustworthy'] = $trustworthy;

    if ( ! $trustworthy ) {
        $result['note'] = 'reads returned non-200; not trusted';
    }

    $now       = time();
    $confirmed = array();
    foreach ( hsts_plugin_htaccess_managed_headers() as $name => $value ) {
        $n_on   = hsts_plugin_count_header( $on, $name );
        $n_off  = hsts_plugin_count_header( $off, $name );
        $decide = $trustworthy && hsts_plugin_should_confirm( $n_on, $n_off );

        $result['per_header'][ $name ] = array(
            'n_on'    => $n_on,
            'n_off'   => $n_off,
            'confirm' => $decide,
        );
        if ( $decide ) {
            $confirmed[ $name ] = $now;
        }
    }

    if ( $persist ) {
        update_option( 'hsts_htaccess_confirmed', $trustworthy ? $confirmed : array() );
    }

    $result['ok'] = true;
    return $result;
}

/* -------------------------------------------------------------------------
 * Cron scheduling (only when passive suppression is enabled)
 * ---------------------------------------------------------------------- */

/**
 * Background probe. Anti-stack lock so overlapping cron runs never launch
 * multiple loopback requests into a busy pool. No-op unless suppression is on.
 */
function hsts_plugin_probe_cron(): void {
    if ( ! hsts_plugin_suppression_enabled() ) {
        return;
    }
    if ( get_transient( 'hsts_probe_lock' ) ) {
        return;
    }
    set_transient( 'hsts_probe_lock', 1, (int) apply_filters( 'hsts_plugin_probe_lock_ttl', 60 ) );
    hsts_plugin_probe( true );
    delete_transient( 'hsts_probe_lock' );
}
add_action( 'hsts_probe_event', 'hsts_plugin_probe_cron' );

/**
 * Keep the cron event in sync with the feature flag.
 */
function hsts_plugin_schedule_probe(): void {
    $scheduled = wp_next_scheduled( 'hsts_probe_event' );

    if ( ! hsts_plugin_suppression_enabled() ) {
        if ( $scheduled ) {
            wp_unschedule_event( $scheduled, 'hsts_probe_event' );
        }
        return;
    }
    if ( ! $scheduled ) {
        wp_schedule_event( time() + 300, 'hourly', 'hsts_probe_event' );
    }
}
add_action( 'init', 'hsts_plugin_schedule_probe' );

/* -------------------------------------------------------------------------
 * Re-sync triggers
 * ---------------------------------------------------------------------- */

/**
 * When any plugin option changes, refresh the .htaccess block once on shutdown.
 * Skips our own bookkeeping options to avoid pointless rewrites / recursion.
 */
function hsts_plugin_on_option_change( $option ): void {
    if ( ! is_string( $option ) || 0 !== strpos( $option, 'hsts_' ) ) {
        return;
    }
    $ignore = array(
        'hsts_htaccess_confirmed',
        'hsts_probe_token',
        'hsts_backend_endpoint',
        'hsts_htaccess_write_failed',
        'hsts_htaccess_written_headers',
        'hsts_plugin_db_version',
        'hsts_htaccess_cleanup_pending',
        'hsts_show_migration_notice_v2',
        'hsts_show_migration_notice_v3',
    );
    if ( in_array( $option, $ignore, true ) ) {
        return;
    }

    static $scheduled = false;
    if ( $scheduled ) {
        return;
    }
    $scheduled = true;
    add_action( 'shutdown', 'hsts_plugin_write_htaccess' );
}
add_action( 'added_option', 'hsts_plugin_on_option_change' );
add_action( 'updated_option', 'hsts_plugin_on_option_change' );

/* -------------------------------------------------------------------------
 * nginx configuration snippet (manual step)
 * ---------------------------------------------------------------------- */

/**
 * The add_header lines an nginx user must hand to their hoster (nginx does not
 * read .htaccess; no plugin can change that). Same static-safe subset the
 * writer would emit; CSP stays PHP-emitted.
 */
function hsts_plugin_nginx_snippet(): string {
    $lines = array();
    foreach ( hsts_plugin_htaccess_managed_headers() as $name => $value ) {
        $v       = str_replace( '"', '\\"', preg_replace( '/[\r\n]+/', ' ', (string) $value ) );
        $lines[] = 'add_header ' . $name . ' "' . trim( $v ) . '" always;';
    }
    return implode( "\n", $lines );
}

/* -------------------------------------------------------------------------
 * Diagnostic panel + "Run check now" button
 * ---------------------------------------------------------------------- */

/**
 * Diagnostic block rendered under the settings form: which server was detected,
 * whether the .htaccess block is in place, and a "Run check now" button that
 * runs the two-read probe on demand and prints n_on / n_off / decision per
 * header. manage_options + nonce; kept in production as the support diagnostic.
 */
function hsts_plugin_render_diagnostic_panel(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $result    = null;
    $throttled = false;
    if ( isset( $_POST['hsts_run_check'] ) ) {
        check_admin_referer( 'hsts_run_check' );
        // Rate limit: the button fires two loopback HTTP requests at the site
        // itself, so a repeated click could pile requests onto a small FPM
        // pool. One run per 30s is plenty for a manual diagnostic.
        if ( get_transient( 'hsts_run_check_throttle' ) ) {
            $throttled = true;
        } else {
            set_transient( 'hsts_run_check_throttle', 1, 30 );
            $result = hsts_plugin_probe( true );
        }
    }

    $server    = hsts_plugin_detect_server();
    $supports  = hsts_plugin_server_supports_htaccess();
    $status    = hsts_plugin_htaccess_block_status();
    $suppress  = hsts_plugin_suppression_enabled();
    $failed    = (int) get_option( 'hsts_htaccess_write_failed' );

    $server_name = hsts_plugin_server_label( $server );

    echo '<div class="hsts-pro-panel" style="margin-top:24px;padding:20px;border:1px solid #e8e0f3;border-radius:14px;background:#fff;">';
    echo '<h3 style="margin-top:0;">' . esc_html__( 'Delivery diagnostics', 'headers-security-advanced-hsts-wp' ) . '</h3>';

    // Server + writer status. This is the APPLICATION server as PHP sees it
    // (SERVER_SOFTWARE) - the one that reads .htaccess. Behind a reverse proxy
    // the edge server the client sees can differ; that is shown after a check.
    echo '<p><strong>' . esc_html__( 'Application server (as PHP sees it):', 'headers-security-advanced-hsts-wp' ) . '</strong> ' . esc_html( $server_name ) . '</p>';

    if ( 'nginx' === $server ) {
        // Neutral, not a warning: on nginx PHP covers dynamic pages, so the
        // snippet below is only needed for server-level caches that serve HTML
        // without invoking PHP.
        echo '<p>' . esc_html__( 'On nginx the security headers are served by PHP, so your pages are covered. nginx does not read .htaccess, so the plugin does not write one here (no plugin can change that). Only if you run a server-level cache that serves HTML without invoking PHP do you need the equivalent rules added to the nginx config by your host:', 'headers-security-advanced-hsts-wp' ) . '</p>';
        echo '<pre style="background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:12px;overflow:auto;">' . esc_html( hsts_plugin_nginx_snippet() ) . '</pre>';
    } elseif ( 'iis' === $server ) {
        echo '<p>' . esc_html__( 'On IIS the headers are served by PHP. IIS does not read .htaccess, so no file is written; add the equivalent headers in web.config if you serve cached HTML without PHP.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    } elseif ( $supports ) {
        if ( $status['present'] ) {
            echo '<p style="color:#1a7f37;"><strong>' . esc_html__( '.htaccess block: present', 'headers-security-advanced-hsts-wp' ) . '</strong> &mdash; ' . esc_html( implode( ', ', $status['headers'] ) ) . '</p>';
        } else {
            echo '<p style="color:#8a6d00;"><strong>' . esc_html__( '.htaccess block: not found', 'headers-security-advanced-hsts-wp' ) . '</strong> &mdash; ' . esc_html__( 'save the settings, or press the button below, to (re)write it.', 'headers-security-advanced-hsts-wp' ) . '</p>';
        }
        if ( $failed >= 500 ) {
            /* translators: %d: HTTP status code. */
            echo '<p style="color:#b32d2e;">' . esc_html( sprintf( __( 'A previous write was rolled back automatically because the site returned HTTP %d. The block is not applied. Check whether your server supports mod_headers.', 'headers-security-advanced-hsts-wp' ), $failed ) ) . '</p>';
        }
    } else {
        echo '<p>' . esc_html__( 'The headers are served by PHP on this server.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    }

    echo '<p style="color:#575ba3;">' . esc_html(
        $suppress
            ? __( 'PHP self-suppression: ON (a header is dropped from PHP only when the probe has proven the server layer already emits it).', 'headers-security-advanced-hsts-wp' )
            : __( 'PHP self-suppression: OFF (PHP always emits; the .htaccess block covers cached and static responses). The button below still measures what the probe would decide.', 'headers-security-advanced-hsts-wp' )
    ) . '</p>';

    // Button.
    echo '<form method="post" action="">';
    wp_nonce_field( 'hsts_run_check' );
    echo '<button type="submit" name="hsts_run_check" value="1" class="button button-secondary">' . esc_html__( 'Run check now', 'headers-security-advanced-hsts-wp' ) . '</button>';
    echo '</form>';

    if ( $throttled ) {
        echo '<p style="color:#8a6d00;">' . esc_html__( 'Please wait a few seconds before running the check again.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    }

    if ( null !== $result ) {
        hsts_plugin_render_probe_result( $result );
    }

    echo '</div>';
}
add_action( 'hsts_settings_after_form', 'hsts_plugin_render_diagnostic_panel' );

/**
 * Render one probe run's outcome as a table.
 *
 * @param array $result From hsts_plugin_probe().
 */
function hsts_plugin_render_probe_result( array $result ): void {
    echo '<div style="margin-top:16px;">';

    // Application vs edge server: distinct facts. The application server (what
    // PHP sees, reads .htaccess) and the edge Server header (what the client
    // sees) legitimately differ behind a reverse proxy - show both.
    $app_server = isset( $result['app_server'] ) ? hsts_plugin_server_label( (string) $result['app_server'] ) : '';
    $edge       = (string) $result['server_header'];
    echo '<p><strong>' . esc_html__( 'Application server (backend, as PHP sees it):', 'headers-security-advanced-hsts-wp' ) . '</strong> ' . esc_html( '' !== $app_server ? $app_server : __( 'unknown', 'headers-security-advanced-hsts-wp' ) ) . '</p>';
    echo '<p><strong>' . esc_html__( 'Edge server (Server header the client sees):', 'headers-security-advanced-hsts-wp' ) . '</strong> ' . esc_html( '' !== $edge ? $edge : __( '(none returned)', 'headers-security-advanced-hsts-wp' ) ) . '</p>';
    if ( '' !== $app_server && '' !== $edge && false === stripos( $edge, (string) $result['app_server'] ) ) {
        echo '<p style="color:#575ba3;">' . esc_html__( 'These differ because a reverse proxy fronts your application server (e.g. nginx in front of Apache). That is normal: the .htaccess block still applies because the backend serves the pages.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    }

    // Learned backend port: distinguishes "I never learned the port" (probe
    // starts from static fallbacks) from "I learned it but it does not answer".
    $ep = isset( $result['endpoint'] ) && is_array( $result['endpoint'] ) ? $result['endpoint'] : array( 'port' => 0, 'https' => false, 'addr' => '' );
    if ( (int) $ep['port'] > 0 ) {
        $learned = ( $ep['https'] ? 'https' : 'http' ) . '://127.0.0.1:' . (int) $ep['port'] . '/';
        echo '<p><strong>' . esc_html__( 'Learned backend port:', 'headers-security-advanced-hsts-wp' ) . '</strong> <code>' . esc_html( $learned ) . '</code>';
        if ( '' !== (string) $ep['addr'] ) {
            echo ' ' . esc_html( sprintf( __( '(observed on SERVER_ADDR %s)', 'headers-security-advanced-hsts-wp' ), (string) $ep['addr'] ) );
        }
        echo '</p>';
    } else {
        echo '<p style="color:#8a6d00;"><strong>' . esc_html__( 'Learned backend port:', 'headers-security-advanced-hsts-wp' ) . '</strong> ' . esc_html__( 'not yet learned - visit a front-end page while logged in (so the page cache is bypassed and PHP runs on the backend). The probe is using its static fallback ports for now.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    }

    // Which loopback candidate armed the self-check, and how many were tried:
    // a screenshot then shows whether the probe truly reached the backend or
    // stopped on a fallback.
    if ( '' !== (string) $result['backend'] ) {
        /* translators: 1: number of candidates tried, 2: total candidates. */
        echo '<p><strong>' . esc_html__( 'Backend reached:', 'headers-security-advanced-hsts-wp' ) . '</strong> <code>' . esc_html( (string) $result['backend'] ) . '</code> ' . esc_html( sprintf( __( '(candidate %1$d of %2$d tried)', 'headers-security-advanced-hsts-wp' ), (int) $result['targets_tried'], (int) $result['targets_total'] ) ) . '</p>';
    } elseif ( (int) $result['targets_total'] > 0 ) {
        /* translators: 1: number of loopback candidates tried. */
        echo '<p style="color:#8a6d00;"><strong>' . esc_html__( 'Backend reached:', 'headers-security-advanced-hsts-wp' ) . '</strong> ' . esc_html( sprintf( __( 'none - all %1$d loopback candidates tried, none armed the self-check', 'headers-security-advanced-hsts-wp' ), (int) $result['targets_total'] ) ) . '</p>';
    }
    echo '<p><strong>' . esc_html__( 'Probed URL:', 'headers-security-advanced-hsts-wp' ) . '</strong> <code>' . esc_html( (string) $result['probed_url'] ) . '</code></p>';

    // Per-candidate attempt log: one row per loopback target the probe dialled,
    // so a screenshot shows exactly where it stopped and why (connection error
    // vs. answered-but-no-sentinel).
    if ( ! empty( $result['attempts'] ) ) {
        echo '<table class="widefat striped" style="max-width:640px;"><thead><tr>';
        echo '<th>' . esc_html__( 'Loopback candidate', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '<th>' . esc_html__( 'HTTP', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '<th>' . esc_html__( 'Self-check', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '<th>' . esc_html__( 'Outcome', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ( $result['attempts'] as $a ) {
            $armed = ! empty( $a['sentinel'] ) && 200 === (int) $a['code'];
            if ( '' !== (string) $a['error'] ) {
                $outcome = sprintf( __( 'no connection (%s)', 'headers-security-advanced-hsts-wp' ), (string) $a['error'] );
            } elseif ( $armed ) {
                $outcome = __( 'armed - backend reached', 'headers-security-advanced-hsts-wp' );
            } else {
                $outcome = __( 'answered, no sentinel (proxy or wrong vhost)', 'headers-security-advanced-hsts-wp' );
            }
            echo '<tr>';
            echo '<td><code>' . esc_html( (string) $a['url'] ) . '</code></td>';
            echo '<td>' . ( (int) $a['code'] > 0 ? (int) $a['code'] : '&mdash;' ) . '</td>';
            echo '<td>' . esc_html( ! empty( $a['sentinel'] ) ? __( 'yes', 'headers-security-advanced-hsts-wp' ) : __( 'no', 'headers-security-advanced-hsts-wp' ) ) . '</td>';
            echo '<td>' . esc_html( $outcome ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    echo '<p><strong>' . esc_html__( 'Probe result', 'headers-security-advanced-hsts-wp' ) . '</strong> &mdash; ';
    /* translators: 1: on-read HTTP status, 2: off-read HTTP status. */
    echo esc_html( sprintf( __( 'reads returned %1$d / %2$d', 'headers-security-advanced-hsts-wp' ), (int) $result['status_on'], (int) $result['status_off'] ) );
    echo ', ' . esc_html( $result['sentinel'] ? __( 'self-check ran (sentinel present)', 'headers-security-advanced-hsts-wp' ) : __( 'self-check did NOT run (sentinel absent)', 'headers-security-advanced-hsts-wp' ) );
    echo ', ' . esc_html( $result['trustworthy'] ? __( 'result trusted', 'headers-security-advanced-hsts-wp' ) : __( 'result NOT trusted', 'headers-security-advanced-hsts-wp' ) );
    echo '</p>';

    if ( '' !== (string) $result['note'] ) {
        echo '<p style="color:#8a6d00;">' . esc_html( $result['note'] ) . '</p>';
    }

    // The probe could not arm on any loopback candidate (no self-check sentinel
    // came back - e.g. a reverse-proxy backend that selects the vhost by SNI and
    // answers 421 to the loopback dial, common on Plesk/cPanel with nginx in
    // front of Apache). Automatic de-duplication cannot run here, so point the
    // user at the manual fallback instead of leaving them with an inert panel.
    if ( empty( $result['trustworthy'] ) ) {
        echo '<p style="color:#8a6d00;"><strong>' . esc_html__( 'Automatic check could not arm on this server.', 'headers-security-advanced-hsts-wp' ) . '</strong> '
            . esc_html__( 'This is expected on some reverse-proxy stacks (for example nginx in front of Apache on Plesk or cPanel). Header delivery still works - PHP keeps sending every header. If you see a duplicated header, open "Advanced: per-header delivery" above and set it to "Server only" to keep the server copy and drop the plugin\'s own.', 'headers-security-advanced-hsts-wp' ) . '</p>';
    }

    if ( ! empty( $result['per_header'] ) ) {
        echo '<table class="widefat striped" style="max-width:640px;"><thead><tr>';
        echo '<th>' . esc_html__( 'Header', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '<th>n_on</th><th>n_off</th>';
        echo '<th>' . esc_html__( 'Would suppress PHP copy', 'headers-security-advanced-hsts-wp' ) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ( $result['per_header'] as $name => $row ) {
            echo '<tr>';
            echo '<td><code>' . esc_html( $name ) . '</code></td>';
            echo '<td>' . (int) $row['n_on'] . '</td>';
            echo '<td>' . (int) $row['n_off'] . '</td>';
            echo '<td>' . esc_html( $row['confirm'] ? __( 'yes', 'headers-security-advanced-hsts-wp' ) : __( 'no', 'headers-security-advanced-hsts-wp' ) ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p style="color:#575ba3;">' . esc_html__( 'Suppress when n_off >= 1 (server layer covers this response on its own) AND n_on >= 2 (a real duplicate to collapse). Everything else keeps PHP emitting.', 'headers-security-advanced-hsts-wp' ) . '</p>';

        // Duplicate the probe cannot resolve on its own: the header is doubled
        // (n_on >= 2) but automatic suppression will not arm (confirm == false,
        // because the server layer does not keep the header on its own once the
        // plugin drops its copy - the second copy comes from another source).
        // Auto-suppression stays primary; the per-header selector is the manual
        // fallback for these stacks. Only surface headers still on the default
        // 'on' mode, so we do not nag once the user has chosen server/off.
        $unresolved = array();
        if ( function_exists( 'hsts_plugin_header_mode_by_name' ) ) {
            foreach ( $result['per_header'] as $name => $row ) {
                if ( (int) $row['n_on'] >= 2 && empty( $row['confirm'] )
                    && 'on' === hsts_plugin_header_mode_by_name( $name ) ) {
                    $unresolved[] = $name;
                }
            }
        }
        if ( ! empty( $unresolved ) ) {
            $unresolved_html = implode( '</code>, <code>', array_map( 'esc_html', $unresolved ) );
            echo '<p style="color:#8a6d00;"><strong>' . esc_html__( 'Duplicate detected that cannot be resolved automatically:', 'headers-security-advanced-hsts-wp' ) . '</strong> <code>' . $unresolved_html . '</code>. '
                . esc_html__( 'Another source (your server config or another plugin) keeps sending these, so the plugin cannot collapse the duplicate on its own. Open "Advanced: per-header delivery" above and set each of them to "Server only" to drop the plugin copy and keep the other source.', 'headers-security-advanced-hsts-wp' ) . '</p>';
        }
    }

    echo '</div>';
}
