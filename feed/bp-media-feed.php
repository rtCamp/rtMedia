<?php
/**
 * RSS2 Feed Template for displaying the most recent sitewide links.
 *
 * @package BuddyPress-Links
 */

header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
header('Status: 200 OK');
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('bp_directory_media_feed'); ?>
>

<channel>
	<title><?php echo bp_site_name() ?> - <?php _e( 'Most Recent Media', 'buddypress-media' ) ?></title>
	<link><?php echo site_url(); ?></link>
	<description><?php _e( 'Most Recent Media Feed', 'buddypress-media' ) ?></description>
        <pubDate><?php echo date('d-m-Y') ?></pubDate>
	<generator>http://buddypress.org/?v=<?php echo BP_VERSION ?></generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('bp_directory_media_feed_head'); ?>
	
	<?php if ( bp_has_media( 'type=recent&max=5' ) ) : ?>
		<?php while ( bp_pictures() ) : bp_the_picture(); ?>
			<item>
				
				<title><?php bp_media_feed_item_title() ?></title>
				<link><?php bp_media_feed_item_link() ?></link>
				<description><?php bp_media_feed_item_description() ?></description>
                                <image>
                                    <url>bp_picture_view_link().jpg</url>
                                    <title>ashish</title>
                                    <link>bp_picture_view_link()</link>
                                </image>
			<?php do_action('bp_directory_media_feed_item'); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss>