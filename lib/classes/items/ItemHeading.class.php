<?php

import('items.ElectronicReserveItem');

class ItemHeading extends ElectronicReserveItem {

	/**
	 * @brief a static function for creating a new ItemHeading when handed a sectionID and a headingTitle String.
	 * @param int $sectionID the primary key of the section.
	 * @param String $headingTitle the string of text for this heading title.
	 * @return int the newly created record ID, or the id of that heading if it already exists, or 0 on failure.
	 */
	static function create($sectionID, $headingTitle) {
		$db = getDB();
		$sectionID = (int) $sectionID;
		if ($sectionID > 0) {

			/* first test to make sure this headingTitle does not already exist*/

			$sql = "SELECT itemHeadingID FROM itemHeading WHERE sectionID = ? AND headingTitle = ?";
			$returnStatement = $db->Execute($sql, array($sectionID, $headingTitle));
			if ($returnStatement->RecordCount() == 1) {
				$recordRow = $returnStatement->FetchNextObject();
				return $recordRow->ITEMHEADINGID;
			}

			/* we should get the max sequence value first, so this new section is after it in browse lists */
			$sql = "SELECT max(sequence) as maxsequence FROM itemHeading WHERE sectionID = ?";
			$returnStatement = $db->Execute($sql, array($sectionID));
			$sequence = 0;
			if ($returnStatement->RecordCount() == 1) {
				$recordRow = $returnStatement->FetchNextObject();
				$sequence = (int) $recordRow->MAXSEQUENCE;
				$sequence ++;
			} else { // zero records.  First heading for this Section?
				$sequence = 1;
			}
			$sql = "INSERT INTO itemHeading (headingTitle, sectionID, sequence, itemHeadingID) VALUES (?, ?, ?, '0')";
			$returnStatement = $db->Execute($sql, array($headingTitle, $sectionID, $sequence));
			if ($returnStatement) {
				return $db->Insert_ID();
			} else {
				return 0; // this shouldn't happen, if the insert succeeded.
			}
		}
	}

	public function __construct($itemHeadingID = 0) {

		if ($itemHeadingID > 0) {
			$db = getDB();
			$sql = "SELECT itemHeadingID, headingTitle, sectionID, sequence FROM itemHeading WHERE itemHeadingID = ?";
			$returnStatement = $db->Execute($sql, array($itemHeadingID));
			if ($returnStatement->RecordCount() ==  1) {
				$recordRow = $returnStatement->GetRowAssoc(FALSE);
				foreach ($recordRow AS $key => $value) {
					$this->setAttribute($key, $value);
				}
				return true;
			} else {
				return false;
			}
		} else { // loading a blank Item Heading, probably in order to create a new one.

			$this->setAttribute('itemheadingid', '0');
			return true;
		}
	}

	/**
	 * @brief convenience function for getting the ItemHeading ID.
	 * @return Int the itemHeading id.
	 */
	function getItemHeadingID() {
		$returner = $this->getAttribute('itemheadingid');
		return $returner;
	}

	/**
	 * @brief convenience function for getting the ItemHeading title.
	 * @return Int the headingtitle.
	 */
	function getHeadingName() {
		$returner = $this->getAttribute('headingtitle');
		return $returner;
	}

	/**
	 * @brief convenience method for setting the section ID.
	 * @param $sectionID the section id for this heading.
	 */
	function setSectionID($sectionID) {
		$this->setAttribute('sectionid', intval($sectionID));
	}

	/**
	 * @brief convenience method for getting the section ID.
	 * return $sectionID the section id for this heading.
	 */
	function getSectionID() {
		$returner = $this->getAttribute('sectionid');
		return $returner;
	}

	/**
	 * @brief returns a list of the ReservesRecord objects assigned to this ItemHeading.
	 * @return Array ItemHeading objects.
	 */
	function getListedReserves() {
		$db = getDB();
		$sql = "SELECT r.reservesRecordID FROM reservesRecord r WHERE itemHeadingID = ?";
		$returnStatement = $db->Execute($sql, array($this->getItemHeadingID()));
		$listedReserves = array();
		if ($returnStatement && $returnStatement->RecordCount() > 0) {
			import('items.ReservesRecord');
			while ($reservesObject = $returnStatement->FetchNextObject()) {
				$listedReserves[] = new ReservesRecord($reservesObject->RESERVESRECORDID);
			}
		}

		return $listedReserves;
	}

	/**
	 * @brief updates or creates a ItemHeading record in the database by pulling out the bits passed in when the form was submitted.
	 * @return boolean true or false, if the update succeeded or not.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$sectionid = intval(ReservesRequest::getRequestValue('sectionid'));
		$headingtitle = ReservesRequest::getRequestValue('headingtitle');
		$itemheadingid = $this->getItemHeadingID();

		if ($itemheadingid > 0 && $sectionid > 0) {
			$sql = "UPDATE itemHeading SET headingTitle = ?, sectionID = ? WHERE itemHeadingID = ?";
		} else {
			$sql = "INSERT INTO itemHeading (headingTitle, sectionID, sequence, itemHeadingID) VALUES (?, ?, '1', ?)";
		}
		$returnStatement = $db->Execute($sql, array($headingtitle, $sectionid, $itemheadingid));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief a convenience function to just toggle a heading title when we use our AJAX method.
	 * @return boolean true or false, if the update succeeded or not.
	 */
	function updateTitle() {
		$db = getDB();
		import('general.ReservesRequest');
		$headingtitle = ReservesRequest::getRequestValue('headingtitle');
		if ($headingtitle != '') {
			$sql = "UPDATE itemHeading SET headingTitle = ? WHERE itemHeadingID = ?";
			$returnStatement = $db->Execute($sql, array($headingtitle, $this->getItemHeadingID()));
			if ($returnStatement) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * @brief function which assembles a Form object representing this item heading, so it can be edited by a course admin.
	 * @return Form the form object.
	 */
	function assembleEditForm($basePath) {

		import('forms.Form');
		$action = 'itemHeadings';

		$form = new Form(array('id' => $action, 'method' => 'post', 'action' => $basePath . '/index.php/' . $action . '/' . $this->getAttribute('sectionid') . '/' . $this->getItemHeadingID()));
		$label = $this->getItemHeadingID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . '  Item Heading'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'sectionid', 'value' => intval($this->getAttribute('sectionid')) ) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'itemheadingid', 'value' => $this->getItemHeadingID() ) ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Item Heading Title', 'secondaryLabel' => 'plain-text title', 'name' => 'headingtitle',
							'value' => $this->getAttribute('headingtitle'), 'requiredMsg' => 'Please enter a title') ));

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
