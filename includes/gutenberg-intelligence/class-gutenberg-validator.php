<?php
/**
 * Gutenberg block validator.
 *
 * Validates block structure and attributes before injection.
 *
 * @package    Respira_For_WordPress
 * @subpackage Respira_For_WordPress/includes/page-builders/gutenberg-intelligence
 * @since      1.3.0
 */

require_once RESPIRA_PLUGIN_DIR . 'includes/page-builders/intelligence/class-builder-validator-base.php';

/**
 * Gutenberg validator class.
 *
 * @since 1.3.0
 */
class Respira_Gutenberg_Validator extends Respira_Builder_Validator_Base {

	/**
	 * Validate a layout structure.
	 *
	 * @since  1.3.0
	 * @param  array $content Content structure to validate.
	 * @return array Validation result with 'valid' boolean and 'errors' array.
	 */
	public function validate_layout( $content ) {
		$this->clear_errors();

		if ( ! is_array( $content ) ) {
			$this->add_error( __( 'Content must be an array.', 'respira-for-wordpress' ) );
			return array(
				'valid'  => false,
				'errors' => $this->get_errors(),
			);
		}

		// Validate each block.
		foreach ( $content as $block ) {
			$this->validate_block( $block );
		}

		return array(
			'valid'  => empty( $this->errors ),
			'errors' => $this->get_errors(),
		);
	}

	/**
	 * Validate a single block.
	 *
	 * @since  1.3.0
	 * @param  array $block Block structure.
	 */
	private function validate_block( $block ) {
		// Check required fields.
		if ( ! isset( $block['type'] ) ) {
			$this->add_error( __( 'Block must have a type.', 'respira-for-wordpress' ) );
			return;
		}

		$block_name = $block['type'];

		// Validate block name format.
		if ( ! preg_match( '/^[a-z0-9][a-z0-9-]*\/[a-z0-9][a-z0-9-]*$/', $block_name ) ) {
			$this->add_error(
				sprintf(
					/* translators: %s: block name */
					__( 'Invalid block name format: %s', 'respira-for-wordpress' ),
					$block_name
				)
			);
		}

		// Validate attributes if present.
		if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
			$this->validate_block_attributes( $block_name, $block['attrs'] );
		}

		// Validate inner blocks recursively.
		if ( ! empty( $block['inner_blocks'] ) && is_array( $block['inner_blocks'] ) ) {
			foreach ( $block['inner_blocks'] as $inner_block ) {
				$this->validate_block( $inner_block );
			}
		}
	}

	/**
	 * Validate block attributes.
	 *
	 * @since  1.3.0
	 * @param  string $block_name Block name.
	 * @param  array  $attrs      Block attributes.
	 */
	private function validate_block_attributes( $block_name, $attrs ) {
		$block = Respira_Gutenberg_Block_Registry::get_block( $block_name );
		if ( ! $block || empty( $block['attributes'] ) ) {
			return; // Unknown block or no attributes defined.
		}

		// Validate each attribute.
		foreach ( $attrs as $attr_name => $attr_value ) {
			if ( ! isset( $block['attributes'][ $attr_name ] ) ) {
				// Unknown attribute - allow it (may be custom).
				continue;
			}

			$attr_def = $block['attributes'][ $attr_name ];
			$expected_type = isset( $attr_def['type'] ) ? $attr_def['type'] : 'string';

			if ( ! $this->validate_attribute_format( $attr_value, $expected_type, $attr_name ) ) {
				$this->add_error(
					sprintf(
						/* translators: 1: attribute name, 2: block name */
						__( 'Invalid attribute format for %1$s in block %2$s', 'respira-for-wordpress' ),
						$attr_name,
						$block_name
					)
				);
			}
		}
	}
}

