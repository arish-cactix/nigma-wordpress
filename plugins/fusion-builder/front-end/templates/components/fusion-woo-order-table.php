<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/html" id="tmpl-fusion_woo_order_table-shortcode">
	<#
		var i,
			rowClass;
	#>
	<section {{{ _.fusionGetAttributes( elementAttr ) }}}>
		<div class="avada-order-details">
			<table class="shop_table order_details">
				<thead>
					<tr>
						<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th class="product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					</tr>
				</thead>

				<# for ( i = 0; i < extras.items.length; i++ ) { #>
					<tr class="awb-woo-order-table__order-item order_item">
						<td class="product-name">
							<div class="fusion-product-name-wrapper">
								<# if ( extras.items[i].is_visible ) { #>
									<span class="product-thumbnail">
									<# if ( ! extras.items[i].product_permalink ) { #>
										{{{ extras.items[i].thumbnail }}}
									<# } else { #>
										<a href="{{{ extras.items[i].product_permalink }}}">{{{ extras.items[i].thumbnail }}}</a>
									<# } #>
									</span>
								<# } #>
								<div class="product-info">
									{{{ extras.items[i].item_name_html }}}
									{{{ extras.items[i].quantity_html }}}
									{{{ extras.items[i].item_meta }}}
									<# if( 'yes' === showDownloads ) { #>
										{{{ extras.items[i].item_downloads }}}
									<# } #>
								</div>
							</div>
						</td>

						<td class="product-total">
							{{{ extras.items[i].product_total }}}
						</td>
					</tr>
				<# } #>

				<tfoot>
					<# for ( i = 0; i < extras.item_totals.length; i++ ) { #>
						<#
						rowClass = 'product-total';
						if ( extras.item_totals.length - 1 === i ) {
							rowClass += ' awb-woo-order-table__total';
						}
						#>
						<tr>
							<th scope="row">{{{ extras.item_totals[i]['label'] }}}</th>
							<td class="{{{ rowClass }}}">{{{ extras.item_totals[i]['value'] }}}</td>
						</tr>
					<# } #>
					<tr>
					<th scope="row"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td class="product-total awb-woo-order-table__note">{{{ extras.order_note }}}</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</section>
</script>
