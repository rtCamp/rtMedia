<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rtForms
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

if(!class_exists("rtForm")) {

	class rtForm {


		private $element_id;
		/**
		 * default id counts
		 * if id for any element is not given then these count will be used in id generation
		 */
		private static $id_counts = array(
			"rtText" => 0,
			"rtNumber" => 0,
			"rtDate" => 0,
			"rtRadio" => 0,
			"rtCheckbox" => 0,
			"rtSelect" => 0,
			"rtTextarea" => 0,
			"rtHidden" => 0,
			"rtWysiwyg" => 0
		);

		private static $default_classes = array(
			"rtText" => "rt-form-text",
			"rtNumber" => "rt-form-number",
			"rtDate" => "rt-form-date",
			"rtRadio" => "rt-form-radio",
			"rtCheckbox" => "rt-form-checkbox",
			"rtSelect" => "rt-form-select",
			"rtTextarea" => "rt-form-textarea",
			"rtHidden" => "rt-form-hidden",
			"rtWysiwyg" => "rt-form-wysiwyg"
		);


		private function get_default_id($element) {
			return self::$id_counts[$element];
		}

		private function update_default_id($element) {
			self::$id_counts[$element] ++;
		}

		private function get_default_class($element) {
			return self::$default_classes[$element];
		}


		private function embedd_class($element, $class = NULL) {

			$html = 'class="' . $this->get_default_class($element);

			if( isset( $class ) ) {

				if( is_array( $class ) )
					$html .= ' ' . implode(" ", $class);
				else
					throw new rtFormInvalidArgumentsException( "class [". $element ."]" );
			}
			$html .= '" ';

			return $html;
		}

		private function generate_element_id($element, $id = NULL) {

			$html = 'id="';
			if( isset( $id ) ) {
				$html .= $id . '"';
				$this->element_id = $id;
			} else {
				$html .= $this->get_default_class($element) . "-" . $this->get_default_id($element) . '"';
				$this->element_id = $this->get_default_class($element) . "-" . $this->get_default_id($element);
				$this->update_default_id($element);
			}

			return $html;
		}

		private function generate_element_name($element, $multiple, $name) {

			$html = 'name="';
			if( $multiple ) {

				$html .= isset( $name ) ? $name . '[]' : $element . '[]';

				// for select - add multiple = multiple
				if( $element == "rtSelect" ) {
					$html .= 'multiple = "multiple"';
				}
			}
			else
				$html .= isset( $name ) ? $name : $element;
			$html .= '"';

			return $html;
		}

		private function generate_element_value($element, $attributes) {

			$html = '';
			switch( $element ) {
				case "rtHidden"://hidden
				case "rtNumber"://number
				case "rtText" :	//text
								$html .= 'value="';
								$html .= ( isset($attributes['value']) ) ? $attributes['value'] : '';
								$html .= '" ';
								break;

				case "rtTextarea" : /**textarea
									 * no process --- handled in between the start tab and end tag.
									 * <textarea> value </textarea>
									 */
									break;

				case "rtCheckbox" :	//checkbox
				case "rtRadio" ://radio
								$html .= 'value = "' . $attributes['value'] . '">';
								break;
			}
			return $html;
		}

		private function generate_element_desc($attributes) {

			if( isset($attributes['desc']) ) {

				$html = '<span class="clearfix large-offset-3 description">' . $attributes['desc'] . '</span>';

				return $html;
			}

			return "";
		}

		private function embedd_misc_attributes($misc) {

			if(!is_array($misc)) {
				throw new rtFormInvalidArgumentsException("attributes : misc");
				return;
			}

			$html = '';

			foreach ($misc as $key => $value) {
				$html .= $key . '="' . $value . '" ';
			}

			return $html;
		}

		private function processAttributes($element, $attributes, $container = false) {

			/* generating the id on its own if not provided otherwise taken from the parameter provided */
			if( isset($attributes['id']) )
				$html = $this->generate_element_id($element, $attributes['id']) . ' ';
			else
				$html = $this->generate_element_id($element) . ' ';

			/* name attrbute according to multiple flag */
			$multiple = ( isset($attributes['multiple']) && $attributes['multiple'] ) ? true : false;
			$name = ( isset($attributes['name']) ) ? $attributes['name'] : $element;
			$html .= $this->generate_element_name($element, $multiple, $name) . ' ';

			/*
			 *  list down all the classes provided along with the default class of rtForms.
			 *  default class of rtForms will always be attached irrespective of the attributes provided.
			 */
			if(!$container) {

				if(isset($attributes['class']))
					$html .= $this->embedd_class($element, $attributes['class']);
				else
					$html .= $this->embedd_class($element);
			}

			if(isset($attributes['misc']))
				$html.= ' ' . $this->embedd_misc_attributes($attributes['misc']);

			$html .= $this->generate_element_value($element, $attributes);

			return $html;
		}

		private function container_enclosed_elements($element, $attrib, $rtForm_options) {

			$html = '';
			$size = count($rtForm_options);
			if( isset($attrib['id']) )
				$id = $attrib['id'];

			foreach ($rtForm_options as $opt) {

				if( isset($attrib['id']) && $size>1 ) {
					$attrib['id'] = $id . "-" . $this->get_default_id($element);
					$this->update_default_id($element);
				}

				foreach ((array)$opt as $key => $val) {

					if($key == "checked")
						$attrib['checked'] = $val;
					else if($key == "selected")
						$attrib['selected'] = $val;
					else if($key == "desc")
						$attrib['desc'] = $val;
					else if($key == "id")
						$attrib['id'] = $val;
					else {
						$attrib['key'] = $key;
						$attrib['value'] = $val;
					}
				}

				$checked = (isset($attrib['checked']) && $attrib['checked']) ? "checked=checked" : "";
				if( isset($attrib['switch']) && $attrib['switch'])
					$switch = 'data-toggle="switch"';
				else
					$switch = '';

				switch($element) {
					case "rtRadio" :
									$data = '<input type="radio" ' . $checked . " ";
									break;
					case "rtCheckbox" :
										$data = '<input type="checkbox" ' . $checked . " " . $switch . " ";
										break;
					case "rtSelect" :
										$selected = ($attrib['selected']) ? "selected=selected" : "";
										$data = '<option value="' . $attrib['value'] . '"' . $selected . '>' . $attrib['key'] . '</option><br />';
										break;
				}

				if($element != "rtSelect") {
					$data .= $this->processAttributes($element, $attrib, true);

					if( isset($attrib['switch_square']) && $attrib['switch_square'] ) {

						$data = '<div class="rt-switch switch-square" data-on-label="<i class=\'fui-check\'></i>" data-off-label="<i class=\'fui-cross\'></i>">' . $data . '</div>';

					} else if( (isset($attrib['switch']) && $attrib['switch']) ||
								(isset($attrib['switch_square']) && $attrib['switch_square']) ) {

						if( $size > 1 )
							$data = '<div>' . $this->enclose_label($element, $data, $attrib['key']) . '</div>';
						else
							$data = $this->enclose_label($element, $data, $attrib['key']);

					} else
						$data = $this->enclose_label($element, $data, $attrib['key']);

					$data .= '<br>';
				}

				$html .= $data;

				unset($attrib['id']);
				unset($attrib['key']);
				unset($attrib['value']);
			}
			return $html;
		}

		private function parse_multiple_options($element, $attributes) {

			if( is_array($attributes) ) {

				if( isset($attributes['rtForm_options']) && is_array($attributes['rtForm_options']) ) {

					$attribKeys = array_keys($attributes);
					$attrib = array();

					foreach ($attribKeys as $key) {
						if( $key != "rtForm_options" )
							$attrib[$key] = $attributes[$key];
					}

					$rtForm_options = (array) $attributes['rtForm_options'];

					return array( 'attrib' => $attrib, 'rtForm_options' => $rtForm_options );
				} else
					throw new rtFormInvalidArgumentsException( "rtForm_options [" . $element . "]" );
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		protected function enclose_label($element, $html, $label, $class=false) {

			$labelClass = '';
			if(!empty($class) && is_array($class))
				$labelClass = 'class="'.implode(' ', $class).'"';

			$data = '<label for="' . $this->element_id . '" '.$labelClass.'>';

			if( $element == "rtRadio" || $element == "rtCheckbox" )
				$data .= $html . ' ' . $label;
			else
				$data .= $label . ' ' . $html;

			$data .= '</label>';

			return $data;
		}


        protected function generate_textbox($attributes) {

			$element = 'rtText';
			if( is_array( $attributes ) ) {

				/* Starting the input tag */
				$html = '<input type="text" ';

				/* generating attributes */
				$html .= $this->processAttributes($element, $attributes);

				/* ending the tag */
				$html .= ' />';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['show_desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		public function get_textbox( $attributes = '' ) {

			return $this->generate_textbox($attributes);
		}


		protected function generate_number($attributes) {

			$element = 'rtNumber';
			if( is_array( $attributes ) ) {

				/* Starting the input tag */
				$html = '<input type="number" ';

				/* generating attributes */
				$html .= $this->processAttributes($element, $attributes);
				if( isset($attributes['min']) ) {
				    $html.= " min='".$attributes['min']."' ";
				}
				/* ending the tag */
				$html .= ' />';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['show_desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		public function get_number( $attributes = '' ) {

			return $this->generate_number($attributes);
		}

		protected function generate_date($attributes) {

			$element = 'rtDate';
			if( is_array( $attributes ) ) {
				$html = '<input type="date" ';

				$html .= $this->processAttributes($element, $attributes);

				$html .= ' />';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		public function get_date($attributes) {
			return $this->generate_date($attributes);
		}


		protected function generate_hidden($attributes) {

			$element = 'rtHidden';
			if( is_array( $attributes ) ) {

				/* Starting the input tag */
				$html = '<input type="hidden" ';

				/* generating attributes */
				$html .= $this->processAttributes($element, $attributes);

				/* ending the tag */
				$html .= ' />';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['show_desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		public function get_hidden( $attributes = '' ) {

			return $this->generate_hidden($attributes);
		}


		protected function generate_textarea($attributes) {

			$element = 'rtTextarea';
			if( is_array( $attributes ) ) {

				$html = '<textarea ';
				$html .= $this->processAttributes($element, $attributes);
				$html .= '>';

				$html .= (isset($attributes['value'])) ? $attributes['value'] : "" ;

				$html .= '</textarea>';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['show_desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );
		}

		public function get_textarea( $attributes = '' ) {

			return $this->generate_textarea($attributes);
		}



		/* wysiwyg
		 *
		 * pending as of now.
		 *
		 * functionality and flow needs to be decided
		 *
		 *  */
//		protected function generate_wysiwyg($attributes) {
//
//			$element = 'rtWysiwyg';
//			if( is_array($attributes) ) {
//
//				$id = isset( $attributes['id'] ) ? $attributes['id'] : $this->get_default_class($element) . "-" . $this->get_default_id($element);
//				$name = isset( $attributes['name'] ) ? $attributes['name'] : $element;
//				if(isset($attributes['class']))
//					$class = $this->embedd_class($element, $attributes['class']);
//				else
//					$class = $this->embedd_class($element);
//				$value = isset( $attributes['value'] ) ? $attributes['value'] : "";
//
//				echo '<label for="' . $id . '">';
//					wp_editor( $value, $id, array('textarea_name' =>  $name, 'editor_class' => $class) );
//				echo '</label>';
//			} else
//				throw new rtFormInvalidArgumentsException( "attributes" );
//		}
//
//		public function get_wysiwyg( $attributes = '' ) {
//
//			ob_start();
//			$this->generate_wysiwyg($attributes);
//			return ob_get_clean();
//		}


		protected function generate_radio($attributes) {

			$element = 'rtRadio';
			$html = '';

			$meta = $this->parse_multiple_options($element, $attributes);
			$html .= $this->container_enclosed_elements($element, $meta['attrib'], $meta['rtForm_options']);

			if( isset($attributes['show_desc']) && $attributes['show_desc'] )
				$html .= $this->generate_element_desc($attributes);

			$container = '<span ';
			if(isset($attributes['class']))
				$container .= $this->embedd_class($element, $attributes['class']);
			else
				$container .= $this->embedd_class($element);
			$container .= '>';

			$container .= $html;

			$container .= '</span>';

//			if( isset($attributes['label']) )
//				$container = $this->enclose_label('container', $container, $attributes['label']);

			return $container;
		}

		public function get_radio( $attributes = '' ) {

			return $this->generate_radio($attributes);
		}


		protected function generate_checkbox($attributes) {

			$element = 'rtCheckbox';
			$html = '';

			$meta = $this->parse_multiple_options($element, $attributes);
			$html .= $this->container_enclosed_elements($element, $meta['attrib'], $meta['rtForm_options']);

			if( isset($attributes['show_desc']) && $attributes['show_desc'] )
				$html .= $this->generate_element_desc($attributes);

			$container = '<span ';
			if(isset($attributes['class']))
				$container .= $this->embedd_class($element, $attributes['class']);
			else
				$container .= $this->embedd_class($element);
			$container .= '>';

			$container .= $html;

			$container .= '</span>';

//			if( isset($attributes['label']) )
//				$container = $this->enclose_label('container', $container, $attributes['label']);

			return $container;
		}

		public function get_checkbox( $attributes = '' ) {

			return $this->generate_checkbox($attributes);
		}

		public function get_switch($attributes = '') {

			$attributes['switch'] = true;
			return $this->generate_checkbox($attributes);
		}

		public function get_switch_square($attributes = '') {

			$attributes['switch_square'] = true;
			return $this->generate_checkbox($attributes);
		}

		protected function generate_select($attributes) {

			if( is_array($attributes) ) {
				$element = 'rtSelect';
				$html = '<select ';

				if(isset($attributes['id']))
					$id = $attributes['id'];
				else {
					$id = $element.$this->get_default_id ($element);
					$this->update_default_id($element);
				}
				$html .= $this->generate_element_id($element, $id) . ' ';
				$multiple = ( isset($attributes['multiple']) && $attributes['multiple'] ) ? true : false;
				$name = ( isset($attributes['name']) ) ? $attributes['name'] : $element;
				$html .= $this->generate_element_name($element, $multiple, $name) . ' ';
				if(isset($attributes['class']))
					$html .= $this->embedd_class($element, $attributes['class']);
				else
					$html .= $this->embedd_class($element);

				if(isset($attributes['misc']))
					$html.= ' ' . $this->embedd_misc_attributes($attributes['misc']);

				$html .= '>';

				$meta = $this->parse_multiple_options($element, $attributes);
				$html .= $this->container_enclosed_elements($element, $meta['attrib'], $meta['rtForm_options']);

				$html .= '</select>';

				if( isset($attributes['label']) ) {
					if( isset($attributes['labelClass']) )
						$html = $this->enclose_label($element, $html, $attributes['label'], $attributes['labelClass']);
					else
						$html = $this->enclose_label($element, $html, $attributes['label']);
				}

				if( isset($attributes['show_desc']) && $attributes['show_desc'] )
					$html .= $this->generate_element_desc($attributes);

				return $html;
			} else
				throw new rtFormInvalidArgumentsException( "attributes" );

		}

		public function get_select( $attributes = '' ) {

			return $this->generate_select($attributes);
		}
	}
}