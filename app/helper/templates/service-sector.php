<?php
/**
 * RTMediaSupport::service_selector().
 *
 * @package rtMedia
 */

?>

<div>
	<form name="rtmedia_service_select_form" method="post">
		<p>
			<label class="bp-media-label" for="select_support">
				<?php esc_html_e( 'Service', 'buddypress-media' ); ?>:
			</label>

			<select name="rtmedia_service_select">
				<option value="premium_support" <?php selected( $form, 'premium_support' ); ?>>
					<?php esc_html_e( 'Premium Support', 'buddypress-media' ); ?>
				</option>

				<option value="bug_report" <?php selected( $form, 'bug_report' ); ?>>
					<?php esc_html_e( 'Bug Report', 'buddypress-media' ); ?>
				</option>

				<option value="new_feature" <?php selected( $form, 'new_feature' ); ?>>
					<?php esc_html_e( 'New Feature', 'buddypress-media' ); ?>
				</option>
			</select>

			<input name="support_submit" value="<?php esc_attr_e( 'Submit', 'buddypress-media' ); ?>" type="submit" class="button"/>

		</p>
	</form>
</div>
