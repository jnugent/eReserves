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

			$courseCode = '';
			$courseName = '';

			foreach ($lines as $line) {
				$lineFragments = preg_split("/\t/", $line, -1, PREG_SPLIT_NO_EMPTY);
				if (sizeof($lineFragments) > 1) { // this is a new Course description

					if ($courseCode != '' && $courseName != '') {

						if (preg_match("/^([^*]+)\*(.+)$/", $courseCode, $matches)) {
							$prefix = $matches[1];
							$courseNumber = $matches[2];
						}

						$sql = "SELECT courseID from course WHERE prefix = ? AND courseNumber = ?";
						$returnStatement = $db->Execute($sql, array($prefix, $courseNumber));

						if ($returnStatement->RecordCount() ==  0) { // a test, since the courses are duplicated if offered at both campuses.  We're only interested in storing one.
							$sql = "INSERT INTO course (courseID, courseName, prefix, courseNumber, visible) VALUES (0, ?, ?, ?, '1')";
							$statement = $db->Execute($sql,  array($courseName, $prefix, $courseNumber));
						}
					}
					$courseCode = $lineFragments[0];
					$courseName = $lineFragments[1];

				} else { // This line is a Course name fragment

					if (preg_match("/\s*\-\s*$/", $courseName)) {
						$courseName = preg_replace("/\s*\-\s*$/", "", $courseName);
						$trailing_dash = true;
					} else {
						$courseName .= " ";
						$trailing_dash = false;
					}

					$nameFragment = $trailing_dash ? strtolower($lineFragments[0]) : $lineFragments[0];
					$courseName .= $nameFragment;
				}
			}
		} else {
			print "$fileName does not exist or is not readable.\n";
		}
	} else {
		print "Usage: php importCourses.php -f <yourCourseDataFile>\n";
	}
?>