<?php // phpcs:ignore
/**
 * Settings page.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Assets;

$assets = new Assets();

?>
<div class="sui-wrap snapshot-page-settings">
	<?php $this->render( 'common/header' ); ?>

	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Tutorials', 'snapshot' ); ?></h1>
		<div class="sui-actions-right">
			<a href="https://wpmudev.com/blog/tutorials/tutorial-category/snapshot-pro/?utm_source=snapshot&utm_medium=plugin&utm_campaign=snapshot_tutorial_read_article" target="_blank" class="sui-button">
				<span class="sui-icon-open-new-window" aria-hidden="true"></span>
				<?php esc_html_e( 'View all', 'snapshot' ); ?>
			</a>
		</div>
	</div>
	<?php
	$this->render(
		'common/v3-prompt',
		array(
			'active_v3'          => $active_v3,
			'v3_local'           => $v3_local,
			'assets'             => $assets,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);
	?>

	<div class="sui-box snapshot-tutorials" id="snapshot-tutorials-list"></div>

	<?php

	// Snapshot getting started dialog.
	$this->render(
		'modals/welcome-activation',
		array(
			'errors'             => $errors,
			'welcome_modal'      => $welcome_modal,
			'welcome_modal_alt'  => $welcome_modal_alt,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);
	$this->render( 'modals/confirm-wpmudev-password' );

	$this->render( 'modals/settings-reset-settings' );
	$this->render( 'modals/confirm-v3-uninstall' );

	$this->render( 'common/footer' );

	?>

</div> <?php
// .sui-wrap ?>