<?php

	import('forms.FormField');

	class TextArea extends FormField {

		const T_AREA_ROWS	= 8;
		const T_AREA_COLS	= 60;

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief generates the XHTML necessary to display a textarea on an form.
		 * @return String the XHTML for this text area.
		 */

		public function display() {

			$returner = '';

			$name = htmlentities($this->getProperty('name'));
			$value = htmlentities($this->getProperty('value'));

			$returner = '<label for="' . $name . '">' . $this->getProperty('primaryLabel') . ' <span class="small">' . $this->getProperty('secondaryLabel') . '</span></label>' . "\n";
			$returner .= '<textarea id="' . $name . '" name="' . $name . '" rows="' . TextArea::T_AREA_ROWS . '" cols="' . TextArea::T_AREA_COLS . '">' . $value. '</textarea>';
			$returner .= "\n";

			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . ":\n\n";
			$returner .= $this->getProperty('data') != '' ? $this->getProperty('data') . "\n\n" : '';

			return $returner;
		}
	}
?>