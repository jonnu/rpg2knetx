<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/* load the HMVC_Loader class */
require APPPATH . 'extensions/HMVC/Loader.php';

class RPG_Loader extends HMVC_Loader {

	public function __construct() {

		parent::__construct();

		// Allow module views to exist in VIEWPATH
		$r_view = realpath(FCPATH . VIEWPATH);
		$module = $this->_ci_get_component('router')->fetch_module();
		$r_path = sprintf('%s/%s/', $r_view, $module);

		// Append this path to allowed view paths
		$this->_ci_view_paths[$r_path] = true;
	}
}