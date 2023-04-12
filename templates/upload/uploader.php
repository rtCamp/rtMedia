<?php
/**
 * Show media uploader.
 *
 * @package rtMedia
 */

if ( ! function_exists( 'rtmedia_uploader_hidden_fields' ) ) {
	/**
	 * Display hidden fields.
	 *
	 * @param string $key   Input key.
	 * @param string $value Input value.
	 *
	 * @since 4.6.11
	 *
	 * @return void
	 */
	function rtmedia_uploader_hidden_fields( $key, $value ) {

		printf( '<input type="hidden" name="%1$s" value="%2$s" />', esc_attr( $key ), esc_attr( $value ) );
	}
}
if ( is_array( $tabs ) && count( $tabs ) ) { ?>

	<div class="rtmedia-container rtmedia-uploader-div">

		<?php
		if ( isset( $attr['rtmedia_simple_file_upload'] ) && true === $attr['rtmedia_simple_file_upload'] ) {

			if ( isset( $attr['rtmedia_upload_without_form'] ) && true === $attr['rtmedia_upload_without_form'] ) {
				?>

				<div class="rtmedia-simple-file-upload">

				<?php } else { ?>

					<form id="rtmedia-uploader-form" method="post" action="upload" enctype="multipart/form-data">

						<div class="rtmedia-simple-file-upload">
						<?php
				}

						RTMediaUploadView::upload_nonce_generator( true );

				if ( ! empty( $attr ) ) {

					foreach ( $attr as $key => $value ) {

						switch ( $key ) {

							case 'context':
							case 'context_id':
							case 'privacy':
							case 'album_id':
								rtmedia_uploader_hidden_fields( $key, $value );

								break;

							case 'title':
								echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-title"><input type="text" name="title" /></p>';

								break;

							case 'description':
								echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-desc"><textarea name="description"></textarea></p>';

								break;

							default:
								printf(
									'<input type="hidden" id="rt_upload_hf_%1$s" value="%2$s" name="%3$s" />',
									esc_attr( sanitize_key( $key ) ),
									esc_attr( $value ),
									esc_attr( $key )
								);
						}
					}
				}

				if ( isset( $attr['rtmedia_upload_allow_multiple'] ) && true === $attr['rtmedia_upload_allow_multiple'] ) {
					?>
					<div class="rtm-file-input-container">
						<p class="rtmedia-file-upload-p">
							<input type="file" name="rtmedia_file_multiple[]" multiple="true" class="rtm-simple-file-input" id="rtmedia_simple_file_input" />
						</p>
					</div>
					<?php
				} else {
					?>
					<div class="rtm-file-input-container">
						<p class="rtmedia-file-upload-p">
							<input type="file" name="rtmedia_file" class="rtm-simple-file-input" id="rtmedia_simple_file_input" />
						</p>
					</div>
					<?php
				}

				do_action( 'rtmedia_add_upload_content' );

				if ( isset( $attr['rtmedia_upload_without_form'] ) && true === $attr['rtmedia_upload_without_form'] ) {
					?>
					</div>
					<?php
				} else {
					?>
						<p><input type="submit" name="rtmedia_simple_file_upload_submit" /></p>
					</div>
			</form>
					<?php
				}
		} else {
			?>
			<div class="rtmedia-uploader no-js">
				<div id="rtmedia-uploader-form">
					<?php do_action( 'rtmedia_before_uploader' ); ?>

					<div class="rtm-tab-content-wrapper">
						<div id="rtm-<?php echo esc_attr( $mode ); ?>-ui" class="rtm-tab-content">
							<?php
							do_action( 'rtmedia_before_' . $mode . '_ui' );
							// it is outputting html and make sure content is escaping proper while setting.
							echo wp_kses( $tabs[ $mode ][ $upload_type ]['content'], RTMedia::expanded_allowed_tags() );
							echo '<input type="hidden" name="mode" value="' . esc_attr( $mode ) . '" />';
							do_action( 'rtmedia_after_' . $mode . '_ui', $attr );
							?>
						</div>
					</div>

					<?php do_action( 'rtmedia_after_uploader' ); ?>

					<?php RTMediaUploadView::upload_nonce_generator( true ); ?>

					<?php
					global $rtmedia_interaction;

					if ( ! empty( $attr ) ) {

						foreach ( $attr as $key => $value ) {

							switch ( $key ) {

								case 'context':
								case 'context_id':
								case 'privacy':
								case 'album_id':
									rtmedia_uploader_hidden_fields( $key, $value );

									break;
							}
						}
					}
					?>

					<input type="submit" id='rtMedia-start-upload' name="rtmedia-upload" value="<?php echo esc_attr( RTMEDIA_UPLOAD_LABEL ); ?>" />

				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
