<?php

/**
 * Description of BPMediaFeed
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMediaFeed {

    public $feed_url = 'http://rtcamp.com/tag/buddypress/feed/';

    public function __construct($feed_url = '') {
        if ($feed_url)
            $this->feed_url = $feed_url;
    }

    /**
     * Get BuddyPress Media Feed from rtCamp.com
     */
    public function fetch_feed() {
        global $bp_media;
        // Get RSS Feed(s)
        require_once( ABSPATH . WPINC . '/feed.php' );
        $maxitems = 0;
        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed($this->feed_url);
        if (!is_wp_error($rss)) { // Checks that the object is created correctly
            // Figure out how many total items there are, but limit it to 5.
            $maxitems = $rss->get_item_quantity(5);

            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items(0, $maxitems);
        }
        ?>
        <ul><?php
        if ($maxitems == 0) {
            echo '<li>' . __('No items', BP_MEDIA_TXT_DOMAIN) . '.</li>';
        } else {
            // Loop through each feed item and display each item as a hyperlink.
            foreach ($rss_items as $item) {
                ?>
                    <li>
                        <a href='<?php echo $item->get_permalink(); ?>' title='<?php echo __('Posted ', BP_MEDIA_TXT_DOMAIN) . $item->get_date('j F Y | g:i a'); ?>'><?php echo $item->get_title(); ?></a>
                    </li><?php
            }
        }
        ?>
        </ul><?php
        if (DOING_AJAX) {
            die();
        }
    }

}
?>
