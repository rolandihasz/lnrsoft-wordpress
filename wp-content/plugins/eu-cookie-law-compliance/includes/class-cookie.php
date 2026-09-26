<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class TPLIS_CL_Cookie {

	/**
	 * Cookie name
	 *
	 * @var string
	 */
	private $cookie_name;

	/**
	 * Global JS variable stores options
	 *
	 * @var string
	 */
	private $options_var = 'tplis_cl_options';

	/**
	 * Global JS variable stores options updater
	 *
	 * @var string
	 */
	private $updater_var = 'tplis_cl_updater_options';

	/**
	 * Expiry days
	 *
	 * @var int
	 */
	private $expiry_days = 365;

	/**
	 * Html to output
	 *
	 * @var string
	 */
	private $html;
	
	/*
	 * Redirect URL
	 * after refuse
	 */
	private $redirect_url;
	
	/*
	 * Effect on the cookie bar
	 * @var array
	 */
	public $effect = array();

	/**
	 * Position of the cookie bar
	 *
	 * @var string
	 */
	public $position;

	/*
	 * Is preview
	 * @var bool
	 */
	public $is_preview = false;

	function __construct( $args = array() ) {

		// Set preview variable
		if ( isset( $args[ 'is_preview' ] ) && $args[ 'is_preview' ] === true ) {
			$this->is_preview = true;
		}

		// Set cookie name
		if ( isset( $args[ 'cookie_name' ] ) && !empty( $args[ 'cookie_name' ] ) ) {
			$this->cookie_name = sanitize_title( $args[ 'cookie_name' ] );
		} else {
			$this->cookie_name = TPLIS_CL_COOKIE_NAME;
		}

		// Set cookie name
		if ( isset( $args[ 'expiry_days' ] ) && !empty( $args[ 'expiry_days' ] ) ) {
			$this->expiry_days = absint( $args[ 'expiry_days' ] );
		}

		// Set html to output
		if ( isset( $args[ 'html' ] ) && !empty( $args[ 'html' ] ) ) {
			$this->html = wp_slash( $args[ 'html' ] );
		}

		// Position of the cookie bar
		if ( isset( $args[ 'css' ][ 'ver_position' ] ) && !empty( $args[ 'css' ][ 'ver_position' ] ) ) {
			$this->ver_position = sanitize_title( $args[ 'css' ][ 'ver_position' ] );
		}

		// Set effect
		$this->effect[ 'type' ]			 = sanitize_title( TPLIS_CL()->settings->get_opt( 'effect' ) );
		$this->effect[ 'duration' ]		 = absint( TPLIS_CL()->settings->get_opt( 'effect_duration' ) );
		$this->effect[ 'start_timeout' ] = absint( TPLIS_CL()->settings->get_opt( 'start_timeout' ) );
		
		$this->redirect_url = esc_url(TPLIS_CL()->settings->get_opt( 'redirect_refuse' ));
		
	}

	/*
	 * Store 
	 */

	public function print_js() {
		
		ob_start();
		?>
		<script type="text/javascript">
		    ( function () {
		// Stop from running again, if accidently included more than once.
		        if ( window.hasPolisClConsent )
		            return;
		        window.hasPolisClConsent = true;

		// No point going further if they've already dismissed.
		<?php if ( !$this->is_preview ): ?>
			        if ( document.cookie.indexOf( '<?php echo $this->cookie_name; ?>' ) > -1 || ( window.navigator && window.navigator.CookiesOK ) ) {
			            return;
			        }
		<?php endif; ?>

		// IE8...
		        if ( typeof String.prototype.trim !== 'function' ) {
		            String.prototype.trim = function () {
		                return this.replace( /^\s+|\s+$/g, '' );
		            };
		        }

		        /*
		         Helper methods
		         */
		        var Util = {
		            isArray: function ( obj ) {
		                var proto = Object.prototype.toString.call( obj );
		                return proto == '[object Array]';
		            },
		            isObject: function ( obj ) {
		                return Object.prototype.toString.call( obj ) == '[object Object]';
		            },
		            each: function ( arr, callback, /* optional: */context, force ) {
		                if ( Util.isObject( arr ) && !force ) {
		                    for ( var key in arr ) {
		                        if ( arr.hasOwnProperty( key ) ) {
		                            callback.call( context, arr[key], key, arr );
		                        }
		                    }
		                } else {
		                    for ( var i = 0, ii = arr.length; i < ii; i++ ) {
		                        callback.call( context, arr[i], i, arr );
		                    }
		                }
		            },
		            merge: function ( obj1, obj2 ) {
		                if ( !obj1 )
		                    return;
		                Util.each( obj2, function ( val, key ) {
		                    if ( Util.isObject( val ) && Util.isObject( obj1[key] ) ) {
		                        Util.merge( obj1[key], val );
		                    } else {
		                        obj1[key] = val;
		                    }
		                } )
		            },
		            bind: function ( func, context ) {
		                return function () {
		                    return func.apply( context, arguments );
		                };
		            },
		            /*
		             find a property based on a . separated path.
		             i.e. queryObject({details: {name: 'Adam'}}, 'details.name') // -> 'Adam'
		             returns null if not found
		             */
		            queryObject: function ( object, query ) {
		                var queryPart;
		                var i = 0;
		                var head = object;
		                query = query.split( '.' );
		                while ( ( queryPart = query[i++] ) && head.hasOwnProperty( queryPart ) && ( head = head[queryPart] ) ) {
		                    if ( i === query.length )
		                        return head;
		                }
		                return null;
		            },
		            setCookie: function ( name, value, expiryDays, domain, path ) {
		                expiryDays = expiryDays || 365;

		                var exdate = new Date();
		                exdate.setDate( exdate.getDate() + expiryDays );

		                var cookie = [
		                    name + '=' + value,
		                    'expires=' + exdate.toUTCString(),
		                    'path=' + path || '/'
		                ];

		                if ( domain ) {
		                    cookie.push(
		                        'domain=' + domain
		                        );
		                }

		                document.cookie = cookie.join( ';' );
		            },
		            addEventListener: function ( el, event, eventListener ) {
		                if ( el.addEventListener ) {
		                    el.addEventListener( event, eventListener );
		                } else {
		                    el.attachEvent( 'on' + event, eventListener );
		                }
		            }
		        };

		        var DomBuilder = ( function () {
		            /*
		             Shim to make addEventListener work correctly with IE.
		             */
		            var addEventListener = function ( el, event, eventListener ) {
		                // Add multiple event listeners at once if array is passed.
		                if ( Util.isArray( event ) ) {
		                    return Util.each( event, function ( ev ) {
		                        addEventListener( el, ev, eventListener );
		                    } );
		                }

		                if ( el.addEventListener ) {
		                    el.addEventListener( event, eventListener );
		                } else {
		                    el.attachEvent( 'on' + event, eventListener );
		                }
		            };

		            /*
		             Turn a string of html into DOM
		             */
		            var buildDom = function ( htmlStr ) {
		                var container = document.createElement( 'div' );
		                container.innerHTML = htmlStr;

		                return container.children[0];
		            };

		            var applyToElementsWithAttribute = function ( dom, attribute, func ) {
		                var els = dom.parentNode.querySelectorAll( '[' + attribute + ']' );
		                Util.each( els, function ( element ) {
		                    var attributeVal = element.getAttribute( attribute );
		                    func( element, attributeVal );
		                }, window, true );
		            };

		            /*
		             Parse event attributes in dom and set listeners to their matching scope methods
		             */
		            var applyEvents = function ( dom, scope ) {
		                applyToElementsWithAttribute( dom, '<?php echo TPLIS_CL_EVENT_ATTR; ?>', function ( element, attributeVal ) {
		                    var parts = attributeVal.split( ':' );
		                    var listener = Util.queryObject( scope, parts[1] );
		                    addEventListener( element, parts[0], Util.bind( listener, scope ) );
		                } );
		            };

		            return {
		                build: function ( htmlStr, scope ) {
		                    var dom = buildDom( htmlStr );
		                    applyEvents( dom, scope );

		                    return dom;
		                }
		            };
		        } )();


		        /*
		         Plugin
		         */
		        var PolisClConsent = {
		            options: {
		                container: null, // selector
		                domain: null, // default to current domain.
		                path: '/',
		                expiryDays: <?php echo $this->expiry_days; ?>,
		                html: '<?php echo $this->html; ?>'
		            },
		            init: function () {
		                var options = window['<?php echo $this->options_var; ?>'];
		                if ( options )
		                    this.setOptions( options );

		                this.setContainer();

		                // Calls render when theme is loaded.
		                this.render();
		            },
		            setOptionsOnTheFly: function ( options ) {
		                this.setOptions( options );
		                this.render();
		            },
		            setOptions: function ( options ) {
		                Util.merge( this.options, options );
		            },
		            setContainer: function () {
		                this.container = document.body;

		                // Add class to container classes so we can specify css for IE8 only.
		                this.containerClasses = '';
		                if ( navigator.appVersion.indexOf( 'MSIE 8' ) > -1 ) {
		                    this.containerClasses += ' cc_ie8'
		                }
		            },
		            render: function () {

		                var that = this,
		                    container = this.container,
		                    element = this.element,
		                    options = this.options;

		                // remove current element (if we've already rendered)
		                if ( element && element.parentNode ) {
		                    element.parentNode.removeChild( element );
		                    delete element;
		                }

		                this.element = DomBuilder.build( options.html, that );
		                element = this.element;

		<?php echo $this->effect[ 'start_timeout' ] > 0 ? 'setTimeout( function () {' : ''; ?>

		                if ( !container.firstChild ) {
		                    container.appendChild( element );
		                } else {
		                    container.insertBefore( element, container.firstChild );
		                }

		                that.addWithEffect( element );
		<?php echo $this->effect[ 'start_timeout' ] > 0 ? '}, ' . $this->effect[ 'start_timeout' ] . ' );' : ''; ?>



		            },
		            accept: function ( evt ) {
		                evt.preventDefault && evt.preventDefault();
		                evt.returnValue = false;
		                this.setDismissedCookie();
		                this.removeWithEffect( this.element );
		            },
		            refuse: function ( evt ) {
						evt.preventDefault && evt.preventDefault();
		                evt.returnValue = false;
		                location.href = '<?php echo $this->redirect_url; ?>';
		            },
		                setDismissedCookie: function () {
		<?php if ( !$this->is_preview ): ?>
			                    Util.setCookie( '<?php echo $this->cookie_name; ?>', 'yes', this.options.expiryDays, this.options.domain, this.options.path );
		<?php endif; ?>
		                },
		            addWithEffect: function ( element ) {
		<?php echo $this->inject_js_effect( 'start' ); ?>
		            },
		            removeWithEffect: function ( element ) {
		<?php echo $this->inject_js_effect( 'finish' ); ?>
		            }
		        };

		        var init;
		        var initialized = false;
		        ( init = function () {
		            if ( !initialized && document.readyState == 'complete' ) {
		                PolisClConsent.init();
		                initialized = true;
		                window['<?php echo $this->updater_var; ?>'] = Util.bind( PolisClConsent.setOptionsOnTheFly, PolisClConsent );
		            }
		        } )();

		        Util.addEventListener( document, 'readystatechange', init );

		    } )();

		</script>
		<?php
		
		$js = ob_get_contents();
		ob_end_clean();

		echo tplis_cl_minify_js( $js );
	}

	/*
	 * Print fragment of the JS code with specific effect on cookie bar
	 * 
	 * @param $dest destination - start or finish
	 */

	private function inject_js_effect( $dest ) {

		$js = '';

		// Slide
		if ( $this->effect[ 'type' ] === 'slide' ) {

			if ( $dest === 'start' ) {
				$js = "jQuery( element ).css( '" . $this->ver_position . "', '-100%' );";
				$js .= "jQuery( element ).animate( {" . $this->ver_position . ": 0}, " . $this->effect[ 'duration' ] . " )";
			}

			if ( $dest === 'finish' ) {
				$js .= "jQuery( element ).animate( {" . $this->ver_position . ": '-100%'}, {duration: " . $this->effect[ 'duration' ] . ",complete: function () {jQuery(element).remove();}} )";
			}
		}
		
		// Fade
		if ( $this->effect[ 'type' ] === 'fade' ) {

			if ( $dest === 'start' ) {
				$js = "jQuery( element ).css( 'opacity', 0 );";
				$js .= "jQuery( element ).animate( { opacity:1}, " . $this->effect[ 'duration' ] . " );";
			}

			if ( $dest === 'finish' ) {
				$js .= "jQuery( element ).animate( {opacity:0}, {duration: " . $this->effect[ 'duration' ] . ",complete: function () {jQuery(element).remove();}} );";
			}
		}

		return $js;
	}

}
