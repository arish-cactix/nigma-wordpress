<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>

<script type="text/template" id="fusion-builder-form-step-template">
	<div class="fusion-special-item-wrapper">
		<div class="fusion-droppable fusion-droppable-horizontal target-before fusion-container-target"></div>
		<# 
		var stepTitle = fusionBuilderText.form_step;
		if ( 'undefined' !== typeof values.title && '' !== values.title ) {
			stepTitle = fusionBuilderText.form_step + ' - ' + values.title;
		} 
		#>
		<div class="fusion-builder-special-item-desc"><span class="fusion-builder-form-step-title">{{{ stepTitle }}}</span></div>
		<div class="fusion-special-item-controls fusion-builder-module-controls fusion-builder-container-controls">
			<div class="fusion-builder-controls">
				<a href="#" class="fusion-builder-special-item-drag" ><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.drag_element }}</span></span></a>
				<a href="#" class="fusion-builder-delete-special-item fusion-builder-delete-form-step" ><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.delete_element }}</span></span></a>
				<a href="#" class="fusion-builder-special-item-container-clone fusion-builder-module-control"><span class="fusiona-file-add"></span><span class="fusion-container-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.clone_element }}</span></span></a>
				<a href="#" class="fusion-builder-settings fusion-builder-module-control"><span class="fusiona-pen"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{{ editLabel }}}</span></span></a>
				<a href="#" class="fusion-builder-container-add fusion-builder-module-control"><span class="fusiona-add-container"></span><span class="fusion-container-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Container', 'fusion-builder' ); ?></span></span></a>
			</div>
		</div>

		<div class="fusion-droppable fusion-droppable-horizontal target-after fusion-container-target"></div>
	</div>
</script>
