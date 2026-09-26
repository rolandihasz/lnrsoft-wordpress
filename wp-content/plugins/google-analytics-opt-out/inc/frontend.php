<?php

/**
 * Adds a hidden div to the footer
 *
 * @return void
 * @since 1.0
 */
function gaoop_footer() {

	if ( ! get_option( 'gaoop_banner', false ) ) {
		return;
	}

	$opt_out_text = apply_filters( 'gaoop_optout_text', '' );
	if ( empty( $opt_out_text ) ) {
		return;
	}

	/**
	 * INFO icon
	 */
	$info_icon_url = apply_filters( 'gaoop_info_icon', '' );

	if ( empty( $info_icon_url ) ) {
		$info_icon = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="info-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg>';
	} else {
		$info_icon = sprintf(
			'<img src="%s" alt="%s" />',
			esc_url( $info_icon_url ),
			esc_html( __( 'Close', 'google-analytics-opt-out' ) )
		);
	}

	$info_icon = apply_filters( 'gaoop_info_icon_html', $info_icon );

	/**
	 * CLOSE icon
	 */
	$close_icon_url = apply_filters( 'gaoop_close_icon', '' );

	if ( empty( $close_icon_url ) ) {
		$close_icon = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="times" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><path fill="currentColor" d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg>';
	} else {
		$close_icon = sprintf(
			'<img src="%s" alt="%s" />',
			esc_url( $close_icon_url ),
			esc_html( __( 'Close', 'google-analytics-opt-out' ) )
		);
	}

	$close_icon = apply_filters( 'gaoop_close_icon_html', $close_icon );

	printf(
		'<input type="checkbox" class="gaoop-checkbox" id="gaoop_checkbox" />'
		. '<div data-gaoop_hide_after_close="%d" class="gaoop gaoop-hidden">'
		. '<label for="gaoop_checkbox" class="gaoop-info-icon" title="%s">%s</label>'
		. '<div class="gaoop-opt-out-content">%s</div>'
		. '<label for="gaoop_checkbox" class="gaoop-close-icon" title="%s">%s</label>'
		. '</div>',
		intval( get_option( 'gaoop_hide', 0 ) ),
		esc_attr( __( 'Google Analytics Opt-Out Information', 'google-analytics-opt-out' ) ),
		$info_icon,
		$opt_out_text,
		esc_attr( __( 'Close this and do not ask me again', 'google-analytics-opt-out' ) ),
		$close_icon
	);

}

add_action( 'wp_footer', 'gaoop_footer' );


/**
 * The opt-out text on the DIV on the footer
 *
 * @since 1.0
 */
function gaoop_optout_text() {

	$opt_out_text = get_option( 'gaoop_opt_out_text', '' );
	if ( empty( $opt_out_text ) ) {
		$opt_out_text = __( 'This website is using Google Analytics. Please click here if you want to opt-out.', 'google-analytics-opt-out' );
	}

	if ( ! has_shortcode( $opt_out_text, 'google_analytics_optout' ) && (bool) get_option( 'gaoop_opt_out_shortcode_integration', 1 ) ) {
		$opt_out_text .= sprintf( ' [google_analytics_optout]%s[/google_analytics_optout]', __( 'Click here to opt-out.', 'google-analytics-opt-out' ) );
	}

	return $opt_out_text;
}

add_filter( 'gaoop_optout_text', 'gaoop_optout_text', 5 );

add_filter( 'gaoop_optout_text', 'do_shortcode', 15 );

/**
 * Adds the custom styles to the header
 *
 * @return void
 * @since 1.0
 */
function gaoop_wp_head() {

	$box_shadow = '0 4px 15px rgba(0, 0, 0, 0.4)';

	$standard_css_array = array(
		'.gaoop'                                                             => array(
			'color'              => '#ffffff',
			'line-height'        => '2',
			'position'           => 'fixed',
			'bottom'             => 0,
			'left'               => 0,
			'width'              => '100%',
			'-webkit-box-shadow' => $box_shadow,
			'-moz-box-shadow'    => $box_shadow,
			'box-shadow'         => $box_shadow,
			'background-color'   => '#0E90D2',
			'padding'            => '1rem',
			'margin'             => 0,
			'display'            => 'flex',
			'align-items'        => 'center',
			'justify-content'    => 'space-between'
		),
		'.gaoop-hidden'                                                      => array(
			'display' => 'none',
		),
		'.gaoop-checkbox:checked + .gaoop'                                   => array(
			'width'          => 'auto',
			'right'          => 0,
			'left'           => 'auto',
			'opacity'        => '0.5',
			'ms-filter'      => '"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)"',
			'filter'         => 'alpha(opacity=50)',
			'-moz-opacity'   => '0.5',
			'-khtml-opacity' => '0.5',
		),
		'.gaoop-checkbox:checked + .gaoop .gaoop-close-icon'                 => array(
			'display' => 'none',
		),
		'.gaoop-checkbox:checked + .gaoop .gaoop-opt-out-content'            => array(
			'display' => 'none',
		),
		'input.gaoop-checkbox'                                               => array(
			'display' => 'none',
		),
		'.gaoop a'                                                           => array(
			'color'           => '#67C2F0',
			'text-decoration' => 'none',
		),
		'.gaoop a:hover'                                                     => array(
			'color'           => '#ffffff',
			'text-decoration' => 'underline',
		),
		'.gaoop-info-icon'                                                   => array(
			'margin'  => '0',
			'padding' => '0',
			'cursor'  => 'pointer',
		),
		'.gaoop svg'                                                         => array(
			'position' => 'relative',
			'margin'   => '0',
			'padding'  => '0',
			'width'    => 'auto',
			'height'   => '25px',
		),
		'.gaoop-close-icon'                                                  => array(
			'cursor'         => 'pointer',
			'position'       => 'relative',
			'opacity'        => '0.5',
			'ms-filter'      => '"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)"',
			'filter'         => 'alpha(opacity=50)',
			'-moz-opacity'   => '0.5',
			'-khtml-opacity' => '0.5',
			'margin'         => '0',
			'padding'        => '0',
			'text-align'     => 'center',
			'vertical-align' => 'top',
			'display'        => 'inline-block',
		),
		'.gaoop-close-icon:hover'                                            => array(
			'z-index'        => '1',
			'opacity'        => '1',
			'ms-filter'      => '"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)"',
			'filter'         => 'alpha(opacity=100)',
			'-moz-opacity'   => '1',
			'-khtml-opacity' => '1',
		),
		'.gaoop_closed .gaoop-opt-out-link, .gaoop_closed .gaoop-close-icon' => array(
			'display' => 'none',
		),
		'.gaoop_closed:hover'                                                => array(
			'opacity'        => '1',
			'ms-filter'      => '"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)"',
			'filter'         => 'alpha(opacity=100)',
			'-moz-opacity'   => '1',
			'-khtml-opacity' => '1',
		),
		'.gaoop_closed .gaoop-opt-out-content'                               => array(
			'display' => 'none',
		),
		'.gaoop_closed .gaoop-info-icon'                                     => array(
			'width' => '100%',
		),
		'.gaoop-opt-out-content'                                             => array(
			'display'        => 'inline-block',
			'vertical-align' => 'top',
		),
	);

	$standard_css_array = apply_filters( 'gaoop_standard_styles_array', $standard_css_array );

	$standard_css = '';
	if ( is_array( $standard_css_array ) ) {
		foreach ( $standard_css_array as $key => $options ) {
			$standard_css .= $key . ' {';
			if ( is_array( $options ) ) {
				foreach ( $options as $option => $value ) {
					$standard_css .= $option . ': ' . $value . '; ';
				}
			}
			$standard_css .= '} ';
		}
	}

	$standard_css = apply_filters( 'gaoop_standard_styles', $standard_css );

	$custom_css = get_option( 'gaoop_custom_styles', '' );
	$custom_css = apply_filters( 'gaoop_custom_styles', $custom_css );

	echo '<style type="text/css">/** Google Analytics Opt Out Custom CSS **/' . $standard_css . $custom_css . '</style>';
}

add_action( 'wp_head', 'gaoop_wp_head' );
