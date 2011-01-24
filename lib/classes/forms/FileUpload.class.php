<?php

	import('forms.FormField');

	class FileUpload extends FormField {

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

			$returner = '<label for="' . $name . '">' . $this->getProperty('primaryLabel') . ' <span class="small">' . $this->getProperty('secondaryLabel') . '</span></label>' . "\n";
			$returner .= '<input type="file" id="' . $name . '" name="' . $name . '" value="" />';
			if ($value != '') {
				$returner .= '<br /><span class="small">Original: ' . $value . '</span>';
			}
			$returner .= "\n";

			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner .= "uploaded file not included in emailed response\n\n";
			return $returner;
		}
	}
?>
