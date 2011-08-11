<?php

import ('items.ReserveItem');

define("ELECTRONIC_ITEM_NOT_WITHIN", "0");
define("ELECTRONIC_ITEM_PENDING", "1");
define("ELECTRONIC_ITEM_CLEARED", "2");

class ElectronicReserveItem extends ReserveItem {

	var $_properties = array();

	function __construct($electronicItemID = 0) {

		if ($electronicItemID > 0) {
			$db = getDB();
			$sql = "SELECT e.electronicItemID, e.mimeType, e.doi, e.notes, e.reservesRecordID, e.usageRights, e.url, e.itemTitle, e.originalFileName, e.restrictToLogin, e.restrictToEnroll, e.dateAdded,
					e.itemAuthor, e.itemSource, e.itemPublisher, e.itemPages, e.itemVolIss, e.proxy, e.linkID
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
			$this->setAttribute('restricttoenroll', '1');
			$this->setAttribute('linkid', '0');
			return true;
		}

	}

	/**
	 * @brief returns the primary key.
	 * @return int the key.
	 */
	function getElectronicItemID() {
		$returner = $this->getAttribute('electronicitemid');
		return $returner;
	}

	/**
	 * @brief returns the URL to an electronic resource.
	 * @return String the URL.
	 */
	function getURL() {

		import('general.Config');
		$config = new Config();

		$prefix = $this->requiresProxy() ? $config->getSetting('proxy', 'prefix') : '';

		$url = $this->getAttribute('url');
		if (preg_match("/^https?:/", $url)) {  // it's a remotely referenced page
			return $prefix . $url;
		} else {  // we'll need to stream this, so build a link to our content service

			$url = $config->getSetting('general', 'base_path') . '/index.php/stream/' . $this->getElectronicItemID() . '/' . htmlspecialchars($this->getAttribute('originalfilename'));
			return $url;
		}
	}

	/**
	 * @brief returns the notes field for a record.
	 * @return String the concatenated fields for a Citation (essentially).
	 */
	function getNotes() {
		$fields = array();
		foreach (array('itemauthor', 'itemsource', 'itempublisher', 'itemvoliss', 'itempages') as $field) {
			if (($val =& $this->getAttribute($field)) != '') {
				$fields[] = $val;
			}
		}
		$returner = join(', ', $fields);
		return $returner;
	}

	/**
	 * @brief returns a boolean about whether or not the file requires logins to view.
	 * @return boolean true or false.
	 */
	function isOpenAccess() {
		if ($this->getAttribute('restricttologin') == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief electronic items can be shadowed if their copyright clearance is in dispute.  Items that are 'not_within' are shadowed to all but admins.
	 * 'pending' items are still hidden, but tagged such that admins are aware that they are in review.
	 * @return int ELECTRONIC_ITEM_PENDING, ELECTRONIC_ITEM_NOT_WITHIN, ELECTRONIC_ITEM_CLEARED
	 */
	function isShadowed() {
		switch ($this->getAttribute('usagerights')) {
			case 'not_within':
				return ELECTRONIC_ITEM_NOT_WITHIN;
				break;
			case 'pending':
				return ELECTRONIC_ITEM_PENDING;
				break;
			default:
				return ELECTRONIC_ITEM_CLEAR;
				break;
		}
	}

	function getClearanceStatus() {
		return $this->getAttribute('usagerights') == 'not_within' ? 'Not cleared' : 'Pending';
	}
	/**
	 * @brief returns a boolean about whether or not the file requires a user to be enrolled in the section to view.
	 * @return boolean true or false.
	 */
	function requiresEnrolment() {
		if ($this->getAttribute('restricttoenroll') == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief returns a boolean about whether or not the file requires a user to be enrolled in the section to view.
	 * @return boolean true or false.
	 */
	function requiresProxy() {
		if ($this->getAttribute('proxy') == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief returns the original file name, used as a "save as" hint.
	 * @return String the file name.
	 */
	function getLinkTitle() {
		$name = $this->getAttribute('originalfilename');
		if($name != '') {
			return $name;
		} else { // return the URL, but truncate it to keep long urls sane.
			$title = $this->getURL();
			if (strlen($title) > 30) {
				return preg_replace('{^(.{0,15}).*?(.{0,10})$}', "$1 ... $2", $title);
			} else {
				return $title;
			}
		}
	}

	/**
	 * @brief returns the mime type of an uploaded file.
	 * @return String the mime type (ie, text/html).
	 */
	function getMimeType() {
		$returner = $this->getAttribute('mimetype');
		return $returner;
	}

	/**
	 * @brief returns an image type based on the Item's mime type.
	 * @return String the type of image file to display on the record page.
	 */
	function mapTypeToImg() {

		$mimeMap = array(
			'application/octet-stream' => 'binary',
			'bmp',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc', 'application/msword' => 'doc', 'application/rtf' => 'doc',
			'text/' => 'html',
			'image/' => 'image', 'image/png' => 'image', 'image/gif' => 'image', 'image/tiff' => 'image',
			'application/x-pdf' => 'pdf', 'application/pdf' => 'pdf',
			'audio/' => 'snd',
			'application/vnd.ms-excel' => 'spreadsheet',
			'video/' => 'video',
			'application/x-gzip' => 'zip', 'application/zip' => 'zip',
		);
		$mimeType = $this->getMimeType();
		foreach (array_keys($mimeMap) as $key) {
			if (preg_match("{^" . quotemeta($key). "}", $mimeType))
				return $mimeMap[$key];
		}
		return $mimeMap['application/octet-stream'];
	}

	/**
	 * @brief updates or creates an ElectronicReservesItem.
	 * @return boolean true or false on success or failure.
	 */
	function update() {
		$db = getDB();
		import('general.ReservesRequest');

		$itemtitle = ReservesRequest::getRequestValue('itemtitle');
		$doi = ReservesRequest::getRequestValue('doi');
		$notes = ReservesRequest::getRequestValue('notes');
		$author = ReservesRequest::getRequestValue('itemauthor');
		$publisher = ReservesRequest::getRequestValue('itempublisher');
		$itemsource = ReservesRequest::getRequestValue('itemsource');
		$pages = ReservesRequest::getRequestValue('itempages');
		$voliss = ReservesRequest::getRequestValue('itemvoliss');
		$electronicitemid = ReservesRequest::getRequestValue('electronicitemid');
		$reservesrecordid = ReservesRequest::getRequestValue('reservesrecordid');
		$usagerights = ReservesRequest::getRequestValue('usagerights');
		$restricttologin = ReservesRequest::getRequestValue('restricttologin') != '' ? '1' : '0';
		$restricttoenroll = ReservesRequest::getRequestValue('restricttoenroll') != '' ? '1' : '0';
		$proxy = ReservesRequest::getRequestValue('proxy') != '' ? '1' : '0';
		$linkid = ReservesRequest::getRequestValue('linkid') != '' ? ReservesRequest::getRequestValue('linkid') : 0;
		$updatedlinked = ReservesRequest::getRequestValue('updatelinked') != '' ? true : false;
		$keeplinked = ReservesRequest::getRequestValue('keeplinked') != '' ? true : false;

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
			if ($url != '') {
				$mimetype = ReservesRequest::determineMimeType($url);
			}
		}

		/* if these variables are still empty, this was probably a record edit and no file was uploaded so keep the original settings */
		if ($url == '') {
			$url = $this->getAttribute('url');
		}

		if ($originalfilename == '') {
			$originalfilename = $this->getAttribute('originalfilename');
		}

		if ($mimetype == '') {
			$mimetype = $this->getAttribute('mimetype');
		}

 		$electronicItemIDs = array();

		if ($updatedlinked && $keeplinked) {
			$sql = "UPDATE electronicItem SET itemTitle = ?, doi = ?, mimeType = ?, url = ?, usageRights = ?, reservesRecordID = ?, originalFileName = ?, restrictToLogin = ?,
					restrictToEnroll = ?, notes = ?, itemAuthor = ?, itemPublisher = ?, itemSource = ?, itemPages =? , itemVolIss = ?, proxy = ?, dateAdded = now()
					WHERE linkID = ?";
			$returnStatement = $db->Execute($sql, array($itemtitle, $doi, $mimetype, $url, $usagerights, $reservesrecordid, $originalfilename, $restricttologin, $restricttoenroll, $notes, $author, $publisher, $itemsource, $pages, $voliss, $proxy, $linkid));
		}

		if (!$keeplinked) {
			$sql = 'UPDATE electronicItem SET linkID = ? WHERE electronicItemID = ?';
			$returnStatement = $db->Execute($sql, array('0', $electronicitemid));
		}

		foreach ($reservesrecordids as $reservesrecordid) {
			$sqlParams = array($itemtitle, $doi, $mimetype, $url, $usagerights, $reservesrecordid, $originalfilename, $restricttologin, $restricttoenroll, $notes, $author, $publisher, $itemsource, $pages, $voliss, $proxy, $electronicitemid);
			if ($electronicitemid > 0) {
				$sql = "UPDATE electronicItem SET itemTitle = ?, doi = ?, mimeType = ?, url = ?, usageRights = ?, reservesRecordID = ?, originalFileName = ?, restrictToLogin = ?,
						restrictToEnroll = ?, notes = ?, itemAuthor = ?, itemPublisher = ?, itemSource = ?, itemPages =? , itemVolIss = ?, proxy = ?, dateAdded = now()
						WHERE electronicItemID = ?";
			} else {
				$sql = "INSERT INTO electronicItem (itemTitle, doi, mimeType, url, usageRights, reservesRecordID, originalFileName, restrictToLogin, restrictToEnroll, notes, itemAuthor, itemPublisher, itemSource, itemPages, itemVolIss, proxy, electronicItemID, dateAdded)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())";
			}
			$returnStatement = $db->Execute($sql, $sqlParams);
			if ($returnStatement) {
				$electronicItemIDs[] = $db->Insert_ID() ? $db->Insert_ID() : $electronicitemid; // we don't really need these yet but the code is here to get them.
				if ($linkid == 0) {
					$linkid = $db->Insert_ID();
				}
			}
		}

		if (sizeof($reservesrecordids) > 1) { // this was a bulk create request
			$sql = "UPDATE electronicItem SET linkID = ? WHERE electronicItemID IN (". join(",", $electronicItemIDs) . ")";
			$db->Execute($sql, array($linkid));
		}

		if ($returnStatement) {
			return $reservesrecordids;
		} else {
			error_log('Error occurred: ' . $db->ErrorMsg());
			return false;
		}
	}

	/**
	 *  @brief Deletes this electronic reserve item.
	 *  @return boolean success or not.
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
	 * @param $basePath String the base path from the config file for URLs to other pages. Usually '/reserves'.
	 * @return Form the form object for this item.
	 */
	function assembleEditForm($basePath) {
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
		$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'linkid', 'value' => $this->getLinkID()) ));

		if ($this->getLinkID() > 0) {
			$fieldSet->addField(new HTMLBlock(array('content' => '<strong style="color: red">This Item is linked to others. You can push these changes to the other records.</strong>')));
			$fieldSet->addField(new Checkbox( array('name' => 'updatelinked', 'primaryLabel' => 'Update linked Records?', 'secondaryLabel' => '' ,'value' => true) ) );
			$fieldSet->addField(new Checkbox( array('name' => 'keeplinked', 'primaryLabel' => 'Maintain link to others?', 'secondaryLabel' => '' ,'value' => true) ) );
		}

		if (sizeof($bulkRecordIDs) > 0) {
			$fieldSet->addField(new HiddenField( array('required' => true, 'name' => 'bulkrecordids', 'value' => join(',', $bulkRecordIDs)) ));
		}

		$fileChoiceValue = '';
		if ($this->getURL() != '') {
			$fileChoiceValue = preg_match('|^https?:|', $this->getURL()) ? 'filechoiceurl' : 'filechoicelocal';
		}

		/* the form contains both a URL and an Upload field, depending on how the record is created */
		$fieldSet->addField(new HTMLBlock(array('content' => '<p>Please choose either a URL to a document, or a file on your own computer.</p>')));

		$radio = new Radio( array('name' => 'fileChoice', 'value' => $fileChoiceValue, 'required' => true, 'primaryLabel' => 'File Location', 'secondaryLabel' => 'Please choose one',
							'requiredMsg' => 'Please choose a location', 'onClick' => 'toggleFields()') );
		$radio->addButton( array('id' => 'fileChoiceURL', 'value'=> 'filechoiceurl', 'caption' => 'Remote URL') );
		$radio->addButton( array('id' => 'fileChoiceLocal', 'value'=> 'filechoicelocal', 'caption' => 'Local File') );
		$fieldSet->addField($radio);
		$fieldSet->addField(new TextField( array('required' => true, 'primaryLabel' => 'Item Title', 'secondaryLabel' => 'plain-text title', 'name' => 'itemtitle',
							'value' => $this->getAttribute('itemtitle'), 'requiredMsg' => 'Please enter a title') ));
		$fieldSet->addField(new TextField( array('primaryLabel' => 'Item Source', 'secondaryLabel' => '', 'name' => 'itemsource', 'required' => true,
							'value' => $this->getAttribute('itemsource')) ));
		$fieldSet->addField(new TextField( array('primaryLabel' => 'Item Author', 'secondaryLabel' => '', 'name' => 'itemauthor',
							'value' => $this->getAttribute('itemauthor'), 'validationDep' => 'function(element) {if ($("#fileChoice:checked").val() == "filechoicelocal") return true; return false; } ' ) ));
		$fieldSet->addField(ReservesRecord::getUsageRightsRadio($this->getAttribute('usagerights')));

		$fieldSet->addField(new Checkbox( array('name' => 'restricttologin', 'primaryLabel' => 'Require logins to view?', 'value' => $this->getAttribute('restricttologin')) ) );
		$fieldSet->addField(new Checkbox( array('name' => 'restricttoenroll', 'primaryLabel' => 'Require section enrolment?', 'value' => $this->getAttribute('restricttoenroll')) ) );

		$fieldSet->addField(new TextField( array( 'primaryLabel' => 'Item URL', 'secondaryLabel' => 'Full URL', 'name' => 'url',
							'value' => $fileChoiceValue == 'filechoiceurl' ? $this->getAttribute('url') : '', 'requiredMsg' => 'Please enter a full URL',
							'validationDep' => 'function(element) { if ($("#fileChoice:checked").val() == "filechoiceurl") return true; return false; }') ));
		$fieldSet->addField(new Checkbox( array('name' => 'proxy', 'primaryLabel' => 'Proxy this URL?', 'value' => $this->getAttribute('proxy')) ) );

		if ($this->getElectronicItemID() == 0) {
			$fieldSet->addField(new FileUpload( array('primaryLabel' => 'Upload', 'secondaryLabel' => 'A file on your computer', 'name' => 'uploadedfile',
							'value' => $fileChoiceValue == 'filechoicelocal' ? $this->getAttribute('originalfilename') : '', 'validationDep' => 'function(element) {if ($("#fileChoice:checked").val() == "filechoicelocal") return true; return false; } ') ));
		} else {
			$fieldSet->addField(new FileUpload( array('primaryLabel' => 'Upload', 'secondaryLabel' => 'A file to replace this one', 'name' => 'uploadedfile',
							'value' => $fileChoiceValue == 'filechoicelocal' ? $this->getAttribute('originalfilename') : '') ));
		}
		$fieldSet->addField(new TextField( array('primaryLabel' => 'DOI', 'secondaryLabel' => 'A DOI', 'name' => 'doi',
							'value' => $this->getAttribute('doi')) ));

		$fieldSet->addField(new TextField( array('primaryLabel' => 'Publisher', 'secondaryLabel' => '', 'name' => 'itempublisher','value' => $this->getAttribute('itempublisher')) ));
		$fieldSet->addField(new TextField( array('primaryLabel' => 'Volume/Issue', 'secondaryLabel' => '', 'name' => 'itemvoliss','value' => $this->getAttribute('itemvoliss')) ));
		$fieldSet->addField(new TextField( array('primaryLabel' => 'Pages', 'secondaryLabel' => '', 'name' => 'itempages', 'value' => $this->getAttribute('itempages')) ));
		$fieldSet->addField(new TextArea( array( 'primaryLabel' => 'Notes', 'secondaryLabel' => 'For internal use, do not display.', 'name' => 'notes',
							'value' => $this->getAttribute('notes')) ));

		$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );
		$form->addFieldSet($fieldSet);
		return $form;
	}
}
?>
