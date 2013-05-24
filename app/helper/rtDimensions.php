<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtfDimension
 *
 * @author udit
 */
class rtDimensions extends rtForm {

	private $element_id;
	
	private static $id_count = 0;
	
	private static $default_class = "rt-form-dimension";

	private function get_default_id() {
		return self::$id_count;
	}

	private function update_default_id() {
		self::$id_count ++;
	}

	private function get_default_class() {
		return self::$default_class;
	}
	
	private function embedd_class( $element, $class = null ) {

		$html = 'class = "' . $this->get_default_class();

		if( isset( $class ) ) {

			if( is_array( $class ) )
				$html .= ' ' . implode(" ", $class);
			else
				throw new rtFormsInvalidArgumentsException( "class [". $element ."]" );
		}
		$html .= '"';

		return $html;
	}


	protected function generate_dimensions( $attributes ) {

		$element = "rtDimension";
		global $bp_media;
		$defaults = array(
			'type' => 'image',
			'size' => 'thumbnail',
			'height' => true,
			'crop' => false,
			'desc' => '',
			'show_desc' => false
		);

		$attributes = wp_parse_args($attributes, $defaults);
		extract($attributes);

		$options = bp_get_option('bp_media_options');

		$w = $options['sizes'][$type][$size]['width'];
		if ($height) {
			$h = $options['sizes'][$type][$size]['height'];
		}
		if ($crop) {
			$c = $options['sizes'][$type][$size]['crop'];
		}
		
		$html = '<div ';

		if( isset($attributes['id']) )
			$html .= 'id="' . $attributes['id'] . '" ';
		else {
			$html .= 'id="' . $this->get_default_class () . '-' . $this->get_default_id () . '" ';
			$this->update_default_id();
		}

		if( isset($attributes['class']) )
			$html .= self::embedd_class($element, $attributes['class']);
		else
			$html .= self::embedd_class($element);
		$html .= '>';

		$html .= parent::get_number(array(
			'id' => sanitize_title("{$type}_{$size}_w"),
			'name' => "bp_media_options[sizes][$type][$size][width]",
//			'label' => __('Width', 'buddypress-media'),
			'value' => $w,
			'class' => array("small-text large-offset-1"),
			'show_desc' => $show_desc
		));

		if ($height) {
			$html .= parent::get_number(array(
				'id' => sanitize_title("{$type}_{$size}_h"),
				'name' => "bp_media_options[sizes][$type][$size][height]",
//				'label' => __('Height', 'buddypress-media'),
				'value' => $h,
				'class' => array("small-text large-offset-1"),
				'show_desc' => $show_desc
			));
		}

		if($crop) {
			$html .= parent::get_checkbox(array(
				'id' => sanitize_title("{$type}_{$size}_c"),
				'name' => "bp_media_options[sizes][$type][$size][crop]",
				'rtForm_options' => array(array(
					'' => 1,
//					__('Crop', 'buddypress-media') => 1,
					'checked' => $c
				)),
				'class' => array("large-offset-1"),
				'show_desc' => $show_desc
			));
		}

		if ($desc && $show_desc)
			$html .= '<span class="clearfix large-offset-3 description">' . $desc . '</span>';

		$html .= '</div>';

		if( isset($attributes['label']) )
			$html = parent::enclose_label('container', $html, $attributes['label']);
		
		return $html;
	}

	public function get_dimensions( $attributes = '' ) {

		return $this->generate_dimensions($attributes);
	}
}

?>
