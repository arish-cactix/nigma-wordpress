<?php // phpcs:ignore
/**
 * Snapshot controllers: Hosting backups AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use DateTime;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Hosting backups AJAX controller class
 */
class Hosting extends Controller\Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_snapshot-list_hosting_backups', array( $this, 'json_list_hosting_backups' ) );
		add_action( 'wp_ajax_snapshot-download_hosting_backup', array( $this, 'json_download_hosting_backup' ) );
	}

	/**
	 * Handles requesting the hosting API for actions about backup listing.
	 */
	public function json_list_hosting_backups() {
		$this->do_request_sanity_check( 'snapshot_list_hosting_backups', self::TYPE_POST );

		$args = array(
			'per_page' => 20,
			'page'     => isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1, //phpcs:ignore
		);

		$task     = new Task\Hosting\Listing();
		$response = $task->apply( $args );

		if ( ! is_array( $response ) ) {
			wp_send_json_error();
		}

		$backups = $response['result'];

		$template = new Helper\Template();
		foreach ( $backups as $key => $item ) {
			$item['icon'] = $item['is_automate'] ? 'automate' : 'storage-server-data';

			$item['icon_tooltip_text'] = $item['is_automate']
			/* translators: %s - brand name*/
				? sprintf( __( '%s Automate backup', 'snapshot' ), Settings::get_brand_name() )
				/* translators: %s - brand name*/
				: sprintf( __( '%s Hosting backup', 'snapshot' ), Settings::get_brand_name() );

			$item['destination_icon_details'] = Settings::get_icon_details();
			$item['destination_title']        = $item['is_automate']
			/* translators: %s - brand name*/
				? sprintf( __( '%s (Automate)', 'snapshot' ), Settings::get_brand_name() )
				/* translators: %s - brand name*/
				: sprintf( __( '%s (Hosting)', 'snapshot' ), Settings::get_brand_name() );

			$site_id                  = Helper\Api::get_site_id();
			$hub_hosting_backups_link = sprintf( 'https://wpmudev.com/hub2/site/%s/backups', rawurlencode( $site_id ) );
			$item['manage_link']      = $hub_hosting_backups_link;

			ob_start();
			$template->render( 'pages/hosting_backups/row', $item );
			$backups[ $key ]['html_row'] = ob_get_clean();
		}//end foreach

		$last_backup_ts = null;
		if ( isset( $backups[0] ) ) {
			$last_backup_ts = $backups[0]['created_at'];
		}

		$schedule        = $response['schedule'];
		$next_backup_ts  = Model\Schedule::get_next_backup_timestamp( 'daily', '02:00' );
		$backup_schedule = __( 'Nightly @', 'snapshot' ) . ' ' . Helper\Datetime::format( $next_backup_ts, Helper\Datetime::get_time_format() );

		if ( 'hourly' === $schedule ) {
			$backup_schedule = __( 'Hourly', 'snapshot' );
			$next_backup_ts  = (int) $last_backup_ts + 3600;
		}

		/* translators: %s - Backup schedule */
		$backup_schedule_tooltip = sprintf( __( 'The hosting backups are running %s. You can\'t update the schedule of hosting backups.', 'snapshot' ), $backup_schedule );

		$pagination = array(
			'pages'   => $response['total_pages'],
			'next'    => ( $args['page'] + 1 < $response['total_pages'] ) ? $args['page'] + 1 : $args['page'],
			'current' => $args['page'],
			'total'   => $response['total_backups'],
		);

		$pagination_html = $this->generate_pagination( $pagination );

		$pagination['html'] = $pagination_html;

		wp_send_json_success(
			array(
				'total_backups'           => $response['total_backups'],
				'pagination'              => $pagination,
				'backups'                 => $backups,
				'last_backup_time'        => Helper\Datetime::format( $last_backup_ts ),
				'next_backup_time'        => Helper\Datetime::format( $next_backup_ts ),
				'backup_schedule'         => $backup_schedule,
				'backup_schedule_tooltip' => $backup_schedule_tooltip,
			)
		);
	}

	/**
	 * Handles requesting the hosting API for backup downloading.
	 */
	public function json_download_hosting_backup() {
		$this->do_request_sanity_check( 'snapshot_download_hosting_backup', self::TYPE_POST );

		$data              = array();
		$data['backup_id'] = isset( $_POST['backup_id'] ) ? $_POST['backup_id'] : null; // phpcs:ignore

		$task = new Task\Hosting\Export();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args['backup_id'] = $validated_data['backup_id'];

		$result = $task->apply( $args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result ) );
		}

		wp_send_json_success(
			array(
				'api_response' => $result,
				/* translators: %s - Website hostname */
				'notice_html'  => sprintf( __( 'We are preparing your backup export for <strong>%s</strong>. You will recieve an email with the backup file to download.', 'snapshot' ), esc_html( wp_parse_url( get_site_url(), PHP_URL_HOST ) ) ),
			)
		);
	}

	/**
	 * Generates the pagination for hosting backups.
	 *
	 * @param array $args Args.
	 * @return string Pagination HTML.
	 */
	private function generate_pagination( $args = array() ) {
		$link = network_admin_url( 'admin.php?page=snapshot-hosting-backups' );

		$paginate_args = array(
			'type'      => 'array',
			'total'     => $args['pages'],
			'format'    => '?paged=%#%',
			'current'   => max( 1, $args['current'] ),
			'base'      => "{$link}&paged=%#%",
			'next_text' => '<i class="sui-icon-chevron-right" aria-hidden="true"></i>',
			'prev_text' => '<i class="sui-icon-chevron-left" aria-hidden="true"></i>',
		);

		return $this->format_pagination_html( (array) paginate_links( $paginate_args ), $args['total'] );
	}

	/**
	 * Modifies the pagination HTML.
	 *
	 * @param array $pages Number of pages.
	 * @param int   $total Total results.
	 * @return string
	 */
	private function format_pagination_html( array $pages, int $total ): string {

		$html  = '<div class="sui-pagination-wrap">';
		$html .= sprintf( "\t<span class='sui-pagination-results'>%d results</span>", $total );
		$html .= "\t<ul class='sui-pagination'>";
		foreach ( $pages as $page ) {
			$class = '';
			if ( false !== strpos( $page, 'span' ) ) {
				$page = str_replace( 'span', 'a', $page );
			}

			if ( false !== strpos( $page, 'class="next' ) || false !== strpos( $page, 'class="prev' ) ) {
				$page = str_replace( array( 'next', 'prev' ), '', $page );
			}

			if ( false !== strpos( $page, 'current' ) ) {
				$class = "class='sui-active'";
				$page  = str_replace( 'page-numbers', '', $page );
			}

			$html     .= "\t\t<li {$class}>";
				$html .= "\t\t\t{$page}";
			$html     .= "\t\t</li>";
		}

		$html .= "\t</ul>";
		$html .= '</div>';
		return $html;
	}
}