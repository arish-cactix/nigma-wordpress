<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.3
 */

?>
<script type="text/html" id="tmpl-fusion_tb_post_card_archives-shortcode">
	<div {{{ _.fusionGetAttributes( attr ) }}}>
	<#
	// If Query Data is set, use it and continue. If not, echo HTML.
	if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.loop_product && query_data.loop_product ) {
	#>
		<# if ( _.contains( [ 'slider', 'carousel' ], values.layout ) ) { #>
			<div {{{ _.fusionGetAttributes( productsAttrs ) }}}>
				{{{ productsLoop }}}
			</div>

			<# if ( _.contains( [ 'dots', 'arrows_dots'], values.show_nav ) ) { #>
				<div class="swiper-pagination"></div>
			<# } #>

			<# if ( _.contains( [ 'yes', 'arrows_dots' ], values.show_nav ) ) { #>
				<div class="awb-swiper-button awb-swiper-button-prev"><i {{{ _.fusionGetAttributes( prevAttr ) }}}></i></div>
				<div class="awb-swiper-button awb-swiper-button-next"><i {{{ _.fusionGetAttributes( nextAttr ) }}}></i></div>
			<# } #>

		<# } else { #>
			<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
				{{{ productsLoop }}}
			</ul>
		<# } #>

		<# if ( 'no' !== values.scrolling && 'terms' !== values.source && ( 'grid' === values.layout || 'masonry' === values.layout ) ) { #>
			{{{ pagination }}}
		<# } #>

		<# if ( 'load_more_button' === values.scrolling && -1 !== values.number_posts && 'terms' !== values.source && ( 'grid' === values.layout || 'masonry' === values.layout ) ) { #>
			<button class="fusion-load-more-button fusion-product-button fusion-clearfix">{{{ loadMoreText }}}</button>
		<# } #>

	<#
	} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
	#>
		{{{ query_data.placeholder }}}
	<# } #>
	</div>
</script>
