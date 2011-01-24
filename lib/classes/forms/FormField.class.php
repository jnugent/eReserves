<?php

define("PHONEUS_REGEX", '^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$');
define("EMAIL_REGEX",   '^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$');

define("T_AREA_ROWS", "8");
define("T_AREA_COLS", "60");

class FormField {

	/* an @array of field properties for this Form item */
	private $_properties;

	function __construct() {
		$this->_properties = array();
	}

	/*
	 * @brief sets the field property to the correct valie
	 * @param String name the name of the field property
	 * @param String value the value of the field property
	 */

	public function setProperty($name, $value) {
		$this->_properties[$name] = $value;
	}

	/*
	 * @brief returns the value of the given property
	 * @param the name of the field to get
	 * @return String the value of the property
	 */

	public function getProperty($name) {
		$value = '';
		if (array_key_exists($name, $this->_properties)) {
			$value = $this->_properties[$name];
		}
		return $value;
	}

	/*
	 * @brief accessor method to determine whether or not this field is mandatory
	 * @return boolean
	 */

	public function isRequired() {
		if ($this->getProperty('required') == 'true') {
			return true;
		}
		else {
			return false;
		}
	}

	/*
	 * @brief abstract method that must be overridden for field display.  Should return XHTML describing
	 * this field's appearance.
	 */

	public function display() {
		assert(false);
	}

	/*
	 * @brief abstract method that must be overridden for field inclusion in an enail.  Should return plain text describing
	 * this field's appearance.
	 */

	public function formatForMail() {
		assert(false);
	}

	/*
	 * @brief generates a segment of JSON describing the properties of this field, as per JQuery's ruleset for Form validation.
	 * @return String a valid JSON segment
	 */

	public function getJSONRule() {

		if ($this->isRequired()) {
			$rules[] = "required: true";
		}

		switch ($this->getProperty('name')) {

			case 'email':
				$rules[] = "email: true";
				break;

			case 'phone':
				$rules[] = "phoneUS: true";
				break;

			default:
				if ($this->getProperty('digitsOnly') == 'true') {
					$rules[] = "digits: true";
				}
				break;
		}

		if ($this->getProperty('minlength') > 0) {
			$rules[] = "minlength: " . $this->getProperty('minlength');
		}

		if (sizeof($rules) < 2) {
			if ($this->isRequired() || $this->getProperty('validationDep') != '') {
				if ($this->getProperty('validationDep') == '') {
					$returner = '"' . $this->getProperty('name') . '": "required"';
				} else {
					$returner .= '"' . $this->getProperty('name') . '"' . ': { required: ' . $this->getProperty('validationDep') . '}';
				}
			}
		} else {
			$returner = '"' . $this->getProperty('name') . '"' . ': {';
			$returner .= implode(", ", $rules);
			$returner .= '}';
		}
		return $returner;
	}

	/*
	 * @brief generates a JSON segment containing the message to be displayed if this field is required and/or fails validation
	 * @return String a valid JSON segment
	 */

	public function getJSONMessage() {

		$requiredMsg = $this->getProperty('requiredMsg');
		if ($requiredMsg == '') {
			$requiredMsg = 'Please complete the field';
		}

		$returner = $this->getProperty('name') . ': {';

		if ($this->isRequired()) {
			$rules[] = 'required: "' . $requiredMsg . '"';
		}

		switch ($this->getProperty('name')) {

			case 'email':
				$rules[] = 'email: "Please enter a valid email address"';
				break;

			case 'phone':
				$rules[] = 'phoneUS: "Please enter a valid phone number"';
				break;
		}

		if (sizeof($rules) < 2) {
			$returner = '"' . $this->getProperty('name') . '": ';
			$returner .= '"' . $requiredMsg . '"';
		} else {
			$returner = '"' . $this->getProperty('name') . '": {';
			$returner .= implode(", ", $rules);
			$returner .= '}';
		}

		return $returner;
	}

	/*
	 * @brief this function must be overridden in child classes to determine
	 * if the field content is correct.
	 * @param String $name the field name
	 * @param String $type the field class type
	 * @param String $fieldContent the content of the field item when the form is submitted
	 * @return boolean is it valid
	 */

	public static function validate($name, $type, $fieldContent) {

		switch ($type) {

			case "Password":
			case "TextArea":
			case "TextField":
				if ($name == 'email') {
					if (preg_match("/" . EMAIL_REGEX . "/", $fieldContent)) {
						return true;
					} else
					return false;
				} else if ($name == 'phone') {
					if (preg_match("/" . PHONEUS_REGEX . "/", $fieldContent)) {
						return true;
					} else
					return false;
				}

				// default is to ensure that the field wasn't completely empty
				if ($fieldContent != '') {
					return true;
				}
				break;

			case "Checkbox":
				if ($fieldContent == 'on') {
					return true;
				}
				break ;

			case "Select":
			case "Radio":
				if ($fieldContent != '') {
					return true;
				}
				break;

			default:
				return true;
				break;
		}

		return false;
	}
}
?>