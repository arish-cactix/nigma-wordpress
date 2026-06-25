<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-template">
	<#
	var previewStyles = '';
	if ( 'object' === typeof FusionApp && 'object' === typeof FusionApp.data && ( ( 'string' === typeof FusionApp.data.fusion_element_type && 'post_cards' === FusionApp.data.fusion_element_type ) || 'fusion_form' === FusionApp.data.postDetails.post_type ) && 'object' === typeof FusionApp.data.postMeta && 'object' === typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion.preview_width ) {
		previewStyles = 'width:' + parseInt( FusionApp.data.postMeta._fusion.preview_width ) + '%;';
	}

	if ( previewStyles ) {
		previewStyles = 'style="' + previewStyles + '"';
	}
	#>
	<div id="fusion_builder_container" {{{ previewStyles }}}></div>
</script>
