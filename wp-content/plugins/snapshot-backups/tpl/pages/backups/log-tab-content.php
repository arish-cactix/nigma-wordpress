<?php // phpcs:ignore
/**
 * Log tab content.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Log;

$assets = new Assets();

/* translators: %s - Admin name */
$empty_logs_text = sprintf( __( '%s, you don\'t have any log history yet. Once you create your first backup, logs will be available here.', 'snapshot' ), wp_get_current_user()->display_name );

?>
<div class="sui-box-body logs-empty" style="<?php echo count( $logs ) || $loading ? 'display: none;' : ''; ?>">
	<div class="sui-message">
		<img src="<?php echo esc_attr( $assets->get_asset( 'img/no-backups-icon.svg' ) ); ?>"
			class="sui-image snapshot-no-backups-hero"
			aria-hidden="true" />

		<div class="sui-message-content">
			<p><?php echo esc_html( $empty_logs_text ); ?></p>
		</div>
	</div>
</div>

<div class="logs-not-empty" style="<?php echo ! count( $logs ) || $loading ? 'display: none;' : ''; ?>">
	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'Available Logs', 'snapshot' ); ?></h2>
	</div>
	<div class="sui-box-body">
		<p><?php esc_html_e( 'Here\'s your log history for your backups. You can use these to debug issues and see what\'s happening behind the scenes.', 'snapshot' ); ?></p>
	</div>
	<table class="sui-table sui-table-flushed sui-accordion">
		<thead>
			<tr class="sui-hidden-xs">
				<th><?php esc_html_e( 'Date', 'snapshot' ); ?></th>
				<th><?php esc_html_e( 'Storage', 'snapshot' ); ?></th>
				<th><?php esc_html_e( 'Export Destination', 'snapshot' ); ?></th>
				<th style="width: 50px;"></th>
			</tr>
			<tr class="sui-hidden-sm sui-hidden-md sui-hidden-lg">
				<th colspan="4" style="height: 0;"></th>
			</tr>
		</thead>
		<tbody class="log-rows">

			<?php $append_log = true; ?>
			<?php foreach ( $logs as $log ) { ?>
				<?php
				$this->render(
					'pages/backups/log-row',
					array(
						'name'       => Helper\Datetime::format( $log['created_at'] ),
						'log'        => array(),
						'log_url'    => Log::get_log_url( $log['backup_id'] ),
						'backup_id'  => $log['backup_id'],
						'append_log' => intval( $append_log ),
					)
				);
				$append_log = false;
				?>
			<?php } ?>


		</tbody>
	</table>
	<div style="height: 30px;"></div>
</div>

<div class="sui-box-body logs-loading" style="<?php echo ! $loading ? 'display: none;' : ''; ?>">
	<div class="sui-message">

		<div class="sui-message-content">
			<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span> <?php esc_html_e( 'Loading logs...', 'snapshot' ); ?></p>
		</div>

	</div>
</div>