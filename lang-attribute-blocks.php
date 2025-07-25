<?php
/**
 * Plugin Name:          Language Attribute for Container Blocks
 * Plugin URI:
 * Description:          Add “lang” and “dir” attributes on Group, Columns, and Cover WordPress Blocks
 * Version:              1.2
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
 * - 'core/columns': The Columns block, which allows for multi-column layouts
 * - 'core/column': The Column block used within the Columns block
 * - 'core/cover': The Cover block, which allows for full-width background images and text
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
		'core/cover',
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
			'supportedBlocks'  => NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_BLOCKS,
			'siteLanguage'     => get_bloginfo( 'language' ), // This will get the site language (e.g., 'en-US'),
			'highlightEnabled' => get_option( 'nakedcatplugins_lang_attr_highlight_blocks', false ),
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
 * Enqueues CSS assets for the frontend.
 *
 * This function loads the plugin's CSS styles on the frontend to provide
 * visual highlighting of blocks with language attributes.
 *
 * @since 1.2
 * @hook wp_enqueue_scripts
 * @return void
 */
function enqueue_frontend_assets() {
	// Enqueue the CSS styles for the frontend
	if ( current_user_can( 'edit_others_posts' ) ) {
		$highlight_enabled = get_option( 'nakedcatplugins_lang_attr_highlight_blocks', false );
		if ( $highlight_enabled ) {
			wp_enqueue_style(
				'nakedcatplugins-lang-attribute-blocks-style',
				plugins_url( 'build/index.css', __FILE__ ),
				array(),
				filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_frontend_assets' );

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

/**
 * Add settings section to Settings > Writing page.
 *
 * This function registers a new settings section for the Language Attribute Blocks
 * plugin on the WordPress Settings > Writing admin page.
 *
 * @since 1.2
 * @hook admin_init
 * @return void
 */
function add_writing_settings() {
	// Register the setting
	register_setting(
		'writing',
		'nakedcatplugins_lang_attr_highlight_blocks',
		array(
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);

	// Add settings section
	add_settings_section(
		'nakedcatplugins_lang_attr_section',
		__( 'Language Attribute for Container Blocks', 'lang-attribute-blocks' ),
		__NAMESPACE__ . '\settings_section_callback',
		'writing'
	);

	// Add settings field
	add_settings_field(
		'nakedcatplugins_lang_attr_highlight_blocks',
		__( 'Highlight blocks with lang attribute', 'lang-attribute-blocks' ),
		__NAMESPACE__ . '\highlight_blocks_field_callback',
		'writing',
		'nakedcatplugins_lang_attr_section'
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\add_writing_settings' );

/**
 * Settings section callback.
 *
 * @since 1.2
 * @return void
 */
function settings_section_callback() {
	echo '<p>' . esc_html__( 'Configure Language Attribute for Container Blocks plugin settings.', 'lang-attribute-blocks' ) . '</p>';
}

/**
 * Highlight blocks field callback.
 *
 * @since 1.2
 * @return void
 */
function highlight_blocks_field_callback() {
	$option = get_option( 'nakedcatplugins_lang_attr_highlight_blocks', false );
	?>
	<label for="nakedcatplugins_lang_attr_highlight_blocks">
		<input type="checkbox" id="nakedcatplugins_lang_attr_highlight_blocks" name="nakedcatplugins_lang_attr_highlight_blocks" value="1" <?php checked( $option, true ); ?> />
		<?php esc_html_e( 'Show visual outline around blocks that have a language attribute set', 'lang-attribute-blocks' ); ?>
	</label>
	<p class="description">
		<?php esc_html_e( 'When enabled, blocks with a language attribute will be visually highlighted with a red dashed outline in both the editor and frontend (only for Administrators and Editors).', 'lang-attribute-blocks' ); ?>
	</p>
	<?php
}

/**
 * Add settings link to the plugin action links.
 *
 * This function adds a "Settings" link to the plugin's row on the Plugins page
 * that points to the Language Attribute for Container Blocks settings section
 * on the Settings > Writing page.
 *
 * @since 1.2
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links with settings link added.
 */
function add_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'options-writing.php' ),
		esc_html__( 'Settings', 'lang-attribute-blocks' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __NAMESPACE__ . '\add_plugin_action_links' );

/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
