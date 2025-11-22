<?php
/**
 * Gutenberg block schema generator.
 *
 * Generates schemas for all registered blocks with attribute definitions.
 *
 * @package    Respira_For_WordPress
 * @subpackage Respira_For_WordPress/includes/page-builders/gutenberg-intelligence
 * @since      1.3.0
 */

require_once RESPIRA_PLUGIN_DIR . 'includes/page-builders/intelligence/class-builder-schema-base.php';

/**
 * Gutenberg block schema class.
 *
 * @since 1.3.0
 */
class Respira_Gutenberg_Block_Schema extends Respira_Builder_Schema_Base {

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		parent::__construct( 'gutenberg', get_bloginfo( 'version' ) );
	}

	/**
	 * Get builder schema for AI context.
	 *
	 * @since  1.3.0
	 * @param  array $blocks_used Optional. Array of block names used on the page.
	 * @return array Builder schema with block information.
	 */
	public function get_builder_schema( $blocks_used = array() ) {
		$schema = array(
			'builder'          => 'gutenberg',
			'builder_version'  => $this->builder_version,
			'available_blocks' => array(),
			'quick_reference'  => $this->get_quick_reference(),
		);

		// Get schemas for blocks used on the page (or all blocks if none specified).
		if ( empty( $blocks_used ) ) {
			$all_blocks = Respira_Gutenberg_Block_Registry::get_all_blocks();
			$blocks_used = array_column( $all_blocks, 'name' );
		}

		foreach ( $blocks_used as $block_name ) {
			$block = Respira_Gutenberg_Block_Registry::get_block( $block_name );
			if ( ! $block ) {
				continue;
			}

			$block_schema = array(
				'title'       => $block['title'],
				'description' => $block['description'],
				'category'    => $block['category'],
				'attributes'  => array(),
			);

			// Extract attribute schemas.
			if ( ! empty( $block['attributes'] ) ) {
				foreach ( $block['attributes'] as $attr_name => $attr_def ) {
					$type = isset( $attr_def['type'] ) ? $this->normalize_type( $attr_def['type'] ) : 'string';
					$format = $this->get_format_hint( $type, $attr_name );

					$block_schema['attributes'][ $attr_name ] = array(
						'type'    => $type,
						'format'  => $format,
						'default' => isset( $attr_def['default'] ) ? $attr_def['default'] : null,
						'example' => $this->get_example_value( $type, $attr_name ),
					);

					// Add source information for dynamic content.
					if ( isset( $attr_def['source'] ) ) {
						$block_schema['attributes'][ $attr_name ]['source'] = $attr_def['source'];
					}
				}
			}

			// Add supports information.
			if ( ! empty( $block['supports'] ) ) {
				$block_schema['supports'] = $block['supports'];
			}

			$schema['available_blocks'][ $block_name ] = $block_schema;
		}

		return $schema;
	}
}

