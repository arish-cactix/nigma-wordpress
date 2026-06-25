<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/html" id="tmpl-fusion_woo_order_details-shortcode">
	<div {{{ _.fusionGetAttributes( elementAttr ) }}}>
		<ul class="awb-woo-order-details__list order_details">

		<# for( let i = 0; i <= details_ordering.length; i++ ) {
			switch( details_ordering[i] ) {
				case 'order_number':
					get_order_number_el();
					break;

				case 'order_date':
					get_order_date_el();
					break;

				case 'user_email':
					get_billing_email_el();
					break;

				case 'total_amount':
					get_order_total_el();
					break;

				case 'payment_method':
					get_payment_method_el();
					break;
			}
		} #>

		</ul>

		<p class="awb-woo-order-details__failed awb-woo-order-details__le-hide">
			<# if ( failedMessage ) { #>
				{{{ failedMessage }}}
			<# } else { #>
				<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?>
			<# } #>

		</p>

		<p class="awb-woo-order-details__failed-actions awb-woo-order-details__le-hide">
			<a href="#" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
			<a href="#" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
		</p>
	</div>


	<# function get_order_number_el() { #>
		<li class="awb-woo-order-details__order order">
		<?php esc_html_e( 'Order number:', 'woocommerce' ); ?> <strong>{{extras.order_number}}</strong>
		</li>
	<# } #>

	<# function get_order_date_el() { #>
		<li class="awb-woo-order-details__date date">
		<?php esc_html_e( 'Date:', 'woocommerce' ); ?> <strong>{{extras.date}}</strong>
		</li>
	<# } #>

	<# function get_billing_email_el() { #>
		<li class="awb-woo-order-details__email">
		<?php esc_html_e( 'Email:', 'woocommerce' ); ?> <strong>{{extras.billing_email}}</strong>
		</li>
	<# } #>

	<# function get_order_total_el() { #>
		<li class="awb-woo-order-details__total total">
		<?php esc_html_e( 'Total:', 'woocommerce' ); ?> <strong>{{{extras.total}}}</strong>
		</li>
	<# } #>

	<# function get_payment_method_el() { #>
		<li class="awb-woo-order-details__method method">
		<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?> <strong>{{extras.payment_method}}</strong>
		</li>
	<# } #>

</script>
