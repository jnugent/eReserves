<?php

class ReserveItem {

	/**
	 * Abstract constructor.  Subclassed by ElectronicReserveItem and PhysicalReserveItem.
	 */
	function __construct() {
		assert(false);
	}

	/**
	 * @brief fetches an attribute from the attributes array created when an item is instantiated.
	 * @param String $attribute the attribute to fetch.
	 * @return Mixed the attribute value.
	 */
	 function getAttribute($attribute) {
		if (array_key_exists($attribute, $this->_properties)) {
			return $this->_properties[$attribute];
		} else {
			return '';
		}
	}

	/**
	 * @brief sets an attribute of an item.  Probably when an item is instantiated in the __construct() call.
	 * @param $attributeName the attribute to set.
	 * @param $attributeValue the new value.
	 */
	 function setAttribute($attributeName, $attributeValue) {
		$this->_properties[$attributeName] = $attributeValue;
	}

	/**
	 * @brief returns the title of this record.
	 * @return String the title.
	 */
	function getTitle() {

		$returner = $this->getAttribute('itemtitle');
		return $returner;
	}

	/**
	 * @brief returns the timestamp corrsponding to the date the reserves item was created (or modified).
	 * @return Int the unix time stamp.
	 */
	function getDateTimestamp() {
		$returner = strtotime($this->getAttribute('dateadded'));
		return $returner;
	}

	/**
	 * @brief must be overridden in subclasses
	 */
	function delete() {
		return false;
	}

	/**
	 * @brief returns the id of this record.
	 * @return int the ID.
	 */
	function getReservesRecordID() {
		$returner = $this->getAttribute('reservesrecordid');
		return $returner;
	}

	/**
	 * @brief extracts the bulkRecordIDs array from the session superglobal, which is populated
	 * when an administrator is attempting to add a reserve record to more than one Section at once.
	 * @return Array $bulkRecordIDs an array of ints for each ReservesRecord.
	 */
	function getBulkRecordIDs() {
		$bulkRecordIDs = $_SESSION['bulkRecordIDs'];
		return $bulkRecordIDs;
	}

	/**
	 * @brief garbage collection function useful when clearing _SESSION information for bulk addition
	 * @return void
	 */
	function destroyBulkRecordIDs() {
		$_SESSION['bulkRecordIDs'] = '';
	}

	/**
	 * @brief returns the section this record is assigned to
	 * @return int the Section ID
	 */
	function getSectionID() {
		import ('items.ReservesRecord');
		$reservesRecord = new ReservesRecord($this->getReservesRecordID());
		return $reservesRecord->getSectionID();
	}
}
?>