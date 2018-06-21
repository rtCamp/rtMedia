<?php
/**
 * Description of RTMediaWidget
 *
 * @package    rtMedia
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>, Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */

if ( ! class_exists( 'RTMediaAdminWidget' ) ) {

	/**
	 * Class RTMediaAdminWidget
	 */
	class RTMediaAdminWidget {

		/**
		 * Constructs the RTMediaAdminWidget.
		 *
		 * @global mixed  $rtmedia
		 *
		 * @param  int    $id Id.
		 * @param  string $title Title.
		 * @param  string $content Content.
		 */
		public function __construct( $id = null, $title = null, $content = null ) {
			if ( $id ) {
				?>
			<div class="postbox" id="<?php echo esc_attr( $id ); ?>">
				<?php
				if ( $title ) {
				?>
					<h3 class="hndle"><span><?php esc_html_e( $title, 'buddypress-media' ); ?></span></h3>
				<?php
				}
				?>
				<div class="inside"><?php echo $content; // @codingStandardsIgnoreLine ?></div>
				</div>
				<?php
			} else {
				trigger_error( esc_html__( 'Argument missing. id is required.', 'buddypress-media' ) ); // phpcs:ignore
			}
		}
	}

}
