<div id="usage" class="content-usage tab_item">
    <div class="content_heading">
        <h2><?php esc_html_e('Basic Usage', 'smart-phone-field-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('A Beginner\'s Guide to Efficiently Using the Plugin', 'smart-phone-field-for-gravity-forms'); ?></p>
    </div>

    <section class="section_full background_free">
        <div class="col_description">
            <h2><?php esc_html_e('Basic Usage (Free version)', 'smart-phone-field-for-gravity-forms'); ?></h2>
            <p><?php esc_html_e('After installing the plugin, add the phone number field from the Advanced tab in Gravity Forms. Simply click on the smart phone field tab in the Gravity Forms field panel to show smart phone field options to your form.', 'smart-phone-field-for-gravity-forms'); ?></p>
            <p><?php esc_html_e('Now, configure it according to your requirements, as shown in the image on the right.', 'smart-phone-field-for-gravity-forms'); ?></p>
        </div>
        <div class="col_image">
            <img src="<?php echo esc_url(GF_SMART_PHONE_FIELD_URL . 'admin/images/free_options.webp'); ?>">
        </div>
    </section>

    <div class="usage_wrap">
        <section class="section_full background_pro">
            <div class="col_description">
                <h2><?php esc_html_e('Basic Usage Smart Phone Field (Pro)', 'smart-phone-field-for-gravity-forms'); ?></h2>
                <p><?php esc_html_e('After installing the plugin, add the phone number field from the Advanced tab in Gravity Forms. Then, click on the Smart Phone Field tab in the field settings panel to access and configure its options for your form.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <p><?php esc_html_e('Now, configure it according to your requirements, as shown in the image on the right.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <ol>
                    <li>
                        <?php esc_html_e('After clicking "Enable Smart Phone Field Options," a configuration selection will appear. Choose Global to use the default settings, or select Custom to personalize specific options for this field.', 'smart-phone-field-for-gravity-forms'); ?>
                    </li>
                </ol>
            </div>
            <div class="col_image">
                <img src="<?php echo esc_url(GF_SMART_PHONE_FIELD_URL . 'admin/images/pro_options.webp'); ?>">
            </div>
        </section>
        <section class="section_full background_pro">
            <div class="col_description">
                <h2><?php esc_html_e('Basic Usage of Global Settings (Pro)', 'smart-phone-field-for-gravity-forms'); ?></h2>
                <p><?php esc_html_e('After installing the plugin, navigate to the Gravity Forms Settings menu. You\'ll find a Smart Phone Field section there, where you can access and manage all related options.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <p><?php esc_html_e('Now, configure it according to your requirements, as shown in the image on the right.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <ol>
                    <li>
                        <?php esc_html_e('You can set the default country using the dropdown or enable GeoIP detection. Options such as showing flags, country dropdown, phone number validation, number formatting, validation messages, and placeholder visibility can all be customized or overridden from the Phone Field tab.', 'smart-phone-field-for-gravity-forms'); ?>
                    </li>
                    <li><?php esc_html_e('Other options such as Strict Mode, Country Search, RTL support, Language selection, IP merge tag, and the ipinfo.io token are global settings. These features are not available in the Phone Field tab but can be easily modified using hooks.', 'smart-phone-field-for-gravity-forms'); ?></li>
                </ol>
                <p><a class="pcafe_btn" target="_blank" href="<?php echo esc_url(admin_url('admin.php?page=gf_settings&subview=smart-phone-field-for-gravity-forms')); ?>">Go to Global Settings</a></p>
            </div>
            <div class="col_image">
                <img src="<?php echo esc_url(GF_SMART_PHONE_FIELD_URL . 'admin/images/settings.webp'); ?>">
            </div>
        </section>
    </div>


</div>