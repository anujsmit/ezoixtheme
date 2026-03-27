jQuery(document).ready(function ($) {
    var mediaUploader;

    $('#ezoix_upload_banner_btn').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Choose Banner Image',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#ezoix_banner_image_url').val(attachment.url);

            var preview = $('#ezoix_banner_preview');
            preview.attr('src', attachment.url).show();
        });

        mediaUploader.open();
    });
});