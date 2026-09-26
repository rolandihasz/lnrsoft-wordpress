<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Cut string
 * 
 * @param string $string 
 * @param int $length
 * 
 * @return string
 */

function tplis_cl_str_cut( $string, $length = 40 ) {

	$string = strip_tags( $string );

	if ( strlen( $string ) > $length ) {
		$title = mb_substr( $string, 0, $length, 'utf-8' ) . '...';
	} else {
		$title = $string;
	}
	return $title;
}

/*
 * Add event to the html selector
 * 
 * @param string $action
 * @param string $name
 * 
 * @return string 
 */

function tplis_cl_add_attr_event( $action, $name ) {

	$output = TPLIS_CL_EVENT_ATTR . '="' . sanitize_title( $action ) . ':' . sanitize_title( $name ) . '"';

	return $output;
}

/*
 * Hide admin bar on cookies preview
 */
if ( isset( $_GET[ 'tplis-cl-preview' ] ) && $_GET[ 'tplis-cl-preview' ] === 'yes' ) {
	add_action( 'after_setup_theme', 'tplis_cl_hide_admin_bar_on_preview' );
}

function tplis_cl_hide_admin_bar_on_preview() {
	show_admin_bar( false );
}

/*
 * Convert hexdec color string to rgb(a) string 
 */

function tplis_cl_hex_to_rgba( $color, $opacity = false ) {

	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if ( empty( $color ) )
		return $default;

	//Sanitize $color if "#" is provided 
	if ( $color[ 0 ] == '#' ) {
		$color = substr( $color, 1 );
	}

	//Check if color has 6 or 3 characters and get values
	if ( strlen( $color ) == 6 ) {
		$hex = array( $color[ 0 ] . $color[ 1 ], $color[ 2 ] . $color[ 3 ], $color[ 4 ] . $color[ 5 ] );
	} elseif ( strlen( $color ) == 3 ) {
		$hex = array( $color[ 0 ] . $color[ 0 ], $color[ 1 ] . $color[ 1 ], $color[ 2 ] . $color[ 2 ] );
	} else {
		return $default;
	}

	//Convert hexadec to rgb
	$rgb = array_map( 'hexdec', $hex );

	//Check if opacity is set(rgba or rgb)
	if ( $opacity ) {
		if ( abs( $opacity ) > 1 )
			$opacity = 1.0;
		$output	 = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
	} else {
		$output = 'rgb(' . implode( ",", $rgb ) . ')';
	}

	//Return rgb(a) color string
	return $output;
}

/*
 * Register fonts
 */

add_action( 'wp_enqueue_scripts', 'tplis_cl_register_fonts' );

function tplis_cl_register_fonts() {

	$font = TPLIS_CL()->settings->get_opt( 'fonts' );

	// Not load Google fonts on inherit or empty option
	if ( empty( $font ) || $font === 'inherit' ) {
		return;
	}

	$subset			 = '';
	$character_sets	 = TPLIS_CL()->settings->get_opt( 'character_sets' );


	switch ( $character_sets ) {
		case 'greek-ext':
			$subset	 = '&subset=greek-ext,greek,latin';
			break;
		case 'latin-ext':
			$subset	 = '&subset=latin,latin-ext';
			break;
		default :

			break;
	}

	$gfonts = array(
		'arimo'		 => 'Arimo:400,700',
		'cabin'		 => 'Cabin:400,600',
		'lato'		 => 'Lato:400,700',
		'montserrat' => 'Montserrat:400,700',
		'opensans'	 => 'Open+Sans:400,600',
		'pacifico'	 => 'Pacifico',
		'pt_sans'	 => 'PT+Sans:400,700',
		'raleway'	 => 'Raleway:400,600',
		'source'	 => 'Source+Sans+Pro:600',
		'ubuntu'	 => 'Ubuntu:400,500'
	);

	if ( is_string( $font ) && array_key_exists( $font, $gfonts ) ) {
		wp_enqueue_style( 'tplis-cl-googlefonts', "//fonts.googleapis.com/css?family=" . $gfonts[ $font ] . $subset, false, null );
	}

	if ( !$font ) {
		wp_enqueue_style( 'tplis-cl-googlefonts', "//fonts.googleapis.com/css?family=" . $gfonts[ 'opensans' ] . $subset, false, null );
	}
}

/*
 * Minify JS
 * 
 * @see https://gist.github.com/tovic/d7b310dea3b33e4732c0
 * 
 * @since  1.0.2
 * 
 * @param string
 * @return string
 */

function tplis_cl_minify_js( $input ) {

	if ( trim( $input ) === "" )
		return $input;
	return preg_replace(
	array(
		// Remove comment(s)
		'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
		// Remove white-space(s) outside the string and regex
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
		// Remove the last semicolon
		'#;+\}#',
		// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
		'#([\{,])([\'])(\d+|[a-z_]\w*)\2(?=\:)#i',
		// --ibid. From `foo['bar']` to `foo.bar`
		'#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',
		// Replace `true` with `!0`
		'#(?<=return |[=:,\(\[])true\b#',
		// Replace `false` with `!1`
		'#(?<=return |[=:,\(\[])false\b#',
		// Clean up ...
		'#\s*(\/\*|\*\/)\s*#'
	), array(
		'$1',
		'$1$2',
		'}',
		'$1$3',
		'$1.$3',
		'!0',
		'!1',
		'$1'
	), $input );
}

/*
 * Minify CSS
 * 
 * @see https://gist.github.com/tovic/d7b310dea3b33e4732c0
 *
 * @since  1.0.2
 * 
 * @param string
 * @return string
 */

function tplis_cl_minify_css( $input ) {

	if ( trim( $input ) === "" )
		return $input;
	// Force white-space(s) in `calc()`
	if ( strpos( $input, 'calc(' ) !== false ) {
		$input = preg_replace_callback( '#(?<=[\s:])calc\(\s*(.*?)\s*\)#', function($matches) {
			return 'calc(' . preg_replace( '#\s+#', "\x1A", $matches[ 1 ] ) . ')';
		}, $input );
	}
	return preg_replace(
	array(
		// Remove comment(s)
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
		// Remove unused white-space(s)
		'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
		// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
		'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
		// Replace `:0 0 0 0` with `:0`
		'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
		// Replace `background-position:0` with `background-position:0 0`
		'#(background-position):0(?=[;\}])#si',
		// Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`
		'#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',
		// Minify string value
		'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
		'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
		// Minify HEX color code
		'#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
		// Replace `(border|outline):none` with `(border|outline):0`
		'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
		// Remove empty selector(s)
		'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
		'#\x1A#'
	), array(
		'$1',
		'$1$2$3$4$5$6$7',
		'$1',
		':0',
		'$1:0 0',
		'.$1',
		'$1$3',
		'$1$2$4$5',
		'$1$2$3',
		'$1:0',
		'$1$2',
		' '
	), $input );
}