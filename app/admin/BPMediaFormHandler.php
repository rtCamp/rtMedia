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
		);
		$args = wp_parse_args($args, $defaults);
		extract($args);

		if (empty($option)) {
			trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' ) ', 'buddypress-media'));
			return;
		}

		$args['id'] = $option;
		
		if (!empty($setting)) {
			$args['name'] = $setting . '[' . $option . ']';
			$options = bp_get_option($setting);
		} else
			$args['name'] = $option;

		if (!isset($options[$option]))
			$options[$option] = '';

		if(isset($desc))
			$args['rtForm_options'] = array(array($desc=>1, 'checked'=>$options[$option]));
		else
			$args['rtForm_options'] = array(array($label=>1, 'checked'=>$options[$option]));

		$args['class'] = array("large-offset-1");
		
		$chkObj = new rtForm();
		echo $chkObj->get_checkbox($args);
	}

	public static function dimensions($args) {

		$dmnObj = new rtDimensions();
		$args['label'] = "Dimensions";
		$args['class'] = array("large-offset-1");
		echo $dmnObj->get_dimensions($args);
	}
	
	public static function rtForm_do_settings_sections($page) {

		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
			return;

		echo '<div class="small-11 small-centered columns">';
			foreach ( (array) $wp_settings_sections[$page] as $section ) {
				if ( $section['title'] )
					echo "<div><h3>{$section['title']}</h3>";

				if ( $section['callback'] )
						call_user_func( $section['callback'], $section );

				if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
					continue;
				echo '<div class="row small-11 small-centered columns">';
					self::rtForm_do_settings_fields( $page, $section['id'] );
				echo '</div></div>';
			}
		echo '</div>';		
	}

	public static function rtForm_do_settings_fields($page, $section) {
		global $wp_settings_fields;

		if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
			return;

		foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
			echo '<div class="row">';
				echo '<div class="large-11 columns">';

					if( isset($field['args']['label_for']) )
						call_user_func($field['callback'], array_merge($field['args'],array('label' => $field['args']['label_for'])));
					else if( isset($field['title']) )
						call_user_func($field['callback'], array_merge($field['args'],array('label' => $field['title'])));
					else
						call_user_func($field['callback'], $field['args']);
				echo '</div>';
			echo '</div>';
//			echo '<div class="clearfix">&nbsp;</div>';
		}
	}	
}
?>
