<?php

import('forms.FormField');

	class Checkbox extends FormField {

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief generates the XHTML to display this checkbox on a form.
		 * @return String the XHTML for the checkbox
		 */

		public function display() {

			$returner = '';
			$name = htmlentities($this->getProperty('name'));
			$checked = ($this->getProperty('value') != '0' && $this->getProperty('value') != '') ? 'checked="checked"' : '';

			$returner = '<label for="' . $name . '">' . $this->getProperty('primaryLabel') . '<span class="small">' . $this->getProperty('secondaryLabel') . '</span></label>' . "\n";
			$returner .= '<input class="chkbx" type="checkbox" ' . $checked . ' id="' . $name . '" name="' . $name . '" />';
			$returner .= "\n";

			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . "\n\n";
			$returner .= $this->getProperty('value') == '1' ? ' * This checkbox was CHECKED.' . "\n\n" : '';

			return $returner;
		}
	}
?>