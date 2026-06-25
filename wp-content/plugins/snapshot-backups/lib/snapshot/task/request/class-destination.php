<?php // phpcs:ignore
/**
 * Destination backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;

/**
 * Destination backup task class.
 */
class Destination extends Task {
	const ERR_SERVICE_UNREACHABLE = 'snapshot_destination_service_unreachable';

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
	 * @param string $type   Type of the destination (S3, Google Drive, etc.).
	 */
	public function __construct( $action, $type = '' ) {
		if ( 'get_destinations' === $action ) {
			$this->required_params = array(
				'tpd_action' => 'sanitize_text_field',
			);
		} elseif ( 'delete_destination' === $action ) {
			$this->required_params = array(
				'tpd_id' => 'sanitize_text_field',
			);
		} elseif ( 'update_destination' === $action ) {
			if ( 'gdrive' === $type || 'dropbox' === $type || 'ftp' === $type || 'sftp' === $type ) {
				$this->required_params = array(
					'tpd_id'    => 'sanitize_text_field',
					'tpd_name'  => 'sanitize_text_field',
					'tpd_path'  => 'sanitize_text_field',
					'tpd_limit' => 'sanitize_text_field',
				);
			} elseif ( 'onedrive' === $type ) {
				$this->required_params = array(
					'tpd_id'    => 'sanitize_text_field',
					'tpd_name'  => 'sanitize_text_field',
					'tpd_limit' => 'sanitize_text_field',
					'tpd_path'  => 'sanitize_text_field',
				);
			} else {
				$extra = array(
					'tpd_id'    => 'sanitize_text_field',
					'tpd_name'  => 'sanitize_text_field',
					'tpd_path'  => 'sanitize_text_field',
					'tpd_limit' => 'sanitize_text_field',
				);

				if ( 's3_other' === $type ) {
					$extra['tpd_endpoint'] = 'esc_url_raw';
				}

				$this->required_params = array_merge(
					$this->required_params,
					$extra,
				);

			}//end if
		} elseif ( 'activate_destination' === $action ) {
			$this->required_params = array(
				'tpd_id'      => 'sanitize_text_field',
				'aws_storage' => 'intval',
			);
		} elseif ( 'delete_all_destinations' === $action ) {
			$this->required_params = array();
		}//end if
	}

	/**
	 * Request for destination.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		/**
		 * Destination request model.
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Request\Destination
		 */
		$request_model = $args['request_model'];

		$ok_codes      = $request_model->get( 'ok_codes' );
		$empty_for_404 = false;

		$meta = array();
		if ( isset( $args['ftp_mode'] ) ) {
			$meta['ftp_mode'] = $args['ftp_mode'];
		}

		if ( isset( $args['ftp_timeout'] ) ) {
			$meta['ftp_timeout'] = $args['ftp_timeout'];
		}

		if ( isset( $args['ftp_port'] ) ) {
			$meta['ftp_port'] = $args['ftp_port'];
		}

		switch ( $args['tpd_action'] ) {
			case 'get_destinations':
				$empty_for_404 = true;
				$request_model->set( 'ok_codes', array( 404 ) );
				$response = $request_model->get_destinations();
				break;
			case 'delete_destination':
				$response = $request_model->delete_destination( $args['tpd_id'] );
				break;
			case 'update_destination':
				$response = $request_model->update_destination(
					$args['tpd_id'],
					$args['tpd_accesskey'],
					$args['tpd_secretkey'],
					$args['tpd_region'],
					$args['tpd_path'],
					$args['tpd_name'],
					$args['tpd_limit'],
					$args['tpd_type'],
					$args['tpd_endpoint'] ?? '',
					$meta
				);
				break;
			case 'activate_destination':
				$response = $request_model->activate_destination( $args['tpd_id'], $args['aws_storage'] );
				break;
			case 'delete_all_destinations':
				$empty_for_404 = true;
				$request_model->set( 'ok_codes', array( 404 ) );
				$response = $request_model->delete_all_destinations();
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