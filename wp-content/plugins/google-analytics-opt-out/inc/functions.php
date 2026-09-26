<?php

/**
 * Checks if the Yoast Analytics Plugin is active
 *
 * @since 1.0
 * @deprecated 2.0.2
 *
 * @todo Remove this in a future version.
 *
 * @return bool
 */
function gaoop_yoast_plugin_active() {

	return gaoop_monster_insights_plugin_active();
}

/**
 * Checks if Monster Insights is active
 *
 * @since 2.0.2
 * @return bool
 */
function gaoop_monster_insights_plugin_active() {

	return function_exists( 'monsterinsights_get_v4_id' );
}

/**
 * Return the UA from Yoast settings, if Yoast Analytics plugin is installed
 * If Yoast Analytics is not installed this will return an empty string
 *
 * @since 1.0
 * @deprecated 2.0.2
 *
 * @todo Remove this in a future version.
 *
 * @return string
 */
function gaoop_get_yoast_ua() {

	return gaoop_get_monster_insights_ua();
}

/**
 * Return the UA from Monster Insights settings.
 * If Monster Insights is not installed this will return an empty string.
 *
 * @since 1.0
 * @return string
 */
function gaoop_get_monster_insights_ua() {

	if ( ! gaoop_monster_insights_plugin_active() ) {
		return '';
	}

	return monsterinsights_get_v4_id();
}

/**
 * Returns the UA-Code
 *
 * @since 1.0
 * @return string
 */
function gaoop_get_ua_code() {

	$use_monster_insights = get_option( 'gaoop_yoast', null ); # in a past version this was Yoast Analytics.

	// if the plugin is used the first time, this value is NULL
	if ( is_null( $use_monster_insights ) ) {
		$use_monster_insights = 1;
	}

	// if Monster Insights should be used, try to get the ua code from the plugin
	if ( 1 == intval( $use_monster_insights ) && gaoop_monster_insights_plugin_active()) {

		$monster_insights_ua = gaoop_get_monster_insights_ua();

		if ( ! empty( $monster_insights_ua ) ) {
			return apply_filters( 'gaoop_get_ua_code', $monster_insights_ua );
		}
	}

	// if yoast returns an empty string OR if the checkbox was set to 0 return the textbox content
	return apply_filters( 'gaoop_get_ua_code', esc_attr( get_option( 'gaoop_property', '' ) ) );

}

/**
 * Sets the message to show when cookie has set
 *
 * @since 1.0
 */
function gaoop_cookie_set() {

	$message = apply_filters( 'gaoop_opt_out_cookie_set_text', '' );

	return "alert('" . esc_attr( esc_html( $message ) ) . "');";
}

add_filter( 'gaoop_cookie_set', 'gaoop_cookie_set' );


function gaoop_opt_out_cookie_set_text() {

	$text = sanitize_text_field( get_option( 'gaoop_opt_out_cookie_set_text', '' ) );
	if ( ! empty( $text ) ) {
		return $text;
	}

	return __( 'Thanks. We have set a cookie so that Google Analytics data collection will be disabled on your next visit.', 'google-analytics-opt-out' );
}

add_filter( 'gaoop_opt_out_cookie_set_text', 'gaoop_opt_out_cookie_set_text' );