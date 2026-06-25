<?php // phpcs:ignore
/**
 * Welcome modal.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Date;
use WPMUDEV\Snapshot4\Helper\Datetime;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Helper\Settings;

$is_pro = \WPMUDEV\Snapshot4\Helper\Api::is_pro();

$button_class = ! empty( $button_class ) ? $button_class : 'sui-button-ghost';
$modal_title  = ! empty( $modal_title ) ? $modal_title : '';
$message      = ! empty( $message ) ? $message : '';
$message2     = ! empty( $message2 ) ? $message2 : '';
$button       = ! empty( $button ) ? $button : '';

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();

$schedule  = Model\Schedule::get_schedule_info( false, true );
$frequency = $schedule['values']['frequency'];

$weekday = Date::get_randomized_weekday();
if ( isset( $schedule['values']['frequency_weekday'] ) && null !== $schedule['values']['frequency_weekday'] ) {
	$weekday = absint( $schedule['values']['frequency_weekday'] );
}

$hours       = Datetime::get_hour_list();
$random_hour = array_rand( $hours, 1 );
// Get a random hour.
$time = isset( $schedule['values']['time'] ) ? $schedule['values']['time'] : $random_hour;

$has_hosting_backups = Env::is_wpmu_hosting();
$brand_name          = Helper\Settings::is_branding_hidden() ? Helper\Settings::get_brand_name() . '\'s' : 'WPMU DEV\'s';

if ( $has_hosting_backups ) {
	/* translators: %s - Admin name */
	$message = Settings::get_branding_hide_doc_link() ? sprintf( __( '%s, welcome to the hottest backup plugin for WordPress. Both Hosting and plugin backups are available within the plugin.', 'snapshot' ), wp_get_current_user()->display_name ) : sprintf( __( '%s, welcome to the hottest backup plugin for WordPress. We\'ve detected you\'re hosting this website with us. Great! Both Hosting and Snapshot backups are available within the plugin.', 'snapshot' ), wp_get_current_user()->display_name );
}

$settings_link = network_admin_url( 'options-general.php' );
?>

<div class="sui-modal sui-modal-md">
	<div role="dialog" id="snapshot-welcome-dialog" class="sui-modal-content" aria-modal="true">

		<div class="sui-modal-slide sui-active sui-loaded" id="snapshot-welcome-dialog-slide-1" data-modal-size="md">
			<div class="sui-box">

				<div class="sui-box-header sui-flatten sui-content-center">

					<div class="sui-box-banner <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						role="banner" aria-hidden="true"></div>

					<h3 class="sui-box-title sui-lg"><?php echo esc_html( $modal_title ); ?></h3>
					<span class="sui-description">
						<?php echo esc_html( $message ); ?>
					</span>
				</div>

				<div class="sui-box-body">
					<?php
					wp_nonce_field( 'reactivate_snapshot_schedule', '_wpnonce-reactivate_snapshot_schedule' );
					wp_nonce_field( 'snapshot_check_if_region', '_wpnonce-snapshot_check_if_region' );
					?>

					<?php if ( $has_hosting_backups ) { ?>
					<div class="hosting-backups-description">
						<p class="list-header"><strong><span
									class="bullet">•</span><?php esc_html_e( 'Hosting Backups', 'snapshot' ); ?></strong>
						</p>
						<p><?php esc_html_e( 'Hosting backups run nightly on a 30 day storage cycle. Backups are available to download within the plugin whereas restoring and any additional configuration is done via the Hub.', 'snapshot' ); ?>
						</p>

						<p class="list-header" style="margin-top: 20px;"><strong><span
									class="bullet">•</span><?php Settings::get_branding_hide_doc_link() ? esc_html_e( 'Plugin Backups', 'snapshot' ) : esc_html_e( 'Snapshot Backups', 'snapshot' ); ?></strong>
						</p>
						<p>
							<?php
								Settings::get_branding_hide_doc_link() ?
								printf(
									/* translators: %s - Brand name */
									esc_html__(
										'Plugin backups are incremental, allowing you to back up your site more frequently. You can set a storage limit, up to 30 manual backups and 30 scheduled backups (60 in total), and keep them on %s Storage Cloud until you reach our 50-days expiry policy for backups.',
										'snapshot'
									),
									esc_html( $brand_name )
								) :
								printf(
									/* translators: %s - Brand name */
									esc_html__(
										'Snapshot backups are incremental, allowing you to back up your site more frequently. You can set a storage limit, up to 30 manual backups and 30 scheduled backups (60 in total), and keep them on %s Storage Cloud until you reach our 50-days expiry policy for backups.',
										'snapshot'
									),
									esc_html( $brand_name )
								);
							?>
						</p>
					</div>
					<?php } else { ?>

					<div class="hosting-backups-description">
						<p class="list-header"><strong><span
									class="bullet">•</span><?php Settings::get_branding_hide_doc_link() ? esc_html_e( 'Plugin Backups', 'snapshot' ) : esc_html_e( 'Snapshot Backups', 'snapshot' ); ?></strong>
						</p>
						<p>
							<?php
								Settings::get_branding_hide_doc_link() ?
								printf(
									/* translators: %s - Brand name */
									esc_html__(
										'Plugin backups are incremental, allowing you to back up your site more frequently. You can set a storage limit, up to 30 manual backups and 30 scheduled backups (60 in total), and keep them on %s Storage Cloud until you reach our 50-days expiry policy for backups.',
										'snapshot'
									),
									esc_html( $brand_name )
								) :
								printf(
									/* translators: %s - Brand name */
									esc_html__(
										'Snapshot backups are incremental, allowing you to back up your site more frequently. You can set a storage limit, up to 30 manual backups and 30 scheduled backups (60 in total), and keep them on %s Storage Cloud until you reach our 50-days expiry policy for backups.',
										'snapshot'
									),
									esc_html( $brand_name )
								);
							?>
						</p>
					</div>
						<?php
					}//end if
					?>

					<div class="sui-notice sui-notice-error on-error" style="display: none;">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
								<p><?php esc_html_e( 'A connection with the API couldn\'t be established. Give it another try below, and if you continue having connection issues, contact support.', 'snapshot' ); ?>
								</p>
								<?php } else { ?>
									<?php /* translators: %s - link */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'A connection with the API couldn\'t be established. Give it another try below, and if you continue having connection issues, our <a href="%s" target="_blank">support team</a> is ready to help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
								</p>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="sui-block-content-center on-error" style="display: none;">
						<button class="sui-button sui-button-ghost snapshot-get-started" role="button"
							onclick="jQuery(window).trigger('snapshot:check_if_region_modal')">
							<span class="sui-button-text-default">
								<span class="sui-icon-refresh" aria-hidden="true"></span>
								<?php esc_html_e( 'Reload', 'snapshot' ); ?>
							</span>
							<span class="sui-button-text-onload">
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php esc_html_e( 'Reload', 'snapshot' ); ?>
							</span>
						</button>
					</div>

					<div class="sui-block-content-center on-success">
						<button
							class="sui-button <?php echo sanitize_html_class( $button_class ); ?> snapshot-get-started"
							data-modal-slide="snapshot-welcome-dialog-slide-configs">
							<span class="sui-button-text-default"><?php echo esc_html( $button ); ?></span>
							<span class="sui-button-text-onload">
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php echo esc_html( $button ); ?>
							</span>
						</button>

						<?php if ( $has_hosting_backups ) { ?>
						<!--<p><small><strong><a class="hosting-backups-link" href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=snapshot-hosting-backups' ); ?>"><?php esc_html_e( 'View Hosting Backups', 'snapshot' ); ?></a></strong></small></p>-->
						<?php } ?>
					</div>
				</div>

			</div>
		</div>

		<div class="sui-modal-slide" id="snapshot-welcome-dialog-slide-configs" data-modal-size="md">
			<div class="sui-box">
				<div class="sui-box-header sui-flatten sui-content-center">
					<div class="sui-box-banner <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						role="banner" aria-hidden="true"></div>
					<h3 class="sui-box-title sui-lg">
						<?php esc_html_e( 'Choose Config', 'snapshot' ); ?>
					</h3>
					<span class="sui-description">
						<?php esc_html_e( 'Please choose how you would like to configure Snapshot.', 'snapshot' ); ?>
					</span>
				</div>

				<div class="sui-box-body sui-content-center sui-spacing-top--30 sui-spacing-sides--40">
					<ul class="sui-flex justify-evenly apply-config--links">
						<li>
							<span class="sui-flex align-items-center justify-content-center">
								<a href="#" class="sui-welcome-apply-configs"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot-fetch' ) ); ?>"><?php esc_html_e( 'Apply Default Config', 'snapshot' ); ?></a>
								<span class="sui-tooltip sui-tooltip-constrained"
									data-tooltip="<?php esc_attr_e( 'Region: US Schedule: Weekly, Every Sunday 12 AM Storage Limit: 5 Email settings, notifications, recipients: Off Exclusions: Deafult Global exclusions enabled Data & Settings: the default one - Settings set to Keep.', 'snapshot' ); ?>">
									<span class="sui-icon-info" aria-hidden="true"></span>
								</span>
							</span>
						</li>
						<li>
							<span class="sui-flex align-items-center justify-content-center">
								<a href="#" class="sui-welcome-load-hub-configs"
									data-slide-to="snapshot-welcome-dialog-slide--hub__configs"><?php esc_html_e( 'Apply Your Own Config', 'snapshot' ); ?></a>
								<span class="sui-tooltip sui-tooltip-constrained" style="--tooltip-width: 171px;"
									data-tooltip="<?php esc_attr_e( 'Import and apply a saved Config from the Hub.', 'snapshot' ); ?>">
									<span class="sui-icon-info" aria-hidden="true"></span>
								</span>
							</span>
						</li>
					</ul>
				</div>

				<div class="sui-box-footer sui-flatten sui-content-center sui-spacing-top--0 sui-spacing-bottom--60">
					<button class="sui-button snapshot-get-started" role="button"
						onclick="jQuery(window).trigger('snapshot:check_if_region_modal')">
						<span
							class="sui-button-text-default"><?php esc_html_e( 'SET UP MANUALLY', 'snapshot' ); ?></span>
						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Please wait...', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>
		</div>

		<div class="sui-modal-slide sui-loaded" id="snapshot-welcome-dialog-slide--hub__configs" data-modal-size="md">
			<div class="sui-box">
				<div class="sui-box-header sui-flatten sui-content-center">
					<div class="sui-box-banner <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						role="banner" aria-hidden="true"></div>
					<h3 class="sui-box-title sui-lg">
						<?php esc_html_e( 'Do you wish to import your Settings?', 'snapshot' ); ?>
					</h3>
					<span class="sui-description">
						<?php esc_html_e( 'You can automatically import your Snapshot settings configurations from other sites in just a click.', 'snapshot' ); ?>
					</span>
				</div>

				<div class="sui-box-body sui-spacing-top--30 sui-spacing-bottom--0 sui-spacing-sides--40">
					<form id="snapshot-apply-config--form" method="post">
						<input type="hidden" id="snapshot-fetch-nonce"
							value="<?php echo esc_attr( wp_create_nonce( 'snapshot-fetch' ) ); ?>" />
						<div class="sui-form-field">
							<label class="sui-label" for="snapshot-configs--hub">
								<?php esc_html_e( 'Snapshot Configs', 'snapshot' ); ?><span>
							</label>
							<div class="sui-row" style="align-items: center;">
								<div class="sui-col-sm-9">
									<select id="snapshot-configs--hub" class="sui-select"></select>
								</div>
								<div class="sui-col-sm-3">
									<button id="snapshot-configs--hub__apply" type="submit"
										class="sui-button sui-button-blue">
										<span class="sui-button-text-default">
											<span class="sui-icon-check" aria-hidden="true"></span>
											<?php esc_html_e( 'Apply', 'snapshot' ); ?>
										</span>

										<span class="sui-button-text-onload">
											<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
										</span>
									</button>
								</div>
							</div>
							<p id="select-single-icon-helper" class="sui-description" style="margin-top: 10px;">
								<?php esc_html_e( 'Select the Default Config to apply our Recommended default settings.', 'snapshot' ); ?>
							</p>
						</div>
					</form>
					<hr>
				</div>

				<div class="sui-box-body sui-spacing-sides--60 sui-spacing-top--0">
					<div style="text-align: center;">
						<p class="sui-description">
							<?php esc_html_e( 'You can also skip this and configure the plugin settings following a few easy steps.', 'snapshot' ); ?>
						</p>
					</div>
				</div>

				<div class="sui-box-footer sui-flatten sui-content-center sui-spacing-top--0 sui-spacing-bottom--60">


					<button class="sui-button sui-button-ghost snapshot-get-started" role="button"
						onclick="jQuery(window).trigger('snapshot:check_if_region_modal')">
						<span
							class="sui-button-text-default"><?php esc_html_e( 'LET\'S GET STARTED', 'snapshot' ); ?></span>
						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Please wait...', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>
		</div>

		<div class="sui-modal-slide sui-loaded" id="snapshot-welcome-dialog-slide-2" data-modal-size="md">
			<div class="sui-box">

				<div class="sui-box-header sui-flatten sui-content-center">

					<div class="sui-box-banner <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						role="banner" aria-hidden="true"></div>

					<h3 class="sui-box-title sui-lg">
						<?php echo esc_html( Settings::get_branding_hide_doc_link() ? __( 'Welcome!', 'snapshot' ) : __( 'Welcome to Snapshot Pro', 'snapshot' ) ); ?>
					</h3>
					<span
						class="sui-description"><?php echo esc_html( __( 'Please choose the backup storage region to continue.', 'snapshot' ) ); ?></span>

				</div>

				<div class="sui-box-body">
					<div class="sui-notice sui-notice-error on-error" style="display: none;">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
								<p><?php esc_html_e( 'We were unable to proceed due to a connection problem. Please change the storage region again, or contact support if the problem persists.', 'snapshot' ); ?>
								</p>
								<?php } else { ?>
									<?php /* translators: %s - link */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'We were unable to proceed due to a connection problem. Please choose the storage region again, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
								</p>
								<?php } ?>
							</div>
						</div>
					</div>

					<form method="post" id="onboarding-region">
						<?php
						wp_nonce_field( 'save_snapshot_region', '_wpnonce-save_snapshot_region' );
						?>
						<div class="sui-form-field">

							<label for="onboarding-select-region" id="label-onboarding-select-region"
								class="sui-label"><?php echo esc_html( __( 'Storage Region', 'snapshot' ) ); ?></label>

							<select class="sui-select" id="onboarding-select-region" placeholder="Choose storage region"
								aria-labelledby="label-onboarding-select-region"
								aria-describedby="description-onboarding-select-region">
								<option value="us">
									<?php echo esc_html( __( 'United States (better performance, recommended)', 'snapshot' ) ); ?>
								</option>
								<option value="eu">
									<?php echo esc_html( __( 'Europe (EU data protection directive compliant)', 'snapshot' ) ); ?>
								</option>
							</select>

						</div>

						<div class="sui-box-footer sui-flatten sui-lg sui-content-center">
							<button type="button" id="snapshot-set-initial-region" class="sui-button"
								onclick="jQuery(window).trigger('snapshot:save_region')">
								<span
									class="sui-button-text-default"><?php esc_html_e( 'Continue', 'snapshot' ); ?></span>
								<span class="sui-button-text-onload">
									<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
									<?php esc_html_e( 'Continue', 'snapshot' ); ?>
								</span>
							</button>
						</div>
					</form>
				</div>

			</div>
		</div>

		<div class="sui-modal-slide sui-loaded" id="snapshot-welcome-dialog-slide-3" data-modal-size="md">
			<div class="sui-box">

				<div class="sui-box-header sui-flatten sui-content-center">

					<div class="sui-box-banner <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						role="banner" aria-hidden="true"></div>
					<button
						class="sui-button-icon sui-button-float--right close-modal  <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>">
						<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					</button>
					<button
						class="sui-button-icon sui-button-float--left hide-when-region-selected <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"
						data-modal-slide="snapshot-welcome-dialog-slide-2">
						<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
						<span
							class="sui-screen-reader-text"><?php esc_html_e( 'Back to Choose Region', 'snapshot' ); ?></span>
					</button>

					<h3 class="sui-box-title sui-lg">
						<?php Settings::get_branding_hide_doc_link() ? esc_html_e( 'Welcome!', 'snapshot' ) : esc_html_e( 'Welcome to Snapshot Pro', 'snapshot' ); ?>
					</h3>
					<span
						class="sui-description"><?php esc_html_e( 'Choose your backup schedule.', 'snapshot' ); ?></span>

				</div>

				<form method="post" id="onboarding-schedule" data-show-schedule-notice="true">

					<?php wp_nonce_field( 'backup_schedule' ); ?>
					<input type="hidden" name="schedule_action"
						value="<?php echo esc_attr( $schedule['schedule_action'] ); ?>">
					<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
					<input type="hidden" name="files" value="<?php echo esc_attr( $files ); ?>">
					<input type="hidden" name="tables" value="<?php echo esc_attr( $tables ); ?>">

					<div class="sui-box-body sui-lg">

						<div class="sui-flushed">
							<div class="sui-box-settings-col-2">

								<div class="sui-tabs sui-side-tabs">

									<div data-tabs>
										<div class="<?php echo 'daily' === $frequency ? 'active' : ''; ?>"
											data-frequency="daily"><?php esc_html_e( 'Daily', 'snapshot' ); ?><span
												class="sui-tag sui-tag-pro"
												<?php echo $is_pro ? 'style="display: none;"' : ''; ?>><?php esc_html_e( 'PRO', 'snapshot' ); ?></span>
										</div>
										<div class="<?php echo ( 'weekly' === $frequency || ( ! $frequency && $is_pro ) ) ? 'active' : ''; ?>"
											data-frequency="weekly"><?php esc_html_e( 'Weekly', 'snapshot' ); ?><span
												class="sui-tag sui-tag-pro"
												<?php echo $is_pro ? 'style="display: none;"' : ''; ?>><?php esc_html_e( 'PRO', 'snapshot' ); ?></span>
										</div>
										<div class="<?php echo ( 'monthly' === $frequency || ( ! $frequency && ! $is_pro ) ) ? 'active' : ''; ?>"
											data-frequency="monthly"><?php esc_html_e( 'Monthly', 'snapshot' ); ?></div>
										<div class="" data-frequency=""><?php esc_html_e( 'None', 'snapshot' ); ?></div>
									</div>

									<div data-panes>

										<div
											class="sui-tab-boxed <?php echo 'daily' === $frequency ? 'active' : ''; ?>">
											<?php if ( $is_pro ) { ?>
											<label for="snapshot-welcome-daily-time"
												class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
											<select class="sui-select" id="snapshot-welcome-daily-time"
												name="daily_time">
												<?php foreach ( $hours as $value => $text ) { ?>
												<option <?php selected( $value, $time ); ?>
													value="<?php echo esc_attr( $value ); ?>">
													<?php echo esc_html( $text ); ?></option>
												<?php } ?>
											</select>
											<?php } else { ?>
											<div class="sui-box sui-message sui-no-padding">
												<div class="sui-message-content">
													<p><small><?php esc_html_e( 'Daily backup scheduling is a Pro feature, and is not included with your current WPMU DEV membership. Upgrade your membership today to unlock all the Pro features!', 'snapshot' ); ?></small>
													</p>
													<p><a href="https://wpmudev.com/pricing/?utm_source=snapshot&utm_medium=free-membership&utm_campaign=free-plan-upgrade"
															target="_blank"
															class="sui-button sui-button-purple"><?php esc_html_e( 'Unlock now with pro', 'snapshot' ); ?></a>
													</p>
												</div>
											</div>
												<?php
											}//end if
											?>
										</div>

										<div
											class="sui-tab-boxed <?php echo ( 'weekly' === $frequency || ( ! $frequency && $is_pro ) ) ? 'active' : ''; ?>">
											<?php if ( $is_pro ) { ?>
											<div class="sui-row">
												<div class="sui-col-sm-6">
													<label for="snapshot-welcome-weekly-dow"
														class="sui-label"><?php esc_html_e( 'Day of the week', 'snapshot' ); ?></label>
													<select class="sui-select" id="snapshot-welcome-weekly-dow"
														name="frequency_weekday">
														<option <?php selected( 1, $weekday, true ); ?> value="1">
															<?php esc_html_e( 'Sunday', 'snapshot' ); ?></option>
														<option <?php selected( 2, $weekday, true ); ?> value="2">
															<?php esc_html_e( 'Monday', 'snapshot' ); ?></option>
														<option <?php selected( 3, $weekday, true ); ?> value="3">
															<?php esc_html_e( 'Tuesday', 'snapshot' ); ?></option>
														<option <?php selected( 4, $weekday, true ); ?> value="4">
															<?php esc_html_e( 'Wednesday', 'snapshot' ); ?></option>
														<option <?php selected( 5, $weekday, true ); ?> value="5">
															<?php esc_html_e( 'Thursday', 'snapshot' ); ?></option>
														<option <?php selected( 6, $weekday, true ); ?> value="6">
															<?php esc_html_e( 'Friday', 'snapshot' ); ?></option>
														<option <?php selected( 7, $weekday, true ); ?> value="7">
															<?php esc_html_e( 'Saturday', 'snapshot' ); ?></option>
													</select>
												</div>
												<div class="sui-col-sm-6">
													<label for="snapshot-welcome-weekly-time"
														class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
													<select class="sui-select" id="snapshot-welcome-weekly-time"
														name="weekly_time">
														<?php foreach ( $hours as $value => $text ) : ?>
														<option <?php selected( $value, $time ); ?>
															value="<?php echo esc_attr( $value ); ?>">
															<?php echo esc_html( $text ); ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
											<?php } else { ?>
											<div class="sui-box sui-message sui-no-padding">
												<div class="sui-message-content">
													<p><small><?php esc_html_e( 'Weekly backup scheduling is a Pro feature, and is not included with your current WPMU DEV membership. Upgrade your membership today to unlock all the Pro features!', 'snapshot' ); ?></small>
													</p>
													<p><a href="https://wpmudev.com/pricing/?utm_source=snapshot&utm_medium=free-membership&utm_campaign=free-plan-upgrade"
															target="_blank"
															class="sui-button sui-button-purple"><?php esc_html_e( 'Unlock now with pro', 'snapshot' ); ?></a>
													</p>
												</div>
											</div>
												<?php
											}//end if
											?>
										</div>

										<div
											class="sui-tab-boxed <?php echo ( 'monthly' === $frequency || ( ! $frequency && ! $is_pro ) ) ? 'active' : ''; ?>">
											<div class="sui-row">
												<div class="sui-col-sm-6">
													<label for="snapshot-welcome-monthly-day"
														class="sui-label"><?php esc_html_e( 'Day of the month', 'snapshot' ); ?></label>
													<select class="sui-select" id="snapshot-welcome-monthly-day"
														name="frequency_monthday">
														<?php foreach ( range( 1, 28 ) as $day ) { ?>
														<option
															<?php echo $day === $schedule['values']['frequency_monthday'] ? 'selected' : ''; ?>
															value="<?php echo esc_attr( $day ); ?>">
															<?php echo esc_html( $day ); ?></option>
														<?php } ?>
													</select>
												</div>
												<div class="sui-col-sm-6">
													<label for="snapshot-welcome-monthly-time"
														class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
													<select class="sui-select" id="snapshot-welcome-monthly-time"
														name="monthly_time">
														<?php foreach ( $hours as $value => $text ) : ?>
														<option <?php selected( $value, $time ); ?>
															value="<?php echo esc_attr( $value ); ?>">
															<?php echo esc_html( $text ); ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										</div>

										<div></div>

									</div>

								</div>

								<p class="sui-description" style="margin-top: 10px">
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %1$s: Time format, %2$s: Link to settings page. */
											__( 'Your site\'s current time is <strong>%1$s</strong> based on your <a href="%2$s">WordPress Settings.</a>', 'snapshot' ),
											Helper\Datetime::get_timezone_string(),
											esc_url( $settings_link )
										)
									);
									?>
								</p>
							</div>
						</div>

					</div>

					<div class="sui-box-footer sui-flatten sui-lg sui-content-center">
						<button type="submit" class="sui-button sui-button-blue" aria-live="polite">
							<span class="sui-button-text-default"><?php esc_html_e( 'Save', 'snapshot' ); ?></span>
							<span class="sui-button-text-onload">
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php esc_html_e( 'Saving', 'snapshot' ); ?>
							</span>
						</button>
					</div>

				</form>

			</div>
		</div>
	</div>
</div>