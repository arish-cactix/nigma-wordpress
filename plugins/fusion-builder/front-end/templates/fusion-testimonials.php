<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_testimonials-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div class="reviews fusion-child-element"></div>

	<# if ( 'yes' === navigation ) { #>
		<div {{{ _.fusionGetAttributes( paginationAttr ) }}}>
			<# for ( var i = 0; i < children; i++ ) { #>
				<# var activeClass = 0 === i ? ' class="activeSlide"' : ''; #>
				<a href="#" aria-label="Testimonial Pagination" data-order="{{{ i + 1 }}}"{{{ activeClass }}}></a>
			<# } #>
		</div>
	<# } #>
</div>
</script>

<script type="text/html" id="tmpl-fusion_testimonial-shortcode">
<#
var thumbnail = '',
	image = '',
	author = '',
	combined_attribs = '',
	icon = '',
	triangle = '',
	html = '';

if ( 'none' !== values.avatar ) {
	if ( 'image' === values.avatar ) {
		image = '<img ' + _.fusionGetAttributes( imageAttr ) + ' />';
	}

	thumbnail = '<div ' + _.fusionGetAttributes( thumbnailAttr ) + '>' + image + '</div>';
}

if ( values.name ) {
	author += '<strong>' + values.name + '</strong>';
	author += ( values.company ) ? '<span>, </span>' : '';
}

if ( values.testimonial_icon ) {
	icon = '<i ' + _.fusionGetAttributes( iconAttr ) + '></i>';
}

if ( values.company ) {
	if ( values.link && '' !== values.link ) {
		combined_attribs = 'target="' + values.target + '"';
		combined_attribs += ( '_blank' === values.target ) ? ' rel="noopener noreferrer"' : '';

		author += '<a href="' + values.link + '" ' + combined_attribs + '><span>' + values.company + '</span></a>';
	} else {
		author += '<span>' + values.company + '</span>';
	}
}

if ( 'clean' === parentValues.design ) {

	author = '<div ' + _.fusionGetAttributes( authorAttr ) + '><span class="company-name">' + author + '</span></div>';

	if ( 'below' === values.avatar_position ) {
		html = '<blockquote ' + _.fusionGetAttributes( blockquoteAttr ) + '><div ' + _.fusionGetAttributes( quoteAttr ) + '>' + icon + '<div ' + _.fusionGetAttributes( quoteContentAttr ) + '>' + FusionPageBuilderApp.renderContent( content, cid, parent ) + '</div></div></blockquote>' + thumbnail + author;
	} else {
		html = thumbnail + '<blockquote ' + _.fusionGetAttributes( blockquoteAttr ) + '><div ' + _.fusionGetAttributes( quoteAttr ) + '>' + icon + '<div ' + _.fusionGetAttributes( quoteContentAttr ) + '>' + FusionPageBuilderApp.renderContent( content, cid, parent ) + '</div></div></blockquote>' + author;
	}

} else {
	author = '<div ' + _.fusionGetAttributes( authorAttr ) + '>' + thumbnail + '<span class="company-name">' + author + '</span></div>';

	if ( 'show' === parentValues.testimonial_speech_bubble ) {
		triangle = '<span class="awb-triangle"></span>';
	}

	html = '<blockquote><div ' + _.fusionGetAttributes( quoteAttr ) + '>' + icon + '<div ' + _.fusionGetAttributes( quoteContentAttr ) + '>' + FusionPageBuilderApp.renderContent( content, cid, parent ) + '</div></div>' + triangle + '</blockquote>' + author;
}
#>
{{{ html }}}
</script>
