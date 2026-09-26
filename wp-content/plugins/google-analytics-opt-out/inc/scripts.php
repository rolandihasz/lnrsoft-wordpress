<?php

/**
 * Echos the Javascript or returns it (if $echo is set to TRUE)
 *
 * @return void|string
 * @since 1.0
 *
 */
function gaoop_js() {

	$ua_code = gaoop_get_ua_code();
	if ( empty( $ua_code ) ) {
		return;
	}
	?>
	<script type="text/javascript">
		/* Google Analytics Opt-Out by WP-Buddy | https://wp-buddy.com/products/plugins/google-analytics-opt-out */
		<?php do_action( 'gaoop_js_before_script' ); ?>
		<?php if(gaoop_monster_insights_plugin_active()): ?>
		var gaoop_disable_strs = disableStrs;
		<?php else: ?>
		var gaoop_property = '<?php echo esc_attr( $ua_code ); ?>';
		var gaoop_disable_strs = ['ga-disable-' + gaoop_property];
		<?php endif; ?>
		for (var index = 0; index < disableStrs.length; index++) {
			if (document.cookie.indexOf(disableStrs[index] + '=true') > -1) {
				window[disableStrs[index]] = true; // for Monster Insights
			}
		}

		function gaoop_analytics_optout() {
			for (var index = 0; index < disableStrs.length; index++) {
				document.cookie = disableStrs[index] + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; SameSite=Strict; path=/';
				window[disableStrs[index]] = true; // for Monster Insights
			}
			<?php echo apply_filters( 'gaoop_cookie_set', '' ); ?>
		}
		<?php
		do_action( 'gaoop_js_after_script' );
		?>
	</script>
	<?php
}

add_action( 'plugins_loaded', 'gaoop_plugins_loaded' );

function gaoop_plugins_loaded() {
	if ( gaoop_monster_insights_plugin_active() ) {
		add_action( 'monsterinsights_tracking_after', 'gaoop_js' );
	} else {
		add_action( 'wp_head', 'gaoop_js', 0 );
	}
}


/**
 * Enqueue Frontend Scripts
 *
 * @since 1.0
 */
function gaoop_enqueue_scripts() {
	wp_enqueue_script( 'goop', GAOOP_URL . 'js/frontend.js', array(), false, true );
}

add_action( 'wp_enqueue_scripts', 'gaoop_enqueue_scripts' );
