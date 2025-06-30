/**
 * Adds language attributes to container blocks
 */

import './index.scss';

import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { TextControl, SelectControl, PanelBody } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';


/**
 * Add language attributes to Group block
 */
const addLangAttributesToGroupBlock = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		// Only for supported blocks
		if (
			window.nakedCatPluginsLangAttributeBlocks
			&&
			window.nakedCatPluginsLangAttributeBlocks.supportedBlocks
			&& 
			! window.nakedCatPluginsLangAttributeBlocks.supportedBlocks.includes( props.name )
		) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;

		// Get existing lang and dir attributes or set default values
		const lang = attributes.lang || '';
		const dir = attributes.dir || 'ltr';

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Language Settings', 'lang-attribute-blocks' ) }
						initialOpen={ true }
					>
						<TextControl
							label={ __( 'Language Code', 'lang-attribute-blocks' ) }
							value={ lang }
							onChange={ ( value ) => setAttributes( { lang: value } ) }
							placeholder={ window.nakedCatPluginsLangAttributeBlocks?.siteLanguage || 'en' }
							help={ __( 'Valid language code, like “fr” or “pt-PT”, if different from the website main language (shown as a placeholder)', 'lang-attribute-blocks' ) }
						/>
						<SelectControl
							label={ __( 'Text Direction', 'lang-attribute-blocks' ) }
							value={ dir }
							options={[
								{ label: __( 'Left to right', 'lang-attribute-blocks' ), value: 'ltr' },
								{ label: __( 'Right to left', 'lang-attribute-blocks' ), value: 'rtl' },
							]}
							onChange={ ( value ) => setAttributes( { dir: value } ) }
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'addLangAttributesToGroupBlock' );

// Register the filters
addFilter(
	'editor.BlockEdit',
	'lang-attribute-blocks/add-lang-attributes-to-group-block',
	addLangAttributesToGroupBlock
);

function addListBlockClassName( settings, name ) {
	// Only for supported blocks
	if (
		window.nakedCatPluginsLangAttributeBlocks
		&&
		window.nakedCatPluginsLangAttributeBlocks.supportedBlocks
		&& 
		window.nakedCatPluginsLangAttributeBlocks.supportedBlocks.includes( name )
	) {
		// Add custom attributes for lang and dir
		settings.attributes = {
			...settings.attributes,
			lang: {
				type: 'string',
				default: '',
			},
			dir: {
				type: 'string',
				default: 'ltr',
			},
		};
	}
	return settings;

}

// Register the filters
addFilter(
	'blocks.registerBlockType',
	'lang-attribute-blocks/add-list-block-class-name',
	addListBlockClassName
);