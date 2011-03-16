<?php

	import('forms.FormField');

	class Button extends FormField {

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief displays a button on a form
		 * @return the XHTML for the button
		 */

		public function display() {

			$returner = '';
			$class = "strongButton";
			if ($this->getProperty('buttonClass') == 'tiny') {
				$class = "tiny";
			}
			$returner .= '<input class="' . $class . '" type="' . $this->getProperty('type') . '" value="' . $this->getProperty('label') . '"/>';
			return $returner;
		}

		/*
		 * @brief we provide this method stub to override the false assertion in FormField.  This should be modified
		 * if buttons should ever be included in an emailed response.
		 * @return String an empty string (for now)
		 */

		public function formatForMail() {
			return '';
		}
	}
?>