<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_widget_area-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div class="fusion-additional-widget-content">
		<# if ( widgetArea ) { #>
		{{{ widgetArea }}}
		<# } #>
	</div>
</div>
</script>
