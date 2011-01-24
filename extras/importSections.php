<?php

$commandOpts = 'f:';
$scriptArguments = getopt($commandOpts);
require_once("../lib/functions.php");

if ($scriptArguments['f'] != '') {

	$fileName = $scriptArguments['f'];
	if (file_exists($fileName)) {

		$db = getDB();

		$contents = file_get_contents($fileName);
		$lines = preg_split("/\n/", $contents, -1, PREG_SPLIT_NO_EMPTY);

		/* tokenize the section string -- here are the bits we are interested in. With examples. */

		foreach ($lines as $line) {
			if (preg_match("/^\d{4}/", $line)) { // we're only interested in lines that begin with a year (like 2010).
				$lineFragments = preg_split("/\t/", $line, -1, PREG_SPLIT_NO_EMPTY);
				$sectionInfo = array();
				list ($sectionInfo['sectionYear'], $sectionInfo['sectionTerm']) = preg_split("|/|", $lineFragments[0]); // split 2010/FA into the two bits we want
				list ($sectionInfo['sectionPrefix'], $sectionInfo['sectionCourseNumber'], $sectionInfo['sectionNumber']) = preg_split("/\*/", $lineFragments[1]); // ditto for ANTH*1000*FR01A

				$courseID = 0;

				$sql = "SELECT courseID from course WHERE prefix = ? AND courseNumber = ?";
				$returnStatement = $db->Execute($sql, array($sectionInfo['sectionPrefix'], $sectionInfo['sectionCourseNumber']));
				if ($returnStatement->RecordCount() ==  1) {

					$recordRow = $returnStatement->FetchNextObject();
					$courseID = $recordRow->COURSEID;
				} else { // the course doesn't exist, so we should create that record, and then get the ID, and create the section again.

					$sql = "INSERT INTO course (courseID, courseName, prefix, courseNumber, visible) VALUES (0, ?, ?, ?, '1')";
					$sqlParams = array($lineFragments[2], $sectionInfo['sectionPrefix'], $sectionInfo['sectionCourseNumber']);
					$statement = $db->Execute($sql,  $sqlParams);

					$sql = "SELECT courseID from course WHERE prefix = ? AND courseNumber = ?";
					$returnStatement = $db->Execute($sql, array($sectionInfo['sectionPrefix'], $sectionInfo['sectionCourseNumber']));

					if ($returnStatement->RecordCount() == 1) {
						$recordRow = $returnStatement->FetchNextObject();
						$courseID = $recordRow->COURSEID;

					} else {
						print "Error: This seems to be a problem.  ";
						print "The record didn't exist, we tried to create it, and still nothing (" . $sectionInfo['sectionPrefix'] . ', ' . $sectionInfo['sectionCourseNumber'] . ")\n";
					}
				}

				if ($courseID > 0) { // we have found a course in the previous bits

					// check to see if this Section already exists.  If not, create it.
					$sql = "SELECT sectionID FROM section WHERE year = ? and term = ? and sectionNumber = ? and courseID = ?";
					$returnStatement = $db->Execute($sql, array($sectionInfo['sectionYear'], $sectionInfo['sectionTerm'], $sectionInfo['sectionNumber'], $courseID));
					if ($returnStatement->RecordCount() == 0) { // does not exist
						$sql = "INSERT INTO section (sectionID, year, term, sectionNumber, courseID, visible) VALUES (0, ?, ?, ?, ?, '1')";
						$returnStatement = $db->Execute($sql, array($sectionInfo['sectionYear'], $sectionInfo['sectionTerm'], $sectionInfo['sectionNumber'], $courseID));
					}
				}
			}
		}

	} else {
		print "$fileName does not exist or is not readable.\n";
	}
} else {
	print "Usage: php importSections.php -f <yourSectionDataFile>\n";
}
?>