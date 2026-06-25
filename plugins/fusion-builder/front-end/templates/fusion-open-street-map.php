<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.10
 */

?>
<script type="text/html" id="tmpl-fusion_openstreetmap-shortcode">
	<div {{{ _.fusionGetAttributes( elementAttr ) }}}></div>
</script>

<script type="text/html" id="tmpl-fusion_openstreetmap_marker-shortcode">
	<# if ( icon ) { #>
		<div class="awb-openstreet-map-marker-icon-wrapper {{{ animationClass }}}" style="{{{ childInlineStyle }}}">
			<i {{{ _.fusionGetAttributes( iconAttr ) }}}></i>
		</div>
	<# } #>

	<div class="awb-openstreet-map-content-wrapper">
	<# if ( values.title ) { #>
		<h5 class="awb-openstreet-map-marker-title">{{{ values.title }}}</h5>
	<# } #>

	<# if ( values.tooltip_content ) { #>
		<div class="awb-openstreet-map-marker-content">
			{{{ markerContent }}}
		</div>
	<# } #>
	</div>
</script>
