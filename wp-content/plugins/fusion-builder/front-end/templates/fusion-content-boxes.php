<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_content_boxes-shortcode">
	<div {{{ _.fusionGetAttributes( attr ) }}}>
	</div>
</script>
<script type="text/html" id="tmpl-fusion_content_box-shortcode">
<#
var output          = '',
	icon_output     = '',
	title_output    = '',
	content_output  = '',
	link_output     = '',
	heading         = '',
	heading_size    = '',
	heading_content = '',
	full_icon_size  = '',
	timeline        = '';

if ( values.image && '' !== values.image ) {
	icon_output  = '<div ' + _.fusionGetAttributes( attrShortcodeIcon ) + '>';
	icon_output += '<img src="' + values.image + '" width="' + values.image_width + '" height="' + values.image_height + '" alt="" />';
	icon_output += '</div>';
} else if ( values.icon ) {
	icon_output  = '<div ' + _.fusionGetAttributes( attrShortcodeIconParent ) + '>';
	icon_output += '<i ' + _.fusionGetAttributes( attrShortcodeIcon ) + '></i>';
	icon_output += '</div>';
	if ( values.outercirclebordercolor && values.outercirclebordersize && 0 !== parseFloat( values.outercirclebordersize ) ) {
		icon_output  = '<div ' + _.fusionGetAttributes( attrShortcodeIconParent ) + '>';
		icon_output += '<span ' + _.fusionGetAttributes( attrShortcodeIconWrapper ) + '>';
		icon_output += '<i ' + _.fusionGetAttributes( attrShortcodeIcon ) + '></i>';
		icon_output += '</span></div>';
	}
}

let title = values.title;
let itemContent = FusionPageBuilderApp.renderContent( values.element_content, cid, false );

if ( usingDynamicParent ) {
	title = '<?php esc_html_e( 'Box Title.', 'fusion-builder' ); ?>';
	itemContent = '<?php esc_html_e( 'This content box element is set to use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?>';
}
if ( '' !== title ) {
	heading_size = 'div' === values.heading_size || 'p' === values.heading_size ? values.heading_size : 'h' + values.heading_size;
	title_output = '<' + heading_size + _.fusionGetAttributes( attrContentBoxHeading ) + '>' + title + '</' + heading_size +'>';
}

if ( 'right' === parentValues.icon_align && -1 !== jQuery.inArray( parentValues.layout, [ 'icon-on-side', 'icon-with-title', 'timeline-vertical', 'clean-horizontal' ] ) ) {
	heading_content = title_output + icon_output;
} else {
	heading_content = icon_output + title_output;
}

if ( '' !== values.link ) {
	heading_content = '<a ' + _.fusionGetAttributes( attrHeadingLink ) + '>' + heading_content + '</a>';
}

if ( '' !== heading_content ) {
	heading = '<div ' + _.fusionGetAttributes( attrHeadingWrapper ) + '>' + heading_content + '</div>';
}

if ( '' !== values.link && '' !== values.linktext ) {
	if ( 'text' === parentValues.link_type || 'button-bar' === parentValues.link_type ) {
		link_output  = '<div class="fusion-clearfix"></div>';
		link_output += '<a ' + _.fusionGetAttributes( attrReadMore ) + '>' + values.linktext + '</a>';
		link_output += '<div class="fusion-clearfix"></div>';
	} else if ( 'button' === parentValues.link_type ) {
		link_output  = '<div class="fusion-clearfix"></div>';
		link_output += '<a ' + _.fusionGetAttributes( attrButton ) + '><span class="fusion-button-text">' + values.linktext + '</span></a>';
		link_output += '<div class="fusion-clearfix"></div>';
	}
}

content_output  = '<div class="fusion-clearfix"></div>';

content_output += '<div ' + _.fusionGetAttributes( attrContentContainer ) + '>' + itemContent + '</div>' + link_output;
output          = heading + content_output;

if ( values.icon && 'yes' === parentValues.icon_circle && 'timeline-horizontal' === parentValues.layout && '1' != parentValues.columns ) {
	timeline = '<div ' + _.fusionGetAttributes( attrShortcodeTimeline ) + '></div>';
}

if ( values.icon && 'yes' === parentValues.icon_circle && 'timeline-vertical' === parentValues.layout ) {
	timeline = '<div ' + _.fusionGetAttributes( attrShortcodeTimeline ) + '></div>';
}
#>
<div {{{ _.fusionGetAttributes( attrContentWrapper ) }}}> {{{ output }}} {{{ timeline }}}</div>
</script>
