<?php // phpcs:ignore
/**
 * Snapshot controllers: activation setup controller
 *
 * Handles plugin activation.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4;

use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Setup activation class
 */
class Activate {

	/**
	 * Activate instance
	 *
	 * @var Activate
	 */
	protected static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Handles the plugin activation action.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->maybe_create_log_dir();
		$this->handle_migration();
		$this->maybe_create_snapshot_action_logs_table();
	}

	/**
	 * Invoke the private method
	 *
	 * @return void
	 */
	public function invoke_maybe_create_snapshot_action_logs_table(): void {
		$this->maybe_create_snapshot_action_logs_table();
	}

	/**
	 * Creates the singleton instance of this class.
	 *
	 * @return Activate
	 */
	public static function get_instance(): Activate {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Runs on plugin activation.
	 */
	public static function snapshot_activated() {
		self::get_instance();
	}

	/**
	 * Handles the creation of Log dir.
	 *
	 * @return void
	 */
	private function maybe_create_log_dir(): void {
		Log::check_dir( true );
	}

	/**
	 * Snapshot v3 to v4 migration.
	 *
	 * @return void
	 */
	private function handle_migration(): void {
		// Ensure no schedule stored at first install.
		if ( empty( get_site_option( 'snapshot_v4_installed' ) ) || empty( get_site_option( 'snapshot_v4_cleaned_up' ) ) ) {
			delete_site_option( 'wp_snapshot_backup_schedule' );

			add_site_option( 'snapshot_v4_installed', true );
			add_site_option( 'snapshot_v4_cleaned_up', true );
		}
	}

	/**
	 * Maybe create Snapshot logs table.
	 *
	 * @return void
	 */
	private function maybe_create_snapshot_action_logs_table(): void {
		/**
		 * Database access abstraction instance
		 *
		 * @var \wpdb
		 */
		global $wpdb;

		$table_name = $wpdb->prefix . 'snapshot_action_logs';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id INT AUTO_INCREMENT PRIMARY KEY,
			user_id INT NULL,
			action varchar(255) NOT NULL,
			details TEXT,
			performed_at DATETIME NOT NULL,
			INDEX(performed_at),
			INDEX(action)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}