<?php

require_once(dirname(__FILE__) . "/../../../lib/twig/lib/Twig/Autoloader.php");

class Template {

	var $twig;
	var $reservesPage;
	var $templateDir;

	function __construct() {

		Twig_Autoloader::register();

		import('general.Config');
		$config = new Config();
		$this->templateDir = $config->getSetting('template', 'template_dir');
		$loader = new Twig_Loader_Filesystem($this->templateDir);
		$this->twig = new Twig_Environment($loader, array(
			'cache' => $config->getSetting('template', 'template_cache'),
			'auto_reload' => true
		));
	}

	/**
	 * @brief this function loads a template (either cached or not) and returns it rendered with
	 * values filled in, if any.
	 *
	 * @param $template String the name of the template to load.
	 * @param $templateState Array the variables in name/value pairs for the template.
	 */
	public function loadTemplate($template, $templateState = array()) {

		if (file_exists($this->templateDir . '/' . $template)) {
			$template = $this->twig->loadTemplate($template);
			echo $template->render($templateState);
		}
	}
}
?>
