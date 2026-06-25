<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/html" id="tmpl-fusion_woo_order_customer_details-shortcode">
	<section {{{ _.fusionGetAttributes( elementAttr ) }}}>
		<section class="awb-woo-order-customer-details__cols">
			<div class="awb-woo-order-customer-details__col awb-woo-order-customer-details__col--1">
				<{{{titleTag}}} {{{ _.fusionGetAttributes( headingsAttr ) }}}><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></{{{titleTag}}}>

				<# if ( 'none' !== separatorStyle ) { #>
					<hr class="awb-woo-order-customer-details__sep" />
				<# } #>

				<address class="awb-woo-order-customer-details__address">
					{{{ extras.billing_address }}}

					<# if ( extras.billing_phone ) { #>
						<p class="awb-woo-order-customer-details--phone">{{ extras.billing_phone }}</p>
					<# } #>

					<# if ( extras.billing_email ) { #>
						<p class="awb-woo-order-customer-details--email">{{ extras.billing_email }}</p>
					<# } #>
				</address>
			</div>

			<div class="awb-woo-order-customer-details__col awb-woo-order-customer-details__col--2">
				<{{{titleTag}}} {{{ _.fusionGetAttributes( headingsAttr ) }}}><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></{{{titleTag}}}>

				<# if ( 'none' !== separatorStyle ) { #>
					<hr class="awb-woo-order-customer-details__sep" />
				<# } #>

				<address class="awb-woo-order-customer-details__address">
					{{{ extras.shipping_address }}}

					<# if ( extras.shipping_phone ) { #>
						<p class="awb-woo-order-customer-details__phone">{{ extras.shipping_phone }}</p>
					<# } #>
				</address>
			</div>
		</section>
	</section>
</script>
