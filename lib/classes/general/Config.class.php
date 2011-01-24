<?php

/* define the location of our base configuration file */
define('CONFIG_FILE', dirname(__FILE__) . '/../../../config.php');

class Config {

	var $config;

	function __construct() {

		$this->config = parse_ini_file(CONFIG_FILE, true);
	}

	/**
	 * @brief retrieves a setting from the configuration file
	 * @param String $section the [section] to pull from
	 * @param String $setting the setting name to retrieve
	 * @return String the setting value
	 */
	public function getSetting($section, $setting) {
		return $this->config[$section][$setting];
	}
}

?>