<?php
/**
 * File to include class rtDimensions
 *
 * @package rtMedia
 */

/**
 * Class to handle rtMedia dimensions
 *
 * @author udit
 */
class rtDimensions extends rtForm { // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

	/**
	 * Element ID.
	 *
	 * @var $element_id
	 */
	private $element_id;

	/**
	 * ID count.
	 *
	 * @var int $id_count
	 */
	private static $id_count = 0;

	/**
	 * Default class.
	 *
	 * @var string $default_class
	 */
	private static $default_class = 'rt-form-dimension';

	/**
	 * Get default html id count.
	 *
	 * @access private
	 *
	 * @return int $id_count
	 */
	private function get_default_id() {
		return self::$id_count;
	}

	/**
	 * Update default html id count.
	 *
	 * @access private
	 */
	private function update_default_id() {
		self::$id_count ++;
	}

	/**
	 * Get default html class.
	 *
	 * @access private
	 *
	 * @return string $default_class
	 */
	private function get_default_class() {
		return self::$default_class;
	}


	/**
	 * Embed html class to html output.
	 *
	 * @access private
	 *
	 * @param string $element element.
	 * @param null   $class class.
	 *
	 * @return string
	 *
	 * @throws rtFormsInvalidArgumentsException Form invalid argument exception.
	 */
	private function embedd_class( $element, $class = null ) {
		$html = 'class= "' . $this->get_default_class();

		if ( isset( $class ) ) {

			if ( is_array( $class ) ) {
				$html .= ' ' . implode( ' ', $class );
			} else {
				throw new rtFormsInvalidArgumentsException( 'class [' . $element . ']' );
			}
		}
		$html .= '"';

		return $html;
	}

	/**
	 * Generate rtmedia dimensions in admin options.
	 *
	 * @access protected
	 *
	 * @param  array $attributes Attributes.
	 *
	 * @return string $html
	 */
	protected function generate_dimensions( $attributes ) {
		$element = 'rtDimension';
		global $rtmedia;
		$options  = $rtmedia->options;
		$defaults = array(
			'desc'      => '',
			'show_desc' => false,
		);

		$attributes = wp_parse_args( $attributes, $defaults );

		$html = '';

		$html .= '<td>' .
				parent::get_number(
					array(
						'name'      => "rtmedia-options[{$attributes['key']}_width]",
						'value'     => $attributes['width'],
						'class'     => array( 'small-text large-offset-1' ),
						'show_desc' => $attributes['show_desc'],
					)
				) .
				'</td>';

		if ( isset( $attributes['height'] ) ) {
			$html .= '<td>' .
					parent::get_number(
						array(
							'name'      => "rtmedia-options[{$attributes['key']}_height]",
							'value'     => $attributes['height'],
							'class'     => array( 'small-text large-offset-1' ),
							'show_desc' => $attributes['show_desc'],
						)
					) .
					'</td>';
		}

		if ( isset( $attributes['crop'] ) ) {
			$html .= '<td>' .
					parent::get_switch(
						array(
							'name'           => "rtmedia-options[{$attributes['key']}_crop]",
							'rtForm_options' => array(
								array(
									''        => 1, // label would be blank.
									'checked' => $attributes['crop'],
								),
							),
							'value'          => ( isset( $options[ "rtmedia-options[{$attributes['key']}_crop]" ] ) ) ? $options[ "rtmedia-options[{$attributes['key']}_crop]" ] : '0',
							'show_desc'      => $attributes['show_desc'],
						)
					) .
					'</td>';
		}

		if ( $attributes['desc'] && $attributes['show_desc'] ) {
			$html .= '<span class="clearfix large-offset-3 description">' . esc_html( $attributes['desc'] ) . '</span>';
		}

		if ( isset( $attributes['label'] ) ) {
			$html = parent::enclose_label( 'container', $html, $attributes['label'] );
		}

		return $html;
	}

	/**
	 * Get rtmedia dimensions in admin options.
	 *
	 * @access public
	 *
	 * @param  mixed $attributes Attribute.
	 *
	 * @return string|null
	 */
	public function get_dimensions( $attributes = '' ) {
		return $this->generate_dimensions( $attributes );
	}

	/**
	 * Display dimensions.
	 *
	 * @param string $args Arguments.
	 */
	public function display_dimensions( $args = '' ) {
		echo wp_kses( $this->get_dimensions( $args ), RTMedia::expanded_allowed_tags() );
	}

}
