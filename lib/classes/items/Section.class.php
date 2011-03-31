<?php

import('items.ElectronicReserveItem');

class Section extends ElectronicReserveItem {

	function __construct($sectionID = 0) {

		if ($sectionID > 0) {
			$db = getDB();
			$sql = "SELECT s.sectionID, s.year, s.term, s.sectionNumber, s.courseID, s.visible, c.prefix, c.courseNumber, c.courseName
					FROM section s, course c
					WHERE s.sectionID = ? and s.courseID = c.courseID";
			$returnStatement = $db->Execute($sql, array($sectionID));
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

			$this->setAttribute('sectionid', '0');
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
	 * @brief Static method for splitting up the calendar code and returning the bits and pieces
	 * @param String $code (see source for an example)
	 * @return Array the fragments
	 */
	static function parseCalendarCode($code) {

		// 2010FA_CS_4735_FR01A (from LDAP) or 2010/FA ADM*2313*FRE1A (from Datatel)

		$wholeMatch = $year = $semester = $prefix = $number = $section = '';

		if (preg_match('|^(\d{4})(\w{2})_(\w+)_(\d+)_(.*)$|', $code, $matches)) {
			list ($wholeMatch, $year, $semester, $prefix, $number, $section) = $matches;
		} else if (preg_match('|^(\d+)/(\w+)\s+(\w+)\*(\d+)\*(.*)$|', $code, $matches)) {
			list ($wholeMatch, $year, $semester, $prefix, $number, $section) = $matches;
		} else {
			return false;
		}

		return array('year' => $year, 'semester' => $semester, 'prefix' => $prefix, 'number' => $number, 'section' => $section);
	}

	/**
	 * @brief Static method for building a Section based on the Calendar code format
	 * @param String $code the section code like 2010FA_CS*1000_FR01A or something
	 * @return Section the section object if it can be created, null otherwise
	 */
	static function getSectionFromCalendarCode($code) {

		if ($code != '') {

			$parsedCode = self::parseCalendarCode($code);

			$db = getDB();
			$sql = 'SELECT s.sectionID FROM section s, course c WHERE s.year = ? AND s.term = ? AND s.sectionNumber = ? AND c.prefix = ? and c.courseNumber = ? AND
					s.courseID = c.courseID';
			$sqlParams = array($parsedCode['year'], $parsedCode['semester'], $parsedCode['section'], $parsedCode['prefix'], $parsedCode['number']);

			$returnStatement = $db->Execute($sql, $sqlParams);

			if ($returnStatement->RecordCount() == 1) {

				$recordRow = $returnStatement->FetchNextObject();
				$sectionID = $recordRow->SECTIONID;
				return new Section($sectionID);
			} else {
				error_log('Unable to find section ID for code: ' . $code);
				error_log('SQL Parameters: ' . var_export($sqlParams, TRUE));
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @brief a convenience function for building a list of moving-window semester terms for a given course. It centers the current year in a ten year
	 * window, and returns an array of each of those ten years with the semester codes attached to them.
	 * @param $noFutureTerms boolean whether or not to display sections that are in the future
	 * @return Array the semesters
	 */
	static function _getPlausibleSemesters($noFutureYears = false) {

		$db = getDB();
		$currentYear = intval(date('Y'));
		$fiveYearsPrior = $currentYear - 1;
		$fiveYearsHence = $currentYear + 1;
		$semesterTerms = array('WI', 'FA', 'IN', 'SP', 'SU');

		$semesters = array();
		$sql = $db->Prepare('SELECT count(r.reservesRecordID) AS reservesTotal FROM reservesRecord r, itemHeading i, section s WHERE
							r.itemHeadingID = i.itemHeadingID AND i.sectionID = s.sectionID AND s.year = ? AND s.term = ?');

		for ($year = $fiveYearsHence ; $year >= $fiveYearsPrior ; $year --) {
			if (!$noFutureYears || $year <= $currentYear) {
				foreach ($semesterTerms as $term) {
					$sqlParams = array($year, $term);
					$returnStatement = $db->execute($sql, $sqlParams);
					$returnObject = $returnStatement->FetchNextObject();
					$semesters[$year . $term] = $returnObject->RESERVESTOTAL;
				}
			}
		}
		return $semesters;
	}

	/**
	 * @brief determines if a given semester is a possible one, within the context of the eReserves system.
	 * @param String $semester the semester to test, like 2010FA
	 * @return boolean is it valid?
	 */
	static function isValidSemester($semester) {
		$plausibleSemesters = self::_getPlausibleSemesters();
		if (in_array($semester, array_keys($plausibleSemesters))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief determines what the current semester is, and returns it.
	 * @return String the current semester code
	 */
	static function getCurrentSemester() {

		$currMonth = date('n');
		$term = '';
		if ($currMonth <= 4) {
			$term = 'WI';
		} else if ($currMonth <= 8) {
			$term = 'SU';
		} else {
			$term = 'FA';
		}
		return intval(date('Y')) . $term;
	}

	/**
	 * @brief a convenience function for providing SELECT values in a section editing form, for assigning a section to a course.
	 * @return Array the possible courses to create sections for.
	 */
	static function _getPlausibleCourses() {

		$db = getDB();
		$sql = "SELECT courseID, prefix, courseNumber FROM course ORDER BY prefix ASC";
		$returnStatement = $db->Execute($sql);
		$courses = array();
		while ($returnObject = $returnStatement->FetchNextObject()) {
			$courses[ $returnObject->COURSEID ] = $returnObject->PREFIX . ' ' . $returnObject->COURSENUMBER;
		}
		return $courses;
	}

	/**
	 * @brief returns a dropdown list for choosing a semester
	 * @param String $semester the semester to preselect (like 2010FA)
	 * @return Form $form the form object
	 */
	static function assembleSemesterChooseForm($semester = '') {
		import('forms.Form');
		$action = 'viewAllReserves';
		$form = new Form(array('id' => 'viewAllReserves', 'method' => 'get', 'action' => '/reserves/index.php/' . $action . '/0'));
		$fieldSet = new FieldSet(array('legend' => 'Choose Semester'));

		$select = self::getSemesterDropdown($semester);

		$fieldSet->addField($select);
		$form->addFieldSet($fieldSet);

		return $form;
	}

	static function getSemesterDropdown($all = FALSE, $semester = '') {
		$semesters = self::_getPlausibleSemesters(TRUE);
		if ($semester == '' && $all) {
			$semester = self::getCurrentSemester();
		}
		$required = $all ? true : false;
		$secondaryLabel = !$required ? 'Leave empty to search all semesters' : 'Those with reserves are marked';
		$select = new Select( array('name' => 'semester', 'primaryLabel' => 'Course Semester', 'secondaryLabel' => $secondaryLabel, 'required' => $required,
				'requiredMsg' => 'Please choose a semester', 'value' => $semester, 'onChange' => 'switchSemesters()') );
		if (!$all) {
			$select->addOption( array('value' => '', 'label' => '------') );
		}
		foreach ($semesters as $semesterName => $semesterReservesCount) {
			$label = $semesterName;
			$label .= $semesterReservesCount > 0 ? " ($semesterReservesCount)" : "";
			$select->addOption( array('value' => $semesterName, 'label' => $label) );
		}

		return $select;
	}

	/**
	 * @brief convenience function for getting the section ID
	 * @return Int the section id
	 */
	function getSectionID() {
		$returner = $this->getAttribute('sectionid');
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
	 * @brief convenience function for getting the semester
	 * @return String the semester
	 */
	function getSemester() {
		$returner = $this->getAttribute('year') . '/' . $this->getAttribute('term');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the course Name
	 * @return String the course name
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
	function getCalendarCourseCode() {

		$returner = $this->getSemester() . ' ' . $this->getAttribute('prefix') . '*' . $this->getAttribute('coursenumber') . '*' . $this->getAttribute('sectionnumber');
		return $returner;
	}

	/**
	 * @brief this concatenates the relevant bits of the course prefix and number to return a shorter version of the course code.
	 * @return String the short code for the course
	 */
	function getShortCourseCode() {

		$returner = $this->getAttribute('prefix') . '*' . $this->getAttribute('coursenumber');
		return $returner;
	}

	/**
	 * @brief returns the role assigned to the given user id, for this course.
	 * @param $reservesUserName the user name.
	 * @return int the role, or 0 if this user has nothing to do with this course.
	 */
	function getSectionRoleForUserID($reservesUserName) {

		$db = getDB();
		$sql = "SELECT r.roleID FROM sectionRole r WHERE r.sectionID = ? AND r.userName = ?";
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), $reservesUserName));
		if ($returnStatement->RecordCount() ==  1) {
			$recordObject = $returnStatement->FetchNextObject();
			$roleID = $recordObject->ROLEID;
			return $roleID;
		} else {
			return false;
		}
	}

	/**
	 * @brief determines if a student is enroled in this section or not.
	 * @param String $reservesUserName the name to check.
	 * @return boolean true or false.
	 */
	function userIsEnrolled($reservesUserName) {
		$db = getDB();
		$sql = "SELECT r.sectionRoleID FROM sectionRole r WHERE r.sectionID = ? AND r.userName = ?";
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), $reservesUserName));
		if ($returnStatement->RecordCount() ==  1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief removes all student associations with this section. Does NOT remove instructor records.
	 * @return boolean true or false, success or failure.
	 */
	function unenrolStudents() {
		import('auth.ReservesUser');

		$db = getDB();
		$sql = "DELETE FROM sectionRole WHERE sectionID = ? AND roleID = ?";
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), ReservesUser::ROLE_SECTION_STUDENT));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @brief returns a list of usernames and the roles that have been assigned to them, for this section.
	 * @return Array the users and roles.
	 */
	function getSectionRoles($filter = 0) {
		import('auth.ReservesUser');

		$db = getDB();
		if ($filter == 0) {
			$sql = "SELECT r.roleID, r.userName FROM sectionRole r WHERE r.sectionID = ? AND r.roleID != '0' ORDER BY r.userName ASC";
		} else {
			$sql = "SELECT r.roleID, r.userName FROM sectionRole r WHERE r.sectionID = ? AND r.roleID = ? ORDER BY r.userName ASC";
		}
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), $filter));
		$sectionUsers = array();
		while ($returnObject = $returnStatement->FetchNextObject()) {
			$sectionUsers[$returnObject->USERNAME] = array('roleID' => $returnObject->ROLEID, 'roleDesc' => ReservesUser::mapRoleIDToCommonName($returnObject->ROLEID));
		}

		return $sectionUsers;
	}

	function getInstructors() {

		import('auth.ReservesUser');
		import('auth.LDAPConnection');
		$sectionUsers = $this->getSectionRoles(ReservesUser::ROLE_SECTION_INSTRUCTOR);
		$instructors = array();
		foreach ($sectionUsers as $username => $role) {
			$userEntry = LDAPConnection::getUserLDAPInfo($username);
			$instructors[] = $userEntry['cn'][0];
		}

		return join(', ', $instructors);
	}
	/**
	 * @brief adds or updates a role for a user, for this section
	 * @param String $reservesUserName
	 * @param int $roleID
	 */
	function assignSectionRoleForUserID($reservesUserName, $roleID) {
		$db = getDB();
		$sql = "SELECT r.roleID FROM sectionRole r WHERE r.sectionID = ? AND r.userName = ?";
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), $reservesUserName));
		if ($returnStatement->RecordCount() == 1) { // there is already a role for this user and this section
			$sql = "UPDATE sectionRole SET roleID = ? WHERE sectionID = ? AND userName = ?";
		} else {
			$sql = "INSERT INTO sectionRole (sectionRoleID, roleID, sectionID, userName) VALUES (0, ?, ?, ?)";
		}

		$sqlParams = array($roleID, $this->getSectionID(), $reservesUserName);
		$returnStatement = $db->Execute($sql, $sqlParams);
		return $returnStatement;
	}

	/**
	 * @removes a user from this section
	 * @param String $reservesUserName
	 */
	function removeSectionRoleForUserID($reservesUserName) {
		$db = getDB();
		$sql = "SELECT r.roleID FROM sectionRole r WHERE r.sectionID = ? AND r.userName = ?";
		$returnStatement = $db->Execute($sql, array($this->getSectionID(), $reservesUserName));
		if ($returnStatement->RecordCount() == 1) { // there is already a role for this user and this section
			$sql = "DELETE FROM sectionRole WHERE sectionID = ? AND userName = ?";
		}

		$sqlParams = array($this->getSectionID(), $reservesUserName);
		$returnStatement = $db->Execute($sql, $sqlParams);
		return $returnStatement;
	}

	/**
	 * @brief returns a list of ItemHeading objects that have been created for this course.
	 * @return array $itemHeadings the headings for this course
	 */
	function getHeadings() {

		$db = getDB();
		$sql = "SELECT i.itemHeadingID FROM itemHeading i WHERE i.sectionID = ? ORDER BY sequence ASC";
		$returnStatement = $db->Execute($sql, array($this->getSectionID()));

		import('items.ItemHeading');
		$itemHeadings = array();

		while ($recordObject = $returnStatement->FetchNextObject()) {
			$itemHeadings[] = new ItemHeading($recordObject->ITEMHEADINGID);
		}

		return $itemHeadings;
	}

	/**
	 * brief determines if a heading has been assigned to a section or not.
	 * @param String $headingName the heading to test
	 * @return ItemHeading or false, if it has it or not
	 */
	function hasHeading($headingName) {
		foreach ($this->getHeadings() as $heading) {
			if ($heading->getHeadingName() == $headingName) {
				return $heading;
			}
		}
		return false;
	}

	/**
	 * @brief adds a new ItemHeading to this section, with the assigned heading name.
	 * @param String $headingName the name to use
	 * @return int the itemHeadingID of the new Heading
	 */
	function addHeading($headingName) {
		import ('items.ItemHeading');
		$itemHeadingID = ItemHeading::create($this->getSectionID(), $headingName);
		return new ItemHeading($itemHeadingID);
	}

	/**
	 * @brief deletes an ItemHeading object assigned to this Section.
	 * @param int $itemHeadingID the ID of the heading to remove.
	 * @return boolean true or false on success or failure.
	 */
	function deleteHeading($itemHeadingID) {
		$db = getDB();
		/* it is important to constrain this to the sectionID.  Otherwise Sections could delete Headings that are not their own */
		$sql = "DELETE FROM itemHeading WHERE itemHeadingID = ? AND sectionID = ?";
		$returnStatement = $db->Execute($sql, array($itemHeadingID, $this->getSectionID()));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @brief returns a total number of reserves assigned to this section.  Used to generate a list on the viewSections template.
	 * @return int the number of reserves in the section
	 */
	function getTotalNumberOfReserves() {

		$db = getDB();
		$sql = "select count(*) as total from reservesRecord r, section s, itemHeading i WHERE s.sectionID = ?
			AND s.sectionID = i.sectionID AND i.itemHeadingID =  r.itemHeadingID";

		$returnStatement = $db->Execute($sql, array($this->getSectionID()));
		$recordObject = $returnStatement->FetchNextObject();
		return $recordObject->TOTAL;
	}

	/**
	 * @brief returns an array of ReservesRecord objects for this Section
	 * @return array
	 */
	function getReserves() {

		$db = getDB();
		$sql = "select r.reservesRecordID FROM reservesRecord r, section s, itemHeading i WHERE s.sectionID = ?
			AND s.sectionID = i.sectionID AND i.itemHeadingID =  r.itemHeadingID";

		$returnStatement = $db->Execute($sql, array($this->getSectionID()));
		$reserves = array();
		import('items.ReservesRecord');
		while ($recordObject = $returnStatement->FetchNextObject()) {
			$reserves[] = new ReservesRecord($recordObject->RESERVESRECORDID);
		}
		return $reserves;
	}

	/**
	 * @brief a static function that returns an array of the MySQL fields in the course table
	 * to search with the regular MySQL keyword search
	 * @return array the field names
	 */
	public static function getSearchFields() {
		$returner = array('courseName', 'prefix', 'courseNumber');
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

		$semester = ReservesRequest::getRequestValue('semester');

		$year = '';
		$term = '';

		if (preg_match('/^(\d{4})(\w{2})$/', $semester, $matches)) {
			$year = $matches[1];
			$term = $matches[2];
		}

		$sectionnumber = ReservesRequest::getRequestValue('sectionnumber');
		$courseid = ReservesRequest::getRequestValue('courseid');
		$sectionid = ReservesRequest::getRequestValue('sectionid');

		if ($courseid > 0) {
			$sql = "UPDATE section SET sectionNumber = ?, year = ?, term = ?, courseID = ? WHERE sectionID = ?";
		} else {
			$sql = "INSERT INTO section (sectionNumber, year, term, courseID, sectionID) VALUES (?, ?, ?, ?, ?)";
		}
		$returnStatement = $db->Execute($sql, array($sectionnumber, $year, $term, $courseid, $sectionid));
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
		$action = $this->getSectionID() > 0 ? 'editSection' : 'createNewSection';

		$form = new Form(array('id' => 'editSection', 'method' => 'post', 'action' => $basePath . '/index.php/' . $action . '/' . $this->getSectionID()));
		$label = $this->getSectionID() > 0 ? 'Edit ' . $this->getCalendarCourseCode() : 'Create New Section';
		$fieldSet = new FieldSet(array('legend' => $label));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'sectionid', 'value' => $this->getSectionID()) ));

		$select = self::getSemesterDropdown(TRUE, $this->getAttribute('year') . $this->getAttribute('term'));
		$fieldSet->addField($select);

		unset ($select);

		$courses = $this->_getPlausibleCourses();
		$select = new Select( array('name' => 'courseid', 'primaryLabel' => 'Course', 'secondaryLabel' => 'Choose one', 'required' => true,
				'requiredMsg' => 'Please choose a course', 'value' => $this->getAttribute('courseid')) );
		foreach ($courses as $courseid => $coursename) {
			$select->addOption( array('value' => $courseid, 'label' => $coursename) );
		}
		$fieldSet->addField($select);

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Section Number', 'secondaryLabel' => 'ie, FRE01', 'name' => 'sectionnumber',
							'value' => $this->getAttribute('sectionnumber'), 'requiredMsg' => 'Please enter a section number') ));

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
