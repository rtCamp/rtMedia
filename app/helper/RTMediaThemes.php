<?php

/**
 * Description of RTMediaThemes
 *
 * @author ritz
 */
class RTMediaThemes {

	/**
	 * Render themes
	 *
	 * @access public
	 *
	 * @param $page
	 *
	 * @return void
	 */
	public static function render_themes( $page = '' ){
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
	 * Get themes.
	 *
	 * @access public
	 *
	 * @param  void
	 *
	 * @return void
	 */
	public function get_themes(){
		$tabs = array();
		global $rtmedia_admin;
		$tabs[] = array(
			'title' => __( 'rtMedia Themes By rtCamp', 'rtmedia' ),
			'name' => __( 'rtMedia Themes By rtCamp', 'rtmedia' ),
			'href' => '#rtmedia-themes',
			'callback' => array( $this, 'rtmedia_themes_content' )
		);
		$tabs[] = array(
			'title' => __( '3rd Party Themes', 'rtmedia' ),
			'name' => __( '3rd Party Themes', 'rtmedia' ),
			'href' => '#rtmedia-themes-3',
			'callback' => array( $this, 'rtmedia_3rd_party_themes_content' )
		);
		?>
		<div id="rtm-themes">
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
			echo '<div class="content' . $active_class . '" id="' . $tab_without_hash . '">';
			call_user_func( $tab['callback'] );
			echo '</div>';
		}
		echo '</div>';
		?>
			</div>
		</div>
	<?php
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
	public function rtmedia_themes_content(){


		$rtdating = wp_get_theme( 'rtdating' );
		if( $rtdating->exists() ){
			$rtdating_purchase = '';
		} else {
			$rtdating_purchase = '<a href="https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy rtDating</a> | ';
		}

		$inspirebook = wp_get_theme( 'inspirebook' );
		if( $inspirebook->exists() ){
			$inspirebook_purchase = '';
		} else {
			$inspirebook_purchase = '<a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">Buy InspireBook</a> | ';
		}

		?>
		<div class="row">
			<div class="columns large-12">
				<div class="columns large-4 rtmedia-theme-image">
					<a href="https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank"><img src="<?php echo RTMEDIA_URL . 'app/assets/img/rtDating.png' ?>"/></a>
				</div>
				<div class="columns large-7 rtmedia-theme-content">
					<h3 class="rtmedia-theme-title"><a href="https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">rtDating</a></h3>

					<div>
						<p>
							<span>rtDating is a unique, clean and modern theme only for WordPress. This theme is mostly useful for dating sites and community websites. It can also be use for any other WordPress based website. <a href="https://rtcamp.com/products/rtdating/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" class="rtmedia-theme-inner-a" target="_blank">Read More</a></span>
						</p>

						<p>
							<span>Links: <?php echo $rtdating_purchase; ?> <a href="http://demo.rtcamp.com/rtmedia/?theme=rtDating" target="_blank">Live Demo</a> | <a href="http://docs.rtcamp.com/rtdating/" target="_blank">Documentation</a> | <a href="https://rtcamp.com/support/forum/premium-themes/" target="_blank">Support Forum</a></span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="columns large-12">
				<div class="columns large-4 rtmedia-theme-image">
					<a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank"><img src="<?php echo RTMEDIA_URL . 'app/assets/img/rtmedia-theme-InspireBook.png' ?>"/></a>
				</div>
				<div class="columns large-7 rtmedia-theme-content">
					<h3 class="rtmedia-theme-title"><a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank">InspireBook</a></h3>

					<div>
						<p>
							<span><a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" target="_blank"><b>Meet InspireBook</b></a> - First official rtMedia premium theme.</span>
						</p>

						<p>
							<span>InspireBook is a premium WordPress theme, designed especially for BuddyPress and rtMedia powered social-networks. <a href="https://rtcamp.com/products/inspirebook/?utm_source=readme&utm_medium=plugin&utm_campaign=buddypress-media" class="rtmedia-theme-inner-a" target="_blank">Read More</a> </span>
						</p>

						<p>
							<span>Links: <?php echo $inspirebook_purchase; ?> <a href="http://demo.rtcamp.com/rtmedia/?theme=InspireBook" target="_blank">Live Demo</a> | <a href="http://docs.rtcamp.com/inspirebook" target="_blank">Documentation</a> | <a href="http://community.rtcamp.com/c/premium-themes" target="_blank">Support Forum</a></span>
						</p>
					</div>
				</div>
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
	public function rtmedia_3rd_party_themes_content(){
		?>
		<div class="row">
			<div class="columns large-12">
				<h4 class="rtmedia-theme-warning"><?php _e( 'These are the third party themes. For any issues or queries regarding these themes please contact theme developers.', 'rtmedia' ) ?></h4>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="columns large-4 rtmedia-theme-image">
				<a href="http://rt.cx/klein" target="_blank"><img src="<?php echo RTMEDIA_URL . 'app/assets/img/rtmedia-theme-klein.jpg' ?>"/></a>
			</div>
			<div class="columns large-7">
				<h3 class="rtmedia-theme-3rd-party-title"><a href="http://rt.cx/klein" target="_blank">Klein</a></h3>

				<div>
					<span><?php _e( 'Klein is an innovative WordPress theme built to support BuddyPress, bbPress, and WooCommerce out of the box. Perfect for websites that interacts with many users.', 'rtmedia' ); ?></span>
				</div>
				<div>
					<h4><?php echo __( 'Click', 'rtmedia' ) . " <a href='http://rt.cx/klein' target='_blank'>" . __( 'here', 'rtmedia' ) . '</a> ' . __( 'for preview.', 'rtmedia' ); ?></h4>
				</div>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="columns large-4 rtmedia-theme-image">
				<a href="http://rt.cx/sweetdate" target="_blank"><img src="<?php echo RTMEDIA_URL . 'app/assets/img/rtmedia-theme-sweetdate.png' ?>"/></a>
			</div>
			<div class="columns large-7">
				<h3 class="rtmedia-theme-3rd-party-title"><a href="http://rt.cx/sweetdate" target="_blank">Sweet Date</a></h3>

				<div>
					<span><?php _e( 'SweetDate is a unique, clean and modern Premium Wordpress theme. It is perfect for a dating or community website but can be used as well for any other domain. They added all the things you need to create a perfect community system.', 'rtmedia' ); ?></span>
				</div>
				<div>
					<h4><?php echo __( 'Click', 'rtmedia' ) . " <a href='http://rt.cx/sweetdate' target='_blank'>" . __( 'here', 'rtmedia' ) . '</a> ' . __( 'for preview.', 'rtmedia' ); ?></h4>
				</div>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="columns large-4 rtmedia-theme-image">
				<a href="http://rt.cx/kleo" target="_blank"><img src="<?php echo RTMEDIA_URL . 'app/assets/img/rtmedia-theme-kleo.png' ?>"/></a>
			</div>
			<div class="columns large-7">
				<h3 class="rtmedia-theme-3rd-party-title"><a href="http://rt.cx/kleo" target="_blank">KLEO</a></h3>

				<div>
					<span><?php _e( 'You no longer need to be a professional developer or designer to create an awesome website. Let your imagination run wild and create the site of your dreams. KLEO has all the tools to get you started.', 'rtmedia' ); ?></span>
				</div>
				<div>
					<h4><?php echo __( 'Click', 'rtmedia' ) . " <a href='http://rt.cx/kleo' target='_blank'>" . __( 'here', 'rtmedia' ) . '</a> ' . __( 'for preview.', 'rtmedia' ); ?></h4>
				</div>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="columns large-12">
				<h3><?php _e( 'Are you a developer?', 'rtmedia' ); ?></h3>

				<p><?php _e( 'If you have developed a rtMedia compatible theme and would like it to list here, please email us at', 'rtmedia' ) ?>
					<a href="mailto:product@rtcamp.com"><?php _e( 'product@rtcamp.com', 'rtmedia' ) ?></a>.</p>
			</div>
		</div>
	<?php
	}
}