( function( blocks, i18n, element, components ) {
    var el = element.createElement;
    var Placeholder = components.Placeholder;
    var Button = components.Button;
    var TextControl = components.TextControl;
    var __ = i18n.__;
    var dokanShortcodes = window.dokan_shortcodes;

    function getDokanIcon( svgProps ) {
        svgProps = svgProps || {};

        svgProps.xmlns = 'http://www.w3.org/2000/svg';
        svgProps.viewBox = '0 0 58 63';

        var SVG = components.SVG;
        var G = components.G;
        var Path = components.Path;
        var icon = el(
            SVG,
            svgProps,
            el(
                G,
                {
                    stroke: 'none',
                    strokeWidth: '1',
                    fill: 'none',
                    fillRule: 'evenodd',
                },
                el(
                    G,
                    {
                        fillRule: 'nonzero',
                        fill: '#9EA3A8',
                    },
                    el(
                        Path,
                        {
                            d : 'M5.33867702,3.0997123 C5.33867702,3.0997123 40.6568031,0.833255993 40.6568031,27.7724223 C40.6568031,54.7115885 31.3258879,60.1194199 23.1436827,61.8692575 C23.1436827,61.8692575 57.1718639,69.1185847 57.1718639,31.1804393 C57.1718639,-6.75770611 13.7656892,-1.28321423 5.33867702,3.0997123 Z',
                        }
                    ),
                    el(
                        Path,
                        {
                            d : 'M34.0564282,48.9704547 C34.0564282,48.9704547 30.6479606,59.4444826 20.3472329,60.7776922 C10.0465051,62.1109017 8.12571122,57.1530286 0.941565611,57.4946635 C0.941565611,57.4946635 0.357794932,52.5784532 6.1578391,51.8868507 C11.9578833,51.1952482 22.8235504,52.5451229 30.0547743,48.5038314 C30.0547743,48.5038314 34.3294822,46.5206821 35.1674756,45.5624377 L34.0564282,48.9704547 Z',
                        }
                    ),
                    el(
                        Path,
                        {
                            d : 'M4.80198462,4.99953596 L4.80198462,17.9733318 L4.80198462,17.9733318 L4.80198462,50.2869992 C5.1617776,50.2053136 5.52640847,50.1413326 5.89420073,50.0953503 C7.92701701,49.903571 9.97004544,49.8089979 12.0143772,49.8120433 C14.1423155,49.8120433 16.4679825,49.7370502 18.7936496,49.5454014 L18.7936496,34.3134818 C18.7936496,29.2472854 18.426439,24.0727656 18.7936496,19.0149018 C19.186126,15.9594324 21.459175,13.3479115 24.697266,12.232198 C27.2835811,11.3792548 30.1586431,11.546047 32.5970015,12.6904888 C20.9498348,5.04953132 7.86207285,4.89954524 4.80198462,4.99953596 Z',
                        }
                    ),
                ),
            ),
        );

        return icon;
    }

    blocks.registerBlockType( 'dokan/shortcode', {
        title: __( 'Dokan Shortcode', 'dokan' ),
        icon: getDokanIcon(),
        category: 'dokan',

        attributes: {
            editMode: {
                type: 'bool',
                default: true,
            },
            shortcode: {
                type: 'string',
                default: '',
            }
        },

        example: {},

        edit: function( props ) {
            var editMode = props.attributes.editMode;
            var tableRow = '';
            var bottomControls = '';


            function insertShortCode( shortcode ) {
                props.setAttributes( {
                    editMode: false,
                    shortcode: shortcode,
                } );
            };

            if ( editMode ) {
                var shortcodes = Object.keys( dokanShortcodes );

                tableRow = shortcodes.map( function ( shortcode, i ) {
                    var shortcodeTitle = dokanShortcodes[ shortcode ].title;
                    var shortcodeContent = dokanShortcodes[ shortcode ].content;

                    var titleProps = {
                        style: {
                            borderBottom: '1px solid #eee',
                        },
                    };

                    var contentProps = {
                        style: {
                            borderBottom: '1px solid #eee',
                            textAlign: 'right',
                        },
                    };

                    if ( i === 0 ) {
                        titleProps.style.borderTop = '1px solid #eee';
                        contentProps.style.borderTop = '1px solid #eee';
                    }

                    return  el(
                        'tr',
                        {
                            key: shortcode,
                        },
                        el(
                            'td',
                            titleProps,
                            shortcodeTitle,
                        ),
                        el(
                            'td',
                            contentProps,
                            el(
                                Button,
                                {
                                    isSmall: true,
                                    onClick: insertShortCode.bind( this, shortcodeContent ),
                                },
                                __( 'Add Shortcode', 'dokan' ),
                            )
                        ),
                    );
                } );
            } else {
                tableRow = el(
                    'tr',
                    {},
                    el(
                        'td',
                        {},
                        el(
                            TextControl,
                            {
                                value: props.attributes.shortcode,
                                onChange: function ( shortcode ) {
                                    props.setAttributes( {
                                        shortcode: shortcode,
                                    } );
                                },
                            }
                        ),
                    )
                );

                bottomControls = el(
                    Button,
                    {
                        isSmall: true,
                        onClick: function () {
                            props.setAttributes( {
                                editMode: true,
                            } );
                        }
                    },
                    __( 'Change Shortcode', 'dokan' ),
                );
            }

            var content = el(
                Placeholder,
                {
                    icon: getDokanIcon( {
                        style: {
                            width: '20px',
                            height: '20px',
                            marginRight: '5px',
                        }
                    } ),
                    label: __( 'Dokan Shortcode', 'dokan' ),
                },
                el(
                    'table',
                    {},
                    el(
                        'tbody',
                        {},
                        tableRow,
                    ),
                ),
                el(
                    'div',
                    {
                        style: {
                            textAlign: 'center',
                        }
                    },
                    bottomControls
                )
            );

            return content;
        },

        save: function( props ) {
            return props.attributes.shortcode;
        },
    } );
}(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
) );
