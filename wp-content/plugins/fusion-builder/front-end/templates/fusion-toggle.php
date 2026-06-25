<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_accordion-shortcode">
<div {{{ _.fusionGetAttributes( toggleShortcode ) }}}>
	<div {{{ _.fusionGetAttributes( toggleShortcodePanelGroup ) }}}></div>
</div>
</script>
<script type="text/html" id="tmpl-fusion_toggle-shortcode">
<#
		let itemContent = FusionPageBuilderApp.renderContent( elementContent, cid, false );

		if ( usingDynamicParent ) {
			title = '<?php esc_html_e( 'Toggle Title', 'fusion-builder' ); ?>';
			itemContent = '<?php esc_html_e( 'This toggle use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?>';
		}
#>
<div class="panel-heading">
	<{{titleTag}} class="panel-title toggle">
		<a {{{ _.fusionGetAttributes( toggleShortcodeDataToggle ) }}}>
			<span class="fusion-toggle-icon-wrapper" aria-hidden="true">
				<i class="fa-fusion-box active-icon {{activeIcon}}" aria-hidden="true"></i>
				<i class="fa-fusion-box inactive-icon {{inActiveIcon}}" aria-hidden="true"></i>
			</span>
			<span {{{ _.fusionGetAttributes( headingAttr ) }}}>
				{{{ title }}}
			</span>
		</a>
	</{{titleTag}}>
</div>
<div {{{ _.fusionGetAttributes( toggleShortcodeCollapse ) }}}>
	<div {{{ _.fusionGetAttributes( contentAttr ) }}}>
		{{{ itemContent }}}
	</div>
</div>
</script>
