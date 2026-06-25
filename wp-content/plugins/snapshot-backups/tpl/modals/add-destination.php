<?php // phpcs:ignore
/**
 * Add destinations modal.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;

$assets = new Helper\Assets();

wp_nonce_field( 'snapshot_s3_connection', '_wpnonce-snapshot_s3_connection' );
wp_nonce_field( 'snapshot_azure_connection', '_wpnonce-snapshot_azure_connection' );

?>

<div class="sui-modal sui-modal-md" id="snapshot-add-destination-modal">
	<div
		role="dialog"
		id="snapshot-add-destination-dialog"
		class="sui-modal-content"
		aria-modal="true"
	>

		<?php
		$this->render(
			'modals/modal_parts/add-destination-screen-1',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-2-s3',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-3-s3',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-4-s3',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-2-gd',
			array(
				'assets'   => $assets,
				'auth_url' => $auth_url,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-3-gd',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/modal_parts/add-destination-screen-4-gd',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/destinations/dropbox/step-1',
			array(
				'assets'   => $assets,
				'auth_url' => $dropbox_auth_url,
			)
		);
		$this->render(
			'modals/destinations/dropbox/step-2',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/destinations/dropbox/step-3',
			array(
				'assets' => $assets,
			)
		);

		// FTP destination modals.
		$this->render(
			'modals/destinations/ftp/step-1',
			array(
				'assets' => $assets,
			)
		);

		$this->render(
			'modals/destinations/ftp/step-2',
			array(
				'assets' => $assets,
			)
		);

		// OneDrive destination modals.
		$this->render(
			'modals/destinations/onedrive/step-1',
			array(
				'assets'   => $assets,
				'auth_url' => $onedrive_auth_url,
			)
		);
		$this->render(
			'modals/destinations/onedrive/step-2',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/destinations/onedrive/step-3',
			array(
				'assets' => $assets,
			)
		);

		$this->render(
			'modals/destinations/azure/step-2',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/destinations/azure/step-3',
			array(
				'assets' => $assets,
			)
		);
		$this->render(
			'modals/destinations/azure/step-4',
			array(
				'assets' => $assets,
			)
		);
		?>

	</div>
</div>