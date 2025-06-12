=== Language attribute for container Blocks ===
Contributors: nakedcatplugins, webdados
Tags: language, accessibility, block editor
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add "lang" and "dir" attributes on Group and Columns WordPress Blocks.

== Description ==

This plugin aims to provide a way to ensure that any language change in the content of a page is indicated to assistive technologies at the container block level, helping a website comply with WCAG guidelines.

This feature is available on the core block editor only at a text formatting level after code from [Jb Audras plugin "Lang Attribute for the Block Editor"](https://wordpress.org/plugins/lang-attribute/) was merged into core. The objective of this plugin is to provide the same functionality at a container block level (group, including all its variants, and columns) so that the language applies to all child elements, no matter the kind of content inside.

This plugin is heavily inspired by the Jb Audras plugin (including this readme file), and the development started at WordCamp Europe 2025 Contributor Day, by Marco Almeida from [Naked Cat Plugins](https://profiles.wordpress.org/nakedcatplugins/) / [Webdados](https://profiles.wordpress.org/webdados/), and the help from [Ryan Welcher](https://profiles.wordpress.org/welcher/) on the code side and [Amber Hinds](https://profiles.wordpress.org/alh0319/) on the accessibility compliance side.

For more context: this plugin helps you to make your website compliant to the Web Content Accessibility Guidelines (WCAG) success criterion 3.1.2: "Language of Parts". The purpose of this success Criterion is to ensure that user agents can correctly present content written in multiple languages.

Keep in mind that you only should set the lang and dir attributes to a container block if the content you're going to insert inside it is written on a different language than that set globally on your website.

As per Web Content Accessibility Guidelines:

This makes it possible for user agents and assistive technologies to present content according to the presentation and pronunciation rules for that language. This applies to graphical browsers as well as screen readers, braille displays, and other voice browsers.

Both assistive technologies and conventional user agents can render text more accurately if the language of each passage of text is identified. Screen readers can use the pronunciation rules of the language of the text. Visual browsers can display characters and scripts in appropriate ways.

This is especially important when switching between languages that read from left to right and languages that read from right to left, or when text is rendered in a language that uses a different alphabet. Users with disabilities who know all the languages used in the Web page will be better able to understand the content when each passage is rendered appropriately.


== Screenshots ==

1. 

== Installation ==

1. Install the plugin and activate it.
2. Insert a Group or Column block and use the "Language Settings" sidebar panel, to set the language for all the content inside that container

== Changelog ==

= 1.0 =

* First commit