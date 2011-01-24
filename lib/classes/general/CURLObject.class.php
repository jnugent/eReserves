<?php
class CURLObject {

	var $url;
	var $ch;

	function __construct($url) {

		$this->setUrl($url);
		$this->ch = curl_init();

		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";

		curl_setopt($this->ch, CURLOPT_URL, $this->url); // set url to post to
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->ch, CURLOPT_ENCODING,"");
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($this->ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 0);
	}

	/**
	 * @brief initiates a request to a remote server.
	 * * @param String $method GET or POST
	 * @param array $vars the extra bits of information to pass along
	 */
	function doRequest($method, $vars) {

		if ($method == 'POST') {
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $vars);
		}

		$data = @curl_exec($this->ch);

		if (curl_errno($this->ch)) {
			error_log(curl_error($this->ch));
			$results = false;
		} else {

			@curl_close($this->ch);
			$results = $data;
		}

		return $results;
	}

	/**
	 * @brief sets the URL the request will be made to.  This is the base url, without any query_string info
	 * @param String $url the URL
	 */
	function setURL($url) {
		$this->url = $url;
	}

	/**
	 * @brief returns the URL currently being used by the object
	 * @return String the URL
	 */
	function getURL() {
		$returner = $this->url;
		return $returner;
	}

	/**
	 * @brief returns the mime type of the connection.  It does this by setting a CURLOPT_NOBODY option which
	 * forces the request method to HEAD instead of GET or POST.
	 * @return String the mimetype
	 */
	function getMimeType() {

		curl_setopt($this->ch, CURLOPT_NOBODY, 1);
		$data = @curl_exec($this->ch);
		if (curl_errno($this->ch)) {
			$results = false;
		} else {
			// we're at the mercy of the server correctly setting the Content-type, but not much else we can do.
			$results = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
			@curl_close($this->ch);
		}

		return $results;
	}

	/**
	 * @brief for testing purposes, you may want to disable curl's SSL test for self-signed certificates.
	 */
	function disableSSLCheck() {
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
	}

	/**
	 * @brief initiates a GET request
	 * @return the output of the request
	 */
	function doGet() {
		return $this->doRequest('GET', 'NULL');
	}

	/**
	 * @brief initiates a POST request
	 * @param array $vars the data to be sent
	 * @return the output of the request
	 */
	function doPost($vars) {
		return $this->doRequest('POST', $vars);
	}
}
?>