<?php // phpcs:ignore
/**
 * Snapshot models: Backup progress model
 *
 * Holds information about the currently running backup.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Backup;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Backup progress model class
 */
class Progress extends Model\Request {

	const STATUSES = array(
		'just_triggered',
		'snapshot_initiated',
		'files_fetch_completed',
		'zipstream_completed',
		'files_upload_completed',
		'tables_snapshot_started',
		'tables_snapshot_completed',
		'snapshot_not_exported',
		'snapshot_completed',
	);

	const STEPS = array(
		'just_triggered'            => 0,
		'snapshot_initiated'        => 1,
		'files_fetch_completed'     => 1,
		'zipstream_completed'       => 1,
		'files_upload_completed'    => 2,
		'tables_snapshot_started'   => 2,
		'tables_snapshot_completed' => 3,
		'snapshot_not_exported'     => 3,
		'snapshot_completed'        => 4,
	);

	/**
	 * Getting info of specific backup request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'snapshots';

	/**
	 * Progress constructor.
	 *
	 * @param bool $needs_api_call Whether we need to do an api call to retrieve the progress.
	 */
	public function __construct( $needs_api_call ) {
		$this->set( 'needs_api_call', $needs_api_call );
	}

	/**
	 * Build running backup info for displaying.
	 *
	 * @param array $backup The backup we're going to display the info for.
	 *
	 * @return string The HTML for the backup row.
	 */
	public function get_running_backup_info( $backup ) {
		if ( empty( $backup ) ) {
			return false;
		}

		$export_text = 'Loading';

		$backup_status        = $this->get( 'backup_running_status' );
		$stored_backup_status = get_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
		if ( true === $this->get( 'needs_api_call' ) && 'just_triggered' !== $stored_backup_status ) {
			$response = $this->get_backup_status( $backup['id'] );

			$running_backup = json_decode( wp_remote_retrieve_body( $response ), true );

			$backup_status = isset( $running_backup['snapshot_status'] ) ? $running_backup['snapshot_status'] : 'just_triggered';
			$exports       = isset( $running_backup['tpd_exp_status'] ) ? $running_backup['tpd_exp_status'] : array();

			$backup_status = apply_filters( 'snapshot_custom_service_error', $backup_status );
			Controller\Service\Backup::save_backup_error( $backup['id'], $backup_status, time() );

			$export_text = Model\Request\Listing::get_backup_export_texts( $exports, true );

			$this->set( 'backup_running_status', $backup_status );
			if ( 0 === strpos( $backup_status, 'snapshot_failed_' ) ) {
				$this->set( 'backup_failed', true );

				// Lets delete the local entries too, in case the service wasnt able to hit the finish_backup endpoint.
				delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
				delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
			}
		}//end if

		if ( 'just_triggered' === $stored_backup_status ) {
			// Lets see if we're stuck in just_triggered stage for more than 30 mins. If so, we can safely assume that backup to have failed.
			$manual_trigger_time = get_site_option( Controller\Ajax\Backup::SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME );

			if ( time() - $manual_trigger_time > SNAPSHOT4_BACKUP_TIMEOUT ) {
				delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
				delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
				delete_site_option( Controller\Ajax\Backup::SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME );

				$this->set( 'backup_failed', true );
				delete_transient( 'snapshot_listed_backups' );
				delete_transient( 'snapshot_current_stats' );
			}
		}

		$this->set( 'export_text', $export_text );

		$index   = array_search( $backup_status, self::STATUSES, true );
		$percent = intval( round( $index / ( count( self::STATUSES ) - 1 ) * 100 ) );

		$step     = array_key_exists( $backup_status, self::STEPS ) ? self::STEPS[ $backup_status ] : 0;
		$step_max = max( array_values( self::STEPS ) );

		$id            = esc_attr( 'snapshot_backup_id_' . $backup['id'] );
		$backup_id     = esc_attr( $backup['id'] );
		$name          = esc_attr( $backup['name'] );
		$progress_text = esc_attr( $percent . '%' );
		$percent_width = esc_attr( $percent . '%' );
		$percent       = esc_attr( $percent );

		$backup_info =
		'<tr id="' . $id . '"
	class="current-backup-row sui-accordion-item"
	data-id="' . $id . '"
	data-backup-id="' . $backup_id . '"
	data-name="' . $name . '"
	data-progress-text="' . $progress_text . '"
	data-percent-width="' . $percent_width . '"
	data-percent="' . $percent . '"
	data-step="' . $step . '"
	data-step-max="' . $step_max . '"
	>
	<td class="sui-hidden-xs sui-table-item-title">
		<span class="sui-icon-snapshot" aria-hidden="true"></span>
		<span class="backup-name">' . $name . '</span>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray">
		<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
		' . Settings::get_brand_name() . '
	</td>
	<td class="sui-hidden-xs snapshot-backup-export-destinations sui-table-item-title gray">
			<span style="display:inline-block;">
				<span class="sui-icon-loader sui-loading snapshot-destination-loader" aria-hidden="true"></span>'
				. __( 'Loading...', 'snapshot' ) . '
			</span>
	</td>
	<td class="sui-hidden-xs last-child">
		<div class="sui-progress" style="width: 130px; float: left;">
			<span class="sui-progress-icon" aria-hidden="true"><span class="sui-icon-loader sui-loading"></span></span>
			<span class="sui-progress-text"><span class="progress-text">' . $progress_text . '</span></span>
			<div class="sui-progress-bar" aria-hidden="true"><span class="percent-width" style="width: ' . $percent_width . ';"></span></div>
		</div>
		<span class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down" aria-hidden="true"></span></span>
	</td>

	<td class="sui-hidden-sm sui-hidden-md sui-hidden-lg sui-table-item-title mobile-row" colspan="4">
		<div class="sui-table-item-title">
			<div class="sui-progress" style="width: 90%; float: left;">
				<div class="sui-table-item-title">
					<span class="sui-icon-snapshot sui-md" aria-hidden="true"></span>
				</div>
				<span class="sui-progress-icon" aria-hidden="true"><span class="sui-icon-loader sui-loading"></span></span>
				<span class="sui-progress-text"><span class="progress-text">' . $progress_text . '</span></span>
				<div class="sui-progress-bar" aria-hidden="true"><span class="percent-width" style="width: ' . $percent_width . ';"></span></div>
			</div>
			<span class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down" aria-hidden="true"></span></span>
			<div style="clear: both;"></div>
		</div>

		<div class="sui-row running-backup-desc">
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Storage', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray">
					<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
					' . Settings::get_brand_name() . '
				</div>
			</div>
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Export Destination', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray snapshot-backup-export-destinations">
					<span style="display:inline-block;">
						<span class="sui-icon-loader sui-loading snapshot-destination-loader" aria-hidden="true"></span>'
						. __( 'Loading...', 'snapshot' ) . '
					</span>
				</div>
			</div>
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Frequency', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray">' . esc_html__( 'None', 'snapshot' ) . '</div>
			</div>
		</div>
	</td>
</tr>
<tr class="sui-accordion-item-content snapshot-details-row current-backup-details">
	<td colspan="4" class="sui-hidden-xs current-backup-step-wrap step-' . $step . '">
		<div class="sui-box">
			<div class="sui-box-body">
				<div class="progressbar-header">
					<p>' . esc_html__( 'Backup is in progress', 'snapshot' ) . '</p>
					<p class="current-step">' .
					/* translators: %1$d - step, %2$d - step max*/
					esc_html( sprintf( __( 'Step %1$d/%2$d', 'snapshot' ), $step, $step_max ) ) . '</p>
				</div>

				<section>
					<div class="progressbar-container">
						<div class="progressbar-status">
							<div role="alert" class="sui-screen-reader-text" aria-live="assertive">
								<p>Snapshot progress at 0%</p>
							</div>
						</div>
						<ul class="progress-circles" aria-hidden="true">
							<li class="circle sui-tooltip ci-step-1" data-tooltip="' . esc_attr__( 'Backup initiated', 'snapshot' ) . '">
								<span class="sui-icon-check"></span>
							</li>
							<li class="circle sui-tooltip ci-step-2" data-tooltip="' . esc_attr__( 'Files have been backed up successfully', 'snapshot' ) . '">
								<span class="sui-icon-check"></span>
							</li>
							<li class="circle sui-tooltip ci-step-3" data-tooltip="' . esc_attr__( 'Database has been backed up successfully', 'snapshot' ) . '">
								<span class="sui-icon-check"></span>
							</li>
						</ul>
					</div>
				</section>


				<div class="progress-title">
					<p><span class="lt-step-1">' . esc_html__( 'Initiating backup', 'snapshot' ) . '</span><span class="on-step-1">' . esc_html__( 'Backup initiated', 'snapshot' ) . '</span></p>
					<p><span class="lt-step-2">' . esc_html__( 'Files', 'snapshot' ) . '</span><span class="on-step-2">' . esc_html__( 'Files are backed up', 'snapshot' ) . '</span></p>
					<p><span class="lt-step-3">' . esc_html__( 'Database', 'snapshot' ) . '</span><span class="on-step-3">' . esc_html__( 'Database is backed up', 'snapshot' ) . '</span></p>
					<p><span class="lt-step-4">' . esc_html__( 'Finalize backup', 'snapshot' ) . '</span><span class="on-step-4">' . esc_html__( 'Backup finalized', 'snapshot' ) . '</span></p>
				</div>

			</div>
			<div class="sui-box-footer" style="justify-content: space-between;">
				<button role="button" class="sui-button sui-button-ghost button-cancel-backup" disabled>' . esc_html__( 'Cancel', 'snapshot' ) . '</button>
				<button role="button" class="sui-button sui-button-ghost button-view-log" disabled><span class="sui-icon-eye" aria-hidden="true"></span>' . esc_html__( 'View logs', 'snapshot' ) . '</button>
			</div>
		</div>
	</td>

	<td colspan="4" class="sui-hidden-sm sui-hidden-md sui-hidden-lg current-backup-step-wrap step-' . $step . '">
		<div class="sui-box">
			<div class="sui-box-body">
				<div class="sui-row">
					<div class="sui-col-xs-6">
						<div class="sui-table-item-title sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Storage', 'snapshot' ) . '</div>
						<div class="sui-table-item-title gray">
							<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
							' . Settings::get_brand_name() . '
						</div>
					</div>
					<div class="sui-col-xs-6">
						<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Export Destination', 'snapshot' ) . '</div>
						<div class="sui-table-item-title gray snapshot-backup-export-destinations">
							<span style="display:inline-block;">
								<span class="sui-icon-loader sui-loading snapshot-destination-loader" aria-hidden="true"></span>'
								. __( 'Loading...', 'snapshot' ) . '
							</span>
						</div>
					</div>
					<div class="sui-col-xs-6">
						<div class="sui-table-item-title sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Frequency', 'snapshot' ) . '</div>
						<div class="sui-table-item-title gray">' . esc_html__( 'Once', 'snapshot' ) . '</div>
					</div>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-1"></div>
					<div class="progressbar mobile-100 on-step-1"></div>
					<div class="progress-circles mobile"><div class="circle active on-step-1"></div></div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-1">' . esc_html__( 'Initiating backup', 'snapshot' ) . '</span><span class="on-step-1">' . esc_html__( 'Backup initiated', 'snapshot' ) . '</span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-2"></div>
					<div class="progressbar mobile-100 on-step-2"></div>
					<div class="progress-circles mobile"><div class="circle active on-step-2"></div></div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-2">' . esc_html__( 'Files', 'snapshot' ) . '</span><span class="on-step-2">' . esc_html__( 'Files are backed up', 'snapshot' ) . '</span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-3"></div>
					<div class="progressbar mobile-100 on-step-3"></div>
					<div class="progress-circles mobile"><div class="circle active on-step-3"></div></div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-3">' . esc_html__( 'Database', 'snapshot' ) . '</span><span class="on-step-3">' . esc_html__( 'Database is backed up', 'snapshot' ) . '</span></p>
				</div>

				<div class="progressbar-container mobile">
					<div class="progressbar mobile-0 lt-step-4"></div>
					<div class="progressbar mobile-100 on-step-4"></div>
					<div class="progress-circles mobile"><div class="circle active on-step-4"></div></div>
				</div>
				<div class="progress-title mobile">
					<p><span class="lt-step-4">' . esc_html__( 'Finalize backup', 'snapshot' ) . '</span><span class="on-step-4">' . esc_html__( 'Backup finalized', 'snapshot' ) . '</span></p>
				</div>

			</div>
			<div class="sui-box-footer" style="justify-content: flex-start;">
				<button role="button" class="sui-button sui-button-ghost button-cancel-backup">' . esc_html__( 'Cancel', 'snapshot' ) . '</button>
			</div>
		</div>
	</td>
</tr>';

		return $backup_info;
	}

	/**
	 * Asks the API for the progress status of a specific backup.
	 *
	 * @param string $backup_id The id of the backup we need the status for.
	 *
	 * @return array|mixed|object
	 */
	public function get_backup_status( $backup_id ) {
		$data   = array();
		$method = 'get';
		$path   = trailingslashit( $this->get_api_url() ) . $backup_id;

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}