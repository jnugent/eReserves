<?php
import ('items.ReserveItem');

class PhysicalReserveItem extends ReserveItem {

	const PHYSICAL_RESERVE_ITEM_CREATE	= 1;
	const PHYSICAL_RESERVE_ITEM_EDIT	= 2;
	const PHYSICAL_RESERVE_ITEM_DELETE	= 3;
	const PHYSICAL_RESERVE_ITEM_QUERY	= 4;

	var $_properties = array();

	function __construct($physicalItemID = 0) {


		if ($physicalItemID > 0) {
			$db = getDB();
			$sql = "SELECT p.physicalItemID, p.callNumber, p.barCode, p.reservesRecordID, p.citation, p.location, p.loanPeriod, p.shadow, p.usageRights FROM physicalItem p WHERE p.physicalItemID = ?";
			$returnStatement = $db->Execute($sql, array($physicalItemID));
			if ($returnStatement->RecordCount() ==  1) {
				$recordRow = $returnStatement->GetRowAssoc(FALSE);
				foreach ($recordRow AS $key => $value) {
					$this->setAttribute($key, $value);
				}
				return true;
			} else {
				return false;
			}
		} else { // loading a blank Item, in order to create a new one.

			$this->setAttribute('physicalitemid', '0');
			return true;
		}
}

	/**
	 * @brief fetches an attribute from the attributes array created when an item is instantiated.
	 * @param String $attribute the attribute
	 * @return Mixed the attribute value
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
	 * @param $attributeName
	 * @param $attributeValue
	 */
	function setAttribute($attributeName, $attributeValue) {
		$this->_properties[$attributeName] = $attributeValue;
	}

	/**
	 * @brief fetches the primary key for this record
	 * @return int the ID
	 */
	function getPhysicalItemID() {
		$returner = $this->getAttribute('physicalitemid');
		return $returner;
	}

	/**
	 * @brief fetches the call number for this record
	 * @return String the call number
	 */
	function getCallNumber() {
		$returner = $this->getAttribute('callnumber');
		return $returner;
	}

	/**
	 * @brief fetches the campus location for this record
	 * @return String the campus
	 */
	function getLocation() {
		$returner = $this->getAttribute('location');
		return $returner;
	}

	/**
	 * @brief fetches the loan period for this record
	 * @return String the loan period (ie, 2 hours)
	 */
	function getLoanPeriod() {
		$returner = $this->getAttribute('loanperiod');
		return $returner;
	}

	/**
	 * @brief a boolean to determine if this is hidden or not
	 * @return boolean true or false
	 */
	function isShadowed() {
		$returner = $this->getAttribute('shadow') == '1' ? true : false;
		return $returner;
	}
	/**
	 * @brief fetches the bar code for this record
	 * @return String the bar code
	 */
	function getBarcode() {
		$returner = $this->getAttribute('barcode');
		return $returner;
	}

	/**
	 * @brief fetches the parent record id for th is Item
	 * @return int the reserves record id
	 */
	function getReservesRecordID() {
		$returner = $this->getAttribute('reservesrecordid');
		return $returner;
	}

	/**
	 * @brief fetches the OPAC record.  This kicks off an AJAX query and you should dig deeper into accessOPACRecord() to find out.
	 * @return String the record
	 */
	function getOPACRecord() {
		accessOPACRecord(self::PHYSICAL_RESERVE_ITEM_QUERY, $this->_properties);
	}

	/**
	 * @brief  updates or creates an ElectronicReservesItem.
	 * @return boolean true or false on success or failure
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$callnumber = ReservesRequest::getRequestValue('callnumber');
		$barcode = ReservesRequest::getRequestValue('barcode');
		$citation = ReservesRequest::getRequestValue('citation');
		$physicalitemid = ReservesRequest::getRequestValue('physicalitemid');
		$reservesrecordid = ReservesRequest::getRequestValue('reservesrecordid');
		$usagerights = ReservesRequest::getRequestValue('usagerights');
		$location = ReservesRequest::getRequestValue('location');
		$loanperiod = ReservesRequest::getRequestValue('loanperiod');
		$shadow = ReservesRequest::getRequestValue('shadow') != '' ? '1' : '0';

		$reservesrecordids = array();

		if ($reservesrecordid == 0) { // this must be a bulk record create then
			$reservesrecordids = explode(',', ReservesRequest::getRequestValue('bulkrecordids'));
		} else {
			$reservesrecordids[] = $reservesrecordid;
		}

		$physicalItemIDs = array();

		foreach ($reservesrecordids as $reservesrecordid) {
			$sqlParams = array($callnumber, $barcode, $usagerights, $location, $citation, $loanperiod, $shadow, $reservesrecordid, $physicalitemid);

			if ($physicalitemid > 0) {
				$sql = "UPDATE physicalItem SET callNumber = ?, barCode = ?, usageRights = ?, location = ?, citation = ?, loanPeriod = ?, shadow = ?, reservesRecordID = ? WHERE physicalItemID = ?";
			} else {
				$sql = "INSERT INTO physicalItem (callNumber, barCode, usageRights, location, citation, loanPeriod, shadow, reservesRecordID, physicalItemID)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			}
			$returnStatement = $db->Execute($sql, $sqlParams);
			if ($returnStatement) {
				$op = $physicalitemid > 0 ? self::PHYSICAL_RESERVE_ITEM_EDIT : self::PHYSICAL_RESERVE_ITEM_CREATE;
				accessOPACRecord($op, $this->_properties);
				$physicalItemIDs[] = $db->Insert_ID() ? $db->Insert_ID() : $physicalitemid;
			}
		}
		if ($returnStatement) {
			return true;
		} else {
			error_log('Error occurred: ' . $db->ErrorMsg());
			return false;
		}
	}

	/**
	 *  @brief Deletes this physical reserve item
	 *  @return boolean success or not
	 */
	function delete() {

		$db = getDB();
		$sql = 'DELETE FROM physicalItem WHERE physicalItemID = ?';
		$returnStatement = $db->Execute($sql, array($this->getPhysicalItemID()));
		if ($returnStatement) {
			accessOPACRecord(self::PHYSICAL_RESERVE_ITEM_DELETE, $this->_properties);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief function for building a form to edit this item.
	 * @param $basePath String the base path from the config file for URLs to other pages. Usually '/reserves'
	 */
	function assembleEditForm(&$basePath) {
		import('general.Config');
		$config = new Config();

		import('forms.Form');
		import('items.ReservesRecord');

		$action = $this->getPhysicalItemID() > 0 ? 'editPhysicalItem' : 'createReservesItem';

		$bulkRecordIDs = array();
		if ($this->getAttribute('reservesrecordid') == 0) {
			$bulkRecordIDs = $this->getBulkRecordIDs();
		}

		$form = new Form(array('id' => 'physicalItem', 'method' => 'post', 'action' => $basePath  . '/index.php/' . $action . '/' . $this->getPhysicalItemID()));
		$label = $this->getPhysicalItemID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . ' Physical Reserves Item'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'physicalitemid', 'value' => $this->getPhysicalItemID()) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'reservesrecordid', 'value' => $this->getAttribute('reservesrecordid')) ));

		if (sizeof($bulkRecordIDs) > 0) {
			$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'bulkrecordids', 'value' => join(',', $bulkRecordIDs)) ));
		}
		$fieldSet->addField(ReservesRecord::getUsageRightsRadio($this->getAttribute('usagerights')));

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Call Number', 'secondaryLabel' => 'plain-text title', 'name' => 'callnumber',
							'value' => $this->getAttribute('callnumber'), 'requiredMsg' => 'Please enter a call number') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Barcode', 'secondaryLabel' => '', 'name' => 'barcode',
							'value' => $this->getAttribute('barcode')) ));

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Item Location', 'secondaryLabel' => 'Where is it?', 'name' => 'location',
							'value' => $this->getAttribute('location'), 'requiredMsg' => 'Please enter a location') ));

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Loan Period', 'secondaryLabel' => 'Length of Time', 'name' => 'loanperiod',
							'value' => $this->getAttribute('loanperiod'), 'requiredMsg' => 'Please enter a loan period') ));

		$fieldSet->addField(new TextArea( array('required' => true, 'primaryLabel' => 'Citation', 'secondaryLabel' => '', 'name' => 'citation',
							'value' => $this->getAttribute('citation'), 'requiredMsg' => 'Please enter a citation') ));

		$fieldSet->addField(new Checkbox( array('name' => 'shadow', 'primaryLabel' => 'Shadow this item?', 'secondaryLabel' => '' ,'value' => $this->getAttribute('shadow')) ) );

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>