<?php

class ReservesUser {

	var $_userAttributes = array('reservesUserID' => 0, 'commonName' => 'Guest');

	const ROLE_SECTION_STUDENT = 0;
	const ROLE_SECTION_SPECIAL_ENROL = 1;
	const ROLE_SECTION_TA = 2;
	const ROLE_SECTION_INSTRUCTOR = 3;
	const ROLE_SECTION_ADMINISTRATOR = 4;

	function __construct() {

		import('general.Config');
		$config = new Config();
		$sessionLifetime = intval($config->getSetting('session', 'session_cookie_timeout'));
		if ($sessionLifetime > 0) {
			session_set_cookie_params($sessionLifetime);
		}

		session_start();
		if (sizeof($_SESSION) > 0) {
			$this->_assignUserAttributes($_SESSION);
		}
	}

	/**
	 * @brief called when a user logs out -- it destroys the current session and unsets the Session cookie.
	 */
	private function _destroySession($entry = NULL) {
		session_start();
		$streamURL = '';
		if (isset($_SESSION['streamURL'])) {
			$streamURL = $_SESSION['streamURL'];
		}
		if ($this->isMobile()) {
			$entry['isMobile'] = TRUE;
		}
		setcookie(session_name(), '');
		session_destroy();
		$_SESSION = array();
		if ($streamURL != '') {
			$entry['streamURL'] = $streamURL;
		}
	}

	/**
	 * @brief starts a session and persists the LDAP field items in the SESSION array.
	 * @param Array $ldapEntry
	 * @param String $username the user's username entered on the login form.  we use this to compare if the uid LDAP field has both student
	 * and staff IDs.
	 */
	private function _createNewSession($ldapEntry, $username) {
		import('general.Config');
		import('general.ReservesRequest');
		$config = new Config();
		$sessionLifetime = intval($config->getSetting('session', 'session_cookie_timeout'));
		if ($sessionLifetime > 0) {
			session_set_cookie_params($sessionLifetime);
		}
		session_start();
		$localUserSettings = $this->_verifyLocalDatabaseRecord($username, $ldapEntry['mail'][0]);

		$accountTypeField = $config->getSetting('ldap', 'account_type_field');


		$_SESSION['commonName'] = $ldapEntry['cn'][0];
		$_SESSION['email'] = $ldapEntry['mail'][0];

		/* PHP lower cases the indexes in the returned $ldapEntry object -- they may be camel-caps in the LDAP tree
		 * but we need to use lower case strings to access them here.
		 */

		$_SESSION['givenName'] = $ldapEntry['givenname'][0];
		$_SESSION['ldapAccountType'] = $ldapEntry[$accountTypeField][0];
		$_SESSION['reservesUserID'] = $localUserSettings['reservesUserID'];
		$_SESSION['isAdmin'] = $localUserSettings['isAdmin'];
		$_SESSION['lastLogin'] = $localUserSettings['lastLogin'];
		$_SESSION['userName'] = $localUserSettings['userName'];
		$_SESSION['isMobile'] = ReservesRequest::isMobile();
		if (isset($ldapEntry['streamURL'])) {
			$_SESSION['streamURL'] = $ldapEntry['streamURL'];
		}

		if (isset($ldapEntry['isMobile'])) {
			$this->setMobile();
		}

		$this->_assignUserAttributes($_SESSION);
	}

	/**
	 * @brief copies the session attributes into the user attribute array so they can be easily accessed in a User context
	 * @param unknown_type $attributes
	 */
	private function _assignUserAttributes($attributes) {
		$this->_userAttributes = $attributes;
	}

	/**
	 * @brief fetches an attribute from the attributes array created when a user logs in.
	 * @param String $attribute the user attribute
	 * @return String the attribute value
	 */
	private function _getAttribute($attribute) {
		if (array_key_exists($attribute, $this->_userAttributes)) {
			return $this->_userAttributes[$attribute];
		} else {
			return '';
		}
	}

	/**
	 * @brief checks to see if there is a record in the reservesUser database table
	 * for this user.  If there is, it returns the local user id and whether or not the user
	 * is a Reserves administrator. If there is not it creates a new entry, and returns the
	 * user id of the new record.
	 *
	 * @return Array the user id and a boolean for administration purposes
	 */
	private function _verifyLocalDatabaseRecord($userName, $email) {

		$db = getDB();
		$userFields = array();
		$returnStatement = $db->Execute("SELECT reservesUserID, userName, email, isAdmin, lastLogin FROM reservesUser WHERE userName = ?", array($userName));
		if ($returnStatement->RecordCount() == 1) {
			$recordObject = $returnStatement->FetchNextObject();
			$userFields = array('reservesUserID' => $recordObject->RESERVESUSERID, 'isAdmin' => $recordObject->ISADMIN, 'lastLogin' => $recordObject->LASTLOGIN,
								'userName' => $recordObject->USERNAME);

			/* we do the next query AFTER we retrieve the record because we want the previous lastLogin date, not this one */
			$db->Execute("UPDATE reservesUser SET lastLogin = now() WHERE reservesUserID = " . $recordObject->RESERVESUSERID);
		} else {
			$userName = $db->qstr($userName);
			$email = $db->qstr($email);
			if ($db->Execute("INSERT INTO reservesUser (reservesUserID, userName, email, isAdmin, lastLogin) VALUES (0, $userName, $email, '0', now())")) {
				$returnStatement = $db->Execute("SELECT reservesUserID, userName, email, isAdmin, lastLogin FROM reservesUser WHERE userName = ?", array($userName));
				if ($returnStatement->RecordCount() == 1) {
					$recordObject = $returnStatement->FetchNextObject();
					$userFields = array('reservesUserID' => $recordObject->RESERVESUSERID, 'isAdmin' => $recordObject->ISADMIN, 'lastLogin' => $recordObject->LASTLOGIN,
										'userName' => $recordObject->USERNAME);
				}
			} else {
				error_log('Error creating new user: ' . $db->ErrorMsg());
			}
		}

		return $userFields;
	}

	/**
	 * @brief determines if a user can edit a course by examining the role assigned to the course, for  the particular user.
	 * @param int $roleID
	 * @return boolean
	 */
	private function _canEditThisSectionByRoleID($roleID) {
		if ($roleID == self::ROLE_SECTION_INSTRUCTOR || $roleID == self::ROLE_SECTION_ADMINISTRATOR) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief determines if a user can edit a course by instantiating the course, getting the roles, and then
	 * passing this value to _canEditThisCourseByRoleID().
	 * @param int $courseID
	 * @return boolean
	 */
	private function _canEditThisSectionBySectionID($sectionID) {

		if ($this->isAdmin()) {
			return true;
		}
		$sectionID = intval($sectionID);
		import('items.Section');
		$section = new Section($sectionID);

		$username =  !$this->isActing() ? $this->getUserName() : $username = $this->getAssumedUserName();

		$roleID = $section->getSectionRoleForUserID($username);
		return $this->_canEditThisSectionByRoleID($roleID);
	}

	/**
	 * @brief convenience function for determining if a user can actually edit either an Electronic or Physical reserve item.
	 * @param int $itemID the _id (primary key) of the item in question
	 * @param String $type the type (one of two, Case statement) of item
	 * @return boolean true or false
	 */
	private function _canEditItem($itemID, $type) {

		$item = null;

		switch ($type) {

			case 'ElectronicReserveItem':
				import ('items.' . $type);
				$item = new $type ($itemID);
				break;
			case 'PhysicalReserveItem':
				import ('items.' . $type);
				$item = new $type ($itemID);
				break;
		}

		if ($this->_canEditThisSectionBySectionID($item->getSectionID())) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief provides labels for role IDs for display purposes.  This does NOT include ROLE_SECTION_STUDENT.
	 * @param int $roleID the role ID to display.
	 * @return String the label.
	 */
	public static function mapRoleIDToCommonName($roleID = 0) {
		$roles = array(
						self::ROLE_SECTION_SPECIAL_ENROL => 'Enroled By Instructor',
						self::ROLE_SECTION_TA => 'Section Teaching Assistant',
						self::ROLE_SECTION_INSTRUCTOR => 'Section Instructor',
						self::ROLE_SECTION_ADMINISTRATOR => 'Reserves Administrator'
					);

		if ($roleID > 0) {
			return $roles[$roleID];
		} else {
			return $roles;
		}
	}

	public static function getAssignedCourseSectionsByUser($userName) {

		$db = getDB();
		$sql = 'SELECT sr.sectionID from sectionRole as sr WHERE sr.userName = ? AND sr.roleID = ?';
		$returnStatement = $db->Execute($sql, array($userName, self::ROLE_SECTION_SPECIAL_ENROL));
		$sectionCodes = array();
		import('items.Section');
		while ($returnObject = $returnStatement->FetchNextObject()) {
			$section = new Section($returnObject->SECTIONID);
			$sectionCodes[] = $section->getCalendarCourseCode();
		}

		return $sectionCodes;
	}
	/**
	 * @brief simple way to get all of the roles possible for a section user
	 */
	public function getAllUserRoles() {
		return self::mapRoleIDToCommonName();
	}

	/**
	 * @brief returns the user's local database ID, from the user attributes
	 * @return int the user id
	 */
	public function getReservesUserID() {
		$returner = intval($this->_getAttribute('reservesUserID'));
		return $returner;
	}

	/**
	 * @brief returns the user's username (originally from LDAP), from the user attributes
	 * @return String the user's user name
	 */
	public function getUserName() {
		$returner = $this->_getAttribute('userName');
		return $returner;
	}

	/**
	 * @brief returns the user's real full name, from the user attributes
	 * @return String the user's real name
	 */
	public function getFullName() {
		$returner = $this->_getAttribute('commonName');
		return $returner;
	}

	/**
	 * @brief returns the LDAP field associated with our record type.
	 */
	public function getAccountType() {
		$returner = $this->_getAttribute('ldapAccountType');
		return $returner;
	}

	/**
	 * @brief convenience method for retrieving a user's first name, from the full commonName.
	 * @return String the firstname
	 */
	public function getFirstName() {
		$givenName = preg_replace('/^(\S+).*$/', "$1", $this->_getAttribute('commonName'));
		return $givenName;
	}

	/**
	 * @brief returns a boolean about the user's logged in status
	 * @return boolean
	 */
	public function isAnonymous() {
		$returner = $this->getReservesUserID() == 0 ? true : false;
		return $returner;
	}

	/**
	 * @brief convenience method for retrieving a user's last login date.
	 * @return String the date.
	 */
	public function getLastLogin() {
		if ($this->_getAttribute('lastLogin') != '0000-00-00 00:00:00') {
			$timestamp = strtotime($this->_getAttribute('lastLogin'));
		} else {
			$timestamp = time();
		}
		$returner = date('M jS, Y', $timestamp);
		return $returner;
	}

	/**
	 * @brief returns a boolean about the user's administrator status
	 * @return boolean
	 */
	public function isAdmin() {
		$returner = $this->_getAttribute('isAdmin') ? true : false;
		return $returner;
	}

	/**
	 * @brief returns a boolean about the user's logged in status
	 * @return boolean
	 */
	public function isLoggedIn() {
		$returner = $this->getReservesUserID() > 0 ? true : false;
		return $returner;
	}

	/**
	 * @brief determines if an admin is currently masquerading as another account
	 * @return boolean true or false
	 */
	public function isActing() {
		$credentials = $_SESSION['assumedUserInfo'];
		if (!empty($credentials) && $this->isAdmin()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief returns a masq'ed username
	 * @return String
	 */
	public function getAssumedUserName() {
		return $_SESSION['assumedUserInfo']['uid'];
	}

	/**
	 * @brief returns a masq'ed full name (typically the CN field in an LDAP record)
	 * @return String
	 */
	public function getAssumedFullName() {
		return $_SESSION['assumedUserInfo']['cn'];
	}

	/**
	 * @brief returns a masq'ed account type (often student or staff)
	 * @return String
	 */
	public function getAssumedAccountType() {
		return $_SESSION['assumedUserInfo']['accountType'];
	}

	/**
	 * @brief returns a session setting depending on whether or not they originally came from a mobile website.
	 * @return boolean mobile or not.
	 */
	public function isMobile() {
		return $_SESSION['isMobile'];
	}

	public function setMobile() {
		$_SESSION['isMobile'] = TRUE;
	}
	/**
	 * @brief logs a user into Reserves.  Extracts a user id from an email address and
	 * authenticates against LDAP only (currently).  If valid, creates a new session.
	 */
	public function logIn() {
		import('general.ReservesRequest');
		$username = strtolower(ReservesRequest::getRequestValue('username'));
		$password = ReservesRequest::getRequestValue('password');

		if (preg_match("/^(\w+)$/", $username, $matches)) {
			$username = $matches[1];

			import('auth.LDAPConnection');

			// returns an array representing the Ldap entry for the user, false otherwise
			$entry = LDAPConnection::ldapAuthenticate($username, $password, LDAPConnection::LDAP_RETURN_ENTRY);

			if ($entry) {
				$this->_destroySession($entry);
				$this->_createNewSession($entry, $username);
				return true;
			}
		}
		// FIXME: this method should test other auth sources
		return false;
	}

	/**
	 * @brief public function for destroying a session
	 */
	public function logOut() {
		$this->_destroySession();
	}

	/**
	 * @brief determines whether or not the current user can perform the operation in question, given
	 * the context.
	 * @param $op the operation (url fragment, usually)
	 * @return boolean
	 */
	public function canPerformOp($op, $objectID) {

		$anonymousOps	 = array('blackboardJSON', 'browseCourses', 'downloadLogin', 'feed', 'login', 'logout', 'loginError', 'opacProxy', 'semester', 'quickSearch', 'quickSearchJSON', 'searchByUserJSON', 'stream', 'viewReserves', 'viewReservesJSON', 'viewReserve', 'viewAllReserves');
		$authenticatedOps = array('listCourseNumbers', 'listCoursePrefixes', 'viewCourses', 'viewSections');

		if ($op == '' || in_array($op, $anonymousOps) ) {
			return true;
		} elseif (in_array($op, $authenticatedOps)) {
			return true;
		} else { // this is an op that requires special privileges or depends on a object ID or an object's status
			switch ($op) {

				case 'assignPeople':
				case 'createNewReserve':
				case 'createNewSection':
				case 'deleteItemHeading':
				case 'deleteReservesRecord':
				case 'editItemHeading':
				case 'editSection':
				case 'findInstructorAssign':
				case 'itemHeadings':
				case 'reorderItemHeadings':
				case 'unenrolStudents':
					if ($this->_canEditThisSectionBySectionID($objectID)) {
						return true;
					} else {
						return false;
					}
					break;

				case 'adminCourseReserves':
					import('items.ItemHeading');
					$itemHeading = new ItemHeading($objectID);
					if ($this->_canEditThisSectionBySectionID($itemHeading->getSectionID())) {
						return true;
					} else {
						return false;
					}
					break;

				case 'createReservesItem':
					import ('items.ReservesRecord');
					$reservesRecord = new ReservesRecord($objectID);
					if ($this->_canEditThisSectionBySectionID($reservesRecord->getSectionID())) {
						return true;
					} else {
						return false;
					}
					break;

				case 'deleteElectronicItem':
				case 'editElectronicItem';

					if ($this->_canEditItem($objectID, 'ElectronicReserveItem')) {
						return true;
					} else {
						return false;
					}
					break;


				case 'deletePhysicalItem':
				case 'editPhysicalItem':

					if ($this->_canEditItem($objectID, 'PhysicalReserveItem')) {
						return true;
					} else {
						return false;
					}
					break;

				case 'assumeUserRole':
				case 'switchToStudent':
					if ($this->isActing() || $this->isAdmin()) {
						return true;
					} else {
						return false;
					}
					break;

				case 'adminSemesters':
				case 'downloadReports':
				case 'reorderSemesters':
				case 'editSemester':
				case 'createNewCourse':
				case 'editCourse':
				case 'searchByUser':
				case 'searchWC':

					if ($this->isAdmin()) {
						return true;
					} else {
						return false;
					}
					break;
			}
		}
		return false;
	}

	/**
	 * @brief returns an array of Course objects that the user currently has available.  Filters on the basis of administration privileges,
	 * whether the user is an instructor, or an adminstrator of the course.   This administrator privilege is NOT the same as being a global super admin,
	 * as tested by the isAdmin() function.
	 * This function is dual purpose.  For admins, it returns a list of Courses.  For everyone else, it returns Sections since that is what students and
	 * instructors are usually interested in.
	 * @return Array an array of Course or Section objects.
	 */
	public function getCourseSections($offset = 0, $filters = array()) {

		$db = getDB();
		$courses = array();
		import('items.Course');

		$sql = '';
		$sqlParams = array();
		if ($this->isAdmin() && !$this->isActing()) {

			$sql = "SELECT count(DISTINCT s.courseID) AS total FROM section s, course c WHERE c.courseID = s.courseID"; // get the total, before limiting by page.

			$filterSql = '';
			if ($filters['courseNameFilter'] != '' || $filters['courseCodeFilter'] != '') {
				$filters['courseCodeFilter'] = preg_replace("/[\s\*]/", "", $filters['courseCodeFilter']);
				$filterSql = ' AND ';
				if ($filters['courseNameFilter'] != '' && $filters['courseCodeFilter'] != '') {
					$filterSql .= ' c.courseName LIKE ' . $db->quote('%' . $filters['courseNameFilter'] . '%') . ' AND CONCAT(c.prefix, c.courseNumber) LIKE ' . $db->quote('%' . $filters['courseCodeFilter'] . '%') . ' ';
				} else if ($filters['courseNameFilter'] != '') {
					$filterSql .= ' c.courseName LIKE ' . $db->quote('%' . $filters['courseNameFilter'] . '%');
				} else {
					$filterSql .= ' CONCAT(c.prefix, c.courseNumber) LIKE ' . $db->quote('%' . $filters['courseCodeFilter'] . '%');
				}
			}

			$sql .= $filterSql;

			$returnStatement = $db->Execute($sql);
			$recordObject = $returnStatement->FetchNextObject();
			$totalRecords = $recordObject->TOTAL;

			$sql = "SELECT DISTINCT s.courseID, c.prefix FROM section s, course c WHERE c.courseID = s.courseID "  . $filterSql . " ORDER BY c.prefix ASC LIMIT $offset, 25";
		} else {

			$username = $this->isActing() ? $this->getAssumedUserName() : $this->getUserName();
			import('auth.LDAPConnection');
			import('items.Section');
			import('general.Config');
			$config = new Config();

			$sectionCodesInLDAP = LDAPConnection::getSectionsFromLDAPRecord($username);
			$sectionCodesInRoles = $this->getAssignedCourseSections();

			$sectionCodes = array();

			if (is_array($sectionCodesInLDAP[0])) {
				$sectionCodes = array_merge($sectionCodesInLDAP[0], $sectionCodesInRoles);
			} else {
				$sectionCodes = $sectionCodesInRoles;
			}
			if (is_array($sectionCodes)) {
				$sections = array();
				foreach ($sectionCodes as $sectionCode) {

					//$sections[] = Section::getSectionFromCalendarCode($sectionCode);
					$section = Section::getSectionFromCalendarCode($sectionCode) ; 
					if ($section !== false) {
						$sections[] = $section ;
					}
					
					$accountType = $this->isActing() ? $this->getAssumedAccountType() : $this->getAccountType();
	 				if ($section) {
						$section->assignSectionRoleForUserID($username, $accountType == $config->getSetting('ldap', 'staff_field_string') ? self::ROLE_SECTION_INSTRUCTOR : self::ROLE_SECTION_STUDENT);
	 				}
	 			}

	 			return array('0' => $sections, '1' => sizeof($sections));
			}
			/*
			$sqlParams = array($username);
			$sql = "SELECT count(c.courseID) AS total FROM sectionRole r, section s, course c WHERE r.userName = ? AND s.sectionID = r.sectionID AND s.courseID = c.courseID";
			$returnStatement = $db->Execute($sql, $sqlParams);
			$recordObject = $returnStatement->FetchNextObject();
			$totalRecords = $recordObject->TOTAL;

			$sql = "SELECT c.courseID FROM sectionRole r, section s, course c WHERE r.userName = ? AND s.sectionID = r.sectionID AND s.courseID = c.courseID
				ORDER BY c.prefix ASC";
			*/
		}

		$returnStatement = $db->Execute($sql, $sqlParams);

		while ($recordObject = $returnStatement->FetchNextObject()) {
			$courses[] = new Course($recordObject->COURSEID);
		}

		return array ('0' => $courses, '1' => $totalRecords);
	}

	function getAssignedCourseSections() {

		$db = getDB();
		$sql = 'SELECT sr.sectionID from sectionRole sr WHERE sr.userName = ? AND sr.roleID = ?';

		$userName = $this->isActing() ? $this->getAssumedUserName() : $this->getUserName();

		$returnStatement = $db->Execute($sql, array($userName, self::ROLE_SECTION_SPECIAL_ENROL));
		$sectionCodes = array();
		import('items.Section');
		while ($returnObject = $returnStatement->FetchNextObject()) {
			$section = new Section($returnObject->SECTIONID);
			$sectionCodes[] = $section->getCalendarCourseCode();
		}

		return $sectionCodes;
	}
	/**
	 * @brief verifies that a given user has privileges to administer this paricular section.
	 * @param int $sectionID
	 *
	 * @return boolean true or false
	 */
	public function canAdministerSection($sectionID){

		if ($this->isAdmin() && !$this->isActing()) {
			return true;
		}

		return false; // FIXME: for the time being, only admins can edit or create reserves.  This is what the Library staff would like for now.

		$db = getDB();
		$sql = "SELECT roleID from sectionRole WHERE sectionID = ? AND userName = ?";
		$userName = $this->isActing() ? $this->getAssumedUserName() : $this->getUserName();

		$sqlParams = array($sectionID, $userName);
		$returnStatement = $db->Execute($sql, $sqlParams);
		if ($returnStatement->RecordCount() == 1) {

			$recordObject = $returnStatement->FetchNextObject();
			if ($recordObject->ROLEID == self::ROLE_SECTION_ADMINISTRATOR || $recordObject->ROLEID == self::ROLE_SECTION_INSTRUCTOR || $recordObject->ROLEID == self::ROLE_SECTION_TA) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
?>
