=== Language Attribute for Container Blocks ===
Contributors: nakedcatplugins, webdados
Tags: language, accessibility, block editor
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 2.0-beta.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add “lang” and “dir” attributes to Group, Columns, Cover, and other specific WordPress Blocks.

== Description ==

This plugin aims to provide a way to ensure that any language change in the content of a page is indicated to assistive technologies at the container block level, helping a website comply with WCAG guidelines.

This feature is available on the core block editor only at a text formatting level after code from [Jb Audras plugin “Lang Attribute for the Block Editor”](https://wordpress.org/plugins/lang-attribute/) was merged into core. The objective of this plugin is to provide the same functionality at a container block level (Group - including all its variants, Columns, Cover, and other specific block types) so that the language applies to all child elements, no matter the kind of content inside.

This plugin is heavily inspired by the Jb Audras plugin (including this readme file), and the development started at WordCamp Europe 2025 Contributor Day, by Marco Almeida from [Naked Cat Plugins](https://profiles.wordpress.org/nakedcatplugins/) / [Webdados](https://profiles.wordpress.org/webdados/), and the help from [Ryan Welcher](https://profiles.wordpress.org/welcher/) on the code side and [Amber Hinds](https://profiles.wordpress.org/alh0319/) on the accessibility compliance side.

For more context: this plugin helps you to make your website compliant with the Web Content Accessibility Guidelines (WCAG) success criterion 3.1.2: “Language of Parts”. The purpose of this success Criterion is to ensure that user agents can correctly present content written in multiple languages.

Keep in mind that you should only set the lang and dir attributes to a container block if the content you’re going to insert inside it is written in a different language than that set globally on your website.

As per Web Content Accessibility Guidelines:

This makes it possible for user agents and assistive technologies to present content according to the presentation and pronunciation rules for that language. This applies to graphical browsers as well as screen readers, braille displays, and other voice browsers.

Both assistive technologies and conventional user agents can render text more accurately if the language of each passage of text is identified. Screen readers can use the pronunciation rules of the language of the text. Visual browsers can display characters and scripts in appropriate ways.

This is especially important when switching between languages that read from left to right and languages that read from right to left, or when text is rendered in a language that uses a different alphabet. Users with disabilities who know all the languages used in the Web page will be better able to understand the content when each passage is rendered appropriately.

That’s not just good for accessibility. It’s also great for SEO. Search engines like Google can better understand your content when languages are clearly defined. That means improved indexing and potentially better rankings.

Banner photo by [Hannah Wright](https://unsplash.com/@hannahwrightdesigner?utm_content=creditCopyText&utm_medium=referral&utm_source=unsplash).

== Supported block types ==
* Group (`core/group`): Group contents together and set a language for them
* Columns (`core/columns` and `core/column`): Organize content into a set of columns and set a language for all the columns or a specific column
* Cover (`core/cover`): Set the language to all the contents inside a cover block
* Navigation (`core/navigation`): Create full navigation menus in different languages
* Submenu (`core/navigation-submenu`): Set a different language on a sub-section of your menu (for example, if you have the default language pages on the first level and a sub-menu with pages in another language)
* Page List (`core/page-list`): List all the pages on your website that are written in a different language and created as a child of the main page of that language


== Features ==
* Add “lang” and “dir” attributes to Group, Columns, Cover, and other specific WordPress Blocks
* Show visual outline around blocks that have a language attribute set - For easy identification of blocks you have already set to a different language during your editing process, only for Administrators and Editors, and if enabled in Settings - Writing

== Screenshots ==

1. Using the block editor to add a language attribute to a Group block
2. The lang and dir attributes rendered on the frontend
3. Using the highlighting option during the editing process

== Installation ==

1. Install the plugin and activate it.
2. Insert a Group, Columns, Cover (or other specific) block, and use the “Language Settings” sidebar panel to set the language for all the content inside that container

== Frequently Asked Questions ==

= Why not have the option to set the language attribute on all block types? =

The idea is to keep it simple and help content and website editors set different language sections, with as many child-blocks as they want, instead of setting it block by block.

= How can I contribute to this plugin? =

[On GitHub](https://github.com/webdados/lang-attribute-blocks)

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team helps validate, triag, and handle any security vulnerabilities. [Report a security vulnerability.]( https://patchstack.com/database/vdp/ce04f590-44d9-45f3-9411-9028e87d4725 )

== Changelog ==

= 2.0 - 2025-08-01 =
* [NEW] Support for new block types: Navigation, Submenu, and Page List
* [DEV] Code refactoring: everything now happens inside a class instead of the main plugin file
* [TWEAK] Better information and screenshot about the highlighting feature for website Administrators and Editors, in the readme.txt file
* [TWEAK] Make evident that the language shown as a placeholder is the default website language when a custom language is not set for a block

= 1.2 - 2025-07-25 =
* [NEW] Show visual outline around blocks that have a language attribute set - For easy identification, only for Administrators and Editors, and if enabled in Settings > Writing

= 1.1 =
* [NEW] Add support for the Cover block
* [DEV] Patchstack mVDP disclaimer on the readme file

= 1.0 =
* First release