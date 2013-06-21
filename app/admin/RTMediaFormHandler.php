<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaFormHandler
 *
 * @author udit
 */
class RTMediaFormHandler {

	public static function checkbox($args) {

		global $rt_media;
		$options = $rt_media->options;
		$defaults = array(
			'key' => '',
			'desc' => '',
			'show_desc' => false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (!isset($value)) {
			trigger_error(__('Please provide "value" in the argument.', 'rt-media'));
			return;
		}

		if (!empty($key)) {
			$args['name'] = 'rt-media-options[' . $key . ']';
		}

		$args['rtForm_options'] = array(array('' => 1, 'checked' => $value));

		$chkObj = new rtForm();
//		echo $chkObj->get_checkbox($args);
		echo $chkObj->get_switch($args);
//		echo $chkObj->get_switch_square($args);
	}

	public static function radio($args) {

		global $rt_media;
            $options = $rt_media->options;
		$defaults = array(
			'key' => '',
			'radios' => array(),
			'default' => '',
			'show_desc' => false
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (2 > count($radios)) {
			trigger_error(__('Need to specify atleast to radios else use a checkbox instead', 'rt-media'));
			return;
		}

		if (!empty($key))
			$args['name'] = 'rt-media-options[' . $key . ']';

		$args['rtForm_options'] = array();
		foreach ($radios as $value => $key) {
			$args['rtForm_options'][] = array(
				$key => $value,
				'checked' => ($default == $value) ? true : false
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
		global $rt_media;
		$options = $rt_media->options;
		$defaults = array(
			'key' => '',
			'desc' => ''
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (!isset($value)) {
			trigger_error(__('Please provide "value" in the argument.', 'rt-media'));
			return;
		}

		if (!empty($key)) {
			$args['name'] = 'rt-media-options[' . $key . ']';
		}

		$args['value'] = $value;

		$numObj = new rtForm();
		echo $numObj->get_number($args);
	}

	public static function general_content($page='') {
		global $rt_media;

		$options = $rt_media->options['rt-media-general'];

		foreach ($options as $key => $option) { ?>
			<div class="row section">
				<div class="columns large-2"> <?php echo $option['title']; ?> </div>
				<div class="columns large-4">
					<?php call_user_func($option['callback'], $option['args']); ?>
				</div>
			</div>
			<div class="clearfix">&nbsp;</div>
		<?php }
	}

	public static function types_content() {

		global $rt_media;

		$options = $rt_media->options['rt-media-allowed-types'];
?>
		<div class="rt-table large-12">
			<div class="row rt-header">
				<h4 class="columns large-2"><?php echo __("Media Type","rt-media") ?></h4>
				<h4 class="columns large-2 rtm-show-tooltip" title="<?php echo __("Allows you to upload a particular media type on your post.","rt-media"); ?>"><abbr><?php echo __("Allow Upload","rt-media"); ?></abbr></h4>
				<h4 class="columns large-2 rtm-show-tooltip" title="<?php echo __("Put a specific media as a featured content on the post.","rt-media"); ?>"><abbr><?php echo __("Set Featured","rt-media"); ?></abbr></h4>
				<h4 class="columns large-3 rtm-show-tooltip" title="<?php echo __("File extensions that can be uploaded on the website.","rt-media"); ?>"><abbr><?php echo __("File Extensions","rt-media"); ?></abbr></h4>
			</div>
<?php
		$even = 0;
		foreach ($options as $key=>$section) {
			if( ++$even%2 )
				echo '<div class="row rt-odd">';
			else
				echo '<div class="row rt-even">';

				echo '<div class="columns large-2">' . $section['name'] . '</div>';
				$args = array('key' => 'rt-media-allowed-types]['.$key.'][enable', 'value' => $section['enable']);
				echo '<div class="columns large-2">';
					self::checkbox($args);
				echo '</div>';
				$args = array('key' => 'rt-media-allowed-types]['.$key.'][featured', 'value' => $section['featured']);
				echo '<div class="columns large-2">';
					self::checkbox($args);
				echo '</div>';
				echo '<div class="columns large-3">' . implode(',', $section['extn']) . '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	public static function sizes_content() {

		global $rt_media;

		$options = $rt_media->options['rt-media-allowed-sizes'];

		//container
		echo '<div class="rt-table large-12">';

		//header
		echo '<div class="rt-header row">';
			echo '<h4 class="columns large-3">' . __("Category","rt-media") . '</h4>';
			echo '<h4 class="columns large-3">' . __("Entity","rt-media") . '</h4>';
			echo '<h4 class="columns large-4"><span class="large-offset-2">' . __("Width","rt-media") . '</span><span class="large-offset-2">' . __("Height","rt-media") . '</span><span class="large-offset-2">' . __("Crop","rt-media") . '</span></h4>';
		echo'</div>';

		//body
		$even = 0;
		foreach ($options as $parent_key => $section) {
			if( ++$even%2 )
				echo '<div class="row rt-odd">';
			else
				echo '<div class="row rt-even">';
			echo '<div class="columns large-3">' . $section['title'] . '</div>';
			$entities = $section;
			unset($entities['title']);
			echo '<div class="columns large-3">';
			foreach ($entities as $entity) {
				echo '<div class="row">' . $entity['title'] . '</div>';
			}
			echo '</div>';
			echo '<div class="columns large-4">';
			foreach ($entities as $child_key=>$entity) {
				$args = array(
					'key' => 'rt-media-allowed-sizes]['.$parent_key.']['.$child_key,
				);
				foreach ($entity['dimensions'] as $prop => $value) {
					$args[$prop] = $value;
				}
				self::dimensions($args);
			}
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}

	public static function privacy_content() {

		global $wp_settings_fields;

		global $rt_media;

		$options = $rt_media->options['rt-media-privacy'];

		echo '<div class="large-12">';
			foreach ($options as $key=>$privacy) {
				echo '<div class="row section">';
					echo '<div class="columns large-2">' . $privacy['title'] . '</div>';
					echo '<div class="columns large-5">';
						if($key != "enable")
							call_user_func($privacy['callback'], array_merge_recursive($privacy['args'], array('class' => array("privacy-driven-disable"))));
						else
							call_user_func($privacy['callback'], $privacy['args']);
					echo '</div>';
				echo '</div>';
			}
		echo '</div>';
	}

	public static function buddypress_content($page='') {

		global $rt_media;

		$options = $rt_media->options['rt-media-buddypress'];

		echo '<div class="large-12">';
		foreach ($options as $option) { ?>
			<div class="row section">
				<div class="columns large-2"><?php echo $option['title']; ?></div>
				<div class="columns large-4">
					<?php call_user_func($option['callback'], $option['args']); ?>
				</div>
			</div>
		<?php }
		echo '</div>';
	}

	public static function rtForm_settings_tabs_content($page, $sub_tabs) {

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
