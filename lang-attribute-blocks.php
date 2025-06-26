<?php
/**
 * Plugin Name:          Language Attribute for Container Blocks
 * Plugin URI:
 * Description:          Add "lang" and "dir" attributes on Group and Columns WordPress Blocks
 * Version:              1.0
 * Author:               Naked Cat Plugins (by Webdados)
 * Author URI:           https://nakedcatplugins.com
 * Text Domain:          lang-attribute-blocks
 * Requires at least:    5.9
 * Tested up to:         6.9
 * Requires PHP:         7.2
 * License:              GPLv3
 **/

namespace NakedCatPlugins\LangAttr;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the WordPress blocks that will support language attributes.
 *
 * This constant stores an array of block type identifiers that the plugin will
 * extend with language and direction attributes. Currently supports:
 *
 * - 'core/group': The standard WordPress Group block (and all it's variations)
 * - 'core/column': The Column block used within the Columns block
 *
 * This constant is used throughout the plugin to determine which blocks
 * should receive the language attribute functionality.
 *
 * @since 1.0
 * @type array
 */
define(
	'NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS',
	array(
		'core/group',
		'core/columns',
		'core/column',
	)
);

/**
 * Enqueues JavaScript and CSS assets for the WordPress block editor.
 *
 * This function loads the necessary scripts and styles for the Language Attribute Blocks
 * plugin to operate within the Gutenberg editor. It registers:
 *
 * 1. The main plugin JavaScript file with appropriate dependencies
 * 2. Translation support for the script
 * 3. The plugin's CSS styles
 *
 * Each asset is versioned using the file's last modification time for cache busting.
 *
 * @since 1.0
 * @hook enqueue_block_editor_assets
 * @return void
 */
function enqueue_block_editor_assets() {

	// Enqueue the main JavaScript file for the block editor
	wp_enqueue_script(
		'nakedcatplugins-lang-attribute-blocks-script',
		plugins_url( 'build/index.js', __FILE__ ),
		array( 'wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-block-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
		true
	);

	// Localize script to pass the blocks array to JavaScript
	wp_localize_script(
		'nakedcatplugins-lang-attribute-blocks-script',
		'nakedCatPluginsLangAttributeBlocks',
		array(
			'supportedBlocks' => NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS,
			'siteLanguage'    => get_bloginfo( 'language' ), // This will get the site language (e.g., 'en-US')
		)
	);

	// Set script translations
	wp_set_script_translations( 'lang-attribute-blocks-script', 'lang-attribute-blocks' );

	// Enqueue the CSS styles for the block editor
	wp_enqueue_style(
		'nakedcatplugins-lang-attribute-blocks-style',
		plugins_url( 'build/index.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' )
	);
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );

/**
 * Register block attributes for language settings.
 *
 * This function adds custom 'lang' and 'dir' attributes to specified WordPress blocks
 * defined in the NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS constant. These attributes allow users
 * to set language-specific properties on block elements:
 *
 * - 'lang': Specifies the language code (e.g., 'en', 'fr', 'es')
 * - 'dir': Sets text direction, defaulting to 'ltr' (left-to-right)
 *
 * @since 1.0
 * @hook register_block_type_args
 * @param array  $args       The arguments being passed to register_block_type.
 * @param string $block_type The block type being registered.
 * @return array Modified arguments with added language attributes if applicable.
 */
function register_block_attributes( $args, $block_type ) {
	if ( in_array( $block_type, NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS, true ) ) {
		$args['attributes']['lang'] = array(
			'type'    => 'string',
			'default' => '',
		);
		$args['attributes']['dir']  = array(
			'type'    => 'string',
			'default' => 'ltr',
		);
	}
	return $args;
}
add_filter( 'register_block_type_args', __NAMESPACE__ . '\register_block_attributes', 10, 2 );

/**
 * Applies language attributes to block content in the frontend.
 *
 * This function processes blocks with language settings and inserts the appropriate
 * 'lang' and 'dir' HTML attributes to their markup. It:
 *
 * 1. Checks if a block has a language attribute specified
 * 2. Sanitizes the language code and direction values
 * 3. Uses WP_HTML_Tag_Processor to safely modify the HTML
 * 4. Targets the first div element in the block content
 * 5. Returns the modified HTML with language attributes applied
 *
 * @since 1.0
 * @hook render_block_{block_name}
 * @param string $block_content The HTML content of the block.
 * @param array  $block         The block data including attributes.
 * @return string Modified block content with language attributes applied.
 */
function process_blocks( $block_content, $block ) {
	if ( isset( $block['attrs']['lang'] ) && ! empty( $block['attrs']['lang'] ) ) {
		$lang          = esc_attr( $block['attrs']['lang'] );
		$dir           = isset( $block['attrs']['dir'] ) ? esc_attr( $block['attrs']['dir'] ) : 'ltr';
		$tag_processor = new \WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag( 'div' );
		$tag_processor->set_attribute( 'lang', $lang );
		$tag_processor->set_attribute( 'dir', $dir );
		return $tag_processor->get_updated_html();
	}
	return $block_content;
}
foreach ( NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS as $block_name ) {
	add_filter( 'render_block_' . $block_name, __NAMESPACE__ . '\process_blocks', 10, 2 );
}


/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
