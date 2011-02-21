<?php

class ReservesPage {

	var $title;
	var $template;

	function __construct($title, $op) {
		$this->title = $title . $this->_getTitleForPage($op);
		import('templates.Template');
		$this->template = new Template();

	}

	/**
	 * @brief convenience method for page titles.  FIXME this should really be in locale/i18n or in the template as a variable
	 * @param $op the page operation
	 * @return the title
	 */
	private function _getTitleForPage($op) {

		$titles = array('adminCourseReserves' => 'Admin Course Reserves', 'createNewReserve' => 'Create New Reserve', 'viewReserve' => 'View Reserve');

		if (array_key_exists($op, $titles)) {
			return ' - ' . $titles[$op];
		} else {
			return '';
		}
	}
	/**
	 * @brief returns the current page title for the <title> tag of the document
	 * @return String the title
	 */
	private function _getTitle() {
		return $this->title;
	}

	/**
	 * @brief generates a bit of HTML to produce the login form on every page.  Broken out of other routines because of a need
	 * to have it included on the securityException.tpl template which may lack other  context objects.
	 * @param String $basePath the current base document path.  Normally /reserves
	 * @return $form a forms.Form object, which has its display() method called in the template.
	 */
	private function _getLoginForm($basePath) {

		import('general.ReservesRequest');
		import('forms.Form');

		$form = new Form(array('id' => 'login', 'method' => 'post', 'action' => $basePath . '/index.php/login'));
		$fieldSet = new FieldSet(array('legend' => 'Login'));
		$fieldSet->addField( new HiddenField( array('name' => 'currentURI', 'value' => ReservesRequest::getRequestURI() ) ) );
		$fieldSet->addField(new TextField( array('name' => 'username','primaryLabel' => 'Email ID', 'secondaryLabel' => 'Your UNB UserID', 'required' => true,
				'requiredMsg' => 'Please enter a UNB user ID')) );
		$fieldSet->addField(new Password());
		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Login')) );
		$form->addFieldSet($fieldSet);

		return $form;
	}

	/**
	 * @brief returns a template page header
	 * @param ReservesUser $reservesUser
	 */
	public function getHeader(&$reservesUser) {

		include_once ("/www/core/inc/func.php");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"><head>  <title>University of New Brunswick Libraries - ';

		echo $this->_getTitle();

		echo '</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta name="description" content="University of New Brunswick Libraries website serving the UNB Fredericton, UNB Saint John and St. Thomas University research and academic communities." />
			<meta name="keywords" content="library,journals,databases,ebooks,government documents,indexes,abstracts,books,maps,catalogue,research,Harriet Irving Library,Science and Forestry Library,Engineering Library" />';
		include_once ("/www/core/inc/headfiles.php");
		echo '
			<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
			<link rel="stylesheet" type="text/css" media="screen" href="/core/css/validforms.css" />
			<script src="/core/js/jquery.validate.js" type="text/javascript"></script>
			<script src="/core/js/jquery.tablednd_0_5.js" type="text/javascript"></script>';


			echo '</head>
			<body class="twoCol lite reserves">';

		include_once ("/www/core/inc/headnav.php");

		echo '<div id="contentwrapper">
			<div id="contentcolumn">
			<div class="innertube">';
	}

	/**
	 * @brief returns a template page footer
	 * @param ReservesUser $reservesUser
	 */
	public function getFooter(&$reservesUser) {

		echo '	</div><!-- end innertube -->
				</div><!-- end contentcolumn -->
				</div><!-- end contentwrapper --> ';

		import('general.Config');
		$config = new Config();

		$this->template->loadTemplate('sideBar.tpl', array('user' => $reservesUser, 'basePath' => $config->getSetting('general', 'base_path')));

		include_once ("/www/core/inc/footer.php");

		// the next little block automatically puts the focus on the first visible text field on the page.
		echo '
			<script type="text/javascript">
				<!--
					var inputs = $(":text:visible");
					if (inputs.length > 0) {
					  inputs[0].focus();
					}
				// -->
			</script>';

		echo '</body></html>';

	}
	/**
	 * @brief builds a page for the Templating sytem, based on the current operation, user, and oject.  Accepts
	 * an extra $opPerformed object that can contain information relevant to the page
	 * @param String $op
	 * @param int $objectID
	 * @param ReservesUser $reservesUser
	 * @param array $opPerformed
	 */
	public function showPage($op, $objectID, &$reservesUser, $opPerformed = false, $extraArgs = array()) {

		import('general.Config');
		import('general.ReservesRequest');
		import('forms.Form');

		$config = new Config();
		$basePath = $config->getSetting('general', 'base_path');
		$templateState = array('page' => $this, 'basePath' => $basePath, 'user' => $reservesUser, 'opPerformed' => $opPerformed);
		$referringDoc = ReservesRequest::getReferringPage() != '' ? ReservesRequest::getReferringPage() : 'javascript:history.go(-1)';
		$breadCrumb = '<a href="' . $basePath . '/index.php">Home</a> | <a href="' . $referringDoc . '">Previous Page</a> ';

		if ($reservesUser->isAnonymous()) {
			$templateState['loginForm'] = $this->_getLoginForm($basePath);
			unset($form, $fieldSet);
		}

		switch ($op) {

			case 'quickSearch':
				$templateState['courses'] = $opPerformed['0'];
				$templateState['totalRecords'] = $opPerformed['1'];

				$templateState['pageOffset'] = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;

				$templateState['keywords'] = $extraArgs[1] != '' ? $extraArgs[1] : ReservesRequest::getRequestValue('keywords');
				$templateState['semester'] = $extraArgs[2] != '' ? $extraArgs[2] : ReservesRequest::getRequestValue('semester');

				// NOTE this case falls through to get the search form that is present on the default index page.

			case '': // the default front page case
				if ($op == '') {
					$op = 'index';
					$breadCrumb = '';
				}

				$templateState['extraJS'] = getQuickSearchAJAX($basePath);
				$formsArray = array();

				$form = new Form(array('id' => 'searchReserves', 'method' => 'post', 'action' => $basePath . '/index.php/quickSearch'));
				$fieldSet = new FieldSet(array('legend' => 'Reserves Quick Search'));

				$keywords = ReservesRequest::getRequestValue('keywords') != '' ? htmlspecialchars(ReservesRequest::getRequestValue('keywords')) : $extraArgs[1];

				$fieldSet->addField(new TextField( array('name' => 'keywords', 'primaryLabel' => 'Search', 'secondaryLabel' => 'Enter some keywords to search for', 'required' => true,
							'requiredMsg' => 'Please enter some keywords', 'value' => $keywords)));
				import('items.Section');
				$fieldSet->addField(Section::getSemesterDropdown($templateState['semester']));
				$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );

				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;

				$templateState['forms'] = $formsArray;
				break;

			case 'assignInstructors':

				import('items.Section');
				$section = new Section($objectID);
				$templateState['sectionRoles'] = $section->getSectionRoles();
				$templateState['sectionID'] = $objectID;

				$formsArray = array();
				$form = new Form(array('id' => 'assignInstructors', 'method' => 'post', 'action' => $basePath . '/index.php/assignInstructors/' . $section->getSectionID()));
				$fieldSet = new FieldSet(array('legend' => 'Assign Instructors'));
				$fieldSet->addField(new TextField( array('name' => 'instructor', 'primaryLabel' => 'Instructor', 'secondaryLabel' => 'The user ID (not name) of the person to add',
							'required' => true, 'requiredMsg' => 'You must enter a User ID', 'value' => '') ));
				$select = new Select(array('name' => 'roleID', 'primaryLabel' => "User's Role", 'secondaryLabel' => 'Choose ' . $secondaryLabel, 'required' => true,
				'requiredMsg' => 'Please choose a role for this user', 'value' => ''));

				foreach ($reservesUser->getAllUserRoles() as $roleID => $roleDesc) {
					$select->addOption(array('value' => $roleID, 'label' => $roleDesc));
				}
				$fieldSet->addField($select);
				$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;
				$templateState['forms'] = $formsArray;
				break;

			case 'editCourse':
			case 'createNewCourse':

				$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewCourses">View Your Courses</a>';
				$formsArray = array();
				import('items.Course');
				$course = new Course($objectID);
				$editForm = $course->assembleEditForm($basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;

				break;

			case 'editSection':
			case 'createNewSection':

				$formsArray = array();
				import('items.Section');
				$section = new Section($objectID);
				$editForm = $section->assembleEditForm($basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;

				break;

			case 'itemHeadings':

				import('items.Section');
				import('items.ItemHeading');
				$itemHeadingID = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
				$itemHeading = new ItemHeading($itemHeadingID);
				$section = new Section($objectID);
				$itemHeadings = $section->getHeadings();
				$templateState['itemHeadings'] = $itemHeadings;
				$templateState['section'] = $section;
				$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewReserves/' . $objectID . '">View This Section\'s Reserves</a>';

				$itemHeading->setSectionID($section->getSectionID());
				$formsArray[] = $itemHeading->assembleEditForm($basePath);
				$templateState['forms'] = $formsArray;
				break;

			case 'deleteItemHeading':

				import('items.Section');
				$section = new Section($objectID);
				$section->deleteHeading(intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0);
				break;

			case 'adminReserve':
			case 'createNewReserve':

				$formsArray = array();
				import('items.ReservesRecord');
				// check to see if this was a "bulk add" request from an admin
				$reservesRecord = new ReservesRecord($objectID);

				if ($reservesUser->isAdmin() && !$reservesUser->isActing()) {
					$sectionIDs = ReservesRequest::getRequestValue('sectionIDs');
					if (sizeof($sectionIDs) > 0) {
						import('items.Section');
						$defaultHeading = $config->getSetting('bulk_reserves', 'default_heading_title');
						$itemHeadings = array();
						foreach ($sectionIDs as $sectionID) {
							$section = new Section($sectionID);
							$itemHeading = $section->hasHeading($defaultHeading);
							if (!$itemHeading) {
								$itemHeadings[] = $section->addHeading($defaultHeading);
							} else {
								$itemHeadings[] = $itemHeading;
							}
						}
						$reservesRecord->setPossibleItemHeadings($itemHeadings);

					}
				}
				$editForm = $reservesRecord->assembleEditForm($basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				break;


			case 'adminCourseReserves':
				$formsArray = array();

				import('items.ItemHeading');
				import('items.Section');
				import('items.ReservesRecord');

				$itemHeading = new ItemHeading($objectID);
				$section = new Section($itemHeading->getSectionID());

				$reserves = $section->getReserves();
				$templateState['itemHeading'] = $itemHeading;
				$templateState['reserves'] = $reserves;
				$reservesRecord = new ReservesRecord(intval($extraArgs[0]));
				$templateState['section'] = $section;
				$reservesRecord->setPossibleItemHeadings($section->getHeadings());
				$editForm = $reservesRecord->assembleEditForm($basePath);
				$templateState['reservesRecord'] = $reservesRecord;
				$templateState['reservesForm'] = $editForm;
				break;

			case 'editElectronicItem':

				$formsArray = array();
				import('items.ElectronicReserveItem');
				$item = new ElectronicReserveItem($objectID);
				$editForm = $item->assembleEditForm($basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewReserve/' . $item->getReservesRecordID() . '">View Reserves Record</a>';

				break;

			case 'editPhysicalItem':

				$formsArray = array();
				import('items.PhysicalReserveItem');
				$item = new PhysicalReserveItem($objectID);
				$editForm = $item->assembleEditForm($basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewReserve/' . $item->getReservesRecordID() . '">View Reserves Record</a>';

				break;

			case 'viewReserves':

				import('items.Section');
				$section = new Section($objectID);
				$itemHeadings = $section->getHeadings();
				$templateState['section'] = $section;
				$templateState['itemHeadings'] = $itemHeadings;

				if ($reservesUser->isLoggedIn()) {
					$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewCourses">View Your Courses</a>';
				}
				break;

			case 'viewCourses':

				$templateState['courses'] = $opPerformed['0'];
				$templateState['totalRecords'] = $opPerformed['1'];
				$templateState['pageOffset'] = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
				break;

			case 'viewSections':

				import('items.Course');
				if ($objectID > 0) {
					$course = new Course($objectID);
					$sections = $course->getSections();
					$templateState['course'] = $course;
					$templateState['sections'] = $sections;
					$templateState['defaultHeadingTitle'] = $config->getSetting('bulk_reserves', 'default_heading_title');
				}
				if ($reservesUser->isLoggedIn()) {
					$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewCourses">View Your Courses</a>';
				}
				break;

			case 'viewReserve':

				import('items.ReservesRecord');
				$reservesRecord = new ReservesRecord($objectID);
				$templateState['reservesRecord'] = $reservesRecord;
				$breadCrumb .= ' | <a href="' . $basePath . '/index.php/viewReserves/' . $reservesRecord->getSectionID()  . '">View This Section\'s Other Reserves </a>';
				break;

			case 'searchByUser':

				$formsArray = array();
				$form = new Form(array('id' => 'searchByName', 'method' => 'post', 'action' => $basePath . '/index.php/searchByUser'));
				$fieldSet = new FieldSet(array('legend' => 'Find Courses for a User Name'));
				$fieldSet->addField(new TextField( array('name' => 'nameterms','primaryLabel' => 'Some Name Terms', 'secondaryLabel' => '', 'required' => true,
							'value' => ReservesRequest::getRequestValue('nameterms'),  'minlength' => 3, 'requiredMsg' => 'Please enter at least 3 letters')) );

				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;

				unset ($fieldSet, $form);
				$form = new Form(array('id' => 'searchByID', 'method' => 'post', 'action' => $basePath . '/index.php/searchByUser'));
				$fieldSet = new FieldSet(array('legend' => 'Find Courses for a User ID'));
				$fieldSet->addField(new TextField( array('name' => 'emailid','primaryLabel' => 'UNB Email ID', 'secondaryLabel' => 'A UNB email ID', 'required' => true,
							'value' => ReservesRequest::getRequestValue('emailid'),  'requiredMsg' => 'Please enter an email ID')) );

				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;
				$templateState['forms'] = $formsArray;
				// in this case, the opPerformed is a list of courses from the user's LDAP record, and the user's common name 'cn' ldap attribute
				if (sizeof($opPerformed) == 1) {
					$templateState['validEntries'] = $opPerformed[0];
				} else {
					$templateState['sections'] = $opPerformed[0];
					$templateState['commonName'] = $opPerformed[1];
					$templateState['emailID'] = $opPerformed[2];
				}

				$templateState['defaultHeadingTitle'] = $config->getSetting('bulk_reserves', 'default_heading_title');

				break;

			case 'browseCourses':

				$templateState['letterSections'] = $opPerformed;
				break;

			case 'viewAllReserves':

				import('search.ReservesSearch');
				import('items.Section');
				$pageOffset = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
				$semester = Section::isValidSemester($extraArgs[1]) ? $extraArgs[1] : Section::getCurrentSemester();
				$sectionsWithReserves = ReservesSearch::getSectionsWithReserves($pageOffset, $semester);
				$templateState['sections'] = $sectionsWithReserves;
				$templateState['semesterForm'] = Section::assembleSemesterChooseForm($semester);

			break;

			case 'createReservesItem':

				import('items.ElectronicReserveItem');
				import('items.PhysicalReserveItem');

				$electronicReserveItem = new ElectronicReserveItem(intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0);
				$electronicReserveItem->setAttribute('reservesrecordid', $objectID);
				$templateState['electronicReserveForm'] = $electronicReserveItem->assembleEditForm($basePath);

				$physicalReserveItem = new PhysicalReserveItem(intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0);
				$physicalReserveItem->setAttribute('reservesrecordid', $objectID);
				$templateState['physicalReserveForm'] = $physicalReserveItem->assembleEditForm($basePath);

			break;

			case 'searchWC':

				$formsArray = array();
				import('search.WorldcatSearch');
				$formsArray[] = WorldcatSearch::buildWCSearchForm($basePath);
				$templateState['forms'] = $formsArray;

			break;

			case 'unenrolStudents':

				import('items.Section');
				$section = new Section($objectID);
				$section->unenrolStudents();
				break;

		}

		$templateState['action'] = $op;
		$templateState['breadCrumb'] = $breadCrumb;
		$this->template->loadTemplate($op . '.tpl', $templateState);
	}

	/**
	 * @brief directs to a security error page
	 * @return void
	 */
	public function showSecurityException(&$reservesUser) {
		import('general.Config');
		$config = new Config();
		$templateState = array('loginForm' => $this->_getLoginForm($config->getSetting('general', 'base_path')), 'breadCrumb' => '<a href="/reserves/index.php">Home</a>', 'page' => $this, 'user' => $reservesUser, 'basePath' => $config->getSetting('general', 'base_path'));
		$this->template->loadTemplate('securityException.tpl', $templateState);
	}
}
?>
