<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-post-lock">

	<#
		const lockData = window.fusionAppConfig.post_lock_data;
	#>
	<div class="fusion-post-lock-content">
		<div class="user-avatar">
			{{{ lockData.avatar }}}
		</div>
		<div class="msg">
			<p><strong>{{ lockData.name }}</strong> {{ lockData.msg }}
			<# if ( lockData.is_taken && lockData.saved_msg ) { #>
				<br>
				<span class="post-lock-revision">{{ lockData.saved_msg }}</span>
			<# } #>
			</p>

			<div class="actions">
				<# if ( ! lockData.is_taken ) { #>
					<a href="{{ lockData.back_link }}" class="button back">{{ lockData.back_text }}</a>
					<a href="{{ lockData.preview_link }}" class="button link preview">{{ lockData.preview_text }}</a>
					<a href="{{ window.location.href }}&take-over" class="button take-over">{{ lockData.takeover_text }}</a>
				<# } #>
				<# if ( lockData.is_taken ) { #>
					<a href="{{ lockData.back_link }}" class="button link back">{{ lockData.back_text }}</a>
				<# } #>
			</div>
		</div>
	</div>
</script>
