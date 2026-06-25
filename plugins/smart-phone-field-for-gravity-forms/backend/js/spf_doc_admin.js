jQuery(document).ready(function () {
    jQuery(".tabs-stage div.pc_tab").hide();
    jQuery(".tabs-stage div:first").show();
    jQuery(".tabs-nav li:first").addClass("tab-active");

    // Change tab class and display content
    jQuery(".tabs-nav a").on("click", function (event) {
        event.preventDefault();
        jQuery(".tabs-nav li").removeClass("tab-active");
        jQuery(this).parent().addClass("tab-active");
        jQuery(".tabs-stage div.pc_tab").hide();
        jQuery(jQuery(this).attr("href")).show();
    });
});
