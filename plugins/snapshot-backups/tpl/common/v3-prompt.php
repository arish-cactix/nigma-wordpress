<?php // phpcs:ignore
/**
 * Notification in Snapshot pages for prompting to uninstall Snapshot v3.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Settings;

if ( ! empty( $active_v3 ) ) {
	wp_nonce_field( 'snapshot_uninstall_snapshot_v3', '_wpnonce-uninstall_snapshot_v3' );
	?>
	<div id="snapshot-uninstall-v3" class="sui-notice sui-notice-info" >

		<div class="sui-notice-content">

			<div class="sui-notice-message">
			<div style="position: relative;">
				<div class="snapshot-v3-uninstall-hero <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>"></div>
					<div class="snapshot-v3-uninstall-text <?php echo ! empty( $is_branding_hidden ) ? esc_html( 'snapshot-hidden-branding' ) : esc_html( '' ); ?>">
						<div style="min-height: 115px;">

							<h4 style="margin: 14px auto 5px; line-height: 22px;"><?php esc_html_e( 'Uninstall the old Snapshot', 'snapshot' ); ?></h4>

							<?php if ( Settings::is_branding_hidden() ) { ?>
								<?php /* translators: %s - link for Feature Request modal in DEV site */ ?>
								<p><?php echo wp_kses_post( __( 'You\'re using the new Snapshot v4 plugin, but it looks like you still have the old Snapshot plugin installed. You can safely use Snapshot v3 and v4 side by side.', 'snapshot' ) ); ?></p>

								<?php /* translators: %s - link for Snapshot 4.0 migration Q&A in DEV site */ ?>
								<p><?php echo wp_kses_post( __( 'Note: <strong>Your local backups will be removed after uninstalling the plugin</strong>, and Snapshot v4 does not currently support all v3 features.', 'snapshot' ) ); ?></p>
							<?php } else { ?>
								<?php /* translators: %s - link for Feature Request modal in DEV site */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'You\'re using the new Snapshot v4 plugin, but it looks like you still have the old Snapshot plugin installed. You can safely use Snapshot v3 and v4 side by side, please let us know <a href="%s" target="_blank">here</a> if you have any suggestions or feature requests for v4, as it will be our focus going forward!', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>

								<?php /* translators: %s - link for Snapshot 4.0 migration Q&A in DEV site */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'Note: <strong>Your local Snapshots will be removed after uninstalling the plugin</strong>, and Snapshot v4 does not currently support all v3 features. Learn more in our <a href="%s" target="_blank">Snapshot 4.0 migration Q&A</a>.', 'snapshot' ), 'https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/#faq' ) ); ?></p>
							<?php } ?>
							<?php
							if ( empty( $v3_local ) ) {
								?>
								<button style="" type="button" class="sui-button sui-button-blue snapshot-uninstall-v3" onclick="jQuery(window).trigger('snapshot:uninstall_snapshot_v3')">
									<span class="sui-button-text-default"><?php esc_html_e( 'Uninstall', 'snapshot' ); ?></span>
									<span class="sui-button-text-onload">
										<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
										<?php esc_html_e( 'Uninstalling', 'snapshot' ); ?>
									</span>
								</button>
								<?php
							} else {
								?>
								<button style="" type="button" class="sui-button sui-button-blue" id="snapshot-uninstall-v3-confirm">
									<span class="sui-button-text-default"><?php esc_html_e( 'Uninstall', 'snapshot' ); ?></span>
								</button>
								<?php
							}
							?>
						</div>
					</div>
				</div>

			</div>

		</div>

	</div>

	<?php
}//end if
?>