class SmartPhoneFieldFree {
    // Keeps one instance per field so repeated gform_post_render events
    // (ajax validation, multi-page, repeater re-renders) don't stack
    // document-level listeners.
    static instances = new Map();

    constructor(options) {
        const key = `${options.formId}_${options.fieldId}`;

        if (SmartPhoneFieldFree.instances.has(key)) {
            const existing = SmartPhoneFieldFree.instances.get(key);
            existing.options = options;
            existing.init();
            return existing;
        }

        this.options = options;
        this.repeaterListener = null;

        SmartPhoneFieldFree.instances.set(key, this);
        this.init();
    }

    init() {
        this.initSmartPhoneFieldFree();
        this.addCountryCodeInputHandler();
        this.bindRepeaterListeners();
    }

    /**
     * Collects every input belonging to this field: the input itself plus one
     * input per repeater row (ids get a "-{index}" suffix inside repeaters,
     * e.g. #input_12_3-0). The hidden repeater template row is excluded.
     */
    getInputs(root) {
        const scope = root || document;
        const baseId = (this.options.inputId || '').replace(/^#/, '');

        if (!baseId) {
            return [];
        }

        const inputs = scope.querySelectorAll(`#${baseId}, input[id^="${baseId}-"]`);

        return Array.from(inputs).filter((input) => !input.closest('.gfield_repeater_template'));
    }

    initSmartPhoneFieldFree() {
        if (typeof intlTelInput === 'undefined') {
            return;
        }

        this.getInputs().forEach((input) => this.initInput(input));
    }

    initInput(input) {
        // Already initialized (e.g. gform_post_render fired again on the same DOM).
        if (typeof intlTelInput.getInstance === 'function' && intlTelInput.getInstance(input)) {
            return;
        }

        const preValue = input.value;
        const iti = window.intlTelInput(input, this.configuration());
        const fieldId = `${this.options.formId}_${this.options.fieldId}`;

        input.addEventListener('blur', (e) => {
            this.validateNumber(e.currentTarget, fieldId, iti);
        });

        input.addEventListener('keyup', (e) => {
            this.formatValidation(e.currentTarget, iti);
        });

        if (preValue) {
            iti.setNumber(preValue);
            this.validateNumber(input, fieldId, iti);
        }

        this.attachCountryCodeHandler(input, iti);
    }

    /**
     * Gravity Forms fires this DOM event on document after a repeater row is
     * added. The row is cloned from the (uninitialized) template, so its phone
     * inputs need to be initialized here.
     */
    bindRepeaterListeners() {
        if (this.repeaterListener) {
            return;
        }

        this.repeaterListener = (event) => {
            if (typeof intlTelInput === 'undefined') {
                return;
            }

            const clone = event.detail && event.detail.clone;

            if (!clone || typeof clone.querySelectorAll !== 'function') {
                return;
            }

            this.getInputs(clone).forEach((input) => this.initInput(input));
        };

        document.addEventListener('gform/repeater/post_item_add', this.repeaterListener);
    }

    configuration() {
        let config = {
            initialCountry: this.options.defaultCountry,
            formatOnDisplay: false,
            formatAsYouType: false,
            fixDropdownWidth: true,
            hiddenInput: function (telInputName) {
                return {
                    phone: telInputName
                };
            },
            useFullscreenPopup: false
        };

        if (this.options.countrySearch) {
            config.countrySearch = true;
        }

        if (this.options.flag === "flagcode") {
            config.nationalMode = false;
            config.autoHideDialCode = false;
        } else if (this.options.flag === "flagdial" || this.options.flag === "flagwithcode") {
            config.nationalMode = false;
            config.separateDialCode = true;
        } else {
            config.nationalMode = true;
        }

        if (this.options.exIn === 'ex_only') {
            config.onlyCountries = this.options.countries.split(',');
        }

        if (this.options.exIn === 'pre_only') {
            config.excludeCountries = this.options.countries.split(',');
        }

        if (this.options.autoIp) {
            this.detectIPAddress(config);
        }

        if (this.options.placeholder) {
            config.autoPlaceholder = 'off';
        }

        config = gform.applyFilters('gform_spf_options_pre_init', config, this.options.formId, this.options.fieldId);

        return config;
    }

    detectIPAddress(config) {
        const api_url = "https://ipinfo.io/json";
        config.initialCountry = "auto";
        config.geoIpLookup = function (callback) {
            fetch(api_url)
                .then(r => r.json())
                .then(data => {
                    const country = (data && data.country) ? data.country.toLowerCase() : 'us';
                    callback(country);
                })
                .catch(() => callback('us'));
        };
    }

    validateNumber(input, fieldId, iti) {
        if (!input.value) {
            const errorMsg = input.parentNode?.parentNode?.querySelector('.error-msg');
            const validMsg = input.parentNode?.parentNode?.querySelector('.valid-msg');
            if (errorMsg) errorMsg.classList.add('hide');
            if (validMsg) validMsg.classList.add('hide');
            return;
        }

        const isValid = iti.isValidNumber();
        const errorMsg = input.parentNode?.parentNode?.querySelector('.error-msg');
        const validMsg = input.parentNode?.parentNode?.querySelector('.valid-msg');
        const hiddenInput = input.parentNode?.parentNode?.querySelector('input[type="hidden"]');
        const number = iti.getNumber(intlTelInput.utils.numberFormat.E164);

        if (errorMsg && validMsg && input.value) {
            if (isValid) {
                errorMsg.classList.add('hide');
                validMsg.classList.remove('hide');
                input.setAttribute('aria-invalid', 'false');
            } else {
                validMsg.classList.add('hide');
                errorMsg.classList.remove('hide');
                input.setAttribute('aria-invalid', 'true');
            }
            hiddenInput.value = number;
        } else {
            validMsg.classList.add('hide');
	        errorMsg.classList.add('hide');
        }
    }

    formatValidation(input, iti) {
        if (!input.value) {
            const errorMsg = input.parentNode?.parentNode?.querySelector('.error-msg');
            const validMsg = input.parentNode?.parentNode?.querySelector('.valid-msg');
            if (errorMsg) errorMsg.classList.add('hide');
            if (validMsg) validMsg.classList.add('hide');
            return;
        }

        const isValid = iti.isValidNumber();
        const errorMsg = input.parentNode?.parentNode?.querySelector('.error-msg');
        const validMsg = input.parentNode?.parentNode?.querySelector('.valid-msg');

        if (errorMsg && validMsg) {
            if (isValid) {
                errorMsg.classList.add('hide');
                validMsg.classList.remove('hide');
            } else {
                validMsg.classList.add('hide');
                errorMsg.classList.add('hide');
            }
        }
    }

    addCountryCodeInputHandler() {
        if (this.options.flag !== 'flagcode') {
            return;
        }

        this.getInputs().forEach((input) => {
            const iti = intlTelInput.getInstance(input);
            this.attachCountryCodeHandler(input, iti);
        });
    }

    attachCountryCodeHandler(input, iti) {
        if (this.options.flag !== 'flagcode' || !iti || input.dataset.spfCountryHandler) {
            return;
        }

        input.dataset.spfCountryHandler = '1';

        const handleCountryChange = (event) => {
            const currentCountryData = iti.getSelectedCountryData();
            const currentCode = `+${currentCountryData.dialCode}`;
            this.updateCountryCodeHandler(event.currentTarget, currentCode);
        };

        input.addEventListener('keydown', handleCountryChange);
        input.addEventListener('input', handleCountryChange);
        input.addEventListener('countrychange', handleCountryChange);
    }

    updateCountryCodeHandler(element, currentCode) {
        let value = element.value;

        if (!currentCode || currentCode === '+undefined' || ['', '+'].includes(value)) {
            return;
        }

        if (!value.startsWith(currentCode)) {
            value = value.replace(/\+/g, '');
            element.value = currentCode + value;
        }
    }
}
