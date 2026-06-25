<?php

if (!defined('ABSPATH')) {
    exit;
}

class GF_smart_phone_field_frontend {

    function __construct() {
        add_filter('gform_field_content', array($this, 'pcafe_spf_attributes'), 10, 5);
        add_action('gform_enqueue_scripts', array($this, 'pcafe_enqueue_scripts'), 10, 2);
        add_filter('gform_field_css_class', array($this, 'pcafe_custom_class'), 10, 3);
    }

    	public function pcafe_spf_attributes( $content, $field, $value, $lead_id, $form_id  ){

		if ($field->smartPhoneFieldGField === true && $field->type == 'phone') {
			$auto_ip        = $field->smartPhoneAutoIpGField ? $field->smartPhoneAutoIpGField : '0';
			$flag           = $field->countryFlagGField ? $field->countryFlagGField : 'flagcode';
			$de_country     = $field->defaultCountryGField ? $field->defaultCountryGField : 'us';
			$pre_country    = $field->preferredCountriesGField ? implode(',', $field->preferredCountriesGField) : 'none';

			$config = "data-formid='{$field->formId}' data-fieldid='{$field->id}' data-auto_flag='{$auto_ip}' data-flag='{$flag}' data-pre_country='{$pre_country}' data-de_country='{$de_country}'";

			$content = str_replace(" ginput_container_phone'", " ginput_container_phone' {$config}", $content);
            $content = str_replace( '<input', "<span class='spf-phone error-msg hide'></span><span class='spf-phone valid-msg hide'></span><input", $content );
            
		}

		return $content;
	}

    public function pcafe_custom_class( $classes, $field, $form ) {

        if ($field->smartPhoneFieldGField === true && $field->type == 'phone') {
            $classes .= ' pcafe_sp_field';
        }

        return $classes;
    }


    function pcafe_enqueue_scripts($form, $is_ajax) {

        $form_id = $form['id'];
        $field_arr = [];

        foreach ($form['fields'] as $field) {

            if (property_exists($field, 'smartPhoneFieldGField') && $field->smartPhoneFieldGField) {

                $lvl = "input_{$field->formId}_{$field->id}";

                $field_arr[$lvl] = [];

                $field_arr[$lvl][] = "#input_{$field->formId}_{$field->id}";
                $field_arr[$lvl][] = $field['smartPhoneAutoIpGField'];
                $field_arr[$lvl][] = empty($field['defaultCountryGField']) ? 'us' : $field['defaultCountryGField'];
                $field_arr[$lvl][] = empty($field['preferredCountriesGField']) ? 'none' : $field['preferredCountriesGField'];
                $field_arr[$lvl][] = "input_{$field->id}";
                $field_arr[$lvl][] = $field['multiStepGField'];
                $field_arr[$lvl][] = empty($field['countryFlagGField']) ? 'flag' : $field['countryFlagGField'];
            }
        }

        if (count($field_arr) === 0) {
            return;
        }


        wp_enqueue_style('spf_intlTelInput', GF_SMART_PHONE_FIELD_URL . 'frontend/css/intlTelInput.min.css', array(), GF_SMART_PHONE_FIELD_VERSION_NUM);
        wp_enqueue_style('spf_style', GF_SMART_PHONE_FIELD_URL . 'frontend/css/spf_style.css', array('spf_intlTelInput'), GF_SMART_PHONE_FIELD_VERSION_NUM);

        wp_enqueue_script('spf_intlTelInput', GF_SMART_PHONE_FIELD_URL . 'frontend/js/intlTelInput-jquery.min.js', array('jquery'), GF_SMART_PHONE_FIELD_VERSION_NUM);
        wp_enqueue_script('spf_utils', GF_SMART_PHONE_FIELD_URL . 'frontend/js/utils.js', array('jquery'), GF_SMART_PHONE_FIELD_VERSION_NUM);
        wp_enqueue_script('spf_intlTelInput_main', GF_SMART_PHONE_FIELD_URL . 'frontend/js/spf_main.js', array('spf_intlTelInput'), GF_SMART_PHONE_FIELD_VERSION_NUM);

        wp_localize_script('spf_intlTelInput_main', 'spfMainData_' . $form_id, array(
            'utilsScript' => GF_SMART_PHONE_FIELD_URL . 'frontend/js/utils.js'
        ));
    }
}

new GF_smart_phone_field_frontend();
