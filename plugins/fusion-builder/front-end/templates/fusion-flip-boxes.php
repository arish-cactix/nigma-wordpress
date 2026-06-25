<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_flip_boxes-shortcode">

<div {{{ _.fusionGetAttributes( flipBoxesShortcode ) }}}></div>
<div class="fusion-clearfix"></div>
</script>

<script type="text/html" id="tmpl-fusion_flip_box-shortcode">
	<# 
		let text_front = values.text_front;
		let text_back = FusionPageBuilderApp.renderContent( values.element_content, cid, false );

		if ( 'undefined' !== typeof usingDynamicParent && usingDynamicParent ) {
			text_front = '<?php esc_html_e( 'This flip box element is set to use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?>';
			text_back = '<?php esc_html_e( 'This flip box element is set to use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?>';
		}
	#>
	<div {{{ _.fusionGetAttributes( flipBoxAttributes ) }}}>
		<div class="flip-box-inner-wrapper">
			<div {{{ _.fusionGetAttributes( flipBoxShortcodeFrontBox ) }}}>
				<div class="flip-box-front-inner">
					{{{ icon_output }}} {{{ title_front_output }}} {{{ text_front }}}
				</div>
			</div>

			<div {{{ _.fusionGetAttributes( flipBoxShortcodeBackBox ) }}}>
				<div class="flip-box-back-inner">
					{{{ title_back_output }}}{{{ text_back }}}
				</div>
			</div>
		</div>
	</div>
</script>
