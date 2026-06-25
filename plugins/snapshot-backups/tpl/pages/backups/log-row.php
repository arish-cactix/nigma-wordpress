<?php // phpcs:ignore
/**
 * Log row.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Model;

$icon              = Model\Request\Listing::get_backup_icon( $backup_type );
$icon_tooltip_text = Model\Request\Listing::get_backup_icon_tooltip_text( $backup_type );
$destination_text  = Model\Request\Listing::get_backup_destination_text( $backup_type );
$export_text       = Model\Request\Listing::get_backup_export_texts( $tpd_exp_status );
?>
<tr class="sui-accordion-item log-row"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_get_backup_log' ) ); ?>"
	data-backup-id="<?php echo esc_attr( $backup_id ); ?>" data-append-log="<?php echo esc_attr( $append_log ); ?>">
	<td class="sui-hidden-xs sui-table-item-title">
		<div class="sui-tooltip sui-tooltip-top-left snapshot-icon-tooltip"
			data-tooltip="<?php echo esc_attr( $icon_tooltip_text ); ?>"></div>
		<i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
		<?php echo esc_html( $name ); ?>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray">

		<?php
		if ( isset( $destination_icon_details['icon_url'] ) ) {
			?>
		<div class="custom-icon-sm icon-linked"
			style="background-image: url( <?php echo esc_url( $destination_icon_details['icon_url'] ); ?>);"></div>
			<?php
		} elseif ( 'sui-no-icon' !== $destination_icon_details['icon_class'] ) {
			?>
		<span
			class="<?php echo 'sui-icon-wpmudev-logo' === $destination_icon_details['icon_class'] ? 'sui-icon-wpmudev-logo' : 'custom-icon-sm ' . esc_attr( $destination_icon_details['icon_class'] ); ?>"
			aria-hidden="true">
		</span>
			<?php
		}
		echo esc_html( $destination_text );
		?>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray snapshot-export-column">
		<span style="display: inline-block;"><?php echo wp_kses_post( $export_text['row'] ); ?></span>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray last-child">
		<span class="sui-accordion-open-indicator" aria-label="<?php esc_html_e( 'Expand', 'snapshot' ); ?>"><span
				class="sui-icon-chevron-down" aria-hidden="true"></span></span>
	</td>

	<td class="sui-hidden-sm sui-hidden-md sui-hidden-lg sui-table-item-title mobile-row" colspan="4">
		<div class="sui-table-item-title">
			<div class="sui-tooltip sui-tooltip-top-left snapshot-icon-tooltip"
				data-tooltip="<?php echo esc_attr( $icon_tooltip_text ); ?>"></div>
			<i class="<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
			<?php echo esc_html( $name ); ?>
			<span class="sui-accordion-open-indicator" aria-label="<?php esc_html_e( 'Expand', 'snapshot' ); ?>"><span
					class="sui-icon-chevron-down" aria-hidden="true"></span></span>
		</div>
		<div class="sui-row">
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title"><?php esc_html_e( 'Storage', 'snapshot' ); ?>
				</div>
				<div class="sui-table-item-title gray">
					<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
					<?php echo esc_html( $destination_text ); ?>
				</div>
			</div>
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">
					<?php esc_html_e( 'Export destination', 'snapshot' ); ?></div>
				<div class="sui-table-item-title gray">
					<span
						style="display: inline-block;"><?php echo wp_kses_post( $export_text['row_mobile'] ); ?></span>
				</div>
			</div>
		</div>
	</td>
</tr>

<tr class="sui-accordion-item-content">
	<td colspan="4">

		<div class="sui-box snapshot-loading">
			<div class="sui-box-body log-loader">
				<div class="sui-message">
					<div class="sui-message-content">
						<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Loading log...', 'snapshot' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="sui-box snapshot-loaded" style="display: none;">
			<div class="sui-box-header">
				<div class="sui-form-field" style="width: 140px;">
					<select class="sui-select sui-select-sm log-filter">
						<option value="all" selected><?php esc_html_e( 'All', 'snapshot' ); ?></option>
						<option value="warning"><?php esc_html_e( 'Warning', 'snapshot' ); ?></option>
						<option value="error"><?php esc_html_e( 'Error', 'snapshot' ); ?></option>
					</select>
				</div>
			</div>
			<div class="sui-box-body log-items-container" id="log-container-<?php echo esc_attr( $backup_id ); ?>">
				<div class="sui-notice sui-notice-info no-warning notice-error">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'No warning found in the log.', 'snapshot' ); ?></p>
						</div>
					</div>
				</div>
				<div class="sui-notice sui-notice-info no-error notice-error">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-warning-alert sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'No error found in the log.', 'snapshot' ); ?></p>
						</div>
					</div>
				</div>
				<div class="sui-notice sui-notice-info no-log-content notice-error" style="display: none;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'No entries found in the log.', 'snapshot' ); ?></p>
						</div>
					</div>
				</div>

				<div class="sui-notice sui-notice-error general-error notice-error" style="display: none;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Sorry! We couldn\'t load log entries at the moment. Please try again later.', 'snapshot' ); ?>
							</p>
						</div>
					</div>
				</div>

				<div class="log-lists">
					<?php foreach ( $log as $item ) { ?>
					<div class="log-item <?php echo esc_attr( 'log-level-' . $item['level'] ); ?>">
						<div class="log-item__icon" aria-hidden="true"></div>
						<div class="log-item__content">
							<?php echo esc_html( $item['message'] ); ?>
						</div>
					</div>
					<?php } ?>
				</div>

				<div class="snapshot-action__wrap" style="display: none; margin-top: 20px; margin-right: 10px;">
					<button type="button" class="sui-button sui-button-ghost sui-button-blue paginate-log"
						id="paginate-log" data-backup-id="<?php echo esc_attr( $backup_id ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_get_backup_log' ) ); ?>"
						style="border:none;">
						<span class="sui-loading-text">
							+ <?php esc_html_e( 'Load more', 'snapshot' ); ?>
						</span>

						<!-- Spinning loading icon -->
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>
			</div>

			<div class="sui-box-footer">
				<button class="sui-button sui-button-ghost view-backup"
					data-backup-id="<?php echo esc_attr( $backup_id ); ?>">
					<span class="sui-icon-reply flip-h" aria-hidden="true"></span>
					<?php esc_html_e( 'View backup', 'snapshot' ); ?>
				</button>

				<a download class="sui-button sui-button-blue" href="<?php echo esc_attr( $log_url ); ?>">
					<span class="sui-icon-download" aria-hidden="true"></span>
					<?php esc_html_e( 'Download', 'snapshot' ); ?>
				</a>
			</div>
		</div>

	</td>
</tr>