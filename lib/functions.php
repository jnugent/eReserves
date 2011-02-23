<?php

require_once('/www/reserves/lib/adodb5/adodb.inc.php');

define("CRYPT_SECRET_KEY", "E12diq57q90bVceq");
define("CRYPT_IV", "f64a3248");

/**
 * @brief mimics a java-like import function which automatically pulls in files ending in .class.php, and converts . to / to imitate packages
 * @param String $class like general.Something -> classes/general/Something.class.php
 */
function import($class) {
	require_once('classes/' . str_replace('.', '/', $class) .  '.class.php');
}

/**
 * @brief returns an ADODB database object
 * @return ADOConnection
 */
function getDB() {

	import('general.Config');
	$config = new Config();
	$DB = NewADOConnection('mysql://'. $config->getSetting('database', 'user') .':' . $config->getSetting('database', 'password') . '@' .
		$config->getSetting('database', 'host') . '/' . $config->getSetting('database', 'dbname') . '?persist');

	return $DB;
}

/**
 * @brief performs an operation based on the page requested.
 * @param String $op the operation to perform
 * @param int $objectID the objectID representing the object the operation is to be performed on
 * @param ReservesUser $reservesUser the user currently registered with this session (could be anonymous)
 */
function performOp($op, $objectID, &$reservesUser, $extraArgs = array()) {

	import('forms.Form');
	import('general.ReservesRequest');

	switch ($op) {

		case '':
		break;

		case 'assumeUserRole':

			if ($extraArgs[0] != '') {

				import('general.ReservesRequest');
				$assumedEmailID = $extraArgs[0];
				import('auth.LDAPConnection');
				$ldapInfo = LDAPConnection::getUserLDAPInfo($assumedEmailID);
				import('general.Config');
				$config = new Config();
				$accountTypeField = $config->getSetting('ldap', 'account_type_field');

				/* for professors, the accountTypeField will hold the string 'faculty' */
				$_SESSION['assumedUserInfo'] = array('uid' => $ldapInfo['uid'][0], 'cn' => $ldapInfo['cn'][0], 'accountType' => $ldapInfo[$accountTypeField][0]);
				$op = 'viewCourses'; // as a default, redirect to the person's course list
			} else {
				unset($_SESSION['assumedUserInfo']); // if the page is called with no argument, destroy the array and turn it off
				$op = ReservesRequest::getReferringPage();
			}
			ReservesRequest::doRedirect($op);
		break;

		case 'login':

			// Form::isValidSubmission returns true, or an array of strings with the names of the missing fields
			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				if ($reservesUser->logIn()) {
					$currentURI = ReservesRequest::getRequestValue('currentURI');
					if ($currentURI != '') {
						ReservesRequest::doRedirect($currentURI);
					} else {
						ReservesRequest::showHomePage();
					}
				} else {
					ReservesRequest::doRedirect('loginError');
				}
			} else {
				ReservesRequest::doRedirect('loginError');
			}
		break;

		case 'logout':

			$reservesUser->logOut();
			ReservesRequest::showHomePage();
		break;

		case 'editCourse':
		case 'createNewCourse':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				import('items.Course');
				$course = new Course($objectID);
				$course->update();
				return true;
			}
		break;

		case 'itemHeadings':

			import('items.ItemHeading');
			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				$itemHeadingID = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : '0';
				$itemHeading = new ItemHeading($itemHeadingID);

				$itemHeading->update();
				return true;
			}
		break;

		case 'editItemHeading':

			import('items.ItemHeading');

			$itemHeadingID = 0;
			if (preg_match("{^headingTitleText-(\d+)$}", $extraArgs[0], $matches)) {
				$itemHeadingID = $matches[1];
			}

			if ($itemHeadingID > 0) {
				$itemHeading = new ItemHeading($itemHeadingID);
				$itemHeading->updateTitle();
				return true;
			}
		break;

		case 'adminCourseReserves':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				import('items.ReservesRecord');
				if (isCreationAttempt($extraArgs)) {
					$reservesRecord = new ReservesRecord();
				} else {
					$reservesRecord = new ReservesRecord($extraArgs[0]);
				}

				$return = $reservesRecord->update();
				if ($return != RESERVE_RECORD_ITEM_BULK_CREATE_SUCCESS) {
					return true;
				} else {
					ReservesRequest::doRedirect('createReservesItem/0/0');
				}
			}
		break;

		case 'createReservesItem':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				$class = '';
				if (ReservesRequest::getRequestValue('electronicitemid') != '') {
					$class = 'ElectronicReserveItem';
				}
				else {
					$class = 'PhysicalReserveItem';
				}

				import('items.' . $class);

				if (isCreationAttempt($extraArgs)) {
					$reserveItem = new $class ();
				}

				$reserveItem->update();
				return true;
			}
		break;

		case 'editElectronicItem':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				import('items.ElectronicReserveItem');
				$item = new ElectronicReserveItem($objectID);
				$item->update();
				return true;
			}
		break;

		case 'editPhysicalItem':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission) {
				import('items.PhysicalReserveItem');
				print $extraArgs[0];
				$item = new PhysicalReserveItem($objectID);
				$item->update();
				return true;
			}
		break;

		case 'deleteElectronicItem':

			import('items.ElectronicReserveItem');
			$item = new ElectronicReserveItem($objectID);
			if ($item->delete()) {
				$op = ReservesRequest::getReferringPage();
				ReservesRequest::doRedirect($op);
			}
		break;

		case 'deletePhysicalItem':

			import('items.PhysicalReserveItem');
			$item = new PhysicalReserveItem($objectID);
			if ($item->delete()) {
				$op = ReservesRequest::getReferringPage();
				ReservesRequest::doRedirect($op);
			}
		break;

		case 'deleteReservesRecord':

			import('items.ReservesRecord');
			$item = new ReservesRecord($objectID);
			$sectionID = $item->getSectionID();
			if ($item->delete()) {
				$op = ReservesRequest::getReferringPage();
				ReservesRequest::doRedirect("viewReserves/$sectionID");
			}
		break;

		case 'searchByUser':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission || $extraArgs[0] != '') {
				import('auth.LDAPConnection');
				import('items.Section');
				if (ReservesRequest::getRequestValue('emailid') != '' || $extraArgs[0] != '') {
					$emailid = ReservesRequest::getRequestValue('emailid') != '' ? ReservesRequest::getRequestValue('emailid') : $extraArgs[0];
					$ldapInfo = LDAPConnection::getSectionsFromLDAPRecord($emailid);
					$sectionCodes = $ldapInfo[0];
					$commonName = $ldapInfo[1];
					$sections = array();
					if (is_array($sectionCodes)) {
						foreach ($sectionCodes as $code) {
							$sections[ $code ] = Section::getSectionFromCalendarCode($code);
						}
					}
					return array($sections, $commonName, $emailid);

				} else { // they are searching based on a common name search, like last name
					$validEntries = LDAPConnection::getUserIDsFromCNSearch(ReservesRequest::getRequestValue('nameterms'));
					return array('0' => $validEntries);
				}
//				return array($sections, $commonName, ReservesRequest::getRequestValue('emailid'));
			}
		break;

		case 'searchWC':

			if (ReservesRequest::getRequestValue('author') != '' || ReservesRequest::getRequestValue('title') != '') {
				import('search.WorldcatSearch');
				$wcSearch = new WorldcatSearch();
				$results = $wcSearch->doSearch(array('author' => ReservesRequest::getRequestValue('author'), 'title' => ReservesRequest::getRequestValue('title')));
				return $results;
			}

		break;

		case 'quickSearch':

			$validSubmission = Form::isValidSubmission();
			if ($validSubmission || $extraArgs[1] != '') {
				import('search.ReservesSearch');
				$keywords = ReservesRequest::getRequestValue('keywords') != '' ? ReservesRequest::getRequestValue('keywords') : $extraArgs[1];
				$semesterLimit = ReservesRequest::getRequestValue('semester') != '' ? ReservesRequest::getRequestValue('semester') : $extraArgs[2];
				$pageOffset = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
				$reserveCourses = ReservesSearch::searchCourses($keywords, $semesterLimit, $pageOffset);
				return $reserveCourses;
			}
		break;

		case 'reorderItemHeadings':

			if ($objectID > 0) {
				import('items.Section');
				$section = new Section($objectID);
				$sequences = ReservesRequest::getRequestValue('itemHeadings-' . $objectID);
				assert(is_array($sequences));
				$section->updateItemHeadingSequence($sequences);
			}
		break;

		case 'viewCourses':

			$pageOffset = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
			$reserveCourses = $reservesUser->getCourseSections($pageOffset);
			return $reserveCourses;
		break;

		case 'browseCourses':

			import('search.ReservesSearch');
			$sectionPrefixes = ReservesSearch::getActiveSectionPrefixes();
			return $sectionPrefixes;
		break;

		case 'stream':

			if ($objectID > 0) {
				import('items.ElectronicReserveItem');
				$item = new ElectronicReserveItem($objectID);
				$url = $item->getAttribute('url');
				$mimeType = $item->getAttribute('mimeType');
				$originalFileName = $item->getAttribute('originalFileName');
				if ( !$item->isRestricted() || $reservesUser->isAdmin() || ($reservesUser->isLoggedIn() && !$item->requiresEnrolment())
					|| ($item->getReservesRecord()->getSection()->userIsEnrolled($reservesUser->getUserName())) ) {
					header('Content-Type: ' . $mimeType);
//					header('Content-Disposition: attachment; filename="' . urlencode($originalFileName) . '"');
//					header('Content-Length: ' . filesize($url));
					echo file_get_contents($url);
				} else {
					ReservesRequest::doRedirect('securityException');
				}
			}
		break;

		case 'opacProxy':

			if ($objectID > 0) {
				import('items.PhysicalReserveItem');
				$item = new PhysicalReserveItem($objectID);
				$barCode = $item->getBarcode();

				if (ctype_digit($barCode)) { // this is a sanity check
					$content = accessOPACRecord(PhysicalReserveItem::PHYSICAL_RESERVE_ITEM_QUERY, array('barCode' => $item->getBarCode()));
					/* the opacProxy only ever gets used when a user views the catalogue record on the reserves site.  So, we're only ever
					 * querying for a record with a barcode search.
					 */
					echo $content;
				} else {
					return false;
				}
			} else {
				return false;
			}
		break;

		case 'assignInstructors':

			$validSubmission = Form::isValidSubmission();
			import('items.Section');

			if (preg_match('{[a-z0-9]+}', $extraArgs[0])) {
				$section = new Section($objectID);
				return $section->removeSectionRoleForUserID($extraArgs[0]);
			}
			else if ($validSubmission) {
				$section = new Section($objectID);
				return $section->assignSectionRoleForUserID(ReservesRequest::getRequestValue('instructor'), ReservesRequest::getRequestValue('roleID'));
			}
		break;

		case 'findInstructorAssign':

			/* this is the AJAX bit for populating the drop down based on a query against LDAP.  ITS is going to kill me. */
			$userTerms = $extraArgs[1];
			if (preg_match('{[a-z0-9]+}', $userTerms)) {
				import('auth.LDAPConnection');
				$entriesFound = LDAPConnection::getUserIDsFromUIDSearch($userTerms);
				foreach ($entriesFound as $uid => $name) {
					echo $uid . '|' . $name . "\n";
				}
			}
		break;
	}

	return false;
}

/*
 *  @brief decrypts a string that had been encryted with mcrypt()
 *  @param String the string to decrypt
 *  @return @String the plaintext string
 */
function decryptString($string) {

	$string = trim(base64_decode($string));
	$c = mcrypt_cbc (MCRYPT_TripleDES, CRYPT_SECRET_KEY, $string, MCRYPT_DECRYPT, CRYPT_IV);

	return $c;
}

/*
 * @brief encrypts a string with mcrypt()
 * @param String the string to encrypt
 * @return @String the mcrypted string
 */
function encryptString($string) {

	$c = mcrypt_cbc (MCRYPT_TripleDES, CRYPT_SECRET_KEY, $string, MCRYPT_ENCRYPT, CRYPT_IV);
	$c = base64_encode($c);

	return $c;
}

/**
 * @brief examines the arguments passed into the op to determine if a new item is being created.  This
 * is usually the case when the first argument in the array is zero (a new record).
 * @param Array $extraArgs
 * @return boolean true or false
 */
function isCreationAttempt($extraArgs) {
	if ($extraArgs[0] == 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * @brief moves an uploaded file to the assetstore directory, creating a unique file name in the process.  The
 * path to the uploaded file is returned.
 * @param String $uploadedFile the path to the uploaded file
 * @return String the path to the moved file
 */
function moveUploadedAsset($uploadedFile) {

	import('general.Config');
	$config = new Config();
	$assetstore = $config->getSetting('assetstore', 'asset_dir');

	$directoryFragment = date('Y/m/d');

	// our asset directory is of the format /www/reserves/asssetstore/2010/10/24/uniqueFileName
	$saveDir = $assetstore . $directoryFragment;
	if (!file_exists($saveDir)) {
		mkdir($saveDir, 0700, TRUE);
	}

	// we need a unique file name.  Calculate the SHA1 checksum of the uploaded file and use that.
	if (is_uploaded_file($uploadedFile)) {
		$sha1Hash = sha1_file($uploadedFile);
		if ($sha1Hash) {
			$savePath = $saveDir . '/' . $sha1Hash;
			if (move_uploaded_file($uploadedFile, $savePath)) {
				return $savePath;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

/**
 * @brief connects to the library OPAC and performs a command on a reserves record.  $cmd can be one of
 * several predefined constants in PhysicalReserveItem - create, query, edit, and delete.
 * this method assembles the record details into a JSON string and sends it to the defined CGI script in the config.inc.php file
 *
 * @param integer $cmd a predefined constant for what command to perform
 * @param mixed array $recordDetails the fields for the reserve record
 * @return boolean success or failure
 */
function accessOPACRecord($cmd, $recordDetails = array()) {
	import ('general.Config');
	$config = new Config();
	if ($config->getSetting('catalogue', 'catalogue_integration')) {

		import('general.CURLObject');
		$opacController = $config->getSetting('catalogue', 'controller_url');
		$curlObject = new CURLObject($opacController);

		if ($config->getSetting('catalogue', 'controller_disable_ssl_verify')) {
			$curlObject->disableSSLCheck();
		}

		$json_string = json_encode($recordDetails);
		$result = $curlObject->doPost(array('cmd' => $cmd, 'json' => $json_string));
		return $result;
	} else {
		return true;
	}
}

/**
 * @brief convenience method for building the AJAX to submit the quickSearch form.  Needed to correctly
 * build the URL for document.location calls, so the "previous page" referring doc works correctly.
 * @param $basePath the Config setting (ie, /reserves).
 * @return String the JavaScript AJAX snippet.
 */
function getQuickSearchAJAX($basePath) {

	$quickSearchAJAX = '
	<script type="text/javascript">

		$().ready(function() {
			$(\'#searchReserves\').submit(function() {

					keywords = encodeURIComponent($(\'#keywords\').attr(\'value\'));
					section  = encodeURIComponent($(\'#semester\').attr(\'value\'));
					url = "' . ${basePath} . '/index.php/quickSearch/0/0/" + keywords;
					if (section != "") {
						url += "/" + section;
					}
					document.location = url;
					return false;
				}
			);
		});

	</script>';

	return $quickSearchAJAX;
}
?>
