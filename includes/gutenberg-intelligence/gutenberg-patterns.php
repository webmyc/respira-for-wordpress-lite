<?php
/**
 * Gutenberg block patterns library.
 *
 * Common layout patterns for Gutenberg blocks.
 *
 * @package    Respira_For_WordPress
 * @subpackage Respira_For_WordPress/includes/page-builders/gutenberg-intelligence
 * @since      1.3.0
 */

/**
 * Get Gutenberg block patterns.
 *
 * @since  1.3.0
 * @return array Array of patterns.
 */
function respira_get_gutenberg_patterns() {
	return array(
		'hero-section' => array(
			'title'       => __( 'Hero Section', 'respira-for-wordpress' ),
			'description' => __( 'Full-width hero section with heading and CTA', 'respira-for-wordpress' ),
			'category'    => 'headers',
			'structure'   => array(
				array(
					'type'       => 'core/group',
					'attributes' => array(
						'align' => 'full',
					),
					'inner_blocks' => array(
						array(
							'type'       => 'core/heading',
							'attributes' => array(
								'level' => 1,
								'content' => __( 'Welcome to Our Site', 'respira-for-wordpress' ),
							),
						),
						array(
							'type'       => 'core/paragraph',
							'attributes' => array(
								'content' => __( 'Your compelling tagline here', 'respira-for-wordpress' ),
							),
						),
						array(
							'type'       => 'core/buttons',
							'inner_blocks' => array(
								array(
									'type'       => 'core/button',
									'attributes' => array(
										'text' => __( 'Get Started', 'respira-for-wordpress' ),
										'url'  => '#',
									),
								),
							),
						),
					),
				),
			),
		),
		'three-column-features' => array(
			'title'       => __( 'Three Column Features', 'respira-for-wordpress' ),
			'description' => __( 'Three-column layout with feature items', 'respira-for-wordpress' ),
			'category'    => 'content',
			'structure'   => array(
				array(
					'type'       => 'core/columns',
					'attributes' => array(
						'columns' => 3,
					),
					'inner_blocks' => array(
						array(
							'type'       => 'core/column',
							'inner_blocks' => array(
								array(
									'type'       => 'core/heading',
									'attributes' => array(
										'level' => 3,
										'content' => __( 'Feature 1', 'respira-for-wordpress' ),
									),
								),
								array(
									'type'       => 'core/paragraph',
									'attributes' => array(
										'content' => __( 'Feature description', 'respira-for-wordpress' ),
									),
								),
							),
						),
						array(
							'type'       => 'core/column',
							'inner_blocks' => array(
								array(
									'type'       => 'core/heading',
									'attributes' => array(
										'level' => 3,
										'content' => __( 'Feature 2', 'respira-for-wordpress' ),
									),
								),
								array(
									'type'       => 'core/paragraph',
									'attributes' => array(
										'content' => __( 'Feature description', 'respira-for-wordpress' ),
									),
								),
							),
						),
						array(
							'type'       => 'core/column',
							'inner_blocks' => array(
								array(
									'type'       => 'core/heading',
									'attributes' => array(
										'level' => 3,
										'content' => __( 'Feature 3', 'respira-for-wordpress' ),
									),
								),
								array(
									'type'       => 'core/paragraph',
									'attributes' => array(
										'content' => __( 'Feature description', 'respira-for-wordpress' ),
									),
								),
							),
						),
					),
				),
			),
		),
		'call-to-action' => array(
			'title'       => __( 'Call to Action', 'respira-for-wordpress' ),
			'description' => __( 'Centered CTA section with heading and button', 'respira-for-wordpress' ),
			'category'    => 'content',
			'structure'   => array(
				array(
					'type'       => 'core/group',
					'attributes' => array(
						'align' => 'wide',
					),
					'inner_blocks' => array(
						array(
							'type'       => 'core/heading',
							'attributes' => array(
								'level'   => 2,
								'content' => __( 'Ready to Get Started?', 'respira-for-wordpress' ),
								'align'   => 'center',
							),
						),
						array(
							'type'       => 'core/paragraph',
							'attributes' => array(
								'content' => __( 'Join thousands of satisfied customers', 'respira-for-wordpress' ),
								'align'   => 'center',
							),
						),
						array(
							'type'       => 'core/buttons',
							'attributes' => array(
								'layout' => array(
									'type' => 'flex',
									'justifyContent' => 'center',
								),
							),
							'inner_blocks' => array(
								array(
									'type'       => 'core/button',
									'attributes' => array(
										'text' => __( 'Sign Up Now', 'respira-for-wordpress' ),
										'url'  => '#',
									),
								),
							),
						),
					),
				),
			),
		),
	);
}

