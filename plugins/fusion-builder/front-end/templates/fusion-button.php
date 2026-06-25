<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_button-shortcode">
<#
var iconHTML = '';
if ( values.icon ) {
	iconHTML = '<i' + _.fusionGetAttributes( IconAttr ) + '></i>';
	if ( 'yes' === values.icon_divider ) {
		iconHTML = '<span class="fusion-button-icon-divider button-icon-divider-' + values.icon_position + '">' + iconHTML + '</span>';
	} else if ( 'icon_position' === values.hover_transition ) {
		iconHTML += iconHTML;
	}
}

buttonText = '<span' + _.fusionGetAttributes( textAttr ) + '>' + values.element_content + '</span>';

if ( 'text_slide_up' === values.hover_transition || 'text_slide_down' === values.hover_transition ) {
	buttonText = '<div class="awb-button-text-transition">' + buttonText + buttonText + '</div>';
}

innerContent = ( 'left' === values.icon_position ) ? iconHTML + buttonText : buttonText + iconHTML;
#>

<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
	<# if ( 'undefined' !== typeof values.button_el_type && 'submit' === values.button_el_type ) { #>
		<button {{{ _.fusionGetAttributes( attr ) }}} >
			{{{ innerContent }}}
		</button>
	<# } else { #>
		<a {{{ _.fusionGetAttributes( attr ) }}} >
			{{{ innerContent }}}
		</a>
	<# } #>
</div>
</script>
