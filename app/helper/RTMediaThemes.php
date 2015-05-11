<?php

/**
 * Description of RTMediaThemes
 *
 * @author ritz
 */
class RTMediaThemes {

	// current page
	public static $page;

	/**
	 * Render themes
	 *
	 * @access public
	 *
	 * @param $page
	 *
	 * @return void
	 */
	public static function render_themes( $page = '' ) {
		global $wp_settings_sections, $wp_settings_fields;

		self::$page = $page;

		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( ( array ) $wp_settings_sections[ $page ] as $section ) {

			if ( $section[ 'callback' ] ) {
				call_user_func( $section[ 'callback' ], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section[ 'id' ] ] ) ) {
				continue;
			}

			echo '<table class="form-table">';
			do_settings_fields( $page, $section[ 'id' ] );
			echo '</table>';
		}
	}

	/**
	 * Get themes.
	 *
	 * @access public
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public function get_themes() {
		$tabs = array();
		global $rtmedia_admin;
		$tabs[] = array(
			'title' => __( 'Themes By rtCamp', 'rtmedia' ),
			'name' => __( 'Themes By rtCamp', 'rtmedia' ),
			'href' => '#rtmedia-themes',
			'icon' => 'dashicons-admin-appearance',
			'callback' => array( $this, 'rtmedia_themes_content' )
		);
		$tabs[] = array(
			'title' => __( '3rd Party Themes', 'rtmedia' ),
			'name' => __( '3rd Party Themes', 'rtmedia' ),
			'href' => '#rtmedia-themes-3',
			'icon' => 'dashicons-randomize',
			'callback' => array( $this, 'rtmedia_3rd_party_themes_content' )
		);
		RTMediaAdmin::render_admin_ui( self::$page, $tabs );
	}

	/**
	 * Show rtmedia_themes_content.
	 *
	 * @access public
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public function rtmedia_themes_content() {


		$rtdating = wp_get_theme( 'rtdating' );
		if ( $rtdating->exists() ) {
			$rtdating_purchase = '';
		} else {
			$rtdating_purchase = '<a href="https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy rtDating</a> | ';
		}

		$inspirebook = wp_get_theme( 'inspirebook' );
		if ( $inspirebook->exists() ) {
			$inspirebook_purchase = '';
		} else {
			$inspirebook_purchase = '<a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy InspireBook</a> | ';
		}


		$themes = array(
			'rtdating' => array(
				'name' => __( 'rtDating', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtDating.png',
				'demo_url' => 'http://demo.rtcamp.com/rtdating/',
				'author' => __( 'rtCamp', 'rtmedia' ),
				'author_url' => 'https://rtcamp.com/',
				'buy_url' => 'https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media',
				'description' => __( 'rtDating is a unique, clean and modern theme only for WordPress. This theme is mostly useful for dating sites and community websites. It can also be use for any other WordPress based website.', 'rtmedia' ),
				'tags' => 'black, green, white, light, dark, two-columns, three-columns, left-sidebar, right-sidebar, fixed-layout, responsive-layout, custom-background, custom-header, custom-menu, editor-style, featured-images, flexible-header, full-width-template, microformats, post-formats, rtl-language-support, sticky-post, theme-options, translation-ready, accessibility-ready',
			),
			'inspirebook' => array(
				'name' => __( 'InspireBook', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-InspireBook.png',
				'demo_url' => 'http://demo.rtcamp.com/inspirebook/',
				'author' => __( 'rtCamp', 'rtmedia' ),
				'author_url' => 'https://rtcamp.com/',
				'buy_url' => 'https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media',
				'description' => __( 'InspireBook is a premium WordPress theme, designed especially for BuddyPress and rtMedia powered social-networks.', 'rtmedia' ),
				'tags' => 'black, blue, white, light, one-column, two-columns, right-sidebar, custom-header, custom-background, custom-menu, editor-style, theme-options, threaded-comments, sticky-post, translation-ready, responsive-layout, full-width-template, buddypress',
			)
		);
		?>

		<div class="theme-browser rtm-theme-browser rendered">
			<div class="themes rtm-themes clearfix">

				<?php
				foreach ( $themes as $theme ) {
					?>

					<div class="theme rtm-theme">
						<div class="theme-screenshot">
							<img src="<?php echo $theme[ 'image' ]; ?>" />
						</div>

						<span class="more-details"><?php _e( 'Theme Details' ); ?></span>

						<h3 class="theme-name"><?php echo $theme[ 'name' ]; ?></h3>

						<div class="theme-actions">
							<a class="button load-customize hide-if-no-customize" href="<?php echo $theme[ 'demo_url' ]; ?>"><?php _e( 'Live Demo' ); ?></a>
							<a class="button button-primary load-customize hide-if-no-customize" href="<?php echo $theme[ 'buy_url' ]; ?>"><?php _e( 'Buy Now' ); ?></a>
						</div>

						<div class="rtm-theme-content hide">
							<div class="theme-wrap">
								<div class="theme-header">
									<button class="left rtm-previous dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous theme' ); ?></span></button>
									<button class="right rtm-next dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next theme' ); ?></span></button>
									<button class="close rtm-close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close overlay' ); ?></span></button>
								</div>

								<div class="theme-about">
									<div class="theme-screenshots">
										<div class="screenshot">
											<a href="<?php echo $theme[ 'buy_url' ]; ?>" target="_blank"><img src="<?php echo $theme[ 'image' ]; ?>"/></a>
										</div>
									</div>

									<div class="theme-info">
										<h3 class="theme-name"><?php echo $theme[ 'name' ]; ?></h3>
										<h4 class="theme-author">By <a href="https://rtcamp.com/"><?php echo $theme[ 'author' ]; ?></a></h4>
										<p class="theme-description"><?php echo $theme[ 'description' ]; ?> <a href="<?php echo $theme[ 'buy_url' ]; ?>" class="rtmedia-theme-inner-a" target="_blank"><?php _e( 'Read More' ); ?></a></p>
										<p class="theme-tags"><span><?php _e( 'Tags:' ); ?></span><?php echo $theme[ 'tags' ]; ?></p>
									</div>
								</div>

								<div class="theme-actions">
									<a class="button load-customize hide-if-no-customize" href="<?php echo $theme[ 'demo_url' ]; ?>"><?php _e( 'Live Demo' ); ?></a>
									<a class="button button-primary load-customize hide-if-no-customize" href="<?php echo $theme[ 'buy_url' ]; ?>"><?php _e( 'Buy Now' ); ?></a>
								</div>
							</div>
						</div>
					</div>

				<?php } ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Show rtmedia_3rd_party_themes_content.
	 *
	 * @access public
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public function rtmedia_3rd_party_themes_content() {

		$themes = array(
			'msocial' => array(
				'name' => __( '(M) SOCIAL NETWORK BUDDYPRESS THEME', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-msocial.jpg',
				'demo_url' => 'http://rt.cx/msocial',
				'author' => __( 'gavick', 'rtmedia' ),
				'author_url' => 'http://rt.cx/msocial',
				'buy_url' => 'http://rt.cx/msocial',
				'description' => __( '(M)Social is a sophisticated, vibrant community theme that offers incredible grid layouts, with full BuddyPress support so your users can interact with each other, create their own pages, and share their thoughts and images with the community. ', 'rtmedia' ),
				'tags' => 'responsive, WPML, BuddyPress, social, business',
			),
			'klein' => array(
				'name' => __( 'Klein', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-klein.jpg',
				'demo_url' => 'http://rt.cx/klein',
				'author' => __( 'dunhakdis', 'rtmedia' ),
				'author_url' => 'http://rt.cx/klein',
				'buy_url' => 'http://rt.cx/klein',
				'description' => __( 'Klein is an innovative WordPress theme built to support BuddyPress, bbPress, and WooCommerce out of the box. Perfect for websites that interacts with many users.', 'rtmedia' ),
				'tags' => 'bbpress, bp, buddypress, buddypress flat design, buddypress theme, community, responsive buddypress, responsive buddypress theme, social network, wordpress community theme',
			),
			'sweetdate' => array(
				'name' => __( 'SweetDate', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-sweetdate.png',
				'demo_url' => 'http://rt.cx/sweetdate',
				'author' => __( 'SeventhQueen', 'rtmedia' ),
				'author_url' => 'http://rt.cx/sweetdate',
				'buy_url' => 'http://rt.cx/sweetdate',
				'description' => __( 'SweetDate is a unique, clean and modern Premium Wordpress theme. It is perfect for a dating or community website but can be used as well for any other domain. They added all the things you need to create a perfect community system.', 'rtmedia' ),
				'tags' => 'bbpress, buddypress, clean, community, creative, dating, facebook, foundation, mailchimp, retina, seo, social, woocommerce, wordpress, zurb',
			),
			'kleo' => array(
				'name' => __( 'KLEO', 'rtmedia' ),
				'image' => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-kleo.png',
				'demo_url' => 'http://rt.cx/kleo',
				'author' => __( 'SeventhQueen', 'rtmedia' ),
				'author_url' => 'http://rt.cx/kleo',
				'buy_url' => 'http://rt.cx/kleo',
				'description' => __( 'You no longer need to be a professional developer or designer to create an awesome website. Let your imagination run wild and create the site of your dreams. KLEO has all the tools to get you started.', 'rtmedia' ),
				'tags' => 'bbpress, Bootstrap 3, buddypress, clean design, community theme, e-commerce theme, multi-purpose, responsive design, retina, woocommerce, wordpress theme',
			)
		);
		?>


		<div class="theme-browser rtm-theme-browser rendered">
			<div class="themes rtm-themes clearfix">

				<?php
				foreach ( $themes as $theme ) {
					?>

					<div class="theme rtm-theme">
						<div class="theme-screenshot">
							<img src="<?php echo $theme[ 'image' ]; ?>" />
						</div>

						<span class="more-details"><?php _e( 'Theme Details' ); ?></span>

						<h3 class="theme-name"><?php echo $theme[ 'name' ]; ?></h3>

						<div class="theme-actions">
							<a class="button load-customize hide-if-no-customize" href="<?php echo $theme[ 'demo_url' ]; ?>"><?php _e( 'Live Demo' ); ?></a>
							<a class="button button-primary load-customize hide-if-no-customize" href="<?php echo $theme[ 'buy_url' ]; ?>"><?php _e( 'Buy Now' ); ?></a>
						</div>

						<div class="rtm-theme-content hide">
							<div class="theme-wrap">
								<div class="theme-header">
									<button class="left rtm-previous dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous theme' ); ?></span></button>
									<button class="right rtm-next dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next theme' ); ?></span></button>
									<button class="close rtm-close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close overlay' ); ?></span></button>
								</div>

								<div class="theme-about">
									<div class="theme-screenshots">
										<div class="screenshot">
											<a href="<?php echo $theme[ 'buy_url' ]; ?>" target="_blank"><img src="<?php echo $theme[ 'image' ]; ?>"/></a>
										</div>
									</div>

									<div class="theme-info">
										<h3 class="theme-name"><?php echo $theme[ 'name' ]; ?></h3>
										<h4 class="theme-author">By <a href="<?php echo $theme[ 'author_url' ]; ?>"><?php echo $theme[ 'author' ]; ?></a></h4>
										<p class="theme-description"><?php echo $theme[ 'description' ]; ?> <a href="<?php echo $theme[ 'buy_url' ]; ?>" class="rtmedia-theme-inner-a" target="_blank"><?php _e( 'Read More' ); ?></a></p>
										<p class="theme-tags"><span><?php _e( 'Tags:' ); ?></span><?php echo $theme[ 'tags' ]; ?></p>
									</div>
								</div>

								<div class="theme-actions">
									<a class="button load-customize hide-if-no-customize" href="<?php echo $theme[ 'demo_url' ]; ?>"><?php _e( 'Live Demo' ); ?></a>
									<a class="button button-primary load-customize hide-if-no-customize" href="<?php echo $theme[ 'buy_url' ]; ?>"><?php _e( 'Buy Now' ); ?></a>
								</div>
							</div>
						</div>
					</div>

				<?php } ?>
			</div>
		</div>

		<div class="rtmedia-theme-warning rtm-warning"><?php _e( 'These are the third party themes. For any issues or queries regarding these themes please contact theme developers.', 'rtmedia' ) ?></div>

		<div>
			<h3 class="rtm-option-title"><?php _e( 'Are you a developer?', 'rtmedia' ); ?></h3>

			<p>
				<?php _e( 'If you have developed a rtMedia compatible theme and would like it to list here, please email us at', 'rtmedia' ) ?>
				<a href="mailto:product@rtcamp.com"><?php _e( 'product@rtcamp.com', 'rtmedia' ) ?></a>.
			</p>
		</div>
		<?php
	}

}
