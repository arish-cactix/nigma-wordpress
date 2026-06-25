<?php // phpcs:ignore
/**
 * Documentation button in Snapshot pages.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Settings;

$url = SNAPSHOT_WPMUDEV_DOCS . '?utm_source=snapshot&utm_medium=plugin&utm_campaign=' . $utm_tags;
?>
<div class="sui-header">
	<h1 class="sui-header-title"><?php echo esc_html( $header_title ); ?></h1>
	<?php if ( ! Settings::get_branding_hide_doc_link() ) { ?>
		<div class="sui-actions-right">
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="sui-button sui-button-ghost">
				<span class="sui-icon-academy" aria-hidden="true"></span>
				<?php esc_html_e( 'Documentation', 'snapshot' ); ?>
			</a>
		</div>
	<?php } ?>
</div>