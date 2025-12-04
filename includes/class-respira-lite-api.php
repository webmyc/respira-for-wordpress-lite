<?php
/**
 * REST API functionality for Respira for WordPress Lite.
 *
 * Provides comprehensive REST API endpoints for content management,
 * authentication, context extraction, and builder integration.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * REST API class.
 *
 * Handles all REST API endpoints for the Lite version with proper
 * usage limiting, security validation, and upgrade prompts.
 *
 * @since 1.0.0
 */
class Respira_Lite_API {

	/**
	 * Initialize the API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all REST API routes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Authentication endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/auth/generate-key',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'generate_api_key' ),
				'permission_callback' => array( $this, 'admin_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/auth/validate-key',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'validate_api_key' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/auth/revoke-key/(?P<key_id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'revoke_api_key' ),
				'permission_callback' => array( $this, 'admin_permission_check' ),
				'args'                => array(
					'key_id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/usage',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_usage' ),
				'permission_callback' => array( $this, 'check_api_key' ),
			)
		);

		// Context endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/context/site-info',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_site_info' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/context/theme-docs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_theme_docs' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/context/builder-info',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_builder_info' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		// Page endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/pages',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_pages' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/pages/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_page' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
					),
				),
				array(
					'methods'             => array( 'PUT', 'POST' ),
					'callback'            => array( $this, 'update_page' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id'      => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
						'content' => array(
							'required'          => false,
							'sanitize_callback' => 'wp_kses_post',
						),
						'title'   => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'status'  => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'skip_security_check' => array(
							'required'          => false,
							'validate_callback' => function( $param ) {
								return is_bool( $param ) || '1' === $param || '0' === $param;
							},
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_page' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
					),
				),
			)
		);

		// Post endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/posts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_posts' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
				'args'                => $this->get_collection_params(),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/posts/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_post' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
					),
				),
				array(
					'methods'             => array( 'PUT', 'POST' ),
					'callback'            => array( $this, 'update_post' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id'      => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
						'content' => array(
							'required'          => false,
							'sanitize_callback' => 'wp_kses_post',
						),
						'title'   => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'status'  => array(
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'skip_security_check' => array(
							'required'          => false,
							'validate_callback' => function( $param ) {
								return is_bool( $param ) || '1' === $param || '0' === $param;
							},
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_post' ),
					'permission_callback' => array( $this, 'api_key_permission_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
						),
					),
				),
			)
		);

		// Builder endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/builder/(?P<builder>[a-zA-Z0-9-_]+)/extract/(?P<page_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'extract_builder_content' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
				'args'                => array(
					'builder' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page_id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/builder/(?P<builder>[a-zA-Z0-9-_]+)/inject/(?P<page_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'inject_builder_content' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
				'args'                => array(
					'builder' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page_id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'content' => array(
						'required' => true,
					),
				),
			)
		);

		// Media endpoints.
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/media/upload',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'upload_media' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		// Analysis endpoints (all return upgrade messages).
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/analyze/seo/(?P<page_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'analyze_seo' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/analyze/performance/(?P<page_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'analyze_performance' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/analyze/aeo/(?P<page_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'analyze_aeo' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/analyze/accessibility/(?P<page_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'analyze_accessibility' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		// Plugin endpoints (all return upgrade messages).
		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/plugins',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_plugins' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/plugins/install',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'install_plugin' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/plugins/(?P<slug>[a-zA-Z0-9-_]+)/activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'activate_plugin' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);

		register_rest_route(
			RESPIRA_LITE_REST_NAMESPACE,
			'/plugins/(?P<slug>[a-zA-Z0-9-_]+)/deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'deactivate_plugin' ),
				'permission_callback' => array( $this, 'api_key_permission_check' ),
			)
		);
	}

	/**
	 * Admin permission check.
	 *
	 * Checks if the current user has manage_options capability.
	 *
	 * @since 1.0.0
	 * @return bool True if user has permission, false otherwise.
	 */
	public function admin_permission_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * API key permission check.
	 *
	 * Validates the API key from the Authorization header.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
	 */
	public function api_key_permission_check( $request ) {
		$auth_header = $request->get_header( 'authorization' );

		if ( empty( $auth_header ) ) {
			return new WP_Error(
				'respira_lite_missing_auth',
				__( 'Respira Lite says: Authorization header is required. Please provide your API key.', 'respira-for-wordpress-lite' ),
				array( 'status' => 401 )
			);
		}

		// Extract API key from "Bearer <key>" format.
		$api_key = str_replace( 'Bearer ', '', $auth_header );

		$valid = Respira_Lite_Auth::validate_api_key( $api_key );

		if ( ! $valid ) {
			return new WP_Error(
				'respira_lite_invalid_key',
				__( 'Respira Lite says: Invalid API key. Please check your credentials.', 'respira-for-wordpress-lite' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Generate API key endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function generate_api_key( $request ) {
		$user_id     = get_current_user_id();
		$name        = $request->get_param( 'name' );
		$permissions = $request->get_param( 'permissions' );

		$api_key = Respira_Lite_Auth::generate_api_key( $user_id, $name, $permissions );

		if ( is_wp_error( $api_key ) ) {
			return $api_key;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: API key generated successfully. Please save this key - it will not be shown again.', 'respira-for-wordpress-lite' ),
				'api_key' => $api_key,
			),
			201
		);
	}

	/**
	 * Validate API key endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function validate_api_key( $request ) {
		$api_key = $request->get_param( 'api_key' );

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'respira_lite_missing_key',
				__( 'Respira Lite says: API key is required.', 'respira-for-wordpress-lite' ),
				array( 'status' => 400 )
			);
		}

		$valid = Respira_Lite_Auth::validate_api_key( $api_key );

		if ( ! $valid ) {
			return new WP_Error(
				'respira_lite_invalid_key',
				__( 'Respira Lite says: Invalid API key.', 'respira-for-wordpress-lite' ),
				array( 'status' => 401 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: API key is valid.', 'respira-for-wordpress-lite' ),
				'key'     => array(
					'id'         => $valid->id,
					'name'       => $valid->name,
					'created_at' => $valid->created_at,
					'last_used'  => $valid->last_used,
				),
			),
			200
		);
	}

	/**
	 * Revoke API key endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function revoke_api_key( $request ) {
		$key_id  = $request->get_param( 'key_id' );
		$user_id = get_current_user_id();

		$result = Respira_Lite_Auth::revoke_api_key( $key_id, $user_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: API key revoked successfully.', 'respira-for-wordpress-lite' ),
			),
			200
		);
	}

	/**
	 * Get usage statistics endpoint.
	 *
	 * Returns current usage data for the Lite version monthly edit limit.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response Response object with usage statistics.
	 */
	public function get_usage() {
		return new WP_REST_Response(
			Respira_Lite_Usage_Limiter::get_usage(),
			200
		);
	}

	/**
	 * Get site info endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_site_info( $request ) {
		$info = Respira_Lite_Context::get_site_info();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $info,
			),
			200
		);
	}

	/**
	 * Get theme docs endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_theme_docs( $request ) {
		$docs = Respira_Lite_Context::get_theme_docs();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $docs,
			),
			200
		);
	}

	/**
	 * Get builder info endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_builder_info( $request ) {
		$info = Respira_Lite_Context::get_builder_info();

		// Add Lite version notice.
		$info['notice'] = __( 'Respira Lite says: Only Gutenberg builder is supported in the Lite version. Upgrade for full builder support.', 'respira-for-wordpress-lite' );
		$info['upgrade_url'] = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'builder_upgrade' );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $info,
			),
			200
		);
	}

	/**
	 * Get pages endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_pages( $request ) {
		$per_page = $request->get_param( 'per_page' ) ?: 10;
		$page     = $request->get_param( 'page' ) ?: 1;
		$search   = $request->get_param( 'search' );
		$status   = $request->get_param( 'status' ) ?: 'any';

		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => $status,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = sanitize_text_field( $search );
		}

		$query = new WP_Query( $args );

		$pages = array();
		foreach ( $query->posts as $post ) {
			$pages[] = $this->prepare_post_response( $post );
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'data'       => $pages,
				'pagination' => array(
					'total'       => $query->found_posts,
					'total_pages' => $query->max_num_pages,
					'current'     => $page,
					'per_page'    => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get single page endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function get_page( $request ) {
		$page_id = $request->get_param( 'id' );
		$post    = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_page_not_found',
				__( 'Respira Lite says: Page not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->prepare_post_response( $post ),
			),
			200
		);
	}

	/**
	 * Update page endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function update_page( $request ) {
		// Feature detection: Check if operation is supported (wp-ai-client pattern).
		$operation_check = Respira_Lite_Feature_Detector::is_operation_supported( 'update_page' );
		if ( is_wp_error( $operation_check ) ) {
			return $operation_check;
		}

		// Feature detection: Check if usage is available (wp-ai-client pattern).
		$usage_check = Respira_Lite_Feature_Detector::is_usage_available();
		if ( is_wp_error( $usage_check ) ) {
			return $usage_check;
		}

		$page_id = $request->get_param( 'id' );
		$post    = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_page_not_found',
				__( 'Respira Lite says: Page not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		$content              = $request->get_param( 'content' );
		$title                = $request->get_param( 'title' );
		$status               = $request->get_param( 'status' );
		$skip_security_check  = $request->get_param( 'skip_security_check' );

		// Validate content if provided and security check not skipped.
		if ( ! empty( $content ) && ! $skip_security_check ) {
			$validation = Respira_Lite_Security::validate_content( $content );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}

		$update_data = array(
			'ID' => $page_id,
		);

		if ( ! empty( $content ) ) {
			$update_data['post_content'] = $content;
		}

		if ( ! empty( $title ) ) {
			$update_data['post_title'] = $title;
		}

		if ( ! empty( $status ) ) {
			$update_data['post_status'] = $status;
		}

		$result = wp_update_post( $update_data, true );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'page_updated',
			$page_id,
			'page',
			get_current_user_id(),
			array(
				'title_changed'   => ! empty( $title ),
				'content_changed' => ! empty( $content ),
				'status_changed'  => ! empty( $status ),
			)
		);

		$updated_post = get_post( $page_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Page updated successfully.', 'respira-for-wordpress-lite' ),
				'warning' => __( 'Lite version edits live pages directly. Upgrade for safety features.', 'respira-for-wordpress-lite' ),
				'data'    => $this->prepare_post_response( $updated_post ),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			200
		);
	}

	/**
	 * Delete page endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function delete_page( $request ) {
		// Check usage limit first.
		if ( Respira_Lite_Usage_Limiter::is_limit_reached() ) {
			return Respira_Lite_Usage_Limiter::get_limit_reached_error();
		}

		$page_id = $request->get_param( 'id' );
		$post    = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_page_not_found',
				__( 'Respira Lite says: Page not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		$result = wp_delete_post( $page_id, true );

		if ( ! $result ) {
			return new WP_Error(
				'respira_lite_delete_failed',
				__( 'Respira Lite says: Failed to delete page.', 'respira-for-wordpress-lite' ),
				array( 'status' => 500 )
			);
		}

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'page_deleted',
			$page_id,
			'page',
			get_current_user_id(),
			array(
				'title' => $post->post_title,
			)
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Page deleted successfully.', 'respira-for-wordpress-lite' ),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			200
		);
	}

	/**
	 * Get posts endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_posts( $request ) {
		$per_page = $request->get_param( 'per_page' ) ?: 10;
		$page     = $request->get_param( 'page' ) ?: 1;
		$search   = $request->get_param( 'search' );
		$status   = $request->get_param( 'status' ) ?: 'any';

		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => $status,
		);

		if ( ! empty( $search ) ) {
			$args['s'] = sanitize_text_field( $search );
		}

		$query = new WP_Query( $args );

		$posts = array();
		foreach ( $query->posts as $post ) {
			$posts[] = $this->prepare_post_response( $post );
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'data'       => $posts,
				'pagination' => array(
					'total'       => $query->found_posts,
					'total_pages' => $query->max_num_pages,
					'current'     => $page,
					'per_page'    => $per_page,
				),
			),
			200
		);
	}

	/**
	 * Get single post endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function get_post( $request ) {
		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'post' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_post_not_found',
				__( 'Respira Lite says: Post not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->prepare_post_response( $post ),
			),
			200
		);
	}

	/**
	 * Update post endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function update_post( $request ) {
		// Feature detection: Check if operation is supported (wp-ai-client pattern).
		$operation_check = Respira_Lite_Feature_Detector::is_operation_supported( 'update_post' );
		if ( is_wp_error( $operation_check ) ) {
			return $operation_check;
		}

		// Feature detection: Check if usage is available (wp-ai-client pattern).
		$usage_check = Respira_Lite_Feature_Detector::is_usage_available();
		if ( is_wp_error( $usage_check ) ) {
			return $usage_check;
		}

		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'post' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_post_not_found',
				__( 'Respira Lite says: Post not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		$content              = $request->get_param( 'content' );
		$title                = $request->get_param( 'title' );
		$status               = $request->get_param( 'status' );
		$skip_security_check  = $request->get_param( 'skip_security_check' );

		// Validate content if provided and security check not skipped.
		if ( ! empty( $content ) && ! $skip_security_check ) {
			$validation = Respira_Lite_Security::validate_content( $content );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}

		$update_data = array(
			'ID' => $post_id,
		);

		if ( ! empty( $content ) ) {
			$update_data['post_content'] = $content;
		}

		if ( ! empty( $title ) ) {
			$update_data['post_title'] = $title;
		}

		if ( ! empty( $status ) ) {
			$update_data['post_status'] = $status;
		}

		$result = wp_update_post( $update_data, true );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'post_updated',
			$post_id,
			'post',
			get_current_user_id(),
			array(
				'title_changed'   => ! empty( $title ),
				'content_changed' => ! empty( $content ),
				'status_changed'  => ! empty( $status ),
			)
		);

		$updated_post = get_post( $post_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Post updated successfully.', 'respira-for-wordpress-lite' ),
				'warning' => __( 'Lite version edits live pages directly. Upgrade for safety features.', 'respira-for-wordpress-lite' ),
				'data'    => $this->prepare_post_response( $updated_post ),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			200
		);
	}

	/**
	 * Delete post endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function delete_post( $request ) {
		// Check usage limit first.
		if ( Respira_Lite_Usage_Limiter::is_limit_reached() ) {
			return Respira_Lite_Usage_Limiter::get_limit_reached_error();
		}

		$post_id = $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post || 'post' !== $post->post_type ) {
			return new WP_Error(
				'respira_lite_post_not_found',
				__( 'Respira Lite says: Post not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		$result = wp_delete_post( $post_id, true );

		if ( ! $result ) {
			return new WP_Error(
				'respira_lite_delete_failed',
				__( 'Respira Lite says: Failed to delete post.', 'respira-for-wordpress-lite' ),
				array( 'status' => 500 )
			);
		}

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'post_deleted',
			$post_id,
			'post',
			get_current_user_id(),
			array(
				'title' => $post->post_title,
			)
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Post deleted successfully.', 'respira-for-wordpress-lite' ),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			200
		);
	}

	/**
	 * Extract builder content endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function extract_builder_content( $request ) {
		$builder = $request->get_param( 'builder' );
		$page_id = $request->get_param( 'page_id' );

		// Gate: Only Gutenberg is supported in Lite version.
		if ( 'gutenberg' !== strtolower( $builder ) ) {
			$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'builder_upgrade' );
			return new WP_Error(
				'respira_lite_builder_not_supported',
				sprintf(
					/* translators: 1: Builder name, 2: Upgrade URL */
					__( 'Respira Lite says: %1$s builder is not supported in the Lite version. Only Gutenberg is available. Upgrade for full builder support: %2$s', 'respira-for-wordpress-lite' ),
					ucfirst( $builder ),
					$upgrade_url
				),
				array(
					'status'      => 403,
					'upgrade_url' => $upgrade_url,
				)
			);
		}

		$post = get_post( $page_id );

		if ( ! $post ) {
			return new WP_Error(
				'respira_lite_page_not_found',
				__( 'Respira Lite says: Page not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		// Load Gutenberg intelligence.
		if ( file_exists( RESPIRA_LITE_PLUGIN_DIR . 'includes/gutenberg-intelligence/class-gutenberg-intelligence-loader.php' ) ) {
			require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/gutenberg-intelligence/class-gutenberg-intelligence-loader.php';
			if ( class_exists( 'Respira_Gutenberg_Intelligence_Loader' ) ) {
				Respira_Gutenberg_Intelligence_Loader::load();
			}
		}

		// Extract blocks if Gutenberg registry is available.
		$blocks = array();
		if ( class_exists( 'Respira_Gutenberg_Block_Registry' ) ) {
			$blocks = Respira_Gutenberg_Block_Registry::extract_blocks( $post->post_content );
		} else {
			// Fallback: return raw content.
			$blocks = parse_blocks( $post->post_content );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Gutenberg content extracted successfully.', 'respira-for-wordpress-lite' ),
				'data'    => array(
					'page_id'   => $page_id,
					'builder'   => 'gutenberg',
					'blocks'    => $blocks,
					'raw_content' => $post->post_content,
				),
			),
			200
		);
	}

	/**
	 * Inject builder content endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function inject_builder_content( $request ) {
		// Check usage limit first.
		if ( Respira_Lite_Usage_Limiter::is_limit_reached() ) {
			return Respira_Lite_Usage_Limiter::get_limit_reached_error();
		}

		$builder = $request->get_param( 'builder' );
		$page_id = $request->get_param( 'page_id' );
		$content = $request->get_param( 'content' );

		// Gate: Only Gutenberg is supported in Lite version.
		if ( 'gutenberg' !== strtolower( $builder ) ) {
			$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'builder_upgrade' );
			return new WP_Error(
				'respira_lite_builder_not_supported',
				sprintf(
					/* translators: 1: Builder name, 2: Upgrade URL */
					__( 'Respira Lite says: %1$s builder is not supported in the Lite version. Only Gutenberg is available. Upgrade for full builder support: %2$s', 'respira-for-wordpress-lite' ),
					ucfirst( $builder ),
					$upgrade_url
				),
				array(
					'status'      => 403,
					'upgrade_url' => $upgrade_url,
				)
			);
		}

		$post = get_post( $page_id );

		if ( ! $post ) {
			return new WP_Error(
				'respira_lite_page_not_found',
				__( 'Respira Lite says: Page not found.', 'respira-for-wordpress-lite' ),
				array( 'status' => 404 )
			);
		}

		// Load Gutenberg intelligence.
		if ( file_exists( RESPIRA_LITE_PLUGIN_DIR . 'includes/gutenberg-intelligence/class-gutenberg-intelligence-loader.php' ) ) {
			require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/gutenberg-intelligence/class-gutenberg-intelligence-loader.php';
			if ( class_exists( 'Respira_Gutenberg_Intelligence_Loader' ) ) {
				Respira_Gutenberg_Intelligence_Loader::load();
			}
		}

		// Validate content if validator is available.
		if ( class_exists( 'Respira_Gutenberg_Validator' ) ) {
			$validation = Respira_Gutenberg_Validator::validate_content( $content );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
		}

		// Security validation.
		$security_validation = Respira_Lite_Security::validate_content( $content );
		if ( is_wp_error( $security_validation ) ) {
			return $security_validation;
		}

		// Update the post.
		$result = wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $content,
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'builder_content_injected',
			$page_id,
			$post->post_type,
			get_current_user_id(),
			array(
				'builder' => $builder,
			)
		);

		$updated_post = get_post( $page_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Gutenberg content injected successfully.', 'respira-for-wordpress-lite' ),
				'warning' => __( 'Lite version edits live pages directly. Upgrade for safety features.', 'respira-for-wordpress-lite' ),
				'data'    => $this->prepare_post_response( $updated_post ),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			200
		);
	}

	/**
	 * Upload media endpoint.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function upload_media( $request ) {
		// Check usage limit first.
		if ( Respira_Lite_Usage_Limiter::is_limit_reached() ) {
			return Respira_Lite_Usage_Limiter::get_limit_reached_error();
		}

		$files = $request->get_file_params();

		if ( empty( $files['file'] ) ) {
			return new WP_Error(
				'respira_lite_no_file',
				__( 'Respira Lite says: No file provided.', 'respira-for-wordpress-lite' ),
				array( 'status' => 400 )
			);
		}

		// Require WordPress file upload handler.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file = $files['file'];

		// Handle the upload.
		$upload = wp_handle_upload(
			$file,
			array( 'test_form' => false )
		);

		if ( isset( $upload['error'] ) ) {
			return new WP_Error(
				'respira_lite_upload_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Respira Lite says: Upload failed: %s', 'respira-for-wordpress-lite' ),
					$upload['error']
				),
				array( 'status' => 500 )
			);
		}

		// Create attachment.
		$attachment = array(
			'post_mime_type' => $upload['type'],
			'post_title'     => sanitize_file_name( basename( $file['name'] ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );

		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}

		// Generate attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// Increment usage counter.
		Respira_Lite_Usage_Limiter::increment();

		// Log the action.
		Respira_Lite_Audit::log(
			'media_uploaded',
			$attach_id,
			'attachment',
			get_current_user_id(),
			array(
				'filename' => basename( $file['name'] ),
				'type'     => $upload['type'],
			)
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Respira Lite says: Media uploaded successfully.', 'respira-for-wordpress-lite' ),
				'data'    => array(
					'id'   => $attach_id,
					'url'  => wp_get_attachment_url( $attach_id ),
					'type' => $upload['type'],
				),
				'usage'   => Respira_Lite_Usage_Limiter::get_usage(),
			),
			201
		);
	}

	/**
	 * Analyze SEO endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function analyze_seo( $request ) {
		// Feature detection: Check if analysis is supported (wp-ai-client pattern).
		return Respira_Lite_Feature_Detector::is_analysis_supported( 'seo' );
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'seo_analysis',
			)
		);
	}

	/**
	 * Analyze performance endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function analyze_performance( $request ) {
		// Feature detection: Check if analysis is supported (wp-ai-client pattern).
		return Respira_Lite_Feature_Detector::is_analysis_supported( 'performance' );
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'performance_analysis',
			)
		);
	}

	/**
	 * Analyze AEO endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function analyze_aeo( $request ) {
		// Feature detection: Check if analysis is supported (wp-ai-client pattern).
		return Respira_Lite_Feature_Detector::is_analysis_supported( 'aeo' );
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'aeo_analysis',
			)
		);
	}

	/**
	 * Analyze accessibility endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function analyze_accessibility( $request ) {
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_upgrade' );

		return new WP_Error(
			'respira_lite_pro_feature',
			sprintf(
				/* translators: %s: Upgrade URL */
				__( 'Respira Lite says: Accessibility analysis is a Pro feature. Upgrade to ensure WCAG compliance and improve site accessibility: %s', 'respira-for-wordpress-lite' ),
				$upgrade_url
			),
			array(
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'accessibility_analysis',
			)
		);
	}

	/**
	 * Get plugins endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function get_plugins( $request ) {
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_upgrade' );

		return new WP_Error(
			'respira_lite_pro_feature',
			sprintf(
				/* translators: %s: Upgrade URL */
				__( 'Respira Lite says: Plugin management is a Pro feature. Upgrade to install, activate, and manage plugins via the API: %s', 'respira-for-wordpress-lite' ),
				$upgrade_url
			),
			array(
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'plugin_management',
			)
		);
	}

	/**
	 * Install plugin endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function install_plugin( $request ) {
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_upgrade' );

		return new WP_Error(
			'respira_lite_pro_feature',
			sprintf(
				/* translators: %s: Upgrade URL */
				__( 'Respira Lite says: Plugin installation is a Pro feature. Upgrade to install plugins via the API: %s', 'respira-for-wordpress-lite' ),
				$upgrade_url
			),
			array(
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'plugin_installation',
			)
		);
	}

	/**
	 * Activate plugin endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function activate_plugin( $request ) {
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_upgrade' );

		return new WP_Error(
			'respira_lite_pro_feature',
			sprintf(
				/* translators: %s: Upgrade URL */
				__( 'Respira Lite says: Plugin activation is a Pro feature. Upgrade to manage plugins via the API: %s', 'respira-for-wordpress-lite' ),
				$upgrade_url
			),
			array(
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'plugin_activation',
			)
		);
	}

	/**
	 * Deactivate plugin endpoint (Pro feature).
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error Error with upgrade message.
	 */
	public function deactivate_plugin( $request ) {
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_upgrade' );

		return new WP_Error(
			'respira_lite_pro_feature',
			sprintf(
				/* translators: %s: Upgrade URL */
				__( 'Respira Lite says: Plugin deactivation is a Pro feature. Upgrade to manage plugins via the API: %s', 'respira-for-wordpress-lite' ),
				$upgrade_url
			),
			array(
				'status'      => 403,
				'upgrade_url' => $upgrade_url,
				'feature'     => 'plugin_deactivation',
			)
		);
	}

	/**
	 * Get collection params for list endpoints.
	 *
	 * @since 1.0.0
	 * @return array Collection parameters.
	 */
	private function get_collection_params() {
		return array(
			'per_page' => array(
				'required'          => false,
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'page'     => array(
				'required'          => false,
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'status'   => array(
				'required'          => false,
				'default'           => 'any',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Prepare post response data.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post Post object.
	 * @return array Prepared post data.
	 */
	private function prepare_post_response( $post ) {
		$author = get_user_by( 'id', $post->post_author );

		return array(
			'id'            => $post->ID,
			'title'         => $post->post_title,
			'content'       => $post->post_content,
			'excerpt'       => $post->post_excerpt,
			'status'        => $post->post_status,
			'type'          => $post->post_type,
			'slug'          => $post->post_name,
			'permalink'     => get_permalink( $post->ID ),
			'created_at'    => $post->post_date,
			'modified_at'   => $post->post_modified,
			'author'        => array(
				'id'   => $post->post_author,
				'name' => $author ? $author->display_name : '',
			),
			'featured_image' => get_post_thumbnail_id( $post->ID ) ? array(
				'id'  => get_post_thumbnail_id( $post->ID ),
				'url' => get_the_post_thumbnail_url( $post->ID, 'full' ),
			) : null,
			'meta'          => get_post_meta( $post->ID ),
		);
	}
}
