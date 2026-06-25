<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tabs-shortcode">
	<div {{{ _.fusionGetAttributes( tabsShortcode ) }}}>
		<div class="nav">
			<ul class="nav-tabs {{ justifiedClass }} fusion-child-element">
			</ul>
		</div>
		<div class="tab-content fusion-child-contents-{{ cid }}"></div>
	</div>
</script>

<script type="text/html" id="tmpl-fusion_tab-shortcode">
<#
var titleTag   = values.title_tag;
var title = values.title;
var icon   = '';
var itemContent = FusionPageBuilderApp.renderContent( values.element_content, cid, false );

if ( usingDynamicParent ) {
			title = '<?php esc_html_e( 'Tab Title', 'fusion-builder' ); ?>';
			itemContent = '<?php esc_html_e( 'This tab use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?>';
}

if ( 'none' !== values.icon ) {
	icon = '<i ' + _.fusionGetAttributes( tabsShortcodeIcon ) + '></i>';
}
var tab_nav = '<a ' + _.fusionGetAttributes( tabsShortcodeLink ) + '><' + titleTag + ' class="fusion-tab-heading">';
if ( 'right' === parentValues.icon_position ) {
	tab_nav +=  title + icon;
} else {
	tab_nav += icon + title;
}
tab_nav += '</' + titleTag + '></a>';
html = tab_nav;

// Change ID for mobile to ensure no duplicate ID.
tab_nav = tab_nav.replace( 'id="fusion-tab-', 'id="mobile-fusion-tab-' );
var tabNavContents  = 'accordion' === values.mobile_mode || 'toggle' === values.mobile_mode ? '<div class="nav fusion-mobile-tab-nav fusion-mobile-extra-' + cid + '"><ul class="nav-tabs ' + justifiedClass + '"><li>' + tab_nav + '</li></ul></div>' : '';
var tabContents     = '<div ' + _.fusionGetAttributes( tabsShortcodeTab ) + '>' + itemContent + '</div>';

var contentsEl = '.fusion-child-contents-' + parentModel.attributes.cid;

var liSelectors = {};

var thisModel = FusionPageBuilderElements.find( function( model ) {
	return model.get( 'cid' ) == cid;
} );

thisModel = parentModel.children.models.find( function( model ) {
	return model.get( 'cid' ) == cid;
} );

var extraAppend = {
	selector : contentsEl,
	contents : [ tabNavContents, tabContents ],
	existing : [ '.fusion-mobile-extra-' + cid, '.fusion-extra-' + cid ],
	trigger  : '#' + tabsShortcodeLink.id
}
thisModel.set( 'extraAppend', extraAppend );
thisModel.set( 'selectors', liSelectors );
#>
{{{ html }}}
</script>
