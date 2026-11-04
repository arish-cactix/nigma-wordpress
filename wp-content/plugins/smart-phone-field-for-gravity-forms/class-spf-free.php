<?php

GFForms::include_addon_framework();

class GFSPFFreeAddOn extends GFAddOn {
    protected $_version = GF_SMART_PHONE_FIELD_VERSION_NUM;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'smart-phone-field-for-gravity-forms';
    protected $_path = 'smart-phone-field-for-gravity-forms/gravityforms-smart-phone-field.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Smart Phone Field For Gravity Forms';
    protected $_short_title = 'Smart Phone Field';

    private static $_instance = null;

    /**
     * Get an instance of this class.
     *
     * @return GFSPFFreeAddOn
     */
    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new GFSPFFreeAddOn();
        }

        return self::$_instance;
    }

    public function get_menu_icon() {
        return file_get_contents($this->get_base_path() . '/assets/img/logo.svg');
    }

    /**
     * Return the scripts which should be enqueued.
     *
     * @return array
     */
    public function scripts() {
        $scripts = array(
            array(
                'handle'  => 'spf_admin_script',
                'src'     => $this->get_base_url() . '/assets/js/spf_admin_script.js',
                'version' => $this->_version,
                'deps'    => array('jquery', 'wp-i18n'),
                'enqueue'  => array(
                    array('admin_page' => array('form_editor', 'plugin_settings')),
                )
            ),
            array(
                'handle'  => 'spf_intlTelInput',
                'src'     => $this->get_base_url() . '/assets/js/intlTelInputWithUtils.min.js',
                'version' => $this->_version,
                'deps'    => array('jquery'),
                'enqueue'  => array(
                    array('field_types' => array('phone')),
                    array('admin_page' => 'form_editor', 'field_types' => array('phone'))
                )
            ),
            array(
                'handle'  => 'spf_main',
                'src'     => $this->get_base_url() . '/assets/js/spf_main.js',
                'version' => time(),
                'deps'    => array('jquery', 'spf_intlTelInput'),
                'enqueue'  => array(
                    array('field_types' => array('phone')),
                    array('admin_page' => 'form_editor', 'field_types' => array('phone'))
                )
            )
        );

        return array_merge(parent::scripts(), $scripts);
    }

    public function styles() {
        $styles = array(
            array(
                'handle'  => 'spf_intlTelInput',
                'src'     => $this->get_base_url() . '/assets/css/intlTelInput.min.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array('field_types' => array('phone')),
                    array('admin_page' => 'form_editor', 'field_types' => array('phone'))
                )
            ),
            array(
                'handle'  => 'spf_admin_style',
                'src'     => $this->get_base_url() . '/assets/css/spf_admin.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array('admin_page' => array('plugin_settings'))
                )
            )
        );

        return array_merge(parent::styles(), $styles);
    }

    /**
     * Handles hooks and loading of language files.
     */
    public function init() {
        parent::init();

        add_filter('gform_tooltips', array($this, 'spf_add_tooltips'));
        add_action('gform_editor_js', array($this, 'spf_editor_script'));

        add_filter('gform_field_css_class', array($this, 'spf_custom_class'), 10, 3);
        add_filter('gform_register_init_scripts', array($this, 'spf_add_init_script'), 10, 2);

        add_filter('gform_field_content', array($this, 'spf_add_attributes'), 10, 5);

        add_filter('gform_field_settings_tabs', array($this, 'spf_field_settings_tab'), 10, 2);
        add_action('gform_field_settings_tab_content_spf_phone_tab', array($this, 'spf_fields_settings_tab_content'), 10, 2);
    }

    public function spf_field_settings_tab($tabs, $form) {
        $tabs[] = array(
            // Define the unique ID for your tab.
            'id'             => 'spf_phone_tab',
            // Define the title to be displayed on the toggle button your tab.
            'title'          => 'Smart Phone Field',
            // Define an array of classes to be added to the toggle button for your tab.
            'toggle_classes' => array('gfip_toggle_1', 'gfip_toggle_2'),
            // Define an array of classes to be added to the body of your tab.
            'body_classes'   => array('gfip_toggle_class'),
        );

        return $tabs;
    }

    public function spf_fields_settings_tab_content($form) {
?>
        <li class="spf_field_setting field_setting">
            <ul>
                <li>
                    <input type="checkbox" id="spf_enable_value" onclick="SetFieldProperty('smartPhoneFieldGField', this.checked);" />
                    <label for="spf_enable_value" class="inline"><?php esc_html_e("Enable smart phone field", "smart-phone-field-for-gravity-forms"); ?>
                        <?php gform_tooltip("spf_enable_tooltips"); ?>
                    </label>
                </li>
            </ul>

            <ul id="spf_option" style="margin-top: 20px">
                <li class="spf_global_select_field field_setting">
                    <label for="spf_global_settings" class="section_label">
                        <?php esc_html_e("Configuration", "smart-phone-field-for-gravity-forms"); ?>
                        <?php gform_tooltip("global_settings"); ?>
                    </label>
                    <select name="spf_configuration_type" id="spf_configuration_type" onChange="SetFieldProperty('configurationType', this.value);">
                        <option value="global"><?php esc_html_e('Global', 'smart-phone-field-for-gravity-forms'); ?></option>
                        <option value="custom"><?php esc_html_e('Custom', 'smart-phone-field-for-gravity-forms'); ?></option>
                    </select>
                    <p style="margin-top:5px"><a target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=gf_settings&subview=smart-phone-field-for-gravity-forms')); ?>">Set Global Settings</a></p>
                </li>
            </ul>

            <div id="spf_custom_options">
                <ul>
                    <li class="spf_flag_setting field_setting">
                        <label for="field_admin_label" class="section_label">
                            <?php esc_html_e("Flag Options", "smart-phone-field-for-gravity-forms"); ?>
                            <?php gform_tooltip("spf_flag_tooltips"); ?>
                        </label>
                        <select name="spf_country_flag_value" id="spf_country_flag_value" onChange="SetFieldProperty('countryFlagGField', this.value);">
                            <option value="">Choose Flag</option>
                            <option value="flagdial">Flag with country code</option>
                            <option value="flagcode">Flag separate country code</option>
                            <option value="flag">Flag only</option>
                        </select>
                    </li>
                    <li class="spf_auto_ip_setting field_setting">
                        <ul>
                            <li>
                                <input type="checkbox" id="spf_auto_ip_value" onclick="SetFieldProperty('smartPhoneAutoIpGField', this.checked);" />
                                <label for="spf_auto_ip_value" class="inline"><?php esc_html_e("Automatically select countries", "smart-phone-field-for-gravity-forms"); ?><?php gform_tooltip("spf_autoip_tooltips"); ?></label>
                            </li>
                        </ul>
                    </li>
                    <li class="spf_default_setting field_setting">
                        <label for="field_admin_label" class="section_label">
                            <?php esc_html_e("Default country", "smart-phone-field-for-gravity-forms"); ?>
                            <?php gform_tooltip("spf_default_tooltips"); ?>
                        </label>
                        <select name="spf_default_country_value" id="spf_default_country_value" onChange="SetFieldProperty('defaultCountryGField', this.value);">
                            <?php
                            foreach (GF_SPF_Helper::get_countries() as $value => $name) {
                                echo '<option value="' . esc_attr($value) . '">' . esc_html($name) . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li class="spf_dropdown_countries field_setting">
                        <label for="field_admin_label" class="section_label">
                            <?php esc_html_e("Dropdown countries", "smart-phone-field-for-gravity-forms"); ?>
                            <?php gform_tooltip("spf_dropdown_countries"); ?>
                        </label>
                        <select name="spf_dropdown_countries" id="spf_dropdown_countries" onChange="SetFieldProperty('exInCountryGField', this.value);">
                            <option value=""><?php esc_html_e('All countries', 'smart-phone-field-for-gravity-forms'); ?></option>
                            <option value="ex_only"><?php esc_html_e('Only included following countries', 'smart-phone-field-for-gravity-forms'); ?></option>
                            <option value="pre_only"><?php esc_html_e('Exclude following countries', 'smart-phone-field-for-gravity-forms'); ?></option>
                        </select>
                    </li>
                    <li class="spf_prefer_setting field_setting">
                        <label for="field_admin_label" class="section_label">
                            <?php esc_html_e("Countries", "smart-phone-field-for-gravity-forms"); ?>
                            <?php gform_tooltip("spf_prefered_tooltips"); ?>
                        </label>
                        <select style="min-height: 100px" multiple="multiple" name="spf_preferred_countries_value" id="spf_preferred_countries_value" onChange="SetFieldProperty('preferredCountriesGField', jQuery(this).val());">
                            <?php
                            foreach (GF_SPF_Helper::get_countries() as $value => $name) {
                                echo '<option value="' . esc_attr($value) . '">' . esc_html($name) . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                </ul>

            </div>
        </li>
    <?php
    }

    public function spf_add_init_script($form) {
        $smart_phone_fields = $this->get_smart_phone_fields($form);

        if (empty($smart_phone_fields)) {
            return $form;
        }

        require_once(GFCommon::get_base_path() . '/form_display.php');

        foreach ($smart_phone_fields as $field) {
            $form_id = $form['id'];
            $id      = $field['id'];

            $common_args = [
                'formId'            =>  $form_id,
                'fieldId'           =>  $id,
                'inputId'           =>  '#input_' . $form_id . '_' . $id,
                'countrySearch'     =>  $this->get_plugin_setting('country_search') ? $this->get_plugin_setting('country_search') : true,
                'placeholder'       =>  $this->get_plugin_setting('rm_placeholder') ? $this->get_plugin_setting('rm_placeholder') : false,
            ];

            $args = [];

            if ($field->configurationType == 'global') {
                $args = [
                    'defaultCountry'    =>  $this->get_plugin_setting('default_country') ? $this->get_plugin_setting('default_country') : 'us',
                    'autoIp'            =>  $this->get_plugin_setting('auto_ip') ? $this->get_plugin_setting('auto_ip') : false,
                    'flag'              =>  $this->get_plugin_setting('flag_options') ? $this->get_plugin_setting('flag_options') : 'flagwithcode',
                    'exIn'              =>  $this->get_plugin_setting('county_type') ? $this->get_plugin_setting('county_type') : false,
                    'countries'         =>  $this->get_plugin_setting('countries') ? implode(',', $this->get_plugin_setting('countries')) : 'none',
                ];
            } else {
                $args = [
                    'defaultCountry'    => $field->defaultCountryGField ? $field->defaultCountryGField : 'us',
                    'autoIp'            => $field->smartPhoneAutoIpGField ? $field->smartPhoneAutoIpGField : false,
                    'flag'              => $field->countryFlagGField ? $field->countryFlagGField : 'flagcode',
                    'exIn'              => $field->exInCountryGField ? $field->exInCountryGField : '',
                    'countries'         => $field->preferredCountriesGField ? implode(',', $field->preferredCountriesGField) : 'none'
                ];
            }

            $config = array_merge($common_args, $args);

            $slug   = 'pcafe_spf_free_' . $form_id . '_' . $id;
            $script = 'window.' . $slug . ' = new SmartPhoneFieldFree( ' . json_encode($config) . ' );';

            GFFormDisplay::add_init_script($form_id, $slug, GFFormDisplay::ON_PAGE_RENDER, $script);
        }

        return $form;
    }

    public function get_smart_phone_fields($form) {
        if (empty($form['fields'])) {
            return array();
        }

        $fields = array();

        foreach ($form['fields'] as $field) {
            $this->collect_smart_phone_fields($field, $fields);
        }

        return $fields;
    }

    /**
     * Recursively collects smart phone fields, including fields nested
     * inside repeater fields at any depth.
     *
     * @param object $field  The field object.
     * @param array  $fields Collected smart phone fields, passed by reference.
     */
    public function collect_smart_phone_fields($field, &$fields) {
        if ($this->is_smart_phone_field($field)) {
            $fields[] = $field;
        }

        $children = array();
        if (isset($field->fields) && is_array($field->fields)) {
            $children = $field->fields;
        } elseif (isset($field->subfields) && is_array($field->subfields)) {
            $children = $field->subfields;
        }

        foreach ($children as $child) {
            $this->collect_smart_phone_fields($child, $fields);
        }
    }

    public function is_smart_phone_field($field) {
        return rgar($field, 'type') === 'phone' && rgar($field, 'smartPhoneFieldGField');
    }

    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__('Smart Phone Field Settings', 'smart-phone-field-for-gravity-forms'),
                'class'  => 'spf_global_settings_field',
                'fields' => array(
                    array(
                        'name'              => 'auto_ip',
                        'type'              => 'toggle',
                        'label'             => esc_html__('Automatically select country (GeoIP)', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable to show ip based country flag.', 'smart-phone-field-for-gravity-forms'),
                    ),
                    array(
                        'name'              => 'default_country',
                        'type'              => 'select',
                        'label'             => esc_html__('Default Country', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable to show ip based country flag.', 'smart-phone-field-for-gravity-forms'),
                        'choices'           => GF_SPF_Helper::get_settings_countries(),
                        'enhanced_ui'       => true,
                        'default_value'     => 'US',
                        'dependency'        => array(
                            'live'   => true,
                            'fields' => array(
                                array(
                                    'field'  => 'auto_ip',
                                    'values'  => array(false, '', 0)
                                ),
                            )
                        ),
                    ),
                    array(
                        'name'              => 'flag_options',
                        'type'              => 'select',
                        'label'             => esc_html__('Flag Options', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable to show ip based country flag.', 'smart-phone-field-for-gravity-forms'),
                        'choices'           => array(
                            array(
                                'label' => esc_html__('Flag Only', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'flagonly'
                            ),
                            array(
                                'label' => esc_html__('Flag with Country Code', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'flagwithcode'
                            ),
                            array(
                                'label' => esc_html__('Flag with Separate Country Code', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'flagcode'
                            )
                        ),
                        'enhanced_ui'       => true,
                        'default_value'     => 'flagwithcode'
                    ),
                    array(
                        'name'              => 'county_type',
                        'type'              => 'select',
                        'label'             => esc_html__('Dropdown Countries', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable it for ignore any irrelevant characters.', 'smart-phone-field-for-gravity-forms'),
                        'choices' => array(
                            array(
                                'label' => esc_html__('Include all countries', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'all'
                            ),
                            array(
                                'label' => esc_html__('Exclude following countries', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'pre_only'
                            ),
                            array(
                                'label' => esc_html__('Only include following countries', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'ex_only'
                            )
                        ),
                        'default_value'     => 'all'
                    ),
                    array(
                        'name'              => 'countries[]',
                        'type'              => 'select',
                        'label'             => esc_html__('Countries', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable it for ignore any irrelevant characters.', 'smart-phone-field-for-gravity-forms'),
                        'choices'           => GF_SPF_Helper::get_settings_countries(),
                        'multiple'          => true,
                        'enhanced_ui'       => true,
                        'dependency'        => array(
                            'live'   => true,
                            'fields' => array(
                                array(
                                    'field'  => 'county_type',
                                    'values' => array('ex_only', 'pre_only'),
                                ),
                            )
                        ),
                    ),
                    array(
                        'name'              => 'country_search',
                        'label'             => esc_html__('Country Search', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable it for ignore any irrelevant characters.', 'smart-phone-field-for-gravity-forms'),
                        'type'              => 'toggle',
                        'default_value'     => true,
                    ),
                    array(
                        'name'              => 'rm_placeholder',
                        'type'              => 'toggle',
                        'label'             => esc_html__('Hide Placeholder', 'smart-phone-field-for-gravity-forms'),
                        'tooltip'           => esc_html__('Enable to show ip based country flag.', 'smart-phone-field-for-gravity-forms'),
                    ),
                    array(
                        'name'              => 'enable_validation',
                        'type'              => 'toggle',
                        'label'             => esc_html__('Enable phone number validation (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'disabled'          => true,
                    ),
                    array(
                        'name'              => 'validation_message',
                        'type'              => 'text',
                        'label'             => esc_html__('Type validation fail message (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'default_value'     => 'Please enter a valid phone number.',
                        'disabled'          => true,
                    ),
                    array(
                        'name'              => 'number_format',
                        'type'              => 'radio',
                        'label'             => esc_html__('Phone format in notification (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'choices'           => array(
                            array(
                                'label' => esc_html__('Raw Phone Number e.g. +12025552671', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'raw'
                            ),
                            array(
                                'label' => esc_html__('Raw National Format e.g. 2015552671', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'raw_n'
                            ),
                            array(
                                'label' => esc_html__('National Format e.g. (201) 555-0123', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'nf',
                            ),
                            array(
                                'label' => esc_html__('International Format e.g. +1 201-555-2671', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'in_f'
                            )
                        ),
                        'default_value'     => 'raw',
                        'disabled'          => true,
                    ),
                    array(
                        'name'              => 'strict_mode',
                        'type'              => 'toggle',
                        'label'             => esc_html__('Strict Mode (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'disabled'          => true
                    ),
                    array(
                        'name'              => 'rtl',
                        'type'              => 'toggle',
                        'label'             => esc_html__('Enable RTL (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'disabled'          => true
                    ),
                    array(
                        'name'              => 'language',
                        'label'             => esc_html__('Language (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'description'       => esc_html__('About 30+ languages added. You can add or change language text with custom hook also.', 'smart-phone-field-for-gravity-forms'),
                        'type'              => 'select',
                        'class'             => 'small',
                        'choices'           => [
                            array(
                                'label' => esc_html__('English', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'en'
                            ),
                            array(
                                'label' => esc_html__('Arabic', 'smart-phone-field-for-gravity-forms'),
                                'value' => 'ar'
                            )
                        ],
                        'disabled'          => true
                    ),
                    array(
                        'name'              => 'enable_ipinfo',
                        'label'             => esc_html__('Enable IP merge tag  (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'type'              => 'toggle',
                        'disabled'          => true
                    ),
                    array(
                        'name'              => 'ipinfo_token',
                        'label'             => esc_html__('Ipinfo token (Pro version)', 'smart-phone-field-for-gravity-forms'),
                        'type'              => 'text',
                        'default_value'     => 'Token here',
                        'disabled'          => true,
                    ),

                )
            )
        );
    }

    public function spf_editor_script() {
        $global_settings = array(
            'defaultCountry' => $this->get_plugin_setting('default_country') ? $this->get_plugin_setting('default_country') : 'us',
            'autoIp'         => $this->get_plugin_setting('auto_ip') ? $this->get_plugin_setting('auto_ip') : false,
            'flag'           => $this->get_plugin_setting('flag_options') ? $this->get_plugin_setting('flag_options') : 'flagwithcode',
            'exIn'           => $this->get_plugin_setting('county_type') ? $this->get_plugin_setting('county_type') : false,
            'countries'      => $this->get_plugin_setting('countries') ? implode(',', (array) $this->get_plugin_setting('countries')) : 'none',
            'countrySearch'  => $this->get_plugin_setting('country_search') ? $this->get_plugin_setting('country_search') : true,
            'placeholder'    => $this->get_plugin_setting('rm_placeholder') ? $this->get_plugin_setting('rm_placeholder') : false,
        );
    ?>
        <script type='text/javascript'>
            //global plugin settings for the editor preview (mirrors the frontend config)
            window.spf_global_settings = <?php echo wp_json_encode($global_settings); ?>;

            //adding setting to fields of type "phone"
            fieldSettings.phone += ", .spf_global_select_field";
            fieldSettings.phone += ", .spf_dropdown_countries";
            fieldSettings.phone += ", .spf_field_setting";
            fieldSettings.phone += ", .spf_auto_ip_setting";
            fieldSettings.phone += ", .spf_prefer_setting";
            fieldSettings.phone += ", .spf_default_setting";
            fieldSettings.phone += ", .spf_multi_setting";
            fieldSettings.phone += ", .spf_flag_setting";

            //binding to the load field settings event to initialize the checkbox
            jQuery(document).bind("gform_load_field_settings", function(event, field, form) {

                if (field.smartPhoneFieldGField && !field.configurationType) {
                    field.configurationType = 'custom';
                }

                if (!field.configurationType) {
                    field.configurationType = 'global';
                }

                if (!field.defaultCountryGField) {
                    field.defaultCountryGField = 'US';
                }

                jQuery("#spf_enable_value").prop('checked', Boolean(rgar(field, 'smartPhoneFieldGField')));
                jQuery("#spf_multi_value").prop('checked', Boolean(rgar(field, 'multiStepGField')));
                jQuery("#spf_auto_ip_value").prop('checked', Boolean(rgar(field, 'smartPhoneAutoIpGField')));
                jQuery("#spf_preferred_countries_value").val(field["preferredCountriesGField"]);
                jQuery("#spf_default_country_value").val(field["defaultCountryGField"]);
                jQuery("#spf_country_flag_value").val(field["countryFlagGField"]);
                jQuery("#spf_configuration_type").val(field["configurationType"]);
                jQuery("#spf_dropdown_countries").val(field["exInCountryGField"]);
            });
        </script>
<?php
    }

    public function spf_add_attributes($content, $field, $value, $lead_id, $form_id) {
        if ($field->smartPhoneFieldGField === true && $field->type == 'phone') {
            $content = str_replace('<input', "<span class='spf-phone error-msg hide'></span><span class='spf-phone valid-msg hide'></span><input", $content);
        }

        return $content;
    }

    public function spf_custom_class($classes, $field, $form) {
        if ($field->smartPhoneFieldGField === true && $field->type == 'phone') {
            $classes .= ' pcafe_sp_field';
        }

        return $classes;
    }


    /**
     * Returns an array of tooltips for the plugin.
     *
     * @return array
     */
    public function spf_add_tooltips() {
        $tooltips['spf_enable_tooltips'] = "<h6>" . esc_html__("Enable smart phone field", "smart-phone-field-for-gravity-forms") . "</h6>" . esc_html__("Check this box to show smart phone field", "smart-phone-field-for-gravity-forms") . "";
        $tooltips['spf_autoip_tooltips'] = esc_html__("Check this box to show ip based country flag.", "smart-phone-field-for-gravity-forms");
        $tooltips['spf_default_tooltips'] = esc_html__("Select one for showing specific country. Default: US", "smart-phone-field-for-gravity-forms");
        $tooltips['spf_prefered_tooltips'] = esc_html__("Select multiple country for showing in preferred country suggestion. Default: US, UK", "smart-phone-field-for-gravity-forms");
        $tooltips['flag_options'] = esc_html__("Select an option for showing flag type in the input field.", "smart-phone-field-for-gravity-forms");
        $tooltips['spfield_validation'] = esc_html__("Check this box for adding validation on smart phone field.", "smart-phone-field-for-gravity-forms");
        $tooltips['spf_flag_tooltips'] = esc_html__("Choose flag option for getting flag and dial code in input field.", "smart-phone-field-for-gravity-forms");
        $tooltips['spf_multi_tooltips'] = esc_html__("Multistep with country code submission is available in pro version. <a href='https://pluginscafe.com/smart-phone-field-pro/' target='_blank'>PRO</a>", "smart-phone-field-for-gravity-forms");
        $tooltips['global_settings']        = esc_html__("Choose configuration type", "smart-phone-field-for-gravity-forms");
        return $tooltips;
    }
}
