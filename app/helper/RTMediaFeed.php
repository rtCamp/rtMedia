<?php
/**
 * Media feed.
 *
 * @package    rtMedia
 */

/**
 * Class for rtMedia feed operations.
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaFeed {

	/**
	 * Feed url.
	 *
	 * @var string
	 */
	public $feed_url = '';

	/**
	 * RTMediaFeed Constructor
	 *
	 * @access public
	 *
	 * @param  string $feed_url Feed url.
	 */
	public function __construct( $feed_url = '' ) {
		if ( $feed_url ) {
			$this->feed_url = $feed_url;
		}
	}

	/**
	 * Get BuddyPress Media Feed from rtmedia.io
	 */
	public function fetch_feed() {
		// Get RSS Feed(s).
		require_once ABSPATH . WPINC . '/feed.php';
		$maxitems  = 0;
		$rss_items = array();
		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( $this->feed_url );
		if ( ! is_wp_error( $rss ) ) { // Checks that the object is created correctly
			// Figure out how many total items there are, but limit it to 3.
			$maxitems = $rss->get_item_quantity( 3 );
			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );
		}
		?>
		<ul>
		<?php
		if ( 0 === $maxitems ) {
			echo '<li>' . esc_html__( 'No items', 'buddypress-media' ) . '.</li>';
		} else {
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) {
				?>
				<li>
				<a href='<?php echo esc_url( $item->get_permalink() ); ?>?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media'
					title='<?php echo esc_attr__( 'Posted ', 'buddypress-media' ) . esc_attr( $item->get_date( 'j F Y | g:i a' ) ); ?>'><?php echo esc_html( $item->get_title() ); ?></a>
				</li>
				<?php
			}
		}
		?>
		</ul>
		<?php
		if ( DOING_AJAX ) {
			wp_die();
		}
	}
}
