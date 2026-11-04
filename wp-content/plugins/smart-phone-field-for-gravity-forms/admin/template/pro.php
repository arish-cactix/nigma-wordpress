<?php
if (! defined('ABSPATH')) exit;

$features = [
    [
        'feature'   => __('Live validation', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Automatic country select with ip address (GeoIP)', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Default country selection', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Exclude/Include countries', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Multi step support', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Multiple phone field', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Three flag option', 'smart-phone-field-for-gravity-forms'),
        'pro'       => 0
    ],
    [
        'feature'   => __('Add country code with notification/entries', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Prevent form submission on invalid phone number', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Phone number format with typing', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Get city, zip code, country and more based on IP address via merge tag', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('RTL support', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('30+ language support', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Phone number format in 4 different types. Ex: E.164 and more.', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Add country code automatically', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ],
    [
        'feature'   => __('Get country code, name, dial code separately in text field', 'smart-phone-field-for-gravity-forms'),
        'pro'       => true
    ]
];

?>
<div id="pro" class="pro_introduction tab_item">

    <?php if (spffgfp_fs()->is_plan('pro', true)) : ?>
        <div class="content_heading pro_using">
            <p style="color: darkorange"><?php esc_html_e('Thanks for using Smart Phone For Gravity Forms Pro.', 'smart-phone-field-for-gravity-forms'); ?></p>
        </div>
    <?php endif; ?>

    <div class="content_heading">
        <h2><?php esc_html_e('Unlock the full power of Smart Phone Field For Gravity Forms', 'smart-phone-field-for-gravity-forms'); ?></h2>
        <p><?php esc_html_e('The amazing PRO features will make your smart phone field even more efficient.', 'smart-phone-field-for-gravity-forms'); ?></p>
        <?php if (spffgfp_fs()->is_plan('pro', false)) : ?>
            <a href="<?php echo esc_url(spffgfp_fs()->get_upgrade_url()); ?>" class="pcafe_btn">
                <?php esc_html_e('Get PRO Now', 'smart-phone-field-for-gravity-forms'); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="content_heading free_vs_pro">
        <h2>
            <span><?php esc_html_e('Free', 'smart-phone-field-for-gravity-forms'); ?></span>
            <?php esc_html_e('vs', 'smart-phone-field-for-gravity-forms'); ?>
            <span><?php esc_html_e('Pro', 'smart-phone-field-for-gravity-forms'); ?></span>
        </h2>
    </div>

    <div class="features_list">
        <div class="list_header">
            <div class="feature_title"><?php esc_html_e('Feature List', 'smart-phone-field-for-gravity-forms'); ?></div>
            <div class="feature_free"><?php esc_html_e('Free', 'smart-phone-field-for-gravity-forms'); ?></div>
            <div class="feature_pro"><?php esc_html_e('Pro', 'smart-phone-field-for-gravity-forms'); ?></div>
        </div>
        <?php foreach ($features as $feature) : ?>
            <div class="feature">
                <div class="feature_title"><?php echo esc_html($feature['feature']); ?></div>
                <div class="feature_free">
                    <?php if ($feature['pro']) : ?>
                        <i class="dashicons dashicons-no-alt"></i>
                    <?php else : ?>
                        <i class="dashicons dashicons-saved"></i>
                    <?php endif; ?>
                </div>
                <div class="feature_pro">
                    <i class="dashicons dashicons-saved"></i>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (! spffgfp_fs()->is_plan('pro', true)) : ?>
        <div class="pro-cta background_pro">
            <div class="cta-content">
                <h2><?php esc_html_e('Don\'t waste time, get the PRO version now!', 'smart-phone-field-for-gravity-forms'); ?></h2>
                <p><?php esc_html_e('Upgrade to the PRO version of the plugin and unlock all the amazing Range Slider features for
                your website.', 'smart-phone-field-for-gravity-forms'); ?></p>
            </div>
            <div class="cta-btn">
                <a href="<?php echo esc_url(spffgfp_fs()->get_upgrade_url()); ?>" class="pcafe_btn"><?php esc_html_e('Upgrade Now', 'smart-phone-field-for-gravity-forms'); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <div class="pro-cta background_free">
        <div class="cta-content">
            <h2><?php esc_html_e('Want to try live demo, before purchase?', 'smart-phone-field-for-gravity-forms'); ?></h2>
            <p><?php esc_html_e('Try our instant ready-made demo with form submission! If you use an active email address, you\'ll also receive a notification.', 'smart-phone-field-for-gravity-forms'); ?></p>
        </div>
        <div class="cta-btn">
            <a href="https://demo.pluginscafe.com/smart-phone-field-for-gravity-forms/" target="_blank" class="pcafe_btn"><?php esc_html_e('Try Live Demo', 'smart-phone-field-for-gravity-forms'); ?></a>
        </div>
    </div>
</div>