jQuery(document).ready(function(){

jQuery('.report-hide').hide();
// REPORT ABUSE STARTS HERE


jQuery('.report-abuse').click(function(){
     jQuery('.report-hide').show();
});

jQuery('.cancel-abuse').click(function(){
    jQuery(this).parent().hide();
});


    jQuery('.rpt-btn').click(function(){
        var url = jQuery('#current-url').val();
        var k = jQuery('#report-option').val();
        var media_id =jQuery('#current-media-id').val();
        jQuery( 'div.report-hide').children('span.ajax-loader' ).show();
        var data = {action: 'media_report_abuse',report_type: k,report_id:media_id,report_url:url};

      jQuery.get(ajaxurl, data, function(response) {
                       jQuery( 'div.report-hide').children('span.ajax-loader' ).hide();
                       jQuery('.report-hide').hide();
                       alert(response);
                    });
});
 // End Report Abuse Here
});