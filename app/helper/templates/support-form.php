<?php
/**
 * RTMediaSupport::get_form().
 *
 * @package rtMedia
 */

?>

<h3 class="rtm-option-title"><?php echo esc_html( $meta_title ); ?></h3>
<div id="support-form" class="bp-media-form rtm-support-form rtm-option-wrapper">

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label" for="name">
			<?php esc_html_e( 'Name', 'buddypress-media' ); ?>
		</label>
		<input class="bp-media-input" id="name" type="text" name="name" value="" required/>
		<span class="rtm-tooltip">
			<i class="dashicons dashicons-info"></i>
			<span class="rtm-tip">
				<?php esc_html_e( 'Use actual user name which used during purchased.', 'buddypress-media' ); ?>
			</span>
		</span>
	</div>

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label" for="email">
			<?php esc_html_e( 'Email', 'buddypress-media' ); ?>
		</label>
		<input id="email" class="bp-media-input" type="text" name="email" value="" required/>
		<span class="rtm-tooltip">
			<i class="dashicons dashicons-info"></i>
			<span class="rtm-tip">
				<?php esc_html_e( 'Use email id which used during purchased', 'buddypress-media' ); ?>
			</span>
		</span>
	</div>

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label" for="website">
			<?php esc_html_e( 'Website', 'buddypress-media' ); ?>
		</label>
		<input id="website" class="bp-media-input" type="text" name="website"
			value="<?php echo esc_url( isset( $website ) ? $website : get_bloginfo( 'url' ) ); ?>"
			required />
	</div>

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label" for="subject">
			<?php esc_html_e( 'Subject', 'buddypress-media' ); ?>
		</label>
		<input id="subject" class="bp-media-input" type="text" name="subject"
			value="<?php echo esc_attr( isset( $subject ) ? esc_attr( $subject ) : '' ); ?>"
			required />
	</div>

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label"
			for="details"><?php esc_html_e( 'Details', 'buddypress-media' ); ?></label>
		<textarea id="details" class="bp-media-textarea" name="details"
			required><?php echo esc_html( isset( $details ) ? esc_textarea( $details ) : '' ); ?></textarea>

		<input type="hidden" name="request_type" value="<?php echo esc_attr( $form ); ?>"/>
		<input type="hidden" name="request_id"
			value="<?php echo esc_attr( wp_create_nonce( date( 'YmdHis' ) ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?>" />
		<input type="hidden" name="server_address" value="<?php echo esc_attr( $server_addr ); ?>"/>
		<input type="hidden" name="ip_address" value="<?php echo esc_attr( $remote_addr ); ?>"/>
		<input type="hidden" name="server_type" value="<?php echo esc_attr( $server_software ); ?>"/>
		<input type="hidden" name="user_agent" value="<?php echo esc_attr( $http_user_agent ); ?>"/>

		<?php
		// Adding nonce for file upload.
		$nonce = wp_create_nonce( 'rtmedia-admin-upload' );
		?>
		<input type="hidden" id="rtmedia_admin_upload_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
		<input type="hidden" name="debuglog_temp_path" id="debuglog_temp_path" />
	</div>

	<div class="rtm-form-filed clearfix">
		<label class="bp-media-label" for="subject">
			<?php esc_html_e( 'Attachment', 'buddypress-media' ); ?>
		</label>
		<input id="debuglog" class="bp-media-input" type="file" name="debuglog" />
		<span class="rtm-tooltip">
			<i class="dashicons dashicons-info"></i>
			<span class="rtm-tip">
				<?php esc_html_e( 'Allowed file types are : images, documents and texts.', 'buddypress-media' ); ?>
			</span>
		</span>
	</div>
</div><!-- .submit-bug-box -->

<div class="rtm-form-filed rtm-button-wrapper clearfix">
	<?php wp_nonce_field( 'rtmedia-support-request', 'support_wpnonce' ); ?>
	<?php submit_button( 'Submit', 'primary', 'rtmedia-submit-request', false ); ?>
	<?php submit_button( 'Cancel', 'secondary', 'cancel-request', false ); ?>
</div>
