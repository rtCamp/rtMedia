<?php
/**
 * Template for RTMediaAdmin::admin_sidebar().
 *
 * @package rtMedia
 */

?>

<div id="social" class="rtm-social-share">
	<p>
		<a href="http://twitter.com/home/?status=<?php echo esc_attr( $rtmedia_sidebar_message ); ?>"
			class="button twitter" target= "_blank" title="<?php esc_attr_e( 'Post to Twitter Now', 'buddypress-media' ); ?>"
		>
			<?php esc_html_e( 'Post to Twitter', 'buddypress-media' ); ?>
			<span class="dashicons dashicons-twitter"></span>
		</a>
	</p>
	<p>
		<a href="https://www.facebook.com/sharer/sharer.php?u=https://rtmedia.io/" class="button facebook" target="_blank"
			title="<?php esc_attr_e( 'Share on Facebook Now', 'buddypress-media' ); ?>"
		>
			<?php esc_html_e( 'Share on Facebook', 'buddypress-media' ); ?>
			<span class="dashicons dashicons-facebook"></span>
		</a>
	</p>
	<p>
		<a href="https://wordpress.org/support/plugin/buddypress-media/reviews/#new-post" class="button wordpress" target= "_blank"
			title="<?php esc_attr_e( 'Rate rtMedia on Wordpress.org', 'buddypress-media' ); ?>"
		>
			<?php esc_html_e( 'Rate on Wordpress.org', 'buddypress-media' ); ?>
			<span class="dashicons dashicons-wordpress"></span>
		</a>
	</p>
	<p>
		<a href="https://rtmedia.io/feed/" class="button rss" target="_blank"
		   title="<?php esc_attr_e( 'Subscribe to our Feeds', 'buddypress-media' ); ?>"
		>
			<?php esc_html_e( 'Subscribe to our Feeds', 'buddypress-media' ); ?>
			<span class="dashicons dashicons-rss"></span>
		</a>
	</p>
</div>
