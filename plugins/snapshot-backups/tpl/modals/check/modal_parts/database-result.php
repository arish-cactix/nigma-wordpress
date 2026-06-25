<?php // phpcs:ignore
/**
 * Snapshot database tables check result template
 *
 * @package snapshot
 */

$large_tables_count = count( $args );
$items_per_page     = 5;
$total_pages        = ceil( $large_tables_count / $items_per_page );

if ( ! empty( $args ) ) :
	?>
	<div class="sui-accordion accordion-large-tables">
		<div class="sui-accordion-item">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title sui-accordion-col-4"><span aria-hidden="true" class="sui-icon-warning-alert sui-warning"></span> <?php esc_html_e( 'Large Database Table found', 'snapshot' ); ?></div>
				<div class="sui-accordion-col-4">
					<div class="sui-tag sui-tag-yellow">
						<?php echo esc_html( $large_tables_count ); ?>
					</div>
					<button class="sui-button-icon sui-accordion-open-indicator" aria-label=<?php esc_attr__( 'Open item', 'snapshot' ); ?>>
						<span class="sui-icon-chevron-down" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<div class="seperator"></div>
				<div class="sui-box" tabindex="0">
					<div class="sui-box-body">
						<div class="sub-heading overview">
							<?php esc_html_e( 'Overview', 'snapshot' ); ?>
						</div>
						<p class="mb-30">
							<?php
								echo esc_html__( 'To expedite the backup process and reduce backup size, consider excluding large database tables that are not essential for the backup.', 'snapshot' );
							?>
						</p>
						<div class="snapshot-pf-status mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'Status', 'snapshot' ); ?>
							</div>
							<div class="sui-notice sui-notice-yellow sui-active" style="display: block;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
										<p>
										<?php
											esc_html_e( 'Database table larger than 50MB has been detected', 'snapshot' );
										?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div class="snapshot-pf-fix mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'How to Fix', 'snapshot' ); ?>
							</div>
							<p>
								<?php
									esc_html_e( "Optimize your large database tables by utilizing the 'Exclude' button below to omit large, non-critical tables from backups, ensuring more streamlined and faster backup processes.", 'snapshot' );
								?>
							</p>
						</div>
						<div class="seperator"></div>
						<div class="snapshot-pf-paginated-header">
							<div>
								<div id='snapshot-pf-bulk-actions' class="sui-form-field">
									<select id="snapshot-pf-bulk-actions-tables" class="sui-select snapshot-pf-bulk-actions" data-width="200px" data-check="tables">
										<option value="" selected disabled class="select-placeholder"><?php esc_html_e( 'Bulk actions', 'snapshot' ); ?></option>
										<option value="exclude"><?php esc_html_e( 'Exlude', 'snapshot' ); ?></option>
										<option value="include"><?php esc_html_e( 'Include', 'snapshot' ); ?></option>
									</select>
								</div>
								<button class="sui-button snapshot-pf-bulk-apply" data-check="tables"><?php esc_html_e( 'APPLY', 'snapshot' ); ?></button>
							</div>
							<div class="sui-pagination-wrap">
								<span class="sui-pagination-results"><?php echo esc_html( $large_tables_count . __( ' results', 'snapshot' ) ); ?></span>
								<ul class="sui-pagination">
									<li class="pagenate-link-step" data-pagenate-link="prev" data-check='tables'><a href="#" role="button">
										<span class="sui-icon-chevron-left" aria-hidden="true"></span>
										<span class="sui-screen-reader-text"><?php esc_html_e( 'Go to previous page', 'snapshot' ); ?></span>
									</a></li>

									<?php
									for ( $i = 1; $i <= $total_pages; $i++ ) {
										1 === $i ? $active = 'sui-active' : $active = '';
										?>
										<li class="pagenate-link <?php echo esc_attr( $active ); ?>" data-check='tables' data-pagenate-link="<?php echo esc_attr( $i ); ?>"><a href="#" role="button"><?php echo esc_html( $i ); ?></a></li>
										<?php
									}
									?>
									<li class="pagenate-link-step" data-pagenate-link="next" data-check='tables' ><a href="#" role="button">
										<span class="sui-icon-chevron-right" aria-hidden="true"></span>
										<span class="sui-screen-reader-text"><?php esc_html_e( 'Go to next page', 'snapshot' ); ?></span>
									</a></li>
								</ul>
							</div>

						</div>
						<div class="snapshot-pf-paginated paginated-content-wrapper">
							<div class="sui-table snapshot-pf-tables-table">
								<table class="sui-table">
									<thead>
										<tr>
											<th>
												<input type="checkbox" id="bulk-check-tables" class="bulk-checkbox"  data-check='tables' aria-labelledby="label-check-tables" />
											</th>
											<th colspan="2"><?php esc_html_e( 'File Name', 'snapshot' ); ?></th>
											<th><?php esc_html_e( 'Size', 'snapshot' ); ?></th>
											<th><?php esc_html_e( 'Include/Exclude', 'snapshot' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $args as $data ) {
											++$i;
											$paged_classes = 'paged-' . ceil( $i / $items_per_page );
											if ( $i > $items_per_page ) {
												$paged_classes .= ' sui-hidden';
											}
											?>
											<tr data-exclusion_path="<?php echo esc_attr( $data['table_name'] ); ?>" class="<?php echo esc_attr( $paged_classes ); ?>" >
												<td>
												<input
													type="checkbox"
													class="snapshot-pf-exclude-checkbox checkbox-tables"
													aria-labelledby="label-unique-id"
												/>
												</td>
												<td colspan="2">
													<?php echo esc_html( $data['table_name'] ); ?>
												</td>
												<td><?php echo esc_html( $data['size'] ) . ' MB'; ?></td>
												<td>

													<button class="sui-button sui-button-ghost sui-button-blue btn-exclude-table snapshot-pf-exclude-btn" data-file="<?php echo esc_attr( $data['table_name'] ); ?>" data-check='tables' data-action='exclude'><?php esc_html_e( 'EXCLUDE', 'snapshot' ); ?></button>
													<button class="sui-button sui-button-ghost btn-exclude-file snapshot-pf-include-btn" data-file="<?php echo esc_attr( $data['table_name'] ); ?>" data-check='tables' data-action='include'><?php esc_html_e( 'INCLUDE', 'snapshot' ); ?></button>
												</td>
											</tr>
											<?php
										}//end foreach
										?>
									</tbody>
								</table>
						</div>
						<p>
							<?php esc_html_e( 'Note that excluded files won’t be deleted. They will simply be excluded from the backup.', 'snapshot' ); ?>
						</p>
						<div class="ignore-wrapper buttons-wrap">
							<button id="snapshot-pf-reset-all" type="submit" data-check="tables" class="sui-button snapshot-pf-reset-all">
								<span class="sui-button-text-default">
									<?php esc_html_e( 'RESET ALL', 'snapshot' ); ?>
								</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
endif;