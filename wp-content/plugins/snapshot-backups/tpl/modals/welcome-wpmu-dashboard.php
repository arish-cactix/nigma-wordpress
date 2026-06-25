<?php // phpcs:ignore
/**
 * Install/ log into Dashboard modal.
 *
 * @package snapshot
 */

$button_class = isset( $button_class ) && ! empty( $button_class ) ? $button_class : 'sui-button-ghost';
$modal_title  = isset( $modal_title ) && ! empty( $modal_title ) ? $modal_title : '';
$message      = isset( $message ) && ! empty( $message ) ? $message : '';
$button       = isset( $button ) && ! empty( $button ) ? $button : '';

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-md">
	<?php
	wp_nonce_field( 'snapshot_install_dashboard', '_wpnonce-snapshot_install_dashboard' );
	?>
	<div
		role="dialog"
		id="snapshot-welcome-dashboard-dialog"
		class="sui-modal-content"
		aria-modal="true"
	>

		<div class="sui-modal-slide <?php echo $active_first_slide ? 'sui-active sui-loaded' : ''; ?>" id="snapshot-welcome-dashboard-dialog-slide-1" data-modal-size="md">
			<div class="sui-box">

				<div class="sui-box-header sui-flatten sui-content-center">
					<figure class="sui-box-banner" role="banner" aria-hidden="true">
						<img src="<?php echo esc_attr( $assets->get_asset( 'img/snapshot-logo.svg' ) ); ?>">
					</figure>

					<h3 class="sui-box-title sui-lg"><?php echo esc_html( $modal_title ); ?></h3>
					<span class="sui-description"><?php echo esc_html( $message ); ?></span>

				</div>

				<div class="sui-box-body sui-lg sui-block-content-center">
					<button class="sui-button <?php echo sanitize_html_class( $button_class ); ?>" onclick="jQuery(window).trigger('snapshot:install_dashboard', ['<?php echo esc_attr( $installed ); ?>'])">
						<span class="sui-button-text-default">
							<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span>
							<?php echo esc_html( $button ); ?>
						</span>
						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php echo esc_html( $button_loading ); ?>
						</span>
					</button>
				</div>

			</div>
		</div>

		<div class="sui-modal-slide <?php echo ! $active_first_slide ? 'sui-active sui-loaded' : ''; ?>" id="snapshot-welcome-dashboard-dialog-slide-2" data-modal-size="md">
			<div class="sui-box">

				<div class="sui-box-header sui-flatten sui-content-center">

					<figure class="sui-box-banner" role="banner" aria-hidden="true">
						<img src="<?php echo esc_attr( $assets->get_asset( 'img/snapshot-logo.svg' ) ); ?>">
					</figure>

					<h3 class="sui-box-title sui-lg"><?php echo esc_html__( 'Login to WPMU DEV Dashboard', 'snapshot' ); ?></h3>
					<span class="sui-description"><?php echo esc_html__( 'Whoops, looks like you haven\'t logged into the WPMU DEV Dashboard. This plugin is the API connection between WPMU DEV and your site, so if you want to use WPMU DEV to store your backups you\'ll need to login using your WPMU DEV account details', 'snapshot' ); ?></span>

				</div>

				<div class="sui-box-body sui-lg sui-block-content-center">
					<a href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=wpmudev' ); ?>" class="sui-button <?php echo sanitize_html_class( $button_class ); ?>">
						<span class="sui-icon-wpmudev-logo" aria-hidden="true"></span><?php echo esc_html__( 'Login to the plugin', 'snapshot' ); ?>
					</a>
				</div>

			</div>
		</div>

	</div>
</div>