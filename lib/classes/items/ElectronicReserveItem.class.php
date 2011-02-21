<?php

import ('items.ReserveItem');

class ElectronicReserveItem extends ReserveItem {

	var $_properties = array();

	function __construct($electronicItemID = 0) {

		if ($electronicItemID > 0) {
			$db = getDB();
			$sql = "SELECT e.electronicItemID, e.mimeType, e.doi, e.reservesRecordID, e.usageRights, e.url, e.itemTitle, e.originalFileName, e.restrictToLogin, e.restrictToEnroll
					 FROM electronicItem e WHERE e.electronicItemID = ?";
			$returnStatement = $db->Execute($sql, array($electronicItemID));
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

			$this->setAttribute('electronicitemid', '0');
			return true;
		}

	}

	/**
	 * @brief returns the primary key
	 * @return int the key
	 */
	function getElectronicItemID() {
		$returner = $this->getAttribute('electronicitemid');
		return $returner;
	}

	/**
	 * @brief returns the parent container Reserves Record
	 * @return ReservesRecord
	 */
	function getReservesRecord() {
		import ('items.ReservesRecord');
		return new ReservesRecord($this->getAttribute('reservesrecordid'));
	}

	/**
	 * @brief returns the URL to an electronic resource
	 * @return String the url
	 */
	function getURL() {
		$url = $this->getAttribute('url');
		if (preg_match("/^http:/", $url)) {  // it's a remotely referenced page
			return $url;
		} else {  // we'll need to stream this, so build a link to our content service

			import('general.Config');
			$config = new Config();
			$url = $config->getSetting('general', 'base_path') . '/index.php/stream/' . $this->getElectronicItemID() . '/' . htmlspecialchars($this->getAttribute('originalfilename'));
			return $url;
		}
	}

	/**
	 * @brief returns a boolean about whether or not the file requires logins to view
	 * @return boolean true or false
	 */
	function isRestricted() {
		if ($this->getAttribute('restricttologin') == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief returns a boolean about whether or not the file requires a user to be enrolled in the section to view
	 * @return boolean true or false
	 */
	function requiresEnrolment() {
		if ($this->getAttribute('restricttoenroll') == 1) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @brief returns the original file name, used as a "save as" hint
	 * @return String the file name
	 */
	function getLinkTitle() {
		$name = $this->getAttribute('originalfilename');
		if($name != '') {
			return $name;
		} else {
			return $this->getURL();
		}
	}

	/**
	 * @brief returns the mime type of an uploaded file
	 * @return String the mime type (ie, text/html)
	 */
	function getMimeType() {
		$returner = $this->getAttribute('mimetype');
		return $returner;
	}

	/**
	 * @brief  updates or creates an ElectronicReservesItem.
	 * @return boolean true or false on success or failure
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$itemtitle = ReservesRequest::getRequestValue('itemtitle');
		$doi = ReservesRequest::getRequestValue('doi');
		$electronicitemid = ReservesRequest::getRequestValue('electronicitemid');
		$reservesrecordid = ReservesRequest::getRequestValue('reservesrecordid');
		$usagerights = ReservesRequest::getRequestValue('usagerights');
		$restricttologin = ReservesRequest::getRequestValue('restricttologin') != '' ? '1' : '0';
		$restricttoenroll = ReservesRequest::getRequestValue('restricttoenroll') != '' ? '1' : '0';

		$url = ReservesRequest::getRequestValue('url');
		$mimetype = '';
		$originalfileName = '';

		$reservesrecordids = array();

		if ($reservesrecordid == 0) { // this must be a bulk record create then
			$reservesrecordids = explode(',', ReservesRequest::getRequestValue('bulkrecordids'));
		} else {
			$reservesrecordids[] = $reservesrecordid;
		}

		/* Uploaded document get priority in the choice process */
		if (sizeof($_FILES) > 0 && $_FILES['uploadedfile']['tmp_name'] != '') {
			$originalfilename = $_FILES['uploadedfile']['name'];
			$mimetype = $_FILES['uploadedfile']['type'];
			$temporaryName = $_FILES['uploadedfile']['tmp_name'];
			$url = moveUploadedAsset($temporaryName);
		} else {
			$mimetype = ReservesRequest::determineMimeType($url);
		}

		$electronicItemIDs = array();
		foreach ($reservesrecordids as $reservesrecordid) {
			$sqlParams = array($itemtitle, $doi, $mimetype, $url, $usagerights, $reservesrecordid, $originalfilename, $restricttologin, $restricttoenroll, $electronicitemid);
			if ($electronicitemid > 0) {
				$sql = "UPDATE electronicItem SET itemTitle = ?, doi = ?, mimeType = ?, url = ?, usageRights = ?, reservesRecordID = ?, originalFileName = ?, restrictToLogin = ?,
						restrictToEnroll = ?
						WHERE electronicItemID = ?";
			} else {
				$sql = "INSERT INTO electronicItem (itemTitle, doi, mimeType, url, usageRights, reservesRecordID, originalFileName, restrictToLogin, restrictToEnroll, electronicItemID)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			}
			$returnStatement = $db->Execute($sql, $sqlParams);
			if ($returnStatement) {
				$electronicItemIDs[] = $db->Insert_ID() ? $db->Insert_ID() : $electronicitemid; // we don't really need these yet but the code is here to get them.
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
	 *  @brief Deletes this electronic reserve item
	 *  @return boolean success or not
	 */
	function delete() {

		$db = getDB();
		$sql = 'DELETE FROM electronicItem WHERE electronicItemID = ?';
		$returnStatement = $db->Execute($sql, array($this->getElectronicItemID()));
		if ($returnStatement) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief  function for building a form to edit this item.
	 * @param $basePath String the base path from the config file for URLs to other pages. Usually '/reserves'
	 */
	function assembleEditForm(&$basePath) {
		import('general.Config');
		import('items.ReservesRecord');
		$config = new Config();

		import('forms.Form');
		$action = $this->getElectronicItemID() > 0 ? 'editElectronicItem' : 'createReservesItem';

		$bulkRecordIDs = array();
		if ($this->getAttribute('reservesrecordid') == 0) {
			$bulkRecordIDs = $this->getBulkRecordIDs();
		}

		$form = new Form(array('id' => 'electronicItem', 'method' => 'post', 'enctype' => 'multipart/form-data',
						'action' => $basePath . '/index.php/' . $action . '/' . $this->getElectronicItemID()));
		$label = $this->getElectronicItemID() > 0 ? 'Edit' : 'Create New';
		$fieldSet = new FieldSet(array('legend' => $label . ' Electronic Reserves Item'));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'MAX_FILE_SIZE', 'value' => $config->getSetting('assetstore', 'max_upload_size')) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'electronicitemid', 'value' => $this->getElectronicItemID()) ));
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'reservesrecordid', 'value' => $this->getAttribute('reservesrecordid')) ));

		if (sizeof($bulkRecordIDs) > 0) {
			$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'bulkrecordids', 'value' => join(',', $bulkRecordIDs)) ));
		}
		$fieldSet->addField(ReservesRecord::getUsageRightsRadio($this->getAttribute('usagerights')));

		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Item Title', 'secondaryLabel' => 'plain-text title', 'name' => 'itemtitle',
							'value' => $this->getAttribute('itemtitle'), 'requiredMsg' => 'Please enter a title') ));
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'DOI', 'secondaryLabel' => 'A DOI', 'name' => 'doi',
							'value' => $this->getAttribute('doi')) ));

		/* the form contains both a URL and an Upload field, depending on how the record is created */
		$fieldSet->addField(new HTMLBlock(array('content' => '<p>Please choose either a URL to a document, or a file on your own computer.</p>')));

		$fileChoiceValue = '';
		if ($this->getURL() != '') {
			$fileChoiceValue = preg_match('|^http://|', $this->getURL()) ? 'filechoiceurl' : 'filechoicelocal';
		}

		$radio = new Radio( array('name' => 'fileChoice', 'value' => $fileChoiceValue, 'required' => true, 'primaryLabel' => 'File Location', 'secondaryLabel' => 'Please choose one',
							'requiredMsg' => 'Please choose a location') );
		$radio->addButton( array('id' => 'fileChoiceURL', 'value'=> 'filechoiceurl', 'caption' => 'Remote URL') );
		$radio->addButton( array('id' => 'fileChoiceLocal', 'value'=> 'filechoicelocal', 'caption' => 'Local File') );
		$fieldSet->addField($radio);

		$fieldSet->addField(new Checkbox( array('name' => 'restricttologin', 'primaryLabel' => 'Require logins to view?', 'secondaryLabel' => 'only for uploaded files' ,'value' => $this->getAttribute('restricttologin')) ) );
		$fieldSet->addField(new Checkbox( array('name' => 'restricttoenroll', 'primaryLabel' => 'Require section enrolment?', 'secondaryLabel' => 'only for uploaded files' ,'value' => $this->getAttribute('restricttoenroll')) ) );

		$fieldSet->addField(new TextField( array( 'primaryLabel' => 'Item URL', 'secondaryLabel' => 'Full URL', 'name' => 'url',
							'value' => $fileChoiceValue == 'filechoiceurl' ? $this->getAttribute('url') : '', 'requiredMsg' => 'Please enter a full URL',
							'validationDep' => 'function(element) { if ($("#fileChoice:checked").val() == "filechoiceurl") return true; return false; }') ));
		$fieldSet->addField(new FileUpload( array('primaryLabel' => 'Upload', 'secondaryLabel' => 'A File on your computer', 'name' => 'uploadedfile',
							'value' => $fileChoiceValue == 'filechoicelocal' ? $this->getAttribute('originalfilename') : '', 'validationDep' => 'function(element) {if ($("#fileChoice:checked").val() == "filechoicelocal") return true; return false; } ') ));

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>