<?php

class ReservesSearch {

	public function __construct() {
		return true;
	}

	/**
	 * @brief a prototype function for generating a list of Courses based on a keyword search in the title.
	 * // FIXME make this search more useful.
	 * @param $keywords the string to look for.
	 * @return array an array of Course objects that matched, or an empty array if none.
	 */
	static function searchSections($reservesUser, $keywords, $semester, $offset = 0) {

		$db = getDB();
		import('items.Course');
		import('items.Section');
		import('general.ReservesRequest');

		$searchType = Course::COURSE_SEARCH_RETURN_ALL;
		if (preg_match("/browseCourses$/", ReservesRequest::getReferringPage()) || isset($_GET['corrected'])) {
			$searchType = Course::COURSE_SEARCH_RETURN_ONLY_PREFIX;
		}
		$fields = Course::getSearchFields($searchType);

		$semesterSQL = '';
		$sqlParams = array();
		$adminRequest = $reservesUser->isAdmin();
		$year = '';
		$term = '';
		$groupBySQL = '';

		if ($semester != '') {
			//$semester = Section::getCurrentSemester();
			if (preg_match('|^(\d+)(\w{2})$|', $semester, $matches)) {
				$year = $matches[1];
				$term = $matches[2];
			}
		} else {
			$groupBySQL = ' ORDER BY concat(year, term)';
		}

		$sectionRoleSQL = ' WHERE ';
		//$sectionRoleSQL = ', sectionRole sr WHERE sr.sectionID = s.sectionID AND ';
		$sectionRoleSQL = ' LEFT JOIN sectionRole sr ON (sr.sectionID = s.sectionID) WHERE ';
		if (!$adminRequest) {
			$semesterSQL = ', reservesRecord r, itemHeading i, section s ' . $sectionRoleSQL . ' s.courseID = c.courseID ';
			if ($year != '' && $term != '') {
				if (in_array($term, array('FA','WI'))) {
					$semesterSQL .= "AND s.year = ? AND (s.term = ? OR s.term = 'FY') ";
				} 
				else {
					$semesterSQL .= 'AND s.year = ? AND s.term = ? ';
				} 
			}
			$semesterSQL .= 'AND s.sectionID = i.sectionID AND i.itemHeadingID =  r.itemHeadingID';
		}

		else {
			$semesterSQL = ', section s ' . $sectionRoleSQL . ' s.courseID = c.courseID ';
			if ($year != '' && $term != '') {
				if (in_array($term, array('FA','WI'))) { 
					$semesterSQL .= "AND s.year = ? AND (s.term = ? OR s.term = 'FY') ";
				}
				else {
					$semesterSQL .= 'AND s.year = ? AND s.term = ? ';
				} 
			}
		}

		if ($year != '' && $term != '') {
			$sqlParams = array($year, $term);
		}

		 //first query to get the total number of records
		$sql = 'SELECT DISTINCT s.sectionID FROM course c';
		$prefix = '';
		$whereClause = self::buildMySQLWhereClause($fields, $keywords, $semesterSQL, $db, $prefix, $searchType);
		$sql .= $whereClause;
error_log($sql) ;
		$returnStatement = $db->Execute($sql, $sqlParams);
		$totalRecords = $returnStatement->RecordCount();

		if ($totalRecords > 0) { // there's no point in doing the real query again, if we have zero records that matched the full one

			$sql = 'SELECT DISTINCT s.sectionID FROM course c';


			$limitClause = " LIMIT $offset, 25";
			$sql .= $whereClause;
			if ($groupBySQL != '') {
				$sql .= $groupBySQL;
			}
			$sql .= $limitClause;

			$returnStatement = $db->Execute($sql, $sqlParams);
			if ($returnStatement->RecordCount() > 0) {
				$matchedSections = array();
				while ($recordObject = $returnStatement->FetchNextObject()) {
					$matchedSections[] = new Section($recordObject->SECTIONID);
				}
				usort($matchedSections, function($s1, $s2){
					return strcmp($s1->getYearTerm(), $s2->getYearTerm());
				}) ;
				return array ('0' => $matchedSections, '1' => $totalRecords, '2' => 'SEMESTER', '3' => $prefix);
			} else {
				return array('0' => null, '1' => 0, '2' => 'SEMESTER', '3' => $prefix);
			}
		} else {
			return array('0' => null, '1' => 0, '2' => 'SEMESTER', '3' => $prefix);
		}
	}

	/**
	 * @brief returns an array of section prefixes in the system (like BIOL or CS or MATH).
	 * @return Array A structure of the prefixes indexed by first letter.
	 */
	static function getActiveSectionPrefixes() {

		$db = getDB();
		$sql = 'SELECT count(s.sectionID) AS total, prefix from section s, course c  WHERE c.courseID = s.courseID GROUP BY prefix';
		/* next one is commented out because many programs are missing from programPrefix */
		$returnStatement = $db->Execute($sql);
		$sectionPrefixes = array();
		while ($recordObject = $returnStatement->FetchNextObject()) {

			/* we grab the first letter of the prefix and use it to build a hashtable where each prefix is also grouped according to its first letter.
			 * We're going to use this in the template to build a better browse system
			 */
			if (preg_match("/^(\w)/", $recordObject->PREFIX, $matches)) {
				$sectionPrefixes[ $matches[1] ][ $recordObject->PREFIX ] = $recordObject->TOTAL;
			}
		}

		return $sectionPrefixes;
	}

	/**
	 * @brief assembles a list of the Sections that are "active", and part of the given semester (like 2010FA).
	 * @param int $pageOffset the first record of the current page.
	 * @param String $semester the semester to check.
	 * @return Array the relevant Section objects.
	 */
	static function getSectionsWithReserves($pageOffset, $semester) {

		$db = getDB();
		import('items.Section');
		$year = date('Y');
		$term = 'FA';

		if (preg_match('/(\d{4})(\w+)/', $semester, $matches)) {
			$year = $matches[1];
			$term = $matches[2];
		}

		$sectionsWithReserves = array();
		// get a total record count first.
		$sql = 'SELECT DISTINCT s.sectionID from section s, itemHeading i, reservesRecord r WHERE s.year = ? AND s.term = ? AND s.sectionID = i.sectionID AND i.itemHeadingID = r.itemHeadingID ORDER BY s.sectionID, i.itemHeadingID';

		$returnStatement = $db->Execute($sql, array($year, $term));

		$totalRecords = $returnStatement->RecordCount();
		if ($totalRecords > 0) {
			$sql = 'SELECT DISTINCT s.sectionID from section s, itemHeading i, reservesRecord r WHERE s.year = ? AND s.term = ? AND s.sectionID = i.sectionID AND i.itemHeadingID = r.itemHeadingID ORDER BY s.sectionID, i.itemHeadingID LIMIT ?, 25';
			$returnStatement = $db->Execute($sql, array($year, $term, $pageOffset));

			while ($recordObject = $returnStatement->FetchNextObject()) {
				$section = new Section($recordObject->SECTIONID);
				$sectionsWithReserves[] = $section;
			}
		}
		return array($sectionsWithReserves, $totalRecords);
	}

	/**
	 * @brief assembles a search class for a MySQL style full text search.
	 * @param Array $fields the fields to search again.
	 * @param String $keywords the words to search, space delimited, tokenized by this function.
	 * @param ADODBObject $db reference to our DB Object.
	 * @return String the WHERE fragment.
	 */
	static function buildMySQLWhereClause($fields, $keywords, $semesterSQL, &$db, &$prefix, $searchType) {
		if ($searchType != Course::COURSE_SEARCH_RETURN_ONLY_PREFIX) {
			$searchFields = " LOWER(CONCAT_WS(''," . join(', ', array_merge($fields, array('sr.userName', 'sr.firstName', 'sr.lastName'))) . '))';
		} else {
			$searchFields = " LOWER(CONCAT_WS(''," . join(', ', $fields) . '))';
		}

		$keywordArray = preg_split("/\s+/", $keywords, -1, PREG_SPLIT_NO_EMPTY);
		$prefix = self::getCoursePrefix($keywordArray);
		$searchFieldArray = array();
		foreach ($keywordArray as $keyword) {
			$searchFieldArray[] .= $searchFields . ' LIKE '. $db->qstr('%' . $keyword . '%');
		}

		$searchFieldSQL = join(' AND ', $searchFieldArray);
		if ($semesterSQL == '') {
			$returner =  "WHERE " . $searchFieldSQL;
		} else {
			$returner = $semesterSQL . "  AND " . $searchFieldSQL;
		}

		return $returner;
	}

	static function getCoursePrefix($keywords) {
		include('lib/prefixes.php');

		$matchedPrefixes = array();
		foreach ($keywords as $index => $keyword) {
			foreach ($courseNameMap as $courseName => $courseCode) {
				if (preg_match('{' . quotemeta($keyword) . '}i', $courseName)) {
					$matchedPrefixes[$courseCode] = 1;
				}
			}
		}

		if (sizeof($matchedPrefixes) > 0) {
			return array_keys($matchedPrefixes);
		}

		return null;
	}
}
?>
