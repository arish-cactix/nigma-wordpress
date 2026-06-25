<?php // phpcs:ignore
/**
 * Azure Destination backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request\Destination;

use WPMUDEV\Snapshot4\Task;

/**
 * Azure Destination backup task class.
 */
class Azure extends Task\Request\Destination {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'tpd_secretkey' => 'sanitize_text_field',
		'tpd_accesskey' => 'sanitize_text_field',
	);

	/**
	 * Constructor
	 *
	 * @param string $action Action to be performed for the destination.
	 */
	public function __construct( $action ) {
		if ( 'test_connection_final' === $action ) {
			$required_params            = $this->required_params;
			$additional_required_params = array(
				'tpd_path'  => 'sanitize_text_field',
				'tpd_limit' => 'intval',
				'tpd_save'  => 'intval',
				'tpd_type'  => 'sanitize_text_field',
			);

			$this->required_params = array_merge( $required_params, $additional_required_params );
		}
	}

	/**
	 * Request for destination.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$request_model = $args['request_model'];

		$ok_codes      = $request_model->get( 'ok_codes' );
		$empty_for_404 = false;

		switch ( $args['tpd_action'] ) {
			case 'load_containers':
				$response = $this->load_containers( $request_model, $args );
				break;
			case 'test_connection_final':
				$response = $this->test_connection_final( $request_model, $args );
				break;
		}

		$request_model->set( 'ok_codes', $ok_codes );

		$request_model->add_errors( $this );

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $empty_for_404 && 404 === wp_remote_retrieve_response_code( $response ) ) {
			$result = array();
		}

		return $result;
	}

	/**
	 * Load containers from Azure based on provided credentials and options.
	 *
	 * @param object $request_model Request model instance.
	 * @param array  $args Arguments from ajax call containing credentials and options.
	 * @return mixed Response from request model's load_containers method
	 */
	protected function load_containers( $request_model, $args ) {
		$data = array(
			'tpd_accesskey' => $args['tpd_accesskey'],
			'tpd_secretkey' => $args['tpd_secretkey'],
			'tpd_type'      => $args['tpd_type'],
		);

		return $request_model->load_containers( $data );
	}

	/**
	 * Test the final Azure connection with provided credentials and options.
	 *
	 * @param object $request_model Request model instance.
	 * @param array  $args Arguments from ajax call containing credentials and options.
	 * @return mixed Response from request model's test_connection_final method
	 */
	protected function test_connection_final( $request_model, $args ) {
		$data = array(
			'tpd_accesskey' => $args['tpd_accesskey'],
			'tpd_secretkey' => $args['tpd_secretkey'],
			'tpd_path'      => $args['tpd_path'],
			'tpd_name'      => $args['tpd_name'],
			'tpd_limit'     => $args['tpd_limit'],
			'tpd_type'      => $args['tpd_type'],
			'tpd_save'      => $args['tpd_save'],
			'aws_storage'   => 1,
		);

		return $request_model->test_connection_final( $data );
	}
}