<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_checklist-shortcode">
<ul {{{ _.fusionGetAttributes( checklistShortcode ) }}}></ul>
</script>

<script type="text/html" id="tmpl-fusion_li_item-shortcode">
<span {{{ _.fusionGetAttributes( checklistShortcodeSpan ) }}}>
	<# if ( 'numbered' === parentValues.type ) { #>
		{{counter}}
	<# } else { #>
		<i {{{ _.fusionGetAttributes( checklistShortcodeIcon ) }}}></i>
	<# } #>
</span>
<#
		let itemContent = FusionPageBuilderApp.renderContent( output, cid, false );

		if ( usingDynamicParent ) {
			itemContent = '<?php esc_html_e( 'This checklist uses dynamic data. For a preview please check the front-end.', 'fusion-builder' ); ?>';
		}
#>
<div {{{ _.fusionGetAttributes( checklistShortcodeItemContent ) }}}>{{{ itemContent }}}</div>
</script>
