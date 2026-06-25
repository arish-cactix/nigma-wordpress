<?php // phpcs:ignore
/**
 * Row with hosting backup details.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Datetime;

?>
<tr class="sui-accordion-item hosting-backup-row">
	<td class="sui-table-item-title">
		<div class="sui-tooltip sui-tooltip-top-left snapshot-icon-tooltip"
			data-tooltip="<?php echo esc_attr( $icon_tooltip_text ); ?>"></div>
		<span class="sui-icon-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
		<span><?php echo esc_html( Datetime::format( $created_at ) ); ?></span>
	</td>
	<td class="sui-table-item-title gray">

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
		?>
		<?php echo esc_html( $destination_title ); ?>
	</td>
	<td class="sui-table-item-title gray"><?php echo esc_html( $created_ago_human ); ?><span
			class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down"
				aria-hidden="true"></span></span></td>
</tr>
<tr class="sui-accordion-item-content">
	<td colspan="3">
		<div class="sui-box">
			<div class="sui-box-body">
				<p style="color: #888888;">
					<?php esc_html_e( 'You can restore the backup, view details, and add comments via the Hub.', 'snapshot' ); ?>
				</p>
				<a class="sui-button sui-button-gray" target="_blank" href="<?php echo esc_attr( $manage_link ); ?>">
					<span class="sui-icon-open-new-window" aria-hidden="true"></span>
					<?php esc_html_e( 'Manage', 'snapshot' ); ?>
				</a>
			</div>
			<div class="sui-box-footer">
				<div class="sui-actions-right">
					<button
						class="sui-button sui-button-blue sui-tooltip sui-tooltip-constrained download-hosting-backup"
						data-backup-id="<?php echo esc_attr( $backup_id ); ?>"
						data-tooltip="<?php esc_attr_e( 'The backup file will be sent to your email', 'snapshot' ); ?>">
						<span class="sui-icon-download" aria-hidden="true"></span>
						<?php esc_html_e( 'Export', 'snapshot' ); ?>
					</button>
				</div>
			</div>
		</div>
	</td>
</tr>