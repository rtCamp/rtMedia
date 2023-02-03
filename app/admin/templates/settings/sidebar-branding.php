<?php
/**
 * Template for RTMediaAdmin::admin_sidebar().
 *
 * @package rtMedia
 */

?>

<form action="http://rtcamp.us1.list-manage1.com/subscribe/post?u=85b65c9c71e2ba3fab8cb1950&amp;id=9e8ded4470" method="post"
	id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate
>

	<div class="mc-field-group">

		<input type="email" value="<?php echo esc_attr( $rtmedia_current_user->user_email ); ?>" name="EMAIL" placeholder="Email" class="required email" id="mce-EMAIL">
		<input style="display:none;" type="checkbox" checked="checked" value="1" name="group[1721][1]" id="mce-group[1721]-1721-0">
		<input type="submit" value="<?php esc_attr_e( 'Subscribe', 'buddypress-media' ); ?>" name="subscribe" id="mc-embedded-subscribe" class="button">

		<div id="mce-responses" class="clear">
			<div class="response" id="mce-error-response" style="display:none"></div>
			<div class="response" id="mce-success-response" style="display:none"></div>
		</div>

	</div>

</form>
