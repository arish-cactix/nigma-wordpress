<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_fontawesome-shortcode">
	<div class="awb-icon-live-editor-wrapper">
		<# if ( hasLink ) { #>
			<a {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</a>
		<# } else { #>
			<i {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</i>
		<# } #>
	</div>
</script>
