( function () {
    // Stop from running again, if accidently included more than once.
    if ( window.hasCookieConsent )
        return;
    window.hasCookieConsent = true;

    var OPTIONS_VARIABLE = 'PolisClConsent_options';
    var OPTIONS_UPDATER = 'update_PolisClConsent_options';
    var DISMISSED_COOKIE = 'PolisClConsent_dismissed';

    // No point going further if they've already dismissed.
    if ( document.cookie.indexOf( DISMISSED_COOKIE ) > -1 || ( window.navigator && window.navigator.CookiesOK ) ) {
        return;
    }

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
         The attribute we store events in.
         */
        var eventAttribute = 'data-tplis-cl-event';

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
         Replace {{variable.name}} with it's property on the scope
         Also supports {{variable.name || another.name || 'string'}}
         */
        var insertReplacements = function ( htmlStr, scope ) {
            return htmlStr.replace( /\{\{(.*?)\}\}/g, function ( _match, sub ) {
                var tokens = sub.split( '||' );
                var value, token;
                while ( token = tokens.shift() ) {
                    token = token.trim();

                    // If string
                    if ( token[0] === '"' )
                        return token.slice( 1, token.length - 1 );

                    // If query matches
                    value = Util.queryObject( scope, token );

                    if ( value )
                        return value;
                }

                return '';
            } );
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
            applyToElementsWithAttribute( dom, eventAttribute, function ( element, attributeVal ) {
                var parts = attributeVal.split( ':' );
                var listener = Util.queryObject( scope, parts[1] );
                addEventListener( element, parts[0], Util.bind( listener, scope ) );
            } );
        };

        return {
            build: function ( htmlStr, scope ) {
                if ( Util.isArray( htmlStr ) )
                    htmlStr = htmlStr.join( '' );

                htmlStr = insertReplacements( htmlStr, scope );
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
            expiryDays: 365,
            html: '<div data-tplis-cl-event="click:dismiss">sss</div>'
        },
        init: function () {
            var options = window[OPTIONS_VARIABLE];
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
            // remove current element (if we've already rendered)
            if ( this.element && this.element.parentNode ) {
                this.element.parentNode.removeChild( this.element );
                delete this.element;
            }

            this.element = DomBuilder.build( this.options.html, this );
            if ( !this.container.firstChild ) {
                this.container.appendChild( this.element );
            } else {
                this.container.insertBefore( this.element, this.container.firstChild );
            }
        },
        dismiss: function ( evt ) {
            evt.preventDefault && evt.preventDefault();
            evt.returnValue = false;
            this.setDismissedCookie();
            this.container.removeChild( this.element );
        },
        setDismissedCookie: function () {
            Util.setCookie( DISMISSED_COOKIE, 'yes', this.options.expiryDays, this.options.domain, this.options.path );
        }
    };

    var init;
    var initialized = false;
    ( init = function () {
        if ( !initialized && document.readyState == 'complete' ) {
            PolisClConsent.init();
            initialized = true;
            window[OPTIONS_UPDATER] = Util.bind( PolisClConsent.setOptionsOnTheFly, PolisClConsent );
        }
    } )();

    Util.addEventListener( document, 'readystatechange', init );

} )();
