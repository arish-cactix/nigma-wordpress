<?php //phpcs:ignore
/**
 * Modal for File explorer tree
 *
 * @package snapshot
 * @since 4.13
 */

use WPMUDEV\Snapshot4\Helper\Explorer;

?>
<div class="sui-modal sui-modal-lg snapshot-modal--file__explorer">
	<div role="dialog" id="snapshot-modal-file-explorer" class="sui-modal-content" aria-modal="true"
		aria-labelledby="snapshot-modal-file-explorer-title" aria-describedby="snapshot-modal-file-explorer-description"
		aria-live="polite">
		<div class="sui-box">

			<div class="sui-box-header">
				<button class="sui-button-icon sui-button-float--right file-explorer--close__modal" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="snapshot-modal-file-explorer-title" class="sui-box-title sui-md">
					<?php esc_html_e( 'Choose Files and Folders', 'snapshot' ); ?></h3>
			</div>

			<div class="sui-box-body">
				<p class="sui-description" id="snapshot-modal-file-explorer-description">
					<?php esc_html_e( 'Choose specific files and folders you want to exclude from manual and scheduled backups.', 'snapshot' ); ?>
				</p>
				<div class="snapshot-modal--file__explorer__content">
					<?php
					$details = Explorer::get_root_details();
					?>
					<ul class="sui-tree" data-tree="directory" role="tree">
						<li class="is-browsable node-type--dir node--enabled" data-root="true"
							data-path="<?php echo esc_attr( wp_strip_all_tags( $details['path'] ) ); ?>"
							data-name="<?php echo esc_attr( $details['name'] ); ?>"
							data-type="<?php echo esc_attr( $details['type'] ); ?>"
							data-page="<?php echo esc_attr( 0 ); ?>" role="treeitem" aria-expanded="false"
							aria-selected="false">
							<span class="sui-tree-node">
								<span class="loading-icon" role="button" data-button="expander"
									aria-label="<?php esc_attr_e( 'Expand or compress item', 'snapshot' ); ?>"></span>
								<span class="sui-node-checkbox" role="checkbox"
									aria-label="<?php esc_attr_e( 'Select this item', 'snapshot' ); ?>"></span>
								<span aria-hidden="true"></span>
								<span class="sui-node-text">
									<?php echo esc_html( sanitize_file_name( $details['name'] ) ); ?>
								</span>
							</span>
						</li>
					</ul>
				</div>
			</div>

			<div class="sui-box-footer">
				<button type="button" class="sui-button sui-button-float--right" aria-live="polite"
					id="file-explorer--add__contents">
					<?php esc_html_e( 'Add', 'snapshot' ); ?>
				</button>
				<button type="button" class="sui-button sui-button-ghost"
					data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
			</div>

		</div>
	</div>
</div>