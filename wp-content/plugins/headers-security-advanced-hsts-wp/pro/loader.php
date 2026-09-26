<?php
/**

 *
 *
 * @package HeadersSecurityAdvancedHSTSWP
 * @since   5.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hsts_pro_is_licensed(): bool {
    return false;
}

function hsts_pro_init(): void {
    add_action( 'hsts_pro_before_settings', 'hsts_pro_render_tab_bar' );
    add_action( 'hsts_settings_after_form', 'hsts_pro_render_tab_panels' );
}
add_action( 'plugins_loaded', 'hsts_pro_init', 20 );

function hsts_pro_tab_btn( string $tab, string $label, bool $active = false, string $extra = '' ): void {
    $bc = $active ? '#5b06b0' : 'transparent';
    $fc = $active ? '#5b06b0' : '#575ba3';
    $fw = $active ? '600' : '500';
    printf(
        '<button type="button" class="hsts-pro-tab" data-tab="%s" style="padding:12px 22px;background:none;border:none;border-bottom:3px solid %s;cursor:pointer;font-size:14px;font-weight:%s;color:%s;transition:all .15s;margin-bottom:-2px;">%s%s</button>',
        esc_attr( $tab ), esc_attr( $bc ), esc_attr( $fw ), esc_attr( $fc ), esc_html( $label ), $extra
    );
}

function hsts_pro_locked_preview( string $title, string $description ): void {
    ?>
    <div style="position:relative;min-height:320px;">
        <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,0) 0%,rgba(255,255,255,.95) 60%,#fff 100%);z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:40px;">
            <div style="text-align:center;max-width:480px;">
                <div style="display:inline-block;padding:6px 20px;background:linear-gradient(135deg,#0f135e,#5b06b0);color:#fff;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:1px;margin-bottom:16px;">SHIELD</div>
                <h4 style="color:#0f135e;font-size:18px;font-weight:700;margin:0 0 8px;"><?php echo esc_html( $title ); ?></h4>
                <p style="color:#575ba3;font-size:13px;line-height:1.6;margin:0 0 20px;"><?php echo esc_html( $description ); ?></p>
                <a href="https://open-headers.lemonsqueezy.com/checkout/buy/02981b50-7291-4551-bb9c-79f0f2c3e568" target="_blank"
                    class="HeaderSecurityAdvancedHSTSWPROSHUEbkg" style="font-size:14px;padding:12px 32px;">
                    <?php esc_html_e( 'Unlock Shield', 'headers-security-advanced-hsts-wp' ); ?> &rarr;
                </a>
                <p style="font-size:11px;color:#717171;margin-top:10px;"><?php esc_html_e( 'Download Shield, replace the plugin ZIP, and enter your license key.', 'headers-security-advanced-hsts-wp' ); ?></p>
            </div>
        </div>
        <div style="filter:blur(3px);opacity:.4;pointer-events:none;">
            <div style="display:grid;grid-template-columns:200px 1fr;gap:20px;">
                <div style="text-align:center;">
                    <div style="width:120px;height:120px;border-radius:50%;border:6px solid #ddd;margin:0 auto;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:32px;color:#ddd;font-weight:800;">A</span>
                    </div>
                </div>
                <div>
                    <div style="height:16px;background:#f0f0f0;border-radius:4px;width:70%;margin-bottom:12px;"></div>
                    <div style="height:12px;background:#f0f0f0;border-radius:4px;width:90%;margin-bottom:8px;"></div>
                    <div style="height:12px;background:#f0f0f0;border-radius:4px;width:80%;margin-bottom:8px;"></div>
                    <div style="height:12px;background:#f0f0f0;border-radius:4px;width:60%;margin-bottom:16px;"></div>
                    <div style="height:40px;background:#f1e3ff;border-radius:8px;width:50%;margin-bottom:8px;"></div>
                    <div style="height:40px;background:#f1e3ff;border-radius:8px;width:65%;"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/* Tab bar */
function hsts_pro_render_tab_bar(): void {
    $shield_badge = ' <span style="display:inline-block;padding:1px 8px;background:#f1e3ff;color:#5b06b0;border-radius:100px;font-size:9px;font-weight:700;letter-spacing:.5px;vertical-align:middle;margin-left:4px;">SHIELD</span>';
    ?>
    <div style="border-bottom:2px solid #f1e3ff;margin-bottom:24px;margin-top:20px;padding-bottom:0;display:flex;flex-wrap:wrap;">
        <div style="display:flex;flex-wrap:wrap;gap:0;">
            <?php
            hsts_pro_tab_btn( 'settings', __( 'Settings', 'headers-security-advanced-hsts-wp' ), true );
            hsts_pro_tab_btn( 'dashboard', __( 'Dashboard', 'headers-security-advanced-hsts-wp' ), false, $shield_badge );
            hsts_pro_tab_btn( 'csp', 'CSP', false, $shield_badge );
            hsts_pro_tab_btn( 'notifications', __( 'Notifications', 'headers-security-advanced-hsts-wp' ), false, $shield_badge );
            hsts_pro_tab_btn( 'about', __( 'Free vs Shield', 'headers-security-advanced-hsts-wp' ) );
            hsts_pro_tab_btn( 'faq', __( 'FAQ', 'headers-security-advanced-hsts-wp' ) );
            ?>
        </div>
    </div>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded',function(){
        var tabs=document.querySelectorAll('.hsts-pro-tab'),panels=document.querySelectorAll('.hsts-pro-panel');
        function activateTab(tgt){
            tabs.forEach(function(b){b.style.borderBottomColor='transparent';b.style.color='#575ba3';b.style.fontWeight='500';});
            panels.forEach(function(p){p.style.display=(p.getAttribute('data-tab')===tgt||p.id==='hsts-panel-'+tgt)?'block':'none';});
            tabs.forEach(function(b){if(b.getAttribute('data-tab')===tgt){b.style.borderBottomColor='#5b06b0';b.style.color='#5b06b0';b.style.fontWeight='600';}});
        }
        tabs.forEach(function(t){t.addEventListener('click',function(){var tgt=this.getAttribute('data-tab');activateTab(tgt);});});
        var hash=window.location.hash;
        if(hash&&hash.indexOf('#tab-')===0){var t=hash.replace('#tab-','');activateTab(t);}
    });
    function hstsToggleFaq(btn){var a=btn.nextElementSibling,ic=btn.querySelector('.hsts-faq-icon');if(!a||!ic)return;var o=a.style.maxHeight&&a.style.maxHeight!=='0px';a.style.maxHeight=o?'0px':a.scrollHeight+'px';ic.textContent=o?'+':'\u2212';ic.style.transform=o?'rotate(0deg)':'rotate(180deg)';}
    </script>
    <?php
}

/* Tab panels */
function hsts_pro_render_tab_panels(): void {
    ?>
    <!-- Dashboard locked -->
    <div class="hsts-pro-panel" data-tab="dashboard" style="display:none;padding-top:24px;">
        <?php hsts_pro_locked_preview( 'Dashboard', __( 'Security score (A-F), AI-powered recommendations, header status for all 10 security headers, automated scanning, and scan history.', 'headers-security-advanced-hsts-wp' ) ); ?>
    </div>

    <!-- CSP locked -->
    <div class="hsts-pro-panel" data-tab="csp" style="display:none;padding-top:24px;">
        <?php hsts_pro_locked_preview( 'CSP', __( 'CSP configuration guide with recommended tools, best practices, real-time violation analytics to monitor what browsers are blocking on your site.', 'headers-security-advanced-hsts-wp' ) ); ?>
    </div>

    <!-- Notifications locked -->
    <div class="hsts-pro-panel" data-tab="notifications" style="display:none;padding-top:24px;">
        <?php hsts_pro_locked_preview( __( 'Notifications', 'headers-security-advanced-hsts-wp' ), __( 'Email alerts and webhook notifications to Slack, Discord, Microsoft Teams, or any custom endpoint. Get alerted when your security score drops, a header is removed, or critical issues are found.', 'headers-security-advanced-hsts-wp' ) ); ?>
    </div>

    <!-- Free vs Shield -->
    <div class="hsts-pro-panel" data-tab="about" style="display:none;padding-top:24px;">
        <div style="margin-bottom:24px;max-width:720px;">
            <h4 style="color:#0f135e;font-size:16px;font-weight:700;margin:0 0 10px;"><?php esc_html_e('Free forever. Shield for those who want more.','headers-security-advanced-hsts-wp'); ?></h4>
            <p style="color:#575ba3;font-size:13px;line-height:1.7;margin:0;">
                <?php esc_html_e('Every feature this plugin has offered since day one remains completely free — no limits, no paywalls, no "lite" restrictions. That will never change. Shield is a separate set of brand-new advanced tools (automated scanning, real-time alerts) built for professionals who want deeper monitoring.','headers-security-advanced-hsts-wp'); ?>
                <strong style="color:#0f135e;"><?php esc_html_e('Revenue from Shield goes directly into maintaining and improving the free plugin for everyone.','headers-security-advanced-hsts-wp'); ?></strong>
            </p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;max-width:960px;">
            <div style="background:#fafafa;border-radius:16px;padding:24px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;"><span style="font-size:18px;font-weight:800;color:#0f135e;"><?php esc_html_e('Free','headers-security-advanced-hsts-wp'); ?></span><span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge2049" style="margin-top:0;"><?php esc_html_e('Forever','headers-security-advanced-hsts-wp'); ?></span></div>
                <div style="font-size:28px;font-weight:800;color:#0f135e;margin-bottom:4px;">$0</div>
                <div style="font-size:12px;color:#717171;margin-bottom:16px;"><?php esc_html_e('Free forever','headers-security-advanced-hsts-wp'); ?></div>
                <ul style="list-style:none;padding:0;margin:0;font-size:12px;color:#575ba3;line-height:2.2;">
                    <li>&#10003; <?php esc_html_e('10+ security headers','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('HSTS Preload','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('CSP / Permissions Policy','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('X-Frame / X-Content-Type','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('Per-header on/off control','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('Single-source delivery (no duplicates)','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#10003; <?php esc_html_e('Community support','headers-security-advanced-hsts-wp'); ?></li>
                </ul>
            </div>
            <div style="background:linear-gradient(135deg,#0f135e,#5b06b0);border-radius:16px;padding:24px;color:#fff;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.06);border-radius:50%;"></div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;"><span style="font-size:18px;font-weight:800;">Shield</span><span style="padding:3px 10px;background:rgba(255,255,255,.15);border-radius:100px;font-size:10px;font-weight:600;"><?php esc_html_e('1 site','headers-security-advanced-hsts-wp'); ?></span></div>
                <div style="font-size:28px;font-weight:800;margin-bottom:4px;">$29</div>
                <div style="font-size:12px;color:rgba(255,255,255,.6);margin-bottom:16px;"><?php esc_html_e('per year','headers-security-advanced-hsts-wp'); ?> · <span style="color:rgba(255,255,255,.45);">$2.41/<?php esc_html_e('month','headers-security-advanced-hsts-wp'); ?></span></div>
                <ul style="list-style:none;padding:0;margin:0;font-size:12px;color:rgba(255,255,255,.85);line-height:2.2;">
                    <li>&#10003; <?php esc_html_e('All Free features, plus:','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Security Dashboard & Advisor','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Score Trend chart (week/month/year)','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('PDF Security Report (before/after)','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('CSP Guide & Analytics','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Email & webhook alerts','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Export / Import settings','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Weekly automated scans','headers-security-advanced-hsts-wp'); ?></li>
                </ul>
                <a href="https://open-headers.lemonsqueezy.com/checkout/buy/02981b50-7291-4551-bb9c-79f0f2c3e568" target="_blank" class="HeaderSecurityAdvancedHSTSWPROSHUEbksnack" style="display:inline-block;margin-top:14px;background:#fff !important;color:#5b06b0 !important;font-size:13px;padding:8px 20px;"><?php esc_html_e('Get Shield','headers-security-advanced-hsts-wp'); ?> &rarr;</a>
            </div>
            <div style="background:#fff;border:2px solid #5b06b0;border-radius:16px;padding:24px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;right:0;padding:4px 14px;background:#5b06b0;color:#fff;font-size:10px;font-weight:600;border-radius:0 14px 0 10px;letter-spacing:.5px;"><?php esc_html_e('BEST VALUE','headers-security-advanced-hsts-wp'); ?></div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;"><span style="font-size:18px;font-weight:800;color:#0f135e;">Unlimited</span></div>
                <div style="font-size:28px;font-weight:800;color:#0f135e;margin-bottom:4px;">$99</div>
                <div style="font-size:12px;color:#717171;margin-bottom:16px;"><?php esc_html_e('per year, unlimited sites','headers-security-advanced-hsts-wp'); ?> · <span style="color:#b0adb7;">$8.25/<?php esc_html_e('month','headers-security-advanced-hsts-wp'); ?></span></div>
                <ul style="list-style:none;padding:0;margin:0;font-size:12px;color:#575ba3;line-height:2.2;">
                    <li>&#10003; <?php esc_html_e('Everything in Shield','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Unlimited site activations','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Ideal for agencies','headers-security-advanced-hsts-wp'); ?></li>
                    <li>&#9670; <?php esc_html_e('Priority support','headers-security-advanced-hsts-wp'); ?></li>
                    <li style="color:#28a745;font-weight:600;">&#10003; <?php esc_html_e('$29/site? With 4+ sites you save','headers-security-advanced-hsts-wp'); ?></li>
                </ul>
                <a href="https://open-headers.lemonsqueezy.com/checkout/buy/02981b50-7291-4551-bb9c-79f0f2c3e568" target="_blank" style="display:inline-block;margin-top:14px;padding:8px 20px;background:linear-gradient(to right,#0f135e,#5b06b0);color:#fff;border-radius:100px;text-decoration:none;font-size:13px;font-weight:500;"><?php esc_html_e('Get Unlimited','headers-security-advanced-hsts-wp'); ?> &rarr;</a>
            </div>
        </div>
        <div style="margin-top:24px;padding-top:16px;border-top:1px solid #f1e3ff;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:13px;font-weight:700;color:#0f135e;">Headers Security Advanced &amp; HSTS WP</span>
            <span style="font-size:11px;color:#717171;"><?php printf(esc_html__('by %1$sAndrea Ferro%2$s','headers-security-advanced-hsts-wp'),'<strong>','</strong>'); ?> &middot; <a href="https://openheaders.org" target="_blank" style="color:#5b06b0;text-decoration:none;">OpenHeaders.org</a></span>
            <div style="margin-left:auto;display:flex;gap:8px;">
                <span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge1950"><a href="https://wordpress.org/support/plugin/headers-security-advanced-hsts-wp/reviews/#new-post" target="_blank">&#9733; <?php esc_html_e('Review','headers-security-advanced-hsts-wp'); ?></a></span>
                <span class="HeaderSecurityAdvancedHSTSWPROSHUEbadge1950"><a href="https://wordpress.org/support/plugin/headers-security-advanced-hsts-wp/" target="_blank"><?php esc_html_e('Support','headers-security-advanced-hsts-wp'); ?></a></span>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="hsts-pro-panel" data-tab="faq" style="display:none;padding-top:24px;">
        <div style="margin-bottom:16px;max-width:680px;">
            <input type="text" id="hsts-faq-search" placeholder="<?php esc_attr_e('Search FAQ...','headers-security-advanced-hsts-wp'); ?>" style="width:100%;padding:10px 16px;border:1px solid rgb(224,220,229);border-radius:100px;font-size:14px;box-shadow:rgba(43,34,51,.04) 0 1px 2px inset;" oninput="(function(v){document.querySelectorAll('.hsts-faq-item').forEach(function(el){el.style.display=el.textContent.toLowerCase().indexOf(v.toLowerCase())!==-1?'':'none';});})(this.value)" />
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px;">
            <?php foreach(array('all'=>__('All','headers-security-advanced-hsts-wp'),'general'=>__('General','headers-security-advanced-hsts-wp'),'hsts'=>'HSTS','csp'=>'CSP','headers'=>__('Headers','headers-security-advanced-hsts-wp'),'troubleshooting'=>__('Troubleshooting','headers-security-advanced-hsts-wp'),'pro'=>'Shield') as $ck=>$cl): ?>
                <button type="button" class="hsts-faq-cat" data-cat="<?php echo esc_attr($ck); ?>" style="padding:5px 14px;border-radius:100px;border:1px solid #e0dce5;background:<?php echo 'all'===$ck?'#f1e3ff':'#fff'; ?>;color:<?php echo 'all'===$ck?'#5b06b0':'#575ba3'; ?>;font-size:12px;cursor:pointer;" onclick="(function(btn,cat){document.querySelectorAll('.hsts-faq-cat').forEach(function(b){b.style.background='#fff';b.style.color='#575ba3';});btn.style.background='#f1e3ff';btn.style.color='#5b06b0';document.querySelectorAll('.hsts-faq-item').forEach(function(el){el.style.display=(cat==='all'||el.getAttribute('data-cat')===cat)?'':'none';});})(this,'<?php echo esc_js($ck); ?>')"><?php echo esc_html($cl); ?></button>
            <?php endforeach; ?>
        </div>
        <?php
        $faqs = array(
            array('general',__('What does this plugin do?','headers-security-advanced-hsts-wp'),__('Adds HTTP security headers to your WordPress site: HSTS, CSP, Permissions Policy, X-Frame-Options, and more. These headers tell browsers how to protect visitors from attacks like XSS, clickjacking, and protocol downgrades.','headers-security-advanced-hsts-wp')),
            array('general',__('Will this plugin slow down my site?','headers-security-advanced-hsts-wp'),__('No. Headers add less than 1KB to each response. The plugin uses WordPress native hooks and adds no database queries at page load for visitors.','headers-security-advanced-hsts-wp')),
            array('general',__('Is the free version really free forever?','headers-security-advanced-hsts-wp'),__('Yes. Every security header, every configuration option, every protection this plugin offers today will remain completely free. No features will ever be moved behind a paywall. Shield is a separate set of brand-new tools built on top. Revenue from Shield funds free updates for all users.','headers-security-advanced-hsts-wp')),
            array('general',__('Does it work with Nginx, LiteSpeed, or IIS?','headers-security-advanced-hsts-wp'),__('Yes, on any server. On Apache and LiteSpeed the plugin writes the headers to your .htaccess so cached and static responses are covered too, and keeps PHP as the source on dynamic pages; it resolves any duplicate itself. On Nginx and IIS, which do not read .htaccess, the headers are sent via PHP.','headers-security-advanced-hsts-wp')),
            array('general',__('Does it work with caching plugins?','headers-security-advanced-hsts-wp'),__('Yes. On Apache and LiteSpeed the plugin writes the headers to your .htaccess, so even page caches that serve static HTML directly from the web server (e.g. WP Super Cache in Expert/mod_rewrite mode, or Cache Enabler) still get the security headers. PHP-served caching (WP Super Cache Simple mode, W3 Total Cache, LiteSpeed Cache) is covered as well. One caveat: WP Rocket serves its cache through an early advanced-cache.php drop-in that runs before plugins load; we have modelled this but not verified coverage on a live WP Rocket site, so check its cached responses and add a server or CDN rule if a header is missing. On Nginx and IIS, which do not read .htaccess, static cached responses bypass PHP and so are not covered; add the headers at the server or CDN level there.','headers-security-advanced-hsts-wp')),
            array('general',__('Does it work with Cloudflare?','headers-security-advanced-hsts-wp'),__('Yes. Cloudflare passes through headers set by WordPress. Some headers like HSTS can also be set in Cloudflare dashboard — avoid setting them in both places to prevent duplicates.','headers-security-advanced-hsts-wp')),
            array('general',__('What WordPress version is required?','headers-security-advanced-hsts-wp'),__('WordPress 4.7 and above. Uses only WordPress core APIs with no external dependencies.','headers-security-advanced-hsts-wp')),
            array('general',__('Can it conflict with other security plugins?','headers-security-advanced-hsts-wp'),__('Rarely. The plugin detects and resolves its own duplicates automatically. If another plugin or your server sets the same header and you still see a duplicate, open "Advanced: per-header delivery" in Settings and set that header to "Server only" (keep the server copy, drop the plugin copy) or "Off".','headers-security-advanced-hsts-wp')),
            array('hsts',__('What is HSTS?','headers-security-advanced-hsts-wp'),__('HTTP Strict Transport Security tells browsers to always use HTTPS. Even if someone types http://, the browser upgrades to https:// automatically. Prevents protocol downgrade attacks.','headers-security-advanced-hsts-wp')),
            array('hsts',__('What max-age should I use?','headers-security-advanced-hsts-wp'),__('Minimum for preload: 31536000 (1 year). Recommended: 63072000 (2 years). Start with 86400 (1 day) to test, then increase.','headers-security-advanced-hsts-wp')),
            array('hsts',__('Should I enable HSTS Preload?','headers-security-advanced-hsts-wp'),__('Only if your entire domain (including all subdomains) works over HTTPS. Preload is hardcoded in browsers and difficult to undo. Removal takes months. Test thoroughly first.','headers-security-advanced-hsts-wp')),
            array('hsts',__('How do I undo HSTS?','headers-security-advanced-hsts-wp'),__('Disable HSTS in plugin settings. Clear browser cache: Chrome chrome://net-internals/#hsts → search domain → Delete. If on preload list, submit removal at hstspreload.org (takes months).','headers-security-advanced-hsts-wp')),
            array('csp',__('What is CSP?','headers-security-advanced-hsts-wp'),__('Content Security Policy tells browsers which resources can load on your page. Anything not listed is blocked. It is the strongest protection against XSS attacks.','headers-security-advanced-hsts-wp')),
            array('csp',__('Why is CSP hard to configure?','headers-security-advanced-hsts-wp'),__('WordPress sites load resources from many sources: theme, plugins, Google Fonts, analytics, CDNs. CSP must list every legitimate source. Miss one and something breaks.','headers-security-advanced-hsts-wp')),
            array('csp',__('What is Report-Only mode?','headers-security-advanced-hsts-wp'),__('Your server sends the CSP with Content-Security-Policy-Report-Only instead of Content-Security-Policy. The browser checks the policy but blocks nothing — only reports violations. Perfect for testing.','headers-security-advanced-hsts-wp')),
            array('headers',__('What does X-Frame-Options do?','headers-security-advanced-hsts-wp'),__('Controls iframe embedding. SAMEORIGIN allows only your domain to frame your pages. Prevents clickjacking attacks where malicious sites embed your site in hidden iframes.','headers-security-advanced-hsts-wp')),
            array('headers',__('What does Permissions-Policy do?','headers-security-advanced-hsts-wp'),__('Controls browser APIs: camera, microphone, geolocation, payment, USB. Setting them to () disables access, preventing malicious scripts from using sensitive device features.','headers-security-advanced-hsts-wp')),
            array('headers',__('What does X-Content-Type-Options do?','headers-security-advanced-hsts-wp'),__('The nosniff value prevents browsers from guessing file types. Stops MIME-confusion attacks where text files get executed as scripts.','headers-security-advanced-hsts-wp')),
            array('troubleshooting',__('SecurityHeaders.com shows a bad grade','headers-security-advanced-hsts-wp'),__('Usually means CSP is not configured or using only the default. Configure all headers in Settings. Check each header section and enable the ones that show as missing.','headers-security-advanced-hsts-wp')),
            array('troubleshooting',__('I see "Duplicate header" warnings','headers-security-advanced-hsts-wp'),__('The plugin resolves its own duplicates automatically: when it writes a header to .htaccess it suppresses its own PHP copy of that header. If a header is still doubled, your server or another plugin is also sending it: open Settings → "Advanced: per-header delivery" and set that header to "Server only" to keep the other source and drop the plugin copy, or "Off" to stop the plugin sending it.','headers-security-advanced-hsts-wp')),
            array('pro',__('What happens when Shield license expires?','headers-security-advanced-hsts-wp'),__('Your site stays fully protected. All headers keep working. You lose Shield features (dashboard, advisor, alerts, analytics) and revert to the free version. Nothing breaks.','headers-security-advanced-hsts-wp')),
            array('pro',__('How do I install Shield?','headers-security-advanced-hsts-wp'),__('Purchase Shield, download the ZIP from your order confirmation, go to Plugins → Add New → Upload Plugin → replace the existing plugin. Then enter your license key in the License tab. All your settings are preserved.','headers-security-advanced-hsts-wp')),
        );
        foreach ( $faqs as $faq ) : ?>
            <div class="hsts-faq-item" data-cat="<?php echo esc_attr($faq[0]); ?>" style="margin-bottom:6px;">
                <button type="button" onclick="hstsToggleFaq(this)" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#fafafa;border:none;border-radius:10px;cursor:pointer;text-align:left;">
                    <span style="font-size:13px;color:#0f135e;font-weight:500;"><?php echo esc_html($faq[1]); ?></span>
                    <span class="hsts-faq-icon" style="font-size:18px;color:#5b06b0;transition:transform .2s;">+</span>
                </button>
                <div style="max-height:0;overflow:hidden;transition:max-height .3s ease;">
                    <p style="padding:10px 16px 4px;font-size:13px;color:#575ba3;line-height:1.7;margin:0;"><?php echo esc_html($faq[2]); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
