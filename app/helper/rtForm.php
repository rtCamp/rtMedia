<?php
/**
 * File to include rtForm class
 *
 * @package    rtMedia
 */

if ( ! class_exists( 'rtForm' ) ) {

	/**
	 * Class to handle rtMedia form elements
	 *
	 * Usage Example :
	 *
	 * $obj = new rtForm();
	 *
	 *   ----textbox test
	 *   echo $obj->get_textbox(array(
	 *           "id"=>"myid",
	 *           "label" => "mylabel",
	 *           "name"=>"myname",
	 *           "value"=>"myval",
	 *           "class"=> array("myclass")
	 *   ))."\n";
	 *
	 *
	 *   ----textarea test
	 *   echo $obj->get_textarea(array(
	 *           "id"=>"myid",
	 *           "name"=>"myname",
	 *           "value"=>"myval",
	 *           "class"=> array("myclass")
	 *   ))."\n";
	 *
	 *
	 *   ----radio test
	 *   echo $obj->get_radio(array(
	 *           "id"=>"myid",
	 *           "name"=>"myname",
	 *           "class"=>array("myclass"),
	 *           "rtForm_options"=>array(
	 *                   "op1"=>1,
	 *                   "op2"=>2,
	 *                   "op3"=>3
	 *           )
	 *   ))."\n";
	 *   ----checkbox test
	 *   echo $obj->get_checkbox(array(
	 *           "id"=>"myid",
	 *           "name"=>"myname",
	 *           "class"=>array("myclass"),
	 *           "rtForm_options"=>array(
	 *                   "op1"=>1,
	 *                   "op2"=>2,
	 *                   "op3"=>3
	 *           )
	 *   ))."\n";
	 *   ----select test
	 *   echo $obj->get_select(array(
	 *           "id"=>"myid",
	 *           "name"=>"myname",
	 *           "class"=>array("myclass"),
	 *           "rtForm_options"=>array(
	 *                   "op1"=>1,
	 *                   "op2"=>2,
	 *                   "op3"=>3
	 *           )
	 *   ))."\n";
	 *
	 * @author udit
	 */
	class rtForm { // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

		/**
		 * Element ID.
		 *
		 * @var $element_id
		 */
		private $element_id;

		/**
		 * Default id counts
		 * if id for any element is not given then these count will be used in id generation
		 *
		 * @var array $id_counts
		 */
		private static $id_counts = array(
			'rtText'      => 0,
			'rtFile'      => 0,
			'rtNumber'    => 0,
			'rtDate'      => 0,
			'rtRadio'     => 0,
			'rtCheckbox'  => 0,
			'rtSelect'    => 0,
			'rtTextarea'  => 0,
			'rtHidden'    => 0,
			'rtWysiwyg'   => 0,
			'rtButton'    => 0,
			'rtFileInput' => 0,
			'rtLink'      => 0,
		);

		/**
		 * Default classes.
		 *
		 * @var array $default_classes
		 */
		private static $default_classes = array(
			'rtText'      => 'rtm-form-text',
			'rtFile'      => 'rtm-form-file',
			'rtNumber'    => 'rtm-form-number',
			'rtDate'      => 'rtm-form-date',
			'rtRadio'     => 'rtm-form-radio',
			'rtCheckbox'  => 'rtm-form-checkbox',
			'rtSelect'    => 'rtm-form-select',
			'rtTextarea'  => 'rtm-form-textarea',
			'rtHidden'    => 'rtm-form-hidden',
			'rtWysiwyg'   => 'rtm-form-wysiwyg',
			'rtButton'    => 'rtm-form-button',
			'rtFileInput' => 'rtm-form-file-input',
			'rtLink'      => 'rtm-form-link',
		);

		/**
		 * Get default html id.
		 *
		 * @access private
		 *
		 * @param  string $element element.
		 *
		 * @return mixed
		 */
		private function get_default_id( $element ) {
			return self::$id_counts[ $element ];
		}

		/**
		 * Update default html id.
		 *
		 * @access private
		 *
		 * @param  string $element element.
		 */
		private function update_default_id( $element ) {
			self::$id_counts[ $element ] ++;
		}

		/**
		 * Get default html class.
		 *
		 * @access private
		 *
		 * @param  string $element element.
		 *
		 * @return mixed
		 */
		private function get_default_class( $element ) {
			return self::$default_classes[ $element ];
		}

		/**
		 * Embed html class to html output.
		 *
		 * @access private
		 *
		 * @param  string $element Element.
		 * @param  array  $class Class.
		 *
		 * @return string $html
		 *
		 * @throws rtFormInvalidArgumentsException Exception on invalid argument.
		 */
		private function embedd_class( $element, $class = null ) {

			$html = 'class="' . esc_attr( $this->get_default_class( $element ) );

			if ( isset( $class ) ) {

				if ( is_array( $class ) ) {
					$html .= ' ' . esc_attr( implode( ' ', $class ) );
				} else {
					throw new rtFormInvalidArgumentsException( 'class [' . $element . ']' );
				}
			}
			$html .= '" ';

			return $html;
		}

		/**
		 * Generate rtmedia html element id attribute in admin options.
		 *
		 * @access private
		 *
		 * @param  string $element Element.
		 * @param  string $id Id.
		 *
		 * @return string $html
		 */
		private function generate_element_id( $element, $id = null ) {

			$html = 'id="';
			if ( isset( $id ) ) {
				$html            .= esc_attr( $id ) . '"';
				$this->element_id = $id;
			} else {
				$html            .= esc_attr( $this->get_default_class( $element ) . '-' . $this->get_default_id( $element ) ) . '"';
				$this->element_id = $this->get_default_class( $element ) . '-' . $this->get_default_id( $element );
				$this->update_default_id( $element );
			}

			return $html;
		}

		/**
		 * Generate rtmedia html name attribute in admin options.
		 *
		 * @access private
		 *
		 * @param  string $element Element.
		 * @param  string $multiple Multiple or not.
		 * @param  string $name Name.
		 *
		 * @return string $html
		 */
		private function generate_element_name( $element, $multiple, $name ) {

			$html = 'name="';
			if ( $multiple ) {

				$html .= esc_attr( isset( $name ) ? $name . '[]' : $element . '[]' );

				// for select - add multiple = multiple.
				if ( 'rtSelect' === $element ) {
					$html .= 'multiple = "multiple"';
				}
			} else {
				$html .= esc_attr( isset( $name ) ? $name : $element );
			}
			$html .= '"';

			return $html;
		}

		/**
		 * Generate rtmedia html value attribute in admin options.
		 *
		 * @access private
		 *
		 * @param  string $element Elements.
		 * @param  array  $attributes Attributes.
		 *
		 * @return string $html
		 */
		private function generate_element_value( $element, $attributes ) {

			$html = '';
			switch ( $element ) {
				case 'rtHidden': // hidden.
				case 'rtNumber': // number.
				case 'rtText': // text.
				case 'rtButton': // button.
				case 'rtFile':
					$html .= 'value="';
					$html .= ( isset( $attributes['value'] ) ) ? esc_attr( $attributes['value'] ) : '';
					$html .= '" ';
					break;

				case 'rtTextarea':
					/**
					 * Textarea
					 * no process --- handled in between the start tab and end tag.
					 * <textarea> value </textarea>
					 */
					break;

				case 'rtCheckbox': // checkbox.
				case 'rtRadio': // radio.
					$html .= 'value = "' . esc_attr( $attributes['value'] ) . '">';
					break;
				case 'rtLink': // radio.
					$html .= 'href = "' . esc_url( $attributes['href'] ) . '">';
					break;
			}

			return $html;
		}

		/**
		 * Generate rtmedia html element description in admin options.
		 *
		 * @access private
		 *
		 * @param  array $attributes Attributes array.
		 *
		 * @return string $html
		 */
		private function generate_element_desc( $attributes ) {

			if ( isset( $attributes['desc'] ) ) {

				$html = '<span class="clearfix large-offset-3 description">' . esc_html( $attributes['desc'] ) . '</span>';

				return $html;
			}

			return '';
		}

		/**
		 * Embed html misc attributes in admin options.
		 *
		 * @access private
		 *
		 * @param array $misc Misc.
		 *
		 * @return string|null
		 *
		 * @throws rtFormInvalidArgumentsException Throw invalid arguments exception.
		 */
		private function embedd_misc_attributes( $misc ) {

			if ( ! is_array( $misc ) ) {
				throw new rtFormInvalidArgumentsException( 'attributes : misc' );
			}

			$html = '';

			foreach ( $misc as $key => $value ) {
				$html .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
			}

			return $html;
		}

		/**
		 * Process html attributes in admin options.
		 *
		 * @access private
		 *
		 * @param  string $element Element.
		 * @param  mixed  $attributes Attributes.
		 * @param  bool   $container Container.
		 *
		 * @return string $html
		 */
		private function process_attributes( $element, $attributes, $container = false ) {

			// generating the id on its own if not provided otherwise taken from the parameter provided.
			if ( isset( $attributes['id'] ) ) {
				$html = $this->generate_element_id( $element, $attributes['id'] ) . ' ';
			} else {
				$html = $this->generate_element_id( $element ) . ' ';
			}

			// name attribute according to multiple flag.
			$multiple = ( isset( $attributes['multiple'] ) && $attributes['multiple'] ) ? true : false;
			$name     = ( isset( $attributes['name'] ) ) ? $attributes['name'] : $element;
			$html    .= $this->generate_element_name( $element, $multiple, $name ) . ' ';

			/**
			 *  List down all the classes provided along with the default class of rtForms.
			 *  default class of rtForms will always be attached irrespective of the attributes provided.
			 */
			if ( ! $container ) {

				if ( isset( $attributes['class'] ) ) {
					$html .= $this->embedd_class( $element, $attributes['class'] );
				} else {
					$html .= $this->embedd_class( $element );
				}
			}

			if ( isset( $attributes['misc'] ) ) {
				$html .= ' ' . $this->embedd_misc_attributes( $attributes['misc'] );
			}

			$html .= $this->generate_element_value( $element, $attributes );

			return $html;
		}

		/**
		 * Container enclosed elements in admin options.
		 *
		 * @access private
		 *
		 * @param  string $element Element.
		 * @param  array  $attrib Attributes.
		 * @param  array  $rtform_options Options.
		 *
		 * @return string $html
		 */
		private function container_enclosed_elements( $element, $attrib, $rtform_options ) {

			$html = '';
			$id   = '';
			$size = count( $rtform_options );

			if ( isset( $attrib['id'] ) ) {
				$id = $attrib['id'];
			}

			foreach ( $rtform_options as $opt ) {

				if ( isset( $attrib['id'] ) && $size > 1 ) {
					$attrib['id'] = $id . '-' . $this->get_default_id( $element );
					$this->update_default_id( $element );
				}

				foreach ( (array) $opt as $key => $val ) {

					if ( 'checked' === $key ) {
						$attrib['checked'] = esc_attr( $val );
					} else {
						if ( 'selected' === $key ) {
							$attrib['selected'] = esc_attr( $val );
						} else {
							if ( 'desc' === $key ) {
								$attrib['desc'] = esc_attr( $val );
							} else {
								if ( 'id' === $key ) {
									$attrib['id'] = esc_attr( $val );
								} else {
									$attrib['key']   = $key;
									$attrib['value'] = esc_attr( $val );
								}
							}
						}
					}
				}

				$checked = ( isset( $attrib['checked'] ) && $attrib['checked'] ) ? 'checked=checked' : '';
				if ( isset( $attrib['switch'] ) && $attrib['switch'] ) {
					$switch = 'data-toggle="switch"';
				} else {
					$switch = '';
				}

				$data = '';
				switch ( $element ) {
					case 'rtRadio':
						$data = '<input type="radio" ' . $checked . ' ';
						break;
					case 'rtCheckbox':
						$data = '<input type="checkbox" ' . $checked . ' ' . $switch . ' ';
						break;
					case 'rtSelect':
						$selected = ( $attrib['selected'] ) ? 'selected=selected' : '';
						$data     = '<option value="' . esc_attr( $attrib['value'] ) . '" ' . $selected . '>' . esc_html( $attrib['key'] ) . '</option>';
						break;
				}

				if ( 'rtSelect' !== $element ) {
					$data .= $this->process_attributes( $element, $attrib, true );

					// span elements for checkbox on/off switch.
					if ( 'rtCheckbox' === $element ) {
						$data .= '<span class="switch-label" data-on="On" data-off="Off"></span><span class="switch-handle"></span>';
					}

					if ( isset( $attrib['switch_square'] ) && $attrib['switch_square'] ) {

						$data = '<div class="rt-switch switch-square" data-on-label="<i class=\'fui-check\'></i>" data-off-label="<i class=\'fui-cross\'></i>">' . $data . '</div>';
					} else {
						if ( ( isset( $attrib['switch'] ) && $attrib['switch'] ) || ( isset( $attrib['switch_square'] ) && $attrib['switch_square'] ) ) {

							$label_class = array( 'switch' );

							$data = $this->enclose_label( $element, $data, $attrib['key'], $label_class );
							if ( $size > 1 ) {
								$data = '<div>' . $data . '</div>';
							}
						} else {
							$data = $this->enclose_label( $element, $data, $attrib['key'] );
						}
					}

					$data .= '';
				}

				$html .= $data;

				unset( $attrib['id'] );
				unset( $attrib['key'] );
				unset( $attrib['value'] );
			}

			return $html;
		}

		/**
		 * Parse multiple options in admin options.
		 *
		 * @access private
		 *
		 * @param string $element Element.
		 * @param array  $attributes Attributes.
		 *
		 * @return array
		 *
		 * @throws rtFormInvalidArgumentsException Exception.
		 */
		private function parse_multiple_options( $element, $attributes ) {

			if ( is_array( $attributes ) ) {

				if ( isset( $attributes['rtForm_options'] ) && is_array( $attributes['rtForm_options'] ) ) {

					$attrib_keys = array_keys( $attributes );
					$attrib      = array();

					foreach ( $attrib_keys as $key ) {
						if ( 'rtForm_options' !== $key ) {
							$attrib[ $key ] = $attributes[ $key ];
						}
					}

					$rtform_options = (array) $attributes['rtForm_options'];

					return array(
						'attrib'         => $attrib,
						'rtForm_options' => $rtform_options,
					);
				} else {
					throw new rtFormInvalidArgumentsException( 'rtForm_options [' . $element . ']' );
				}
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Enclose html label.
		 *
		 * @access protected
		 *
		 * @param string     $element Element.
		 * @param string     $html HTML.
		 * @param string     $label Label.
		 * @param array|bool $class Classes.
		 *
		 * @return string
		 */
		protected function enclose_label( $element, $html, $label, $class = false ) {

			$label_class = '';

			if ( ! empty( $class ) && is_array( $class ) ) {
				$label_class = 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
			}

			$data = '<label for="' . esc_attr( $this->element_id ) . '" ' . $label_class . '>';

			if ( 'rtRadio' === $element || 'rtCheckbox' === $element ) {
				$data .= $html . ' ' . $label;
			} else {
				$data .= $label . ' ' . $html;
			}

			$data .= '</label>';

			return $data;
		}

		/**
		 * Generate rtmedia html textbox in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid argument Exception.
		 */
		protected function generate_textbox( $attributes ) {

			$element = 'rtText';
			if ( is_array( $attributes ) ) {

				// Starting the input tag.
				$html = '<input type="text" ';

				// generating attributes.
				$html .= $this->process_attributes( $element, $attributes );

				// ending the tag.
				$html .= ' />';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Generate rtmedia html inputfile in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Arguments to create file input control for default thumbnail generator settings.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid argument exception.
		 */
		protected function generate_inputfile( $attributes ) {

			$element = 'rtFile';
			if ( is_array( $attributes ) ) {

				// Adding nonce for file upload.
				$nonce = wp_create_nonce( 'rtmedia-admin-upload' );

				// Keep customized button for file input.
				$html = '<button class="rtm-form-button button button-primary button-small">Browse File</button>';

				// Starting the input tag.
				$html .= '<input type="hidden" id="rtmedia_admin_upload_nonce" value="' . esc_attr( $nonce ) . '" />';
				$html .= '<input type="file" ';

				// Generating attributes.
				$html .= $this->process_attributes( $element, $attributes );

				// Ending the tag.
				$html .= ' />';

				if ( ! empty( $attributes['name'] ) && ! empty( $attributes['value'] ) ) {
					$html .= '<input type="hidden" name="rtmedia-options[' . esc_attr( $attributes['name'] ) . '_hid]" value="' . esc_attr( $attributes['value'] ) . '" />';
				}

				if ( ! empty( $attributes['value'] ) ) {
					$img_src  = wp_get_attachment_image_src( $attributes['value'], 'thumbnail' );
					$img_path = get_attached_file( $attributes['value'] );

					if ( file_exists( $img_path ) && ! empty( $img_src[0] ) ) {
						$html .= '<span class="rtm-file-preview">';
						$html .= sprintf( '<img src="%s" width="100">', esc_url( $img_src[0] ) );
						$html .= '<a href="#" class="no-popup rtm-delete-preview" title="' . esc_attr__( 'Delete this file', 'buddypress-media' ) . '" data-media_type="' . $attributes['name'] . '">';
						$html .= '<i class="remove-from-queue dashicons dashicons-dismiss"></i>';
						$html .= '</a></span>';
					}
				}

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;

			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}


		/**
		 * Get rtmedia html textbox in admin options.
		 *
		 * @access public
		 *
		 * @param string|array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid argument exception.
		 */
		public function get_textbox( $attributes = '' ) {

			return $this->generate_textbox( $attributes );
		}

		/**
		 * Display textbox.
		 *
		 * @param string|array $args Arguments.
		 */
		public function display_textbox( $args = '' ) {
			echo wp_kses( $this->get_textbox( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtMedia link in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_link( $attributes ) {

			$element = 'rtLink';
			if ( is_array( $attributes ) ) {

				// Starting a tag.
				$html = '<a ';

				// Generating attributes.
				$html .= $this->process_attributes( $element, $attributes );

				// Put text of link.
				$html .= esc_html( ( isset( $attributes['text'] ) ) ? $attributes['text'] : '' );

				// ending a tag.
				$html .= '</a>';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html link or button in admin options.
		 *
		 * @access public
		 *
		 * @param string|array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_link( $attributes = '' ) {

			return $this->generate_link( $attributes );
		}

		/**
		 * Display link.
		 *
		 * @param string|array $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_link( $args = '' ) {
			echo wp_kses( $this->get_link( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Get rtmedia html input file in admin options.
		 *
		 * @access public
		 *
		 * @param string|array $attributes Arguments to create file input control.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid argument exception.
		 */
		public function get_inputfile( $attributes = '' ) {
			return $this->generate_inputfile( $attributes );
		}


		/**
		 * Display file input settings for set custom thumbnail generator.
		 *
		 * @param string|array $args Arguments to create file input control.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_inputfile( $args = '' ) {
			// Previously escaped and sanitized so not required here.
			echo wp_kses( $this->get_inputfile( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html number field in admin options
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_number( $attributes ) {

			$element = 'rtNumber';
			if ( is_array( $attributes ) ) {

				// Starting the input tag.
				$html = '<input type="number" ';

				// generating attributes.
				$html .= $this->process_attributes( $element, $attributes );
				if ( isset( $attributes['min'] ) ) {
					$html .= " min='" . esc_attr( $attributes['min'] ) . "' ";
				}
				if ( isset( $attributes['max'] ) ) {
					$html .= " max='" . esc_attr( $attributes['max'] ) . "' ";
				}
				if ( isset( $attributes['step'] ) ) {
					$html .= " step='" . esc_attr( $attributes['step'] ) . "' ";
				}

				// ending the tag.
				$html .= ' />';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html number field in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_number( $attributes = '' ) {

			return $this->generate_number( $attributes );
		}

		/**
		 * Display number.
		 *
		 * @param string|array $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_number( $args = '' ) {
			echo wp_kses( $this->get_number( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html date field in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_date( $attributes ) {

			$element = 'rtDate';
			if ( is_array( $attributes ) ) {
				$html = '<input type="date" ';

				$html .= $this->process_attributes( $element, $attributes );

				$html .= ' />';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html date field in admin options.
		 *
		 * @access public
		 *
		 * @param  array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_date( $attributes ) {
			return $this->generate_date( $attributes );
		}

		/**
		 * Generate rtmedia html hidden field in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_hidden( $attributes ) {

			$element = 'rtHidden';
			if ( is_array( $attributes ) ) {

				// Starting the input tag.
				$html = '<input type="hidden" ';

				// generating attributes.
				$html .= $this->process_attributes( $element, $attributes );

				// ending the tag.
				$html .= ' />';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html hidden field in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_hidden( $attributes = '' ) {

			return $this->generate_hidden( $attributes );
		}

		/**
		 * Generate rtmedia html textarea in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_textarea( $attributes ) {

			$element = 'rtTextarea';
			if ( is_array( $attributes ) ) {

				$html  = '<textarea ';
				$html .= $this->process_attributes( $element, $attributes );
				$html .= '>';

				$html .= esc_html( ( isset( $attributes['value'] ) ) ? $attributes['value'] : '' );

				$html .= '</textarea>';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html textarea in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_textarea( $attributes = '' ) {

			return $this->generate_textarea( $attributes );
		}

		/**
		 * Display textarea.
		 *
		 * @param string|array $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_textarea( $args = '' ) {
			echo wp_kses( $this->get_textarea( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html input type radio in admin options.
		 *
		 * @access protected
		 *
		 * @param  array $attributes Attributes.
		 *
		 * @return string $container
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_radio( $attributes ) {

			$element = 'rtRadio';
			$html    = '';

			$meta  = $this->parse_multiple_options( $element, $attributes );
			$html .= $this->container_enclosed_elements( $element, $meta['attrib'], $meta['rtForm_options'] );

			if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
				$html .= $this->generate_element_desc( $attributes );
			}

			$container = '<span ';
			if ( isset( $attributes['class'] ) ) {
				$container .= $this->embedd_class( $element, $attributes['class'] );
			} else {
				$container .= $this->embedd_class( $element );
			}
			$container .= '>';

			$container .= $html;

			$container .= '</span>';

			return $container;
		}

		/**
		 * Get rtmedia html input type radio in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_radio( $attributes = '' ) {

			return $this->generate_radio( $attributes );
		}

		/**
		 * Display radio button.
		 *
		 * @param array|string $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_radio( $args = '' ) {
			echo wp_kses( $this->get_radio( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html input type checkbox in admin options.
		 *
		 * @access protected
		 *
		 * @param  array $attributes Attributes.
		 *
		 * @return string $container
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_checkbox( $attributes ) {

			$element = 'rtCheckbox';
			$html    = '';

			$meta  = $this->parse_multiple_options( $element, $attributes );
			$html .= $this->container_enclosed_elements( $element, $meta['attrib'], $meta['rtForm_options'] );

			if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
				$html .= $this->generate_element_desc( $attributes );
			}

			$container = '<span ';
			if ( isset( $attributes['class'] ) ) {
				$container .= $this->embedd_class( $element, $attributes['class'] );
			} else {
				$container .= $this->embedd_class( $element );
			}
			$container .= '>';

			$container .= $html;

			$container .= '</span>';

			return $container;
		}

		/**
		 * Get rtmedia html input type checkbox in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 */
		public function get_checkbox( $attributes = '' ) {

			return $this->generate_checkbox( $attributes );
		}

		/**
		 * Get rtmedia html input type checkbox (get_switch) in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 */
		public function get_switch( $attributes = '' ) {

			$attributes['switch'] = true;

			return $this->generate_checkbox( $attributes );
		}

		/**
		 * Display switch.
		 *
		 * @param string $args Arguments.
		 */
		public function display_switch( $args = '' ) {
			echo wp_kses( $this->get_switch( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Get rtmedia html input type checkbox (get_switch_square) in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 */
		public function get_switch_square( $attributes = '' ) {

			$attributes['switch_square'] = true;

			return $this->generate_checkbox( $attributes );
		}

		/**
		 * Generate rtmedia html input type select in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_select( $attributes ) {

			if ( is_array( $attributes ) ) {
				$element = 'rtSelect';
				$html    = '<select ';

				if ( isset( $attributes['id'] ) ) {
					$id = $attributes['id'];
				} else {
					$id = $element . $this->get_default_id( $element );
					$this->update_default_id( $element );
				}
				$html    .= $this->generate_element_id( $element, $id ) . ' ';
				$multiple = ( isset( $attributes['multiple'] ) && $attributes['multiple'] ) ? true : false;
				$name     = ( isset( $attributes['name'] ) ) ? $attributes['name'] : $element;
				$html    .= $this->generate_element_name( $element, $multiple, $name ) . ' ';
				if ( isset( $attributes['class'] ) ) {
					$html .= $this->embedd_class( $element, $attributes['class'] );
				} else {
					$html .= $this->embedd_class( $element );
				}

				if ( isset( $attributes['misc'] ) ) {
					$html .= ' ' . $this->embedd_misc_attributes( $attributes['misc'] );
				}

				$html .= '>';

				$meta  = $this->parse_multiple_options( $element, $attributes );
				$html .= $this->container_enclosed_elements( $element, $meta['attrib'], $meta['rtForm_options'] );

				$html .= '</select>';

				if ( isset( $attributes['label'] ) ) {
					if ( isset( $attributes['labelClass'] ) ) {
						$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
					} else {
						$html = $this->enclose_label( $element, $html, $attributes['label'] );
					}
				}

				if ( isset( $attributes['show_desc'] ) && $attributes['show_desc'] ) {
					$html .= $this->generate_element_desc( $attributes );
				}

				return $html;
			} else {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}
		}

		/**
		 * Get rtmedia html input type select in admin options.
		 *
		 * @access public
		 *
		 * @param  string|array $attributes Attributes.
		 *
		 * @return string
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_select( $attributes = '' ) {

			return $this->generate_select( $attributes );
		}

		/**
		 * Display select.
		 *
		 * @param string|array $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_select( $args = '' ) {
			echo wp_kses( $this->get_select( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html button in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes attributes for button control in key => value.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_button( $attributes ) {

			if ( ! is_array( $attributes ) ) {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}

			$element = 'rtButton';

			// Starting the input tag.
			$html = '<input type="button" ';

			// generating attributes.
			$html .= $this->process_attributes( $element, $attributes );

			// ending the tag.
			$html .= ' />';

			if ( isset( $attributes['label'] ) ) {
				if ( isset( $attributes['labelClass'] ) ) {
					$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
				} else {
					$html = $this->enclose_label( $element, $html, $attributes['label'] );
				}
			}

			if ( ! empty( $attributes['show_desc'] ) ) {
				$html .= $this->generate_element_desc( $attributes );
			}

			return $html;
		}

		/**
		 * Get rtmedia html button in admin options.
		 *
		 * @access public
		 *
		 * @param string|array $attributes attributes for button control.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_button( $attributes ) {

			return $this->generate_button( $attributes );
		}

		/**
		 * Prints the HTML for button control based on arguments
		 *
		 * @param array $args arguments for button control.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_button( $args ) {
			echo wp_kses( $this->get_button( $args ), RTMedia::expanded_allowed_tags() );
		}

		/**
		 * Generate rtmedia html file input in admin options.
		 *
		 * @access protected
		 *
		 * @param array $attributes arguments to create file input control.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		protected function generate_file_input( $attributes ) {

			if ( ! is_array( $attributes ) ) {
				throw new rtFormInvalidArgumentsException( 'attributes' );
			}

			$element = 'rtFileInput';

			// Adding nonce for file upload.
			$nonce = wp_create_nonce( 'rtmedia-admin-upload' );

			// Keep customized button for file input.
			$html = '<button class="rtm-form-button button button-primary button-small">Browse File</button>';

			// Starting the input tag.
			$html .= '<input type="hidden" id="rtmedia_admin_upload_nonce" value="' . esc_attr( $nonce ) . '" />';
			$html .= '<input type="file" ';

			// generating attributes.
			$html .= $this->process_attributes( $element, $attributes );

			// ending the tag.
			$html .= ' />';

			if ( isset( $attributes['label'] ) ) {
				if ( isset( $attributes['labelClass'] ) ) {
					$html = $this->enclose_label( $element, $html, $attributes['label'], $attributes['labelClass'] );
				} else {
					$html = $this->enclose_label( $element, $html, $attributes['label'] );
				}
			}

			if ( ! empty( $attributes['show_desc'] ) ) {
				$html .= $this->generate_element_desc( $attributes );
			}

			return $html;
		}

		/**
		 * Get rtmedia html file input in admin options.
		 *
		 * @access public
		 *
		 * @param string/array $attributes Attributes.
		 *
		 * @return string
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function get_file_input( $attributes ) {

			return $this->generate_file_input( $attributes );
		}

		/**
		 * Display file input.
		 *
		 * @param array $args Arguments.
		 *
		 * @throws rtFormInvalidArgumentsException Invalid arguments exception.
		 */
		public function display_file_input( $args ) {
			echo wp_kses( $this->get_file_input( $args ), RTMedia::expanded_allowed_tags() );
		}
	}

}
