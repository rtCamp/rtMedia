/**
 * This file is used to handle rtMedia Gallery shortcode
 */
jQuery(document).ready(function() {
    // show notice: "rtMedia Gallery shortcode can be used only once."
    wp.data.dispatch( 'core/notices' ).createNotice(
        'warning',
        rtmedia_gallery_shortcode.notice_message,
        {
            isDismissible: true
        }
    );
});
