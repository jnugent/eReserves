<?php

	import('forms.FormField');

	class FieldSet extends FormField {

		private $fields;

		function __construct($properties = array()) {
			parent::__construct();

			$this->fields = array();

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief adds a field to this fieldset.
		 * @param Field to add
		 */

		public function addField($field) {
			$this->fields[] =& $field;
		}

		/*
		 * @brief returns an array of @Field items assigned to this set
		 * @return Array of FormField objects
		 */

		public function getFields() {
			$fields =& $this->fields;
			return $fields;
		}

		/*
		 * @brief generates valid XHTML for the display of this FieldSet.
		 * @return String the XHTML representation of this item.
		 */

		public function display() {

			$name = htmlentities($this->getProperty('name'));
			$returner = '<fieldset>' . "\n";
			$returner .= '<legend>' . $this->getProperty('legend') . '</legend>';
			$fields = $this->getFields();

			$returner .= '<ul>';
			foreach ($fields as $field) {

				$classesForList = array();

				if ($field->isRequired()) {
					$classesForList[] = 'required';
				}
				$classesForList[] = is_a($field, 'HiddenField') ? 'hide' : 'row';
				$returner .= "\n" . '<li id="li-' . $field->getProperty('name'). '" class="' . join(" ", $classesForList)  . '">' . "\n";
				$returner .= $field->display();
				$returner .= '</li>' . "\n";
			}

			$returner .= '</ul>';
			$returner .= '</fieldset>';
			return $returner;
		}

		/*
		 * @brief Should return plain text describing this field's appearance.
		 * @return String the plain text for the field item
		 */

		public function formatForMail() {

			$returner = '';

			foreach ($fields as $field) {
				$returner .= $field->formatForMail();
			}

			return $returner;
		}
	}
?>
