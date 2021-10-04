<?php
/**
 * Show comment medias.
 *
 * @package rtMedia
 */

if ( is_array( $tabs ) && count( $tabs ) ) {
	if ( isset( $attr['upload_parent_id_type'] ) && isset( $attr['upload_parent_id'] ) ) {
		$main_id = '-' . $attr['upload_parent_id_type'] . '-' . $attr['upload_parent_id'];
	}
	?>
	<div class="rtmedia-container rtmedia-uploader-div">
	<?php
	$bp_template = get_option( '_bp_theme_package_id' );
	if ( 'legacy' !== $bp_template ) {
		?>
		<div class="rtmedia-container rtmedia-uploader-div">
		<?php } ?>
			<?php
			if ( isset( $attr['rtmedia_simple_file_upload'] ) && true === $attr['rtmedia_simple_file_upload'] ) {
				if ( isset( $attr['rtmedia_upload_without_form'] ) && true === $attr['rtmedia_upload_without_form'] ) {
					?>
					<div class="rtmedia-simple-file-upload">
					<?php
				} else {
					?>
					<form id="rtmedia-uploader-form<?php echo esc_attr( $main_id ); ?>" method="post" action="upload" enctype="multipart/form-data">
						<div class="rtmedia-simple-file-upload">
						<?php
				}

				RTMediaUploadView::upload_nonce_generator( true );

				if ( ! empty( $attr ) ) {

					foreach ( $attr as $key => $value ) {

						if ( 'context' === $key ) {
							echo '<input type="hidden" name="context" value="' . esc_attr( $value ) . '" />';
						} elseif ( 'context_id' === $key ) {
							echo '<input type="hidden" name="context_id" value="' . esc_attr( $value ) . '" />';
						} elseif ( 'privacy' === $key ) {
							echo '<input type="hidden" name="privacy" value="' . esc_attr( $value ) . '" />';
						} elseif ( 'album_id' === $key ) {
							echo '<input type="hidden" name="album_id" value="' . esc_attr( $value ) . '" />';
						} elseif ( 'title' === $key ) {
							echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-title"><input type="text" name="title" /></p>';
						} elseif ( 'description' === $key ) {
							echo '<p class="rtmedia-file-upload-p rtmedia-file-upload-desc"><textarea name="description"></textarea></p>';
						} else {
							echo "<input type='hidden' id='rt_upload_hf_" . esc_attr( sanitize_key( $key ) ) . esc_attr( $main_id ) . "' value='" . esc_attr( $value ) . "' name ='" . esc_attr( $key ) . "' />";
						}
					}
				}

				if ( isset( $attr['rtmedia_upload_allow_multiple'] ) && true === $attr['rtmedia_upload_allow_multiple'] ) {
					?>
					<div class="rtm-file-input-container"><p class="rtmedia-file-upload-p"><input type="file" name="rtmedia_file_multiple[]" multiple="true" class="rtm-simple-file-input" id="rtmedia_simple_file_input<?php echo esc_attr( $main_id ); ?>" /></p></div>
					<?php
				} else {
					?>
					<div class="rtm-file-input-container"><p class="rtmedia-file-upload-p"><input type="file" name="rtmedia_file" class="rtm-simple-file-input" id="rtmedia_simple_file_input<?php echo esc_attr( $main_id ); ?>" /></p></div>
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
					<div id="rtmedia-uploader-form<?php echo esc_attr( $main_id ); ?>">
						<?php do_action( 'rtmedia_before_uploader' ); ?>

						<div class="rtm-tab-content-wrapper">
							<div id="rtm-<?php echo esc_attr( $mode ); ?>-ui<?php echo esc_attr( $main_id ); ?>" class="rtm-tab-content">
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
									if ( 'context_id' === $key ) {
										echo '<input type="hidden" name="context_id" value="' . esc_attr( $value ) . '" />';
									}
									if ( 'album_id' === $key ) {
										echo '<input type="hidden" name="album_id" value="' . esc_attr( $value ) . '" />';
									}
								}
							}
							?>
							<input type="submit" id='rtMedia-start-upload<?php echo esc_attr( $main_id ); ?>' name="rtmedia-upload" value="<?php echo esc_attr( RTMEDIA_UPLOAD_LABEL ); ?>" />
						</div>
					</div>
				<?php
			}
			?>
		</div>
	<?php
	if ( 'legacy' !== $bp_template ) {
		?>
		</div>
	<?php } ?>

	<?php
}
