<?php // phpcs:ignore
/**
 * S3 Destination backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request\Destination;

use WPMUDEV\Snapshot4\Task;

/**
 * S3 Destination backup task class.
 */
class S3 extends Task\Request\Destination {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'tpd_accesskey' => 'sanitize_text_field',
		'tpd_secretkey' => 'sanitize_text_field',
		'tpd_region'    => 'sanitize_text_field',
		'tpd_action'    => 'sanitize_text_field',
	);

	/**
	 * Constructor
	 *
	 * @param string $action Action to be performed for the destination.
	 * @param string $tpd_type Connection type.
	 */
	public function __construct( $action, $tpd_type = '' ) {
		if ( 'test_connection_final' === $action ) {
			$required_params            = $this->required_params;
			$additional_required_params = array(
				'tpd_path'  => 'sanitize_text_field',
				'tpd_name'  => 'sanitize_text_field',
				'tpd_limit' => 'intval',
				'tpd_save'  => 'intval',
				'tpd_type'  => 'sanitize_text_field',
			);

			if ( 's3_other' === $tpd_type ) {
				$additional_required_params['tpd_endpoint'] = 'esc_url_raw';
			}

			$this->required_params = array_merge( $required_params, $additional_required_params );
		} elseif ( 'load_buckets' === $action ) {
			switch ( $tpd_type ) {
				case 'aws':
					$this->required_params['tpd_bucket'] = 'sanitize_text_field';
					break;
				case 'backblaze':
					$this->required_params['tpd_bucketname'] = 'sanitize_text_field';
					break;
				case 's3_other':
					$this->required_params['tpd_endpoint'] = 'esc_url_raw';
					break;
			}
		}//end if
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
			case 'load_buckets':
				$response = $this->load_buckets( $request_model, $args );
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
	 * Load buckets from AWS S3 based on provided credentials and options.
	 *
	 * @param object $request_model Request model instance.
	 * @param array  $args Arguments from ajax call containing credentials and options.
	 * @return mixed Response from request model's load_buckets method
	 */
	protected function load_buckets( $request_model, $args ) {
		$data = array(
			'tpd_accesskey' => $args['tpd_accesskey'],
			'tpd_secretkey' => $args['tpd_secretkey'],
			'tpd_region'    => $args['tpd_region'],
			'tpd_type'      => $args['tpd_type'],
		);

		if ( 'aws' === $args['tpd_type'] ) {
			$data['tpd_bucket'] = $args['tpd_bucket'];
		}

		if ( 'backblaze' === $args['tpd_type'] ) {
			$data['tpd_bucketname'] = $args['tpd_bucketname'];
		}

		if ( 's3_other' === $args['tpd_type'] ) {
			$data['tpd_endpoint'] = $args['tpd_endpoint'];
		}

		return $request_model->load_buckets( $data );
	}

	/**
	 * Test the final S3 connection with provided credentials and options.
	 *
	 * @param object $request_model Request model instance.
	 * @param array  $args Arguments from ajax call containing credentials and options.
	 * @return mixed Response from request model's test_connection_final method
	 */
	protected function test_connection_final( $request_model, $args ) {
		$data = array(
			'aws_storage'   => 1,
			'tpd_accesskey' => $args['tpd_accesskey'],
			'tpd_secretkey' => $args['tpd_secretkey'],
			'tpd_region'    => $args['tpd_region'],
			'tpd_path'      => $args['tpd_path'],
			'tpd_name'      => $args['tpd_name'],
			'tpd_limit'     => $args['tpd_limit'],
			'tpd_save'      => $args['tpd_save'],
			'tpd_type'      => $args['tpd_type'],
		);

		if ( isset( $args['tpd_endpoint'] ) && ! empty( $args['tpd_endpoint'] ) ) {
			$data['tpd_endpoint'] = $args['tpd_endpoint'];
		}

		return $request_model->test_connection_final( $data );
	}
}