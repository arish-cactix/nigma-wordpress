<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.9.0
 */

?>
<script type="text/html" id="tmpl-fusion_circles_info-shortcode">
	<div {{{ _.fusionGetAttributes( circlesInfoAtts ) }}}>
		<div class="awb-circles-info-wrapper">

			<div class="awb-circles-info-icons-wrapper">
				{{{iconsHTML}}}
			</div>

			<div class="awb-circles-info-content-wrapper">
			</div>

		</div>
	</div>
</script>
<script type="text/html" id="tmpl-fusion_circle_info-shortcode">
	<div {{{ _.fusionGetAttributes( circleInfoAtts ) }}}>
		<div class="awb-circles-info-title">{{{title}}}</div>
		<# if ( '' !== output ) { #>
			<div class="awb-circles-info-text">{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</div>
		<# } #>
	</div>
</script>
