<?php

import('items.ReserveItem');

define ('RESERVE_RECORD_ITEM_SINGLE_CREATE_SUCCESS', '1');
define ('RESERVE_RECORD_ITEM_SINGLE_UPDATE_SUCCESS', '2');
define ('RESERVE_RECORD_ITEM_BULK_CREATE_SUCCESS', '3');
define ('RESERVE_RECORD_ITEM_BULK_UPDATE_SUCCESS', '4'); // this is not used at all yet

class ReservesRecord extends ReserveItem {

	public function __construct($reservesRecordID = 0) {

		if ($reservesRecordID > 0) {
			$db = getDB();

			$sql = "SELECT r.reservesRecordID, r.reservesRecordTitle, r.details, r.linkID, i.sectionID, r.itemHeadingID, i.headingTitle FROM reservesRecord r, itemHeading i
					WHERE r.reservesRecordID = ? AND r.itemHeadingID = i.itemHeadingID";

			$returnStatement = $db->Execute($sql, array($reservesRecordID));
			if ($returnStatement->RecordCount() ==  1) {
				$recordRow = $returnStatement->GetRowAssoc(FALSE);
				foreach ($recordRow AS $key => $value) {
					$this->setAttribute($key, $value);
				}
				return true;
			} else {
				return false;
			}
		} else { // loading a blank ReservesRecord, in order to create a new one.

			$this->setAttribute('reservesrecordid', '0');
			$this->setAttribute('itemheadingid', '0');
			$this->setAttribute('linkid', '0');
			return true;
		}
	}

	/**
	 * @brief private accessor method for setting the Physical Items assigned to this reserve.
	 * @param $items an array of PhysicalReserveItems.
	 */
	private function _setPhysicalItems($items) {
		$this->_physicalItems = $items;
	}

	/**
	 * @brief private accessor method for setting the Electronic Items assigned to this reserve.
	 * @param $items an array of ElectronicReserveItems.
	 */
	private function _setElectronicItems($items) {
		$this->_electronicItems = $items;
	}

	/**
	 * @brief updates the database field for the ItemHeading assigned to this ReservesRecord.  Will create a new entry if one does not
	 * yet exist.
	 * @param ADODBOject $db our database connection.
	 * @param int $itemHeadingID the id of theItemHeading to be assigned.
	 * @param int $reservesRecordID the primary key of this ReservesRecord.
	 */
	private function _updateItemHeadingForReserve(&$db, $itemHeadingID, $reservesRecordID) {

		$sql = 'UPDATE reservesRecord SET itemHeadingID = ? WHERE reservesRecordID = ?';
		$db->Execute($sql, array(intval($itemHeadingID), $reservesRecordID));
	}

	/**
	 * @brief used to temporarily place the record IDs of bulk records in _SESSION so they can be referenced on other pages.
	 * @param Array $ids the ReserveRecord record IDs.
	 */
	private function _storeBulkRecordIDs($ids) {
		$_SESSION['bulkRecordIDs'] = $ids;
	}

	/**
	 * @brief static method called by *ReserveItem classes to include the waiver option on creation forms.
	 * @return a Radio form field item with four buttons.
	 */
	static function getUsageRightsRadio($usageRights) {

		import('forms.Radio');

		$radio = new Radio( array('name' => 'usagerights', 'value' => $usageRights, 'validationDep' => 'function(element) {if ($("#fileChoice:checked").val() == "filechoicelocal") return true; return false; }', 'primaryLabel' => '<a target="_blank" href="http://lib.unb.ca/copyright/">Copyright Statement</a>', 'secondaryLabel' => 'To the best of my knowledge:', 'requiredMsg' => 'Please choose a statement') );
		$radio->addButton( array('id' => 'creator', 'value'=> 'creator', 'caption' => 'The instructor is the creator of this material') );
		$radio->addButton( array('id' => 'no_infringe', 'value' => 'no_infringe', 'caption' => 'These materials do not infringe on copyright') );
		$radio->addButton( array('id' => 'cleared', 'value' => 'cleared', 'caption' => 'The materials copied here have been cleared of copyright from the rights holder') );
		$radio->addButton( array('id' => 'pending', 'value' => 'pending', 'caption' => 'The materials copied are pending copyright clearance from the rights holder') );
		$radio->addButton( array('id' => 'not_within', 'value' => 'not_within', 'caption' => 'These materials require copyright clearance review.') );

		return $radio;
	}

	/**
	 * @brief returns a list of ReserveItems //FIXME
	 * @param $reservesUser the user currently requesting this list.
	 * @param $sectionID the section ID of the Section we are interested in.  Required to determine whether or not.
	 * the viewing person has admin abilities, in order to return shadowed records or not.
	 * @return Array the PhysicalReserveItems items.
	 */
	public function getPhysicalItems($reservesUser = null, $onlyIDs = FALSE) {

		$db = getDB();

		if ($reservesUser && ($reservesUser->isAdmin() || $reservesUser->canAdministerSection($this->getSectionID()))) {
			$sql = 'SELECT physicalItemID FROM physicalItem WHERE reservesRecordID = ?';
		} else {
			$sql = 'SELECT physicalItemID FROM physicalItem WHERE reservesRecordID = ? AND shadow = "0"';
		}

		$returnStatement = $db->Execute($sql, array($this->getReservesRecordID()));
		if ($returnStatement) {
			$physicalItems = array();
			import('items.PhysicalReserveItem');
			while ($recordObject = $returnStatement->FetchNextObject()) {
				if (!$onlyIDs) {
					$physicalItems[] = new PhysicalReserveItem($recordObject->PHYSICALITEMID);
				} else {
					$physicalItems[] = $recordObject->PHYSICALITEMID;
				}
			}
		}
		return $physicalItems;
	}

	/**
	 * @brief returns an array of PhysicalReserveItem objects that have been assigned to this ReservesRecord.
	 * @return Array the PhysicalReserveItems in question.
	 */
	public function getAllPhysicalItems() {

		$db = getDB();
		$sql = 'SELECT physicalItemID FROM physicalItem WHERE reservesRecordID = ?';

		$returnStatement = $db->Execute($sql, array($this->getReservesRecordID()));
		if ($returnStatement) {
			$physicalItems = array();
			import('items.PhysicalReserveItem');
			while ($recordObject = $returnStatement->FetchNextObject()) {
				$physicalItems[ $recordObject->PHYSICALITEMID ] = new PhysicalReserveItem($recordObject->PHYSICALITEMID);
			}
		}
		return $physicalItems;
	}

	/**
	 * @brief returns a list of ElectronicItems asigned to this ReservesRecord //FIXME
	 * @return Array the collection of ElectronicReserveItems items.
	 */
	public function getElectronicItems($reservesUser = null, $onlyIDs = false) {
		$db = getDB();
		import('items.ElectronicReserveItem');

		$paramArray = array($this->getReservesRecordID());
		if ($reservesUser->isAdmin())  {
			$sql = 'SELECT electronicItemID FROM electronicItem WHERE reservesRecordID = ?';
		} else {
			$sql = 'SELECT electronicItemID FROM electronicItem WHERE reservesRecordID = ? AND (usageRights = "cleared" 
				OR usageRights="creator" OR usageRights = "no_infringe" OR usageRights = "")';
		}

		$returnStatement = $db->Execute($sql, $paramArray);

		if ($returnStatement) {
			$electronicItems = array();
			import('items.ElectronicReserveItem');
			while ($recordObject = $returnStatement->FetchNextObject()) {
				if (!$onlyIDs) {
					$electronicItems[] = new ElectronicReserveItem($recordObject->ELECTRONICITEMID);
				} else {
					$electronicItems[] = $recordObject->ELECTRONICITEMID;
				}
			}
		}
		return $electronicItems;
	}

	/**
	 * @brief returns the total number of Items assigned to this ReservesRecord.  We pass in the User since we hide shadowed records from non-admins.
	 * @param ReservesUser $reservesUser the user using the site.
	 * @return int the total items (that the user can see).
	 */
	function getTotalNumberOfItems($reservesUser) {

		$physicalCount = sizeof($this->getPhysicalItems($reservesUser, true));
		$electronicItems = $this->getElectronicItems($reservesUser);
		$count = 0;
		foreach ($electronicItems as $item) { 
			if ($reservesUser->isAdmin() || $item->isShadowed() == 2) { $count ++; }
		}
		$electronicCount = $count;

		$total = $physicalCount + $electronicCount;
		return $total;
	}

	/**
	 * @brief convenience method for building a Array of the properties for the 'only' reserves item, if a record just has one.
	 * @param ReservesUser $reservesUser the current user (since we may deny access to users who are not enrolled in sections).
	 * @param String $basePath the path to our reserves instance, normally /reserves.
	 * @return Array the item properties, passed into our template.
	 */
	function getSingleItem($reservesUser, $basePath) {

		$physicalItems = $this->getPhysicalItems($reservesUser, true);
		$electronicItems = $this->getElectronicItems($reservesUser, true);
		$item = null;

		if (sizeof($physicalItems) == 1) {
			import('items.PhysicalReserveItem');
			$item = new PhysicalReserveItem($physicalItems[0]);
			return array('loginRequired' => false, 'type' => 'p', 'id' => $item->getPhysicalItemID(), 'class' => 'opacLink', 'title' => $this->getTitle(), 'display' => '', 'info' => 'click for record info', 'url' => $item->getURL());
		} else {
			import('items.ElectronicReserveItem');
			$item = new ElectronicReserveItem($electronicItems[0]);

			$loginRequired = true;
			$info = '<img height="15" src="' . $basePath . '/images/mimeIcons/' . $item->mapTypeToImg() . '.png" alt="MIME type" />';

			if ($reservesUser->isAdmin() || $item->isOpenAccess() || $item->isRemoteFile() || ( $reservesUser->isLoggedIn() && !$item->requiresEnrolment()) ||
				$this->getSection()->userIsEnrolled($reservesUser->getUserName()) ) { $loginRequired = false; }
			return array('loginRequired' => $loginRequired, 'type' => 'e', 'id' => $item->getElectronicItemID(), 'title' => $item->getTitle(), 'display' => 'Available Online', 'info' => $info, 'url' => $item->getURL(), 'notes' => $item->getNotes(), 'shadowed' => $item->isShadowed());
		}
	}

	/**
	 * @brief used to set the ItemHeadings assigned to the course that the user is trying to
	 * add this ReservesRecord to.
	 * @param Array $itemHeadings an array of ItemHeadings to add.
	 */
	public function setPossibleItemHeadings($itemHeadings) {
		$this->_itemHeadings = $itemHeadings;
	}

	/**
	 * @brief pulled by the assembleEditForm() function when creating a dropdown list of
	 * possible ItemHeadings to use for this ReservesRecord.
	 * @return Array an array of ItemHeading objects.
	 */
	public function getPossibleItemHeadings() {
		$returner = $this->_itemHeadings;
		return $returner;
	}

	/**
	 * @brief convenience method to store the Section ID for the section(s) that this reserve belongs to.
	 * @param Array $sectionIDs the IDs that this reserve should be in.
	 */
	public function setSectionIDs($sectionIDs = array()) {
		$this->setAttribute('sectionids', $sectionIDs);
	}

	/**
	 * @brief convenience method to store the ItemHeading ID for the heading that this reserve belongs to.
	 * @param int $itemHeadingID.
	 */
	public function setItemHeadingID($itemHeadingID) {
		$this->setAttribute('itemheadingid', $itemHeadingID);
	}

	/**
	 * @brief returns the Section ID of the section for this ReservesRecord.
	 * @return int the sectionID.
	 */
	public function getSectionID() {
		$returner = $this->getAttribute('sectionid');
		return $returner;
	}

	/**
	 * @brief returns the ItemHeading ID of the heading for this ReservesRecord.
	 * @return int the itemHeadingID.
	 */
	public function getItemHeadingID() {
		$returner = $this->getAttribute('itemheadingid');
		return $returner;
	}

	/**
	 * @brief convenience method for getting the section for this ReservesRecord.
	 * @return Section the section for this ReservesRecord.
	 */
	public function getSection() {
		import('items.Section');
		$section = new Section($this->getSectionID());
		return $section;
	}

	/**
	 * @brief convenience method for getting the title for this ReservesRecord.
	 * @return String the record title.
	 */
	public function getTitle() {
		$returner = $this->getAttribute('reservesrecordtitle');
		return $returner;
	}

	/**
	 * @brief convenience method for getting the heading title for this ReservesRecord.
	 * @return String the heading title.
	 */
	public function getHeadingTitle() {
		$returner = $this->getAttribute('headingtitle');
		return $returner;
	}

	/**
	 * @brief convenience method for getting the linkID for this ReservesRecord.
	 * @return int the ID, if this record is linked to other ones.
	 */
	public function getLinkID() {
		$returner = $this->getAttribute('linkid');
		return $returner;
	}

	/**
	 * @brief is it linked? return true or false.
	 * @return boolean.
	 */
	public function isLinked() {
		if ($this->getLinkedID() > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief convenience method for getting the details assigned to this ReservesRecord.
	 * @return String the details.
	 */
	public function getDetails() {
		$returner = $this->getAttribute('details');
		return $returner;
	}

	/**
	 * @brief updates or creates a ReservesRecord record in the database by pulling out the bits passed in when the form was submitted.
	 * @return boolean true or false, if the updated succeeded or not.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$details = ReservesRequest::getRequestValue('details');
		$reservesrecordtitle = ReservesRequest::getRequestValue('reservesrecordtitle');
		$reservesrecordid = ReservesRequest::getRequestValue('reservesrecordid');
		$itemheadingids = ReservesRequest::getRequestValue('itemheadingid');
		$linkid = ReservesRequest::getRequestValue('linkid');
		$updatedlinked = ReservesRequest::getRequestValue('updatelinked') != '' ? true : false;
		$keeplinked = ReservesRequest::getRequestValue('keeplinked') != '' ? true : false;

		$returnStatement = false;

		if ($updatedlinked && $keeplinked) {
			$sql = 'UPDATE reservesRecord SET reservesRecordTitle = ?, details = ? WHERE linkID = ?';
			$returnStatement = $db->Execute($sql, array($reservesrecordtitle, $details, $linkid));

		}

		if (!$keeplinked) {
			$sql = 'UPDATE reservesRecord SET linkID = ? WHERE reservesRecordID = ?';
			$returnStatement = $db->Execute($sql, array('0', $reservesrecordid));
		}

		$newRecordIDs = array();
		$linkid = 0;
		foreach ($itemheadingids as $itemheadingid) {
			/* ReservesRecord updates are special - the SQL is slightly difference since we don't want to break the linked record IDs, so we leave that column alone in an update */
			if ($reservesrecordid > 0) {
				$sql = "UPDATE reservesRecord SET reservesRecordTitle = ?, details = ?, itemHeadingID = ? WHERE reservesRecordID = ?";
			} else {
				$sql = "INSERT INTO reservesRecord (reservesRecordTitle, details, itemHeadingID, reservesRecordID) VALUES (?, ?, ?, ?)";
			}

			$returnStatement = $db->Execute($sql, array($reservesrecordtitle, $details, $itemheadingid, $reservesrecordid));

			if ($reservesrecordid == 0) {
				$newRecordIDs[] = $db->Insert_ID();
			}
			if ($linkid == 0) {
				$linkid = $db->Insert_ID();
				$this->setAttribute('reservesrecordid', $linkid);
			}
		}

		if (sizeof($newRecordIDs) > 1) {
			$sql = 'UPDATE reservesRecord SET linkID = ? WHERE reservesRecordID IN (' . join(',', $newRecordIDs) . ')';
			$db->Execute($sql, array($linkid));
		}

		if ($returnStatement) {
			if ($reservesrecordid == 0) { // creating new records
				if (sizeof($itemheadingids) == 1)
					return RESERVE_RECORD_ITEM_SINGLE_CREATE_SUCCESS;
				else {
					$this->_storeBulkRecordIDs($newRecordIDs);
					return RESERVE_RECORD_ITEM_BULK_CREATE_SUCCESS;
				}
			} else { // updating records
				if (sizeof($itemheadingids) == 1)
					return RESERVE_RECORD_ITEM_SINGLE_UPDATE_SUCCESS;
				else
				return RESERVE_RECORD_ITEM_BULK_UPDATE_SUCCESS; // never actually used yet
			}
		}
	}

	/**
	 * @brief deletes a ReservesRecord and all items in it.
	 * @return boolean true or false, if it succeeded.
	 */
	public function delete($reservesUser) {

		foreach ($this->getAllPhysicalItems() as $item) {
			$item->delete();
		}

		foreach ($this->getElectronicItems($reservesUser) as $item) {
			$item->delete();
		}

		$db = getDB();
		$sql = 'DELETE FROM reservesRecord WHERE reservesRecordID = ?';
		$returnStatement = $db->Execute($sql, array($this->getReservesRecordID()));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief function which assembles a Form object representing this Reserves Record, so it can be edited by a course admin.
	 * @return Form the form object.
	 */
	function assembleEditForm($basePath) {

		import('general.Config');
		$config = new Config();
		import('forms.Form');
		$form = new Form(array('id' => 'adminReserve', 'method' => 'post', 'action' => $basePath . '/index.php/adminCourseReserves/' . $this->getAttribute('itemheadingid') . '/' . $this->getReservesRecordID()));
		$label = $this->getReservesRecordID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . ' Reserves Record'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'reservesrecordid', 'value' => $this->getReservesRecordID()) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'linkid', 'value' => $this->getLinkID()) ));

		if ($this->getLinkID() > 0) {
			$fieldSet->addField(new HTMLBlock(array('content' => '<strong style="color: red">This Reserves Record is linked to others. You can push these changes to the other records.</strong>')));
			$fieldSet->addField(new Checkbox( array('name' => 'updatelinked', 'primaryLabel' => 'Update linked Records?', 'secondaryLabel' => '' ,'value' => true) ) );
			$fieldSet->addField(new Checkbox( array('name' => 'keeplinked', 'primaryLabel' => 'Maintain link to others?', 'secondaryLabel' => '' ,'value' => true) ) );
		}
		$fieldSet->addField(new TextField( array('primaryLabel' => 'Reserves Record Title', 'secondaryLabel' => 'Not required.  Default is title of first item added.', 'name' => 'reservesrecordtitle',
							'value' => $this->getAttribute('reservesrecordtitle'))));

		$fieldSet->addField(new TextArea( array('primaryLabel' => 'Reserves Record Details', 'secondaryLabel' => 'free-form details and notes', 'name' => 'details',
							'value' => $this->getAttribute('details') )));

		$possibleItemHeadings = $this->getPossibleItemHeadings();
		$selectSize = sizeof($possibleItemHeadings);

		// we only allow multiples on the creation of new records
		$multiple = $this->getReservesRecordID() > 0 ? false : true;
		$secondaryLabel = ($multiple && $selectSize > 1)? '<a href="#" onClick="selectAll()">all of them</a>' : 'one';
		$select = new Select( array('name' => 'itemheadingid[]', 'primaryLabel' => 'Item Heading', 'secondaryLabel' => 'Choose ' . $secondaryLabel, 'required' => true, 'multiple' => $multiple,
				'requiredMsg' => 'Please choose a heading for this reserve', 'value' => $this->getAttribute('itemheadingid'), 'size' => $selectSize) );

		import('items.Section');
		foreach ($possibleItemHeadings as $itemHeading) {
			$label = $itemHeading->getHeadingName();
			$section = new Section($itemHeading->getSectionID());
			$label .= ' (' . $section->getCalendarCourseCode() . ')';
			$select->addOption( array('value' => $itemHeading->getItemHeadingID(), 'label' => $label) );
		}
		$fieldSet->addField($select);

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
