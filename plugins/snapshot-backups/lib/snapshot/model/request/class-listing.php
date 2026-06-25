<?php // phpcs:ignore
/**
 * Snapshot models: Backup listing requests model
 *
 * Holds information for communication with the service about listing existing backups.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model;

/**
 * Listing backups requests model class
 */
class Listing extends Model\Request {


	/**
	 * Listing backups request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'snapshots';

	/**
	 * Retrieves a list of all backups.
	 *
	 * @return array|mixed|object
	 */
	public function list_backups() {
		$data   = array();
		$method = 'get';
		$path   = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Build backup info for displaying.
	 *
	 * @param array $backup The backup we're going to display the info for.
	 *
	 * @return string The HTML for the backup row.
	 */
	public function get_backup_info( $backup ) {
		$failed_backups_number = $this->get( 'failed_backups' );

		$backup_info['id']   = isset( $backup['snapshot_id'] ) ? $backup['snapshot_id'] : null;
		$backup_info['size'] = isset( $backup['snapshot_size'] ) ? ( '0' === $backup['snapshot_size'] ? '1 MB' : $backup['snapshot_size'] . ' MB' ) : '? MB';

		$export_link     = get_site_transient( 'snapshot_export_' . $backup_info['id'] );
		$has_export_link = $export_link ? true : false;

		$backup_info['timestamp'] = strtotime( $backup['created_at'] );
		$destination_icon_details = Settings::get_icon_details();

		$backup_info['date'] = Helper\Datetime::format( $backup_info['timestamp'] );
		$backup_info['name'] = isset( $backup['bu_snapshot_name'] ) ? $backup['bu_snapshot_name'] : null;
		if ( is_null( $backup_info['name'] ) || '' === $backup_info['name'] || 'null' === $backup_info['name'] ) {
			$backup_info['name'] = $backup_info['date'];
		}

		if ( isset( $backup['description'] ) && ! empty( $backup['description'] ) ) {
			if ( 30 < strlen( $backup['description'] ) ) {
				$backup_info['description'] = substr( $backup['description'], 0, 30 ) . ' &hellip;';
			} else {
				$backup_info['description'] = $backup['description'];
			}
		}

		$failed_backup = false;
		if ( 0 === strpos( $backup['snapshot_status'], 'snapshot_failed_' ) ) {
			$failed_backup = true;

			++$failed_backups_number;
			$this->set( 'failed_backups', $failed_backups_number );
		}

		$global_exclusions = array();
		if ( isset( $backup['excluded_files'] ) ) {
			$excluded_files_list = preg_replace( '/(^\[)|(\]$)/u', '', $backup['excluded_files'] );
			$excluded_files      = '' === $excluded_files_list ? array() : explode( ',', $excluded_files_list );
			if ( is_array( $excluded_files ) ) {
				$global_exclusions['files'] = $excluded_files;
			}
		}

		if ( isset( $backup['excluded_tables'] ) ) {
			$excluded_tables_list = preg_replace( '/(^\[)|(\]$)/u', '', $backup['excluded_tables'] );
			$excluded_tables      = '' === $excluded_tables_list ? array() : explode( ',', $excluded_tables_list );
			if ( is_array( $excluded_tables ) ) {
				$global_exclusions['tables'] = $excluded_tables;
			}
		}

		$row_class = ( $failed_backup ) ? ' snapshot-failed-backup' : '';
		$row_icon  = ( $failed_backup )
		? 'sui-icon-warning-alert'
		: self::get_backup_icon( $backup['type'] );

		$row_loading_icon = '<span class="sui-button-icon sui-button-blue sui-button-onload d-nonex snapshot-export--loading__icon" role="alert" aria-live="assertive">
			<span class="sui-loading-text" aria-hidden="true">
				<span class="sui-icon-academy"></span>
			</span>
			<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			<span class="sui-screen-reader-text">' . esc_html__( 'Exporting...', 'snapshot' ) . '</span>
		</span>';

		$row_download_button = '';

		if ( $has_export_link ) {
			$row_download_button = '<a class="sui-button-icon sui-button-green snapshot-download--backup" href="' . esc_url( $export_link ) . '" target="_blank">
				<span class="sui-icon-download" aria-hidden="true"></span>
				<span class="sui-screen-reader-text">' . esc_html__( 'Download backup', 'snapshot' ) . '</span>
			</a>';
		}

		$row_accordion_indicator = ( $failed_backup ) ? '' : '<span class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down" aria-hidden="true"></span></span>';
		$nonce                   = wp_create_nonce( 'snapshot_get_backup_log' );
		$row_failed_buttons      = ( $failed_backup ) ?
		'
	<button class="sui-button sui-button-ghost view-log view-log--text" data-nonce="' . esc_attr( $nonce ) . '" data-backup-id="' . esc_attr( $backup['snapshot_id'] ) . '">
		<span>
			' . esc_html__( 'View logs', 'snapshot' ) . '
		</span>
	</button>
	<button class="sui-button-icon sui-button-ghost view-log sui-tooltip" data-tooltip="' . esc_attr__( 'View log', 'snapshot' ) . '" data-nonce="' . esc_attr( $nonce ) . '" data-backup-id="' . esc_attr( $backup['snapshot_id'] ) . '">
		<span class="sui-icon-eye" aria-hidden="true"></span>
	</button>
	<button class="sui-button-icon sui-button-red sui-tooltip snapshot-delete-backup" data-tooltip="' . esc_html__( 'Delete', 'snapshot' ) . '" onclick="jQuery(window).trigger(\'snapshot:delete_backup\', [\'' . esc_attr( $backup['snapshot_id'] ) . '\'])">
		<span class="sui-icon-trash" aria-hidden="true"></span>
		<span class="sui-screen-reader-text">' . esc_html__( 'Delete', 'snapshot' ) . '</span>
	</button>
'
		: '';

		$frequency_human = '';
		if ( isset( $backup['bu_frequency'] ) ) {
			switch ( $backup['bu_frequency'] ) {
				case 'daily':
					$frequency_human = __( 'Daily', 'snapshot' );
					break;
				case 'weekly':
					$frequency_human = __( 'Weekly', 'snapshot' );
					break;
				case 'monthly':
					$frequency_human = __( 'Monthly', 'snapshot' );
					break;
				default:
					$frequency_human = __( 'None', 'snapshot' );
					break;
			}
		}

		$icon_tooltip_text = self::get_backup_icon_tooltip_text( $backup['type'] );
		if ( $failed_backup ) {
			$icon_tooltip_text = __( 'This backup has failed. Check the logs for further information.', 'snapshot' );
		}

		$destination_text = self::get_backup_destination_text( $backup['type'] );
		$export_status    = isset( $backup['tpd_exp_status'] ) ? $backup['tpd_exp_status'] : array();
		$export_text      = self::get_backup_export_texts( $export_status );

		add_filter(
			'safe_style_css',
			function ( $styles ) {
				$styles[] = '--tooltip-width';
				return $styles;
			}
		);

		$description = ( isset( $backup_info['description'] ) ) ? $backup_info['description'] : '';
		if ( $description && '' !== $description && 'null' !== $description ) {
			$row_class .= ' snapshot-has-comment';
		}

		$desc_html = '';
		if ( 'null' !== $description ) {
			$desc_html = '<span class="sui-description">' . wp_kses_post( $description ) . '</span>';
		}

		$backup_info['row'] =
		'<tr class="snapshot-row' . esc_attr( $row_class ) . '" data-backup_id="' . esc_attr( $backup['snapshot_id'] ) . '" data-destination_text="' . esc_attr( $export_text['destination']['text'] ) . '" data-destination_tooltip="' . esc_attr( $export_text['destination']['tooltip'] ) . '">
	<td class="sui-hidden-xs sui-table-item-title">
		<div class="sui-tooltip sui-tooltip-top-left snapshot-icon-tooltip" data-tooltip="' . esc_attr( $icon_tooltip_text ) . '"></div>
		<span class="' . esc_attr( $row_icon ) . '" aria-hidden="true"></span>
		<span class="snapshot-backup--name">
			' . esc_html( $backup_info['name'] ) . '
			' . stripslashes( $desc_html ) . '
		</span>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray">';

		ob_start();
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

		$backup_info['row'] .= ob_get_clean() . esc_html( $destination_text ) . '
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray snapshot-export-column">
		<span style="display:inline-block;">'
		. wp_kses_post( $export_text['row'] ) . '
		</span>
	</td>
	<td class="sui-hidden-xs sui-table-item-title gray snapshot-schedule-column last-child">
		<div style="display: flex; justify-content: flex-end;">
			<span class="frequency" style="margin-right: auto;">' . esc_html( $frequency_human ) . '</span>' .
			$row_failed_buttons .
			$row_loading_icon .
			$row_download_button .
			$row_accordion_indicator . '
		</div>
	</td>

	<td class="sui-hidden-sm sui-hidden-md sui-hidden-lg sui-table-item-title mobile-row" colspan="4">
		<div class="sui-table-item-title">
			<span class="' . esc_attr( $row_icon ) . ' sui-md" aria-hidden="true"></span>
			' . esc_html( $backup_info['name'] ) .
		$row_failed_buttons .
		$row_accordion_indicator . '
		</div>
		<div class="sui-row">
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Storage', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray">';
		ob_start();
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
		$backup_info['row'] .= ob_get_clean() . esc_html( $destination_text ) . '
				</div>
			</div>
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Export destination', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray">
				<span style="display:inline-block;">'
		. wp_kses_post( $export_text['row_mobile'] ) . '
				</span>
				</div>
			</div>
			<div class="sui-col-xs-6">
				<div class="sui-table-item-title snapshot-mobile-title">' . esc_html__( 'Frequency', 'snapshot' ) . '</div>
				<div class="sui-table-item-title gray">' . esc_html( $frequency_human ) . '</div>
			</div>
		</div>
	</td>

	<td class="snapshot-restoration sui-hidden-xs sui-table-item-title first-child">
		<span class="' . esc_attr( $row_icon ) . '" aria-hidden="true"></span>
		<span class="backup-name">' . esc_html( $backup_info['name'] ) . '</span>
	</td>
	<td class="snapshot-restoration sui-hidden-xs sui-table-item-title">';

		ob_start();
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

		$backup_info['row'] .= ob_get_clean() . esc_html( $destination_text ) . '
	</td>
	<td class="snapshot-restoration sui-hidden-xs sui-table-item-title snapshot-export-column">
		<span style="display:inline-block;">'
		. wp_kses_post( $export_text['row_mobile'] ) . '
		</span>
	</td>
	<td class="snapshot-restoration sui-hidden-xs last-child">
		<div class="sui-progress" style="width: 130px; float: left;">
			<span class="sui-progress-icon" aria-hidden="true"><span class="sui-icon-loader sui-loading"></span></span>
			<span class="sui-progress-text"><span class="progress-text"></span></span>
			<div class="sui-progress-bar" aria-hidden="true"><span class="percent-width" style="width: 0%;"></span></div>
		</div>
		<span class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down" aria-hidden="true"></span></span>
	</td>

	<td class="snapshot-restoration sui-hidden-sm sui-hidden-md sui-hidden-lg sui-table-item-title mobile-row" colspan="4">
		<div class="sui-table-item-title">
			<div class="sui-progress" style="width: 90%; float: left;">
				<div class="sui-table-item-title">
					<span class="' . esc_attr( $row_icon ) . ' sui-md" aria-hidden="true"></span>
				</div>
				<span class="sui-progress-icon" aria-hidden="true"><span class="sui-icon-loader sui-loading"></span></span>
				<span class="sui-progress-text"><span class="progress-text"></span></span>
				<div class="sui-progress-bar" aria-hidden="true"><span class="percent-width" style="width: 0%;"></span></div>
			</div>
			<span class="sui-accordion-open-indicator" aria-label="Expand"><span class="sui-icon-chevron-down" aria-hidden="true"></span></span>
		</div>
	</td>

</tr>';

		$template = new Helper\Template();
		ob_start();
		$template->render(
			'pages/backups/snapshot-details-row',
			array(
				'snapshot_id'        => $backup['snapshot_id'],
				'snapshot_name'      => $backup_info['name'],
				'date'               => $backup_info['date'],
				'global_exclusions'  => $global_exclusions,
				'size'               => $backup_info['size'],
				'frequency_human'    => $frequency_human,
				'last_snap'          => ! empty( $backup['last_snap'] ),
				'backup_type'        => $backup['type'],
				'destination_text'   => $destination_text,
				'export_details'     => $export_text['details'],
				'add_export_notice'  => $export_text['successful_exports'] > 0,
				'plugin_custom_name' => Settings::get_brand_name(),
				'description'        => isset( $backup['description'] ) ? $backup['description'] : false,
			)
		);
		$backup_info['row_content'] = ob_get_clean();

		$backup_info['is_failed'] = $failed_backup;

		$backup['tpd_exp_done'] = isset( $backup['tpd_exp_done'] )
			// @TODO: fix JSON
			? str_replace( "'", '"', $backup['tpd_exp_done'] )
			: null;

		$tpd_exp_done = ( isset( $backup['tpd_exp_done'] ) && '' !== $backup['tpd_exp_done'] ) ? json_decode( $backup['tpd_exp_done'], true ) : array();
		$done_tpd_ids = array();
		if ( isset( $tpd_exp_done['tpd_s3'] ) ) {
			foreach ( $tpd_exp_done['tpd_s3'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_azure'] ) ) {
			foreach ( $tpd_exp_done['tpd_azure'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_gdrive'] ) ) {
			foreach ( $tpd_exp_done['tpd_gdrive'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_dropbox'] ) ) {
			foreach ( $tpd_exp_done['tpd_dropbox'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_ftp'] ) ) {
			foreach ( $tpd_exp_done['tpd_ftp'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_sftp'] ) ) {
			foreach ( $tpd_exp_done['tpd_sftp'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		if ( isset( $tpd_exp_done['tpd_onedrive'] ) ) {
			foreach ( $tpd_exp_done['tpd_onedrive'] as $tpd_value => $export_status ) {
				if ( 'export_success' === $export_status ) {
					$done_tpd_ids[] = $tpd_value;
				}
			}
		}

		$backup_info['done_tpd_ids'] = $done_tpd_ids;

		return $backup_info;
	}

	/**
	 * Utility function to sort backups chronologically.
	 *
	 * @param array $backups The backups to be sorted chronologically.
	 *
	 * @return array
	 */
	public function sort_backups( $backups ) {
		usort(
			$backups,
			function ( $backup1, $backup2 ) {
				$datetime1 = strtotime( $backup1['created_at'] );
				$datetime2 = strtotime( $backup2['created_at'] );

				return $datetime2 - $datetime1;
			}
		);

		return $backups;
	}

	/**
	 * Returns backup icon class
	 *
	 * @param string $backup_type Type of backup.
	 *
	 * @return string
	 */
	public static function get_backup_icon( $backup_type ) {
		return 'automate' === $backup_type ? 'sui-icon-automate' : 'sui-icon-snapshot';
	}

	/**
	 * Returns backup icon tooltip
	 *
	 * @param string $backup_type Type of backup.
	 *
	 * @return string
	 */
	public static function get_backup_icon_tooltip_text( $backup_type ) {
		/* translators: %s - brand name */
		$text = sprintf( __( '%s Scheduled backup', 'snapshot' ), Settings::get_brand_name() );
		if ( 'automate' === $backup_type ) {
			/* translators: %s - brand name */
			$text = sprintf( __( '%s Automated backup', 'snapshot' ), Settings::get_brand_name() );
		} elseif ( 'manual' === $backup_type ) {
			/* translators: %s - brand name */
			$text = sprintf( __( '%s Manual backup', 'snapshot' ), Settings::get_brand_name() );
		}
		return $text;
	}

	/**
	 * Returns backup destination
	 *
	 * @param string $backup_type Type of backup.
	 *
	 * @return string
	 */
	public static function get_backup_destination_text( $backup_type ) {
		$white_label_plugin_name = Settings::get_brand_name();
		$text                    = $white_label_plugin_name;
		if ( 'automate' === $backup_type ) {
			/* translators: %s - Plugin Name */
			$text = sprintf( __( '%s (Automate)', 'snapshot' ), $white_label_plugin_name );
		}
		return $text;
	}

	/**
	 * Returns HTML for 'Export Destination' column of each backup.
	 *
	 * @param string|array $exports List of performed exports with their statuses.
	 * @param bool         $running_backup Whether it's a running backup we're talking about or an already completed one.
	 *
	 * @return array
	 */
	public static function get_backup_export_texts( $exports, $running_backup = false ) {
		$exports = ( ! is_array( $exports ) && '' !== $exports && null !== $exports ) ? str_replace( "'", '"', $exports ) : $exports;
		$exports = ( ! is_array( $exports ) && '' !== $exports && null !== $exports ) ? json_decode( $exports, true ) : $exports;

		$export_info   = array();
		$total_exports = array();
		$exports_exist = false;
		$first_type    = '';

		if ( empty( $exports ) ) {
			$export_info['row']        = ( $running_backup ) ? 'None' : __( 'None', 'snapshot' );
			$export_info['row_mobile'] = $export_info['row'];
			$export_info['details']    = '';

			$export_info['successful_exports'] = 0;

			$export_info['destination'] = array(
				'text'    => Settings::get_brand_name(),
				'tooltip' => '',
			);

			return $export_info;
		}

		if ( isset( $exports['tpd_s3'] ) ) {
			$exports_exist       = true;
			$total_exports['s3'] = $exports['tpd_s3'];
		}

		if ( isset( $exports['tpd_gdrive'] ) ) {
			$exports_exist           = true;
			$total_exports['gdrive'] = $exports['tpd_gdrive'];
		}

		if ( isset( $exports['tpd_dropbox'] ) ) {
			$exports_exist            = true;
			$total_exports['dropbox'] = $exports['tpd_dropbox'];
		}

		if ( isset( $exports['tpd_ftp'] ) ) {
			$exports_exist        = true;
			$total_exports['ftp'] = $exports['tpd_ftp'];
		}

		if ( isset( $exports['tpd_sftp'] ) ) {
			$exports_exist         = true;
			$total_exports['sftp'] = $exports['tpd_sftp'];
		}

		if ( isset( $exports['tpd_onedrive'] ) ) {
			$exports_exist             = true;
			$total_exports['onedrive'] = $exports['tpd_onedrive'];
		}

		if ( isset( $exports['tpd_azure'] ) ) {
			$exports_exist          = true;
			$total_exports['azure'] = $exports['tpd_azure'];
		}

		if ( $exports_exist ) {
			$exports_count              = 0;
			$first_export               = '';
			$exports_tooltip            = '';
			$failed_export              = false;
			$warning_icon_header        = '';
			$warning_icon_header_mobile = '';
			$export_details             = '';
			$successful_exports         = 0;

			foreach ( $total_exports as $type => $type_exports ) {
				foreach ( $type_exports as $name => $status ) {
					if ( ! $running_backup ) {
						if ( false !== strpos( $name, ' ', 0 ) ) {
							list($export_id, $name) = explode( ' ', $name, 2 );
						}
					}
					$first_type       = empty( $first_type ) ? $type : $first_type;
					$first_export     = empty( $first_export ) ? $name : $first_export;
					$exports_tooltip .= $name . ', ';
					++$exports_count;
					$successful_exports += 'export_success' === $status ? 1 : 0;

					$export_details .=
					'<div class="sui-col-md-3 sui-col-xs-6">
	<span class="sui-settings-label">' .
					/* translators: %d - exports count */
					sprintf( __( 'Export Destination %d', 'snapshot' ), $exports_count ) . '</span>';

					if ( ! $failed_export && 'export_failed' === $status ) {
						// If even one export was failed, show warning icon in the header.
						$failed_export              = true;
						$warning_icon_header        = "<span class='sui-tooltip sui-tooltip-constrained snapshot-export-icon snapshot-export-failure' data-tooltip='" . esc_html__( 'Backup failed to export to the connected destination.', 'snapshot' ) . "'><span class='sui-icon-warning-alert' aria-hidden='true'></span></span>";
						$warning_icon_header_mobile = "<span class='sui-tooltip sui-tooltip-left sui-tooltip-constrained snapshot-export-icon snapshot-export-failure' style='--tooltip-width: 170px;' data-tooltip='" . esc_html__( 'Backup failed to export to the connected destination.', 'snapshot' ) . "'><span class='sui-icon-warning-alert' aria-hidden='true'></span></span>";
					}

					if ( 'export_failed' === $status ) {
						$export_details .= '
	<span class="snapshot-export-backup-details snapshot-' . $type . '-export-backup-details">' . $name . '</span>
	<span class="sui-tooltip sui-tooltip-constrained sui-tooltip-left-mobile snapshot-export-icon snapshot-export-details-failure" data-tooltip="' .
						/* translators: %d - brand name */
						esc_html( sprintf( __( 'The backup is stored on %s storage, but has failed to export to the connected destination. Make sure you have the destination set up correctly and try to run the backup again.', 'snapshot' ), Settings::get_brand_name() ) ) . '">
		<span class="sui-icon-warning-alert" aria-hidden="true"></span>
	</span>
	<span class="sui-tooltip sui-tooltip-constrained sui-tooltip-left-mobile snapshot-export-icon snapshot-export-details-failure2" data-tooltip="' .
						/* translators: %s - Plugin Name */
						esc_html( sprintf( __( 'The backup is stored on %s storage, but has failed to export to the connected destination. Make sure you have the destination set up correctly and try to run the backup again.', 'snapshot' ), Settings::get_brand_name() ) ) . '">
		<span class="sui-icon-warning-alert" aria-hidden="true"></span>
	</span>';
					} else {
						$export_details .= '
	<span class="snapshot-export-backup-details snapshot-' . $type . '-export-backup-details sui-tooltip snapshot-export-icon snapshot-export-details-success" data-tooltip="' . esc_html__( 'Exported successfully', 'snapshot' ) . '">' . $name . '
		<span class="sui-icon-check-tick" aria-hidden="true"></span>
	</span>';
					}

					$export_details .= '
</div>';

				}//end foreach
			}//end foreach

			$exports_tooltip = rtrim( rtrim( $exports_tooltip ), ',' );

			$destination_text    = Settings::get_brand_name();
			$destination_tooltip = '';

			if ( 1 < $exports_count ) {
				/* translators: %d - Number of configured 3rd party destinations */
				$export_text = $first_export . sprintf( __( ' + %d more', 'snapshot' ), $exports_count - 1 );
				$export_row  = "<span class='snapshot-export-backup-header snapshot-" . $first_type . "-export-backup-header sui-tooltip sui-tooltip-left-mobile sui-tooltip-constrained' style='--tooltip-width: 170px;' data-tooltip='" . $exports_tooltip . "'>" . $export_text;

				$destination_text   .= ', ' . $export_text;
				$destination_tooltip = Settings::get_brand_name() . ', ' . $exports_tooltip;
			} else {
				$export_row = "<span class='snapshot-export-backup-header snapshot-" . $first_type . "-export-backup-header'>" . $first_export;

				$destination_text .= ', ' . $first_export;
			}

			$export_info['row']        = $export_row . $warning_icon_header . '</span>';
			$export_info['row_mobile'] = $export_row . $warning_icon_header_mobile . '</span>';
			$export_info['details']    = $export_details;

			$export_info['html']['exports_count']     = $exports_count;
			$export_info['html']['first_export']      = $first_export;
			$export_info['html']['first_export_type'] = $first_type;
			$export_info['html']['exports_tooltip']   = $exports_tooltip;

			$export_info['successful_exports'] = $successful_exports;

			$export_info['destination'] = array(
				'text'    => $destination_text,
				'tooltip' => $destination_tooltip,
			);

			return $export_info;
		}//end if
	}
}