<?php // phpcs:ignore
/**
 * "New: Amazon S3 Integration" modal.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Assets;

$assets = new Assets();

?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="snapshot-membership-expired-modal"
		class="sui-modal-content"
		aria-modal="true"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<figure class="sui-box-banner" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/snapshot-logo.svg' ) ); ?>" />
				</figure>

				<div class="sui-box-title sui-lg" style="padding: 0 10px; white-space: normal;"><?php esc_html_e( 'Membership expired', 'snapshot' ); ?></div>
				<?php /* translators: %s - Admin name */ ?>
				<p class="sui-description" style="margin: 15px 30px 0;"><?php echo esc_html( sprintf( __( '%s, it looks like your membership has expired. Reactivate your WPMU DEV membership today to continue backing up your sites, and to carry on scheduling, managing, and restoring your secure incremental backups.', 'snapshot' ), wp_get_current_user()->display_name ) ); ?></p>
			</div>

			<div class="sui-box-body sui-content-center" style="padding-bottom: 30px;">
				<a role="button" class="sui-button sui-button-purple" target="_blank" href="https://wpmudev.com/hub/account/?utm_source=snapshot&utm_medium=plugin&utm_campaign=snapshot_expired_modal_reactivate"><span class="sui-icon-wpmudev-logo" aria-hidden="true"></span><?php esc_html_e( 'Reactivate Membership', 'snapshot' ); ?></a>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center" style="padding-bottom: 50px;">
				<?php /* translators: %s - support link */ ?>
				<p class="sui-description"><?php echo wp_kses_post( sprintf( __( 'Need help? <a href="%s" target="_blank">Get Support</a>', 'snapshot' ), 'https://wpmudev.com/sales-chat/?referrer=snapshot-no-membership' ) ); ?></p>
			</div>

		</div>
	</div>
</div>