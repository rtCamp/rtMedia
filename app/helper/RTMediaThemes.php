<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaThemes
 *
 * @author ritz
 */
class RTMediaThemes {

    public static function render_themes($page = '') {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
				return;

			foreach ( (array) $wp_settings_sections[$page] as $section ) {

				if ( $section['callback'] )
					call_user_func( $section['callback'], $section );

				if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
					continue;

				echo '<table class="form-table">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

    public function get_themes() {

			$tabs = array();
			global $rtmedia_admin;
			$tabs[] = array(
				'title' => __('rtMedia Themes By rtCamp', 'rtmedia'),
				'name' => __('rtMedia Themes By rtCamp', 'rtmedia'),
				'href' => '#rtmedia-themes',
				'callback' => array($this, 'rtmedia_themes_content')
			);
			$tabs[] = array(
				'title' => __('3rd Party Themes', 'rtmedia'),
				'name' => __('3rd Party Themes', 'rtmedia'),
				'href' => '#rtmedia-themes-3',
				'callback' => array($this, 'rtmedia_3rd_party_themes_content')
			);
		?>
			<div id="rtm-themes">
				<ul>
					<?php
						foreach ($tabs as $tab) {?>
							<li><a id="tab-<?php echo substr ( $tab[ 'href' ], 1 ); ?>" title="<?php echo $tab['title'] ?>" href="<?php echo $tab['href']; ?>" class="rtmedia-tab-title"><?php echo $tab['name']; ?></a></li>
						<?php }
					?>
				</ul>

				<?php
					foreach ($tabs as $tab) {
						echo '<div id="' . substr($tab['href'],1) . '">';
							call_user_func($tab['callback']);
						echo '</div>';
					}
				?>
			</div>
			<?php
        }

	function rtmedia_themes_content() {
	?>
	    <div class="row">
		<div class="columns large-12">
		    <h3><?php _e('Coming Soon...','rtmedia'); ?></h3>
		    <p><?php _e('We are working on some rtMedia themes which will be available shortly.','rtmedia'); ?></p>
		</div>
	    </div>
	<?php
	}

	function rtmedia_3rd_party_themes_content() {
	?>
	    <div class="row">
		<h4 class="rtmedia-theme-warning"><?php _e('These are the third party themes. For any issues or queries regarding these themes please contact theme developers.','rtmedia') ?></h4>
	    </div>
	    <hr>
	    <div class="row">
		<div class="columns large-4 rtmedia-theme-image"><a href="http://rt.cx/sweetdate" target="_blank"><img src="<?php echo RTMEDIA_URL.'app/assets/img/rtmedia-theme-sweetdate.png' ?>" /></a> </div>
		<div class="columns large-7">
		    <h3 class="rtmedia-theme-3rd-party-title"><a href="http://rt.cx/sweetdate" target="_blank">Sweet Date</a></h3>
		    <div><span><?php _e('SweetDate is a unique, clean and modern Premium Wordpress theme. It is perfect for a dating or community website but can be used as well for any other domain. They added all the things you need to create a perfect community system.','rtmedia'); ?></span></div>
		    <div><h4><?php echo __('Click','rtmedia') . " <a href='http://rt.cx/sweetdate' target='_blank'>".__('here','rtmedia')."</a> ". __('for preview.','rtmedia'); ?></h4></div>
		</div>
	    </div>
	    <hr>
	    <div class="row">
		<h3><?php _e('Are you a developer?','rtmedia'); ?></h3>
		<p><?php  _e('If you have developed a rtMedia compatible theme and would like it to list here, please email us at','rtmedia') ?> <a href="mailto:product@rtcamp.com"><?php _e('product@rtcamp.com','rtmedia') ?></a>.</p>
	    </div>
	<?php
	}
}