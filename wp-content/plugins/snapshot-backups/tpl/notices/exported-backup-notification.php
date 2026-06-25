<?php
/**
 * Backup exported notification template.
 *
 * @package Snapshot_Backups
 * @since 4.26.0
 */

?>
<div class="notice snapshot-global-notice is-dismissible" id="snapshot-exported-backup-notification">
	<p>
		<span class="dashicons dashicons-yes-alt"></span>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %s: Backup page URL. */
				__( 'Your backup has been successfully exported and is <a href="%s" class="snapshot-notice__dismiss">available for download</a>. Please download your backup within the next 7 days before the link expires.', 'snapshot' ),
				esc_url( $backups_page_url )
			),
			array(
				'a' => array(
					'href'  => array(),
					'class' => array(),
				),
			)
		);
		?>
	</p>
</div>
