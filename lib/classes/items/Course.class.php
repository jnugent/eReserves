<?php

import('items.ElectronicReserveItem');

class Course extends ElectronicReserveItem {

	function __construct($courseID = 0) {

		if ($courseID > 0) {
			$db = getDB();
			$sql = "SELECT courseID, courseName, prefix, courseNumber, visible FROM course WHERE courseID = ?";
			$returnStatement = $db->Execute($sql, array($courseID));
			if ($returnStatement->RecordCount() ==  1) {
				$recordRow = $returnStatement->GetRowAssoc(FALSE);
				foreach ($recordRow AS $key => $value) {
					$this->setAttribute($key, $value);
				}
				return true;
			} else {
				return false;
			}
		} else { // loading a blank Course, in order to create a new one.

			$this->setAttribute('courseid', '0');
			return true;
		}
	}

	/**
	 * @brief returns the UNB program title for a code, by querying the programName table. For example, ED -> Education.
	 * @param String $code the program code
	 * @return String the program name
	 */
	private function _getProgramNameByCode($code) {
		$db = getDB();
		$returnStatement = $db->Execute('SELECT programName FROM programPrefix p WHERE p.programPrefix = ?', array($code));
		if ($returnStatement->RecordCount() == 1) {
			$recordRow = $returnStatement->FetchNextObject();
			$code = $recordRow->PROGRAMNAME;
			return $code;
		} else {
			return '';
		}
	}

	/**
	 * @brief convenience function for getting the course ID
	 * @return Int the course id
	 */
	function getCourseID() {
		$returner = $this->getAttribute('courseid');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the course name
	 * @return String the course name, ie "English for Dummies"
	 */
	function getCourseName() {
		$returner = $this->getAttribute('coursename');
		return $returner;
	}

	/**
	 * @brief this concatenates the relevant bits of the course info to build the internal
	 * string used by other applications, like the calendar and BlackBoard
	 * @return String the code for the course
	 */
	function getCourseCode() {

		$returner = $this->getAttribute('prefix') . '*' . $this->getAttribute('coursenumber');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the section ID
	 * @return Int the section id
	 */
	function getPrefix() {
		$returner = $this->getAttribute('prefix');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the section ID
	 * @return Int the section id
	 */
	function getNumber() {
		$returner = $this->getAttribute('coursenumber');
		return $returner;
	}

	/**
	 * @brief returns a list of Section objects that have been created for this course.
	 * @return array $sections the sections for this course
	 */
	function getSections() {

		$db = getDB();
		$sql = "SELECT s.sectionID FROM section s WHERE s.courseID = ?";
		$returnStatement = $db->Execute($sql, array($this->getCourseID()));

		import('items.Section');
		$sections = array();

		while ($recordObject = $returnStatement->FetchNextObject()) {
			$sections[] = new Section($recordObject->SECTIONID);
		}

		return $sections;
	}

	/**
	 * @brief a static function that returns an array of the MySQL fields in the course table
	 * to search with the regular MySQL keyword search
	 * @return array the field names
	 */
	public static function getSearchFields($only_prefix = false) {
		if ($only_prefix) {
			$returner = array('prefix');
		} else {
			$returner = array('courseName', 'prefix', 'courseNumber');
		}
		return $returner;
	}

	/**
	 * @brief updates or creates a Course record in the database by pulling out the bits passed in when the form was submitted.
	 * @return boolean true or false, if the updated succeeded or not.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$coursename = ReservesRequest::getRequestValue('coursename');
		$prefix = ReservesRequest::getRequestValue('prefix');
		$coursenumber = ReservesRequest::getRequestValue('coursenumber');
		$courseid = ReservesRequest::getRequestValue('courseid');
		$visible = ReservesRequest::getRequestValue('visible') != '' ? '1' : '0';

		if ($courseid > 0) {
			$sql = "UPDATE course SET courseName = ?,  prefix = ?, courseNumber = ?, visible = ? WHERE courseID = ?";
		} else {
			$sql = "INSERT INTO course (courseName,  prefix, courseNumber, visible, courseID) VALUES (?, ?, ?, ?, ?)";
		}
		$returnStatement = $db->Execute($sql, array($coursename, $prefix, $coursenumber, $visible, $courseid));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief updates the order of Item Headings assigned to this Course object.
	 * @param $itemHeadingIDs an array of the IDs, in the order they should be sorted.
	 */
	function updateItemHeadingSequence($itemHeadingIDs) {

		$db = getDB();
		$sequence = 0;
		foreach ($itemHeadingIDs as $headingID) {
			if ($headingID > 0) {
				$sequence ++;
				$db->Execute("UPDATE itemHeading SET sequence = ? WHERE itemHeadingID = ?", array($sequence, intval($headingID)));
			}
		}
	}

	/**
	 * @brief function which assembles a Form object representing this course, so it can be edited by a course admin.
	 * @return Form the form object
	 */
	function assembleEditForm(&$basePath) {

		import('forms.Form');
		$action = $this->getCourseID() > 0 ? 'editCourse' : 'createNewCourse';

		$form = new Form(array('id' => 'editCourse', 'method' => 'post', 'action' => $basePath . '/index.php/' . $action . '/' . $this->getCourseID()));
		$label = $this->getCourseID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . ' Course'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'courseid', 'value' => $this->getCourseID()) ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Course Name', 'secondaryLabel' => 'plain-text course name', 'name' => 'coursename',
							'value' => $this->getCourseName(), 'requiredMsg' => 'Please enter a course name') ));
		$program = $this->_getProgramNameByCode($this->getAttribute('prefix'));

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Course Prefix', 'secondaryLabel' => $program != '' ? "Currently: $program" : 'ie, ED', 'name' => 'prefix',
							'value' => $this->getAttribute('prefix'), 'requiredMsg' => 'Please enter a prefix') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Course Number', 'secondaryLabel' => 'ie, 1234', 'name' => 'coursenumber',
							'value' => $this->getAttribute('coursenumber'), 'digitsOnly' => true, 'requiredMsg' => 'Please enter digits only') ));

		$fieldSet->addField(new Checkbox( array('primaryLabel' => 'Course Visible?', 'secondaryLabel' => '', 'name' => 'visibile',
							'value' => $this->getAttribute('visible'), ) ));
		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>