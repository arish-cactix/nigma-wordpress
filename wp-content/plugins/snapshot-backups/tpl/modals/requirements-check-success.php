<?php // phpcs:ignore
/**
 * Modal for requirements check success, before allowing backups.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="modal-snapshot-requirements-check-success"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-requirements-check-success-title"
		aria-describedby="modal-snapshot-requirements-check-success-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center">
				<figure class="sui-box-banner" role="banner" aria-hidden="true">
					<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-success.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-success.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-success@2x.png' ) ); ?> 2x"
					/>
				</figure>
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg" ><?php esc_html_e( 'Requirement passed', 'snapshot' ); ?></h3>
				<span class="sui-description"><?php esc_html_e( 'You meet the requirement!', 'snapshot' ); ?></span>
			</div>

			<div class="sui-accordion sui-accordion-flushed" style=" margin-top: 35px; ">

				<div class="sui-accordion-item sui-success sui-accordion-item">

					<div class="sui-accordion-item-header">
						<div class="sui-accordion-item-title"><span aria-hidden="true" class="sui-icon-check-tick sui-success"></span>
						<?php esc_html_e( 'PHP version is up to date', 'snapshot' ); ?>
						</div>
					</div>

				</div>

			</div>

			<div class="sui-box-body">
				<div class="sui-block-content-center">
					<button role="button" class="sui-button snapshot-checked-requirements"><?php esc_html_e( 'Continue', 'snapshot' ); ?></button>
				</div>
			</div>

		</div>
	</div>
</div>