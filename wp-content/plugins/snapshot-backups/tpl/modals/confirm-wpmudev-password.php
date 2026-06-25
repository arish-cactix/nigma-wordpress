<?php // phpcs:ignore
/**
 * Modal for confirmation WPMU DEV password.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Model\Env;

$assets = new Helper\Assets();

$wpmudev_user_email = Api::get_dashboard_profile_username();
$hub_link           = 'https://wpmudev.com/hub2/site/' . Api::get_site_id() . '/backups/settings';

?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="snapshot-confirm-wpmudev-password-modal"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="snapshot-confirm-wpmudev-password-modal-title"
		aria-describedby="snapshot-confirm-wpmudev-password-modal-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-sides--30">
				<figure class="sui-box-logo" aria-hidden="true">
					<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/modal-header-wpmudev.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-header-wpmudev.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-header-wpmudev@2x.png' ) ); ?> 2x"
					/>
				</figure>
				<button type="button" class="sui-button-icon sui-button-float--right snapshot-modal--close" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal', 'snapshot' ); ?></span>
				</button>
				<h3 id="snapshot-confirm-wpmudev-password-modal-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Confirm WPMU DEV Password', 'snapshot' ); ?></h3>
				<p id="snapshot-confirm-wpmudev-password-modal-description">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s - WPMU DEV user email */
							__( 'As a security measure, you\'ll need to confirm your WPMU DEV account to continue. Please enter the password for your connected account <strong>(%s)</strong> or authenticate with Google.', 'snapshot' ),
							$wpmudev_user_email
						)
					);
					?>
				</p>
			</div>

			<div class="sui-box-body pb-0 d-none snapshot-login--method__error">
				<div role="alert" id="inline-notice-login-method-error" class="sui-notice sui-notice-red sui-active" aria-live="assertive" style="display: block;">
					<div class="sui-notice-content sui-content-left">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p></p>
						</div>
					</div>
				</div>
			</div>

			<div class="sui-box-body sui-content-center">

				<form id="snapshot-confirm-wpmudev-password-modal-form">
					<div class="sui-form-field">
						<label for="snapshot-wpmudev-password" id="snapshot-wpmudev-password-label" class="sui-label">
							<?php echo esc_html( __( 'WPMU DEV Password', 'snapshot' ) ); ?>
						</label>

						<div class="sui-with-button sui-with-button-icon">
							<input
								placeholder="<?php esc_attr_e( 'Enter your WPMU DEV password', 'snapshot' ); ?>"
								id="snapshot-wpmudev-password"
								class="sui-form-control"
								type="password"
								name="wpmudev_password"
								aria-labelledby="snapshot-wpmudev-password-label"
							/>
							<button type="button" class="sui-button-icon">
								<span aria-hidden="true" class="sui-icon-eye"></span>
								<span class="sui-password-text sui-screen-reader-text"><?php esc_html_e( 'Show Password', 'snapshot' ); ?></span>
								<span class="sui-password-text sui-screen-reader-text sui-hidden"><?php esc_html_e( 'Hide Password', 'snapshot' ); ?></span>
							</button>
						</div>

						<span id="error-snapshot-wpmudev-password" class="sui-error-message" style="display: none; text-align: right;" role="alert"><?php esc_html_e( 'The password you entered is incorrect. Please try again.', 'snapshot' ); ?></span>
					</div>

					<button type="submit" class="sui-button sui-button-icon-right submit-button">
						<span class="sui-loading-text"><?php esc_html_e( 'Continue', 'snapshot' ); ?><span class="sui-icon-chevron-right" aria-hidden="true"></span></span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</form>

				<div class="snapshot-google-login">
					<hr class="sui-separator">

					<div class="snapshot-google-alt">
						<p style="font-size: 13px;"><?php esc_html_e( 'Don\'t have a WPMU DEV password?', 'snapshot' ); ?> <strong><?php esc_html_e( 'Authenticate with Google instead.', 'snapshot' ); ?></strong></p>
					</div>

					<form action="<?php echo esc_url( Env::get_wpmu_api_server_url() ); ?>api/dashboard/v2/google-auth" id="snapshot-google-login" method="POST">
						<div class="sui-form-field"></div>

						<?php
						$nonce = wp_create_nonce( 'snapshot-google-login-nonce' );
						?>
						<input type="hidden" name="redirect_url" value="
						<?php
						echo esc_url(
							Env::get_current_page_url(
								array(
									'ref_nonce' => $nonce,
									'referer'   => 'google_login',
								)
							)
						);
						?>
						">
						<input type="hidden" name="domain" value="<?php echo esc_url( Api::get_site_url() ); ?>">
						<input type="hidden" name="context" value="snapshot">
						<input type="hidden" name="what" value="">
						<button class="sui-button snapshot-google-login-button" type="submit">
							<span class="icon-google-color-svg" style="position: relative; top: 2px; margin-right: 5px;">
								<svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2.58694 6.00004C2.58694 5.61032 2.65163 5.23663 2.76722 4.8862L0.745031 3.34204C0.350906 4.1422 0.128906 5.04388 0.128906 6.00004C0.128906 6.95545 0.350719 7.85648 0.744187 8.65617L2.76525 7.10901C2.65078 6.76017 2.58694 6.38788 2.58694 6.00004Z" fill="#FBBC05"/>
									<path d="M6.1385 2.45456C6.98516 2.45456 7.74988 2.75456 8.35072 3.24544L10.0986 1.5C9.0335 0.572719 7.66794 0 6.1385 0C3.764 0 1.72325 1.35787 0.746094 3.342L2.76819 4.88616C3.23413 3.47184 4.56228 2.45456 6.1385 2.45456Z" fill="#EA4335"/>
									<path d="M6.1385 9.54536C4.56238 9.54536 3.23422 8.52808 2.76828 7.11377L0.746094 8.65764C1.72325 10.6421 3.764 11.9999 6.1385 11.9999C7.604 11.9999 9.00322 11.4795 10.0533 10.5045L8.13387 9.02064C7.59228 9.3618 6.91025 9.54536 6.1385 9.54536Z" fill="#34A853"/>
									<path d="M11.8721 6.00005C11.8721 5.64549 11.8174 5.26365 11.7355 4.90918H6.13672V7.22734H9.35947C9.19831 8.01774 8.75975 8.62534 8.13209 9.02077L10.0515 10.5046C11.1546 9.4809 11.8721 7.95577 11.8721 6.00005Z" fill="#4285F4"/>
								</svg>
							</span>
							<?php esc_html_e( 'Authenticate with Google', 'snapshot' ); ?>
						</button>
					</form>
				</div>

				<?php /* translators: %s - Link to the Hub */ ?>
				<p class="sui-description" style="margin-bottom: 0;margin-top: 32px;"><?php echo wp_kses_post( sprintf( __( 'You can enable/disable this password protection step from <a href="%s" target="_blank">The Hub</a>.', 'snapshot' ), $hub_link ) ); ?></p>

			</div>
		</div>
	</div>
</div>