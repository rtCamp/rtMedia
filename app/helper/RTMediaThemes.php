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

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}

			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
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
			'title'    => esc_html__( 'Themes By rtCamp', 'buddypress-media' ),
			'name'     => esc_html__( 'Themes By rtCamp', 'buddypress-media' ),
			'href'     => '#rtmedia-themes',
			'icon'     => 'dashicons-admin-appearance',
			'callback' => array( $this, 'rtmedia_themes_content' ),
		);
		$tabs[] = array(
			'title'    => esc_html__( '3rd Party Themes', 'buddypress-media' ),
			'name'     => esc_html__( '3rd Party Themes', 'buddypress-media' ),
			'href'     => '#rtmedia-themes-3',
			'icon'     => 'dashicons-randomize',
			'callback' => array( $this, 'rtmedia_3rd_party_themes_content' ),
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
			$rtdating_purchase = '<a href="https://rtmedia.io/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy rtDating</a> | ';
		}

		$inspirebook = wp_get_theme( 'inspirebook' );
		if ( $inspirebook->exists() ) {
			$inspirebook_purchase = '';
		} else {
			$inspirebook_purchase = '<a href="https://rtmedia.io/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy InspireBook</a> | ';
		}

		$rtmedia_demo_url = 'http://demo.rtmedia.io/';
		$themes = array(
			'rtdating'    => array(
				'name'        => esc_html__( 'rtDating', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtDating.png',
				'demo_url'    => $rtmedia_demo_url . 'rtdating/',
				'author'      => esc_html__( 'rtCamp', 'buddypress-media' ),
				'author_url'  => 'https://rtcamp.com/',
				'buy_url'     => 'https://rtmedia.io/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media',
				'description' => esc_html__( 'rtDating is a unique, clean and modern theme only for WordPress. This theme is mostly useful for dating sites and community websites. It can also be use for any other WordPress based website.', 'buddypress-media' ),
				'tags'        => 'black, green, white, light, dark, two-columns, three-columns, left-sidebar, right-sidebar, fixed-layout, responsive-layout, custom-background, custom-header, custom-menu, editor-style, featured-images, flexible-header, full-width-template, microformats, post-formats, rtl-language-support, sticky-post, theme-options, translation-ready, accessibility-ready',
			),
			'inspirebook' => array(
				'name'        => esc_html__( 'InspireBook', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-InspireBook.png',
				'demo_url'    => $rtmedia_demo_url . 'inspirebook/',
				'author'      => esc_html__( 'rtCamp', 'buddypress-media' ),
				'author_url'  => 'https://rtcamp.com/',
				'buy_url'     => 'https://rtmedia.io/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media',
				'description' => esc_html__( 'InspireBook is a premium WordPress theme, designed especially for BuddyPress and rtMedia powered social-networks.', 'buddypress-media' ),
				'tags'        => 'black, blue, white, light, one-column, two-columns, right-sidebar, custom-header, custom-background, custom-menu, editor-style, theme-options, threaded-comments, sticky-post, translation-ready, responsive-layout, full-width-template, buddypress',
			),
			'foodmania'   => array(
				'name'        => esc_html__( 'Foodmania', 'buddypress-media' ),
				'image'       => 'https://rtmedia.io/wp-content/uploads/edd/2015/08/foodmania-img1.png',
				'demo_url'    => $rtmedia_demo_url . 'foodmania/',
				'author'      => esc_html__( 'rtCamp', 'buddypress-media' ),
				'author_url'  => 'https://rtcamp.com/',
				'buy_url'     => 'https://rtmedia.io/products/foodmania/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media',
				'description' => esc_html__( 'Its premium WordPress theme, designed especially for Food, recipe and photography community sites.', 'buddypress-media' ),
				'tags'        => 'black, yellow, white, dark, one-column, two-columns, right-sidebar, custom-header, custom-background, custom-menu, editor-style, theme-options, threaded-comments, sticky-post, translation-ready, responsive-layout, full-width-template, buddypress',
			),
		);
		?>

		<div class="theme-browser rtm-theme-browser rendered">
			<div class="themes rtm-themes clearfix">

				<?php
				foreach ( $themes as $theme ) {
					?>

					<div class="theme rtm-theme">
						<div class="theme-screenshot">
							<img src="<?php echo esc_url( $theme['image'] ); ?>"/>
						</div>

						<span class="more-details"><?php esc_html_e( 'Theme Details', 'buddypress-media' ); ?></span>

						<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>

						<div class="theme-actions">
							<a class="button load-customize hide-if-no-customize"
							   href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
							<a class="button button-primary load-customize hide-if-no-customize"
							   href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
						</div>

						<div class="rtm-theme-content hide">
							<div class="theme-wrap">
								<div class="theme-header">
									<button class="left rtm-previous dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Show previous theme', 'buddypress-media' ); ?></span>
									</button>
									<button class="right rtm-next dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Show next theme', 'buddypress-media' ); ?></span>
									</button>
									<button class="close rtm-close dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Close overlay', 'buddypress-media' ); ?></span>
									</button>
								</div>

								<div class="theme-about">
									<div class="theme-screenshots">
										<div class="screenshot">
											<a href="<?php echo esc_url( $theme['buy_url'] ); ?>" target="_blank"><img
													src="<?php echo esc_url( $theme['image'] ); ?>"/></a>
										</div>
									</div>

									<div class="theme-info">
										<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>
										<h4 class="theme-author">By <a
												href="https://rtcamp.com/"><?php echo esc_html( $theme['author'] ); ?></a></h4>
										<p class="theme-description"><?php echo esc_html( $theme['description'] ); ?> <a
												href="<?php echo esc_url( $theme['buy_url'] ); ?>"
												class="rtmedia-theme-inner-a"
												target="_blank"><?php esc_html_e( 'Read More', 'buddypress-media' ); ?></a>
										</p>
										<p class="theme-tags">
											<span><?php esc_html_e( 'Tags:', 'buddypress-media' ); ?></span><?php echo esc_html( $theme['tags'] ); ?>
										</p>
									</div>
								</div>

								<div class="theme-actions">
									<a class="button load-customize hide-if-no-customize"
									   href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
									<a class="button button-primary load-customize hide-if-no-customize"
									   href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
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
			'woffice'   => array(
				'name'        => esc_html__( 'Woffice - Intranet/Extranet WordPress Theme', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-woffice.jpg',
				'demo_url'    => 'http://rt.cx/woffice',
				'author'      => esc_html__( 'Alkaweb', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/woffice',
				'buy_url'     => 'http://rt.cx/woffice',
				'description' => esc_html__( 'Woffice is a feature rich, powerful Multipurpose Intranet/Extranet Wordpress theme.', 'buddypress-media' ),
				'tags'        => 'association, community, extranet, intranet, management, network, office, private, projects, school, wiki',
			),
			'thrive'    => array(
				'name'        => esc_html__( 'Thrive - Intranet & Community WordPress Theme', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-thrive.jpg',
				'demo_url'    => 'http://rt.cx/thrive',
				'author'      => esc_html__( 'dunhakdis', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/thrive',
				'buy_url'     => 'http://rt.cx/thrive',
				'description' => esc_html__( 'Thrive is an innovative WordPress Theme designed to cater company portals, organisational websites, company intranet and extranets.', 'buddypress-media' ),
				'tags'        => 'community, events, extranet, forums, intranet, membership, network, polls, project management, rtl, social, tasks',
			),
			'msocial'   => array(
				'name'        => esc_html__( '(M) SOCIAL NETWORK BUDDYPRESS THEME', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-msocial.jpg',
				'demo_url'    => 'http://rt.cx/msocial',
				'author'      => esc_html__( 'gavick', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/msocial',
				'buy_url'     => 'http://rt.cx/msocial',
				'description' => esc_html__( '(M)Social is a sophisticated, vibrant community theme that offers incredible grid layouts, with full BuddyPress support so your users can interact with each other, create their own pages, and share their thoughts and images with the community.', 'buddypress-media' ),
				'tags'        => 'responsive, WPML, BuddyPress, social, business',
			),
			'klein'     => array(
				'name'        => esc_html__( 'Klein', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-klein.jpg',
				'demo_url'    => 'http://rt.cx/klein',
				'author'      => esc_html__( 'dunhakdis', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/klein',
				'buy_url'     => 'http://rt.cx/klein',
				'description' => esc_html__( 'Klein is an innovative WordPress theme built to support BuddyPress, bbPress, and WooCommerce out of the box. Perfect for websites that interacts with many users.', 'buddypress-media' ),
				'tags'        => 'bbpress, bp, buddypress, buddypress flat design, buddypress theme, community, responsive buddypress, responsive buddypress theme, social network, wordpress community theme',
			),
			'sweetdate' => array(
				'name'        => esc_html__( 'SweetDate', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-sweetdate.png',
				'demo_url'    => 'http://rt.cx/sweetdate',
				'author'      => esc_html__( 'SeventhQueen', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/sweetdate',
				'buy_url'     => 'http://rt.cx/sweetdate',
				'description' => esc_html__( 'SweetDate is a unique, clean and modern Premium Wordpress theme. It is perfect for a dating or community website but can be used as well for any other domain. They added all the things you need to create a perfect community system.', 'buddypress-media' ),
				'tags'        => 'bbpress, buddypress, clean, community, creative, dating, facebook, foundation, mailchimp, retina, seo, social, woocommerce, wordpress, zurb',
			),
			'kleo'      => array(
				'name'        => esc_html__( 'KLEO', 'buddypress-media' ),
				'image'       => RTMEDIA_URL . 'app/assets/admin/img/rtmedia-theme-kleo.png',
				'demo_url'    => 'http://rt.cx/kleo',
				'author'      => esc_html__( 'SeventhQueen', 'buddypress-media' ),
				'author_url'  => 'http://rt.cx/kleo',
				'buy_url'     => 'http://rt.cx/kleo',
				'description' => esc_html__( 'You no longer need to be a professional developer or designer to create an awesome website. Let your imagination run wild and create the site of your dreams. KLEO has all the tools to get you started.', 'buddypress-media' ),
				'tags'        => 'bbpress, Bootstrap 3, buddypress, clean design, community theme, e-commerce theme, multi-purpose, responsive design, retina, woocommerce, wordpress theme',
			),
		);
		?>


		<div class="theme-browser rtm-theme-browser rendered">
			<div class="themes rtm-themes clearfix">

				<?php
				foreach ( $themes as $theme ) {
					?>

					<div class="theme rtm-theme">
						<div class="theme-screenshot">
							<img src="<?php echo esc_url( $theme['image'] ); ?>"/>
						</div>

						<span class="more-details"><?php esc_html_e( 'Theme Details', 'buddypress-media' ); ?></span>

						<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>

						<div class="theme-actions">
							<a class="button load-customize hide-if-no-customize"
							   href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
							<a class="button button-primary load-customize hide-if-no-customize"
							   href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
						</div>

						<div class="rtm-theme-content hide">
							<div class="theme-wrap">
								<div class="theme-header">
									<button class="left rtm-previous dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Show previous theme', 'buddypress-media' ); ?></span>
									</button>
									<button class="right rtm-next dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Show next theme', 'buddypress-media' ); ?></span>
									</button>
									<button class="close rtm-close dashicons dashicons-no"><span
											class="screen-reader-text"><?php esc_html_e( 'Close overlay', 'buddypress-media' ); ?></span>
									</button>
								</div>

								<div class="theme-about">
									<div class="theme-screenshots">
										<div class="screenshot">
											<a href="<?php echo esc_url( $theme['buy_url'] ); ?>" target="_blank"><img
													src="<?php echo esc_url( $theme['image'] ); ?>"/></a>
										</div>
									</div>

									<div class="theme-info">
										<h3 class="theme-name"><?php echo esc_html( $theme['name'] ); ?></h3>
										<h4 class="theme-author">By <a
												href="<?php echo esc_url( $theme['author_url'] ); ?>"><?php echo esc_html( $theme['author'] ); ?></a>
										</h4>
										<p class="theme-description"><?php echo esc_html( $theme['description'] ); ?> <a
												href="<?php echo esc_url( $theme['buy_url'] ); ?>" class="rtmedia-theme-inner-a"
												target="_blank"><?php esc_html_e( 'Read More', 'buddypress-media' ); ?></a>
										</p>
										<p class="theme-tags">
											<span><?php esc_html_e( 'Tags:', 'buddypress-media' ); ?></span><?php echo esc_html( $theme['tags'] ); ?>
										</p>
									</div>
								</div>

								<div class="theme-actions">
									<a class="button load-customize hide-if-no-customize"
									   href="<?php echo esc_url( $theme['demo_url'] ); ?>"><?php esc_html_e( 'Live Demo', 'buddypress-media' ); ?></a>
									<a class="button button-primary load-customize hide-if-no-customize"
									   href="<?php echo esc_url( $theme['buy_url'] ); ?>"><?php esc_html_e( 'Buy Now', 'buddypress-media' ); ?></a>
								</div>
							</div>
						</div>
					</div>

				<?php } ?>
			</div>
		</div>

		<div
			class="rtmedia-theme-warning rtm-warning"><?php esc_html_e( 'These are the third party themes. For any issues or queries regarding these themes please contact theme developers.', 'buddypress-media' ); ?></div>

		<div>
			<h3 class="rtm-option-title"><?php esc_html_e( 'Are you a developer?', 'buddypress-media' ); ?></h3>

			<p>
				<?php esc_html_e( 'If you have developed a rtMedia compatible theme and would like it to list here, please email us at', 'buddypress-media' ) ?>
				<a href="mailto:rtmedia@rtcamp.com"><?php esc_html_e( 'rtmedia@rtcamp.com', 'buddypress-media' ) ?></a>.
			</p>
		</div>
		<?php
	}
}