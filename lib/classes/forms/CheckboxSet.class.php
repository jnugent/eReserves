<?php

	import('forms.FormField');
	import('forms.Checkbox');

	class CheckboxSet extends FormField {

		private $checkboxes;

		function __construct($properties = array()) {
			parent::__construct();

			$this->checkboxes = array();

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
			if (!is_array($this->getProperty('data'))) {
				$selectedChoices[] = $this->getProperty('data');
			} else {
				$selectedChoices = $this->getProperty('data');
			}

			return $selectedChoices;
		}

		/*
		 * @brief adds a checkbox to this checkbox set.
		 * @param Checkbox the checkbox to add
		 */

		public function addCheckbox($checkbox) {
			$this->checkboxes[] =& $checkbox;
		}

		/*
		 * @brief returns an array of @Checkbox items assigned to this set
		 * @return Array of Checkbox objects
		 */

		public function getCheckboxes() {
			$checkboxes =& $this->checkboxes;
			return $checkboxes;
		}

		/*
		 * @brief generates valid XHTML for the display of this CheckboxSet.
		 * @return String the XHTML representation of this item.
		 */

		public function display() {

			$returner = '';
			$name = htmlentities($this->getProperty('name'));

			$selectedChoices = $this->_getSelectedChoices();

			$returner = '<span class="label">' . $this->getProperty('primaryLabel') . '<span class="small">' . $this->getProperty('secondaryLabel') . '</span></span>';
			$returner .= '<fieldset id="' . $name . '">';
			$returner .= '<legend><span class="hide">' . $this->getProperty('primaryLabel') . '</span></legend>';

			$checkBoxes = $properties['checkBoxes'];

			foreach ($this->getCheckBoxes() as $checkBox) {

				$value = htmlentities($checkBox->getProperty('value'));

				$checked = '';
				if (in_array($checkBox->getProperty('value'), $selectedChoices)) {
					$checked = 'checked="checked"';
				}

				$returner .= '<label for="' . $value . '">';
				$returner .= '<input type="checkbox" ' . $checked . ' class="chkbx" name="' . $name. '" id="' . $name . '_' . $value . '" value="' . $value . '" /> ' .
								$checkBox->getProperty('label');

				$returner .= '</label>';
			}

			$returner .= '</fieldset>';
			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . "\n";
			$choices = $this->_getSelectedChoices();

			$returner .= '* Submitter has chosen: ' . join(', ', $choices) . "\n\n";

			return $returner;
		}
	}
?>