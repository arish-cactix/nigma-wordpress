<?php //phpcs:ignore
/**
 * Template file for Directory Tree
 *
 * @package snapshot
 * @var array $excluded List of excluded files.
 *
 * @since 4.1.4
 */

$count       = 1;
$total_count = count( $files );
foreach ( $files as $file ) :
	$file_path  = $file['path'];
	$suspicious = is_suspicious_file_name( $file_path );

	if ( $suspicious ) {
		continue;
	}

	$checked = in_array( $file_path, $excluded, true );
	$class   = ( isset( $file['browsable'] ) && $file['browsable'] && 'dir' === $file['type'] ) ? 'is-browsable' : 'not-browsable';
	$class  .= ' node-type--' . $file['type'];
	$class  .= ( 'ajax' === $type ) ? ' node--appended' : '';
	$class  .= $checked ? ' node--enabled' : ' node--disabled';
	$class  .= ( $count === $total_count && $more_items_flag ) ? ' explorer-last-item' : '';
	$size    = $file['size'];
	?>
<li class="<?php echo esc_attr( $class ); ?>" data-path="<?php echo esc_attr( wp_strip_all_tags( $file['path'] ) ); ?>"
	data-name="<?php echo esc_attr( $file['name'] ); ?>" data-type="<?php echo esc_attr( $file['type'] ); ?>"
	data-page="<?php echo esc_attr( 0 ); ?>" role="treeitem"
							<?php
							if ( 'dir' === $file['type'] ) :
								?>
								aria-expanded="false" <?php endif; ?> aria-selected="<?php echo $checked ? 'false' : 'true'; ?>">
	<span class="sui-tree-node">
		<?php if ( 'dir' === $file['type'] ) : ?>
		<span role="button" class="loading-icon" data-button="expander"
			aria-label="<?php esc_attr_e( 'Expand or compress item', 'snapshot' ); ?>"></span>
		<?php endif; ?>
		<span class="sui-node-checkbox" role="checkbox"
			aria-label="<?php esc_attr_e( 'Select this item', 'snapshot' ); ?>"></span>
		<span class="snapshot-icon" aria-hidden="true"></span>
		<span class="sui-node-text">
			<?php echo esc_html( $file['name'] ); ?>
		</span>

		<?php if ( 'file' === $file['type'] ) : ?>
		<span class="sui-node-text-right"><?php echo esc_html( $size ); ?></span>
		<?php endif; ?>
	</span>
	<?php
	if ( $count === $total_count && $more_items_flag ) {
		?>
	<div class="exp-load-more-container">
		<div class="exp-last-item-overlay"></div>
		<div class="exp-load-more-content">
			<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			<span class="text"><?php echo esc_html__( 'Please wait, loading more results', 'snapshot' ); ?></span>
		</div>
	</div>
		<?php
	}
	?>
</li>
	<?php
	++$count;
endforeach;
?>