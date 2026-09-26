<?php

/**
 * Settings API data
 */
class TPLIS_CL_Settings {
	/*
	 * @var string
	 * Unique settings slug
	 */

	private $setting_slug = TPLIS_CL_SETTINGS_KEY;

	/*
	 * @var array
	 * All options values in one array
	 */
	public $opt;

	/*
	 * @var object
	 * Settings API object
	 */
	public $settings_api;

	public function __construct() {
		global $tplis_cl_settings;

		// Set global variable with settings
		$settings = get_option( $this->setting_slug );
		if ( !isset( $settings ) || empty( $settings ) ) {
			$tplis_cl_settings = array();
		} else {
			$tplis_cl_settings = $settings;
		}

		$this->opt = $tplis_cl_settings;

		$this->settings_api = new TPLIS_CL_Settings_API( $this->setting_slug );

		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/*
	 * Set sections and fields
	 */

	public function settings_init() {

		//Set the settings
		$this->settings_api->set_sections( $this->settings_sections() );
		$this->settings_api->set_fields( $this->settings_fields() );

		//Initialize settings
		$this->settings_api->settings_init();
	}

	/*
	 * Set settings sections
	 * 
	 * @return array settings sections
	 */

	public function settings_sections() {

		$sections = array(
			array(
				'id'	 => 'tplis_cl_basic',
				'title'	 => __( 'Basic', TPLIS_CL_DOMAIN )
			),
			array(
				'id'	 => 'tplis_cl_appearance',
				'title'	 => __( 'Appearance', TPLIS_CL_DOMAIN )
			),
			array(
				'id'	 => 'tplis_cl_effects',
				'title'	 => __( 'Effects', TPLIS_CL_DOMAIN )
			)
		);
		return $sections;
	}

	/**
	 * Create settings fields
	 *
	 * @return array settings fields
	 */
	function settings_fields() {
		$settings_fields = array(
			'tplis_cl_basic'		 => apply_filters( 'tplis_cl_basic_settings', array(
				array(
					'name'		 => 'enable',
					'label'		 => __( 'Enable plugin', TPLIS_CL_DOMAIN ),
					'type'		 => 'radio',
					'options'	 => array(
						'enable'	 => __( 'Enable', TPLIS_CL_DOMAIN ),
						'disable'	 => __( 'Disable', TPLIS_CL_DOMAIN ),
					),
					'default'	 => 'enable',
				),
				array(
					'name'		 => 'cookie_expiry_days',
					'label'		 => __( 'Cookie expiry days', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'size'		 => 'small',
					'desc'		 => __( 'Show once more a cookies law banner after X days (after accepting cookies)', TPLIS_CL_DOMAIN ),
					'default'	 => 365,
				),
				array(
					'name'		 => 'title',
					'label'		 => __( 'Title', TPLIS_CL_DOMAIN ),
					'type'		 => 'text',
					'desc'		 => __( 'To hide, leave this field empty.', TPLIS_CL_DOMAIN ),
					'default'	 => __( 'Cookies', TPLIS_CL_DOMAIN ),
				),
				array(
					'name'		 => 'message',
					'label'		 => __( 'Message', TPLIS_CL_DOMAIN ),
					'type'		 => 'wysiwyg_basic',
					'desc'		 => __( 'Fill free to link to the page with the terms of the policy"</i>', TPLIS_CL_DOMAIN ),
					'default'	 => __( 'Our website uses cookies. By using our website and agreeing to this policy, you consent to our use of cookies in accordance with the terms of this policy. If you do not consent to the use of these cookies please disable them following the instructions in this Cookie Notice so that cookies from this website cannot be placed on your device.', TPLIS_CL_DOMAIN ),
				),
				array(
					'name'		 => 'button_accept_text',
					'label'		 => __( 'Accept button text', TPLIS_CL_DOMAIN ),
					'type'		 => 'text',
					'default'	 => __( 'I accept cookies', TPLIS_CL_DOMAIN ),
				),
				array(
					'name'		 => 'hide_button_refuse',
					'label'		 => __( 'Refuse button', TPLIS_CL_DOMAIN ),
					'type'		 => 'radio',
					'options'	 => array(
						'hide'	 => __( 'Hide', TPLIS_CL_DOMAIN ),
						'show'	 => __( 'Show', TPLIS_CL_DOMAIN )
					),
					'default'	 => 'hide',
				),
				array(
					'name'		 => 'button_refuse_text',
					'label'		 => __( 'Refuse button text', TPLIS_CL_DOMAIN ),
					'type'		 => 'text',
					'default'	 => __( 'I refuse cookies', TPLIS_CL_DOMAIN ),
				),
				array(
					'name'		 => 'redirect_refuse',
					'label'		 => __( 'Redirect URL aftre refuse', TPLIS_CL_DOMAIN ),
					'type'		 => 'text',
					'desc'		 => __( 'Redirect the user to following URL after clicking a refuse button.', TPLIS_CL_DOMAIN ),
					'default'	 => __( 'https://www.google.pl', TPLIS_CL_DOMAIN ),
				)
			) ),
			'tplis_cl_appearance'	 => apply_filters( 'tplis_cl_appearance_settings', array(
				array(
					'name'		 => 'ver_position',
					'label'		 => __( 'Vertical position', TPLIS_CL_DOMAIN ),
					'type'		 => 'select',
					'options'	 => array(
						'top'	 => __( 'Top', TPLIS_CL_DOMAIN ),
						'bottom' => __( 'Bottom', TPLIS_CL_DOMAIN ),
					),
					'default'	 => 'bottom',
				),
				array(
					'name'		 => 'z_index',
					'label'		 => __( 'Z-index value', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'size'		 => 'small',
					'default'	 => 9000,
				),
				array(
					'name'		 => 'layout_type',
					'label'		 => __( 'Layout', TPLIS_CL_DOMAIN ),
					'type'		 => 'select',
					'options'	 => array(
						'layout-1'	 => __( 'Layout 1', TPLIS_CL_DOMAIN ),
						'layout-2'	 => __( 'Layout 2', TPLIS_CL_DOMAIN ),
						'layout-3'	 => __( 'Layout 3', TPLIS_CL_DOMAIN ),
						'layout-4'	 => __( 'Layout 4', TPLIS_CL_DOMAIN ),
					),
					'default'	 => 'layout-3',
				),
				array(
					'name'	 => 'fonts_head',
					'label'	 => '<h3>' . __( 'Fonts', TPLIS_CL_DOMAIN ) . '</h3>',
					'type'	 => 'head',
				),
				array(
					'name'		 => 'fonts',
					'label'		 => __( 'Fonts', TPLIS_CL_DOMAIN ),
					'type'		 => 'select',
					'options'	 => tplis_cl_fonts_options(),
					'default'	 => 'opensans',
				),
				array(
					'name'		 => 'character_sets',
					'label'		 => __( 'Character sets', TPLIS_CL_DOMAIN ),
					'type'		 => 'select',
					'options'	 => array(
						'latin'		 => __( 'Latin', TPLIS_CL_DOMAIN ),
						'latin-ext'	 => __( 'Latin Extended', TPLIS_CL_DOMAIN ),
						'greek-ext'	 => __( 'Greek Extended', TPLIS_CL_DOMAIN ),
					),
					'default'	 => 'latin-ext',
				),
				array(
					'name'	 => 'colors_head',
					'label'	 => '<h3>' . __( 'Colors', TPLIS_CL_DOMAIN ) . '</h3>',
					'type'	 => 'head',
				),
				array(
					'name'		 => 'bg_color',
					'label'		 => __( 'Background color', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#FFFFFF'
				),
				array(
					'name'		 => 'bg_opacity',
					'label'		 => __( 'Background opacity (%)', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'size'		 => 'small',
					'desc'		 => '%  - ' . __( 'Range from 0 to 100', TPLIS_CL_DOMAIN ),
					'default'	 => '100'
				),
				array(
					'name'		 => 'text_color',
					'label'		 => __( 'Text color', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#333333'
				),
				array(
					'name'		 => 'bg_btn_accept',
					'label'		 => __( 'Button accept background', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#1D1D1D'
				),
				array(
					'name'		 => 'color_btn_accept',
					'label'		 => __( 'Button accept text', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#FFFFFF'
				),
				array(
					'name'		 => 'bg_btn_refuse',
					'label'		 => __( 'Button refuse background', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#3B3939'
				),
				array(
					'name'		 => 'color_btn_refuse',
					'label'		 => __( 'Button refuse text', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#FFFFFF'
				),
				array(
					'name'	 => 'separated_line_head',
					'label'	 => '<h3>' . __( 'Separated line', TPLIS_CL_DOMAIN ) . '</h3>',
					'type'	 => 'head',
				),
				array(
					'name'		 => 'separated_line',
					'label'		 => __( 'Separated line', TPLIS_CL_DOMAIN ),
					'type'		 => 'radio',
					'options'	 => array(
						'hide'	 => __( 'Hide', TPLIS_CL_DOMAIN ),
						'show'	 => __( 'Show', TPLIS_CL_DOMAIN )
					),
					'desc'		 => __( 'Separated line allows to separate cookies bar from the rest of the website content', TPLIS_CL_DOMAIN ),
					'default'	 => 'show',
				),
				array(
					'name'		 => 'separated_line_size',
					'label'		 => __( 'Size', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'size'		 => 'small',
					'desc'		 => __( 'px. Separated line size in pixels', TPLIS_CL_DOMAIN ),
					'default'	 => '1'
				),
				array(
					'name'		 => 'separated_line_color',
					'label'		 => __( 'Color', TPLIS_CL_DOMAIN ),
					'type'		 => 'color',
					'default'	 => '#3B3939'
				)
			) ),
			'tplis_cl_effects'		 => apply_filters( 'tplis_cl_effects_settings', array(
				array(
					'name'		 => 'effect',
					'label'		 => __( 'Effect', TPLIS_CL_DOMAIN ),
					'type'		 => 'select',
					'desc'		 => __( 'The effect on appearance and disappearance of cookie bar', TPLIS_CL_DOMAIN ),
					'options'	 => array(
						'none'	 => __( 'None', TPLIS_CL_DOMAIN ),
						'slide'	 => __( 'Slide', TPLIS_CL_DOMAIN ),
						'fade'	 => __( 'Fade', TPLIS_CL_DOMAIN ),
					),
					'default'	 => 'slide',
				),
				array(
					'name'		 => 'effect_duration',
					'label'		 => __( 'Effect duration', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'desc'		 => __( 'In milliseconds', TPLIS_CL_DOMAIN ),
					'size'		 => 'small',
					'default'	 => 700,
				),
				array(
					'name'		 => 'start_timeout',
					'label'		 => __( 'Start timeout', TPLIS_CL_DOMAIN ),
					'desc'		 => __( 'Show cookie bar x milliseconds after page load. Set 0 to disable timeout.', TPLIS_CL_DOMAIN ),
					'type'		 => 'number',
					'size'		 => 'small',
					'default'	 => 500,
				)
			) )
		);


		return $settings_fields;
	}

	/*
	 * Print optin value
	 * 
	 * @param string $option_key
	 * @param string $default default value if option not exist
	 * 
	 * @return string
	 */

	public function get_opt( $option_key, $default = '' ) {

		$value = '';

		if ( is_string( $option_key ) && !empty( $option_key ) ) {

			if ( array_key_exists( $option_key, $this->opt ) ) {
				$value = $this->opt[ $option_key ];
			} else {

				// Catch default
				foreach ( $this->settings_fields() as $section ) {
					foreach ( $section as $field ) {
						if ( $field[ 'name' ] === $option_key && isset( $field[ 'default' ] ) ) {
							$value = $field[ 'default' ];
						}
					}
				}
			}
		}

		if ( empty( $value ) && !empty( $default ) ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Handles output of the settings
	 */
	public static function output() {

		$settings = TPLIS_CL()->settings->settings_api;

		include_once TPLIS_CL_DIR . 'includes/admin/views/settings.php';
	}

}

/*
 * Customize WYSWIG on the setting pages
 */

if ( is_admin() && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'tplis_cl_settings' ) {

	add_filter( 'mce_buttons', 'tplis_cl_settings_tinymce_buttons_first' );
	add_filter( 'mce_buttons_2', 'tplis_cl_settings_tinymce_buttons_sec' );
}

function tplis_cl_settings_tinymce_buttons_sec( $buttons ) {
	$buttons = array();
	return $buttons;
}

function tplis_cl_settings_tinymce_buttons_first( $buttons ) {

	$buttons = array(
		'bold', 'italic', 'link'
	);


	return $buttons;
}

/*
 * Flush cache on disable option
 * 
 * @param string value
 */

function tplis_cl_flush_cache_on_disable_option( $value ) {

	if ( $value !== 'on' ) {

		TPLIS_CL_Cache::clear_cache();
	}

	return $value;
}

/*
 * Receive flush cache action
 */
add_action( 'admin_init', 'tplis_cl_receive_flush_cache_action' );

function tplis_cl_receive_flush_cache_action() {
	if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'tplis_cl_settings' && isset( $_GET[ 'tplis-cl-fc' ] ) ) {

		if ( wp_verify_nonce( $_GET[ 'tplis-cl-fc' ], TPLIS_CL_Cache::FLUSH_CACHE_NONCE ) ) {
			TPLIS_CL_Cache::clear_cache();
		}

		wp_redirect( admin_url( 'options-general.php?page=tplis_cl_settings' ) );
	}
}

/*
 * Hide WP Settings API head 
 */
add_action( 'admin_head', 'tplis_cl_hide_api_settings_head' );

function tplis_cl_hide_api_settings_head() {
	?>
	<style>
		.tplis_cl_settings-group > h2{
			display: none;
		}
		.tplis-cl-admin-head {
			margin: 30px 0 20px 5px;
		}
		.tplis-cl-admin-head h1 {
			display: inline-block;
			margin: 0 20px 0 0;
			vertical-align: middle;
			padding: 0;
		}
		.wp-core-ui .button.tplis-cl-button-preview {
			vertical-align: middle;
		}
		.tplis-cl-button-preview .dashicons {
			height: 22px;
			vertical-align: middle;
			margin-right: 5px;
		}
		.form-table td p.tplis_cl_settings-description-field {
			color: #777;
			display: inline-block;
			font-size: 12px;
			margin: 0 0 0 10px;
			vertical-align: middle;
		}
	</style>
	<?php

}

/*
 * Show/hide
 */
if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'tplis_cl_settings' ) {
	add_action( 'admin_head', 'tplis_cl_show_hide_options' );
}

function tplis_cl_show_hide_options() {
	?>
	<script type="text/javascript">
	    jQuery( function ( $ ) {

	        $( document ).ready( function () {
	            var refuse = $( '.tplis_cl_settings-hide_button_refuse' ),
	                refuse_val = $( '.tplis_cl_settings-hide_button_refuse:checked' ).val(),
	                separate_line = $( '.tplis_cl_settings-separated_line' ),
	                separate_line_val = $( '.tplis_cl_settings-separated_line:checked' ).val();



	            // Refuse show/hide
	            if ( refuse_val === 'hide' ) {
	                $( '.tplis_cl_settings-button_refuse_text' ).parent().parent().hide();
	                $( '.tplis_cl_settings-redirect_refuse' ).parent().parent().hide();
	            }

	            $( refuse ).on( 'change', function () {
	                if ( $( this ).val() === 'hide' ) {
	                    $( '.tplis_cl_settings-button_refuse_text' ).parent().parent().fadeOut();
	                    $( '.tplis_cl_settings-redirect_refuse' ).parent().parent().fadeOut();
	                } else {
	                    $( '.tplis_cl_settings-button_refuse_text' ).parent().parent().fadeIn();
	                    $( '.tplis_cl_settings-redirect_refuse' ).parent().parent().fadeIn();
	                }
	            } );

	            // Separate line
	            if ( separate_line_val === 'hide' ) {
	                $( '.tplis_cl_settings-separated_line_size' ).parent().parent().hide();
	                $( '.tplis_cl_settings-separated_line_color' ).parent().parent().parent().parent().hide();
	            }

	            $( separate_line ).on( 'change', function () {
	                if ( $( this ).val() === 'hide' ) {
	                    $( '.tplis_cl_settings-separated_line_size' ).parent().parent().fadeOut();
	                    $( '.tplis_cl_settings-separated_line_color' ).parent().parent().parent().parent().fadeOut();
	                } else {
	                    $( '.tplis_cl_settings-separated_line_size' ).parent().parent().fadeIn();
	                    $( '.tplis_cl_settings-separated_line_color' ).parent().parent().parent().parent().fadeIn();
	                }
	            } );

	        } );

	    } );
	</script>
	<?php

}

/**
 * Returns default fonts
 */
function tplis_cl_fonts_options() {
	return array(
		'inherit'	 => __( 'Inherit from theme', TPLIS_CL_DOMAIN ),
		'arial'		 => 'Arial',
		'arimo'		 => 'Arimo',
		'cabin'		 => 'Cabin',
		'courier'	 => 'Courier New',
		'georgia'	 => 'Georgia',
		'helvetica'	 => 'Helvetica',
		'lato'		 => 'Lato',
		'lucida'	 => 'Lucida Sans Unicode',
		'montserrat' => 'Montserrat',
		'opensans'	 => 'Open Sans',
		'pacifico'	 => 'Pacifico',
		'palatino'	 => 'Palatino Linotype',
		'pt_sans'	 => 'PT Sans',
		'raleway'	 => 'Raleway',
		'source'	 => 'Source Sans Pro',
		'tahoma'	 => 'Tahoma',
		'times'		 => 'Times New Roman',
		'trebuchet'	 => 'Trebuchet MS',
		'ubuntu'	 => 'Ubuntu',
		'verdana'	 => 'Verdana'
	);
}
