<?php

	import('forms.FormField');

	class TextField extends FormField {

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 *  @brief Generates an XHTML representation of this field for inclusion on a Form
		 *  @return String the XHTML for the item
		 */

		public function display() {

			$returner = '';

			$name = htmlentities($this->getProperty('name'));
			$value = htmlentities($this->getProperty('value'));

			$type = 'text';

			if (is_a($this, 'Password')) {
				$type = 'password';
			}

			$returner = '<label for="' . $name . '">' . $this->getProperty('primaryLabel') . ' <span class="small">' . $this->getProperty('secondaryLabel') . '</span></label>' . "\n";
			$returner .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" value="' . $value . '" />';
			$returner .= "\n";

			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . ": ";
			if (!is_a($this, 'Password')) {
				$returner .= $this->getProperty('data') != '' ? $this->getProperty('data') . "\n\n" : '';
			} else {
				$returner .= "password not included in emailed response\n\n";
			}
			return $returner;
		}
	}
?>
