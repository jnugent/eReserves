<?php

class ReservesPage {

	var $title;
	var $template;
	var $basePath;
	var $op;

	function __construct($title, $op) {
		$this->op = $op;
		$this->title = $title . $this->_getTitleForPage($op);
		import('templates.Template');
		$this->template = new Template();

		import('general.Config');
		$config = new Config();
		$this->basePath =& $config->getSetting('general', 'base_path');
	}

	/**
	 * @brief convenience method for page titles.  FIXME this should really be in locale/i18n or in the template as a variable
	 * @param $op the page operation
	 * @return the title
	 */
	private function _getTitleForPage($op) {

		$titles = array('adminCourseReserves' => 'Admin Course Reserves', 'createNewReserve' => 'Create New Reserve', 'viewReserve' => 'View Reserve', 'viewReserves' => 'View Reserves for Section');

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
	 * @param String $this->basePath the current base document path.  Normally /reserves
	 * @return $form a forms.Form object, which has its display() method called in the template.
	 */
	private function _getLoginForm() {

		import('general.ReservesRequest');
		import('forms.Form');

		$highlight = isset($_GET['h']) ? 'highlight' : '';
		$form = new Form(array('id' => 'login', 'class' => $highlight, 'method' => 'post', 'action' => $this->basePath . '/index.php/login'));
		$fieldSet = new FieldSet(array('legend' => 'Login'));
		$fieldSet->addField( new HiddenField( array('name' => 'currentURI', 'value' => ReservesRequest::getRequestURI() ) ) );
		$fieldSet->addField(new TextField( array('name' => 'username','primaryLabel' => 'Login ID', 'secondaryLabel' => '', 'required' => true,
				'requiredMsg' => 'Please enter a UNB user ID')) );
		$fieldSet->addField(new Password());
		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Login')) );
		$form->addFieldSet($fieldSet);

		return $form;
	}

	private function _notDownloadLogin() {
		if ($this->op != 'downloadLogin') {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @brief returns a template page header
	 * @param ReservesUser $reservesUser
	 */
	public function getHeader($reservesUser) {

		$mobileClass = $reservesUser->isMobile() ? ' class="mobile"' : '';
		import('general.Config');
		$config = new Config();

		include_once ("/www/core/inc/func.php");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html ' . $mobileClass . ' xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"><head>  <title>University of New Brunswick Libraries - ';

		echo $this->_getTitle();

		echo '</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta name="description" content="University of New Brunswick Libraries website serving the UNB Fredericton, UNB Saint John and St. Thomas University research and academic communities." />
			<meta name="keywords" content="library,journals,databases,ebooks,government documents,indexes,abstracts,books,maps,catalogue,research,Harriet Irving Library,Science and Forestry Library,Engineering Library" />';
		include_once ("/www/core/inc/headfiles.php");
		echo '
			<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
			<link rel="stylesheet" type="text/css" media="screen" href="/core/css/validforms.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="' . $this->basePath . '/css/reserves.css" />
			<script src="/core/js/jquery.validate.js" type="text/javascript"></script>
			<script src="/core/js/jquery.tablednd_0_5.js" type="text/javascript"></script>';
		echo '
			<script type="text/javascript" src="' . $this->basePath . '/js/reserves.js"></script>';
		if ($this->op == 'viewReserves') {
			import('general.ReservesRequest');
			$urlOp = ReservesRequest::getURLOp();
			if (is_numeric($urlOp[1])) {
				echo '<link rel="alternate" type="application/rss+xml" title="Course Reserves" href="' . $this->basePath . '/index.php/feed/' . (int) $urlOp[1] . '" />';
			}
		}

		echo '</head>
		<body class="twoCol lite reserves' . (!$this->_notDownloadLogin() ? ' downloadOnly' : '')  . '">';

		if ($this->_notDownloadLogin()) {

			include_once ("/www/core/inc/headnav.php");

			echo '<div id="contentwrapper">
				<div id="contentcolumn">
				<div class="innertube">';
		}
	}

	/**
	 * @brief returns a template page footer
	 * @param ReservesUser $reservesUser
	 */
	public function getFooter($reservesUser) {

		if ($this->_notDownloadLogin()) {

			echo '	</div><!-- end innertube -->
					</div><!-- end contentcolumn -->
					</div><!-- end contentwrapper --> ';

			$this->template->loadTemplate('sideBar.tpl', array('loginForm' => $this->_getLoginForm(), 'user' => $reservesUser, 'basePath' => $this->basePath));


			include_once ("/www/core/inc/footer.php");

			if ($_SESSION['loginError'] == true) {
				echo '
					<script type="text/javascript">
						<!--
							$(document).ready(function() {
								$("#loginError").show();
							});
						// -->
					</script>';
			}
		}
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

		$templateState = array('page' => $this, 'basePath' => $this->basePath, 'user' => $reservesUser, 'opPerformed' => $opPerformed);
		$referringDoc = '';
		$referringLabel = '';

		if ($_SESSION['quickSearchURL'] != '') {
			$referringDoc = $_SESSION['quickSearchURL'];
			$search = preg_replace('{^/.*/quickSearch/\d+/\d+/}', '', $referringDoc);
			list ($keywords, $term) = preg_split('{/}', $search);
			$label = 'Searched for: &quot;' . htmlentities(urldecode($keywords)) . '&quot;' . (($term != '') ? ' in ' . $term : '');
			$referringLabel = 'Last Search Results';
		} else {
			$referringDoc = ReservesRequest::getReferringPage() != '' ? ReservesRequest::getReferringPage() : 'javascript:history.go(-1)';
			$referringLabel = 'Previous Page';
		}

		$breadCrumb = '<a href="' . $this->basePath . '/index.php">Reserves Home</a> | <a title="' . $label . '" href="' . $referringDoc . '">' . $referringLabel . '</a> ';

		if ($reservesUser->isAnonymous()) {
			$templateState['loginForm'] = $this->_getLoginForm();
			unset($form, $fieldSet);
		}

		switch ($op) {

			case 'quickSearch':
				$templateState['items'] = $opPerformed['0'];
				$templateState['totalRecords'] = $opPerformed['1'];
				$templateState['recordType'] = $opPerformed['2'];
				$templateState['prefixSuggest'] = $opPerformed['3'];

				$templateState['pageOffset'] = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;

				$templateState['keywords'] = $extraArgs[1] != '' ? $extraArgs[1] : ReservesRequest::getRequestValue('keywords');
				$templateState['semester'] = $extraArgs[2] != '' ? $extraArgs[2] : ReservesRequest::getRequestValue('semester');

				$templateState['keywords'] = htmlspecialchars($templateState['keywords']);
				$templateState['semester'] = htmlspecialchars($templateState['semester']);
				$templateState['corrected'] = isset($_GET['corrected']) ? true : false;

				// NOTE this case falls through to get the search form that is present on the default index page.

			case '': // the default front page case
				if ($op == '') {
					$op = 'index';
					$breadCrumb = '';
				}

				$templateState['extraJS'] = getQuickSearchAJAX($this->basePath);
				$formsArray = array();

				$form = new Form(array('id' => 'searchReserves', 'method' => 'post', 'action' => $this->basePath . '/index.php/quickSearch'));
				$fieldSet = new FieldSet(array('legend' => 'Reserves Quick Search'));

				$keywords = ReservesRequest::getRequestValue('keywords') != '' ? htmlspecialchars(ReservesRequest::getRequestValue('keywords')) : $extraArgs[1];

				$fieldSet->addField(new TextField( array('name' => 'keywords', 'primaryLabel' => 'Search', 'secondaryLabel' => 'Search by instructor, course name or course number.', 'required' => true,
							'requiredMsg' => 'Please enter some keywords', 'value' => $templateState['keywords'])));

				import('items.Section');
				$fieldSet->addField(Section::getSemesterDropdown(TRUE, $op, $templateState['semester']));
				$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );

				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;

				$templateState['forms'] = $formsArray;
				break;

			case 'assignPeople':

				import('items.Section');
				$section = new Section($objectID);
				$templateState['sectionRoles'] = $section->getSectionRoles();
				$templateState['sectionID'] = $objectID;

				$formsArray = array();
				$form = new Form(array('id' => 'assignPeople', 'method' => 'post', 'action' => $this->basePath . '/index.php/assignPeople/' . $section->getSectionID()));
				$fieldSet = new FieldSet(array('legend' => 'Assign People to this Section'));
				$fieldSet->addField(new TextField( array('name' => 'instructor', 'primaryLabel' => 'Person', 'secondaryLabel' => 'The user ID (not name) of the person to add',
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
				$breadCrumb = '<a href="' . $this->basePath . '/index.php/viewCourses">View Your Courses</a> | <a href="' . $this->basePath . '/index.php/viewReserves/' . $objectID . '">View Reserves for this Section</a>';
				break;

			case 'downloadLogin':
					if ($_SESSION['loginError'] && !$_SESSION['streamNoAccess']) {
						$templateState['loginError'] = true;
					} else if ($_SESSION['streamNoAccess']) {
						$templateState['noAccess'] = true;
					}
					$templateState['loginForm'] = $this->_getLoginForm();
				break;
			case 'editCourse':
			case 'createNewCourse':

				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewCourses">View Your Courses</a>';
				$formsArray = array();
				import('items.Course');
				$course = new Course($objectID);
				$editForm = $course->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;

				break;

			case 'editSection':
			case 'createNewSection':

				$formsArray = array();
				import('items.Section');
				$section = new Section($objectID);
				$editForm = $section->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;

				break;

			case 'feed':

				$templateState['date'] = date('r');
				$templateState['items'] = $opPerformed[0];
				$templateState['instructors'] = $opPerformed[1];
				$templateState['sectionInfo'] = $opPerformed[2];
				$templateState['baseUrl'] = 'https://' . $config->getSetting('general', 'host_name');

				break;

			case 'adminSemesters':

				import('general.Semester');
				$semesters = Semester::getSemesters();
				$templateState['semesters'] = $semesters;

				$semesterID = intval($objectID);
				$semester = new Semester($semesterID);
				$editForm = $semester->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;
				$templateState['semester'] = $semester;
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
				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewReserves/' . $objectID . '">View This Section\'s Reserves</a>';

				$itemHeading->setSectionID($section->getSectionID());
				$formsArray[] = $itemHeading->assembleEditForm($this->basePath);
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
				$editForm = $reservesRecord->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				break;

			case 'adminCourseReserves':
				$formsArray = array();

				import('items.ItemHeading');
				import('items.Section');
				import('items.ReservesRecord');

				// we test here to see if $objectID is 0.  If it was, there was no item heading chosen.  So we create one
				// based on the default from the config file, just like if it was a bulk add.
				// NOTE: JUST in this case, the $extraArgs[0] parameter will be the sectionID.

				$itemHeadingID = 0;
				$reservesRecordID = 0;

				if ($objectID == 0) {
					$itemHeadingID = ItemHeading::create(intval($extraArgs[0]), $config->getSetting('bulk_reserves', 'default_heading_title'));
				} else {
					$itemHeadingID = $objectID;
					$reservesRecordID = intval($extraArgs[0]);
				}

				$itemHeading = new ItemHeading($itemHeadingID);

				$section = new Section($itemHeading->getSectionID());

				//$reserves = $section->getReserves();
				$reserves = $itemHeading->getListedReserves();

				$templateState['itemHeading'] = $itemHeading;
				$templateState['reserves'] = $reserves;
				$reservesRecord = new ReservesRecord($reservesRecordID);
				$templateState['section'] = $section;
				$reservesRecord->setItemHeadingID($itemHeadingID);
				$reservesRecord->setPossibleItemHeadings($section->getHeadings());
				$editForm = $reservesRecord->assembleEditForm($this->basePath);
				$templateState['reservesRecord'] = $reservesRecord;
				$templateState['reservesForm'] = $editForm;

				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/itemHeadings/' . $section->getSectionID() . '/0">View other section headings</a>';
				break;

			case 'editElectronicItem':

				$formsArray = array();
				import('items.ElectronicReserveItem');
				$item = new ElectronicReserveItem($objectID);
				$editForm = $item->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewReserve/' . $item->getReservesRecordID() . '">View Reserves Record</a>';

				break;

			case 'editPhysicalItem':

				$formsArray = array();
				import('items.PhysicalReserveItem');
				$item = new PhysicalReserveItem($objectID);
				$editForm = $item->assembleEditForm($this->basePath);
				$formsArray[] = $editForm;

				$templateState['forms'] = $formsArray;
				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewReserve/' . $item->getReservesRecordID() . '">View Reserves Record</a>';

				break;

			case 'viewReserves':

				import('items.Section');
				$section = new Section($objectID);
				$itemHeadings = $section->getHeadings();
				$templateState['section'] = $section;
				$templateState['itemHeadings'] = $itemHeadings;
				$templateState['defaultHeadingTitle'] = $config->getSetting('bulk_reserves', 'default_heading_title');
				if ($reservesUser->isLoggedIn()) {
					$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewCourses">View Your Courses</a>';
				}
				break;

			case 'viewCourses':

				$templateState['items'] = $opPerformed['0'];
				$templateState['totalRecords'] = $opPerformed['1'];
				$templateState['pageOffset'] = intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0;
				if (isset($extraArgs[1])) {
					list($templateState['courseNameFilter'], $templateState['courseCodeFilter']) = preg_split("/\|/", $extraArgs[1]);
				}
				break;

			case 'viewSections':

				import('items.Course');
				if ($objectID > 0) {
					$course = new Course($objectID);
					$includeSJ = 'FR';
					if (isset($extraArgs[0])) {
						$includeSJ = $extraArgs[0];
					}
					$sections = $course->getSections($includeSJ);
					$templateState['course'] = $course;

					$includeSections = array('All Sections' => 'ALL', 'Fredericton only' => 'FR', 'Saint John only' => 'SJ');
					$includeSectionLinks = array();
					foreach ($includeSections as $linkText => $urlOp) {
						$style = ($urlOp == $includeSJ) ? 'style="font-weight: bold;"' : '';
						$includeSectionLinks[] .= '<span ' . $style . '>' . (($urlOp != $includeSJ) ? '<a href="' . $this->basePath .
						'/index.php/viewSections/' .  $objectID .
						(($urlOp != '') ? '/' . $urlOp : '') . '">' : '') . $linkText .
						($urlOp != $includeSJ ? '</a>' : '') . '</span>';
					}
					$templateState['includeSections'] = join(' | ', $includeSectionLinks);
					$templateState['includeSJ'] = $includeSJ;
					$templateState['sections'] = $sections;
					$templateState['defaultHeadingTitle'] = $config->getSetting('bulk_reserves', 'default_heading_title');
				}
				if ($reservesUser->isLoggedIn()) {
					$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewCourses">View Your Courses</a>';
				}
				break;

			case 'viewReserve':

				import('items.ReservesRecord');
				$reservesRecord = new ReservesRecord($objectID);
				$templateState['reservesRecord'] = $reservesRecord;
				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewReserves/' . $reservesRecord->getSectionID()  . '">View This Section\'s Other Reserves </a>';
				break;

			case 'searchByUser':

				$formsArray = array();
				$form = new Form(array('id' => 'searchByName', 'method' => 'post', 'action' => $this->basePath . '/index.php/searchByUser'));
				$fieldSet = new FieldSet(array('legend' => 'Find Courses for a User Name'));
				$fieldSet->addField(new TextField( array('name' => 'nameterms','primaryLabel' => 'Some Name Terms', 'secondaryLabel' => '', 'required' => true,
							'value' => ReservesRequest::getRequestValue('nameterms'),  'minlength' => 3, 'requiredMsg' => 'Please enter at least 3 letters')) );

				$form->addFieldSet($fieldSet);
				$formsArray[] = $form;

				unset ($fieldSet, $form);
				$form = new Form(array('id' => 'searchByID', 'method' => 'post', 'action' => $this->basePath . '/index.php/searchByUser'));
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
				list($sectionsWithReserves, $totalRecords) = ReservesSearch::getSectionsWithReserves($pageOffset, $semester);
				$templateState['items'] = $sectionsWithReserves;
				$templateState['semester'] = $semester;
				$templateState['totalRecords'] = $totalRecords;
				$templateState['pageOffset'] = $pageOffset;
				$templateState['semesterForm'] = Section::assembleSemesterChooseForm($semester);

			break;

			case 'createReservesItem':

				import('items.ElectronicReserveItem');
				import('items.PhysicalReserveItem');
				import('items.ReservesRecord');

				$reservesRecord = new ReservesRecord(intval($objectID));
				$electronicReserveItem = new ElectronicReserveItem(intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0);
				$electronicReserveItem->setAttribute('reservesrecordid', $objectID);
				$electronicReserveItem->setAttribute('itemtitle', $reservesRecord->getTitle());
				$templateState['electronicReserveForm'] = $electronicReserveItem->assembleEditForm($this->basePath);

				$physicalReserveItem = new PhysicalReserveItem(intval($extraArgs[0]) > 0 ? intval($extraArgs[0]) : 0);
				$physicalReserveItem->setAttribute('reservesrecordid', $objectID);
				$templateState['physicalReserveForm'] = $physicalReserveItem->assembleEditForm($this->basePath);
				$breadCrumb .= ' | <a href="' . $this->basePath . '/index.php/viewReserves/' . $reservesRecord->getSectionID() . '">Return to the Course Section</a>';

			break;

			case 'searchWC':

				$formsArray = array();
				import('search.WorldcatSearch');
				$formsArray[] = WorldcatSearch::buildWCSearchForm($this->basePath);
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
	public function showSecurityException($reservesUser) {
		import('general.Config');
		$config = new Config();
		$templateState = array('loginForm' => $this->_getLoginForm(), 'breadCrumb' => '<a href="/reserves/index.php">Reserves Home</a>', 'page' => $this, 'user' => $reservesUser, 'basePath' => $this->base_path);
		$this->template->loadTemplate('securityException.tpl', $templateState);
	}
}
?>
