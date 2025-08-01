<?php
/**
 * Plugin Name:          Language Attribute for Container Blocks
 * Plugin URI:
 * Description:          Add “lang” and “dir” attributes on Group, Columns, and Cover WordPress Blocks
 * Version:              2.0-beta.1
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

// Set the plugin's main file constant
define( 'NAKEDCATPLUGINS_LANG_ATTRIBUTE_BLOCKS_FILE', __FILE__ );

/**
 * Initialize the plugin.
 *
 * This function serves as the main entry point for the plugin. It ensures
 * the class is loaded and returns the singleton instance.
 *
 * @since 1.0
 * @return Lang_Attribute_Blocks The singleton instance of the plugin class.
 */
function init_plugin() {
	// Load the main class
	require_once 'includes/class-lang-attribute-blocks.php';
	// Return the singleton instance
	return Lang_Attribute_Blocks::get_instance();
}

// Initialize the plugin
init_plugin();

/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
