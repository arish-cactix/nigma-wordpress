<script type="text/html" id="tmpl-fusion_portfolio-shortcode">
<#
// If Query Data is set, use it and continue.  If not, echo HTML.
if ( portfolio_posts ) {

	if ( 'carousel' === layout ) { #>
		<div {{{ _.fusionGetAttributes( portfolioShortcode ) }}}>
			<div {{{ _.fusionGetAttributes( portfolioShortcodeCarousel ) }}}>
				<div class="swiper-wrapper">{{{ portfolio_posts }}}</div>

				<# if ( 'yes' === show_nav ) { #>
					<div class="awb-swiper-button awb-swiper-button-prev"><i class="awb-icon-angle-left"></i></div>
					<div class="awb-swiper-button awb-swiper-button-next"><i class="awb-icon-angle-right"></i></div>
				<# } #>
			</div>
		</div>
	<# } else { #>
		{{{ alignPaddingStyle }}}
		<div {{{ _.fusionGetAttributes( portfolioShortcode ) }}}>
			{{{ filters }}}
			{{{ columnSpacingStyle }}}
			<div {{{ _.fusionGetAttributes( portfolioShortcodePortfolioWrapper ) }}}>
				{{{ portfolio_posts }}}
			</div>
			{{{ pagination }}}
		</div>
	<# } #>

<# } else if ( placeholder ) { #>
	{{{ placeholder }}}
<# } #>
</script>
