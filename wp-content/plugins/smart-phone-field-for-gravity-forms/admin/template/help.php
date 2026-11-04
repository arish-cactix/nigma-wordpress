<?php

if (! defined('ABSPATH')) exit;
$faqs = [
    [
        'question' => __('Is this plugin captcha country code?', 'smart-phone-field-for-gravity-forms'),
        'answer' => __('Yes, It captures the country code with a phone number and is supported with multistep.', 'smart-phone-field-for-gravity-forms'),
    ],
    [
        'question' => __('Is it working with the auto-detect country flag?', 'smart-phone-field-for-gravity-forms'),
        'answer' => __('Yes, It will work in all popular browsers.', 'smart-phone-field-for-gravity-forms'),
    ],
    [
        'question' => __('Is it working with popup with elementor popup builder?', 'smart-phone-field-for-gravity-forms'),
        'answer' => __('Yes, it does support.', 'smart-phone-field-for-gravity-forms'),
    ],
    [
        'question' => __('Is there any way to disable letters on phone field?', 'smart-phone-field-for-gravity-forms'),
        'answer' => __('Yes, You can easily prevent letters from being entered in the smartphone field by enabling Strict Mode. This setting is available in the Global Settings.', 'smart-phone-field-for-gravity-forms'),
    ],
    [
        'question' => __('Can the dropdown list of countries be translated?', 'smart-phone-field-for-gravity-forms'),
        'answer' => __('Yes, Go to the Global Settings page, where you’ll find a language selection dropdown. Over 30 languages are available—simply choose your preferred one.', 'smart-phone-field-for-gravity-forms'),
    ]
];

?>


<div id="help" class="help_introduction tab_item">
    <div class="content_heading">
        <h2><?php esc_html_e('Frequently Asked Questions', 'smart-phone-field-for-gravity-forms'); ?></h2>
    </div>

    <section class="section_faq">
        <?php foreach ($faqs as $key => $faq) : ?>
            <div class="faq_item">
                <input type="checkbox" name="accordion-1" id="faq<?php echo esc_attr($key); ?>">
                <label for="faq<?php echo esc_attr($key); ?>" class="faq__header">
                    <?php echo esc_html($faq['question']); ?>
                    <i class="dashicons dashicons-arrow-down-alt2"></i>
                </label>
                <div class="faq__body">
                    <p><?php echo esc_html($faq['answer']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="content_heading">
        <h2><?php esc_html_e('Need Help?', 'smart-phone-field-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('If you have any questions or need help, please feel free to contact us.', 'smart-phone-field-for-gravity-forms'); ?></p>
    </div>

    <div class="help_docs">
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GF_SMART_PHONE_FIELD_URL . 'admin/images/docs.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Documentation', 'smart-phone-field-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('Check out our detailed online documentation and video tutorials to find out more about what you can do.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://pluginscafe.com/docs/smart-phone-field-for-gravity-forms-pro/" class="pcafe_btn"><?php esc_html_e('Documentation', 'smart-phone-field-for-gravity-forms'); ?></a>
            </div>
        </section>
        <section class="help_box section_half">
            <div class="help_box__img">
                <img src="<?php echo esc_url(GF_SMART_PHONE_FIELD_URL . 'admin/images/service247.svg'); ?>">
            </div>
            <div class="help_box__content">
                <h3><?php esc_html_e('Support', 'smart-phone-field-for-gravity-forms'); ?></h3>
                <p><?php esc_html_e('We have dedicated support team to provide you fast, friendly & top-notch customer support.', 'smart-phone-field-for-gravity-forms'); ?></p>
                <a target="_blank" href="https://wordpress.org/support/plugin/smart-phone-field-for-gravity-forms/" class="pcafe_btn"><?php esc_html_e('Get Support', 'smart-phone-field-for-gravity-forms'); ?></a>
            </div>
        </section>
    </div>
</div>