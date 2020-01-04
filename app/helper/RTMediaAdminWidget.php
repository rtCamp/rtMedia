<?php
/**
 * File includes RTMediaWidget class
 *
 * @package    rtMedia
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>, Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */

if ( ! class_exists( 'RTMediaAdminWidget' ) ) {

	/**
	 * Class to show rtMedia widget.
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
			add_action( 'safe_style_css', 'RTMedia::allow_display_in_style' );
			if ( $id ) {
				?>
				<div class="postbox" id="<?php echo esc_attr( $id ); ?>">
					<?php
					if ( $title ) {
						?>
						<h3 class="hndle"><span><?php echo esc_html( $title ); ?></span></h3>
					<?php } ?>
					<div class="inside"><?php echo wp_kses( $content, RTMedia::expanded_allowed_tags() ); ?></div>
				</div>
				<?php
			} else {
				trigger_error( esc_html__( 'Argument missing. id is required.', 'buddypress-media' ) ); // phpcs:ignore
			}
			remove_action( 'safe_style_css', 'RTMedia::allow_display_in_style' );
		}
	}

}
