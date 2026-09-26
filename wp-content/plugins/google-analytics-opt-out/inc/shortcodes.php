<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks if a shortcode is used in a string
 *
 * @param string $shortcode
 * @param string $content
 *
 * @return bool
 */
function gaoop_has_shortcode( $shortcode, $content = '' ) {

	if ( stripos( $content, '[' . $shortcode ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Adds the shortcodes
 *
 * @return void
 * @since 1.0
 */
function gaoop_init_shortcodes() {

	add_shortcode( 'google_analytics_optout', 'gaoop_shortcode' );
	add_shortcode( 'google_analytics_optout_close', 'gaoop_shortcode_close' );
}

add_action( 'init', 'gaoop_init_shortcodes' );


/**
 * Creating the shortcode content
 *
 * @param array $atts
 * @param string $content
 *
 * @return string
 * @since 1.0
 *
 */
function gaoop_shortcode( $atts, $content = '' ) {

	//$atts = shortcode_atts( array(), $atts );

	if ( empty( $content ) ) {
		$content = __( 'Click here to opt out.', 'google-analytics-opt-out' );
	}

	$ua_code = gaoop_get_ua_code();

	if ( empty( $ua_code ) ) {
		return '<span style="cursor: help; border: 0 none; border-bottom-width: 1px; border-style: dashed;" title="' . __( 'No UA-Code has been entered. Please ask the admin to solve this issue!', 'google-analytics-opt-out' ) . '">' . do_shortcode( $content ) . '</span>';
	}

	return '<a class="gaoo-opt-out google-analytics-opt-out" href="javascript:gaoop_analytics_optout();">' . do_shortcode( $content ) . '</a>';
}


/**
 * Close the banner button shortcode.
 *
 * @param array $atts
 * @param string $content
 * @param string $name
 *
 * @return string
 * @since 1.4.0
 */
function gaoop_shortcode_close( $atts, $content, $name ) {

	if ( empty( $content ) ) {
		$content = __( 'Close Opt-Out Banner', 'google-analytics-opt-out' );
	}

	ob_start();
	?>
    <script>
        (function () {
            var links = document.querySelectorAll('.gaoop-close-link');

            for (var i = 0; i < links.length; i++) {
                links[i].addEventListener('click', function (e) {
                    e.preventDefault();
                    var elm = document.querySelector('#gaoop_checkbox');
                    if (elm.checked === false) {
                        elm.click();
                    }
                })
            }
        }());
    </script>
	<?php

	return sprintf( '<a href="#"  class="gaoop-close-link">%s</a>%s', $content, ob_get_clean() );
}

/**
 * Doing shortcodes in the text widget, too
 */
if ( ! has_filter( 'widget_text', 'do_shortcode' ) ) {
	add_filter( 'widget_text', 'do_shortcode' );
}