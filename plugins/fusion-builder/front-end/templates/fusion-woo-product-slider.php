<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_products_slider-shortcode">
<# if ( productList ) { #>
	<div {{{ _.fusionGetAttributes( wooProductSliderShortcode ) }}}>
		<div {{{ _.fusionGetAttributes( wooProductSliderShortcodeCarousel ) }}}>
			<div class="swiper-wrapper">
				{{{ productList }}}
			</div>
			<# if ( 'yes' == showNav ) { #>
				<div class="awb-swiper-button awb-swiper-button-prev"><i class="awb-icon-angle-left"></i></div>
				<div class="awb-swiper-button awb-swiper-button-next"><i class="awb-icon-angle-right"></i></div>
			<# } #>
		</div>
	</div>
<# } else if ( placeholder ) { #>
	{{{ placeholder }}}
<# } #>
</script>
