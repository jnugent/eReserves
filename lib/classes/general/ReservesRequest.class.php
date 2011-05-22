<?php

class ReservesRequest {

	public function __construct() {
		return true;
	}

	/**
	 *  static function to force the use of an SSL site, if the setting is enabled within config.php
	 */
	static function forceSSL() {
		import('general.Config');
		$config = new Config();

		if ($config->getSetting('general', 'force_ssl') && $_SERVER['HTTPS'] != 'on') {
			header('Location: https://' . $config->getSetting('general', 'host_name') . $config->getSetting('general', 'base_path') . '/index.php');
			exit(0);
		}
	}

	/**
	 * @brief extracts the operation out of the request URL,  and returns it. The provision to extract an object ID (like a database key) is also here,
	 * but that is not returned yet.
	 * @return String the operation.
	 */
	static function getURLOp() {

		$pathInfo = $_SERVER['PATH_INFO'];
		$op = '';
		$objectID = 0;

		if (preg_match("@/([^/]+)(/(\d+))?((/[^/]+)+)?@", $pathInfo, $matches)) {
			$op = $matches[1];
			$objectID = intval($matches[3]);
			// next line not obvious.  This splits up the rest of the url, and returns the numbers.  ie, 12/1/2/44/1
			// which may be relevant to the operation at hand.
			$extraArgs = preg_split("@/@", $matches[4], -1, PREG_SPLIT_NO_EMPTY);
			if ($objectID == '' || $objectID < 1) {
				$objectID = 0;
			}
		}

		$returner = array($op, $objectID, $extraArgs);
		return $returner;
	}

	/*
	 * @brief extract a value for a submitted form out of the $_REQUEST array.
	 * @param String $parameter the name of the form field item.
	 * @return String or Array, the value of the submitted header value.
	 */
	static function getRequestValue($parameter) {

		$requestValue = '';
		if (array_key_exists($parameter, $_POST)) {
			$requestValue = $_POST[$parameter];
		}
		if (!is_array($requestValue)) {
			return trim($requestValue);
		} else {
			return $requestValue;
		}
	}

	/**
	 * @brief returns the referring document from the _SERVER array.
	 * @return String the url of the referring page.
	 */
	static function getReferringPage() {
		$doc = $_SERVER['HTTP_REFERER'];
		return $doc;
	}

	/**
	 * @brief returns the remote IP address from the client.
	 * @return String the IP address.
	 */
	static function getRemoteHost() {
		$host = $_SERVER["REMOTE_ADDR"];
		return $host;
	}

	/**
	 * @brief returns true if the requesting IP is considered 'local' to the reserves instance.  See the config.php file for including IPs.
	 * @return boolean is it local?
	 */
	static function isLocalHost() {

		import('general.Config');
		$config = new Config();
		$localIps = $config->getSetting('localhosts', 'localhost');
		if (in_array(self::getRemoteHost(), $localIps))
			return true;
		else
			return false;
	}

	/**
	 * @brief determines if a referring request comes from the mobile site or not.
	 * @return boolean true or false.
	 */
	static function isMobile() {

		import('general.Config');
		$config = new Config();
		$mobileDomains = $config->getSetting('mobiledomains', 'domain');

		$referringPage = self::getReferringPage();
		foreach ($mobileDomains as $domain) {
			$domain = quotemeta($domain);
			if (preg_match('{' . $domain . '}', $referringPage) ) {

				return true;
			}
		}
		return false;
	}

	/**
	 * @brief redirects a user using a Location: header to a specified operation url.
	 * @param String $op the operation to redirect to.
	 */
	static function doRedirect($op) {

		import('general.Config');
		$config = new Config();
		$basePath = $config->getSetting('general', 'base_path');

		/* prune initial http(s)://... bits from the referring document, if we were handed getReferringPage() */
		$op = preg_replace('{^https?://' . quotemeta($config->getSetting('general', 'host_name')) . '}', '', $op);

		if (!preg_match("{^$basePath}", $op)) {
			$uri = $basePath . '/index.php/' . $op;
		} else if (preg_match("{^/}", $op) ) {
			$uri = $op;
		} else {
			self::showHomePage();
		}

		header('Location: ' . $uri);
		exit(0);
	}

	/**
	 * @brief shortcut function to return to the home page of the site.
	 */
	static function showHomePage() {
		import('general.Config');
		$config = new Config();
		header('Location: ' . $config->getSetting('general', 'base_path') . '/index.php');
		exit(0);
	}

	/**
	 * @brief returns the request URI from the _SERVER array.
	 * @return the url of the page.
	 */
	static function getRequestURI() {
		$requestURI = htmlentities($_SERVER['REQUEST_URI']);
		if (!preg_match('{loginError$}', $requestURI)) {
			return $requestURI;
		} else {
			if (isset($_SERVER['HTTP_REFERER'])) {
				if (preg_match('{https?://[^/]+(/.+)$}', $_SERVER['HTTP_REFERER'], $matches)) {
					return $matches[1];
				}
			}
		}
	}

	/**
	 * @brief creates a CURL object to determine the mime type of a remote URL, by issuing a HEAD request.
	 * @param String $url.
	 * @return String the Mime Type, if it can be determined.  False otherwise.
	 */
	static function determineMimeType($url) {
		import ('general.CURLObject');

		$mimeType = '';
		if (preg_match("|^https?://|", $url)) {
			$curlObject = new CURLObject($url);
			$mimeType = $curlObject->getMimeType();
		}

		return $mimeType;
	}
}

?>
