<?php

class LDAPConnection {

	/* LDAP constants for return values */
	const LDAP_AUTH_SUCCESS = '11';
	const LDAP_AUTH_CONN_FAIL = '14';
	const LDAP_RETURN_AUTH_ONLY = '15';
	const LDAP_RETURN_ENTRY = '16';
	const LDAP_RETURN_CN = '17';

	/** returns an LDAP connection object, suitable for other connection commands.
	 *  @called by ldap_authenticate
	 */
	private function _getLdapConnection($host){

		$ldapConnection = ldap_connect($host);
		return $ldapConnection;
	}

	/**
	 * @brief authenticates against an LDAP server, with the provided username and password
	 * @param $uid
	 * @param $password
	 * @param $return_type various status codes, or some info about the LDAP user
	 */
	static function ldapAuthenticate($uid, $password, $return_type = self::LDAP_RETURN_ENTRY) {
		import('general.Config');
		$config = new Config();

		$ldapConnection = self::_getLdapConnection($config->getSetting('ldap', 'ldap_host'));

		if ($ldapConnection) {

			/* bind as the authentication user, with the ITS-supplied auth password */

			$ldapBind = ldap_bind($ldapConnection, $config->getSetting('ldap', 'administration_dn'), $config->getSetting('ldap', 'administration_pass'));

			if ($ldapBind) {

				/* search the LDAP tree for the given user UID */
				$accountTypeField = $config->getSetting('ldap', 'account_type_field');
				$instructorCourseField = $config->getSetting('ldap', 'instructor_type_field');
				$studentCourseField = $config->getSetting('ldap', 'student_type_field');

				$ldapFields = array('dn', 'cn', 'mail', 'givenName', 'uid', $accountTypeField, $instructorCourseField, $studentCourseField);
				$result = ldap_search($ldapConnection, "dc=unb,dc=ca", "(uid=$uid)", $ldapFields);
				if ($result) {

					/* obtain the list of matching LDAP entries, of which there should only be one, since the UID is unique */
					$entry = ldap_get_entries($ldapConnection, $result);
					if ($entry['count'] == 1) {

						$dn =  $entry[0]['dn'];

						/* attempt to bind to the tree with the user's UID and supplied password.  This is how password verfication occurs in LDAP */
						$userBind = @ldap_bind($ldapConnection, $dn, "$password");

						if ($userBind) {

							switch ($return_type) {

								case self::LDAP_RETURN_ENTRY:
									return $entry[0];
									break;

								case self::LDAP_RETURN_AUTH_ONLY:
									return true;
									break;

								case self::LDAP_RETURN_CN:
									return $entry[0]['cn'][0];
									break;
							}
						}
						else
						return false; // password failed
					} else
					return false; // user does not exist
				} else
				print ldap_error($ldapConnection);

				@ldap_unbind($ldapConnection);
			} else
			print ldap_error($ldapConnection);
		}

		/* function returns a connection failure by default, to prevent accidential authentication */
		return self::LDAP_AUTH_CONN_FAIL;
	}

	/**
	 * @brief binds to the LDAP tree and looks for a uid, and returns an array of the sections that student or instructor is currently enrolled in.
	 * @param String $username the user's UNB email ID (not for STU yet!)
	 * @return Array the courses they re enrolled in
	 */
	static function getSectionsFromLDAPRecord($username) {
		import('general.Config');
		$config = new Config();
		$uid = '';
		if (preg_match('{^(\w+)$}', $username, $matches)) {
			$uid = $matches[1];
		}

		$ldapConnection = self::_getLdapConnection($config->getSetting('ldap', 'ldap_host'));
		$studentField = $config->getSetting('ldap', 'student_course_field');
		$instructorField = $config->getSetting('ldap', 'instructor_course_field');
		$accountTypeField = $config->getSetting('ldap', 'account_type_field');

		if ($ldapConnection && $uid != '') {
			$ldapBind = ldap_bind($ldapConnection, $config->getSetting('ldap', 'administration_dn'), $config->getSetting('ldap', 'administration_pass'));
			if ($ldapBind) {
				$result = ldap_search($ldapConnection, "dc=unb,dc=ca", "(uid=$uid)", array('dn', 'cn', 'mail', 'givenName', 'sn', 'uid', $studentField, $instructorField, $accountTypeField));
				if ($result) {
					$entry = ldap_get_entries($ldapConnection, $result);
					if ($entry['count'] == 1) {
						// next line is not obvious.  LDAP includes the size of the array as a 'count' first element.  array_slice removes
						// the first element since we are not interested in this.
						// first see if they are instructors
						if(is_array($entry[0][ $instructorField ])) {
							$courses = array_slice($entry[0][ $instructorField ], 1, sizeof($entry[0][$instructorField]), TRUE);
						}
						if (!is_array($entry[0][ $instructorField ]) || sizeof($entry[0][$instructorField]) == 0) {
							if (is_array($entry[0][ $studentField ])) {
								$courses = array_slice($entry[0][ $studentField ], 1, sizeof($entry[0][$studentField]), TRUE);
							}
						}
						return array($courses, $entry[0]['cn'][0], $entry[0][$accountTypeField][0]);  // include the User's name, as well the account type
					}
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * @brief abbreviated method for retrieving some LDAP fields about a user
	 * @param String $username
	 * @return Array the LDAP information
	 */
	static function getUserLDAPInfo($username) {
		import('general.Config');
		$config = new Config();
		$uid = '';
		if (preg_match('/^(\w+)$/', $username, $matches)) {
			$uid = $matches[1];
		}

		$ldapConnection = self::_getLdapConnection($config->getSetting('ldap', 'ldap_host'));
		$accountTypeField = $config->getSetting('ldap', 'account_type_field');

		if ($ldapConnection && $uid != '') {
			$ldapBind = ldap_bind($ldapConnection, $config->getSetting('ldap', 'administration_dn'), $config->getSetting('ldap', 'administration_pass'));
			if ($ldapBind && $uid != '') {
				$result = ldap_search($ldapConnection, "dc=unb,dc=ca", "(uid=$uid)", array('dn', 'cn', 'mail', 'sn', 'givenName', 'uid', $accountTypeField));
				if ($result) {
					$entry = ldap_get_entries($ldapConnection, $result);
					if ($entry['count'] == 1) {
						return $entry[0];
					}
				}
			}
		}
	}

	/**
	 * @brief performs an LDAP search to retrieve UID/email pairs based on a search string on the comon name (cn) field
	 * @param unknown_type $searchString the phrase to search for
	 * @return Array the matched data sets
	 */
	static function getUserIDsFromCNSearch($searchString) {

		import('general.Config');
		$config = new Config();
		$ldapConnection = self::_getLdapConnection($config->getSetting('ldap', 'ldap_host'));

		if ($ldapConnection) {
			$ldapBind = ldap_bind($ldapConnection, $config->getSetting('ldap', 'administration_dn'), $config->getSetting('ldap', 'administration_pass'));
			if ($ldapBind && $searchString != '') { // search string should never be empty anyway
				$result = ldap_search($ldapConnection, "dc=unb,dc=ca", "(cn=*" . $searchString . "*)", array('dn', 'cn', 'mail', 'givenName', 'uid'));
			}
			if ($result) {
				$entries = ldap_get_entries($ldapConnection, $result);
			if ($entries['count'] == 0) { // no hits
					return false;
				} else { // more than one match
					$validEntries = array();
					foreach ($entries as $entry) {
							if (is_array($entry)) {
							if (array_key_exists('uid', $entry)) {
								$validEntries[ $entry['uid'][0] ] = array($entry['mail'][0], $entry['cn'][0]);
							}
						}
					}
					return $validEntries;
				}
			} else {
				error_log('LDAP Search Error.');
			}
		}

	}

	/**
	 * @brief performs an LDAP search to retrieve UID/email pairs based on a search string on the UID field
	 * @param unknown_type $searchString the phrase to search for.  NOTE: the search is anchored to the front of the pattern. it is term* not *term*
	 * @return Array the matched data sets
	 */
	static function getUserIDsFromUIDSearch($searchString) {

		import('general.Config');
		$config = new Config();
		$ldapConnection = self::_getLdapConnection($config->getSetting('ldap', 'ldap_host'));

		if ($ldapConnection) {
			$ldapBind = ldap_bind($ldapConnection, $config->getSetting('ldap', 'administration_dn'), $config->getSetting('ldap', 'administration_pass'));
			if ($ldapBind && $searchString != '') { // search string should never be empty anyway
				$result = ldap_search($ldapConnection, "dc=unb,dc=ca", "(uid=*" . $searchString . "*)", array('dn', 'cn', 'mail', 'givenName', 'uid'));
			}
			if ($result) {
				$entries = ldap_get_entries($ldapConnection, $result);
			if ($entries['count'] == 0) { // no hits
					return false;
				} else { // more than one match
					$validEntries = array();
					foreach ($entries as $entry) {
							if (is_array($entry)) {
							if (array_key_exists('uid', $entry)) {
								$uids = $entry['uid'];  // FIXME WAAAACKED
								foreach ($uids as $uid) {
									if (preg_match("{" . $searchString . "}", $uid)) {
										$validEntries[ $uid ] = $entry['cn'][0];
									}
								}
							}
						}
					}

					return $validEntries;
				}
			} else {
				error_log('LDAP Search Error.');
			}
		}

	}
}

?>
