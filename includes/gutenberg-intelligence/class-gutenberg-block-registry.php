<?php
/**
 * Gutenberg block registry.
 *
 * Enhanced block metadata extraction from WP_Block_Type_Registry.
 *
 * @package    Respira_For_WordPress
 * @subpackage Respira_For_WordPress/includes/page-builders/gutenberg-intelligence
 * @since      1.3.0
 */

/**
 * Gutenberg block registry class.
 *
 * @since 1.3.0
 */
class Respira_Gutenberg_Block_Registry {

	/**
	 * Get all registered blocks with enhanced metadata.
	 *
	 * @since  1.3.0
	 * @return array Array of block information.
	 */
	public static function get_all_blocks() {
		// Check cache first.
		$cached = get_transient( 'respira_gutenberg_blocks' );
		if ( false !== $cached ) {
			return $cached;
		}

		$registry = \WP_Block_Type_Registry::get_instance();
		$blocks   = $registry->get_all_registered();

		$block_list = array();

		foreach ( $blocks as $block_name => $block_type ) {
			$block_info = array(
				'name'        => $block_name,
				'title'       => isset( $block_type->title ) ? $block_type->title : $block_name,
				'category'    => isset( $block_type->category ) ? $block_type->category : 'common',
				'description' => isset( $block_type->description ) ? $block_type->description : '',
				'icon'        => isset( $block_type->icon ) ? $block_type->icon : null,
				'keywords'    => isset( $block_type->keywords ) ? $block_type->keywords : array(),
				'supports'    => isset( $block_type->supports ) ? $block_type->supports : array(),
				'attributes'  => isset( $block_type->attributes ) ? $block_type->attributes : array(),
				'variations'  => isset( $block_type->variations ) ? $block_type->variations : array(),
			);

			// Extract inner blocks support.
			if ( isset( $block_type->supports['__experimentalDefaultControls'] ) ) {
				$block_info['default_controls'] = $block_type->supports['__experimentalDefaultControls'];
			}

			// Check for inner blocks support.
			$block_info['supports_inner_blocks'] = isset( $block_type->supports['__experimentalInnerBlocks'] ) || isset( $block_type->supports['innerBlocks'] );

			$block_list[] = $block_info;
		}

		// Cache for 24 hours.
		set_transient( 'respira_gutenberg_blocks', $block_list, DAY_IN_SECONDS );

		return $block_list;
	}

	/**
	 * Get block by name.
	 *
	 * @since  1.3.0
	 * @param  string $block_name Block name.
	 * @return array|null Block information or null if not found.
	 */
	public static function get_block( $block_name ) {
		$blocks = self::get_all_blocks();

		foreach ( $blocks as $block ) {
			if ( $block['name'] === $block_name ) {
				return $block;
			}
		}

		return null;
	}

	/**
	 * Get blocks by category.
	 *
	 * @since  1.3.0
	 * @param  string $category Category name.
	 * @return array Blocks in category.
	 */
	public static function get_blocks_by_category( $category ) {
		$blocks = self::get_all_blocks();

		return array_filter(
			$blocks,
			function( $block ) use ( $category ) {
				return $block['category'] === $category;
			}
		);
	}

	/**
	 * Clear block cache.
	 *
	 * @since 1.3.0
	 */
	public static function clear_cache() {
		delete_transient( 'respira_gutenberg_blocks' );
	}
}

