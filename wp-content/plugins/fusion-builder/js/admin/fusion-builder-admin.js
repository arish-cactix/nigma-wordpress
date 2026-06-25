/* global ajaxurl, fusionBuilderConfig, fusionBuilderAdmin */
jQuery( document ).ready( function() {

	jQuery( '.fusion-builder-admin-toggle-heading' ).on( 'click', function() {
		jQuery( this ).parent().find( '.fusion-builder-admin-toggle-content' ).slideToggle( 300 );

		if ( jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).hasClass( 'fusion-plus' ) ) {
			jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).removeClass( 'fusion-plus' ).addClass( 'fusion-minus' );
		} else {
			jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).removeClass( 'fusion-minus' ).addClass( 'fusion-plus' );
		}

	} );

	// Toggle an single capability on/off.
	jQuery( '.enable-builder-ui .ui-button' ).on( 'click', function( e ) {
		const parent = jQuery( this ).parent(),
			input = parent.find( 'input' );

		e.preventDefault();

		input.val( jQuery( this ).data( 'value' ) );

		if ( 'checkbox' === input.prop( 'type' ) ) {
			input.prop( 'checked', true );
		}

		parent.find( '.ui-button' ).removeClass( 'ui-state-active' );
		jQuery( this ).addClass( 'ui-state-active' );
	} );

	// Toggle an main dashboard access capability on/off.
	jQuery( '.awb-dashboard-access .ui-button' ).on( 'click', function() {
		const parent = jQuery( this ).parents( '.awb-role-manager-access-items' ),
			items = parent.find( '.awb-role-manager-access-item:not(.awb-dashboard-access):not(.awb-form-submissions)' );

		if ( 'off' === jQuery( this ).data( 'value' ) ) {
			items.find( 'input' ).val( 'off' );
			items.find( '.ui-button' ).removeClass( 'ui-state-active' );
			items.find( '.ui-button[data-value="off"]' ).addClass( 'ui-state-active' );
			items.find( '.fusion-form-radio-button-set' ).addClass( 'awb-disabled' );
		} else {
			items.find( '.fusion-form-radio-button-set' ).removeClass( 'awb-disabled' );
		}
	} );

	// Open/close the individual role toggles.
	jQuery( '.awb-role-manager-item-title' ).on( 'click', function( e ) {
		const parent         = jQuery( this ).closest( '.fusion-builder-option-field' ),
			target           = jQuery( this ).data( 'target' ),
			current          = parent.find( '.awb-role-manager-item-title.open' ).data( 'target' ),
			additionalHeight = jQuery( '.avada-db-menu-sticky' ).outerHeight() + jQuery( '#wpadminbar' ).outerHeight();

		// If reset button was clicked, do nothing.
		if ( jQuery( e.target ).hasClass( 'awb-role-manager-reset-role' ) ) {
			return false;
		}

		// Remove classes.
		if ( current !== target ) {
			parent.find( '.awb-role-manager-item-accordion' ).slideUp( 100 );
			parent.find( '.awb-role-manager-item-title' ).removeClass( 'open' );
		}

		// Toggle classes.
		jQuery( this ).toggleClass( 'open' );
		parent.find( '#' + target ).slideToggle( 200, function() {

			// Scroll to item.
			if ( ! jQuery( this ).is( ':hidden' ) ) {
				jQuery( 'html, body' ).animate( {
					scrollTop: jQuery( this ).closest( '.awb-role-manager-item' ).offset().top - additionalHeight
				}, 500 );
			}
		} );
	} );

	// Reset options for a role.
	jQuery( '.awb-role-manager-reset-role' ).on( 'click', function( e ) {
		e.preventDefault();

		const parent          = jQuery( this ).closest( '.awb-role-manager-item' ),
			buttonSets        = parent.find( '.fusion-form-radio-button-set' ),
			disableButtonSets = parent.find( '.awb-role-manager-access-item:not(.awb-dashboard-access):not(.awb-form-submissions)' ).find( '.fusion-form-radio-button-set ' );

		if ( buttonSets.first().children( 'input' ).data( 'default' ).length ) {
			buttonSets.each( function() {
				const input = jQuery( this ).children( 'input' ),
					  value = input.data( 'default' ),
					  button = jQuery( this ).children( '[data-value="' + value + '"]' );
					  console.log( input, value, button );
					  input.prop( 'checked', true ).val( value );
					  button.trigger( 'click' );
			} );

		} else {
			buttonSets.children( 'input' ).prop( 'checked', false ).val( '' );
			buttonSets.children( '.ui-button' ).removeClass( 'ui-state-active' );
			disableButtonSets.addClass( 'awb-disabled' );
		}

	} );

	jQuery( '.fusion-check-all' ).on( 'click', function( e ) {
		e.preventDefault();
		jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-builder-option-field input' ).prop( 'checked', true );
	} );

	jQuery( '.fusion-uncheck-all' ).on( 'click', function( e ) {
		e.preventDefault();
		jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-builder-option-field input' ).prop( 'checked', false );
	} );

	jQuery( '.fusion-runcheck' ).on( 'click', function( e ) {
		var $button = jQuery( this );

		e.preventDefault();

		if ( $button.hasClass( 'disabled' ) ) {
			return;
		}

		$button.addClass( 'disabled' );
		$button.next().show();

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'fusion_check_elements',
				fusion_import_nonce: fusionBuilderConfig.fusion_import_nonce
			}
		} )
		.done( function( elements ) {
			var $checkboxes = jQuery( '.fusion-builder-element-checkboxes' );
			if ( 'object' === typeof elements && 'object' === typeof elements.data ) {
				jQuery.each( elements.data, function( element, disable ) { // eslint-disable-line no-unused-vars
					var $checkbox = $checkboxes.find( 'input[value="' + element + '"]' );
					if ( ! $checkbox.closest( 'li' ).hasClass( 'hidden' ) ) {
						$checkbox.prop( 'checked', false );
					}
				} );
			}
			$button.removeClass( 'disabled' );
			$button.next().hide();
		} )
		.fail( function() {
			$button.removeClass( 'disabled' );
			$button.next().hide();
		} );
	} );


	jQuery( '#fusion-library-type' ).on( 'change', function( event ) {
		if ( 'templates' === jQuery( event.target ).val() || 'post_cards' === jQuery( event.target ).val() || 'mega_menus' === jQuery( event.target ).val()  ) {
			jQuery( '#fusion-global-field' ).css( { display: 'none' } );
		} else {
			jQuery( '#fusion-global-field' ).css( { display: 'flex' } );
		}
	} );

	// Dimiss notice on templates page.
	jQuery( '.fusion-builder-template-notification button.notice-dismiss' ).on( 'click', function( event ) {
		var $this = jQuery( this ),
			data  = $this.parent().data();

		event.preventDefault();

		// Make ajax request.
		jQuery.post( ajaxurl, {
			data: data,
			action: 'fusion_dismiss_admin_notice',
			nonce: data.nonce
		} );

		$this.closest( '.fusion-builder-important-notice-wrapper' ).removeClass( 'fusion-has-notification' );
		$this.parent().css( 'display', 'none' );
	} );

	jQuery( '.avada-db-more-info' ).on( 'click', function() {
		jQuery( this ).closest( '.fusion-builder-important-notice-wrapper' ).addClass( 'fusion-has-notification' ).find( '.fusion-builder-template-notification' ).css( 'display', 'block' );
	} );

	// Prevent form being submitted multiple times.
	jQuery( '#fusion-create-layout-form, #fusion-create-template-form' ).on( 'submit', function() {
		jQuery( this ).find( 'input[type="submit"]' ).prop( 'disabled', true );
	} );

	// Remove Avada Studio content.
	jQuery( '#awb-remove-studio-content' ).on( 'click', function( event ) {
		var $this = jQuery( this ),
			confirmResponse;

		event.preventDefault();

		// Early exit if process is already started.
		if ( $this.hasClass( 'disabled' ) ) {
			return;
		}

		confirmResponse = confirm( fusionBuilderAdmin.remove_all_studio_content ); // eslint-disable-line no-alert
		if ( ! confirmResponse ) {
			return;
		}

		// Show spinner.
		$this.next().show();
		$this.addClass( 'disabled' );

		jQuery.ajax( {
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'awb_studio_remove_content',
				nonce: jQuery( '#awb_remove_studio_content' ).val()
			}
			} ).done( function( response ) { // eslint-disable-line no-unused-vars
			} ).fail( function() {
				jQuery( '.awb-remove-studio-content-status' ).show();
			} ).always( function() {
				$this.next().hide();
				$this.removeClass( 'disabled' );
			} );
	} );

} );
