<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaFormHandler
 *
 * @author udit
 */
class BPMediaFormHandler {

	public static function checkbox($args) {

		global $bp_media;
		$options = $bp_media->options;
		$defaults = array(
			'setting' => '',
			'option' => '',
			'desc' => '',
			'show_desc' => false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (empty($option)) {
			trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' ) ', 'buddypress-media'));
			return;
		}

		if (!empty($setting)) {
			$args['name'] = $setting . '[' . $option . ']';
			$options = bp_get_option($setting);
		}
		else
			$args['name'] = $option;

		if (!isset($options[$option]))
			$options[$option] = '';

		$args['rtForm_options'] = array(array('id' => $option, '' => 1, 'checked' => $options[$option]));

		$chkObj = new rtForm();
//		echo $chkObj->get_checkbox($args);
		echo $chkObj->get_switch($args);
//		echo $chkObj->get_switch_square($args);
	}

	public static function radio($args) {

		global $bp_media;
            $options = $bp_media->options;
		$defaults = array(
			'setting' => '',
			'option' => '',
			'radios' => array(),
			'default' => '',
			'show_desc' => false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);
		if (empty($option) || ( 2 > count($radios) )) {
			if (empty($option))
				trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'buddypress-media'));
			if (2 > count($radios))
				trigger_error(__('Need to specify atleast to radios else use a checkbox instead', 'buddypress-media'));
			return;
		}

		if (!empty($setting)) {
			$args['name'] = $setting . '[' . $option . ']';
			$options = bp_get_option($setting);
		}
		else
			$args['name'] = $option;

		if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
			$options[$option] = $default;
		}

		$args['rtForm_options'] = array();
		foreach ($radios as $value => $key) {
			$args['rtForm_options'][] = array(
				'id' => sanitize_title($key),
				$key => $value,
				'checked' => ($options[$option] == $value) ? true : false
			);
		}

		$objRad = new rtForm();
		echo $objRad->get_radio($args);
	}

	public static function dimensions($args) {

		$dmnObj = new rtDimensions();
		echo $dmnObj->get_dimensions($args);
	}

	public static function number($args) {
		global $bp_media;
		$options = $bp_media->options;
		$defaults = array(
			'setting' => '',
			'option' => '',
			'desc' => '',
			'password' => false,
			'hidden' => false,
			'number' => false,
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (empty($option)) {
			trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'buddypress-media'));
			return;
		}

		if (!empty($setting)) {
			$args['name'] = $setting . '[' . $option . ']';
			$options = bp_get_option($setting);
		}
		else
			$args['name'] = $option;

		if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
			$options[$option] = '';
		}

		$args['id'] = sanitize_title($option);
		$args['value'] = $options[$option];

		$numObj = new rtForm();
		echo $numObj->get_number($args);
	}

	public static function types_content($page = '') {

		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_fields) ||
				!isset($wp_settings_fields[$page]) ||
				!isset($wp_settings_fields[$page]['bpm-settings']) ||
				!isset($wp_settings_fields[$page]['bpm-featured']))
			return;

		$bpm_settings = $wp_settings_fields[$page]['bpm-settings'];
		$bpm_featured = $wp_settings_fields[$page]['bpm-featured'];
		$headers = array(
			array(
				'id' => 'bpm-media-type',
				'title' => "Media Type",
				'class' => 'large-2',
				'desc' => ''
			),
			array(
				'id' => 'bpm-allow-upload',
				'title' => "Allow Upload",
				'class' => 'large-2',
				'desc' => 'Allows you to upload a particular media type on your post.'
			),
			array(
				'id' => 'bpm-set-feature',
				'title' => "Set Featured",
				'class' => 'large-2',
				'desc' => 'Put a specific media as a featured content on the post.'
			),
			array(
				'id' => 'bpm-file-extn',
				'title' => "File Extensions",
				'class' => 'large-3',
				'desc' => 'File extensions that can be uploaded on the website.'
			)
		);

		$image = array(
			array(
				'class' => 'large-2',
				'content' => $bpm_settings['bpm-image']['title']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_settings['bpm-image']['callback'],
				'args' => $bpm_settings['bpm-image']['args']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_featured['bpm-featured-image']['callback'],
				'args' => $bpm_featured['bpm-featured-image']['args']
			),
			array(
				'class' => 'large-3',
				'content' => "gif,jpeg,png"
			),
		);

		$video = array(
			array(
				'class' => 'large-2',
				'content' => $bpm_settings['bpm-video']['title']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_settings['bpm-video']['callback'],
				'args' => $bpm_settings['bpm-video']['args']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_featured['bpm-featured-video']['callback'],
				'args' => $bpm_featured['bpm-featured-video']['args']
			),
			array(
				'class' => 'large-3',
				'content' => "avi,mp4,mpeg"
			),
		);

		$audio = array(
			array(
				'class' => 'large-2',
				'content' => $bpm_settings['bpm-audio']['title']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_settings['bpm-audio']['callback'],
				'args' => $bpm_settings['bpm-audio']['args']
			),
			array(
				'class' => 'large-2',
				'callback' => $bpm_featured['bpm-featured-audio']['callback'],
				'args' => $bpm_featured['bpm-featured-audio']['args']
			),
			array(
				'class' => 'large-3',
				'content' => "mp3,wav"
			),
		);

		$body = array($image, $video, $audio);

		//container
		echo '<div class="rt-table large-12">';

		//header
		$tooltip_ids = '';
		echo '<div class="row rt-header">';
		foreach ($headers as $val) {
			if( isset($val['desc']) && !empty($val['desc']) ) {
				echo '<h4 id="' . $val['id'] . '" class="columns ' . $val['class'] . '" title="' . $val['desc'] . '"><abbr>' . $val['title'] . '</abbr></h4>';
				$tooltip_ids .= '#' . $val['id'] . ',';
			}
			else
				echo '<h4 " class="columns ' . $val['class'] . '">' . $val['title'] . '</h4>';
		}
		echo '</div>';
		
		$tooltip_ids = substr($tooltip_ids, 0, strlen($tooltip_ids)-1);

		//body
		$even = 0;
		foreach ($body as $section) {
			if( ++$even%2 )
				echo '<div class="row rt-odd">';
			else
				echo '<div class="row rt-even">';

			foreach ($section as $value) { ?>
				<div class="columns<?php echo ' ' . $value['class']; ?>">
				<?php
					if (isset($value['content']))
						echo $value['content'];
					else
						call_user_func($value['callback'], $value['args']);
				?>
				</div>
			<?php
			} ?>
			</div>
		<?php
		}
		?>
		</div>
		<script type="text/javascript">			
			var tooltip_ids = '<?php echo $tooltip_ids;?>';
		</script>
		<?php
	}

	public static function sizes_content($page = '') {

		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_fields) ||
				!isset($wp_settings_fields[$page]) ||
				!isset($wp_settings_fields[$page]['bpm-image-settings']) ||
				!isset($wp_settings_fields[$page]['bpm-video-settings']) ||
				!isset($wp_settings_fields[$page]['bpm-audio-settings']) ||
				!isset($wp_settings_fields[$page]['bpm-featured']))
			return;

		$dimension = '<span class="large-offset-1">Width</span>
					<span class="large-offset-2">Height</span>
					<span class="large-offset-2">Crop</span>';
		$headers = array(
			array('title' => 'Category', 'class' => 'large-3'),
			array('title' => 'Entity', 'class' => 'large-3'),
			array('title' => $dimension, 'class' => 'large-4')
		);

		$sections = array("bpm-image-settings", "bpm-video-settings", "bpm-audio-settings", "bpm-featured");

		$contents = array();
		$body = array();
		foreach ($sections as $section) {

			$contents[$section] = array(
				'entity_names' => array(),
				'callbacks' => array(),
				'args' => array()
			);

			if ($section == "bpm-featured") {
				$contents[$section]['entity_names'][] = $wp_settings_fields[$page][$section]['bpm-featured-media-dimensions']['title'];
				$contents[$section]['callbacks'][] = $wp_settings_fields[$page][$section]['bpm-featured-media-dimensions']['callback'];
				$contents[$section]['args'][] = $wp_settings_fields[$page][$section]['bpm-featured-media-dimensions']['args'];
			} else {
				foreach ($wp_settings_fields[$page][$section] as $value) {
					$contents[$section]['entity_names'][] = $value['title'];
					$contents[$section]['callbacks'][] = $value['callback'];
					$contents[$section]['args'][] = $value['args'];
				}
			}

			$body[$section] = array(
				//title
				array(
					'class' => 'large-3',
					'content' => ( $section == "bpm-featured" ) ? "Featured Media" : $wp_settings_sections[$page][$section]['title']
				),
				//entity names
				array(
					'class' => 'large-3',
					'content' => ( $section == "bpm-featured" ) ? $wp_settings_fields[$page][$section]['bpm-featured-media-dimensions']['title'] : $contents[$section]['entity_names']
				),
				//dimensions
				array(
					'class' => 'large-4',
					'callbacks' => $contents[$section]['callbacks'],
					'args' => $contents[$section]['args']
				)
			);
		}


		//container
		echo '<div class="rt-table large-12">';

		//header
		echo '<div class="rt-header row">';
		foreach ($headers as $value) {
			echo '<h4 class="columns ' . $value['class'] . '">' . $value['title'] . '</h4>';
		}
		echo'</div>';

		//body
		$even = 0;
		foreach ($body as $section) {
			if( ++$even%2 )
				echo '<div class="row rt-odd">';
			else
				echo '<div class="row rt-even">';

			foreach ($section as $value) {
				echo '<div class="columns ' . $value['class'] . '">';
				if (isset($value['content'])) {
					if (is_array($value['content'])) {
						foreach ($value['content'] as $entity) {
							echo '<div class="entity section">';
							echo $entity;
							echo '</div>';
						}
					}
					else
						echo $value['content'];
				} else {
					for ($i = 0; $i < count($value['callbacks']); $i++) {
						echo '<div class="section">';
						call_user_func($value['callbacks'][$i], $value['args'][$i]);
						echo '</div>';
					}
				}
				echo '</div>';
			}
			echo '</div>';
		}

		echo '</div>';
	}

	public static function privacy_content($page = '') {

		global $wp_settings_fields;

		if (!isset($wp_settings_fields) ||
				!isset($wp_settings_fields[$page]) ||
				!isset($wp_settings_fields[$page]['bpm-privacy']))
			return;

		echo '<div class="large-12">';
			foreach ($wp_settings_fields[$page]['bpm-privacy'] as $key => $value) {
				echo '<div class="row section" id="' . $key . '">';
					echo '<div class="columns large-2">' . $value['title'] . '</div>';
					echo '<div class="columns large-5">';
						if($key != "bpm-privacy-enabled")
							call_user_func($value['callback'], array_merge_recursive($value['args'], array('class' => array("privacy-driven-disable"))));
						else
							call_user_func($value['callback'], $value['args']);
					echo '</div>';
				echo '</div>';
			}
		echo '</div>';
	}

	public static function misc_content($page = '') {

		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_fields) ||
				!isset($wp_settings_fields[$page]) ||
				!isset($wp_settings_fields[$page]['bpm-activity-upload']) ||
				!isset($wp_settings_fields[$page]['bpm-media-lightbox']) ||
				!isset($wp_settings_fields[$page]['bpm-media-fine']) ||
				!isset($wp_settings_fields[$page]['bpm-miscellaneous']) )
			return;

		$sections = array("bpm-activity-upload","bpm-media-lightbox","bpm-media-fine","bpm-miscellaneous");

		echo '<div class="large-12">';
		foreach ($sections as $section) {
			echo '<h3>' . $wp_settings_sections[$page][$section]['title'] . '</h3>';
			foreach ($wp_settings_fields[$page][$section] as $value) { ?>
				<div class="row section">
					<div class="columns large-2"> <?php echo $value['title']; ?> </div>
					<div class="columns large-4">
						<?php call_user_func($value['callback'], $value['args']); ?>
					</div>
				</div>
			<?php }
			echo '<div class="clearfix">&nbsp;</div>';
		}
		echo '</div>';
	}

	public static function rtForm_settings_tabs_content($page, $sub_tabs) {

		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page]))
			return;

		foreach ($sub_tabs as $tab) {
			echo '<div id="' . substr($tab['href'], 1) . '">';
				call_user_func($tab['callback'], $page);
			echo '</div>';
		}

//		echo "<pre>";
//		print_r($wp_settings_sections);
//		echo "<br>---------------------------------------------------------------------------<br><br>";
//		print_r($wp_settings_fields);
//		echo "</pre>";

//		echo '<div class="small-11 small-centered columns">';
//			foreach ( (array) $wp_settings_sections[$page] as $section ) {
//				if ( $section['title'] )
//					echo "<div><h3>{$section['title']}</h3>";
//
//				if ( $section['callback'] )
//						call_user_func( $section['callback'], $section );
//
//				if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
//					continue;
//				echo '<div class="row small-11 small-centered columns">';
//					self::rtForm_do_settings_fields( $page, $section['id'] );
//				echo '</div></div>';
//			}
//		echo '</div>';
	}

	public static function rtForm_do_settings_fields($page, $section) {
		global $wp_settings_fields;

		if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]))
			return;

		foreach ((array) $wp_settings_fields[$page][$section] as $field) {
			echo '<div class="row">';
			echo '<div class="large-11 columns">';

			if (isset($field['args']['label_for']) && !empty($field['args']['label_for']))
				call_user_func($field['callback'], array_merge($field['args'], array('label' => $field['args']['label_for'])));
			else if (isset($field['title']) && !empty($field['title']))
				call_user_func($field['callback'], array_merge($field['args'], array('label' => $field['title'])));
			else
				call_user_func($field['callback'], $field['args']);
			echo '</div>';
			echo '</div>';
		}
	}
}
?>
