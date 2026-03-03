/**
 * Adds language attributes to container blocks
 */

import './index.scss';

import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { TextControl, SelectControl, PanelBody } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';


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
						title={ __( 'Block Language', 'lang-attribute-blocks' ) }
						initialOpen={ true }
					>
						<TextControl
							label={ __( 'Language Code', 'lang-attribute-blocks' ) }
							value={ lang }
							onChange={ ( value ) => setAttributes( { lang: value } ) }
							placeholder={ window.nakedCatPluginsLangAttributeBlocks?.placeholderText || 'en (default website language)' }
							help={ __( "Valid language code for this block, like “fr” or “pt-PT”, if different from the website's or page's main language (shown as a placeholder)", 'lang-attribute-blocks' ) }
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

function addLangAndDirAttributes( settings, name ) {
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

// Add a class when the block has any lang attribute applied
const withLangAttr = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		// Only for supported blocks
		if (
			window.nakedCatPluginsLangAttributeBlocks
			&&
			window.nakedCatPluginsLangAttributeBlocks.supportedBlocks
			&& 
			! window.nakedCatPluginsLangAttributeBlocks.supportedBlocks.includes( props.block.name )
		) {
			return <BlockListBlock { ...props } />;
		}

		// Check if the block has a lang attribute set
		const hasLangAttribute = props.block.attributes.lang && props.block.attributes.lang.trim() !== '';

		if ( ! hasLangAttribute ) {
			return <BlockListBlock { ...props } />;
		}

		return <BlockListBlock { ...props } className={ 'naked-cat-plugins-has-lang-attr' } />
	}
}, 'withLangAttr' );

// Register the filters - Register lang and dir attributes
addFilter(
	'blocks.registerBlockType',
	'lang-attribute-blocks/add-lang-and-dir-attributes',
	addLangAndDirAttributes
);

// Register the filters - Only register the highlighting filter if the setting is enabled
if ( 
	window.nakedCatPluginsLangAttributeBlocks 
	&& 
	window.nakedCatPluginsLangAttributeBlocks.highlightEnabled 
) {
	addFilter(
		'editor.BlockListBlock',
		'lang-attribute-blocks/add-lang-and-dir-attributes',
		withLangAttr
	);
}

/**
 * Page-level language controls in the Document Settings panel
 */
const PageLanguageControls = () => {
	const postType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	const pageLang = meta?._nakedcatplugins_page_lang ?? '';
	const pageDir = meta?._nakedcatplugins_page_dir ?? 'ltr';

	return (
		<PluginDocumentSettingPanel
			name="nakedcatplugins-page-lang-panel"
			title={ __( 'Page Language', 'lang-attribute-blocks' ) }
		>
			<TextControl
				label={ __( 'Language Code', 'lang-attribute-blocks' ) }
				value={ pageLang }
				onChange={ ( value ) => setMeta( { ...meta, _nakedcatplugins_page_lang: value } ) }
				placeholder={ window.nakedCatPluginsLangAttributeBlocks?.placeholderText || 'en (default website language)' }
				help={ __( "Valid language code for this page/post, like “fr” or “pt-PT”, if different from the website's main language (shown as a placeholder) - This overrides the HTML language attribute", 'lang-attribute-blocks' ) }
			/>
			<SelectControl
				label={ __( 'Text Direction', 'lang-attribute-blocks' ) }
				value={ pageDir }
				options={[
					{ label: __( 'Left to right', 'lang-attribute-blocks' ), value: 'ltr' },
					{ label: __( 'Right to left', 'lang-attribute-blocks' ), value: 'rtl' },
				]}
				onChange={ ( value ) => setMeta( { ...meta, _nakedcatplugins_page_dir: value } ) }
			/>
		</PluginDocumentSettingPanel>
	);
};

// Register the plugin to show page-level language controls in the Document panel
registerPlugin( 'nakedcatplugins-page-lang-controls', {
	render: PageLanguageControls,
} );