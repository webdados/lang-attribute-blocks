<?php
/**
 * Description missing
 *
 * @since 3.0
 */

namespace NakedCatPlugins\LangAttr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Our main class
 */
final class Lang_Attribute_Blocks_Locale {

	/**
	 * The singleton instance.
	 *
	 * @since 3.0
	 * @var Lang_Attribute_Blocks_Locale|null
	 */
	protected static $instance = null;

	/**
	 * The overriden locale for this post/page
	 *
	 * @var [type]
	 */
	private $overriden_locale = null;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	private function __construct() {
		// Hooks
		$this->init_hooks();
	}

	/**
	 * Prevent cloning of the instance.
	 *
	 * @since 3.0
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the instance.
	 *
	 * @since 3.0
	 * @throws \Exception When attempting to unserialize the singleton instance.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 3.0
	 * @return Lang_Attribute_Blocks_Locale The singleton instance.
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
	 * @since 3.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp', array( $this, 'wp' ) );
		add_filter( 'locale', array( $this, 'filter_locale' ) );
		add_filter( 'determine_locale', array( $this, 'filter_determine_locale' ) );
	}

	public function wp() {
		var_dump( 'wp action triggered' ); // Debugging line to check if the wp action is firing
		var_dump( is_singular() );
		if ( ! is_singular() ) {
			return;
		}
		$post_id = get_queried_object_id();
		var_dump( 'Queried post ID: ' . $post_id ); // Debugging line to check the queried post ID
		$page_lang = trim( get_post_meta( $post_id, '_nakedcatplugins_page_lang', true ) );
		var_dump( 'Page lang meta value: ' . $page_lang ); // Debugging line to check the value of page_lang

		if ( ! empty( $page_lang ) ) {
			var_dump( 'Overriding locale with page_lang: ' . $page_lang ); // Debugging line to check the value of page_lang
			$locale = $this->lang_to_locale( $page_lang );
			var_dump( 'Converted locale: ' . $locale ); // Debugging line to check the converted locale
			if ( $locale ) {
				var_dump( 'Valid locale found: ' . $locale ); // Debugging line to confirm we have a valid locale
				// We have a valid locale, so we set it as the overriden locale for this page/post
				$this->overriden_locale = $locale;
				// Force WordPress to reload translations with the new locale
				var_dump( __( 'January' ) ); // Debugging line to check before reloading translations
				unload_textdomain( 'default' );
				var_dump( load_default_textdomain( $locale ) );
				// Reload plugin and theme translations
				$this->reload_all_textdomains( $locale );
				var_dump( __( 'January' ) ); // Debugging line to check after reloading translations
			}
		}
	}

	/**
	 * Unload all currently loaded textdomains (except 'default', handled separately),
	 * then reload each one using the overridden locale.
	 *
	 * Supports both the modern WP_Translation_Controller introduced in WordPress 6.5
	 * (which handles both .mo and .php performant translation files) and the legacy
	 * MO-based loader used in earlier versions. All failures are silent and graceful:
	 * if a translation file for the target locale cannot be found, that domain stays
	 * unloaded and WordPress returns the untranslated strings.
	 *
	 * @since 3.0
	 * @param string $locale The locale to reload all textdomains in.
	 * @return void
	 */
	private function reload_all_textdomains( string $locale ): void {
		// --- Strategy 1: WordPress 6.5+ WP_Translation_Controller ---
		// The controller stores loaded translations in the protected
		// $loaded_translations property: [ locale => [ textdomain => [ WP_Translation_File, ... ] ] ].
		// There is no public getter for this, so we use reflection.
		if ( class_exists( 'WP_Translation_Controller' ) ) {
			$controller = \WP_Translation_Controller::get_instance();

			try {
				$prop = new \ReflectionProperty( \WP_Translation_Controller::class, 'loaded_translations' );
				$prop->setAccessible( true );
				$loaded_translations = $prop->getValue( $controller );
				var_dump(1);
				echo '<pre>';
				var_dump( $loaded_translations ); // Debugging line to check the loaded translations structure
				echo '</pre>';
			} catch ( \ReflectionException $e ) {
				var_dump(2);
				// If reflection fails for any reason, fall through to the legacy strategy.
				$loaded_translations = null;
				var_dump( 'Reflection failed: ' . $e->getMessage() ); // Debugging line to check for reflection errors
			}

			if ( is_array( $loaded_translations ) ) {
				$current_locale = $controller->get_locale();

				// Snapshot the textdomains + file paths for the current locale before
				// unloading anything, since unload_textdomain() modifies the property.
				$domains_to_reload = array();
				if ( isset( $loaded_translations[ $current_locale ] ) ) {
					foreach ( $loaded_translations[ $current_locale ] as $domain => $files ) {
						if ( 'default' === $domain ) {
							continue; // Handled separately via load_default_textdomain().
						}
						$paths = array();
						foreach ( $files as $translation_file ) {
							if ( $translation_file instanceof \WP_Translation_File ) {
								$paths[] = $translation_file->get_file();
							}
						}
						if ( ! empty( $paths ) ) {
							$domains_to_reload[ $domain ] = $paths;
						}
					}
				}

				foreach ( $domains_to_reload as $domain => $original_paths ) {
					unload_textdomain( $domain );

					foreach ( $original_paths as $original_path ) {
						// Try .php (performant translations) first, then .mo.
						$base     = preg_replace( '/\.(mo|php)$/', '', $original_path );
						$new_path = $this->swap_locale_in_path( $base . '.php', $locale );
						if ( ! $new_path || ! file_exists( $new_path ) ) {
							$new_path = $this->swap_locale_in_path( $base . '.mo', $locale );
						}

						if ( $new_path && file_exists( $new_path ) ) {
							load_textdomain( $domain, $new_path );
							break; // One file per domain is sufficient.
						}
					}
				}

				return;
			}
		}

		// --- Strategy 2: Legacy MO-based loader (pre-WordPress 6.5) ---
		if ( empty( $GLOBALS['l10n'] ) || ! is_array( $GLOBALS['l10n'] ) ) {
			return;
		}

		// Snapshot before unloading, since unload_textdomain() modifies
		// $GLOBALS['l10n'] in place.
		$loaded_domains = array();
		foreach ( $GLOBALS['l10n'] as $domain => $translations ) {
			if ( 'default' === $domain ) {
				continue; // Handled separately via load_default_textdomain().
			}
			var_dump( $domain ); // Debugging line to check the text domain being processed
			var_dump( $translations ); // Debugging line to check the translations object for the domain

			// MO objects store the source file path in ::$filename.
			// Noop_Translations (used for en_US) have no file — skip them.
			if ( $translations instanceof \MO && ! empty( $translations->filename ) ) {
				$loaded_domains[ $domain ] = $translations->filename;
			}
		}

		foreach ( $loaded_domains as $domain => $original_path ) {
			unload_textdomain( $domain );

			$new_path = $this->swap_locale_in_path( $original_path, $locale );

			if ( $new_path && file_exists( $new_path ) ) {
				load_textdomain( $domain, $new_path );
			}
			// If the new path doesn't exist, the domain stays unloaded
			// and WordPress will return the untranslated strings.
		}
	}

	/**
	 * Replace the locale segment in a translation file path with the target locale.
	 *
	 * Handles the two standard WordPress naming conventions for both .mo and .php
	 * performant translation files (WordPress 6.5+):
	 *   - {domain}-{locale}.mo / {domain}-{locale}.php  (plugins/themes)
	 *   - {locale}.mo / {locale}.php                    (core)
	 *
	 * @since 3.0
	 * @param string $path   Original translation file path (.mo or .php).
	 * @param string $locale Target locale, e.g. 'fr_FR'.
	 * @return string|null   Modified path preserving the original extension,
	 *                       or null if no locale segment was found.
	 */
	private function swap_locale_in_path( string $path, string $locale ): ?string {
		$extension = pathinfo( $path, PATHINFO_EXTENSION );
		$filename  = basename( $path, '.' . $extension );
		$directory = dirname( $path );

		// Plugin/theme style: my-plugin-fr_FR.{ext} -> my-plugin-pt_PT.{ext}
		if ( preg_match( '/^(.*-)([a-z]{2,3}_[A-Z]{2,4})$/', $filename, $matches ) ) {
			return $directory . '/' . $matches[1] . $locale . '.' . $extension;
		}

		// Core style: fr_FR.{ext} -> pt_PT.{ext}
		if ( preg_match( '/^[a-z]{2,3}_[A-Z]{2,4}$/', $filename ) ) {
			return $directory . '/' . $locale . '.' . $extension;
		}

		return null;
	}

	public function filter_locale( $locale ) {
		// You can add logic here to modify the locale based on settings or other conditions
		// var_dump( 'filter_locale called with locale: ' . $locale ); // Debugging line
		// var_dump( is_singular() ); // Debugging line to check if it's a singular page/post
		return $locale;
	}

	public function filter_determine_locale( $locale ) {
		// You can add logic here to modify the determined locale based on settings or other conditions
		// var_dump( 'filter_determine_locale called with locale: ' . $locale ); // Debugging line
		return $locale;
	}

	/**
	 * Attempt to convert a BCP 47 language tag (e.g. "pt-PT", "fr", "zh-Hans")
	 * to a WordPress locale string (e.g. "pt_PT", "fr_FR", "zh_CN").
	 *
	 * Resolution order:
	 *  1. Exact match after normalising separator (hyphens → underscores).
	 *  2. Lookup in a curated map of common ambiguous short codes.
	 *  3. Scan installed language packs for a prefix match.
	 *  4. Scan all WordPress-known locales (requires WP API, heavier).
	 *
	 * @since 3.0
	 * @param string $lang BCP 47 language tag from the post meta.
	 * @return string|null A valid WordPress locale, or null if no match found.
	 */
	private function lang_to_locale( string $lang ): ?string {
		if ( empty( $lang ) ) {
			return null;
		}

		// Normalise: lowercase, hyphens to underscores, trim
		$normalised = strtolower( trim( str_replace( '-', '_', $lang ) ) );

		// --- Step 1: Direct/normalised match against installed languages ---
		$installed = get_available_languages();
		// get_available_languages() returns file basenames e.g. ['pt_PT', 'fr_FR']

		// Exact normalised match (case-insensitive)
		foreach ( $installed as $locale ) {
			if ( strtolower( $locale ) === $normalised ) {
				return $locale;
			}
		}

		// --- Step 2: Curated map for ambiguous short codes ---
		// Short codes like "fr" or "zh" don't map to a single locale,
		// so we provide sensible defaults (most widely used locale for that language).
		// Map verified against https://api.wordpress.org/translations/core/1.0/
		// Keys are BCP 47 short codes; values are the canonical WordPress locale.
		// For languages with a single WordPress locale the value equals the locale itself.
		// For ambiguous short codes the most widely-used regional variant is chosen.
		$short_code_map = array(
			'af'  => 'af',
			'am'  => 'am',          // Amharic
			'ar'  => 'ar',
			'arg' => 'arg',         // Aragonese
			'ary' => 'ary',         // Moroccan Arabic
			'as'  => 'as',          // Assamese
			'az'  => 'az',
			'azb' => 'azb',         // South Azerbaijani
			'be'  => 'bel',
			'bel' => 'bel',         // Belarusian (ISO 639-2 code used directly)
			'bg'  => 'bg_BG',
			'bn'  => 'bn_BD',
			'bo'  => 'bo',          // Tibetan
			'bs'  => 'bs_BA',
			'ca'  => 'ca',
			'ceb' => 'ceb',         // Cebuano
			'ckb' => 'ckb',         // Central Kurdish (Sorani)
			'cs'  => 'cs_CZ',
			'cy'  => 'cy',
			'da'  => 'da_DK',
			'de'  => 'de_DE',
			'dsb' => 'dsb',         // Lower Sorbian
			'dzo' => 'dzo',         // Dzongkha
			'el'  => 'el',
			'en'  => 'en_US',
			'eo'  => 'eo',
			'es'  => 'es_ES',
			'et'  => 'et',
			'eu'  => 'eu',
			'fa'  => 'fa_IR',
			'fi'  => 'fi',
			'fr'  => 'fr_FR',
			'fur' => 'fur',         // Friulian
			'fy'  => 'fy',
			'ga'  => 'ga',
			'gd'  => 'gd',          // Scottish Gaelic
			'gl'  => 'gl_ES',
			'gu'  => 'gu',
			'haz' => 'haz',         // Hazaragi
			'he'  => 'he_IL',
			'hi'  => 'hi_IN',
			'hr'  => 'hr',
			'hsb' => 'hsb',         // Upper Sorbian
			'hu'  => 'hu_HU',
			'hy'  => 'hy',
			'id'  => 'id_ID',
			'is'  => 'is_IS',
			'it'  => 'it_IT',
			'ja'  => 'ja',
			'jv'  => 'jv_ID',       // Javanese
			'ka'  => 'ka_GE',
			'kab' => 'kab',         // Kabyle
			'kir' => 'kir',         // Kyrgyz
			'kk'  => 'kk',          // Kazakh
			'km'  => 'km',
			'kn'  => 'kn',          // Kannada
			'ko'  => 'ko_KR',
			'lo'  => 'lo',          // Lao
			'lt'  => 'lt_LT',
			'lv'  => 'lv',
			'mk'  => 'mk_MK',
			'ml'  => 'ml_IN',
			'mn'  => 'mn',
			'mr'  => 'mr',          // Marathi
			'ms'  => 'ms_MY',
			'my'  => 'my_MM',
			'nb'  => 'nb_NO',
			'ne'  => 'ne_NP',       // Nepali
			'nl'  => 'nl_NL',
			'nn'  => 'nn_NO',
			'no'  => 'nb_NO',
			'oci' => 'oci',         // Occitan
			'pa'  => 'pa_IN',
			'pl'  => 'pl_PL',
			'ps'  => 'ps',          // Pashto
			'pt'  => 'pt_PT',
			'rhg' => 'rhg',         // Rohingya
			'ro'  => 'ro_RO',
			'ru'  => 'ru_RU',
			'sah' => 'sah',         // Sakha / Yakut
			'si'  => 'si_LK',       // Sinhala
			'sk'  => 'sk_SK',
			'skr' => 'skr',         // Saraiki
			'sl'  => 'sl_SI',
			'snd' => 'snd',         // Sindhi
			'sq'  => 'sq',
			'sr'  => 'sr_RS',
			'sv'  => 'sv_SE',
			'sw'  => 'sw',
			'szl' => 'szl',         // Silesian
			'ta'  => 'ta_IN',
			'tah' => 'tah',         // Tahitian
			'te'  => 'te',
			'th'  => 'th',
			'tl'  => 'tl',          // Filipino / Tagalog
			'tr'  => 'tr_TR',
			'tt'  => 'tt_RU',       // Tatar
			'ug'  => 'ug_CN',
			'uk'  => 'uk',
			'ur'  => 'ur',
			'uz'  => 'uz_UZ',
			'vi'  => 'vi',
			'zh'  => 'zh_CN',
			'zu'  => 'zu',
		);

		if ( isset( $short_code_map[ $normalised ] ) ) {
			$mapped_locale = $short_code_map[ $normalised ];
			if ( in_array( $mapped_locale, $installed, true ) ) {
				return $mapped_locale;
			}
		}

		// --- Step 3: Prefix match against installed languages ---
		// e.g. user typed "pt" and "pt_PT" is installed but not in the map above
		$lang_prefix = strtolower( explode( '_', $normalised )[0] );
		foreach ( $installed as $locale ) {
			if ( strtolower( explode( '_', $locale )[0] ) === $lang_prefix ) {
				return $locale;
			}
		}

		// --- Step 4: No match found ---
		return null;
	}
}

/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
