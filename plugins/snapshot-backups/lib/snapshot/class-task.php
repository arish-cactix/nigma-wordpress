<?php // phpcs:ignore
/**
 * Tasks reusable, are atomic units of work in Snapshot.
 *
 * A task performs actions on data in response to controller actions.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Task abstraction class
 */
abstract class Task {

	const ERR_STRING_REQUEST_PARAMS = 'Request was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array();

	/**
	 * Task main entry point - actually runs the task
	 *
	 * @param array $args Optional task arguments (if any).
	 *
	 * @return mixed Task-dependent return value
	 */
	abstract public function apply( $args = array() );

	/**
	 * Holds a list of errors encoutered during task execution
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Gets current errors list
	 *
	 * @return array
	 */
	public function get_errors() {
		return (array) $this->errors;
	}

	/**
	 * Checks whether we had any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Clears errors storage
	 *
	 * @return object Task instance
	 */
	public function clear_errors() {
		$this->errors = array();
		return $this;
	}

	/**
	 * Adds a new execution error
	 *
	 * @param string $err Error suffix to be added to error type.
	 * @param string $msg Optional error message.
	 * @param array  $data Optional error data.
	 *
	 * @return object Task instance
	 */
	public function add_error( $err, $msg = '', $data = array() ) {
		$cls = get_called_class();

		$error          = new \WP_Error(
			$cls . Model::SCOPE_DELIMITER . $err,
			$msg,
			$data
		);
		$this->errors[] = $error;

		return $this;
	}

	/**
	 * Checks if the necessary params are coming from the request.
	 * Also sanitizes them.
	 *
	 * Logs an error if they don't exist or are invalid.
	 *
	 * @param object $data The data coming from the request.
	 *
	 * @return array|\WP_Error
	 */
	public function validate_request_data( $data ) {
		// Make sure we got _some_ data.
		if ( ! $data ) {
			$error = new \WP_Error( 'no_content', 'Empty or invalid request body.' );
			/* translators: %s - Request was not successful */
			Log::error( sprintf( __( '%s - An empty or invalid request body.', 'snapshot' ), static::ERR_STRING_REQUEST_PARAMS ) );
			return $error;
		}

		foreach ( $this->required_params as $required_param => $sanitize_func ) {
			$data[ $required_param ] = isset( $data[ $required_param ] ) ? $data[ $required_param ] : null;

			// Make sure we got _everything_ we need.
			if ( ! isset( $data[ $required_param ] ) || is_null( $data[ $required_param ] ) ) {
				$error = new \WP_Error( 'no_' . $required_param, $required_param . ' is required and was not included in the request.' );
				/* translators: %s - Request was not successful */
				Log::error( sprintf( __( '%1$s - A %2$s parameter must be provided.', 'snapshot' ), static::ERR_STRING_REQUEST_PARAMS, $required_param ) );
				return $error;
			}

			// Sanitize 'em, if you got 'em.
			if ( ! is_null( $sanitize_func ) && call_user_func( $sanitize_func, $data[ $required_param ] ) !== $data[ $required_param ] ) {
				$error = new \WP_Error( 'unsanitized_' . $required_param, $required_param . ' is not valid.' );
				/* translators: %s - Request was not successful */
				Log::error( sprintf( __( '%1$s - The %2$s parameter is not valid.', 'snapshot' ), static::ERR_STRING_REQUEST_PARAMS, $required_param ) );
				return $error;
			}
		}

		return $data;
	}
}