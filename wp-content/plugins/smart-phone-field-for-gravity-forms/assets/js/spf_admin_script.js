jQuery(document).ready(function ($) {
    const { __ } = wp.i18n;

    $('#gform_setting_enable_validation, #gform_setting_validation_message, #gform_setting_number_format, #gform_setting_strict_mode, #gform_setting_rtl, #gform_setting_language, #gform_setting_enable_ipinfo, #gform_setting_ipinfo_token').addClass('pro_version');

    $('#gform_setting_enable_validation input[type=checkbox], #gform_setting_strict_mode input[type=checkbox], #gform_setting_rtl input[type=checkbox], #gform_setting_enable_ipinfo input[type=checkbox]').attr('disabled', true);

    // Append Pro button
   $('.spf_global_settings_field .gform-settings-panel__title').append(
        $('<a href="https://pluginscafe.com/plugin/smart-phone-field-for-gravity-forms-pro" target="_blank" class="button primary spf-btn-pro"></a>').text(__('Upgrade to Pro version', 'smart-phone-field-for-gravity-forms'))
   );


   //Admin Settings
   $("#pcafe_tab_box div.tab_item").hide();
   $("#pcafe_tab_box div:first").show();
   $(".pcafe_menu_wrap li:first").addClass("active");

   // Change tab class and display content
   $(".pcafe_menu_wrap a").on("click", function (event) {

    if ($(this).hasClass("demo_btn")) {
        // Open in a new tab
        window.open($(this).attr("href"), "_blank");
    } else {
        // Prevent default for other tabs
        event.preventDefault();
        $(".pcafe_menu_wrap li").removeClass("active");
       $(this).parent().addClass("active");
       $("#pcafe_tab_box div.tab_item").hide();
       $($(this).attr("href")).show();
    }
   });

   $('.p__install').on('click', function (e) {
       $(this).find('.p_btn_text').text('Installing...');
       $(this).find('.loader').addClass('active');
   });

   $('.p__activate').on('click', function (e) {
       $(this).find('.p_btn_text').text('Activating...');
       $(this).find('.loader').addClass('active');
   });

    // A newly added phone field starts on International (unformatted).
    // GF's SetDefaultValues sets phoneFormat to "formatted" and the field is then
    // serialized straight away to ajax-render the preview markup, so the override
    // must happen inside SetDefaultValues — the gform_field_added event fires too late.
    if (typeof window.SetDefaultValues === 'function') {
        var spfSetDefaultValues = window.SetDefaultValues;

        window.SetDefaultValues = function (field, index) {
            spfSetDefaultValues(field, index);

            if (field && field.type == 'phone') {
                field.phoneFormat = 'international';
            }
        };
    }

    // Safety net for add paths that don't run SetDefaultValues (e.g. duplicating a field).
    $(document).bind("gform_field_added", function (event, form, field) {
        if (field && field.type == 'phone' && field.phoneFormat != 'international') {
            field.phoneFormat = 'international';
        }
    });

    $(document).bind( "gform_load_field_settings", function (event, field, form) {
        toggleSpfDefaultSetting();
        toggleSpfOptions();
        toggleCountries();

        // Phone format is locked to international while the smart phone field is on;
        // it becomes editable again when the smart phone field is off.
        if (field.type == 'phone') {
            $('#field_phone_format').prop('disabled', Boolean(field.smartPhoneFieldGField));
        }
    });

    function toggleSpfDefaultSetting() {
        if ($("#spf_auto_ip_value").is(":checked")) {
            $(".spf_default_setting").hide();
        } else {
            $(".spf_default_setting").show();
        }
    }

    function toggleCountries() {
        var country = $("#spf_dropdown_countries").val();
        if( country != '') {
            $('.spf_prefer_setting').show();
        } else {
            $('.spf_prefer_setting').hide();
        }
    }

    function toggleSpfOptions() {
        var isEnabled = $("#spf_enable_value").is(":checked");
        var configType = $("#spf_configuration_type").val();

        if (isEnabled) {
            $("#spf_option").show();

            if (configType === 'custom') {
                $("#spf_custom_options").show();
            } else {
                $("#spf_custom_options").hide();
            }
        } else {
            $("#spf_option").hide();
            $("#spf_custom_options").hide();
        }
    }

    $(document).on("change", "#spf_enable_value", function () {
        toggleSpfOptions();

        var isSmartPhone = $(this).is(":checked");

        if (isSmartPhone) {
            $('#field_phone_format').val('international').change();
            // The format change makes GF refresh the preview; gform_after_refresh_field_preview
            // below re-attaches the flag UI to the fresh markup.
        } else {
            var selected = spfSelectedField();

            if (selected && selected.type == 'phone') {
                spfDestroyEditorField(selected.id);
            }
        }

        $('#field_phone_format').prop('disabled', isSmartPhone);
    });

    $(document).on("change", "#spf_auto_ip_value", function () {
        toggleSpfDefaultSetting();
    });

    $(document).on("change", "#spf_configuration_type", function () {
        toggleSpfOptions();
    });

    $(document).on("change", "#spf_dropdown_countries", function () {
        toggleCountries();
    });

    // ------------------------------------------------------------------
    // Form editor: render the smart phone field flags like the frontend
    // ------------------------------------------------------------------

    function spfIsEditor() {
        return typeof window.form !== 'undefined' && $('#gform_fields').length > 0;
    }

    function spfSelectedField() {
        return typeof GetSelectedField === 'function' ? GetSelectedField() : null;
    }

    // Collects smart phone fields including fields nested in repeaters,
    // mirroring the PHP-side collection.
    function spfSmartPhoneFields(fields, collected) {
        (fields || []).forEach(function (field) {
            if (field.type === 'phone' && field.smartPhoneFieldGField) {
                collected.push(field);
            }

            spfSmartPhoneFields(field.fields || field.subfields, collected);
        });

        return collected;
    }

    // Mirrors the config built in PHP for the frontend. Editor previews render
    // inputs with the id input_{fieldId} (no form id prefix).
    function spfBuildEditorConfig(field) {
        var settings = window.spf_global_settings || {};
        var args;

        if (field.configurationType == 'global') {
            args = {
                defaultCountry: settings.defaultCountry || 'us',
                autoIp: settings.autoIp || false,
                flag: settings.flag || 'flagwithcode',
                exIn: settings.exIn || false,
                countries: settings.countries || 'none'
            };
        } else {
            args = {
                defaultCountry: field.defaultCountryGField || 'us',
                autoIp: field.smartPhoneAutoIpGField || false,
                flag: field.countryFlagGField || 'flagcode',
                exIn: field.exInCountryGField || '',
                countries: field.preferredCountriesGField ? field.preferredCountriesGField.join(',') : 'none'
            };
        }

        return Object.assign({
            formId: window.form.id,
            fieldId: field.id,
            inputId: '#input_' + field.id,
            countrySearch: settings.countrySearch,
            placeholder: settings.placeholder
        }, args);
    }

    function spfInitEditorField(fieldId) {
        if (!spfIsEditor() || typeof SmartPhoneFieldFree === 'undefined' || typeof intlTelInput === 'undefined') {
            return;
        }

        spfSmartPhoneFields(window.form.fields, []).forEach(function (field) {
            if (fieldId !== undefined && fieldId != field.id) {
                return;
            }

            if (document.getElementById('input_' + field.id)) {
                // Same class and options shape as the frontend, so the flag
                // rendering goes through SmartPhoneFieldFree.configuration().
                new SmartPhoneFieldFree(spfBuildEditorConfig(field));
            }
        });
    }

    function spfDestroyEditorField(fieldId) {
        var input = document.getElementById('input_' + fieldId);

        if (input && typeof intlTelInput !== 'undefined' && typeof intlTelInput.getInstance === 'function') {
            var iti = intlTelInput.getInstance(input);

            if (iti) {
                var hadPlaceholder = iti.hadInitialPlaceholder;

                iti.destroy();

                // ITI's destroy() does not remove the example-number placeholder it
                // added; clear it unless the input had its own placeholder.
                if (!hadPlaceholder) {
                    input.removeAttribute('placeholder');
                }
            }
        }
    }

    // GF re-renders a field preview via ajax; re-attach the flag UI to the fresh markup.
    if (typeof window.gform !== 'undefined' && typeof window.gform.addAction === 'function') {
        window.gform.addAction('gform_after_refresh_field_preview', function (fieldId) {
            spfInitEditorField(fieldId);
        });
    }

    // When smart phone settings change, the flag and dial code in the preview must
    // follow the field settings: re-render the preview through GF (server-side,
    // same rendering path as the frontend) — the gform_after_refresh_field_preview
    // action above re-initializes the flag UI on the fresh markup.
    $(document).on('change', '#spf_configuration_type, #spf_country_flag_value, #spf_auto_ip_value, #spf_default_country_value, #spf_dropdown_countries, #spf_preferred_countries_value', function () {
        var selected = spfSelectedField();

        if (!selected || selected.type !== 'phone' || !selected.smartPhoneFieldGField) {
            return;
        }

        if (typeof RefreshSelectedFieldPreview === 'function') {
            RefreshSelectedFieldPreview();
        } else {
            spfDestroyEditorField(selected.id);
            spfInitEditorField(selected.id);
        }
    });

    // Initial editor load: render flags for every smart phone field on the form.
    spfInitEditorField();

});
