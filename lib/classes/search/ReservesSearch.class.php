<?php

class ReservesSearch {

	public function __construct() {
		return true;
	}

	/**
	 * @brief a prototype function for generating a list of Courses based on a keyword search in the title.
	 * // FIXME make this search more useful.
	 * @param $keywords the string to look for
	 * @return array an array of Course objects that matched, or an empty array if none.
	 */
	static function searchCourses(&$keywords, $offset = 0) {

		$db = getDB();
		import('items.Course');
		import('general.ReservesRequest');
		$only_prefix = false;
		if (preg_match("/browseCourses$/", ReservesRequest::getReferringPage())) {
			$only_prefix = true;
		}
		$fields = Course::getSearchFields($only_prefix);

		$whereClause = self::buildMySQLWhereClause($fields, $keywords, $db);
		$limitClause = " LIMIT $offset, 25";

		/* first query to get the total number of records */
		$sql = 'SELECT count(c.courseID) AS total FROM course c ' . $whereClause;
		$returnStatement = $db->Execute($sql);
		$recordObject = $returnStatement->FetchNextObject();
		$totalRecords = $recordObject->TOTAL;

		if ($totalRecords > 0) { // there's no point in doing the real query again, if we have zero records that matched the full one
			$sql = 'SELECT c.courseID FROM course c ' . $whereClause . $limitClause;
			$returnStatement = $db->Execute($sql);
			if ($returnStatement->RecordCount() > 0) {
				import('items.Course');
				$matchedCourses = array();
				while ($recordObject = $returnStatement->FetchNextObject()) {
					$matchedCourses[] = new Course($recordObject->COURSEID);
				}
				return array ('0' => $matchedCourses, '1' => $totalRecords);
			} else {
				return array();
			}
		} else {
			return array();
		}
	}

	/**
	 * @brief returns an array of section prefixes in the system (like BIOL or CS or MATH)
	 * @return Array
	 */
	static function getActiveSectionPrefixes() {

		$db = getDB();
		$sql = 'SELECT count(s.sectionID) AS total, prefix from section s, course c  WHERE c.courseID = s.courseID GROUP BY prefix';
		/* next one is commented out because many programs are missing from programPrefix */
 //	   $sql = 'SELECT count(s.sectionID) AS total , prefix, programName from section s, course c, programPrefix p  WHERE c.courseID = s.courseID AND p.programPrefix = c.prefix  GROUP BY prefix';
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
	 * @param int $pageOffset the first record of the current page
	 * @param String $semester the semester to check.
	 * @return an array of Section objects
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

		$sql = 'SELECT DISTINCT s.sectionID from section s, itemHeading i, reservesRecord r WHERE s.year = ? AND s.term = ? AND s.sectionID = i.sectionID AND i.itemHeadingID = r.itemHeadingID ORDER BY s.sectionID, i.itemHeadingID';
		$returnStatement = $db->Execute($sql, array($year, $term));
		$sectionsWithReserves = array();
		while ($recordObject = $returnStatement->FetchNextObject()) {
			$section = new Section($recordObject->SECTIONID);
			$sectionsWithReserves[] = $section;
		}

		return $sectionsWithReserves;
	}

	/**
	 * @brief assembles a search class for a MySQL style full text search
	 * @param $fields
	 * @param $keywords
	 * @param $db
	 * @return String the WHERE fragment
	 */
	static function buildMySQLWhereClause($fields, $keywords, &$db) {

		$returner =  "WHERE MATCH (" . join(',', $fields) . ") AGAINST (" . $db->qstr($keywords) . " IN BOOLEAN MODE)";
		return $returner;
	}
}
?>