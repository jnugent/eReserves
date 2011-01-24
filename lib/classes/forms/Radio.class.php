<?php

	import('forms.FormField');

	class Radio extends FormField {

		private $buttons = array();

		function __construct($properties = array()) {
			parent::__construct();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief generates the XHTML to display this set of radio buttons on a form.
		 * @return String the XHTML for the radio button set
		 */

		public function display() {

			$returner = '';
			$name = htmlentities($this->getProperty('name'));
			$stackButtons = $this->getProperty('stackButtons');
			$returner = '<span class="label">' . $this->getProperty('primaryLabel') . '<span class="small">' . $this->getProperty('secondaryLabel') . '</span></span>';
			$returner .= '<fieldset id="' . $this->getProperty('fieldsetID') . '">';
			$returner .= '<legend><span class="hide">' . $this->getProperty('primaryLabel') . '</span></legend>';


			foreach ($this->buttons as $radioButton) {
				$caption = $radioButton['caption'] != '' ? $radioButton['caption'] : $radioButton['value'];

				$returner .= '<label for="' . $radioButton['id'] . '">';
				$checked = $this->getProperty('value') == $radioButton['value'] ? 'checked="checked"' : '';

				$returner .= '<input type="radio" class="radio" ' . $checked . ' name="' . $name. '" id="' . $name . '" value="' . htmlentities($radioButton['value']) . '" /> '
				. htmlentities($caption);
				$returner .= '</label>';
			}

			$returner .= '</fieldset>';

			return $returner;
		}

		/*
		 * @brief adds a new button to this set of Radio buttons
		 * @param Array the properties for the button to be added
		 */

		public function addButton($button) {

			$this->buttons[] = $button;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';
			$returner .= $this->getProperty('primaryLabel') . "\n";
			$returner .= $this->getProperty('data') != '' ? '* Submitter has chosen: ' . $this->getProperty('data') . "\n\n" : '';

			return $returner;
		}
	}
?>
