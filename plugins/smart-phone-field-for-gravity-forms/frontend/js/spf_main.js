"use strict";

var $j = jQuery.noConflict();

$j(document).bind("gform_post_render", function (event, form_id) {
    var spfMainData = window["spfMainData_" + form_id];

    if (!spfMainData) {
        return;
    }

    jQuery('.pcafe_sp_field').each( function(i, e) {
        var _this       = jQuery(this).find('.ginput_container_phone'),
            phoneData   = _this.data(),
            form_id     = phoneData.formid,
            field_id    = phoneData.fieldid,
            input_id    = '#input_' + form_id + '_' + field_id,
            field_input = 'input_' + field_id,
            flagOption  = phoneData.flag,
            options     = {
                utilsScript: spfMainData.utilsScript,
                countrySearch: false,
                formatAsYouType: false,
                formatOnDisplay: false,
                fixDropdownWidth: true,
                initialCountry: 'us',
                useFullscreenPopup: false
            };

        if ( phoneData.de_country !== 0 && phoneData.auto_flag === 0 ) {
            options.initialCountry = phoneData.de_country;
        }

        if (flagOption == "flagcode") {
            options.hiddenInput = function(telInputName) {
                return {
                    phone: field_input
                };
            };
            options.autoHideDialCode = false;
            options.nationalMode = false;
        } else if (flagOption == "flagdial") {
            options.showSelectedDialCode = true;
            options.autoHideDialCode = true;
            options.nationalMode = false;
        } else {
            options.nationalMode = true;
        }

        if( phoneData.pre_country && phoneData.pre_country != "none" ) {
            options.preferredCountries = phoneData.pre_country.split(',');
        }

        if ( phoneData.auto_flag ) {
            options.initialCountry = "auto";
            options.geoIpLookup = function (success, failure) {
                $j.get("https://ipinfo.io", function () {}, "jsonp").always(
                    function (resp) {
                        var countryCode =
                            resp && resp.country ? resp.country : "";
                        success(countryCode);
                    }
                );
            };
        }

        options = gform.applyFilters( 'gform_spf_options_pre_init', options, form_id, field_id);

        $j(input_id).intlTelInput(options);    
    });

    /**
     * Phone Number Validation
    */

    jQuery('.pcafe_sp_field').each( function(i, e) {
        var _this       = jQuery(this).find('.ginput_container_phone'),
            phoneData   = _this.data(),
            index = phoneData.formid + '_' + phoneData.fieldid,
            seInputID = "spf_" + index,
            setCountryCode = "spf_c_" + index,
            inputId     = '#input_' + phoneData.formid + '_' + phoneData.fieldid,
            teleInput   = jQuery(inputId);

        teleInput.keypress(function (e) {   
            var charCode = (e.which) ? e.which : event.keyCode;
            if (String.fromCharCode(charCode).match(/[^0-9+]/g))    
                return false;                        
        }); 

        teleInput.on("change", function () {
            var rawNumber = teleInput.intlTelInput("getNumber");
            var country = teleInput.intlTelInput("getSelectedCountryData");
            if (phoneData.flag != "flagdial") {
                sessionStorage.setItem(seInputID, rawNumber);
                teleInput.closest("div").find("input:hidden").val(rawNumber);
            }
            sessionStorage.setItem(setCountryCode, country.iso2);
        });

        if (phoneData.flag == "flagcode") {
            var ova = teleInput.val();
            if (!ova.length) {
                sessionStorage.removeItem(seInputID);
            } else {
                sessionStorage.setItem(seInputID, ova);
            }
            teleInput.on("change", function () {
                var nno = $j(this).val();
                if (!nno.length) {
                    sessionStorage.removeItem(seInputID);
                }
            });
        }

        if ( sessionStorage.getItem(seInputID) != undefined && phoneData.flag == "flagcode" ) {
            teleInput.closest("div").find("input:hidden").val(sessionStorage.getItem(seInputID));
        }

        if (sessionStorage.getItem(setCountryCode) != undefined) {
            teleInput.intlTelInput( "setCountry", sessionStorage.getItem(setCountryCode) );
        }

        teleInput.blur(function () {
            isInputValid($j(this));
        });

        teleInput.keydown(function () {
            hideInputValidation($j(this));
        });

        function hideInputValidation(phoneID) {
            phoneID.removeClass("error");
            phoneID.parent().parent().parent().find(".valid-msg").addClass("hide");
            phoneID.parent().parent().parent().find(".error-msg").addClass("hide");
        }

        function isInputValid(phoneID) {
            var errorMsg = phoneID.parent().parent().parent().find(".error-msg"),
                validMsg = phoneID.parent().parent().parent().find(".valid-msg");

            if ($j.trim(phoneID.val())) {
                if (phoneID.intlTelInput("isValidNumberPrecise")) {
                    validMsg.removeClass("hide");
                    errorMsg.addClass("hide");
                } else {
                    phoneID.addClass("error");
                    errorMsg.removeClass("hide");
                    validMsg.addClass("hide");
                }
            }
        }
    });
    
});


$j(document).on("gform_confirmation_loaded", function (event, formId) {
    jQuery('.pcafe_sp_field').each( function(i, e) {
        var _this       = jQuery(this).find('.ginput_container_phone'),
            phoneData   = _this.data(),
            index = phoneData.formid + '_' + phoneData.fieldid,
            getInputID = "spf_" + index,
            setCountryCode = "spf_c_" + index;

        sessionStorage.removeItem(getInputID);
        sessionStorage.removeItem(setCountryCode);
    });
});   