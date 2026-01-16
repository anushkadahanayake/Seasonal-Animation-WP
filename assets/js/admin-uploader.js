jQuery(document).ready(function ($) {
    var mediaUploader;

    // Generic function to attach uploader to a button
    function attachUploader(buttonClass, inputClass) {
        $(document).on('click', buttonClass, function (e) {
            e.preventDefault();

            var button = $(this);
            var inputField = button.siblings(inputClass); // Find the related text input

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Extend the wp.media object
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Select Seasonal Image',
                button: {
                    text: 'Use this Image'
                },
                multiple: false
            });

            // When a file is selected, grab the URL and set it as the text field's value
            mediaUploader.on('select', function () {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                inputField.val(attachment.url);
            });

            // Open the uploader dialog
            mediaUploader.open();
        });
    }

    // Attach to specific buttons (we'll add class 'seasonal-upload-btn' to buttons)
    // We need to re-assign the input field targeting because 'mediaUploader' is global and might get confused if we share it blindly?
    // Actually, 'wp.media' is usually singleton-ish but we can just recreate it or update the 'select' callback if we want to support multiple distinct inputs on one page safely.
    // Let's use a simpler per-click handler to be safe with multiple fields.

    $('.seasonal-upload-btn').click(function (e) {
        e.preventDefault();

        var button = $(this);
        var inputField = button.prev('input[type="text"]'); // Assuming input is right before button

        var customUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use Image'
            },
            multiple: false
        });

        customUploader.on('select', function () {
            var attachment = customUploader.state().get('selection').first().toJSON();
            inputField.val(attachment.url);
        });

        customUploader.open();
    });
});
