<?php // phpcs:ignore

/**
 * S/FTP Destination backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request\Destination;

use WPMUDEV\Snapshot4\Task;

/**
 * FTP destination backup request class.
 *
 * @since 4.6.0
 */
class Ftp extends Task\Request\Destination {

	/**
	 * Required request parameters, with their sanitization method.
	 *
	 * @var array
	 */
	protected $required_params = array(
		'tpd_accesskey' => 'sanitize_text_field',
		'tpd_secretkey' => 'sanitize_text_field',
		'tpd_action'    => 'sanitize_text_field',
		'ftp_host'      => 'sanitize_text_field',
		'ftp_port'      => 'intval',
	);

	/**
	 * \WPMUDEV\Snapshot4\Task\Request\Destination\FTP constructor.
	 *
	 * @param string $action Action to be performed for the destination.
	 */
	public function __construct( $action ) {
		if ( 'test_connection_final' === $action ) {
			$this->required_params = array(
				'tpd_accesskey' => 'sanitize_text_field',
				'tpd_secretkey' => 'sanitize_text_field',
				'tpd_action'    => 'sanitize_text_field',
				'tpd_name'      => 'sanitize_text_field',
				'tpd_type'      => 'sanitize_text_field',
				'tpd_limit'     => 'intval',
				'tpd_save'      => 'intval',
				'ftp_host'      => 'sanitize_text_field',
				'ftp_port'      => 'intval',
			);
		}
	}

	/**
	 * Apply the task for FTP destination.
	 *
	 * @param array $args Arguments coming from the AJAX call.
	 *
	 * @return array
	 */
	public function apply( $args = array() ) {
		/**
		 * FTP destination model.
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Request\Destination\Ftp
		 */
		$request_model = $args['request_model'];

		$ok_codes = $request_model->get( 'ok_codes' );

		$empty_for_404 = false;

		if ( ! isset( $args['tpd_action'] ) ) {
			return array();
		}

		if ( 'test_connection_final' !== $args['tpd_action'] ) {
			return array();
		}

		$data = array(
			'aws_storage'      => 1,
			'tpd_accesskey'    => $args['tpd_accesskey'],
			'tpd_secretkey'    => $args['tpd_secretkey'],
			'tpd_path'         => $args['tpd_path'],
			'tpd_name'         => $args['tpd_name'],
			'tpd_limit'        => $args['tpd_limit'],
			'tpd_save'         => $args['tpd_save'],
			'tpd_type'         => $args['tpd_type'],
			'ftp_host'         => $args['ftp_host'],
			'ftp_port'         => $args['ftp_port'],
			'ftp_timeout'      => $args['ftp_timeout'],
			'ftp_passive_mode' => $args['ftp_mode'],
		);

		$response = $request_model->test_connection_final( $data );

		$request_model->set( 'ok_codes', $ok_codes );
		$request_model->add_errors( $this );

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $empty_for_404 && 404 === wp_remote_retrieve_response_code( $response ) ) {
			$result = array();
		}

		return $result;
	}
}