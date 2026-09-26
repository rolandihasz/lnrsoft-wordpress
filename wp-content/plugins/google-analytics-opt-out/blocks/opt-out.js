const {__} = wp.i18n;

const {registerBlockType, createBlock} = wp.blocks;

const {TextControl} = wp.components;

export default registerBlockType(
    'gaoop/opt-out-block',
    {
      title:      __( 'Analytics Opt Out', 'google-analytics-opt-out' ),
      category:   'widgets',
      icon:       <svg width="18" height="20"
                       xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M15.61 16.086c.093.094.14.2.14.316 0 .118-.047.211-.14.282a2.763 2.763 0 0 1-2.075.878c-.82 0-1.512-.292-2.074-.878L7.383 12.64l-.88.879c.165.398.247.808.247 1.23 0 .938-.328 1.734-.984 2.39-.657.657-1.454.985-2.391.985-.938 0-1.734-.328-2.39-.984C.327 16.484 0 15.688 0 14.75c0-.937.328-1.734.984-2.39.657-.657 1.453-.985 2.391-.985.164 0 .316.012.457.035l1.16-1.16-1.16-1.16c-.14.023-.293.035-.457.035-.938 0-1.734-.328-2.39-.984C.327 7.484 0 6.688 0 5.75c0-.937.328-1.734.984-2.39.657-.657 1.453-.985 2.391-.985.937 0 1.734.328 2.39.984.657.657.985 1.454.985 2.391 0 .422-.082.832-.246 1.23l.879.88 4.078-4.044a2.763 2.763 0 0 1 2.074-.878c.82 0 1.512.293 2.074.878.094.07.141.164.141.282a.437.437 0 0 1-.14.316L9.772 10.25l5.836 5.836zM3.374 4.625c-.305 0-.568.111-.791.334a1.081 1.081 0 0 0-.334.791c0 .305.111.568.334.791.223.223.486.334.791.334.305 0 .568-.111.791-.334.223-.223.334-.486.334-.791 0-.305-.111-.568-.334-.791a1.081 1.081 0 0 0-.791-.334zm0 9c-.305 0-.568.111-.791.334a1.081 1.081 0 0 0-.334.791c0 .305.111.568.334.791.223.223.486.334.791.334.305 0 .568-.111.791-.334.223-.223.334-.486.334-.791 0-.305-.111-.568-.334-.791a1.081 1.081 0 0 0-.791-.334zm3.937-3.797a.407.407 0 0 0-.298.123.407.407 0 0 0-.123.299c0 .117.04.217.123.299a.407.407 0 0 0 .298.123.407.407 0 0 0 .3-.123.407.407 0 0 0 .122-.299.407.407 0 0 0-.123-.299.407.407 0 0 0-.299-.123z"
                        fill="#000"/>
                    <path fill="#196EEE" d="M16 0H18V4H16z"/>
                    <path fill="#D9442F" d="M16 4H18V8H16z"/>
                    <path fill="#FFBB04" d="M16 8H18V12H16z"/>
                    <path fill="#176CED" d="M16 12H18V16H16z"/>
                    <path fill="#03A25D" d="M16 16H18V20H16z"/>
                  </svg>,
      keywords:   [
        'gaoop',
        __( 'Analytics Opt Out', 'google-analytics-opt-out' )
      ],
      attributes: {
        content: {
          source:   'text',
          selector: 'a',
          default:  __( 'Click here to opt out', 'google-analytics-opt-out' )
        }
      },

      transforms: {
        to:   [
          {
            type:      'block',
            blocks:    ['core/paragraph'],
            transform: ( {content} ) => {
              return createBlock( 'core/paragraph', {
                content
              } );
            }
          }
        ],
        from: [
          {
            type:      'block',
            blocks:    ['core/paragraph'],
            transform: ( {content} ) => {
              return createBlock( 'gaoop/opt-out-block', {
                content
              } );
            }
          }
        ]
      },

      edit: props => {
        const {isSelected, setAttributes} = props;

        const content = '' === props.attributes.content
            ? __( 'Click here to opt out', 'google-analytics-opt-out' )
            : props.attributes.content;

        return isSelected
            ? <TextControl
                label={__( 'Enter a link text', 'google-analytics-opt-out' )}
                value={content}
                onChange={( value ) => setAttributes( {content: value} )}
            />
            : <a className="gaoop-block" href="#">{content}</a>;

      },
      save: props => {

        const content = '' === props.attributes.content
            ? __( 'Click here to opt out', 'google-analytics-opt-out' )
            : props.attributes.content;

        return (
            <a className="gaoop-block"
               href="javascript:gaoop_analytics_optout();">{content}</a>
        );
      }
    }
);
