<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="front-end-form-nav-template">
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
		<#
		if( 'timeline' === formOptions.steps_nav ) {
			timeline_navigator();
		} else if ( 'progress_bar' === formOptions.steps_nav ) {
			progress_bar_navigator();
		}
		#>

		<# function timeline_navigator() {
			for( let i = 0; i < stepViews.length; i++ ) {
				let step  = i + 1;
				let title = ( stepViews[i]?.model?.attributes?.params?.title || '' );
				let icon  = ( stepViews[i]?.model?.attributes?.params?.icon || '' );
				icon = ( icon ? ' ' + _.fusionFontAwesome( icon ) : '' );

				if ( ! title ) {
					if ( 1 === step ) {
						title = fusionBuilderText.singular_form_step.replace( '%s', step );
					} else {
						title = fusionBuilderText.plural_form_step.replace( '%s', step );
					}
				}

				let isLast = ( stepViews.length === step );
				let isFirst = ( i === 0 );
				let displayFirstSpacer  = ( 'around' === formOptions.steps_spacing || 'right' === formOptions.steps_spacing );
				let displaySecondSpacer = ( 'around' === formOptions.steps_spacing || 'left' === formOptions.steps_spacing );
				let additionalStepClass = '';
				if ( step < activeStep ) {
					additionalStepClass = ' awb-form-nav__tl-step-wrapper--completed';
				} else if ( step === activeStep ) {
					additionalStepClass = ' awb-form-nav__tl-step-wrapper--active';
				}

				if ( isFirst && displayFirstSpacer ) { #>
					<span class="awb-form-nav__tl-spacer"></span>
				<# } #>

				<div class="awb-form-nav__tl-step-wrapper{{ additionalStepClass }}" data-step="{{ step }}">
					<div class="awb-form-nav__tl-step">

					<# if ( 'number' === formOptions.steps_number_icon ) { #>
						<#
						let additional_class = '';
						if ( 'yes' === formOptions.step_icon_bg ) {
							additional_class = ' awb-form-nav__tl-number--with-background';
						}
						#>
						<span class="awb-form-nav__tl-number{{ additional_class }}">{{ step }}</span>
					<# } #>

					<# if ( 'icon' === formOptions.steps_number_icon && icon ) { #>
						<span class="awb-form-nav__tl-icon{{ icon }}"></span>
					<# } #>

					<# if ( 'no' !== formOptions.steps_title ) { #>
						<span class="awb-form-nav__tl-title">{{ title }}</span>
					<# } #>

					</div>
				</div>

				<# if ( isLast ) {
						if ( displaySecondSpacer ) { #>
							<span class="awb-form-nav__tl-spacer"></span>
						<# }
					} else { #>
						<span class="awb-form-nav__tl-spacer awb-form-nav__tl-spacer--between"></span>
					<# }
				#>
			<# } #>
		<# } #>

		<# function progress_bar_navigator() { #>
			{{{ progressBarHTML }}}
		<# } #>
	</div>
</script>
