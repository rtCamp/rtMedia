<?php

/**
 * Description of RTMediaAddon
 *
 * @package    rtMedia
 * @subpackage Admin
 *
 * @author     Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author     Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if ( ! class_exists( 'RTMediaAddon' ) ){

	class RTMediaAddon {

		public $enquiry_link = 'http://rtcamp.com/contact/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media';

		/**
		 * Show coming_soon_div.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function coming_soon_div(){
			return '<div class="coming-soon coming-soon-l"></div> <a class="coming-soon coming-soon-r" href="' . $this->enquiry_link . '" target="_blank"></a>';
		}

		/**
		 * Render addons.
		 *
		 * @access public
		 *
		 * @param  type $page
		 *
		 * @return void
		 */
		public static function render_addons( $page = '' ){
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ){
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

				if ( $section['callback'] ){
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ){
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

		/**
		 * Get addons for Audio/Video  Encoding and Plugins.
		 *
		 * @access public
		 *
		 * @param  void
		 *
		 * @return void
		 */
		public function get_addons(){
			$tabs = array();
			global $rtmedia_admin;
			$tabs[] = array(
				'title' => __( 'Plugins', 'rtmedia' ),
				'name' => __( 'Plugins', 'rtmedia' ),
				'href' => '#rtm-plugins',
				'callback' => array( $this, 'plugins_content' )
			);

			$tabs[] = array(
				'title' => __( 'Audio/Video  Encoding', 'rtmedia' ),
				'name' => __( 'Audio/Video  Encoding', 'rtmedia' ),
				'href' => '#rtm-services',
				'callback' => array( $rtmedia_admin->rtmedia_encoding, 'encoding_service_intro' )
			);

			/*			$tabs[] = array(
							'title' => __('Themes', 'rtmedia'),
							'name' => __('Themes', 'rtmedia'),
							'href' => '#bpm-themes',
							'callback' => array($this, 'themes_content')
						);*/

			?>
			<div id="rtm-addons">
				<div class="horizontal-tabs">
					<dl class='tabs' data-tab>
			<?php
			$i = 1;
			foreach ( $tabs as $tab ) {
				$active_class = '';
				if ( 1 == $i ){
					$active_class = 'active';
				}
				$i ++;
				?>
						<dd class="<?php echo $active_class ?>">
							<a id="tab-<?php echo substr( $tab['href'], 1 ) ?>" title="<?php echo $tab['title'] ?>" href="<?php echo $tab['href'] ?>" class="rtmedia-tab-title <?php echo sanitize_title( $tab['name'] ) ?>"><?php echo $tab['name'] ?></a>
						</dd>
			<?php
			}
			?>
					</dl>

			<?php
			$k = 1;
			$active_class = '';
			echo "<div class='tabs-content'>";
			foreach ( $tabs as $tab ) {
				$active_class = '';
				if ( 1 == $k ){
					$active_class = ' active';
				}
				$k ++;
				if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ){
					$icon = '<i class="' . $tab['icon'] . '"></i>';
				}
				$tab_without_hash = explode( '#', $tab['href'] );
				$tab_without_hash = $tab_without_hash[1];
				echo '<div class="row content' . $active_class . '" id="' . $tab_without_hash . '">';
				echo '<div class="large-12 columns">';
				call_user_func( $tab['callback'] );
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			?>
				</div>
			</div>
		<?php
		}


		/**
		 * Display plugins in Addons Section.
		 *
		 * @access public
		 *
		 * @param  array $args
		 *
		 * @return void
		 */
		public function plugins_content( $args = '' ){
			$img_src = RTMEDIA_URL . 'app/assets/img/';
			$addons  = array(
				'general' => array(
					array(
						'title' => __( 'rtMedia Social Sync', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-social-sync-240x184.png',
						'product_link' => 'https://rtcamp.com/products/rtmedia-social-sync/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'rtMedia Social Sync allows you to import media from your Facebook account.', 'rtmedia' ) . '</p> <p><strong>' . __( 'Important', 'rtmedia' ) . ':</strong> ' . __( 'Facebook app is required to work this add-on.', 'rtmedia' ) . '</p>',
						'price' => '$49',
						'demo_link' => 'http://demo.rtcamp.com/rtmedia/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/products/rtmedia-social-sync/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'general',
						'purchased' => ( defined( 'RTMEDIA_SOCIAL_SYNC_URL' ) || file_exists( WP_PLUGIN_DIR . '/rtmedia-social-sync/index.php' ) ) ? true : false,
					),
				),
				'photo' => array(
					array(
						'title' => __( 'rtMedia Photo Watermark', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-watermark-240x184.png',
						'product_link' => 'http://rtcamp.com/store/rtmedia-photo-watermark/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'rtMedia Photo Watermark add-on let you add text or copyright on your images uploaded using rtMedia.', 'rtmedia' ) . '</p> <p><strong>' . __( 'Important', 'rtmedia' ) . ':</strong> ' . __( 'You need to have either ImageMagick or GD library installed on your server for this addon to work.', 'rtmedia' ) . '</p>',
						'price' => '$49',
						'demo_link' => 'http://demo.rtcamp.com/rtmedia/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/store/rtmedia-photo-watermark/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'photo',
						'purchased' => ( defined( 'RTMEDIA_WATERMARK_URL' ) || file_exists( WP_PLUGIN_DIR . '/rtmedia-photo-watermak/index.php' ) ) ? true : false,
					),
					array(
						'title' => __( 'rtMedia Photo Tagging', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-phototagging-240x184.png',
						'product_link' => 'http://rtcamp.com/store/rtmedia-photo-tagging/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'rtMedia Photo Tagging add-on enables tagging on photos uploaded using BuddyPress Media.', 'rtmedia' ) . '</p> <p><strong>' . __( 'Important', 'rtmedia' ) . ':</strong> ' . __( 'You need to have ImageMagick installed on your server for this addon to work.', 'rtmedia' ) . '</p>',
						'price' => '$49',
						'demo_link' => 'http://demo.rtcamp.com/rtmedia/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/store/rtmedia-photo-tagging/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'photo',
						'purchased' => ( defined( 'RTMEDIA_PHOTO_TAGGING_URL' ) || file_exists( WP_PLUGIN_DIR . '/bpm-photo-tag/index.php' ) ) ? true : false,
					),
					array(
						'title' => __( 'rtMedia Instagram', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-instagram-240x184.png',
						'product_link' => 'http://rtcamp.com/store/rtmedia-instagram/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'rtMedia Instagram adds Instagram like filters to images uploaded with rtMedia.', 'rtmedia' ) . '</p> <p><strong>' . __( 'Important', 'rtmedia' ) . ':</strong> ' . __( 'You need to have ImageMagick installed on your server for this addon to work.', 'rtmedia' ) . '</p>',
						'price' => '$49',
						'demo_link' => 'http://demo.rtcamp.com/rtmedia/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/store/rtmedia-instagram/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'photo',
						'purchased' => ( defined( 'RTMEDIA_INSTAGRAM_URL' ) || file_exists( WP_PLUGIN_DIR . '/bpm-instagram/index.php' ) ) ? true : false,
					),
				),
				'video'=> array(
					array(
						'title' => __( 'rtMedia Kaltura Add-on', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-kaltura-240x184.png',
						'product_link' => 'http://rtcamp.com/store/rtmedia-kaltura-add-on/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'Add support for more video formats using Kaltura video solution.', 'rtmedia' ) . '</p> <p>' . __( 'Works with Kaltura.com, self-hosted Kaltura-CE and Kaltura-on-premise.', 'rtmedia' ) . '</p>',
						'price' => '$199',
						'demo_link' => 'http://demo.rtcamp.com/bpm-kaltura/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/store/rtmedia-kaltura/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'video',
						'purchased' => ( defined( 'RTMEDIA_KALTURA_PATH' ) || file_exists( WP_PLUGIN_DIR . '/bpm-kaltura/index.php' ) ) ? true : false,
					),
					array(
						'title' => __( 'rtMedia FFMPEG Add-on', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-ffmpeg-240x184.png',
						'product_link' => 'http://rtcamp.com/store/buddypress-media-ffmpeg-converter/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'Add supports for more audio & video formats using open-source media-node.', 'rtmedia' ) . '</p> <p>' . __( 'Media node comes with automated setup script for Ubuntu/Debian.', 'rtmedia' ) . '</p>',
						'price' => '$199',
						'demo_link' => 'http://demo.rtcamp.com/bpm-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'buy_now' => 'https://rtcamp.com/store/rtmedia-ffmpeg/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'video',
						'purchased' => ( defined( 'RTMEDIA_FFMPEG_URL' ) || file_exists( WP_PLUGIN_DIR . '/bpm-ffmpeg/index.php' ) ) ? true : false,
					),
				),
				'membership'=> array(
					array(
						'title' => __( 'rtMedia Membership Add-on', 'rtmedia' ),
						'img_src' => $img_src . 'rtmedia-membership-240x184.png',
						'product_link' => 'https://rtcamp.com/products/rtmedia-membership/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'desc' => '<p>' . __( 'rtMedia Membership add-on provides membership functionality in your site.', 'rtmedia' ) . '</p> <p>' . __( 'It controls the number of files a member can upload and size of the files uploaded based on membership group.', 'rtmedia' ) . '</p>',
						'price' => '$99',
						'buy_now' => 'https://rtcamp.com/products/rtmedia-membership/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media',
						'category' => 'membership',
						'purchased' => ( defined( 'RTMEDIA_MEMBERSHIP_URL' ) || file_exists( WP_PLUGIN_DIR . '/rtmedia-membership/index.php' ) ) ? true : false,
					),
				),
			);
			$addons  = apply_filters( 'rtmedia_addons', $addons );

			$general_addons = $addons['general'];
			$photo_addons = $addons['photo'];
			$video_addons = $addons['video'];
			$membership_addons = $addons['membership'];

			echo '<h3>';
			echo ( sizeof( $general_addons ) > 1 ) ? __( 'rtMedia Addons', 'rtmedia' ) : __( 'rtMedia Addon', 'rtmedia' );
			echo '</h3>';

			foreach ( $general_addons as $key => $value ) {
				$this->addon( $value );
			}

			echo '<h3>';
			_e( 'rtMedia Addons for Photos', 'rtmedia' );
			echo '</h3>';

			foreach ( $photo_addons as $key => $value ) {
				$this->addon( $value );
			}

			echo '<h3>';
			_e( 'rtMedia Addons for Audio/Video', 'rtmedia' );
			echo '</h3>';

			foreach ( $video_addons as $key => $value ) {
				$this->addon( $value );
			}

			echo '<h3>';
			_e( 'rtMedia Addon for Membership', 'rtmedia' );
			echo '</h3>';

			foreach ( $membership_addons as $key => $value ) {
				$this->addon( $value );
			}
		}

		/**
		 * services_content.
		 *
		 * @access public
		 *
		 * @param  array $args
		 *
		 * @return void
		 */
		public function services_content( $args = '' ){
			$objEncoding->encoding_service_intro();
		}

		/**
		 * themes_content.
		 *
		 * @access public
		 *
		 * @param  array $args
		 *
		 * @return void
		 */
		public function themes_content( $args = '' ){
			echo '<h3>' . __( 'Coming Soon !!', 'rtmedia' ) . '</h3>';
		}


		/**
		 * Define addon.
		 *
		 * @global type  $rtmedia
		 *
		 * @param  array $args
		 *
		 * @return void
		 */
		public function addon( $args ){
			global $rtmedia;

			$defaults = array(
				'title' => '',
				'img_src' => '',
				'product_link' => '',
				'desc' => '',
				'price' => '',
				'demo_link' => '',
				'buy_now' => '',
				'coming_soon' => false,
				'category' => 'photo',
				'purchased' => false,
			);
			$args     = wp_parse_args( $args, $defaults );
			extract( $args );

			$coming_soon ? ' coming-soon' : '';

			if( $purchased ){
				$purchase_link = '<span class="rtm-addon-purchased alignright product_type_simple">' . __( 'Purchased', 'rtmedia' ) . '</span>';
			} else {
				$purchase_link = '<a class="add_to_cart_button  alignright product_type_simple"  href="' . $buy_now . '" target="_blank">' . __( 'Buy Now', 'rtmedia' ) . '</a>';
			}

			$coming_soon_div = ( $coming_soon ) ? $this->coming_soon_div() : '';
			$addon           = '<div class="bp-media-addon">
			    <a href="' . $product_link . '"  title="' . $title . '" target="_blank">
			        <img width="240" height="184" title="' . $title . '" alt="' . $title . '" src="' . $img_src . '">
			    </a>
			    <h4><a href="' . $product_link . '"  title="' . $title . '" target="_blank">' . $title . '</a></h4>
			    <div class="product_desc">
			        ' . $desc . '
			    </div>
			    <div class="product_footer">
			        <span class="price alignleft"><span class="amount">' . $price . '</span></span>' . $purchase_link ;
			if( $demo_link != '' ){
				$addon .= '<a class="alignleft product_demo_link"  href="' . $demo_link . '" title="' . $title . '" target="_blank">' . __( 'Live Demo', 'rtmedia' ) . '</a>';
			}
			$addon .= '</div>' . $coming_soon_div . '</div>';
			echo $addon;
		}

	}

}