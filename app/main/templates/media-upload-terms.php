<?php
/**
 * Template for policy information - RTMediaUploadTerms::terms_and_service_checkbox_html().
 *
 * @package rtMedia
 */

?>

<div class="rtmedia-upload-terms">

	<input type="checkbox" name="rtmedia_upload_terms_conditions" id="<?php echo esc_attr( $id ); ?>" />

	<label for="<?php echo esc_attr( $id ); ?>">

		<?php echo esc_html( apply_filters( 'rtmedia_upload_terms_service_agree_label', __( 'I agree to', 'buddypress-media' ) ) ); ?>

		<a href='<?php echo esc_url( $general_upload_terms_page_link ); ?>' target='_blank'>
			<?php echo esc_html( apply_filters( 'rtmedia_upload_terms_service_link_label', $general_upload_terms_message ) ); ?>
		</a>

	</label>

</div>
