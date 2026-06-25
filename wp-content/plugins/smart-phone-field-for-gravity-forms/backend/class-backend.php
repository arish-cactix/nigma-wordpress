<?php

if (!defined('ABSPATH')) {
	exit;
}


class GF_smart_phone_field_backend {

	function __construct() {
		add_action('gform_field_standard_settings', array($this, 'gf_spf_options_settings'), 10, 2);

		add_action('gform_editor_js', array($this, 'gf_spf_editor_script'));
		add_filter('gform_tooltips', array($this, 'gf_spf_tooltips'));
		add_action('admin_enqueue_scripts', array($this, 'gf_spf_admin_scripts'));
	}


	function gf_spf_options_settings($position, $form_id) {
		if ($position == 25) { ?>
			<li class="spf_field_setting field_setting">
				<ul>
					<li>
						<input type="checkbox" id="spf_enable_value" onclick="SetFieldProperty('smartPhoneFieldGField', this.checked);" />
						<label for="spf_enable_value" class="inline"><?php _e("Enable smart phone field", "gravityforms"); ?><?php gform_tooltip("spf_enable_tooltips"); ?></label>
					</li>
				</ul>

				<div class="spf_options">
					<ul>
						<li class="spf_flag_setting field_setting">
							<label for="field_admin_label" class="section_label">
								<?php _e("Flag Options", "gravityforms"); ?>
								<?php gform_tooltip("spf_flag_tooltips"); ?>
							</label>
							<select name="spf_country_flag_value" id="spf_country_flag_value" onChange="SetFieldProperty('countryFlagGField', this.value);">
								<option value="">Choose Flag</option>
								<option value="flagdial">Flag with dial code</option>
								<option value="flagcode">Flag separate dial code</option>
								<option value="flag">Flag only</option>
							</select>
							<p style="border: 1px solid #ffbe03; padding: 8px 12px; border-radius: 3px; font-size: 12px;"><?php _e("Choose <strong>Flag separate dial code</strong> for getting country/dial code in notification/entries.", "gravityforms"); ?></p>
						</li>
						<li class="spf_auto_ip_setting field_setting">
							<ul>
								<li>
									<input type="checkbox" id="spf_auto_ip_value" onclick="SetFieldProperty('smartPhoneAutoIpGField', this.checked);" />
									<label for="spf_auto_ip_value" class="inline"><?php _e("Automatically select countries", "gravityforms"); ?><?php gform_tooltip("spf_autoip_tooltips"); ?></label>
								</li>
							</ul>
						</li>
						<li class="spf_default_setting field_setting">
							<label for="field_admin_label" class="section_label">
								<?php _e("Default country", "gravityforms"); ?>
								<?php gform_tooltip("spf_default_tooltips"); ?>
							</label>
							<select name="spf_default_country_value" id="spf_default_country_value" onChange="SetFieldProperty('defaultCountryGField', this.value);">
								<?php
								foreach (GF_SPF_Helper::get_countries() as $value => $name) {
									echo '<option value="' . $value . '">' . $name . '</option>';
								}
								?>
							</select>
						</li>
						<li class="spf_prefer_setting field_setting">
							<label for="field_admin_label" class="section_label">
								<?php _e("Preferred countries", "gravityforms"); ?>
								<?php gform_tooltip("spf_prefered_tooltips"); ?>
							</label>
							<select style="min-height: 100px" multiple="multiple" name="spf_preferred_countries_value" id="spf_preferred_countries_value" onChange="SetFieldProperty('preferredCountriesGField', jQuery(this).val());">
								<?php
								foreach (GF_SPF_Helper::get_countries() as $value => $name) {
									echo '<option value="' . $value . '">' . $name . '</option>';
								}
								?>
							</select>
						</li>
					</ul>

				</div>
			</li>


		<?php
		}
	}


	function gf_spf_editor_script() {
		?>
		<script type='text/javascript'>
			//adding setting to fields of type "phone"
			fieldSettings.phone += ", .spf_field_setting";
			fieldSettings.phone += ", .spf_auto_ip_setting";
			fieldSettings.phone += ", .spf_prefer_setting";
			fieldSettings.phone += ", .spf_default_setting";
			fieldSettings.phone += ", .spf_multi_setting";
			fieldSettings.phone += ", .spf_flag_setting";

			//binding to the load field settings event to initialize the checkbox
			jQuery(document).bind("gform_load_field_settings", function(event, field, form) {
				jQuery("#spf_enable_value").prop('checked', Boolean(rgar(field, 'smartPhoneFieldGField')));
				jQuery("#spf_multi_value").prop('checked', Boolean(rgar(field, 'multiStepGField')));
				jQuery("#spf_auto_ip_value").prop('checked', Boolean(rgar(field, 'smartPhoneAutoIpGField')));
				jQuery("#spf_preferred_countries_value").val(field["preferredCountriesGField"]);
				jQuery("#spf_default_country_value").val(field["defaultCountryGField"]);
				jQuery("#spf_country_flag_value").val(field["countryFlagGField"]);
			});

			jQuery('body').on('change', '#spf_enable_value', function(e) {
				if (jQuery(this).is(':checked')) {
					jQuery(this).parent().parent().parent().parent().find('#field_phone_format').val('international').change();
				}
			});
		</script>
<?php
	}

	function gf_spf_tooltips() {
		$tooltips['spf_enable_tooltips'] = "<h6>" . esc_html__("Enable smart phone field", "gravityforms") . "</h6>" . esc_html__("Check this box to show smart phone field", "gravityforms") . "";
		$tooltips['spf_autoip_tooltips'] = esc_html__("Check this box to show ip based country flag.", "gravityforms");
		$tooltips['spf_default_tooltips'] = esc_html__("Select one for showing specific country. Default: US", "gravityforms");
		$tooltips['spf_prefered_tooltips'] = esc_html__("Select multiple country for showing in preferred country suggestion. Default: US, UK", "gravityforms");
		$tooltips['flag_options'] = esc_html__("Select an option for showing flag type in the input field.", "gravityforms");
		$tooltips['spfield_validation'] = esc_html__("Check this box for adding validation on smart phone field.", "gravityforms");
		$tooltips['spf_flag_tooltips'] = esc_html__("Choose flag option for getting flag and dial code in input field.", "gravityforms");
		$tooltips['spf_multi_tooltips'] = esc_html__("Multistep with country code submission is available in pro version. <a href='https://pluginscafe.com/smart-phone-field-pro/' target='_blank'>PRO</a>", "gravityforms");
		return $tooltips;
	}

	function gf_spf_admin_scripts() {
		wp_enqueue_script('spf_admin', GF_SMART_PHONE_FIELD_URL . 'backend/js/spf_admin.js', array(), GF_SMART_PHONE_FIELD_VERSION_NUM, true);
	}
}

new GF_smart_phone_field_backend();
