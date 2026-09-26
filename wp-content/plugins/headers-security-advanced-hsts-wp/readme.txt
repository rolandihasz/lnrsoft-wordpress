=== Headers Security Advanced & HSTS WP ===
Contributors: unicorn03, alexclassroom,
Donate link: https://www.buymeacoffee.com/tentacleplugins
Tags: headers security, hsts, headers, clickjacking, csp
Requires at least: 4.7
Tested up to: 7.0
Stable tag: 5.3.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Best all-in-one WordPress security plugin, uses HTTP & HSTS response headers to avoid vulnerabilities: XSS, injection, clickjacking. Force HTTP/HTTPS.

== Description ==

**Headers Security Advanced & HSTS WP** is Best all-in-one a free plug-in for all WordPress users. Deactivating this plugin will return your site configuration exactly to the state it was in before.

The **Headers Security Advanced & HSTS WP** project implements HTTP response headers that your site can use to increase the security of your website. The plug-in will automatically set up all Best Practices (you don't have to think about anything), these HTTP response headers can prevent modern browsers from running into easily predictable vulnerabilities. The Headers Security Advanced & HSTS WP project wants to popularize and increase awareness and usage of these headers for all wordpress users.

This plugin is developed by OpenHeaders by irn3, we care about WordPress security and best practices.

Check out the best features of **Headers Security Advanced & HSTS WP:**

  * X-XSS-Protection (Deprecated)
  * Pragma (Deprecated)
  * Public-Key-Pins (Deprecated)
  * Expect-CT (Deprecated)
  * Access-Control-Allow-Origin
  * Access-Control-Allow-Methods
  * Access-Control-Allow-Headers
  * X-Content-Security-Policy
  * X-Content-Type-Options
  * X-Frame-Options
  * X-Permitted-Cross-Domain-Policies
  * X-Powered-By
  * Content-Security-Policy
  * Referrer-Policy
  * HTTP Strict Transport Security / HSTS
  * Content-Security-Policy
  * Content-Security-Policy-Report-Only
  * Clear-Site-Data
  * Cross-Origin-Embedder-Policy-Report-Only
  * Cross-Origin-Opener-Policy-Report-Only
  * Cross-Origin-Embedder-Policy
  * Cross-Origin-Opener-Policy
  * Cross-Origin-Resource-Policy
  * Permissions-Policy
  * Strict-dynamic
  * Strict-Transport-Security
  * FLoC (Federated Learning of Cohorts)

**Headers Security Advanced & HSTS WP** is based on **OWASP CSRF** to protect your wordpress site. Using OWASP CSRF, once the plugin is installed, it will provide full CSRF mitigation without having to call a method to use nonce on the output. The site will be secure despite having other vulnerable plugins (CSRF).

HTTP security headers are a critical part of your website's security. After automatic implementation with Headers Security Advanced & HSTS WP, they protect you from the most notorious types of attacks your site might encounter. These headers protect against XSS, code injection, clickjacking, etc.

We have put a lot of effort into making the most important services operational with **Content Security Policy (CSP)**, below are some examples that we have tested and used with **Headers Security Advanced & HSTS WP**:

  * CSP usage for **Google Tag Manager**
    world's most popular tag manager
  * Using CSP for **Gravatar**
    Avatar service for WordPress and Social sites
  * Using CSP for **Wordpress Internal Media**
    support Wordpress media
  * Using CSP for **Youtube Embedded Video SDK**
    support Youtube embedded frames and JS SDK
  * CSP usage for **CookieLaw**
    privacy technology to meet regulatory requirements
  * CSP usage for **Mailchimp**
    support for Mailchimp automation, SDK and modules
  * CSP usage for **Google Analytics**
    support for basic conversion domains such as: stats.g.doubleclick.net and www.google.com
  * CSP usage for **Google Fonts**
    you're not loading it on the page, chances are one of your SDKs is using it
  * Using CSP for **Facebook**
    support Facebook SDK functionality
  * Using CSP for **Stripe**
    highly secure online payment system
  * Using CSP for **New Relic**
    it's a registration and monitoring utility
  * Using CSP for **Linkedin Tags + SDKs**
    support Linkedin Insight, Linkedin Ads and SDK
  * Using CSP for **OneTrust**
    OneTrust support helps companies manage privacy requirements
  * CSP usage for **Moat**
    Moat support to measurement suite such as: ad verification, brand safety, advertising and coverage
  * CSP usage for **jQuery**
    support of jQuery - JS library
  * CSP usage for **Twitter Widgets & SDKs**
    support Connect, Widgets and the Twitter client-side SDK
  * Using CSP for **Google Maps**
    support Google Maps as The ggpht used by streetview
  * Using CSP for **Quantcast Choice**
    Quantcast support for privacy such as GDPR and CCPA
  * CSP usage for **Twitter Ads & Analytics**
    Twitter support for advertising and Analytics
  * Using CSP for **Paypal**
    PayPal support for online payment system
  * Using CSP for **Drift**
    Drift and Driftt support
  * CSP usage for **Cookiebot**
    cookie and tracker support, GDPR/ePrivacy and CCPA compliance
  * CSP usage for **Vimeo Embedded Videos SDK**
    support frames, JS SDK, Froogaloop integration
  * Using CSP for **AppNexus (now Xandr)**
    AppNexus support for custom retargeting
  * Using CSP for **Mixpanel**
    support analytics tool with SDK/JS to collect client-side data
  * Using CSP for **Font Awesome**
    toolkit support for fonts and icons over CSS and Less
  * Using CSP for **Google reCAPTCHA**
    reCAPTCHA support for fraud and bot protection
  * CSP usage for **Bootstrap** CDN
    Bootstrap support for CSS frameworks
  * Using CSP for **HubSpot**
    Hubspot support with many features, used for monitoring and mkt functionality
  * Using CSP for **Hotjar**
    Hotjar tracker support for analytics and metrics
  * Using CSP for **WP.com**
    support for wp.com hosting
  * Using CSP for **Akamai mPulse**
    support for Akamai mPulse, for origin and perimeter integrations
  * CSP usage for **Cloudflare - Rocket-Loader & Mirage**
    support for Mirage libraries for performance acceleration
  * Using CSP for **Cloudflare - CDN.js**
    Cloudflare's open CDN support with multiple libraries
  * Using CSP for **jsDelivr**
    support jsDelivr free CDN for Open Source
    
**Headers Security Advanced & HSTS WP** is based on the OWASP CSRF standard to protect your wordpress site. Using the OWASP CSRF standard, once the plugin is installed, you can customize CSP rules for full CSRF mitigation. The site will be secure despite having other vulnerable plugins (CSRF).

**Integration with Sentry, Report URI, URIports and Datadog**
Sentry is a well-known platform for monitoring and tracking errors in applications. By integrating Sentry with our plugin, users can:
  * Receive detailed reports on content security policy (CSP) violations.
  * Monitor and analyze JavaScript exceptions occurring on their site.
  * Benefit from advanced tools for proactive troubleshooting.

Monitoring and Integration with Sentry, Datadog and URI Reports for optimal security.

= Free Forever =

Every security header, every configuration option, and every protection this plugin offers today will remain completely free. No features will ever be moved behind a paywall. Shield is a separate set of brand-new monitoring tools built on top. The free plugin gets better because Shield exists, not worse.

Even though **FLoC** is still fairly new and not yet widely supported, as programmers we think that privacy protection elements are important, so we choose to give you the feature of being opt out of FLoC! We’ve created a special **“automatic blocking of FLoC”** feature, trying to always **offer the best tool with privacy protection and cyber security** as main targets and focus.

Analyze your site before and after using *Headers Security Advanced & HSTS WP* security headers are self-configured according to HTTP Security Headers and HTTP Strict Transport Security / HSTS best practices.

* Check HTTP Security Headers on <a href="https://securityheaders.com/" target="_blank">securityheaders.com</a> 
* Check HTTP Strict Transport Security / HSTS at <a href="https://hstspreload.org/" target="_blank">hstspreload.org</a>
* Check WebPageTest at <a href="https://www.webpagetest.org/" target="_blank">webpagetest.org</a>
* Check HSTS test website <a href="https://gf.dev/hsts-test/" target="_blank">gf.dev/hsts-test</a>
* Check CSP test website <a href="https://csper.io/evaluator" target="_blank">csper.io/evaluator</a>
* Check CSP Evaluator <a href="https://csp-evaluator.withgoogle.com/" target="_blank">csp-evaluator.withgoogle.com</a>
* CSP Content Security Policy Generator <a href="https://addons.mozilla.org/en-US/firefox/addon/content-security-policy-gen/" target="_blank">addons.mozilla.org</a>

This plugin is updated periodically, our limited support is free, we are available for your feedback (bugs, compatibility issues or recommendations for next updates). We are usually fast :-D.

= Shield — Advanced Features (Optional) =

Every feature this plugin offers today is and will remain completely free, forever. **Shield** is a separate set of brand-new advanced tools for professionals who need deeper monitoring and automation:

* **Security Advisor** — Analyzes your configuration and gives personalized recommendations in plain language
* **CSP Guide** — Recommended tools, safe workflow, WordPress-specific CSP snippets, and CSP FAQ
* **Security Score Dashboard** — Real-time A+ to F grade with header status for all 10 security headers
* **Email & Webhook Alerts** — Get notified via email, Slack, Discord, Microsoft Teams, or custom webhook when something changes
* **CSP Violation Analytics** — See which resources browsers are blocking and why
* **Weekly Automated Scans** — Automatic security audit with scan history and trend tracking

Nothing existing moves behind a paywall. Revenue from Shield directly funds free updates and maintenance for all 100,000+ users. Learn more at [openheaders.org/pro](https://openheaders.org/pro).

== Frequently Asked Questions ==

= Will this plugin slow down my site? =

No. Headers add less than 1KB to each response. The plugin uses WordPress native hooks and adds no database queries at page load for visitors.

= Does it work with Nginx or LiteSpeed? =

Yes. On Apache and LiteSpeed the plugin writes the headers to .htaccess so the web server applies them to every response, including cached HTML and static files, and PHP does not emit a duplicate. On Nginx and IIS (which do not read .htaccess) the headers are sent via PHP, so your normal pages are covered; if you run a server-level cache there, add the equivalent rules to the server config (the plugin's Settings page shows the exact snippet).

= Does it work with caching plugins? =

Yes. On Apache and LiteSpeed the headers are served at the web-server layer (.htaccess), so they are present even on fully cached pages and on static files that never invoke PHP - including caches that serve static HTML through mod_rewrite: W3 Total Cache (including Disk: Enhanced), WP Super Cache (both "Simple" and "Expert"/mod_rewrite modes), Cache Enabler and WP Fastest Cache. On Nginx and IIS those .htaccess rewrite rules are not read, so a server-level cache that serves HTML without PHP needs the equivalent headers added to the server config by your host; pages served through PHP are covered as usual. Note on WP Rocket: it serves its cache through an early advanced-cache.php drop-in that runs before plugins load, so we do not yet claim coverage for its cached responses on any stack - we have modelled this but not verified it on a live WP Rocket site. If you use WP Rocket, check the cached responses with your browser's network tab and, if a header is missing, add the equivalent rule at the server or CDN level.

= Does it work with Cloudflare? =

Yes. Cloudflare passes through headers set by WordPress. If you also set the same headers in the Cloudflare dashboard, open Settings → "Advanced: per-header delivery" and set the matching header to "Off" to avoid duplicates.

= How do I get an A+ grade on SecurityHeaders.com? =

Your site needs all 6 scored headers present: Content-Security-Policy, Strict-Transport-Security, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, and Permissions-Policy. The plugin configures all of these automatically.

= Can it conflict with other security plugins? =

Rarely. The plugin detects and resolves its own duplicates automatically. If another plugin or your server sets the same header and a duplicate remains, open Settings → "Advanced: per-header delivery" and set that header to "Server only" (keep the server copy, drop the plugin copy) or "Off".

= What is HSTS? =

HTTP Strict Transport Security tells browsers to always use HTTPS. Even if someone types http://, the browser upgrades to https:// automatically. Prevents protocol downgrade attacks.

= What max-age should I use for HSTS? =

Minimum for preload: 31536000 (1 year). Recommended: 63072000 (2 years). Start with 86400 (1 day) to test, then increase.

= Should I enable HSTS Preload? =

Only if your entire domain (including all subdomains) works over HTTPS. Preload is hardcoded in browsers and difficult to undo. Removal takes months. Test thoroughly first.

= What is Content Security Policy (CSP)? =

CSP tells browsers which resources can load on your page. Anything not explicitly allowed is blocked. It is the strongest protection against XSS attacks.

= How do I configure CSP Report URI? =

Enter the report URL from your monitoring service (Sentry, Report URI, URIports, or Datadog) into the CSP Report URI field in Settings. The plugin adds the report-uri directive to your CSP header automatically.

= Content Security Policy — Best Practices =

When writing CSP directives:

* Always use single quotes for keywords: 'self', 'none', 'unsafe-inline', 'unsafe-eval'
* Never use double quotes inside CSP syntax
* Avoid smart quotes (curly quotes) — the plugin converts them automatically but standard quotes are recommended
* The plugin validates and sanitizes CSP input to prevent .htaccess errors

= What happens when Shield license expires? =

Your site stays fully protected. All headers keep working. You lose Shield features (dashboard, advisor, alerts, analytics) and revert to the free version. Nothing breaks.

= Can I report a bug or request a feature? =

Yes — contact us at support@openheaders.org or use the [WordPress.org support forum](https://wordpress.org/support/plugin/headers-security-advanced-hsts-wp/).

= What will Report URI monitor for me? =

Report URI will monitor content security policy (CSP) violations and provide detailed reports on detected violations.

= What will Datadog monitor for me? =

Datadog will monitor content security policy (CSP) violations and other security and performance metrics of your site.

= Where can I find my Datadog API Key? =

You can find your Datadog API Key in the "API Keys" section under "Integrations" in the Datadog control panel. Once the plug-in is activated it performs a test (before and after): <a href="https://www.datadoghq.com/blog/content-security-policy-reporting-with-datadog/#csp-reporting-with-datadog" target="_blank">Manage CSP reporting with Datadog</a>

= What will Sentry monitor for me? =

Sentry will monitor and log content security policy (CSP) violations and other JavaScript exceptions that occur on your site.

= How can I configure Sentry integration with the plugin? =

1. Log in to your Sentry dashboard.
2. Click on the "Projects" menu item.
3. Select the project you have created.
4. Click on the gear icon to open project settings.
5. In the project settings, go to the "SDK SETUP" section.
6. Click on "Security Headers".
7. Copy the automatically generated "REPORT URI" URL and paste it into the "CSP Report URI" field in the plugin settings. Example Sentry Report URI (e.g., `https://<your_org>.sentry.io/api/<project_id>/security/?sentry_key=<key>`).
8. The plugin will initialize Sentry and send CSP reports to Sentry.

<a href="https://docs.sentry.io/security-legal-pii/security/security-policy-reporting/" target="_blank">Manage CSP reporting with Sentry</a>

= How can I configure URIports integration with the plugin? =

1. Log in to your Sentry dashboard.
2. Click on the "User Icon" at the top right of your screen.
3. Click "Settings".
4. Add the domains you want to monitor to the "Monitored Domains" section on the settings page.
5. Click on "Security Headers".
6. Copy the automatically generated "URIports" URL and paste it into the "CSP Report URI" field in the plugin settings. Example URIports Report URI (e.g., `https://account-subdomain.uriports.com/reports`).
7. The plugin will initialize URIports and send CSP reports to URIports.

<a href="https://www.uriports.com/getting-started-with-website-monitoring" target="_blank">Manage CSP reporting with URIports</a>

= Why did you choose to integrate with Sentry, URIports, Datadog, and Report URI? =

I chose Sentry, URIports, Datadog, and Report URI for integration with this plugin because they are highly reputable and functional platforms in the field of security monitoring. Here's a brief overview of each:

**Sentry**

Sentry is a well-known platform for monitoring and tracking errors and exceptions in applications. It provides comprehensive tools for logging and analyzing JavaScript errors, making it an excellent choice for monitoring Content Security Policy (CSP) violations. By integrating with Sentry, users can benefit from detailed error reports and proactive issue resolution.

**Datadog**

Datadog is a powerful platform for monitoring infrastructure, applications, and logs. It offers extensive capabilities for tracking security and performance metrics, including CSP violations. The integration with Datadog allows users to gain insights into the health and security of their websites, providing real-time monitoring and alerting features that are essential for maintaining a secure and performant environment.

**Report URI**

Report URI is a dedicated service for collecting and analyzing security violation reports, including CSP, HPKP, and other security headers. It is designed specifically to handle large volumes of security reports and provide detailed analytics and visualizations. By using Report URI, users can easily monitor and analyze CSP violations, helping them to quickly identify and mitigate potential security threats.

Each of these platforms offers unique strengths and capabilities, making them ideal choices for comprehensive security monitoring and reporting. By integrating with these well-established services, we aim to provide users with reliable and effective tools to enhance the security of their WordPress websites.

**URIports**

URIports is a well-known platform for monitoring and tracking errors and exceptions in applications. It provides comprehensive tools for logging and analyzing JavaScript errors, making it an excellent choice for monitoring Content Security Policy (CSP) violations. By integrating with URIports, users can benefit from detailed error reports and proactive issue resolution.

= Can I view CSP reports directly in Sentry? =

Yes, all CSP reports will be sent to Sentry, where you can view and analyze them in the Sentry control panel.

= How do you get an A+ grade? =

To earn an A+ grade, your site must issue all HTTP response headers that we check. This indicates a high level of commitment to improving the security of your visitors.

= What headers are recommended? =

Over an HTTP connection we get Content-Security-Policy, X-Content-Type-Options, X-Frame-Options and X-XSS-Protection. Via an HTTPS connection, 2 additional headers are checked for presence which are Strict-Transport-Security and Public-Key-Pins.

* Once the plug-in is activated it performs a test (before and after): <a href="https://securityheaders.com/" target="_blank">https://securityheaders.com/</a>

= Can the plugin create slowdowns? =

No, Headers Security Advanced & HSTS WP is Fast, Secure and does not affect the SEO and speed of your website.

= Content Security Policy (CSP) – Best Practices =

When writing your CSP directives in the plugin settings, please follow these rules to avoid invalid configurations:

**1. Always use single quotes `'` for CSP keywords**

CSP keywords must always use straight ASCII single quotes:

- 'self'
- 'none'
- 'unsafe-inline'
- 'unsafe-eval'
- 'strict-dynamic'

These are required by the CSP specification.

**2. Never use double quotes `"` inside the CSP**

Double quotes are used only *outside* the policy (for example by Apache when setting headers), not inside the CSP syntax.  
Using double quotes inside the policy may break the .htaccess configuration.

**3. Do not use “smart quotes” or curly quotes (‘ ’ “ ”)**

Smart quotes often appear when copying text from Word, Google Docs, PDFs, email clients, or mobile keyboards. These characters are invalid in CSP and may cause the browser to reject the policy or Apache to return HTTP 500 errors.

The plugin automatically converts smart quotes to standard quotes, but it is recommended to avoid them when writing your policy.

**5. What happens if a user enters an invalid CSP?**

Starting from version 5.2.4, the plugin automatically:
- Normalizes curly quotes to ASCII quotes
- Replaces invalid double quotes inside the CSP
- Prevents malformed CSP syntax from breaking .htaccess
- Falls back to the built-in default CSP if the input is clearly invalid

This ensures that even incorrect CSP input will not cause the site to crash.

= What is HSTS (Strict Transport Security)? =

It was created as a solution to force the browser to use secure connections when a site is running on HTTPS. It is a security header that is added to the web server and reflected in the response header as Strict-Transport-Security. HSTS is important because it addresses the following anomalies:

= Check before and after using Preload HSTS =

This step is important to submit your website and/or domain to an approved HSTS list. Google officially compiles this list and it is used by Chrome, Firefox, Opera, Safari, IE11 and Edge. You can forward your site to the official HSTS preload directory. ('https://hstspreload.org/')

= how to use HTTP Strict Transport Security (HSTS) =

If you want to use Preload HSTS for your site, there are a few requirements before you can activate it.

* Have a valid SSL certificate. You can't do any of this anyway without it.
* You must redirect all HTTP traffic to HTTPS (recommended via permanent 301 redirects). This means that your site should be HTTPS only.
* You need to serve all subdomains in HTTPS as well. If you have subdomains, you will need an SSL certificate.

The HSTS header on your base domain (for example: example.com) is already configured you just need to activate the plug-in.

If you want to check the HSTS status of your site, you can do so here: <a href="https://hstspreload.org/" target="_blank">https://hstspreload.org/</a>

= Can I report a bug or request a feature? =

You can report bugs or request new features right <a href="mailto:support@openheaders.org">support@openheaders[dot]org</a>

= Disable FLoC, Google's advertising technology =

FLoC is a mega tracker that monitors user activity on all sites, stores the information in the browser, and then uses machine learning to place users into cohorts with similar interests. This way, advertisers can target groups of people with similar interests. Plus, according to Google's own testing, FLoC achieves at least 95% more conversions than cookies.

= Who is disabling FLoC by Google? =

Scott Helme reported that as of May 3, already 967 of the first 1 million domains had disabled FLoC's interest-cohort in their Permissions-Policy header. That list included some big sites like The Guardian and IKEA.

= Do you use CloudFlare and the Headers Security Advanced & HSTS WP plugin? =

Are you experiencing any anomalies after a plugin update? If yes, please follow these instructions: clear the cache directly to the CloudFlare Client Area

* Log in to your Cloudflare dashboard, and select your account and domain.
* Select Caching > Configuration.
* Under Cache Purge, select Custom Purge. The custom purge window will be displayed.
* Under Purge by, select URL.
* Enter the appropriate values in the text field using the format shown in the example.
* Run through the additional instructions to complete the form.
* Review the data entered.
* Click Delete.

This will cause the <a href="https://developers.cloudflare.com/cache/how-to/purge-cache/" target="_blank">cloudFlare</a>

== Known limitations ==

* Automatic duplicate resolution needs a self-check that reaches your site's PHP over a loopback connection. On some reverse-proxy stacks - notably nginx in front of Apache, which is the default on Plesk and cPanel - the backend selects the virtual host by TLS SNI, so a loopback request answers 421 (Misdirected Request) and the self-check cannot arm. When this happens the plugin does not de-duplicate automatically; it keeps sending every header via PHP (nothing is lost), and the Delivery diagnostics panel says so. If a header ends up duplicated on such a stack, set it to "Server only" under "Advanced: per-header delivery" - that keeps the server's copy and drops the plugin's own. A future release will make the loopback self-check pin the request to the backend so it can arm automatically on these stacks too.
* Content-Security-Policy is always sent via PHP on the front-end only and is never written to .htaccess, so a strict or nonce-based CSP cannot reach wp-admin or be frozen into a cached response.
* On Nginx and IIS the plugin does not write .htaccess (those servers do not read it). Normal pages are covered via PHP; a server-level cache that serves HTML without invoking PHP needs the equivalent headers added to the server or CDN config. WP Rocket's cache is served by an early advanced-cache.php drop-in that runs before plugins load; we have modelled this but not verified coverage on a live WP Rocket site, so check its cached responses and add a server or CDN rule if a header is missing.

== Installation ==

= ITALIAN =

1. Vai in Plugin 'Aggiungi nuovo'.
2. Cerca Headers Security Advanced & HSTS WP.
3. Cerca questo plugin, scaricalo e attivalo.
4. Vai in 'impostazioni' > 'Headers Security Advanced & HSTS WP'. Per personalizzare le intestazioni.
5. Puoi cambiare questa opzione quando vuoi, Headers Security Advanced & HSTS WP viene impostato in automatico.

= ENGLISH =

1. Go to Plugins 'Add New'.
2. Search for Headers Security Advanced & HSTS WP.
3. Search for this plugin, download and activate it.
4. Go to 'settings' > 'Headers Security Advanced & HSTS WP'. To customize headers.
5. You can change this option whenever you want, Headers Security Advanced & HSTS WP is set automatically.

= FRANÇAIS =

1. Allez dans Plugins 'Add new'.
2. Recherchez Headers Security Advanced & HSTS WP.
3. Recherchez ce plugin, téléchargez-le et activez-le.
4. Allez dans 'settings' > 'Headers Security Advanced & HSTS WP'. Pour personnaliser les en-têtes
5. Vous pouvez modifier cette option quand vous le souhaitez, Headers Security Advanced & HSTS WP est réglé automatiquement.

= SPANISH =

1. Ve a Plugins > Añadir nuevo.
2. Busca Headers Security Advanced & HSTS WP.
3. Busca este plugin, descárgalo y actívalo.
4. Ve a Ajustes > Headers Security Advanced & HSTS WP para personalizar los encabezados.
5. Puedes cambiar esta opción cuando desees, Headers Security Advanced & HSTS WP se configura automáticamente.

= DEUTSCH =

1. Gehen Sie zu Plugins 'Neu hinzufügen'.
2. Suchen Sie nach Headers Security Advanced & HSTS WP.
3. Suchen Sie nach diesem Plugin, laden Sie es herunter und aktivieren Sie es.
4. Gehen Sie zu "Einstellungen" > "Kopfzeilen Sicherheit Erweitert & HSTS WP". So passen Sie die Kopfzeilen an
5. Sie können diese Option jederzeit ändern, Headers Security Advanced & HSTS WP wird automatisch eingestellt.

= PORTUGUESE =

1. Vá para Plugins > Adicionar novo.
2. Procure por Headers Security Advanced & HSTS WP.
3. Procure por este plugin, baixe-o e ative-o.
4. Vá para Configurações > Headers Security Advanced & HSTS WP para personalizar os cabeçalhos.
5. Você pode alterar esta opção sempre que desejar, Headers Security Advanced & HSTS WP é configurado automaticamente.

= SWEDISH =

1. Gå till Plugins > Lägg till nytt.
2. Sök efter Headers Security Advanced & HSTS WP.
3. Sök efter denna plugin, ladda ner och aktivera den.
4. Gå till Inställningar > Headers Security Advanced & HSTS WP för att anpassa rubrikerna.
5. Du kan ändra detta alternativ när du vill, Headers Security Advanced & HSTS WP är inställt automatiskt.

== Screenshots ==

1. Check HTTP Security Headers (AFTER)
2. Check HTTP Security Headers (BEFORE)
3. Check HTTP Strict Transport Security / HSTS (list)
4. Check WebPageTest (AFTER)
5. Check WebPageTest (BEFORE)
6. Setting on single site installation
7. Check HTTP Security Headers - Serpworx (AFTER)
8. Check HTTP Security Headers - Serpworx (BEFORE)
9. Site-wide security setting

== Changelog ==

= 5.3.5 =
This release brings back server-level header coverage (cached pages and static files) that 5.3.4 lost, without reintroducing duplicate headers.

- Fixed: On full-page caches and static files (CSS, JS, images) the security headers were missing after 5.3.4, because headers were emitted only through PHP and those responses never run PHP. External scanners could report the headers as gone. The plugin now writes the headers to .htaccess again on Apache and LiteSpeed, so the web server applies them to every response.
- Added: Automatic de-duplication is available but is turned off by default in this version. The plugin writes the header to .htaccess and PHP keeps sending it too. On Apache the .htaccess directive replaces, so you still get a single copy. On LiteSpeed .htaccess appends instead, so if a header shows up twice, set that header to "Server only" under "Advanced: per-header delivery" to keep the server's copy and drop the plugin's. When automatic de-duplication is enabled, the plugin confirms - by measuring its own live responses - that the server is already sending a header before it stops sending its own copy; if that proof is missing, stale, or uncertain it keeps sending. It always errs toward sending, never toward a missing header.
- Added: A "Run check now" button on the settings page (Delivery diagnostics) shows which web server was detected, whether the .htaccess block is in place, the backend port the plugin learned (or that it has not learned one yet), the exact loopback candidates tried and the responding server, so header delivery can be verified at a glance.
- Added: Per-header delivery control under "Advanced: per-header delivery" (collapsed by default). Each header can be left on "On" (the plugin manages it: it writes the header to .htaccess and PHP sends it), set to "Server only" (keep the server's or another plugin's copy and drop the plugin's own), or "Off" (do not send it at all). With automatic de-duplication off by default in this version, this selector is the primary way to resolve a duplicate: on LiteSpeed, set the affected header to "Server only". The diagnostics panel points here when it detects a duplicate it cannot resolve on its own.
- Added: On Nginx and IIS the plugin does not write .htaccess (those servers do not read it) and instead shows the exact server-config snippet to hand to your host for server-level caches. Normal pages remain covered via PHP.
- Safety: Every .htaccess write is verified over HTTP and automatically rolled back to the previous file if the site returns an error, and the block is wrapped so a server without mod_headers simply ignores it. The block uses stable markers so upgrades replace it in place instead of stacking, and it is removed on deactivation and uninstall.
- Changed: Any header you had previously disabled is preserved as "Off" under the new per-header delivery control, so no header you intentionally turned off comes back.
- Note: Content-Security-Policy continues to be sent via PHP on the front-end only (never written to .htaccess), so a strict or nonce-based policy cannot reach wp-admin or be frozen into a cached file.

= 5.3.4 =
This release fixes duplicate security headers and makes header delivery predictable on every server.

- Fixed: Duplicate headers (Content-Security-Policy, Referrer-Policy, Cross-Origin-*, X-Permitted-Cross-Domain-Policies, Access-Control-Allow-Methods and others) that appeared on Apache and LiteSpeed when both PHP and .htaccess emitted them.
- Changed: Headers are now sent a single time via PHP on all servers. The plugin no longer writes a header block to .htaccess, so there is exactly one source and no duplicates. Any block left by previous versions (including very old formats) is automatically removed on update.
- Fixed: The "disable" checkboxes now work for every header, not just four, and they fully turn a header on or off. The checkboxes have been reset once during the update; a notice explains the change so no site silently loses a header it meant to keep.
- Fixed: The checkbox previously labelled "CSP" actually controlled Permissions-Policy; it has been renamed. Because its meaning changed, it (and the other reset checkboxes) start unchecked. The update notice explains how to re-check any header you want disabled.
- Removed: The deprecated X-Content-Security-Policy header (superseded by Content-Security-Policy).
- Changed: Cross-Origin-Embedder-Policy is no longer sent by default (its previous "unsafe-none" value added no protection and used non-standard syntax).
- Fixed: Removed invalid report-to='default' syntax from Cross-Origin headers.
- Fixed: Rewrite rules are no longer flushed during plugin updates or settings saves (fixes multilingual routing breakage with Polylang and similar plugins).
- Changed: The plugin's Content-Security-Policy is applied to the site front-end; wp-admin is left to WordPress core's own policy, so a strict CSP cannot break the block editor. All other security headers are still applied everywhere.
- Note: static files (CSS, JS, images) served directly by the web server no longer receive security headers, since headers are now emitted via PHP only. The practical impact is X-Content-Type-Options on static assets. Server-level coverage will return as an explicit opt-in feature in a future release.

= 5.3.3 =
This update introduces **Shield** — optional advanced tools for professionals who need deeper security monitoring. Every existing feature remains completely free, forever.

- New: Shield tab-based interface (Settings, Dashboard, CSP, Notifications, Export/Import, License, Free vs Shield, FAQ)
- New: Security Advisor with personalized recommendations
- New: Security Score Dashboard (A+ to F grading, 10 header status overview)
- New: CSP Guide with recommended tools (Csper.io, Google CSP Evaluator, Report URI, URIports)
- New: CSP violation analytics (top blocked domains, directives, trends)
- New: Email alerts when security score drops or headers change
- New: Webhook notifications (Slack, Discord, Microsoft Teams, custom JSON endpoint)
- New: Export/Import settings for agencies managing multiple sites
- New: Weekly automated scans with history tracking
- New: Comprehensive FAQ with search and category filters
- New: Free vs Shield comparison with transparent pricing
- Improved: Clean uninstall (license auto-deactivated, all options removed)

= 5.2.5 =
I don't want to tell you what to do, but here's the thing: When you update the Headers Security Advanced & HSTS WP plugin, you don't just click a button, you enter a world of enhanced security and performance.

With version 5.2.5, I have gone above and beyond to ensure that your experience is nothing short of exceptional. I have eliminated numerous bugs, improved annoying pixels, and updated the graphics in a sleek and modern way. The result? A plugin that not only looks great, but works even better.

But that's not all. This update brings seamless integration with the industry's leading security monitoring platforms-Sentry, Datadog, and Report URI. These integrations offer enhanced reporting capabilities, providing detailed information on content security policy (CSP) violations and improving site security.

- Update: Code optimization for better compatibility with WordPress version 6.9.
- Fixed: Improved compatibility with WordPress 6.9 plugin list rendering
- Fixed: Resolved edge case where plugin list could stop rendering when other plugins return unexpected values from the `plugin_action_links` filter
- Hardened: Added defensive type checking for better interoperability with third-party plugins

By updating to 5.2.5, you’re not just improving your site’s security – you’re optimizing it with the best tools available. Our goal is to provide you with the most beautiful, fastest, and most impressive plugin experience around. So, shall we get started? Hit "update" and step into a new era of security and performance with Headers Security Advanced & HSTS WP. Enjoy the upgrade!
