<?php

	import('forms.FormField');

	class Select extends FormField {

		private $options = array();

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 *  @brief generates  list of selected choices that may be passed in via the form
		 *  @return Array checked values
		 */

		private function _getSelectedChoices() {

			$selectedChoices = array();
			if (!is_array($this->getProperty('value'))) {
				$selectedChoices[] = $this->getProperty('value');
			} else {
				$selectedChoices = $this->getProperty('value');
			}

			return $selectedChoices;
		}

		/*
		 * @brief generates valid XHTML for the display of this Select dropdown list.
		 * @return String the XHTML representation of this item.
		 */

		public function display() {

			$returner = '';

			$name = htmlentities($this->getProperty('name'));

			$selectedChoices = $this->_getSelectedChoices();

			$returner = '<label for="' . $name . '">' . $this->getProperty('primaryLabel') . '<span class="small">' . $this->getProperty('secondaryLabel') . '</span></label>';
			$onChange = '';
			if ($this->getProperty('onChange') != '') {
				$onChange = 'onChange="' . $this->getProperty('onChange') . '"';
			}

			$size = '';
			if ($this->getProperty('size') != '') {
				$size = 'size="' . $this->getProperty('size') . '"';
			}

			$multiple = '';
			$optionClass = '';
			if ($this->getProperty('multiple') == true) {
				$multiple = 'multiple="multiple"';
				$optionClass = 'class="optionMultiple"';
			}

			$returner .= '<select ' . $size . ' ' . $onChange . ' name="' . $name . '" id="' . $name . '" ' . $multiple . '>';

			foreach ($this->options as $option) {
				$selected = '';
				if (in_array($option['value'], $selectedChoices)) {
					$selected = 'selected="selected"';
				}

				$label = $option['label'] != '' ? $option['label'] : $option['value'];
				$returner .= '<option ' . $optionClass . ' ' . $selected . ' value="' . htmlentities($option['value']) . '">' . htmlentities($label) . '</option>';
			}

			$returner .= '</select>';

			return $returner;
		}

		/*
		 * @brief adds a new option to this select dropdown.
		 * @param Array the option to add, as an array of properties
		 */

		public function addOption($option) {

			$this->options[] = $option;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . "\n";
			$choices = $this->_getSelectedChoices();

			$returner .= ' * Submitter has chosen: ' . join(', ', $choices) . "\n\n";

			return $returner;
		}
	}
?>
