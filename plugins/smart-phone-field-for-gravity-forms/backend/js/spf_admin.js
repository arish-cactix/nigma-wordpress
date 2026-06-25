jQuery(document).ready(function ($) {
    $(document).bind(
        "gform_load_field_settings",
        function (event, field, form) {
            if ($("#spf_enable_value").is(":checked")) {
                $(".spf_options").show();
            } else {
                $(".spf_options").hide();
            }

            if ($("#spf_auto_ip_value").is(":checked")) {
                $(".spf_default_setting").hide();
            } else {
                $(".spf_default_setting").show();
            }
        }
    );

    $(document).on("change", "#spf_enable_value", function () {
        $(".spf_options").slideToggle();
    });

    $(document).on("change", "#spf_auto_ip_value", function () {
        $(".spf_default_setting").slideToggle();
    });
});
