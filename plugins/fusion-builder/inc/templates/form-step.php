<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-form-step-template">
	<# 
	var stepTitle = fusionBuilderText.form_step;
	if ( 'undefined' !== typeof params.title && '' !== params.title ) {
		stepTitle = fusionBuilderText.form_step + ' - ' + params.title;
	}
	#>
	<div class="fusion-builder-section-header fusion-builder-special-section">
		<div class="fusion-builder-special-item-title fusion-builder-special-item-{{ cid }}">
			<span class="fusion-builder-form-step-title">{{{ stepTitle }}}</span>
		</div>

		<div class="fusion-builder-controls fusion-builder-section-controls">
			<a href="#" class="fusion-builder-delete-form-step fusion-builder-delete-speical-item" title="{{ fusionBuilderText.delete_form_step }}"><span class="fusiona-trash-o"></span></a>
			<a href="#" class="fusion-builder-clone-special-item fusion-builder-clone" title="{{ fusionBuilderText.clone_element }}"><span class="fusiona-file-add"></span></a>
			<a href="#" class="fusion-builder-edit-form-step fusion-builder-edit-speical-item" title="edit"><span class="fusiona-pen"></span></a>
			<a href="#" class="fusion-builder-special-item-add-container" title="{{ fusionBuilderText.insert_section }}"><span class="fusiona-plus"></span></a>
		</div>
	</div>
	<div class="fusion-builder-section-content fusion-builder-data-cid" data-cid="{{ cid }}">
	</div>
</script>
