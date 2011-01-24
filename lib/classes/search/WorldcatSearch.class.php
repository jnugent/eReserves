<?php

	class WorldcatSearch {

		private $_wsapiKey = '';
		private $_resultFormat = 'dc';
		private $_baseWorldcatUrl = 'http://www.worldcat.org/webservices/catalog/search/sru?query=';

		function __construct() {
			import('general.Config');
			$config = new Config();
			$this->_wsapiKey = $config->getSetting('worldcat', 'wskey');
			return true;
		}

		/**
		 * @brief this method sets the result format to either DC, or to MARC.
		 * @param String $format
		 * @return void
		 */
		public function setResultFormat($format) {

			switch ($format) {

				case 'dc':
				case 'marc':
					$this->_resultFormat = $format;
				break;
				default:
					$this->_resultFormat = 'dc';
				break;
			}
		}

		/**
		 * @brief performs a search using the WC API.  Accepts an array of terms for the search, with author and title for now.
		 * @param Array $terms the author/title/etc... terms to search for
		 * @return the result set, as an array of XML fragments
		 */
		public function doSearch($terms = array()) {

			$author = trim($terms['author']);
			$title  = trim($terms['title']);

			if ($author != '' || $title != '') {
				import('general.CURLObject');

				$url = $this->_baseWorldcatUrl;

				if ($author != '' && $title != '') {
					$url .= 'srw.au+%3D+%22' . urlencode($author) . '%22+and+srw.ti+%3D+%22' . urlencode($title) . '%22';
				} else if ($author != '') {
					$url .= 'srw.au+%3D+%22' . urlencode($author) . '%22';
				} else {
					$url .= 'srw.ti+%3D+%22' . urlencode($title) . '%22';
				}

				if ($this->_resultFormat == 'dc') {
					$url .= '&recordSchema=info%3Asrw%2Fschema%2F1%2Fdc';
				}
				$url .= '&wskey=' . $this->_wsapiKey;

				$curlObject = new CURLObject($url);
				$wcResults = $curlObject->doGet();
				return true;
			}
		}

		/**
		 * @brief assembles the search form on the template page
		 * @param String $basePath from the config.php file.  The base path for for the reserves system.
		 * @return Form a Form object for this form
		 */
		static public function buildWCSearchForm($basePath) {

			import('forms.Form');
			$form = new Form(array('id' => 'searchWC', 'method' => 'post', 'action' => $basePath . '/index.php/searchWC'));
			$fieldSet = new FieldSet(array('legend' => 'Search the Worldcat Catalogue'));
			$fieldSet->addField(new TextField( array('name' => 'author','primaryLabel' => 'Some Author Terms', 'secondaryLabel' => '',
						'value' => ReservesRequest::getRequestValue('author'), 'requiredMsg' => 'Please enter either an author, or a title, or both',
						'validationDep' => 'function(element) { if ($("#author").val() == "" && $("#title").val() == "") return true; return false; }')) );
			$fieldSet->addField(new TextField( array('name' => 'title','primaryLabel' => 'Some Title Terms', 'secondaryLabel' => '',
						'value' => ReservesRequest::getRequestValue('title'), 'requiredMsg' => 'Please enter either an author, or a title, or both',
						'validationDep' => 'function(element) { if ($("#author").val() == "" && $("#title").val() == "") return true; return false; }')) );
			$fieldSet->addField(new Button( array('type' => 'submit', 'label' => 'Submit')) );

			$form->addFieldSet($fieldSet);
			return $form;
		}
	}
?>