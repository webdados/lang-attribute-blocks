<?php
/**
 * Main plugin class for Language Attribute for Container Blocks
 *
 * This file contains the core functionality for adding language and direction
 * attributes to WordPress container blocks.
 * The plugin enhances accessibility by allowing content creators to specify
 * language changes at the block level, helping websites comply with WCAG
 * guidelines.
 *
 * The plugin follows WordPress coding standards and implements the singleton
 * pattern to ensure only one instance exists throughout the request lifecycle.
 *
 * @since 2.0
 */

namespace NakedCatPlugins\LangAttr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Our main class
 */
final class Lang_Attribute_Blocks {

	/**
	 * The singleton instance.
	 *
	 * @since 1.0
	 * @var Lang_Attribute_Blocks|null
	 */
	protected static $instance = null;

	/**
	 * Defines the WordPress blocks that will support language attributes.
	 *
	 * This property stores an array of block type identifiers that the plugin will
	 * extend with language and direction attributes. The blocks listed here will
	 * receive additional controls in the block editor sidebar for setting:
	 *
	 * - 'lang': Language code attribute (e.g., 'en', 'fr', 'pt-PT')
	 * - 'dir': Text direction attribute ('ltr' for left-to-right, 'rtl' for right-to-left)
	 *
	 * Currently supported blocks:
	 * - 'core/group': The Group block and all its variations (Stack, Row)
	 * - 'core/columns': The Columns block for multi-column layouts
	 * - 'core/column': Individual Column blocks used within Columns
	 * - 'core/cover': The Cover block for full-width background images with overlays
	 * - 'core/navigation': The Navigation block for site menus
	 * - 'core/navigation-submenu': Navigation submenu items
	 * - 'core/page-list': Automatic page listing blocks
	 *
	 * This property is used throughout the plugin to:
	 * - Register custom attributes during block registration
	 * - Add editor controls in the Inspector sidebar
	 * - Apply frontend rendering modifications
	 * - Determine which blocks receive visual highlighting (when enabled)
	 *
	 * In the future, this should be filterable to allow developers to add or remove supported blocks.
	 *
	 * @var array List of block type names that support language attributes
	 */
	private $blocks = array(
		// Container blocks
		'core/group',
		'core/columns',
		'core/column',
		'core/cover',
		// Navigation blocks
		'core/navigation',
		'core/navigation-submenu',
		'core/page-list',
	);

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	private function __construct() {
		// Hooks
		$this->init_hooks();
	}

	/**
	 * Prevent cloning of the instance.
	 *
	 * @since 2.0
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the instance.
	 *
	 * @since 2.0
	 * @throws \Exception When attempting to unserialize the singleton instance.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 2.0
	 * @return Lang_Attribute_Blocks The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks and filters.
	 *
	 * This method sets up all the necessary WordPress hooks and filters for the plugin
	 * to function properly.
	 *
	 * This method is called during object construction to ensure all hooks are
	 * registered when the singleton instance is created.
	 *
	 * @since 2.0
	 * @return void
	 */
	public function init_hooks() {
		// Register block attributes
		add_action( 'register_block_type_args', array( $this, 'register_block_attributes' ), 10, 2 );
		// Applies language attributes to block content in the frontend
		foreach ( $this->blocks as $block_name ) {
			add_filter( 'render_block_' . $block_name, array( $this, 'process_blocks' ), 10, 2 );
		}
		// Enqueues JavaScript and CSS assets for the WordPress block editor
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		// Enqueues CSS assets for the frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		// Add settings section to Settings > Writing page
		add_action( 'admin_init', array( $this, 'add_writing_settings' ) );
		// Add settings link to the plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ), array( $this, 'add_plugin_action_links' ) );
	}

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
	public function register_block_attributes( $args, $block_type ) {
		if ( in_array( $block_type, $this->blocks, true ) ) {
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
	public function process_blocks( $block_content, $block ) {
		if ( isset( $block['attrs']['lang'] ) && ! empty( $block['attrs']['lang'] ) ) {
			$lang          = esc_attr( $block['attrs']['lang'] );
			$dir           = isset( $block['attrs']['dir'] ) ? esc_attr( $block['attrs']['dir'] ) : 'ltr';
			$tag_processor = new \WP_HTML_Tag_Processor( $block_content );
			// Depending on the block type, we will set the tag to be processed
			switch ( $block['blockName'] ) {
				case 'core/navigation-submenu':
					$tag = 'li';
					break;
				case 'core/page-list':
					$tag = 'ul';
					break;
				default:
					// If no specific tag is found, we default to a div
					$tag = 'div';
					break;

			}
			$tag_processor->next_tag( $tag );
			$tag_processor->set_attribute( 'lang', $lang );
			$tag_processor->set_attribute( 'dir', $dir );
			return $tag_processor->get_updated_html();
		}
		return $block_content;
	}

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
	 * @since 1.2
	 * @hook enqueue_block_editor_assets
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Enqueue the main JavaScript file for the block editor
		wp_enqueue_script(
			'nakedcatplugins-lang-attribute-blocks-script',
			plugins_url( 'build/index.js', NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ),
			array( 'wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-block-editor' ),
			filemtime( plugin_dir_path( NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ) . 'build/index.js' ),
			true
		);

		// Localize script to pass the blocks array to JavaScript
		wp_localize_script(
			'nakedcatplugins-lang-attribute-blocks-script',
			'nakedCatPluginsLangAttributeBlocks',
			array(
				'supportedBlocks'  => $this->blocks,
				'siteLanguage'     => get_bloginfo( 'language' ), // This will get the site language (e.g., 'en-US'),
				'highlightEnabled' => get_option( 'nakedcatplugins_lang_attr_highlight_blocks', false ),
				'placeholderText'  => sprintf(
					/* translators: %s: The website's default language code */
					__( '%s (default website language)', 'lang-attribute-blocks' ),
					get_bloginfo( 'language' )
				),
			)
		);

		// Set script translations
		wp_set_script_translations( 'lang-attribute-blocks-script', 'lang-attribute-blocks' );

		// Enqueue the CSS styles for the block editor
		wp_enqueue_style(
			'nakedcatplugins-lang-attribute-blocks-style',
			plugins_url( 'build/index.css', NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ),
			array(),
			filemtime( plugin_dir_path( NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ) . 'build/index.css' )
		);
		// Add custom color override if specified
		$this->maybe_add_custom_highlight_color();
	}

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
	public function enqueue_frontend_assets() {
		// Enqueue the CSS styles for the frontend
		if ( current_user_can( 'edit_others_posts' ) ) {
			$highlight_enabled = get_option( 'nakedcatplugins_lang_attr_highlight_blocks', false );
			if ( $highlight_enabled ) {
				wp_enqueue_style(
					'nakedcatplugins-lang-attribute-blocks-style',
					plugins_url( 'build/index.css', NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ),
					array(),
					filemtime( plugin_dir_path( NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE ) . 'build/index.css' )
				);
				// Add custom color override if specified
				$this->maybe_add_custom_highlight_color();
			}
		}
	}

	/**
	 * Add custom highlight color override if specified via filter.
	 *
	 * @since 2.0
	 */
	private function maybe_add_custom_highlight_color() {
		/**
		 * Filter the highlight color for blocks with language attributes.
		 *
		 * @since 2.0
		 * @param string $color The highlight color in any valid CSS format.
		 *                      Default: 'rgba(255, 0, 0, 0.75)'
		 */
		$custom_color = apply_filters( 'nakedcatplugins_lang_attr_highlight_color', '' );

		if ( ! empty( $custom_color ) ) {
			wp_add_inline_style(
				'nakedcatplugins-lang-attribute-blocks-style',
				":root { --nakedcatplugins-lang-attr-highlight-color: {$custom_color}; }"
			);
		}
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
	public function add_writing_settings() {
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
			array( $this, 'settings_section_callback' ),
			'writing'
		);

		// Add settings field
		add_settings_field(
			'nakedcatplugins_lang_attr_highlight_blocks',
			__( 'Highlight blocks with lang attribute', 'lang-attribute-blocks' ),
			array( $this, 'highlight_blocks_field_callback' ),
			'writing',
			'nakedcatplugins_lang_attr_section'
		);
	}

	/**
	 * Settings section callback.
	 *
	 * @since 1.2
	 * @return void
	 */
	public function settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure Language Attribute for Container Blocks plugin settings.', 'lang-attribute-blocks' ) . '</p>';
	}
	/**
	 * Highlight blocks field callback.
	 *
	 * @since 1.2
	 * @return void
	 */
	public function highlight_blocks_field_callback() {
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
	public function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-writing.php' ),
			esc_html__( 'Settings', 'lang-attribute-blocks' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
