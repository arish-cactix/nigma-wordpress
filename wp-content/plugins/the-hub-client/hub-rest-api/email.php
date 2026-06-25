<?php

class WPMUDEV_HUB_HUB_REST_API_Email {
	/**
	 * @var null|WP_Error
	 */
	private $email_failed_error = null;
	private $from_name          = null;
	private $attachments        = array();

	public function __construct() {
		add_filter( 'wdp_register_hub_action', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints( $actions ) {
		$actions = is_array( $actions ) ? $actions : array();

		$actions['hub_client_send_email'] = array( $this, 'process' );

		return $actions;
	}

	public function hook_email() {
		add_action( 'wp_mail_failed', array( $this, 'log_email_failed' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
	}

	public function unhook_email() {
		remove_action( 'wp_mail_failed', array( $this, 'log_email_failed' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
	}

	/**
	 * @param WP_Error $wp_error
	 *
	 * @return bool
	 */
	public function log_email_failed( $wp_error ) {
		if ( ! is_a( $wp_error, 'WP_Error' ) ) {
			return false;
		}

		$this->email_failed_error = $wp_error;

		return $this->maybe_log( $wp_error->get_error_code() . ' - ' . $wp_error->get_error_message() );
	}

	public function mail_from_name( $from ) {
		if ( ! $this->from_name ) {
			return $from;
		}

		return $this->from_name;
	}

	// the callback spec requires these unused params
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	/**
	 * @param object                        $params
	 * @param string                        $action
	 * @param bool|WPMUDEV_Dashboard_Remote $remote
	 */
	public function process( $params, $action, $remote = false ) {
		$params = (array) $params;

		$sent = $this->send( $params );

		// remove attachments file
		if ( $this->attachments ) {
			foreach ( $this->attachments as $attachment_path ) {
				wp_delete_file( $attachment_path );
			}
		}

		if ( is_wp_error( $sent ) ) {
			$this->maybe_log( $sent->get_error_code() . ' - ' . $sent->get_error_message() );
			wp_send_json_error(
				array(
					'code'    => $sent->get_error_code(),
					'message' => $sent->get_error_message(),
					'data'    => $sent->get_error_data(),
				)
			);
		}

		wp_send_json_success( $sent );
	}

	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	public function send( $params ) {
		$app_name = WPMUDEV_HUB_Plugin::get_customization_app_name();

		// setup defaults
		$params = wp_parse_args(
			$params,
			array(
				'subject'     => $app_name,
				'headers'     => '',
				'attachments' => array(),
				'vars'        => array(),
				'from_name'   => '',
				'message'     => '',
			)
		);

		$email_id = isset( $params['id'] ) ? $params['id'] : '';
		$to       = isset( $params['to'] ) ? $params['to'] : '';

		if ( ! $email_id ) {
			return new WP_Error( 'unrecognized_email_id', sprintf( 'Unrecognized or empty email_id : %s', $email_id ) );
		}

		if ( ! $to ) {
			return new WP_Error( 'unrecognized_email_to', sprintf( 'Unrecognized or empty email_to : %s', $to ) );
		}

		if ( $params['from_name'] ) {
			$this->from_name = $params['from_name'];
		}

		if ( isset( $params['attachments'] ) && $params['attachments'] ) {
			$params['attachments'] = (array) $params['attachments'];
			// PHP-mailer need path for attachments, so lets decode and save attachment to file
			foreach ( $params['attachments'] as $attachment ) {
				$attachment = (array) $attachment;
				if ( isset( $attachment['name'], $attachment['encoded_content'] ) && $attachment['name'] && $attachment['encoded_content'] ) {
					$file_path = get_temp_dir() . $attachment['name'];
					// the content is encoded from Hub, and safely decoded
					$written = file_put_contents( $file_path, base64_decode( $attachment['encoded_content'] ) );//phpcs:ignore
					if ( false === $written ) {
						return new WP_Error( 'attachment_write_failed', "Failed to write attachment to $file_path." );
					}
					$this->attachments[] = $file_path;
				}
			}
		}

		$params = $this->maybe_override_email_params( $params );

		/**
		 * Filter whether to send email or not
		 *
		 * @param bool  $is_send whether to send email or not
		 * @param array $params  email parameters
		 *
		 * @since 1.0.0
		 */
		$send = apply_filters( 'wpmudev_hub_email_send', true, $params );
		if ( $send ) {
			$this->hook_email();

			/**
			 * Fires before sending email
			 *
			 * @param array $params email parameters
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpmudev_hub_before_send_email', $params );

			$sent = wp_mail( $to, $params['subject'], $params['message'], $params['headers'], $this->attachments );
			$this->unhook_email();

			/**
			 * Fires after email send
			 *
			 * @param bool  $sent   whether email is successfully sent
			 * @param array $params email parameters
			 *
			 * @since 1.0.0
			 */
			do_action( 'wpmudev_hub_after_send_email', $sent, $params );

			if ( ! $sent ) {
				$error_detail = $this->email_failed_error && is_wp_error( $this->email_failed_error ) ? $this->email_failed_error : new WP_Error( 'unknown_error', 'Unknown Error' );

				return new WP_Error( 'mail_send_failed', sprintf( 'Unable to send email via %1$s: %2$s. Please contact site owner.', network_site_url(), $error_detail->get_error_message() ) );
			}
		} else {
			$this->maybe_log( 'Email sending overridden by other plugins / themes.' );
		}

		return array(
			'id'   => $email_id,
			'sent' => true,
		);
	}

	public function maybe_override_email_params( $params ) {
		$original_params = $params;
		$app_name        = WPMUDEV_HUB_Plugin::get_customization_app_name();
		$needs_override  = false;
		$params['vars']  = isset( $params['vars'] ) ? (array) $params['vars'] : array();

		// override from_name
		if ( $app_name ) {
			$this->from_name = sanitize_text_field( $app_name );

			if ( isset( $params['vars']['team_name'] ) ) {
				$needs_override              = true;
				$params['vars']['team_name'] = sanitize_text_field( $app_name );
			}
		}

		if ( $needs_override ) {

			// only do this when *_template available, so it can work interop with old version upstreams
			if ( isset( $params['subject_template'], $params['message_template'], $params['wrapper_template'] ) ) {
				// rebuild the message and subject
				if ( isset( $params['vars'] ) && is_array( $params['vars'] ) ) {
					$params['subject'] = $params['subject_template'];
					$params['message'] = $params['message_template'];
					foreach ( $params['vars'] as $key => $value ) {
						$var               = sprintf( '{%s}', strtoupper( $key ) );
						$params['subject'] = str_ireplace( $var, $value, $params['subject'] );
						$params['message'] = str_ireplace( $var, $value, $params['message'] );
					}
				}

				// wrapper
				$wrapper = $params['wrapper_template'];
				foreach ( array( 'subject', 'message' ) as $key ) {
					$var     = sprintf( '{%s}', strtoupper( $key ) );
					$value   = $params[ $key ];
					$wrapper = str_ireplace( $var, $value, $wrapper );
				}

				$params['message'] = $wrapper;
			}
		}

		/**
		 * Filter overridden email params
		 *
		 * team_name param is overridden based on the Hub Client Settings
		 *
		 * @param array $overidden_params Overridden Params
		 * @param array $original_params  Original Params before getting overridden
		 *
		 * @since 1.0.5
		 */
		$params = apply_filters( 'wpmudev_hub_email_override_params', $params, $original_params );

		return $params;
	}

	protected function maybe_log( $message ) {
		if ( defined( 'WPMUDEV_API_DEBUG_ALL' ) && WPMUDEV_API_DEBUG_ALL ) {
			// only on debug mode
			error_log( 'WPMUDEV_HUB_HUB_REST_API_Email: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			return true;
		}

		return false;
	}
}

new WPMUDEV_HUB_HUB_REST_API_Email();