<?php // phpcs:ignore
/**
 * Modal for editing an existing schedule.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Date;

$is_pro = \WPMUDEV\Snapshot4\Helper\Api::is_pro();

$assets        = new \WPMUDEV\Snapshot4\Helper\Assets();
$settings_link = network_admin_url( 'options-general.php' );

$hours = Helper\Datetime::get_hour_list();
?>

<div class="sui-modal sui-modal-md">
	<div role="dialog" id="modal-snapshot-edit-schedule" class="sui-modal-content" aria-modal="true"
		aria-labelledby="modal-snapshot-edit-schedule-title"
		aria-describedby="modal-snapshot-edit-schedule-description">
		<div class="sui-box" style="margin-bottom: 0;">
			<div class="sui-box-header sui-flatten sui-content-center">
				<figure class="sui-box-banner" role="banner" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-create-backup.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-create-backup.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-banner-create-backup@2x.png' ) ); ?> 2x" />
				</figure>
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg" id="modal-snapshot-edit-schedule-title">
					<?php echo esc_html( $modal_title ); ?></h3>
				<span id="modal-snapshot-edit-schedule-description"
					class="sui-description"><?php echo esc_html( $message ); ?></span>
			</div>
			<div class="sui-box-body">

				<form method="post" id="form-snapshot-schedule">

					<?php wp_nonce_field( 'snapshot_backup_schedule', '_wpnonce-backup_schedule' ); ?>
					<input type="hidden" name="schedule_action" value="create">
					<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
					<input type="hidden" name="files" value="<?php echo esc_attr( $files ); ?>">
					<input type="hidden" name="tables" value="<?php echo esc_attr( $tables ); ?>">

					<div class="sui-flushed">
						<div class="sui-box-settings-col-2">

							<div class="sui-tabs sui-side-tabs">

								<div data-tabs>
									<div class="" data-frequency="daily">
										<?php esc_html_e( 'Daily', 'snapshot' ); ?><span class="sui-tag sui-tag-pro"
											<?php echo $is_pro ? 'style="display: none;"' : ''; ?>><?php esc_html_e( 'PRO', 'snapshot' ); ?></span>
									</div>
									<div class="" data-frequency="weekly">
										<?php esc_html_e( 'Weekly', 'snapshot' ); ?><span class="sui-tag sui-tag-pro"
											<?php echo $is_pro ? 'style="display: none;"' : ''; ?>><?php esc_html_e( 'PRO', 'snapshot' ); ?></span>
									</div>
									<div class="" data-frequency="monthly"><?php esc_html_e( 'Monthly', 'snapshot' ); ?>
									</div>
									<div class="active" data-frequency=""><?php esc_html_e( 'None', 'snapshot' ); ?>
									</div>
								</div>

								<div data-panes>

									<div class="sui-tab-boxed">
										<?php if ( $is_pro ) { ?>
										<label for="snapshot-daily-time"
											class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
										<select class="sui-select" id="snapshot-daily-time" name="daily_time">
											<?php foreach ( $hours as $value => $text ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>">
												<?php echo esc_html( $text ); ?></option>
											<?php endforeach; ?>
										</select>
										<?php } else { ?>
										<div class="sui-box sui-message sui-no-padding">
											<div class="sui-message-content">
												<p><small><?php esc_html_e( 'Daily backup scheduling is a Pro feature, and is not included with your current WPMU DEV membership. Upgrade your membership today to unlock all the Pro features!', 'snapshot' ); ?></small>
												</p>
												<p>
													<a href="https://wpmudev.com/pricing/?utm_source=snapshot&utm_medium=free-membership&utm_campaign=free-plan-upgrade"
														target="_blank" class="sui-button sui-button-purple">
														<?php esc_html_e( 'Unlock now with pro', 'snapshot' ); ?>
													</a>
												</p>
											</div>
										</div>
											<?php
										}//end if
										?>
									</div>

									<div class="sui-tab-boxed">
										<?php
										if ( $is_pro ) {
											$dow = Date::get_randomized_weekday();
											?>
										<div class="sui-row">
											<div class="sui-col-sm-6">
												<label for="snapshot-weekly-dow"
													class="sui-label"><?php esc_html_e( 'Day of the week', 'snapshot' ); ?></label>
												<select class="sui-select" id="snapshot-weekly-dow"
													name="frequency_weekday">
													<option value="1" <?php selected( 1, $dow, true ); ?>>
														<?php esc_html_e( 'Sunday', 'snapshot' ); ?></option>
													<option value="2" <?php selected( 2, $dow, true ); ?>>
														<?php esc_html_e( 'Monday', 'snapshot' ); ?></option>
													<option value="3" <?php selected( 3, $dow, true ); ?>>
														<?php esc_html_e( 'Tuesday', 'snapshot' ); ?></option>
													<option value="4" <?php selected( 4, $dow, true ); ?>>
														<?php esc_html_e( 'Wednesday', 'snapshot' ); ?></option>
													<option value="5" <?php selected( 5, $dow, true ); ?>>
														<?php esc_html_e( 'Thursday', 'snapshot' ); ?></option>
													<option value="6" <?php selected( 6, $dow, true ); ?>>
														<?php esc_html_e( 'Friday', 'snapshot' ); ?></option>
													<option value="7" <?php selected( 7, $dow, true ); ?>>
														<?php esc_html_e( 'Saturday', 'snapshot' ); ?></option>
												</select>
											</div>
											<div class="sui-col-sm-6">
												<label for="snapshot-weekly-time"
													class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
												<select class="sui-select" id="snapshot-weekly-time" name="weekly_time">
													<?php foreach ( $hours as $value => $text ) : ?>
													<option value="<?php echo esc_attr( $value ); ?>">
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

									<div class="sui-tab-boxed">
										<div class="sui-row">
											<div class="sui-col-sm-6">
												<label for="snapshot-monthly-day"
													class="sui-label"><?php esc_html_e( 'Day of the month', 'snapshot' ); ?></label>
												<select class="sui-select" id="snapshot-monthly-day"
													name="frequency_monthday">
													<?php foreach ( range( 1, 28 ) as $day ) : ?>
													<option value="<?php echo esc_attr( $day ); ?>">
														<?php echo esc_html( $day ); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="sui-col-sm-6">
												<label for="snapshot-monthly-time"
													class="sui-label"><?php esc_html_e( 'Time of the day', 'snapshot' ); ?></label>
												<select class="sui-select" id="snapshot-monthly-time"
													name="monthly_time">
													<?php foreach ( $hours as $value => $text ) : ?>
													<option value="<?php echo esc_attr( $value ); ?>">
														<?php echo esc_html( $text ); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</div>
									</div>

									<div class="active"></div>
								</div>

								<div id="snapshot-notice-tpd-schedule" class="sui-notice sui-notice-info">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<span class="sui-notice-icon sui-icon-info sui-md"
												aria-hidden="true"></span>
											<?php /* translators: %s - Link for Destination page */ ?>
											<p><?php echo wp_kses_post( sprintf( __( 'The backup schedule will be applied to all destinations connected on the <a href="%s">Destinations page</a>.', 'snapshot' ), network_admin_url() . 'admin.php?page=snapshot-destinations' ) ); ?>
											</p>
										</div>
									</div>
								</div>
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

					<div class="sui-block-content-center">
						<button type="submit" class="sui-button sui-button-blue" aria-live="polite">
							<span class="sui-button-text-default">
								<span class="sui-icon-save" aria-hidden="true"></span><?php echo esc_attr( $button ); ?>
							</span>
							<span class="sui-button-text-onload">
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php echo esc_attr( $button_saving ); ?>
							</span>
						</button>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>