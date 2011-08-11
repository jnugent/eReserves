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
			$sql = "SELECT p.physicalItemID, p.barCode, p.reservesRecordID, p.shadow, p.dateAdded, p.linkID FROM physicalItem p WHERE p.physicalItemID = ?";
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
			$this->setAttribute('linkid', '0');
			return true;
		}
}

	/**
	 * @brief fetches an attribute from the attributes array created when an item is instantiated.
	 * @param String $attribute the attribute.
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
	 * @param $attributeName the property name to set.
	 * @param $attributeValue the value to set the property to.
	 */
	function setAttribute($attributeName, $attributeValue) {
		$this->_properties[$attributeName] = $attributeValue;
	}

	/**
	 * @brief fetches the primary key for this record.
	 * @return int the ID.
	 */
	function getPhysicalItemID() {
		$returner = $this->getAttribute('physicalitemid');
		return $returner;
	}

	/**
	 * @brief a boolean to determine if this is hidden or not.
	 * @return boolean true or false.
	 */
	function isShadowed() {
		$returner = $this->getAttribute('shadow') == '1' ? true : false;
		return $returner;
	}

	/**
	 * @brief fetches the bar code for this record.
	 * @return String the bar code.
	 */
	function getBarcode() {
		$returner = $this->getAttribute('barcode');
		return $returner;
	}

	/**
	 * @brief fetches the OPAC record.  This kicks off an AJAX query and you should dig deeper into accessOPACRecord() to find out.
	 * @return String the record.
	 */
	function getOPACRecord() {
		accessOPACRecord(self::PHYSICAL_RESERVE_ITEM_QUERY, $this->_properties);
	}

	/**
	 * @brief returns the URL to the catalogue record (a JavaScript link).
	 * @return String the JavaScript link to our catalogue.
	 */
	function getURL() {
		$url = "javascript:getOPACRecord(" . $this->getPhysicalItemID() . ")";
		return $url;
	}

	/**
	 * @brief  updates or creates an ElectronicReservesItem.
	 * @return boolean true or false on success or failure.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$barcodeString = ReservesRequest::getRequestValue('barcode'); // may contain more than one code, newline separated
		$barcodes = preg_split("{\s+}s", $barcodeString);
		$physicalitemid = ReservesRequest::getRequestValue('physicalitemid');
		$reservesrecordid = ReservesRequest::getRequestValue('reservesrecordid');
		$shadow = ReservesRequest::getRequestValue('shadow') != '' ? '1' : '0';
		$linkid = ReservesRequest::getRequestValue('linkid') != '' ? ReservesRequest::getRequestValue('linkid') : 0;
		$updatedlinked = ReservesRequest::getRequestValue('updatelinked') != '' ? true : false;
		$keeplinked = ReservesRequest::getRequestValue('keeplinked') != '' ? true : false;

		$reservesrecordids = array();

		if ($reservesrecordid == 0) { // this must be a bulk record create then
			$reservesrecordids = explode(',', ReservesRequest::getRequestValue('bulkrecordids'));
		} else {
			$reservesrecordids[] = $reservesrecordid;
		}

		if ($updatedlinked && $keeplinked) {
			$sql = "UPDATE physicalItem SET barCode = ?, shadow = ?, dateAdded = now() WHERE linkID = ?";
			$returnStatement = $db->Execute($sql, array($barcodes[0], $shadow, $linkid));
		}

		if (!$keeplinked) {
			$sql = 'UPDATE physicalItem SET linkID = ? WHERE physicalItemID = ?';
			$returnStatement = $db->Execute($sql, array('0', $physicalitemid));
		}

		foreach ($barcodes as $barcode) {

			$physicalItemIDs = array();

			foreach ($reservesrecordids as $reservesrecordid) {
				$sqlParams = array($barcode, $shadow, $reservesrecordid, $physicalitemid);

				if ($physicalitemid > 0) {
					$sql = "UPDATE physicalItem SET barCode = ?, shadow = ?, reservesRecordID = ?, dateAdded = now() WHERE physicalItemID = ?";
				} else {
					$sql = "INSERT INTO physicalItem (barCode, shadow, reservesRecordID, physicalItemID, dateAdded)
						VALUES (?, ?, ?, ?, now())";
				}
				$returnStatement = $db->Execute($sql, $sqlParams);
				if ($returnStatement) {
					$op = $physicalitemid > 0 ? self::PHYSICAL_RESERVE_ITEM_EDIT : self::PHYSICAL_RESERVE_ITEM_CREATE;
					accessOPACRecord($op, $this->_properties);
					$physicalItemIDs[] = $db->Insert_ID() ? $db->Insert_ID() : $physicalitemid;
					if ($linkid == 0) {
						$linkid = $db->Insert_ID();
					}
				}
			}

			if (sizeof($reservesrecordids) > 1) { // this was a bulk create request
				$sql = "UPDATE physicalItem SET linkID = ? WHERE physicalItemID IN (". join(",", $physicalItemIDs) . ")";
				$db->Execute($sql, array($linkid));
			}
		}
		if ($returnStatement) {
			return $reservesrecordids;
		} else {
			error_log('Error occurred: ' . $db->ErrorMsg());
			return false;
		}
	}

	/**
	 *  @brief Deletes this physical reserve item.
	 *  @return boolean success or not.
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
	 * @param $basePath String the base path from the config file for URLs to other pages. Usually '/reserves'.
	 */
	function assembleEditForm($basePath) {
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
		$barCodeFieldType = $this->getPhysicalItemID() > 0 ? 'TextField' : 'TextArea';
		$secondaryLabel = $this->getPhysicalItemID() > 0 ? '' : 'Enter more than one code, one per line. <br /><br />Each item will become a separate reserve item for this section.';

		$fieldSet = new FieldSet(array('legend' => $label . ' Physical Reserves Item'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'physicalitemid', 'value' => $this->getPhysicalItemID()) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'reservesrecordid', 'value' => $this->getAttribute('reservesrecordid')) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'linkid', 'value' => $this->getLinkID()) ));

		if ($this->getLinkID() > 0) {
			$fieldSet->addField(new HTMLBlock(array('content' => '<strong style="color: red">This Item is linked to others. You can push these changes to the other records.</strong>')));
			$fieldSet->addField(new Checkbox( array('name' => 'updatelinked', 'primaryLabel' => 'Update linked Records?', 'secondaryLabel' => '' ,'value' => true) ) );
			$fieldSet->addField(new Checkbox( array('name' => 'keeplinked', 'primaryLabel' => 'Maintain link to others?', 'secondaryLabel' => '' ,'value' => true) ) );
		}

		if (sizeof($bulkRecordIDs) > 0) {
			$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'bulkrecordids', 'value' => join(',', $bulkRecordIDs)) ));
		}
//		$fieldSet->addField(ReservesRecord::getUsageRightsRadio($this->getAttribute('usagerights')));

//		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Call Number', 'secondaryLabel' => 'plain-text title', 'name' => 'callnumber',
//							'value' => $this->getAttribute('callnumber'), 'requiredMsg' => 'Please enter a call number') ));
		$fieldSet->addField(new $barCodeFieldType( array('required' => true, 'primaryLabel' => 'Barcode', 'secondaryLabel' => $secondaryLabel, 'name' => 'barcode',
							'value' => $this->getAttribute('barcode')) ));

//		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Item Location', 'secondaryLabel' => 'Where is it?', 'name' => 'location',
//							'value' => $this->getAttribute('location'), 'requiredMsg' => 'Please enter a location') ));

//		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Loan Period', 'secondaryLabel' => 'Length of Time', 'name' => 'loanperiod',
//							'value' => $this->getAttribute('loanperiod'), 'requiredMsg' => 'Please enter a loan period') ));

//		$fieldSet->addField(new TextArea( array('required' => true, 'primaryLabel' => 'Citation', 'secondaryLabel' => '', 'name' => 'citation',
//							'value' => $this->getAttribute('citation'), 'requiredMsg' => 'Please enter a citation') ));

		$fieldSet->addField(new Checkbox( array('name' => 'shadow', 'primaryLabel' => 'Shadow this item?', 'secondaryLabel' => '' ,'value' => $this->getAttribute('shadow')) ) );

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
