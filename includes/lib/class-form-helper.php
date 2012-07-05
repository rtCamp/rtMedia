<?php
/**
 * Contains class for initializing and echoing forms
 *
 * @package rtLibrary
 *
 * @since rtLibrary 0.1
 * 
 * @author rtCamp
 * 
 */
if (!class_exists('RTL_Form_Helper')) {

	/**
	 * Class to initialize and render forms
	 */
	Class RTL_Form_Helper {

		//Work in progress.....
			private $name, $method, $action, $label, $icon, $elements;

		/**
		 * Constructor for RTL_Form_Helper
		 */
		function __construct($args) {
			//todo Functionality of RTL Form Constructor
			$defaults = array(
				'name' => '',
				'label' => '',
				'action' => '',
				'method' => 'post',
				'icon' => '',
				'elements' => null
			);
		}

		/**
		 * Destructor for RTL_Form_Helper
		 */
		function __destruct() {
			//todo Functionality of RTL Form Destructor
		}

		/**
		 * Renders the form and prints the html code for the form
		 */
		public function render() {
			//todo Fuctionality of RTL Form Render function
			$this->show_header();
			if ($this->icon != '')
				$this->show_icon();
			if ($this->label != '')
				$this->show_title();
			$this->show_content();
			$this->show_footer();
		}

		private function show_header() {
			?>
			<div class="wrap">
				<?php
			}

			private function show_footer() {
				?>
			</div>
			<?php
		}

		private function show_icon() {
			
		}

		private function show_title() {
			?>
			<h2><?php echo $this->label ?></h2>
			<?php
		}

		private function show_content() {
			//Work in progress.....
			?>
			<form action="<?php echo $this->action ?>" method="<?php echo $this->method ?>">
				<input type="hidden" name="option_page" value="<?php $this->name ?>" />
				<input type="hidden" name="action" value="update" />
			</form>
			<?php
		}

	}

}

if (!class_exists('RTL_Form_Section')) {

	/**
	 * Form Section Class
	 */
	Class RTL_Form_Section {

		private $description, $title, $fields;

		/**
		 * Constructor for the RTL Form Fieldset
		 */
		public function __construct($args, $override = null) {
			$defaults = array(
				'title' => '',
				'description' => '',
				'fields' => array()
			);
			$args = wp_parse_args($args, $defaults);
			$this->title = $args['title'];
			$this->description = $args['description'];
			if (is_array($args['fields'])) {
				foreach ($args['fields'] as $field) {
					if (get_class($field) == 'RTL_Form_Field')
						$this->fields[] = $field;
					else
						throw new RTL_Exception('Array Element is not an RTL Form Field', 12);
				}
			}
			else if (get_class($args['fields']) == 'RTL_Form_Field')
				$this->fields[] = $args['fields'];
			else
				throw new RTL_Exception('Field is not an RTL Form Field', 12);
		}

		/**
		 * 
		 */
		public function __destruct() {
			//todo Functionality of RTL Form Section Destructor
		}

		/**
		 * Adds an element in the section
		 */
		public function add_field($field) {
			//todo Functionality of RTL Form Section add_element function
			if (get_class($field) == 'RTL_Form_Field')
				$this->fields[] = $field;
			else
				throw new RTL_Exception('Field is not an RTL Form Field', 12);
		}

		/**
		 * Updates a previously added element in the Form Section
		 */
		public function update_field() {
			//todo Functionality of RTL Form Section update_element function
		}

		/**
		 * Removes a previously added element from the Form Section
		 */
		public function remove_field() {
			//todo Functionality of RTL Form Section remove_element function
		}

		public function render($override = null) {
			$this->show_title();
			$this->show_description();
			$this->show_content();
		}

		private function show_title() {
			?>
			<h3><?php echo $this->title ?></h3>
			<?php
		}

		private function show_description() {
			?>
			<p><?php echo $this->description ?></p>
			<?php
		}

		private function show_content() {
			if (count($this->fields) > 0) {
				?>
				<table class="form-table">
					<tbody>
						<?php
						foreach ($this->fields as $field) {
							$field->render();
						}
						?>
					</tbody>
				</table>
				<?php
			}
		}

	}

}

if (!class_exists('RTL_Form_Field')) {

	/**
	 * Form Element Class
	 */
	Class RTL_Form_Field {

		private $name, $type, $id, $class, $label, $default, $description, $options, $disabled, $multiple, $custom, $priority;

		/**
		 * This will construct a new Form Element to be added in the section.
		 */
		public function __construct($args, $override = null) {
			if (!is_array($args))
				throw new RTL_Exception('Passed argument is not an array');
			$defaults = array(
				'name' => '', //Required
				'label' => '', //Required
				'type' => 'text',
				'id' => '',
				'class' => '',
				'options' => false,
				'default' => '',
				'description' => '',
				'disabled' => false,
				'custom' => false,
				'priority' => 10,
			);
			$args = wp_parse_args($args, $defaults);
			if ($args['label'] == '') {
				throw new RTL_Exception('Label not set while creating an RTL Form Element', 10);
			} else if ($args['name'] == '') {
				throw new RTL_Exception('Name not set while creating an RTL Form Element', 11);
			} else if ($args['type'] == 'radio' || $args['type'] == 'checkbox' || $args['type'] == 'select') {
				if (is_array($args['options']) && count($args['options']) > 0) {
					if (array_key_exists('label', $args['options']) || array_key_exists('value', $args['options'])) {
						$option = $args['options'];
						if (!array_key_exists('value', $option) || $option['value'] == '') {
							throw new RTL_Exception('Value parameter missing in the RTL Form Element\'s option');
						} else if (!array_key_exists('label', $option) || $option['label'] == '') {
							throw new RTL_Exception('Label parameter missing in the RTL Form Element\'s option');
						}
						$this->options[] = $args['options'];
					} else {
						foreach ($args['options'] as $option) {
							//Required fields for option of radio button, not sure whether to allow one option or not on radio button.
							if (!array_key_exists('value', $option) || $option['value'] == '') {
								throw new RTL_Exception('Value parameter missing in the RTL Form Element\'s option');
							} else if (!array_key_exists('label', $option) || $option['label'] == '') {
								throw new RTL_Exception('Label parameter missing in the RTL Form Element\'s option');
							}
						}
						$this->options = $args['options'];
					}
				} else {
					throw new RTL_Exception('No option passed for a radio button field');
				}
			}
			$this->name = $args['name'];
			$this->label = $args['label'];
			$this->type = $args['type'];
			$this->id = $args['id'] == '' ? $this->name : $args['id'];
			$this->class = 'rtl-form-element ' . $args['class'];
			$this->default = $args['default'];
			$this->description = $args['description'];
			$this->disabled = ($args['disabled'] == 'true' || $args['disabled'] == true) ? true : false;
			$this->custom = $args['custom'];
		}

		/**
		 * Renders the form field and generates the output
		 * @param array $override Array to override the default values
		 */
		public function render($override = null) {
			$this->show_header();
			if ($this->type == 'radio' || ($this->type == 'checkbox' && count($this->options) > 1)) {
				$this->show_title_text();
			} else {
				$this->show_title_label();
			}
			if (!$this->custom) {
				$this->show_content();
			} else {
				$this->show_custom();
			}
			$this->show_footer();
		}

		/**
		 * Internal function to output start of the Form Element
		 */
		private function show_header() {
			echo '<tr valign="top">';
		}

		/**
		 * Internal function to output the title of the Form Element within label
		 */
		private function show_title_label() {
			?>
			<th scope="row">
				<label for="<?php echo $this->id ?>"><?php echo $this->label ?></label>
			</th>
			<?php
		}

		/**
		 * Internal function to output the title of the Form Element without label
		 */
		private function show_title_text() {
			?>
			<th scope="row">
				<?php echo $this->label ?>
			</th>
			<?php
		}

		/**
		 * Internal function to output the content of the Form Element depending upon the type of element
		 */
		private function show_content() {
			echo '<td>';
			//Types are text, textarea, color, date, datetime, datetime-local, email, month, number, range, search, tel, time, url, week, number
			switch ($this->type) {
				case 'file' :
				case 'password' :
				case 'hidden' :
				case 'text' : $this->show_content_generic();
					break;
				case 'textarea' : $this->show_content_textarea();
					break;
				case 'radio' : $this->show_content_radio();
					break;
				case 'checkbox' : $this->show_content_checkbox();
					break;
				case 'select' : $this->show_content_select_list();
					break;
			}
			if ($this->description != '') {
				?>
				<span class="description">
					<label for="<?php echo $this->id; ?>"><?php echo $this->description; ?></label>
				</span>
				<?php
			}
			echo '</td>';
		}

		/**
		 * Output the end of the Form Element
		 */
		private function show_footer() {
			echo '</tr>';
		}

		/**
		 * Output the content of text, password, file and hidden type form field
		 */
		private function show_content_generic() {
			?>
			<input type="<?php echo $this->type ?>" value="<?php echo $this->default ?>" name="<?php echo $this->name ?>" id="<?php echo $this->id ?>" class="<?php echo $this->class ?>" />
			<?php
		}

		/**
		 * Output the content of the textarea type form field
		 */
		private function show_content_textarea() {
			?>
			<textarea name="<?php echo $this->name ?>" id="<?php echo $this->id ?>" class="<?php echo $this->class ?>"><?php echo $this->default ?></textarea>
			<?php
		}

		/**
		 * Output content for radio type form field
		 */
		private function show_content_radio() {
			//todo Implement radio buttons
			?>
			<fieldset>
				<legend class="screen-reader-text">
					<span><?php echo $this->label ?></span>
				</legend>
				<p>
					<?php
					$flag = true;
					foreach ($this->options as $option) {
						if ($flag) {
							$flag = false;
						} else {
							echo '<br/>';
						}
						?>
						<label><input name="<?php echo $this->name ?>" type="radio" value="<?php echo $option['value'] ?>" <?php echo (isset($option['checked']) && $option['checked'] == true) ? 'checked="checked"' : ''; ?> /> <?php echo $option['label'] ?></label>
					<?php }
					?>
				</p>
			</fieldset>
			<?php
		}

		/**
		 * Output the content of Select type form field
		 */
		private function show_content_select_list() {
			//todo Implement select list
			?>
			<select name="<?php echo $this->name ?>" id="<?php echo $this->id ?>" class="postform <?php echo $this->class ?>">
				<?php
				foreach ($this->options as $option) {
					?>
					<option value="<?php echo $option['value'] ?>" <?php echo (isset($option['selected']) && $option['selected'] == true) ? 'selected="selected"' : ''; ?>><?php echo $option['label'] ?></option>
					<?php
				}
				?>
			</select>
			<?php
		}

		/**
		 * Output the content of checkbox type form field
		 */
		private function show_content_checkbox() {
			?>
			<fieldset>
				<legend class="screen-reader-text">
					<span><?php echo $this->label ?></span>
				</legend>
				<p>
					<?php
					if (count($this->options) > 1) {
						$flag = true;
						foreach ($this->options as $option) {
							if ($flag) {
								$flag = false;
							} else {
								echo '<br/>';
							}
							?>
							<label><input name="<?php echo $this->name ?>" type="checkbox" value="<?php echo $option['value'] ?>" <?php echo (isset($option['checked']) && $option['checked'] == true) ? 'checked="checked"' : ''; ?> /> <?php echo $option['label'] ?></label>
							<?php
						}
					} else {
						$option = $this->options[0];
						?>
						<label><input name="<?php echo $this->name ?>" id="<?php echo $this->id ?>" type="checkbox" value="<?php echo $option['value'] ?>" <?php echo (isset($option['checked']) && $option['checked'] == true) ? 'checked="checked"' : ''; ?> /> <?php echo $option['label'] ?></label>
						<?php
					}
					?>
				</p>
			</fieldset>
			<?php
		}

		/**
		 * Output the content of custom type form field
		 */
		private function show_custom() {
			
		}

		public function __destruct() {
			//todo Functionality of RTL Form Element Destructor
		}

	}

}

if (!class_exists('RTL_Exception')) {

	Class RTL_Exception Extends Exception {

		public function __construct($message, $code = 0, Exception $previous = null) {
			parent::__construct($message, $code, $previous);
		}

		public function __toString() {
			return __CLASS__ . ": [{$this->code}]: {$this->message} " . ' in ' . parent::getFile() . " on line " . parent::getLine() . "\n";
		}

	}

}

/*
 * Exceptions listing:
 * 10	Label not set while creating an RTL Form Element
 * 11	Name not set while creating an RTL Form Element
 * 12	Field is not an RTL Form Field
 */
?>