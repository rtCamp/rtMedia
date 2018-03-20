<?php
/**
 * Functionalities provided in this page will allow users to send media with BuddyPress message.
 *
 * @author malav
 *
 * @package rtMedia
 **/

/**
 *  This function will place uploader in BuddyPress compose message form and handle send reply activity.
 */
function rtm_bp_message_media_add_upload_media_button() {
?>
	<script>
		$(function(){
			$("#send-notice").click(function () {
				if ($(this).is(":checked")) {
					$("#rtm_show_upload_ui").slideUp();
				} else {
					$("#rtm_show_upload_ui").slideDown();
				}
			});
		});
		jQuery(document).ready(function(){
			handler = function(){
				var order = jQuery('#messages_order').val() || 'ASC',
				offset = jQuery('#message-recipients').offset();
				var button = jQuery("input#send_reply_button");
				jQuery(button).addClass('loading');
				jQuery.post( ajaxurl, {
					action: 'messages_send_reply',
					'cookie': bp_get_cookies(),
					'_wpnonce': jQuery("input#send_message_nonce").val(),
					'content': jQuery("#message_content").val(),
					'send_to': jQuery("input#send_to").val(),
					'subject': jQuery("input#subject").val(),
					'thread_id': jQuery("input#thread_id").val(),
					'rtm_bpm_uploaded_media': jQuery("input#rtm_bpm_uploaded_media").val()
				},
				function(response)
				{
					if ( response[0] + response[1] == "-1" ) {
						jQuery('form#send-reply').prepend( response.substr( 2, response.length ) );
					} else {
						jQuery('form#send-reply div#message').remove();
						jQuery("#message_content").val('');
						jQuery("#rtm_bpm_uploaded_media").removeAttr('value');
						if ( 'ASC' == order ) {
							jQuery('form#send-reply').before( response );
						} else {
							jQuery('#message-recipients').after( response );
							jQuery(window).scrollTop(offset.top);
						}
					jQuery(".new-message").hide().slideDown( 200, function() {
						jQuery('.new-message').removeClass('new-message');
					});
					}
					jQuery(button).removeClass('loading');
				});
				return false;
			};
			$( "input#send_reply_button" ).unbind( "click").bind("click", handler);
		});

	</script>
	<span class="primary rtm-media-msg-upload-button rtmedia-upload-media-link" id="rtm_show_upload_ui" title="Upload Media"><i class="dashicons dashicons-upload rtmicon"></i>Upload Media File</span>
	<div id="rtm-media-gallery-uploader" class="rtm-media-gallery-uploader">
		<?php
		rtmedia_uploader(
			[
				'is_up_shortcode' => false,
				'allow_anonymous' => true,
				'privacy_enabled' => false,
			]
		);
			?>
	</div>
	<input type="hidden" id="rtm_bpm_uploaded_media" name="rtm_bpm_uploaded_media" />
	<?php
}

/**
* Add additional parameter media ID in BuddyPress message SEND MESSAGE process. and Insert data into MEDIA_META table.
*
* @param object $message Getting parameter response when sending message.
**/
function rtm_add_message_media_params( $message ) {
	$insert_media_object  = new RTDBModel( 'rtm_media_meta' );
	$message->media_array = filter_input( INPUT_POST, 'rtm_bpm_uploaded_media' );
	$media                = explode( ',', $message->media_array );
	if ( ! empty( $media ) && null !== $media ) {
		foreach ( $media as $media_id ) {
			$insert_media_object->insert(
				[
					'media_id'   => $media_id,
					'meta_key'   => 'rtm-bp-message-media', // phpcs:ignore
					'meta_value' => $message->id, // phpcs:ignore
				]
			);
		}
	}
	?>
	<script>
		jQuery("#msg-success-bp-msg-media").hide();
		jQuery(".rtm-media-msg-upload-button").attr("id", "rtm_show_upload_ui");
		jQuery(".rtm-media-msg-upload-button").html("");
		jQuery(".rtm-media-msg-upload-button").html("<i class='dashicons dashicons-upload rtmicon'></i>Upload Media File");
	</script>
	<?php
}

/**
* As a result show attached media with message.
**/
function show_rtm_bp_msg_media() {
	$get_data_object = new RTDBModel( 'rtm_media_meta' );
	$media_result    = $get_data_object->get( [ 'meta_value' => bp_get_the_thread_message_id() ] );  // phpcs:ignore
	$url             = explode( 'messages/', sanitize_text_field( wp_unslash( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ) );
	if ( '0' !== $media_result[0]->media_id ) {
	?>
	<ul class="rtmedia-list-media rtm-gallery-list clearfix" style = "margin-top: 10px;">
	<?php
	foreach ( $media_result as $media_result_array_value ) {
		$media     = rtmedia_image( 'rt_media_thumbnail', $media_result_array_value->media_id, false );
		$media_url = $url[0] . '/media/' . $media_result_array_value->media_id . '/';
		?>

			<li class="rtmedia-list-item" style="display:inline; float: left;" id="<?php echo $media_result_array_value->media_id; // @codingStandardsIgnoreLine?>">
				<a href="<?php echo esc_attr( $media_url ); ?>" class="<?php echo esc_attr( apply_filters( 'rtmedia_gallery_list_item_a_class', 'rtmedia-list-item-a' ) ); ?>">
					<div class="rtmedia-item-thumbnail">
						<img src="<?php echo esc_attr( $media ); ?>" alt="<?php echo esc_attr( apply_filters( 'rtmc_change_alt_text', $alt_text, $rtmedia_media ) ); ?>">
					</div>
				</a>
			</li>

		<?php } ?>
	</ul>
	<?php
	}
}

// Adding rtMedia uploader in BuddyPress compose message form.
add_action( 'bp_after_messages_compose_content', 'rtm_bp_message_media_add_upload_media_button' );
// Adding rtMedia uploader in BuddyPress Send Reply form.
add_action( 'bp_after_message_reply_box', 'rtm_bp_message_media_add_upload_media_button' );
// Handling BuddyPress send message action by adding MEDIA ID.
add_action( 'messages_message_sent', 'rtm_add_message_media_params' );
// Showing media below BuddyPress message.
add_action( 'bp_after_message_content', 'show_rtm_bp_msg_media' );


//rtm_bp_message_media_add_button function will add Media attachment button to both Compose message tab and Send a reply in BuddyPress
function rtm_bp_message_media_add_button(){
	?>
	<label for="rtm_media_message_content"><?php _e( 'Attach Media ( Optional )', 'buddypress' ); ?></label>
	<input type="file" name="rtm_media_message_content" id="rtm_media_message_content" />

	<?php
}

//Adding Browse button under message in Compose tab
add_action( 'bp_after_messages_compose_content', 'rtm_bp_message_media_add_button' );

//Adding Browse button under message in Send reply tab
add_action( 'bp_after_message_thread_reply', 'rtm_bp_message_media_add_button' );