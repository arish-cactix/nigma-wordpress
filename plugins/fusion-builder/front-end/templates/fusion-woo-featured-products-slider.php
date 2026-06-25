<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_featured_products_slider-shortcode">
	<# if ( product_list ) { #>
		<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcode ) }}}>
			<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcodeCarousel ) }}}>
				<div class="swiper-wrapper">
					{{{ product_list }}}
				</div>
				<# if ( 'yes' === show_nav ) { #>
					<div class="awb-swiper-button awb-swiper-button-prev"><i class="awb-icon-angle-left"></i></div>
					<div class="awb-swiper-button awb-swiper-button-next"><i class="awb-icon-angle-right"></i></div>
				<# } #>
			</div>
		</div>
	<# } else if ( placeholder ) { #>
		{{{ placeholder }}}
	<# } #>
</script>
