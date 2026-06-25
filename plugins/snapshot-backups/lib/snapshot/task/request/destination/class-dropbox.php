<?php // phpcs:ignore
/**
 * Dropbox Destination backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request\Destination;

use WPMUDEV\Snapshot4\Task;

/**
 * Dropbox Destination backup task class.
 */
class Dropbox extends Task\Request\Destination {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'tpd_auth_code' => 'sanitize_text_field',
		'tpd_action'    => 'sanitize_text_field',
	);

	/**
	 * Constructor
	 *
	 * @param string $action Action to be performed for the destination.
	 */
	public function __construct( $action ) {
		if ( 'test_connection_final' === $action ) {
			$this->required_params = array(
				'tpd_acctoken_gdrive' => 'sanitize_text_field',
				'tpd_retoken_gdrive'  => 'sanitize_text_field',
				'tpd_email_gdrive'    => 'sanitize_text_field',
				'tpd_path'            => 'sanitize_text_field',
				'tpd_name'            => 'sanitize_text_field',
				'tpd_limit'           => 'intval',
				'tpd_type'            => 'sanitize_text_field',
				'tpd_save'            => 'intval',
				'tpd_action'          => 'sanitize_text_field',
			);
		}
	}

	/**
	 * Request for destination.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		/**
		 * Destination request model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Request\Destination\Dropbox
		 */
		$request_model = $args['request_model'];

		$ok_codes      = $request_model->get( 'ok_codes' );
		$empty_for_404 = false;
		switch ( $args['tpd_action'] ) {
			case 'generate_tokens':
				$data     = array(
					'tpd_auth_code' => $args['tpd_auth_code'],
					'tpd_type'      => $args['tpd_type'],
				);
				$response = $request_model->generate_tokens( $data );

				break;
			case 'test_connection_final':
				$data     = array(
					'aws_storage'         => 1,
					'tpd_acctoken_gdrive' => $args['tpd_acctoken_gdrive'],
					'tpd_retoken_gdrive'  => $args['tpd_retoken_gdrive'],
					'tpd_email_gdrive'    => $args['tpd_email_gdrive'],
					'tpd_path'            => $args['tpd_path'],
					'tpd_name'            => $args['tpd_name'],
					'tpd_limit'           => $args['tpd_limit'],
					'tpd_save'            => $args['tpd_save'],
					'tpd_type'            => $args['tpd_type'],
				);
				$response = $request_model->test_connection_final( $data );
				break;
			default:
				break;
		}//end switch
		$request_model->set( 'ok_codes', $ok_codes );

		$request_model->add_errors( $this );

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $empty_for_404 && 404 === wp_remote_retrieve_response_code( $response ) ) {
			$result = array();
		}

		return $result;
	}
}