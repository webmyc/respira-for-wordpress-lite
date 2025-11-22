<?php
/**
 * Content filtering and sanitization for modern web features.
 *
 * This class provides additional content filters to preserve script tags,
 * modern web features, and JavaScript within Divi Builder modules when
 * content is updated via the Respira API.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.4
 */

/**
 * Content filter class.
 *
 * Handles preservation of script tags and modern web features during
 * content save operations triggered by the Respira API.
 *
 * @since 1.0.4
 */
class Respira_Lite_Content_Filter {

	/**
	 * Flag to indicate if we're in an API update context.
	 *
	 * @since  1.0.4
	 * @var    bool
	 */
	private static $in_api_update = false;

	/**
	 * Initialize the content filter.
	 *
	 * @since 1.0.4
	 */
	public static function init() {
		// Hook into content save to preserve scripts.
		add_filter( 'content_save_pre', array( __CLASS__, 'preserve_scripts_on_save' ), 1, 1 );
		add_filter( 'content_filtered_save_pre', array( __CLASS__, 'preserve_scripts_on_save' ), 1, 1 );

		// Hook into wp_insert_post_data to prevent sanitization at a lower level.
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'preserve_scripts_in_post_data' ), 99, 2 );

		// Frontend rendering: Decode scripts BEFORE Divi processes shortcodes (priority 5).
		add_filter( 'the_content', array( __CLASS__, 'preserve_html_content_scripts' ), 5, 1 );

		// Frontend rendering: Prevent wpautop from adding <br /> tags (priority 8, before wpautop at 10).
		add_filter( 'the_content', array( __CLASS__, 'prevent_wpautop_on_scripts' ), 8, 1 );

		// Frontend rendering: Aggressive final cleanup after all processing (priority 9999).
		add_filter( 'the_content', array( __CLASS__, 'ensure_scripts_render' ), 9999, 1 );

		// Hook into Divi's module content output (after module renders).
		add_filter( 'et_pb_module_content', array( __CLASS__, 'decode_divi_module_output' ), 10, 3 );

		// Hook into Divi's module attributes processing.
		add_filter( 'et_pb_module_shortcode_attributes', array( __CLASS__, 'preserve_scripts_in_divi_attributes' ), 10, 5 );
	}

	/**
	 * Set API update context.
	 *
	 * Call this before updating content via API to enable script preservation.
	 *
	 * @since  1.0.4
	 * @param  bool $enabled Whether API update is active.
	 * @return void
	 */
	public static function set_api_update_context( $enabled = true ) {
		self::$in_api_update = $enabled;
	}

	/**
	 * Check if we're in an API update context.
	 *
	 * @since  1.0.4
	 * @return bool True if in API update.
	 */
	public static function is_api_update() {
		return self::$in_api_update;
	}

	/**
	 * Preserve scripts during content save.
	 *
	 * This filter runs early in the content save pipeline to preserve
	 * script tags in Divi HTML content and code blocks before WordPress's
	 * default sanitization removes them.
	 *
	 * @since  1.0.4
	 * @param  string $content Content being saved.
	 * @return string Processed content.
	 */
	public static function preserve_scripts_on_save( $content ) {
		// Only preserve scripts during API updates.
		if ( ! self::$in_api_update ) {
			return $content;
		}

		// Preserve scripts in Divi modules.
		$content = self::preserve_divi_module_scripts( $content );

		return $content;
	}

	/**
	 * Preserve scripts in post data.
	 *
	 * This filter runs at the wp_insert_post_data level to prevent
	 * WordPress from sanitizing content after our filters have run.
	 *
	 * @since  1.0.4
	 * @param  array $data    An array of slashed post data.
	 * @param  array $postarr An array of sanitized, but otherwise unmodified post data.
	 * @return array Modified post data.
	 */
	public static function preserve_scripts_in_post_data( $data, $postarr ) {
		// Only preserve scripts during API updates.
		if ( ! self::$in_api_update ) {
			return $data;
		}

		// If post_content is set, preserve scripts in it.
		if ( isset( $data['post_content'] ) ) {
			$data['post_content'] = self::preserve_divi_module_scripts( $data['post_content'] );
		}

		return $data;
	}

	/**
	 * Preserve scripts in Divi modules.
	 *
	 * Processes Divi et_pb_html_content and et_pb_code blocks to ensure
	 * JavaScript is preserved without HTML encoding or stripping.
	 *
	 * @since  1.0.4
	 * @param  string $content Content to process.
	 * @return string Content with preserved scripts.
	 */
	private static function preserve_divi_module_scripts( $content ) {
		// Preserve scripts in et_pb_html_content blocks.
		$content = preg_replace_callback(
			'/\[et_pb_html_content([^\]]*)\](.*?)\[\/et_pb_html_content\]/s',
			array( __CLASS__, 'decode_module_content' ),
			$content
		);

		// Preserve scripts in et_pb_code blocks.
		$content = preg_replace_callback(
			'/\[et_pb_code([^\]]*)\](.*?)\[\/et_pb_code\]/s',
			array( __CLASS__, 'decode_module_content' ),
			$content
		);

		return $content;
	}

	/**
	 * Decode module content callback.
	 *
	 * @since  1.0.4
	 * @param  array $matches Regex matches.
	 * @return string Decoded module shortcode.
	 */
	private static function decode_module_content( $matches ) {
		$attributes = $matches[1];
		$content    = $matches[2];
		$tag        = strpos( $matches[0], 'et_pb_html_content' ) !== false ? 'et_pb_html_content' : 'et_pb_code';

		// Decode multiple times to handle double/triple encoding.
		$decoded = $content;
		for ( $i = 0; $i < 3; $i++ ) {
			$previous = $decoded;
			$decoded  = html_entity_decode( $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			// If nothing changed, we're done decoding.
			if ( $previous === $decoded ) {
				break;
			}
		}

		return '[' . $tag . $attributes . ']' . $decoded . '[/' . $tag . ']';
	}

	/**
	 * Preserve scripts in HTML content blocks on frontend.
	 *
	 * This filter runs BEFORE Divi processes shortcodes (priority 5) to decode
	 * any HTML entities in et_pb_html_content and et_pb_code blocks, ensuring
	 * JavaScript executes correctly on the frontend.
	 *
	 * @since  1.0.6
	 * @param  string $content The post content.
	 * @return string Modified content with decoded scripts.
	 */
	public static function preserve_html_content_scripts( $content ) {
		// Only process if content contains Divi HTML content or code blocks.
		if ( strpos( $content, 'et_pb_html_content' ) === false &&
		     strpos( $content, 'et_pb_code' ) === false ) {
			return $content;
		}

		// Decode HTML entities in et_pb_html_content blocks.
		$content = preg_replace_callback(
			'/\[et_pb_html_content([^\]]*)\](.*?)\[\/et_pb_html_content\]/s',
			array( __CLASS__, 'decode_html_entities_in_module' ),
			$content
		);

		// Decode HTML entities in et_pb_code blocks.
		$content = preg_replace_callback(
			'/\[et_pb_code([^\]]*)\](.*?)\[\/et_pb_code\]/s',
			array( __CLASS__, 'decode_html_entities_in_module' ),
			$content
		);

		return $content;
	}

	/**
	 * Prevent wpautop from adding <br /> tags in script tags.
	 *
	 * This filter runs at priority 8, BEFORE wpautop (which runs at priority 10).
	 * It protects script tags from being wrapped with paragraph and break tags.
	 *
	 * @since  1.0.6
	 * @param  string $content The post content.
	 * @return string Modified content with protected scripts.
	 */
	public static function prevent_wpautop_on_scripts( $content ) {
		// Only process if content contains script tags.
		if ( strpos( $content, '<script' ) === false && strpos( $content, '[et_pb_html_content' ) === false ) {
			return $content;
		}

		// Protect script tags from wpautop by temporarily replacing them with placeholders.
		$placeholders = array();
		$placeholder_index = 0;

		// First, protect script tags that are already in the content.
		$content = preg_replace_callback(
			'/<script[^>]*>.*?<\/script>/s',
			function( $matches ) use ( &$placeholders, &$placeholder_index ) {
				$placeholder = '___RESPIRA_SCRIPT_PLACEHOLDER_' . $placeholder_index . '___';
				$placeholders[ $placeholder ] = $matches[0];
				$placeholder_index++;
				return $placeholder;
			},
			$content
		);

		// Store placeholders for later restoration.
		if ( ! empty( $placeholders ) ) {
			add_filter(
				'the_content',
				function( $content ) use ( $placeholders ) {
					// Restore script tags after wpautop has run.
					foreach ( $placeholders as $placeholder => $script ) {
						$content = str_replace( $placeholder, $script, $content );
					}
					return $content;
				},
				11,
				1
			);
		}

		return $content;
	}

	/**
	 * Decode HTML entities in module content.
	 *
	 * Callback for regex that decodes HTML entities in Divi module content
	 * to ensure scripts execute on frontend. Uses aggressive decoding including
	 * explicit replacements for common entities.
	 *
	 * @since  1.0.6
	 * @param  array $matches Regex matches.
	 * @return string Decoded module shortcode.
	 */
	private static function decode_html_entities_in_module( $matches ) {
		$attributes = $matches[1];
		$content    = $matches[2];
		$tag        = strpos( $matches[0], 'et_pb_html_content' ) !== false ? 'et_pb_html_content' : 'et_pb_code';

		// Only decode if content contains script tags.
		if ( strpos( $content, '<script' ) === false && strpos( $content, '&lt;script' ) === false ) {
			return $matches[0];
		}

		// Decode HTML entities multiple times to handle triple-encoded content.
		$decoded = $content;
		for ( $i = 0; $i < 3; $i++ ) {
			$previous = $decoded;
			$decoded  = html_entity_decode( $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			// Stop if nothing changed.
			if ( $previous === $decoded ) {
				break;
			}
		}

		// Explicitly replace common entities that might remain.
		$decoded = str_replace(
			array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
			array( '&', "'", "'", '"', '"', '&', '<', '>', '"', "'" ),
			$decoded
		);

		return '[' . $tag . $attributes . ']' . $decoded . '[/' . $tag . ']';
	}

	/**
	 * Preserve scripts in Divi module attributes.
	 *
	 * Hooks into Divi's module processing to ensure script tags in module
	 * attributes are preserved during rendering. Uses aggressive entity decoding.
	 *
	 * @since  1.0.6
	 * @param  array  $attrs      Module attributes.
	 * @param  array  $unprocessed_attrs Unprocessed attributes.
	 * @param  string $slug       Module slug.
	 * @param  string $address    Module address.
	 * @param  string $content    Module content.
	 * @return array Modified attributes.
	 */
	public static function preserve_scripts_in_divi_attributes( $attrs, $unprocessed_attrs, $slug, $address, $content ) {
		// Only process HTML content and code modules.
		if ( $slug !== 'et_pb_html_content' && $slug !== 'et_pb_code' ) {
			return $attrs;
		}

		// Decode content attribute if it exists.
		if ( isset( $attrs['content'] ) ) {
			$decoded = $attrs['content'];

			// Multiple-pass HTML entity decoding.
			for ( $i = 0; $i < 3; $i++ ) {
				$previous = $decoded;
				$decoded  = html_entity_decode( $decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				if ( $previous === $decoded ) {
					break;
				}
			}

			// Aggressively replace common entities.
			$decoded = str_replace(
				array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
				array( '&', "'", "'", '"', '"', '&', '<', '>', '"', "'" ),
				$decoded
			);

			$attrs['content'] = $decoded;
		}

		return $attrs;
	}

	/**
	 * Decode HTML entities in Divi module output.
	 *
	 * This filter hooks into Divi's module content output, right when each module
	 * renders its HTML. This catches encoding that happens during Divi's rendering.
	 *
	 * @since  1.0.7
	 * @param  string $output Module HTML output.
	 * @param  string $render_slug Module slug.
	 * @param  object $module Module object.
	 * @return string Decoded module output.
	 */
	public static function decode_divi_module_output( $output, $render_slug, $module ) {
		// Only process HTML content and code modules that contain scripts.
		if ( ( $render_slug !== 'et_pb_html_content' && $render_slug !== 'et_pb_code' ) ||
		     strpos( $output, 'script' ) === false ) {
			return $output;
		}

		// Decode HTML entities multiple times.
		for ( $i = 0; $i < 3; $i++ ) {
			$previous = $output;
			$output   = html_entity_decode( $output, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			if ( $previous === $output ) {
				break;
			}
		}

		// Aggressively replace common WordPress entities.
		$output = str_replace(
			array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
			array( '&', "'", "'", '"', '"', '&', '<', '>', '"', "'" ),
			$output
		);

		// Remove any <br /> tags that might be around script tags.
		$output = preg_replace( '/<br\s*\/?>(\s*<script)/i', '$1', $output );
		$output = preg_replace( '/(<\/script>)\s*<br\s*\/?>/i', '$1', $output );

		return $output;
	}

	/**
	 * Ensure scripts render correctly on frontend.
	 *
	 * This filter runs VERY LATE (priority 9999) after ALL other processing to ensure
	 * that any remaining encoded scripts in the rendered output are decoded.
	 * Uses ultra-aggressive entity replacement to handle any encoding.
	 *
	 * @since  1.0.4
	 * @param  string $content The post content.
	 * @return string Modified content.
	 */
	public static function ensure_scripts_render( $content ) {
		// Only process if content contains the word "script" (encoded or not).
		if ( stripos( $content, 'script' ) === false ) {
			return $content;
		}

		// Ultra-aggressive decoding - run 5 times to catch everything.
		for ( $i = 0; $i < 5; $i++ ) {
			$previous = $content;
			$content  = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			if ( $previous === $content ) {
				break;
			}
		}

		// Aggressively replace ALL common entities that WordPress/Divi might create.
		$content = str_replace(
			array( '&#038;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '&#x26;', '&#x27;', '&#x22;' ),
			array( '&', "'", "'", '"', '"', '&', '<', '>', '"', "'", '&', "'", '"' ),
			$content
		);

		// Remove <br /> tags around script tags.
		$content = preg_replace( '/<br\s*\/?>(\s*<script)/i', '$1', $content );
		$content = preg_replace( '/(<\/script>)\s*<br\s*\/?>/i', '$1', $content );

		// Remove <p> tags around script tags.
		$content = preg_replace( '/<p>(\s*<script)/i', '$1', $content );
		$content = preg_replace( '/(<\/script>\s*)<\/p>/i', '$1', $content );

		return $content;
	}

	/**
	 * Remove all WordPress content filters temporarily.
	 *
	 * Call this before wp_update_post to disable all WordPress sanitization.
	 * IMPORTANT: Must call restore_content_filters() after update!
	 *
	 * @since  1.0.4
	 * @return array Array of removed filters for restoration.
	 */
	public static function remove_content_filters() {
		global $wp_filter;

		$removed = array();

		// Remove content_save_pre filters (except ours).
		if ( isset( $wp_filter['content_save_pre'] ) ) {
			foreach ( $wp_filter['content_save_pre']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $id => $callback ) {
					// Don't remove our own filter.
					if ( is_array( $callback['function'] ) &&
					     $callback['function'][0] === __CLASS__ ) {
						continue;
					}

					if ( remove_filter( 'content_save_pre', $callback['function'], $priority ) ) {
						$removed[] = array(
							'hook'     => 'content_save_pre',
							'function' => $callback['function'],
							'priority' => $priority,
							'args'     => $callback['accepted_args'] ?? 1,
						);
					}
				}
			}
		}

		// Remove content_filtered_save_pre filters (except ours).
		if ( isset( $wp_filter['content_filtered_save_pre'] ) ) {
			foreach ( $wp_filter['content_filtered_save_pre']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $id => $callback ) {
					// Don't remove our own filter.
					if ( is_array( $callback['function'] ) &&
					     $callback['function'][0] === __CLASS__ ) {
						continue;
					}

					if ( remove_filter( 'content_filtered_save_pre', $callback['function'], $priority ) ) {
						$removed[] = array(
							'hook'     => 'content_filtered_save_pre',
							'function' => $callback['function'],
							'priority' => $priority,
							'args'     => $callback['accepted_args'] ?? 1,
						);
					}
				}
			}
		}

		return $removed;
	}

	/**
	 * Restore WordPress content filters.
	 *
	 * Restores filters that were removed by remove_content_filters().
	 *
	 * @since  1.0.4
	 * @param  array $removed Array of removed filters from remove_content_filters().
	 * @return void
	 */
	public static function restore_content_filters( $removed ) {
		foreach ( $removed as $filter ) {
			add_filter(
				$filter['hook'],
				$filter['function'],
				$filter['priority'],
				$filter['args']
			);
		}
	}
}
