<?php // phpcs:ignore
/**
 * Snapshot Credentials
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Authentication;

use WPMUDEV\Snapshot4\Exceptions\Invalid;

/**
 * Credentials class.
 */
class Credentials {

	/**
	 * Stores the username & password
	 *
	 * @var array
	 */
	protected $creds = array();

	/**
	 * Stores other information.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Credentials constructor.
	 *
	 * @param string $username user name.
	 * @param string $password password.
	 */
	public function __construct( string $username, string $password ) {
		if ( ! empty( $username ) ) {
			$this->creds['username'] = $username;
		}

		if ( ! empty( $password ) ) {
			$this->creds['password'] = $password;
		}
	}

	/**
	 * Validates the credential
	 *
	 * @throws Invalid Throws Exception.
	 *
	 * @return boolean
	 */
	public function validate() {
		if ( empty( $this->creds['username'] ) && empty( $this->creds['password'] ) ) {
			throw new Invalid( esc_html__( 'No username and password provided.', 'snapshot' ) );
		}

		if ( empty( $this->creds['username'] ) ) {
			throw new Invalid( esc_html__( 'Username is empty.', 'snapshot' ) );
		}

		if ( empty( $this->creds['password'] ) ) {
			throw new Invalid( esc_html__( 'Password is empty.', 'snapshot' ) );
		}

		return true;
	}

	/**
	 * Get the username.
	 *
	 * @return string
	 */
	public function username() {
		return (string) sanitize_text_field( $this->creds['username'] );
	}

	/**
	 * Get the password.
	 *
	 * @return string
	 */
	public function password() {
		return (string) $this->creds['password'];
	}

	/**
	 * Prepare the username & password for API.
	 *
	 * @return array
	 */
	public function mapped() {
		return array(
			'http_user'     => $this->username(),
			'http_password' => $this->password(),
		);
	}

	/**
	 * Set the data
	 *
	 * @param string $key Key name.
	 * @param mixed  $value Value for key.
	 *
	 * @return \WPMUDEV\Snapshot4\Authentication\Credentials
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;

		return $this;
	}

	/**
	 * Check if data has the key set.
	 *
	 * @param string $key Key name.
	 * @return boolean
	 */
	public function has( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Get the value by key.
	 *
	 * @param string $key Key name.
	 * @return mixed|false
	 */
	public function get( $key ) {
		if ( $this->has( $key ) ) {
			return $this->data[ $key ];
		}

		return false;
	}
}