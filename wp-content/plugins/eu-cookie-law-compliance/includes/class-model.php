<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class TPLIS_CL_Model {

	/**
	 * Cookie name
	 *
	 * @var string
	 */
	private $cookie_name;

	/**
	 * Cookie expity days
	 *
	 * @var int
	 */
	private $expiry_days;

	/**
	 * Stores all texts
	 *
	 * @var array
	 */
	public $content;

	/**
	 * Stores CSS variables
	 *
	 * @var array
	 */
	public $css;

	/**
	 * Current layout
	 *
	 * @var string
	 */
	public $layout;

	/**
	 * Check if is button refuse
	 *
	 * @var bool
	 */
	public $is_btn_refuse = false;

	/**
	 * Check if is separated line
	 *
	 * @var bool
	 */
	public $is_separated_line = true;

	/*
	 * Is preview
	 * @var bool
	 */
	public $is_preview = false;

	function __construct( $args = array() ) {

		if ( isset( $_GET[ 'tplis-cl-preview' ] ) && $_GET[ 'tplis-cl-preview' ] === 'yes' ) {
			$this->is_preview = true;
		}


		// Cookie name
		if ( isset( $args[ 'cookie_name' ] ) && !empty( $args[ 'cookie_name' ] ) ) {
			$this->cookie_name = sanitize_title( $args[ 'cookie_name' ] );
		} else {
			$this->cookie_name = TPLIS_CL_COOKIE_NAME;
		}

		// Stop init class if the cookie alredy exists
		if ( isset( $_COOKIE[ $this->cookie_name ] ) && $_COOKIE[ $this->cookie_name ] === 'yes' && !$this->is_preview ) {
			return;
		}

		// Disable form settings
		if ( TPLIS_CL()->settings->get_opt( 'enable' ) !== 'enable' && !$this->is_preview ) {
			return;
		}

		// Set expity days
		$this->expiry_days = absint( TPLIS_CL()->settings->get_opt( 'cookie_expiry_days' ) );

		// Set vivibility button refuse
		if ( TPLIS_CL()->settings->get_opt( 'hide_button_refuse' ) === 'show' ) {
			$this->is_btn_refuse = true;
		}
		$this->is_btn_refuse = apply_filters( 'tplis_cl_is_refuse', $this->is_btn_refuse );

		// Set vivibility separated line
		if ( TPLIS_CL()->settings->get_opt( 'separated_line' ) === 'hide' ) {
			$this->is_separated_line = false;
		}
		$this->is_separated_line = apply_filters( 'tplis_cl_is_separated_line', $this->is_separated_line );


		// All texts
		$this->content = $this->get_content();
		if ( isset( $args[ 'content' ] ) && is_array( $args[ 'content' ] ) ) {
			$this->content = wp_parse_args( $args[ 'content' ], $this->content );
		}

		// Prepare CSS
		if ( isset( $args[ 'css' ] ) && is_string( $args[ 'css' ] ) ) {
			$this->css = prepare_css( $args[ 'css' ] );
		} else {
			$this->css = $this->prepare_css();
		}

		// Set layout
		if ( isset( $args[ 'layout' ] ) && is_string( $args[ 'layout' ] ) ) {
			$this->layout = $this->get_layout( $args[ 'layout' ] );
		} else {
			$this->layout = $this->get_layout();
		}

		// Print css
		add_action( 'wp_head', array( $this, 'print_layout_css' ) );

		add_action( 'wp_footer', array( $this, 'init' ), 500 );
	}

	/*
	 * Get content (all texts)
	 */

	private function get_content() {

		$content = array(
			'title'				 => TPLIS_CL()->settings->get_opt( 'title' ),
			'message'			 => rtrim( wpautop( TPLIS_CL()->settings->get_opt( 'message' ) ) ),
			'btn_accept_text'	 => TPLIS_CL()->settings->get_opt( 'button_accept_text' ),
			'btn_refuse_text'	 => TPLIS_CL()->settings->get_opt( 'button_refuse_text' ),
		);

		return $content;
	}

	/*
	 * Get layout
	 * @param string $layout
	 */

	private function get_layout( $layout = '' ) {

		if ( !empty( $layout ) ) {
			$current_layout = $layout;
		} else {
			$current_layout = TPLIS_CL()->settings->get_opt( 'layout_type' );
		}

		return apply_filters( 'tplis_cl_layout', $current_layout );
	}

	/*
	 * Get HTML
	 */

	public function get_cookie_html() {

		$html = '';

		$directory = sanitize_title( $this->layout );

		$dir = TPLIS_CL_DIR . 'includes/templates/' . $directory . '/layout-html.php';

		if ( file_exists( $dir ) ) {

			// Exclude from cache
			$html = '<!--googleoff: index-->';
			$html .= '<!-- mfunc -->';

			ob_start();
			include $dir;
			$html .= $this->prepare_html( ob_get_clean() );

			$html .= '<!-- /mfunc -->';
			$html .= '<!--googleon: index-->';
		}


		return $html;
	}

	/*
	 * Prepare html
	 */

	private function prepare_html( $html ) {

		// Remove break lines
		$html = str_replace( array( "\n", "\r" ), "", $html );

		//Replace placeholders
		// Title
		$html = str_replace( '{cookie_title}', $this->content[ 'title' ], $html );

		// Message
		$html = str_replace( '{cookie_message}', $this->content[ 'message' ], $html );

		// Accept button text
		$html = str_replace( '{cookie_accept_text}', $this->content[ 'btn_accept_text' ], $html );

		// Refuse button text
		$html = str_replace( '{cookie_refuse_text}', $this->content[ 'btn_refuse_text' ], $html );

		return $html;
	}

	/*
	 * Prepare css
	 * 
	 */

	private function prepare_css( $args = array() ) {

		$defaults = array(
			'ver_position'			 => TPLIS_CL()->settings->get_opt( 'ver_position' ),
			'z-index'				 => TPLIS_CL()->settings->get_opt( 'z_index' ),
			'container_bg'			 => TPLIS_CL()->settings->get_opt( 'bg_color' ),
			'container_bg_opacity'	 => TPLIS_CL()->settings->get_opt( 'bg_opacity' ),
			'text_color'			 => TPLIS_CL()->settings->get_opt( 'text_color' ),
			'bg_btn_accept'			 => TPLIS_CL()->settings->get_opt( 'bg_btn_accept' ),
			'color_btn_accept'		 => TPLIS_CL()->settings->get_opt( 'color_btn_accept' ),
			'bg_btn_refuse'			 => TPLIS_CL()->settings->get_opt( 'bg_btn_refuse' ),
			'color_btn_refuse'		 => TPLIS_CL()->settings->get_opt( 'color_btn_refuse' ),
			'separated_line_size'	 => TPLIS_CL()->settings->get_opt( 'separated_line_size' ),
			'separated_line_color'	 => TPLIS_CL()->settings->get_opt( 'separated_line_color' ),
			'font'					 => '',
		);

		// Converta percentage on float.
		$op = absint( $defaults[ 'container_bg_opacity' ] );
		if ( $op >= 0 && $op <= 100 ) {
			$defaults[ 'container_bg_opacity' ] = number_format( ($op / 100 ), 2, '.', '' );
		} else {
			$defaults[ 'container_bg_opacity' ] = 1;
		}

		// Font family
		$font = TPLIS_CL()->settings->get_opt( 'fonts' );

		// Not load Google fonts on inherit or empty option
		if ( !empty( $font ) && $font !== 'inherit' ) {

			$safe_fonts = tplis_cl_fonts_options();

			if ( is_string( $font ) && array_key_exists( $font, $safe_fonts ) ) {
				$defaults[ 'font' ] = esc_attr( $safe_fonts[ $font ] );
			}
		}

		$args = wp_parse_args( $args, $defaults );

		return apply_filters( 'tplis_cl_css', $args );
	}

	/*
	 * Print css style
	 */

	private function default_css() {
		?>

		.tplis-cl-cookies:after{
		content:'';
		clear:both;
		display:block;
		}

		.tplis-cl-cookies {
		background-color: <?php echo tplis_cl_hex_to_rgba( $this->css[ 'container_bg' ], $this->css[ 'container_bg_opacity' ] ); ?>;
		position: fixed;
		<?php echo!empty( $this->css[ 'font' ] ) ? 'font-family:"' . $this->css[ 'font' ] . '", sans-serif;' . PHP_EOL : ''; ?>
		<?php
		if ( $this->css[ 'ver_position' ] === 'top' ) {
			echo 'top:0;';
			echo $this->is_separated_line === true ? PHP_EOL . 'border-bottom:1px solid #3B3939;' : '';
		} else {
			echo 'bottom:0;';
			echo $this->is_separated_line === true ? PHP_EOL . 'border-top:1px solid #3B3939;' : '';
		}
		?>

		width: 100%;
		z-index: <?php echo absint( $this->css[ 'z-index' ] ); ?>;
		margin: 0;
		overflow:hidden;
		border-color: <?php echo $this->css[ 'separated_line_color' ]; ?>;
		border-width: <?php echo absint( $this->css[ 'separated_line_size' ] ); ?>px;
		}

		.tplis-cl-cookies-head h4{
		border-right-color:<?php echo $this->css[ 'text_color' ]; ?>;
		}

		.tplis-cl-cookies-buttons:after {
		clear:both:
		content:"";
		display:block;
		}

		.tplis-cl-cookies-text * {
		color:<?php echo $this->css[ 'text_color' ]; ?>;
		}

		.tplis-cl-button-accept, .tplis-cl-button-accept:hover, .tplis-cl-button-accept:focus, .tplis-cl-button-accept:active {
		background-color:<?php echo $this->css[ 'bg_btn_accept' ]; ?>;
		color:<?php echo $this->css[ 'color_btn_accept' ]; ?>;
		}
		.tplis-cl-button-accept svg{
		fill:<?php echo $this->css[ 'color_btn_accept' ]; ?>;
		}	

		.tplis-cl-button-refuse, .tplis-cl-button-refuse:hover {
		background-color:<?php echo $this->css[ 'bg_btn_refuse' ]; ?>;
		color:<?php echo $this->css[ 'color_btn_refuse' ]; ?>;
		}
		.tplis-cl-button-refuse svg{
		fill:<?php echo $this->css[ 'color_btn_refuse' ]; ?>;
		}

		.tplis-cl-cookies-text a {
		font-weight:bold;
		-webkit-transition: all 250ms ease-in-out;
		-moz-transition: all 250ms ease-in-out;
		-ms-transition: all 250ms ease-in-out;
		-o-transition: all 250ms ease-in-out;
		transition: all 250ms ease-in-out;
		border-bottom:1px solid <?php echo $this->css[ 'text_color' ]; ?>;
		}
		.tplis-cl-cookies-text a:hover, .tplis-cl-cookies-text a:focus, .tplis-cl-cookies-text a:active{
		color:<?php echo $this->css[ 'text_color' ]; ?>;
		opacity:0.6;
		}

		<?php
	}

	/*
	 * Print css style
	 */

	public function print_layout_css() {
		
		ob_start();
		
		echo '<style type="text/css">';

		echo $this->default_css();

		if ( !empty( $this->layout ) ) {

			$directory = sanitize_title( $this->layout );

			$dir = TPLIS_CL_DIR . 'includes/templates/' . $directory . '/layout-style.css';

			if ( file_exists( $dir ) ) {


				include($dir);
			}
		}


		echo '</style>';

		$css = ob_get_contents();
		ob_end_clean();

		echo tplis_cl_minify_css( $css );
	}

	/*
	 * Print javascript
	 */

	public function init() {

		$args = array(
			'cookie_name'	 => $this->cookie_name,
			'expiry_days'	 => $this->expiry_days,
			'html'			 => $this->get_cookie_html(),
			'is_preview'	 => $this->is_preview,
			'css'			 => $this->css,
		);

		$cookie = new TPLIS_CL_Cookie( $args );

		$cookie->print_js();
	}

}
