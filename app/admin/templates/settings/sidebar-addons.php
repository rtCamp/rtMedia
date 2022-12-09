<?php
/**
 * Template for RTMediaAdmin::admin_sidebar().
 *
 * @package rtMedia
 */

?>

<div id="social" class="rtm-social-share">
	<a href="http://twitter.com/home/?status=" class="button twitter" target= "_blank" title="' . <?php echo esc_attr__( 'Post to Twitter Now', 'buddypress-media' ); ?> . '">
		<span class="dashicons dashicons-twitter"></span>
			<span class="icon-message"> <?php echo esc_html__( 'Post to Twitter', 'buddypress-media' ); ?> </span>
	</a>

	<a href="https://www.facebook.com/sharer/sharer.php?u=https://rtmedia.io/" class="button facebook" target="_blank" title="' . <?php echo esc_attr__( 'Share on Facebook Now', 'buddypress-media' ); ?> . '">
		<span class="dashicons dashicons-facebook"></span>
			<span class="icon-message"> <?php echo esc_html__( 'Post to Facebook', 'buddypress-media' ); ?></span>
	</a>

	<a href="https://wordpress.org/support/plugin/buddypress-media/reviews/#new-post" class="button wordpress" target= "_blank" title="' . <?php echo esc_attr__( 'Rate rtMedia on Wordpress.org', 'buddypress-media' ); ?> . '">
		<span class="dashicons dashicons-wordpress"></span>
			<span class="icon-message"> <?php echo esc_html__( 'Rate us on Wordpress.org', 'buddypress-media' ); ?> </span>			
	</a>

	<a href="' . sprintf( '%s', 'https://rtmedia.io/feed/' ) . '" class="button rss" target="_blank" title="' . <?php echo esc_attr__( 'Subscribe to our Feeds', 'buddypress-media' ); ?> . '">
		<span class="dashicons dashicons-rss"></span>
			<span class="icon-message"> <?php echo esc_html__( 'Subscribe to our Feeds', 'buddypress-media' ); ?> </span>
	</a>

</div>
