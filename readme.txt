=== Language Attribute for Container Blocks and Pages/Posts ===
Contributors: nakedcatplugins, webdados
Tags: language, accessibility, block editor, gutenberg, classic editor
Requires at least: 5.9
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 3.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add `lang` and `dir` attributes to Group, Columns, Cover, and other specific WordPress Blocks, or to the whole page/post.

== Description ==

This plugin aims to ensure that any language change in a page’s content is indicated to assistive technologies at the container block level, helping a website comply with WCAG guidelines.

This feature is available on the core block editor only at a text formatting level after code from [Jb Audras plugin “Lang Attribute for the Block Editor”](https://wordpress.org/plugins/lang-attribute/) was merged into core. The objective of this plugin is to provide the same functionality at a container block level (Group - including all its variants, Columns, Cover, and other specific block types) so that the language applies to all child elements, no matter the kind of content inside.

The plugin also supports setting the language at the page or post level, both on the blocks and classic editor. When an entire page is written in a different language than the website’s default, you can override the HTML `lang` and `dir` attributes for that specific page directly from the Document Settings sidebar, without needing to wrap everything in a container block.

This plugin is heavily inspired by the Jb Audras plugin (including this readme file). The development started at WordCamp Europe 2025 Contributor Day, by Marco Almeida from [Naked Cat Plugins](https://profiles.wordpress.org/nakedcatplugins/) / [Webdados](https://profiles.wordpress.org/webdados/), and the help from [Ryan Welcher](https://profiles.wordpress.org/welcher/) on the code side and [Amber Hinds](https://profiles.wordpress.org/alh0319/) on the accessibility compliance side.

For more context: this plugin helps you to make your website compliant with the Web Content Accessibility Guidelines (WCAG) success criteria:

* **3.1.1 – Language of Page**: The default human language of each web page can be programmatically determined. Use the page-level setting when an entire page or post is written in a language other than the website’s default.
* **3.1.2 – Language of Parts**: The human language of each passage or phrase in the content can be programmatically determined. Use the block-level setting when only specific sections within a page are in a different language.

The purpose of these success criteria is to ensure that user agents can correctly present content written in multiple languages.

Keep in mind that you should set the `lang` and `dir` attributes only on a container block or page if the content is written in a language different from the one set globally on your website.

**As per Web Content Accessibility Guidelines:**

This enables user agents and assistive technologies to present content according to the presentation and pronunciation rules of that language. This applies to graphical browsers, screen readers, braille displays, and other voice browsers.

Both assistive technologies and conventional user agents can render text more accurately if the language of each passage of text is identified. Screen readers can use the language’s pronunciation rules. Visual browsers can display characters and scripts appropriately.

This is especially important when switching between languages that read from left to right and languages that read from right to left, or when text is rendered in a language that uses a different alphabet. Users with disabilities who know all the languages used in the Web page will be better able to understand the content when each passage is rendered appropriately.

That’s not just good for accessibility. It’s also great for SEO. Search engines like Google can better understand your content when languages are clearly defined. That means improved indexing and potentially better rankings.

Banner photo by [Hannah Wright](https://unsplash.com/@hannahwrightdesigner?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash).

== Supported block types ==

* **Group** (`core/group`): Group contents together and set a language for them
* **Columns** (`core/columns` and `core/column`): Organize content into a set of columns and set a language for all the columns or a specific column
* **Cover** (`core/cover`): Set the language to all the contents inside a cover block
* **Navigation** (`core/navigation`): Create full navigation menus in different languages
* **Submenu** (`core/navigation-submenu`): Set a different language on a sub-section of your menu (for example, if you have the default language pages on the first level and a sub-menu with pages in another language)
* **Page List** (`core/page-list`): List all the pages on your website that are written in a different language and created as a child of the main page of that language
* **Content** (`core/post-content`): Set the post content on a custom template to a different language

== Features ==

* Set the language and text direction for an entire page or post, both on the blocks and classic editor: a “Page Language” panel in the Document Settings sidebar overrides the HTML `lang` and `dir` attributes for that specific page
* Add `lang` and `dir` attributes to Group, Columns, Cover, and other specific WordPress Blocks, mentioned above
* Show visual outline around and language code for blocks that have a language attribute set - For easy identification of blocks you have already set to a different language during your editing process, only for Administrators and Editors, and if enabled in Settings - Writing

== Screenshots ==

1. Using the block editor to add a language attribute to a Group block
2. The `lang` and `dir attributes rendered on the frontend
3. Using the highlighting option during the editing process

== Installation ==

1. Install the plugin and activate it.
2. To set the language for an entire page or post: open the Document Settings sidebar (the panel icon at the top right of the editor) and use the “Page Language” panel.
3. To set the language for a specific section within a page: insert a Group, Columns, Cover (or other specific) block, and use the “Block Language” sidebar panel to set the language for all the content inside that container.

== Frequently Asked Questions ==

= When should I use the page-level language setting instead of a block-level one? =

Use the **Page Language** setting (in the Document Settings sidebar) when the entire page or post is written in a different language than the website default. This overrides the `lang` attribute on the HTML element itself, which corresponds to WCAG 3.1.1 (Language of Page).
In this case, we also recommend creating a dedicated template in the Site Editor (Appearance → Editor → Templates) where shared template parts — such as the header and footer — are also in that same language.

Use the **Block Language** setting (in the block’s sidebar panel) when only a specific section within a page is in a different language, while the rest of the page remains in the site’s default language. This corresponds to WCAG 3.1.2 (Language of Parts).

= Why not have the option to set the language attribute on all block types? =

The idea is to keep it simple and help content and website editors set different language sections, with as many child-blocks as they want, instead of setting it block by block.

= Is it possible to change the highlight color? The default red is not suitable for my website background color. =

Yes. Use the `nakedcatplugins_lang_attr_highlight_color` PHP filter and return the color you want.
Here’s a [Gist example](https://gist.github.com/webdados/61197dd2e98f399ba2cfeefbac518851).

If your are working on a WordCamp website, or you don’t want to mess around with PHP, you can also add custom CSS to change the color, overriding our `--nakedcatplugins-lang-attr-highlight-color` variable.
Here’s a [Gist example](https://gist.github.com/webdados/7179f5be4e224ba84867cf77e9bc9174).

= Is it possible to provide a drop-down of pre-set languages instead of a text field?

Yes. Use the `nakedcatplugins_languages` PHP filter and return an array of languages codes you want.
E.g., in your `functions.php`: `add_filter( 'nakedcatplugins_languages', function ( $languages ) { return [ 'pt', 'en' ]; } );`
(Works currently only for blocks, not posts/pages.)

= Is it possible to extend the supported block types? =

Yes. Use the `nakedcatplugins_blocks` PHP filter and return an array of block types you want.
E.g., in your `functions.php`: `add_filter( 'nakedcatplugins_blocks', function ( $blocks ) { $blocks[] = 'core/paragraph'; return $blocks; }`
will add support for the Paragraph block.
Any other block is considered experimental; use at your own risk and test.

= How can I contribute to this plugin? =

[On GitHub](https://github.com/webdados/lang-attribute-blocks)

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team helps validate, triage, and handle any security vulnerabilities. [Report a security vulnerability.]( https://patchstack.com/database/vdp/ce04f590-44d9-45f3-9411-9028e87d4725 )

== Changelog ==

= 3.0 - 2026-03-09 =
* [NEW] Plugin renamed from “Language Attribute for Container Blocks” to “Language Attribute for Container Blocks and Pages/Posts”
* [NEW] Set the page/post language at the document level: a new “Page Language” panel in the Document Settings sidebar allows overriding the HTML `lang` and `dir` attributes for a specific page or post, independently of the website’s default language
* [TWEAK] Rename “Language Settings” sidebar block panel to “Block Language”
* [FIX] Gist URL for changing the highlight color using plain CSS
* [DEV] Tested up to 7.0-beta3-61865

= 2.2 - 2025-10-20 =
* [DEV] Implement deployment actions on GitHub
* [DEV] Update phpcs rules file

= 2.1 - 2025-08-09 =
* [NEW] Support for the Content block type
* [FIX] Apply the attribute to the correct Group block tag when it’s not a DIV

= 2.0 - 2025-08-01 =
* [NEW] Support for new block types: Navigation, Submenu, and Page List
* [DEV] Code refactoring: everything now happens inside a class instead of the main plugin file
* [DEV] Allow changing the highlight color using the `nakedcatplugins_lang_attr_highlight_color` filter
* [TWEAK] Better information and screenshot about the highlighting feature for website Administrators and Editors, in the readme.txt file
* [TWEAK] Make evident that the language shown as a placeholder is the default website language when a custom language is not set for a block

= 1.2 - 2025-07-25 =
* [NEW] Show visual outline around blocks that have a language attribute set - For easy identification, only for Administrators and Editors, and if enabled in Settings > Writing

= 1.1 =
* [NEW] Add support for the Cover block
* [DEV] Patchstack mVDP disclaimer on the readme file

= 1.0 =
* First release