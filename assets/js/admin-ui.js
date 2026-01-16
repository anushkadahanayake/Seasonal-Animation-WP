jQuery(document).ready(function ($) {
    // Check local storage for active tab
    var activeTab = localStorage.getItem('seasonal_active_tab');
    if (activeTab && $(activeTab).length) {
        switchTab(activeTab);
    } else {
        // Default to first tab
        $('.seasonal-tab-link').first().trigger('click');
    }

    // Tab Click Handler
    $('.seasonal-tab-link').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        switchTab(target);
        localStorage.setItem('seasonal_active_tab', target);
    });

    function switchTab(targetId) {
        // Remove active class from all links
        $('.seasonal-tab-link').removeClass('active');
        // Adds active to the link that points to this target
        $('.seasonal-tab-link[href="' + targetId + '"]').addClass('active');

        // Hide all contents
        $('.seasonal-tab-content').removeClass('active').hide();
        // Show target content
        $(targetId).addClass('active').show();
    }
});
