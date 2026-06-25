<?php // phpcs:ignore
/**
 * Settings page.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Env;

$is_wpmu_hosted   = Env::is_wpmu_hosting();
$authorized_users = array_map( 'absint', Api::get_dashboard_authorized_users() );

$assets = new Assets();
wp_nonce_field( 'save_snapshot_settings', '_wpnonce-save_snapshot_settings' );
wp_nonce_field( 'reset_snapshot_settings', '_wpnonce-reset_snapshot_settings' );
wp_nonce_field( 'delete_snapshot_logs', '_wpnonce-delete_snapshot_logs' );
?>
<div class="sui-wrap snapshot-page-settings">
	<?php
	$this->render( 'common/header' );

	$this->render(
		'common/doc-button',
		array(
			'header_title' => __( 'Settings', 'snapshot' ),
			'utm_tags'     => 'snapshot_settings_docs#settings',
		)
	);

	$this->render(
		'common/v3-prompt',
		array(
			'active_v3'          => $active_v3,
			'v3_local'           => $v3_local,
			'assets'             => $assets,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);
	?>

	<div class="sui-row-with-sidenav snapshot-page-main">

		<div class="sui-sidenav">
			<ul class="sui-vertical-tabs sui-sidenav-hide-md">
				<li class="sui-vertical-tab current snapshot-vertical-api-key">
					<a href="#" data-tab="api-key"><?php esc_attr_e( 'General', 'snapshot' ); ?></a>
				</li>
				<li class="sui-vertical-tab snapshot-vertical-configs">
					<a href="#" data-tab="configs"><?php esc_html_e( 'Configs', 'snapshot' ); ?></a>
				</li>
				<?php if ( ! $is_wpmu_hosted ) : ?>
					<li class="sui-vertical-tab snapshot-vertical-password-protection">
						<a href="#" data-tab="password-protection"><?php esc_html_e( 'Password Protection', 'snapshot' ); ?></a>
					</li>
				<?php endif; ?>
				<li class="sui-vertical-tab snapshot-vertical-data-and-settings">
					<a href="#" data-tab="data-and-settings"><?php esc_attr_e( 'Data & Settings', 'snapshot' ); ?></a>
				</li>
			</ul>

			<div class="sui-sidenav-hide-lg" style="margin-bottom: 20px;">
				<select class="sui-select sui-mobile-nav" style="display: none;">
					<option value="api-key" selected="selected"><?php esc_attr_e( 'General', 'snapshot' ); ?></option>
					<option value="configs"><?php esc_attr_e( 'Configs', 'snapshot' ); ?></option>
					<?php
					if ( ! $is_wpmu_hosted ) :
						?>
						<option value="password-protection"><?php esc_attr_e( 'Password Protection', 'snapshot' ); ?></option> <?php endif; ?>
					<option value="data-and-settings"><?php esc_attr_e( 'Data & Settings', 'snapshot' ); ?></option>
				</select>
			</div>
		</div>

		<div class="sui-box snapshot-tab snapshot-tab-api-key" style="display: block;">
			<form id="snapshot-settings-save-tab-1">

				<div class="sui-box-header">
					<h2 class="sui-box-title"><?php esc_html_e( 'General', 'snapshot' ); ?></h2>
				</div>

				<div class="sui-box-body">
					<?php if ( in_array( get_current_user_id(), $authorized_users, true ) ) : ?>
						<div class="sui-box-settings-row">
							<div class="sui-box-settings-col-1">
								<span class="sui-settings-label">
								<?php
								/* translators: %s - plugin name */
								echo esc_html( sprintf( __( '%s API Key', 'snapshot' ), ( Settings::get_brand_name() === 'WPMU DEV' ) ? 'Snapshot' : Settings::get_brand_name() ) );
								?>
								</span>
								<span class="sui-description">
								<?php
								/* translators: %s - plugin name */
								echo esc_html( sprintf( __( 'This is your %s API Key.', 'snapshot' ), ( Settings::get_brand_name() === 'WPMU DEV' ) ? 'Snapshot' : Settings::get_brand_name() ) );
								?>
								</span>
							</div>
							<div class="sui-box-settings-col-2">
								<div class="sui-form-field">
									<label for="snapshot-api-key" class="sui-label">
									<?php
									/* translators: %s - plugin name */
									echo esc_html( sprintf( __( 'Your %s API Key', 'snapshot' ), ( Settings::get_brand_name() === 'WPMU DEV' ) ? 'Snapshot' : Settings::get_brand_name() ) );
									?>
									</label>
									<div class="sui-with-button sui-with-button-inside">
										<input type="text" id="snapshot-api-key" class="sui-form-control" readonly value="<?php echo esc_attr( Api::get_api_key() ); ?>">
										<a class="sui-button" id="snapshot-settings-copy-api-key">
											<span class="sui-icon-copy" aria-hidden="true"></span>
											<?php esc_html_e( 'Copy', 'snapshot' ); ?>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label"><?php esc_html_e( 'Site ID', 'snapshot' ); ?></span>
							<span class="sui-description"><?php esc_html_e( 'This is your website\'s site ID.', 'snapshot' ); ?></span>
						</div>
						<div class="sui-box-settings-col-2">
							<div class="sui-form-field">
								<label for="snapshot-site-id" class="sui-label"><?php esc_html_e( 'Site ID', 'snapshot' ); ?></label>
								<div class="sui-with-button sui-with-button-inside">
									<input type="text" id="snapshot-site-id" class="sui-form-control" readonly value="<?php echo esc_attr( Api::get_site_id() ); ?>">
									<a class="sui-button" id="snapshot-settings-copy-site-id">
										<span class="sui-icon-copy" aria-hidden="true"></span>
										<?php esc_html_e( 'Copy', 'snapshot' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>

				</div>

			</form>
		</div>

		<?php
		/**
		 * Empty DIV. Content is added later via Configs Preset.
		 */
		?>
		<div class="sui-box snapshot-tab snapshot-tab-configs" style="display: none;">
			<div id="snapshot-configs-wrap"></div>
		</div>

		<?php if ( ! $is_wpmu_hosted ) : ?>
			<div class="sui-box snapshot-tab snapshot-tab-password-protection" style="display: none;">
				<div id="snapshot-authentication-wrap">
					<form action="#" id="snapshot-auth-form">
						<div class="sui-box-header">
							<h2 class="sui-box-title">
								<?php esc_html_e( 'Password Protection', 'snapshot' ); ?>
							</h2>

							<div class="sui-actions-right">
								<a href="#" class="sui-button sui-button-ghost sui-button-red sui-tooltip sui-tooltip-top-right btn-delete--http_creds" data-tooltip="<?php esc_attr_e( 'Delete authentication credentials', 'snapshot' ); ?>">
									<span class="sui-loading-text">
										<span class="sui-icon-trash" aria-hidden="true"></span>
										<?php esc_html_e( 'Delete', 'snapshot' ); ?>
									</span>

									<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								</a>
							</div>
						</div>

						<div class="sui-box-body">
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label"><?php esc_html_e( 'HTTP/HTTPS Authentication', 'snapshot' ); ?></span>
									<span class="sui-description">
										<?php esc_html_e( 'If your site is password-protected, you can provide the authentication credentials to grant our service access to perform backups.', 'snapshot' ); ?>
									</span>
								</div>
								<div class="sui-box-settings-col-2">
									<div class="snapshot-form-border p-30">

										<div class="sui-form-field">
											<label for="auth_username" class="sui-label">
												<?php esc_html_e( 'Username', 'snapshot' ); ?>
											</label>
											<input autocomplete="off" type="text" name="auth_username" class="sui-form-control" id="auth_username" placeholder="<?php esc_attr_e( 'Enter your HTTP authentication username.', 'snapshot' ); ?>">
										</div>

										<div class="sui-form-field">
											<label for="auth_password" class="sui-label">
												<?php esc_html_e( 'Password', 'snapshot' ); ?>
											</label>
											<input autocomplete="off" type="password" name="auth_password" id="auth_password" class="sui-form-control" placeholder="<?php esc_attr_e( 'Enter your HTTP authentication password.', 'snapshot' ); ?>">
										</div>

										<div class="sui-form-field">
											<button type="button" class="sui-button sui-button-ghost btn-auth-test-connection" aria-live="polite">
												<span class="sui-button-text-default">
													<span class="sui-icon-update" aria-hidden="true"></span>
													<?php esc_html_e( 'Test Connection', 'snapshot' ); ?>
												</span>

												<span class="sui-button-text-onload">
													<span class="sui-icon-update sui-loading" aria-hidden="true"></span>
													<?php esc_html_e( 'Testing', 'snapshot' ); ?>
												</span>
											</button>
										</div>
									</div>

								</div>
							</div>
						</div>

						<div class="sui-box-footer">
							<div class="sui-actions-right">
								<input type="hidden" id="auth_method" name="auth_method" value="post">
								<button type="submit" disabled class="sui-button sui-button-blue btn-save-auth-connection-details">
									<span class="sui-button-text-default">
										<span class="sui-icon-save" aria-hidden="true"></span>
										<?php esc_html_e( 'Save Changes', 'snapshot' ); ?>
									</span>

									<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</form>

				</div>
			</div>
		<?php endif; ?>

		<div class="sui-box snapshot-tab snapshot-tab-data-and-settings" style="display: none;">
			<form id="snapshot-settings-save-tab-2">

				<div class="sui-box-header">
					<h2 class="sui-box-title"><?php esc_html_e( 'Data & Settings', 'snapshot' ); ?></h2>
				</div>

				<div class="sui-box-body">

					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label"><?php esc_html_e( 'Uninstall', 'snapshot' ); ?></span>
							<span class="sui-description"><?php esc_html_e( 'When uninstalling the plugin, what would you like to do with the settings?', 'snapshot' ); ?></span>
						</div>
						<div class="sui-box-settings-col-2">
							<div style="margin-bottom: 5px;">
								<span class="sui-settings-label"><?php esc_html_e( 'Settings', 'snapshot' ); ?></span>
								<span class="sui-description"><?php esc_html_e( 'Choose whether to save your settings for next time, or reset them.', 'snapshot' ); ?></span>
							</div>
							<div class="sui-side-tabs">
								<div class="sui-tabs-menu">
									<label class="sui-tab-item <?php echo ! $remove_on_uninstall ? 'active' : ''; ?>">
										<input type="radio" name="remove_on_uninstall" value="0" <?php echo ! $remove_on_uninstall ? 'checked' : ''; ?>>
										<?php esc_html_e( 'Keep', 'snapshot' ); ?>
									</label>
									<label class="sui-tab-item <?php echo $remove_on_uninstall ? 'active' : ''; ?>">
										<input type="radio" name="remove_on_uninstall" value="1" <?php echo $remove_on_uninstall ? 'checked' : ''; ?>>
										<?php esc_html_e( 'Remove', 'snapshot' ); ?>
									</label>
								</div>
							</div>

							<div role="info" id="snapshot-remove-options-notice" class="sui-notice sui-notice-info" aria-live="assertive">

								<div class="sui-notice-content">

									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
										<?php /* translators: %1$s - File example #1, %2$s - File example #2 */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'This option will not restore the default  <a href="%1$s" target="_blank" >storage limit</a> nor will it delete all connected  <a href="%2$s" target="_blank" >destinations</a>. Please either make those changes manually or use the Reset settings option below before uninstalling the plugin.', 'snapshot' ), network_admin_url() . 'admin.php?page=snapshot-backups#settings', network_admin_url() . 'admin.php?page=snapshot-destinations' ) ); ?></p>

									</div>

								</div>

							</div>
						</div>
					</div>

					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label"><?php esc_html_e( 'Delete Logs', 'snapshot' ); ?></span>
							<span class="sui-description"><?php esc_html_e( 'This will delete all logs stored on your site.', 'snapshot' ); ?></span>
						</div>
						<div class="sui-box-settings-col-2">
							<div class="sui-form-field">
								<button class="sui-button sui-button-ghost sui-button-red" id="snapshot-settings-delete-logs-confirm" onclick="SUI.openModal('modal-snapshot-settings-delete-logs', this); return false;">
									<span class="sui-icon-trash" aria-hidden="true"></span>
									<?php esc_html_e( 'Delete', 'snapshot' ); ?>
								</button>
							</div>
						</div>
					</div>

					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label"><?php esc_html_e( 'Reset settings', 'snapshot' ); ?></span>
							<span class="sui-description"><?php esc_html_e( 'Needing to start fresh? Use this button to roll back to the default settings.', 'snapshot' ); ?></span>
						</div>
						<div class="sui-box-settings-col-2">
							<div class="sui-form-field">
								<button class="sui-button sui-button-ghost" id="snapshot-settings-reset-settings-confirm">
									<span class="sui-icon-undo" aria-hidden="true"></span>
									<?php esc_html_e( 'Reset', 'snapshot' ); ?>
								</button>
								<p><small><?php esc_html_e( 'Note this will instantly reset all setting back to their defaults, and wipe any destinations you have active. It won’t delete existing backups.', 'snapshot' ); ?></small></p>
							</div>
						</div>
					</div>

				</div>

				<div class="sui-box-footer">
					<div class="sui-actions-right">
						<button class="sui-button sui-button-blue" type="submit">
							<span class="sui-icon-save" aria-hidden="true"></span>
							<?php esc_html_e( 'Save changes', 'snapshot' ); ?>
						</button>
					</div>
				</div>

			</form>
		</div>

	</div>

	<?php

	// Snapshot getting started dialog.
	$this->render(
		'modals/welcome-activation',
		array(
			'errors'             => $errors,
			'welcome_modal'      => $welcome_modal,
			'welcome_modal_alt'  => $welcome_modal_alt,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);

	$this->render( 'modals/settings-delete-logs' );
	$this->render( 'modals/settings-reset-settings' );
	$this->render( 'modals/confirm-v3-uninstall' );
	$this->render( 'modals/confirm-region-change' );
	$this->render( 'modals/confirm-wpmudev-password' );

	$this->render( 'common/footer' );
	?>

</div> <?php
// .sui-wrap ?>