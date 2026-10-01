/**
 * QR Code block: editor.
 *
 * @package Reusable_QR_Codes
 */

( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var useRef = element.useRef;
	var useEffect = element.useEffect;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var ToggleControl = components.ToggleControl;
	var TextControl = components.TextControl;
	var Placeholder = components.Placeholder;

	var data = window.rqrcBlockData || { codes: [], color: '#000000', bgColor: '#ffffff', dotStyle: 'square' };

	/**
	 * Find a QR code by ID.
	 *
	 * @param {number} id QR code post ID.
	 * @return {Object|null} Code entry.
	 */
	function findCode( id ) {
		for ( var i = 0; i < data.codes.length; i++ ) {
			if ( data.codes[ i ].id === id ) {
				return data.codes[ i ];
			}
		}
		return null;
	}

	/**
	 * Options for the QR code picker.
	 *
	 * @return {Array} SelectControl options.
	 */
	function codeOptions() {
		return [ { label: __( 'Select a QR code', 'reusable-qr-codes' ), value: 0 } ].concat(
			data.codes.map( function ( code ) {
				return { label: code.title, value: code.id };
			} )
		);
	}

	/**
	 * Live QR code preview.
	 *
	 * @param {Object} props code and size.
	 * @return {Object} Element.
	 */
	function Preview( props ) {
		var ref = useRef( null );

		useEffect( function () {
			var node = ref.current;
			if ( ! node ) {
				return;
			}
			node.innerHTML = '';
			new window.QRCodeStyling( window.rqrcQr.buildOptions( {
				data: props.code.permalink,
				size: props.size,
				color: data.color,
				bgColor: data.bgColor,
				dotStyle: data.dotStyle,
				logoUrl: props.code.logoUrl
			} ) ).append( node );
		}, [ props.code.permalink, props.code.logoUrl, props.size ] );

		return el( 'div', { ref: ref, className: 'rqrc-block-qr' } );
	}

	blocks.registerBlockType( 'reusable-qr-codes/qr-code', {
		edit: function ( props ) {
			var a = props.attributes;
			var code = findCode( a.qrId );
			// One call per render, whichever branch shows (hooks must not be conditional).
				var blockProps = useBlockProps( code ? { style: { width: a.size + 'px' } } : {} );

			var picker = el( SelectControl, {
				label: __( 'QR code', 'reusable-qr-codes' ),
				value: a.qrId,
				options: codeOptions(),
				onChange: function ( value ) {
					props.setAttributes( { qrId: parseInt( value, 10 ) || 0 } );
				}
			} );

			var inspector = el( InspectorControls, {},
				el( PanelBody, { title: __( 'QR code settings', 'reusable-qr-codes' ) },
					picker,
					el( RangeControl, {
						label: __( 'Size (px)', 'reusable-qr-codes' ),
						value: a.size,
						min: 100,
						max: 600,
						onChange: function ( value ) {
							props.setAttributes( { size: value || 200 } );
						}
					} ),
					el( ToggleControl, {
						label: __( 'Show caption', 'reusable-qr-codes' ),
						checked: a.showCaption,
						onChange: function ( value ) {
							props.setAttributes( { showCaption: value } );
						}
					} ),
					a.showCaption && el( TextControl, {
						label: __( 'Caption', 'reusable-qr-codes' ),
						help: __( 'Leave empty to use the QR code title.', 'reusable-qr-codes' ),
						value: a.caption,
						placeholder: code ? code.title : '',
						onChange: function ( value ) {
							props.setAttributes( { caption: value } );
						}
					} ),
					el( ToggleControl, {
						label: __( 'Show download button', 'reusable-qr-codes' ),
						checked: a.showDownload,
						onChange: function ( value ) {
							props.setAttributes( { showDownload: value } );
						}
					} )
				)
			);

			if ( ! code ) {
				return el( 'div', blockProps,
					inspector,
					el( Placeholder, {
						icon: 'screenoptions',
						label: __( 'QR Code', 'reusable-qr-codes' ),
						instructions: data.codes.length
							? __( 'Choose which QR code to show.', 'reusable-qr-codes' )
							: __( 'No published QR codes yet. Create one under QR Codes first.', 'reusable-qr-codes' )
					}, data.codes.length ? picker : null )
				);
			}

			return el( 'figure', blockProps,
				inspector,
				el( Preview, { code: code, size: a.size } ),
				a.showCaption && el( 'figcaption', {}, a.caption || code.title ),
				a.showDownload && el( 'button', { type: 'button', className: 'rqrc-block-download', disabled: true }, __( 'Download QR code', 'reusable-qr-codes' ) )
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
