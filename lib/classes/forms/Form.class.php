<?php

	import('forms.TextField');
	import('forms.FileUpload');
	import('forms.HiddenField');
	import('forms.Button');
	import('forms.Select');
	import('forms.Radio');
	import('forms.TextArea');
	import('forms.Checkbox');
	import('forms.CheckboxSet');
	import('forms.Password');
	import('forms.HTMLBlock');
	import('forms.FieldSet');

	define("CRYPT_SECRET_KEY", "E12diq57q90bVceq");
	define("CRYPT_IV", "f64a3248");

	define("FORM_SUBMISSION_WAITTIME_MIN", "0");
	define("FORM_SUBMISSION_WAITTIME_MAX", "3600");

	class Form {

		private $_properties = array();
		private $_fields = array();
		private $_fieldsets = array();

		private $_additionalDetails = array();
		private $_requiredFieldNames = array();

		function __construct($properties = array()) {

			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}

		/*
		 * @brief private method for returning the list of @FormField items added to this form.
		 * @return Array the form fields.
		 */
		private function _getFieldSets() {
			return $this->_fields;
		}

		/*
		 * @brief private method for returning the list of @FormField items added to the additional details section
		 * @return Array the form fields.
		 */
		private function _getAdditionalDetails() {
			return $this->_additionalDetails;
		}

		/*
		 * @brief a private helper method for returning the list of fields required by the form.  Used for server-side validation.
		 * @return Array the field names
		 */
		private function _getRequiredFieldNames() {
			return $this->_requiredFieldNames;
		}

		/*
		 * @brief assembles the required field names in a comma-delimited string and then encrypts them for inclusion
		 * on the Form. Used for server-side validation.
		 * @return String the encrypted string of field names.
		 */
		private function _getEncryptedRequiredFieldNames() {

			$encrypted = encryptString(serialize($this->_getRequiredFieldNames()));
			return $encrypted;
		}

		/*
		 * @brief generates an encrypted representation of the current time.  Used in time-lag spam prevention and detection of form submission
		 * @return String the mcrypted string
		 */
		private function _getEncryptedSubmissionTimestamp() {
			$encrypted = encryptString(time());
			return $encrypted;
		}

		/*
		 * @brief sends a 302 Redirect header to the browser, redirecting them to a response page for the form
		 */
		private function _redirectResponse() {
			if ($this->getProperty('responsePage') != '') {
				header("Location: " . $this->getProperty('responsePage'));
			}
		}

		/*
		 * @brief generates an email containing the form contents and sends them to the email address defined in the mail property of the form.
		 * @return boolean whether the mail was sent or not
		 */
		private function _mailForm() {

			import('general.Config');
			$config = new Config();
			$emailAddress = $config->getSetting('general', 'email_address');
			$mailHeaders = 'From: ' . $emailAddress;

			$subject = 'Form submission from ' . $_SERVER['REQUEST_URI']; // FIXME should probably be some sort of subject based on a template or an i18n locale


			$messagePayload = '';

			foreach ($this->_getFieldSets() as $fieldSet) {
				foreach ($fieldSet->getFields() as $field) {
					$messagePayload .= $field->formatForMail();
				}
			}


			$retVal = mail($emailAddress, $subject, $messagePayload, $mailHeaders);

			if ($this->getProperty('sendConfirmationEmail')) {

				import('general.ReservesRequest');
				$address = ReservesRequest::getRequestValue('email');
				$subject = 'Your submission confirmation';
				$messagePayload = $this->getProperty('extraMailNote') . "\n\nThank you for your submission.  Please consider this a confirmation.  We have included what you submitted below: \n\n" . $messagePayload;

				$retVal = mail($address, $subject, $messagePayload, $mailHeaders);
			}

			return $retVal;
		}

		/**
		 * @brief convenience method to see if form submission limits for Spam prevention have been abled in the config file
		 * @return boolean true or false
		 */
		private function _submissionTimesEnabled() {

			import('general.Config');
			$config = new Config();
			$useLimits = $config->getSetting('form', 'use_submission_time_limits');
			if ($useLimits || $useLimits == 'On') {
				return true;
			} else {
				return false;
			}
		}

		/*
		 * @brief determines if a form is valid by ensuring that all mandatory form items contain valid data upon submission.
		 * @return boolean the form's validation state
		 */
		public function isValidSubmission() {

			import('general.ReservesRequest');
			$submittedTimeStamp = decryptString(ReservesRequest::getRequestValue('submitted'));
			$invalidFields = array();
			$time = time();
			// test for overly fast form submission - spam bot prevention, part 1

			if (!self::_submissionTimesEnabled() || ($time - intval($submittedTimeStamp) >= FORM_SUBMISSION_WAITTIME_MIN) && ($time - intval($submittedTimeStamp) <= FORM_SUBMISSION_WAITTIME_MAX)) {
				$requiredFields = array();
				$requiredFields = unserialize(decryptString(ReservesRequest::getRequestValue('requiredFields')));

				if (is_array($requiredFields) && sizeof($requiredFields) > 0) {
					foreach ($requiredFields as $requiredFieldName => $requiredFieldType) {

						$requiredFieldValue = ReservesRequest::getRequestValue($requiredFieldName);
						assert($requiredFieldType != '');
						if (!FormField::validate($requiredFieldName, $requiredFieldType, $requiredFieldValue)) {
							$invalidFields[] = $requiredFieldName;
						}
					}
				} else {
					return false; // there are apparently no required fields in this form
				}
				if (sizeof($invalidFields) > 0) {
					return $invalidFields;
				} else {
					return true;
				}

			} else {
				return false;
			}
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
		 * @brief adds a @FormField item to this form and also logs its name to the list of
		 * required elements if it is required for validation.
		 * @param FormField the field to add
		 */
		public function addFieldSet($fieldSet) {

			import('general.ReservesRequest');
			foreach ($fieldSet->getFields() as $field) {
				$name = preg_replace("/[\[\]]/", "", $field->getProperty('name'));
				if ($field->isRequired()) {
					$this->_requiredFieldNames[$name] = get_class($field);
				}
				$field->setProperty('data', ReservesRequest::getRequestValue($name));
			}

			$this->_fields[] =& $fieldSet;
		}

		/*
		 * @brief determines if a form has been submitted by testing for the presence of a submitted field element.
		 * Note: this does not validate the field item.
		 * @return Boolean whether the form has been submitted or not.
		 */
		public function hasBeenSubmitted() {

			import('general.ReservesRequest');
			if (ReservesRequest::getRequestValue('submitted')) {
				return true;
			} else {
				return false;
			}
		}

		/*
		 * @brief public method for generating the XHTML to display this form.  Calls the display() methods present in all of the fields
		 * on the form.
		 * @return String the XHTML representation of this Form.
		 */
		public function display() {

			$this->buildJSON();

			if ($this->getProperty('processed')) {
				echo '<p><strong>Your submission has been processed.  You may submit another request.  To make this easier, we have preserved your form information below.</strong></p>';
			}

			echo $this->getProperty('submissionValidationError');
			/*
			echo '<div class="error"><p class="caution">Your appear to have JavaScript disabled. Please turn on JavaScript to enable help correctly completely your form.</p></div>';
			*/

			$enctype = $this->getProperty('enctype') != '' ? 'enctype="' . $this->getProperty('enctype') . '"' : '';
			echo '<form class="valid" ' . $enctype . ' action="' . $this->getProperty('action') . '" method="' . $this->getProperty('method') . '" id="' . $this->getProperty('id') . '">';
			echo '<div class="hiddenFields"><input type="hidden" name="requiredFields" value="' . $this->_getEncryptedRequiredFieldNames() . '" />';
			echo '<input type="hidden" name="submitted" value="' . $this->_getEncryptedSubmissionTimestamp() . '" /></div>';
			foreach ($this->_getFieldSets() as $fieldSet) {
				echo $fieldSet->display();
			}

			echo '</form>';
		}

		/*
		 * @brief public method for generating the entire JSON block for this form.  Calls each JSON validation block for both validation and message
		 * creation, for all items on the form. Uses PHP's json_decode method to ensure that the JSON created is indeed valid.
		 * @return String a valid JSON block.
		 */
		public function buildJSON() {

			$rules = array();
			$messages = array();
			$fields = array();
			foreach ($this->_getFieldSets() as $fieldSet) {
				$fields = array_merge($fieldSet->getFields(), $fields);
			}

			foreach ($fields as $field) {
				if ($field->getProperty('name') != '') {
					if ($field->getJSONRule() != '') {
						$rules[] =  $field->getJSONRule();
					}
					if ($field->getJSONMessage() != '') {
						$messages[] = $field->getJSONMessage();
					}
				}
			}

			$json = '';

			$returner = '<script type="text/javascript">' . "\n";
			$returner .= '$().ready(function() {' . "\n";

/*
			$returner .= 'jQuery.validator.addMethod("phoneUS", function(phone_number, element) {
							phone_number = phone_number.replace(/\s+/g, "");
							return this.optional(element) || phone_number.length > 9 &&
							phone_number.match(/' . PHONEUS_REGEX . '/);}, "Please specify a valid phone number");';
*/


			$returner .= "\n\n"	. '$("#' . $this->getProperty('id') . '").validate(' . "\n";

			$json .= '{	"rules": {' . "\n";

			$json .= join(", \n", $rules);

			$json .= "\n" . '		},' . "\n";

			$json .= '		"messages": {' . "\n";

			$json .= join(", ", $messages);

			$json .= "\n" . '		}' . "\n";

			$json .= '	}' . "\n";

			$json_decode = json_decode($json);
			$returner .= $json;

			$returner .= ');	});' . "\n";

			$returner .= '</script>' . "\n";
			echo $returner;
		}

		/*
		 * @brief process a form. Test validation, and send mail.  If there is a response header set, generate a 302 redirection to
		 * that page.
		 * @return boolean the outcome of the process call.  true if everything worked okay.  False if the form failed validation.
		 */
		public function process() {

			$invalidFields = $this->isValidSubmission();

			if (sizeof($invalidFields) == 0) {
				if ($this->_mailForm()) {
					$this->_redirectResponse();
					$this->setProperty('processed', true);
					return true;
				} else {
					return false;
				}
			} else {
				$this->setProperty('submissionValidationError', '<p><strong>There was at least one missing field on your form: ' . join(', ', $invalidFields) . '</strong></p>');
				return false;
			}
		}
	}
?>
