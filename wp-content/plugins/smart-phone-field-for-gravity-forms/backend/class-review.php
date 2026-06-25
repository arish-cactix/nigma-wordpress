<?php

if (!defined('ABSPATH')) {
    exit;
}

class GF_smart_phone_field_review {

    public function __construct() {
        add_action('admin_notices', [$this, 'review_request']);
        add_action('wp_ajax_plc_review_dismiss', [$this, 'review_dismiss']);
    }

    public function review_request() {
        if (! is_super_admin()) {
            return;
        }

        $time = time();
        $load = false;

        $review = get_option('pcafe_spf_review_status');

        if (! $review) {
            $review_time = strtotime("+15 days", time());
            update_option('pcafe_spf_review_status', $review_time);
        } else {
            if (! empty($review) && $time > $review) {
                $load = true;
            }
        }
        if (! $load) {
            return;
        }

        $this->review();
    }

    public function review() {
        $current_user = wp_get_current_user();
?>
        <div class="notice notice-info is-dismissible pcafe_spf_review_notice">
            <p><?php echo sprintf(__('Hey %1$s 👋, I noticed you are using <strong>%2$s</strong> for a few days - that\'s Awesome!  If you feel <strong>%2$s</strong> is helping your business to grow in any way, Could you please do us a BIG favor and give it a 5-star rating on WordPress to boost our motivation?', 'text-domin'), $current_user->display_name, 'Smart Phone Field For Gravity Forms'); ?></p>

            <ul style="margin-bottom: 5px">
                <li style="display: inline-block">
                    <a style="padding: 5px 5px 5px 0; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/smart-phone-field-for-gravity-forms/reviews/?filter=5#new-post') ?>">
                        <span class="dashicons dashicons-external"></span><?php esc_html_e(' Ok, you deserve it!', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="already_done" data-status="already">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('I already did', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="later" data-status="later">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Maybe Later', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://pluginscafe.com/support/') ?>">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e('I need help', 'gravityforms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="never" data-status="never">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Never show again', 'gravityforms') ?>
                    </a>
                </li>
            </ul>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.already_done, .later, .never, .notice-dismiss', function(event) {
                    event.preventDefault();
                    var $this = $(this);
                    var status = $this.attr('data-status');
                    data = {
                        action: 'plc_review_dismiss',
                        status: status,
                    };
                    $.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: data,
                        success: function(data) {
                            $('.pcafe_spf_review_notice').remove();
                        },
                        error: function(data) {}
                    });
                });
            });
        </script>
<?php
    }

    public function review_dismiss() {
        $status = $_POST['status'];

        if ($status == 'already' || $status == 'never') {
            $next_try     = strtotime("+30 days", time());
            update_option('pcafe_spf_review_status', $next_try);
        } else if ($status == 'later') {
            $next_try     = strtotime("+10 days", time());
            update_option('pcafe_spf_review_status', $next_try);
        }
        wp_die();
    }
}

new GF_smart_phone_field_review();
