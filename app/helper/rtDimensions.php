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

	/**
	 * Get default html id count.
	 *
	 * @access private
	 * @param  void
	 * @return int $id_count
	 */
	private function get_default_id () {
		return self::$id_count;
	}

	/**
	 * Update default html id count.
	 *
	 * @access private
	 * @param  void
	 * @return int $id_count
	 */
	private function update_default_id () {
		self::$id_count ++;
	}

	/**
	 * Get default html class.
	 *
	 * @access private
	 * @param  void
	 * @return string $default_class
	 */
	private function get_default_class () {
		return self::$default_class;
	}

	/**
	 * Embedd html class to html output.
	 *
	 * @access private
	 * @param  string $element
	 * @param  array  $class
	 * @return string $html
	 */
	private function embedd_class ( $element, $class = null ) {
		$html = 'class = "' . $this->get_default_class ();

		if ( isset ( $class ) ){

			if ( is_array ( $class ) ){
				$html .= ' ' . implode ( " ", $class );
			} else {
				throw new rtFormsInvalidArgumentsException ( "class [" . $element . "]" );
			}
		}
		$html .= '"';

		return $html;
	}

	/**
	 * Generate rtmedia dimensions in admin options.
	 *
	 * @access protected
	 * @param  array  $attributes
	 * @return string $html
	 */
	protected function generate_dimensions ( $attributes ) {
		$element = "rtDimension";
		global $rtmedia;
		$defaults = array(
		    'desc' => '',
		    'show_desc' => false
		);

		$attributes = wp_parse_args ( $attributes, $defaults );
		extract ( $attributes );

		$html = '<div ';

		if ( isset ( $attributes[ 'id' ] ) ){
			$html .= 'id="' . $attributes[ 'id' ] . '" ';
		} else {
			$html .= 'id="' . $this->get_default_class () . '-' . $this->get_default_id () . '" ';
			$this->update_default_id ();
		}

		if ( isset ( $attributes[ 'class' ] ) ){
			$html .= self::embedd_class ( $element, $attributes[ 'class' ] );
		} else {
			$html .= self::embedd_class ( $element );
		}
		$html .= '>';

		$html .= parent::get_textbox ( array(
		            'name' => "rtmedia-options[{$key}_width]",
		            'value' => $width,
		            'class' => array( "small-text large-offset-1" ),
		            'show_desc' => $show_desc
		) );

		if ( isset ( $height ) ){
			$html .= parent::get_textbox ( array(
			            'name' => "rtmedia-options[{$key}_height]",
			            'value' => $height,
			            'class' => array( "small-text large-offset-1" ),
			            'show_desc' => $show_desc
			) );
		}

		if ( isset ( $crop ) ){
			$html .= parent::get_checkbox ( array(
			            'name' => "rtmedia-options[{$key}_crop]",
			            'rtForm_options' => array( array(
			                    '' => 1, //label would be blank
			                    'checked' => $crop
			                ) ),
			            'class' => array( "large-offset-1" ),
			            'show_desc' => $show_desc
			) );
		}

		if ( $desc && $show_desc ){
			$html .= '<span class="clearfix large-offset-3 description">' . $desc . '</span>';
		}

		$html .= '</div>';

		if ( isset ( $attributes[ 'label' ] ) ){
			$html = parent::enclose_label ( 'container', $html, $attributes[ 'label' ] );
		}

		return $html;
	}

	/**
	 * Get rtmedia dimensions in admin options.
	 *
	 * @access public
	 * @param  mixed  $attributes
	 * @return void
	 */
	public function get_dimensions ( $attributes = '' ) {
		return $this->generate_dimensions ( $attributes );
	}

}
