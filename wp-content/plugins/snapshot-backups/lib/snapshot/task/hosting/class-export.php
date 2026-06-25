<?php // phpcs:ignore
/**
 * Export hosting backup.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Hosting;

/**
 * Export hosting backup requesting class
 */
class Export extends Common {
	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => 'sanitize_text_field',
	);

	/**
	 * Export hosting backup
	 *
	 * @param array $args Arguments coming from the ajax call.
	 * @return array|\WP_Error
	 */
	public function apply( $args = array() ) {
		$backup_id = $args['backup_id'];

		$response = $this->request( 'post', 'backups/test/export', array( 'backup_id' => $backup_id ) );
		if ( ! is_wp_error( $response ) ) {
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			if ( 200 === $response_code ) {
				return json_decode( $response_body, true );
			}
			return new \WP_Error( $response_code, $response_body );
		} else {
			return $response;
		}
	}
}