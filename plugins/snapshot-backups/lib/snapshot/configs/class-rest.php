<?php
/**
 * Registers custom REST routes for Snapshot Configs.
 *
 * @since   4.5.0
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Configs;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Configs REST class.
 */
class Rest {

	/**
	 * Stores the singleton instance of this class.
	 *
	 * @var \WPMUDEV\Snapshot4\Configs\Rest|null
	 */
	protected static $instance = null;

	/**
	 * REST version.
	 *
	 * @var string
	 */
	public $api_version = 'v1';

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'snapshot';

	/**
	 * Configs per page.
	 *
	 * @var integer
	 */
	public $items = 10;

	/**
	 * Rest API endpoint.
	 *
	 * @var string
	 */
	public $rest_base = 'preset_configs';

	/**
	 * Dummy constructor.
	 */
	public function __construct() {}

	/**
	 * Boot Up the class.
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return \WPMUDEV\Snapshot4\Configs\Rest
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register the routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			'/test',
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'api_test_route' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$this->get_namespace(),
			"/{$this->rest_base}",
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => array( $this, 'current_user_can' ),
					'args'                => array(
						'offset' => array(
							'description' => __( 'Offset the result set by a specific number of items.', 'snapshot' ),
							'type'        => 'integer',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'store' ),
					'permission_callback' => array( $this, 'current_user_can' ),
				),
			)
		);
	}

	/**
	 * Check for user permissions.
	 *
	 * @return bool
	 */
	public function current_user_can() {
		$permission = is_multisite() ? 'manage_network' : 'manage_options';
		return current_user_can( $permission );
	}

	/**
	 * Handles /test endpoint.
	 *
	 * @return mixed
	 */
	public function api_test_route() {
		return rest_ensure_response( array( 'status' => true ) );
	}

	/**
	 * Lists all the configs.
	 *
	 * @param WP_REST_Request $request REST Request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function index( $request ) { //phpcs:ignore
		$config  = Config::get_instance();
		$presets = $config->all();

		if ( false === $presets ) {
			// We need default config at least.
			$presets = array( $config->get_default_config() );
			$config->set( $presets );
		}

		return $presets;
	}

	/**
	 * Stores the config.
	 *
	 * @param WP_REST_Request $request The REST API request.
	 *
	 * @return mixed
	 */
	public function store( $request ) {
		$data = json_decode( $request->get_body(), true );

		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'400',
				esc_html__( 'Missing configs data', 'snapshot' ),
				array( 'status' => 400 )
			);
		}

		update_site_option( 'snapshot-preset_configs', $data );
		return $data;
	}

	/**
	 * Snapshot API REST namespace.
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace . '/' . $this->api_version;
	}
}