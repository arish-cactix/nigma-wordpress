<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/html" id="tmpl-fusion_woo_order_downloads-shortcode">
	<# var i, j; #>
	<section {{{ _.fusionGetAttributes( elementAttr ) }}}>
		<table class="awb-woo-order-downloads__table shop_table shop_table_responsive order_details">
			<thead>
				<tr>
				<# for( i = 0; i < extras.downloads_columns.length; i++ ) { #>
					<th class="{{{ extras.downloads_columns[i].id }}}"><span class="nobr">{{{ extras.downloads_columns[i].name }}}</span></th>
				<# } #>
				</tr>
			</thead>

			<# for ( i = 0; i < 2; i++ ) { #>
				<tr class="awb-woo-order-downloads__item">
				<# for( j = 0; j < extras.downloads_columns.length; j++ ) { #>
					<# get_column_value( extras.downloads_columns[j] ); #>
				<# } #>
				</td>
			<# } #>

		</table>
	</section>

	<# function get_column_value( obj ) { #>
		<td class="{{ obj.id }}" data-title="{{ obj.name }}">
		<# switch (obj.id) {
				case 'download-product': #>
					<a href="#"><?php esc_html_e( 'Product', 'woocommerce' ); ?></a>
					<# break;
				case 'download-file': #>
					<a href="#" class="woocommerce-MyAccount-downloads-file button alt"><?php esc_html_e( 'Download', 'woocommerce' ); ?></a>
					<# break;
				case 'download-remaining': #>
					<?php esc_html_e( '&infin;', 'woocommerce' ); ?>
					<# break;
				case 'download-expires': #>
					<?php esc_html_e( 'Never', 'woocommerce' ); ?>
					<# break;
				default: #>
					<?php esc_html_e( 'Value', 'fusion-builder' ); ?>
			<# }
		#>
		</td>
	<# } #>
</script>
