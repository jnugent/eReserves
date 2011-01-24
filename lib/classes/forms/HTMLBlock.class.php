<?php

	import('forms.FormField');

	class HTMLBlock extends FormField {

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief accessor method to determine whether or not this field is mandatory
		 * @return false since these will never be required
		 */

		public function isRequired() {
			return false;
		}

		/*
		 * @brief displays a block of HTML on a form
		 * @return the XHTML for the button
		 */

		public function display() {

			$returner = '';
			$returner .= $this->getProperty('content');
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