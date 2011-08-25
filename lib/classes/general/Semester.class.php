<?php

class Semester {

	public function __construct($semesterID = 0) {

		if ($semesterID > 0) {
			$db = getDB();
			$sql = "SELECT semesterID, year, term, startDate, endDate, isCurrent, isActive, sequence FROM semester WHERE semesterID = ?";
			$returnStatement = $db->Execute($sql, array($semesterID));
			if ($returnStatement->RecordCount() ==  1) {
				$recordRow = $returnStatement->GetRowAssoc(FALSE);
				foreach ($recordRow AS $key => $value) {
					$this->setAttribute($key, $value);
				}
				return true;
			} else {
				return false;
			}
		} else { // loading a blank Semester, probably in order to create a new one.

			$this->setAttribute('semesterid', '0');
			return true;
		}
	}

	static function getSemesters() {
		$db = getDB();
		$sql = "SELECT semesterID from semester ORDER BY sequence ASC";
		$returnStatement = $db->Execute($sql, array());
		if ($returnStatement) {
			$semesters = array();
			while ($recordObject = $returnStatement->FetchNextObject()) {
				$semesters[] = new Semester($recordObject->SEMESTERID);
			}

			return $semesters;
		}
	}

	static function updateSequence($semesterIDs) {
		$db = getDB();
		$sequence = 0;
		foreach ($semesterIDs as $semesterID) {
			if ($semesterID > 0) {
				error_log('setting ' .  $semesterID . ' to ' . $sequence);
				$sequence ++;
				$db->Execute("UPDATE semester SET sequence = ? WHERE semesterID = ?", array($sequence, intval($semesterID)));
			}
		}
	}

	/**
	 * @brief fetches an attribute from the attributes array created when an item is instantiated.
	 * @param String $attribute the attribute to fetch.
	 * @return Mixed the attribute value.
	 */
	 function getAttribute($attribute) {
		if (array_key_exists($attribute, $this->_properties)) {
			return $this->_properties[$attribute];
		} else {
			return '';
		}
	}

	/**
	 * @brief sets an attribute of an item.  Probably when an item is instantiated in the __construct() call.
	 * @param $attributeName the attribute to set.
	 * @param $attributeValue the new value.
	 */
	 function setAttribute($attributeName, $attributeValue) {
		$this->_properties[$attributeName] = $attributeValue;
	}

	/**
	 * @brief convenience function for getting the Semester ID
	 * @return Int the semester id.
	 */
	function getSemesterID() {
		$returner = $this->getAttribute('semesterid');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the Semester year.
	 * @return String the year.
	 */
	function getYear() {
		$returner = $this->getAttribute('year');
		return $returner;
	}

	/**
	 * @brief convenience method for getting the term.
	 * return String the term for this Semester.
	 */
	function getTerm() {
		$returner = $this->getAttribute('term');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the Semester start date.
	 * @return String the date.
	 */
	function getStartDate() {
		$returner = $this->getAttribute('startdate');
		return $returner;
	}

	/**
	 * @brief convenience method for getting the end date.
	 * return String the end date for this Semester.
	 */
	function getEndDate() {
		$returner = $this->getAttribute('enddate');
		return $returner;
	}

	/**
	 * @brief convenience function for determining if this is the current semester
	 * @return boolean.
	 */
	function isCurrent() {
		$isCurrent = $this->getAttribute('iscurrent') == '1' ? true : false;
		if ($isCurrent) { return true; }
		else {
			import('items.Section');
			$currentSemesterTerm = Section::getCurrentSemester();
			if ($currentSemesterTerm == $this->getYear() . $this->getTerm()) {
				return true;
			} else {
				return false;
			}
			
		}			
	}

	/**
	 * @brief convenience method for determining if this should be active in the list.
	 * return String the term for this Semester.
	 */
	function isActive() {
		$returner = $this->getAttribute('isactive') == '1' ? true : false;
		return $returner;
	}

	/**
	 * @brief updates or creates a Semester record in the database by pulling out the bits passed in when the form was submitted.
	 * @return boolean true or false, if the update succeeded or not.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$term = ReservesRequest::getRequestValue('term');
		$year = ReservesRequest::getRequestValue('year');
		$startdate = ReservesRequest::getRequestValue('startdate');
		$enddate = ReservesRequest::getRequestValue('enddate');
		$iscurrent = ReservesRequest::getRequestValue('iscurrent') != '' ? '1' : '0';
		$isactive = ReservesRequest::getRequestValue('isactive') != '' ? '1' : '0';
		$delete = ReservesRequest::getRequestValue('delete') != '' ? '1' : '0';

		$semesterid = $this->getSemesterID();

		if ($delete) {
			if ($semesterid > 0) {
				$this->delete();
				return true;
			}
		}

		if ($semesterid > 0) {
			$sql = "UPDATE semester SET year = ?, term = ?, startDate = ?, endDate = ?, isCurrent = ?, isActive = ? WHERE semesterID = ?";
		} else {
			$sql = "INSERT INTO semester (year, term, startDate, endDate, isCurrent, isActive, semesterid) VALUES (?, ?, ?, ?, ?, ?, ?)";
		}
		$returnStatement = $db->Execute($sql, array($year, $term, $startdate, $enddate, $iscurrent, $isactive, $semesterid));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  @brief Deletes this Semester.
	 *  @return boolean success or not.
	 */
	function delete() {

		$db = getDB();
		$sql = 'DELETE FROM semester WHERE semesterid = ?';
		$returnStatement = $db->Execute($sql, array($this->getSemesterID()));
		if ($returnStatement) {
			$this->setAttribute('semesterid', '0');
			import('general.ReservesRequest');
			ReservesRequest::doRedirect('adminSemesters/0');
		} else {
			return false;
		}
	}

	/**
	 * @brief function which assembles a Form object representing this item heading, so it can be edited by a course admin.
	 * @return Form the form object.
	 */
	function assembleEditForm($basePath) {

		import('forms.Form');
		$action = 'adminSemesters';

		$form = new Form(array('id' => $action, 'method' => 'post', 'action' => $basePath . '/index.php/' . $action . '/' . $this->getSemesterID()));
		$label = $this->getSemesterID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . '  Semester'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'semesterid', 'value' => $this->getSemesterID() ) ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Year', 'secondaryLabel' => 'four digit year', 'name' => 'year',
							'value' => $this->getAttribute('year'), 'requiredMsg' => 'Please enter a year') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Term', 'secondaryLabel' => 'two letter term code', 'name' => 'term',
							'value' => $this->getAttribute('term'), 'requiredMsg' => 'Please enter a term code') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Start Date', 'secondaryLabel' => 'term start date', 'name' => 'startdate',
							'value' => $this->getAttribute('startdate'), 'requiredMsg' => 'Please enter a start date') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'End Date', 'secondaryLabel' => 'term end date', 'name' => 'enddate',
							'value' => $this->getAttribute('enddate'), 'requiredMsg' => 'Please enter an end date') ));
		$fieldSet->addField(new Checkbox( array('name' => 'iscurrent', 'primaryLabel' => 'Current Semester?', 'value' => $this->getAttribute('iscurrent')) ) );
		$fieldSet->addField(new Checkbox( array('name' => 'isactive', 'primaryLabel' => 'Still active?', 'value' => $this->getAttribute('isactive')) ) );
		$fieldSet->addField(new Checkbox( array('name' => 'delete', 'primaryLabel' => 'Check to delete', 'value' => '') ) );

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
