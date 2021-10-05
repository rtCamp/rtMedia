<?php
/**
 * Media single editing template.
 *
 * @package rtMedia
 */

?>
<div class="rtmedia-container rtmedia-single-container rtmedia-media-edit">
	<?php
	if ( have_rtmedia() ) :
		rtmedia();
		if ( rtmedia_edit_allowed() ) {
			global $rtmedia_media;
			// Added to get nonce of current media.
			$media_id_nonce = wp_create_nonce( "image_editor-$rtmedia_media->media_id" );
			?>
			<div class="rtmedia-single-edit-title-container">
				<h2 class="rtmedia-title"><?php esc_html_e( 'Edit Media', 'buddypress-media' ); ?></h2>
			</div>
			<form method="post" action="" name="rtmedia_media_single_edit" id="rtmedia_media_single_edit" class="rtm-form">
				<div class="rtmedia-editor-main">
					<ul class="rtm-tabs clearfix">
						<li class="active">
							<a href="#panel1">
								<i class='dashicons dashicons-list-view'></i>
								<?php esc_html_e( 'Details', 'buddypress-media' ); ?>
							</a>
						</li>
						<!-- use this hook to add title of a new tab-->
						<?php do_action( 'rtmedia_add_edit_tab_title', rtmedia_type() ); ?>
					</ul>
					<div class="rtm-tabs-content">
						<div class="content" id="panel1">
							<!-- First tab i.e Details tab. Active by default-->
							<div class="rtmedia-edit-title rtm-field-wrap">
								<label><?php esc_html_e( 'Title : ', 'buddypress-media' ); ?></label>
								<?php rtmedia_title_input(); ?>
							</div>
							<!--This function shows the privacy dropdown-->
							<?php echo wp_kses( rtmedia_edit_media_privacy_ui(), RTMedia::expanded_allowed_tags() ); ?>
							<div class="rtmedia-editor-description rtm-field-wrap">
								<label><?php esc_html_e( 'Description: ', 'buddypress-media' ); ?></label>
								<?php
								$editor = false;
								rtmedia_description_input( $editor, true );

								RTMediaMedia::media_nonce_generator( rtmedia_id() );
								?>
							</div>
							<!-- Use this hook to add new fields to the edit form-->
							<?php do_action( 'rtmedia_add_edit_fields', rtmedia_type() ); ?>
						</div>
						<!-- use this hook to add content of a new tab-->
						<?php do_action( 'rtmedia_add_edit_tab_content', rtmedia_type() ); ?>
					</div>
					<div class="rtmedia-editor-buttons">
						<input type="hidden" id="rtmedia-editor-nonce" name="rtmedia-editor-nonce" value="<?php echo esc_attr( $media_id_nonce ); ?>" />
						<input type="hidden" id="rtmedia-editor-media-id" name="rtmedia-editor-media-id" value="<?php echo esc_attr( $rtmedia_media->media_id ); ?>" />
						<input type="submit" class="button rtm-button rtm-button-save" value="<?php esc_attr_e( 'Save', 'buddypress-media' ); ?>"/>
						<a class="button rtm-button rtm-button-back" href="<?php rtmedia_permalink(); ?>"><?php esc_html_e( 'Back', 'buddypress-media' ); ?></a>
					</div>
				</div>
			</form>
			<?php
		} else {
			?>
			<p><?php esc_html_e( 'Sorry !! You do not have rights to edit this media', 'buddypress-media' ); ?></p>
			<?php
		} else :
			?>
		<p class="rtmedia-no-media-found">
			<?php
			echo esc_html( apply_filters( 'rtmedia_no_media_found_message_filter', __( 'Sorry !! There\'s no media found for the request !!', 'buddypress-media' ) ) );
			?>
		</p>
			<?php
	endif; // End if.
		?>
</div>
