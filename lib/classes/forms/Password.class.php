<?php

	import('forms.TextField');

	class Password extends TextField {

		function __construct() {
			parent::__construct();

			$properties = array('name' => 'password', 'primaryLabel' => 'Password',' secondaryLabel' => '', 'required' => true);
			foreach ($properties as $key => $val) {
				$this->setProperty($key, $val);
			}
		}
	}
?>