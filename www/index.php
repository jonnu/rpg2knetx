<?php


define('ENVIRONMENT', isset($_SERVER['RPG2KNET_ENVIRONMENT']) ? $_SERVER['RPG2KNET_ENVIRONMENT'] : '');
if (!in_array(ENVIRONMENT, array('development', 'production'))) {
	die('please set RPG2KNET_ENVIRONMENT [development|production]');
}

switch (ENVIRONMENT) {

	case 'development': {

		error_reporting(E_ALL | E_STRICT);

		break;
	}
	case 'production': {

		error_reporting(0);

		break;
	}

}


// Paths to system folders
$system_path        = '/work/2k/common/codeigniter.git/system';
$application_folder = '/work/2k/git/src';
$view_folder        = '/work/2k/git/www/html';


// Set the current directory correctly for CLI requests
if (defined('STDIN')) {
	chdir(dirname(__FILE__));
}


// Check system folder
$system_path = (($spath = realpath($system_path)) !== false) ? $spath . '/' : rtrim($system_path, '/') . '/';
if (!is_dir($system_path)) {
	header('HTTP/1.1 503 Service Unavailable.', true, 503);
	exit('Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME));	
}


// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system folder
define('BASEPATH', str_replace('\\', '/', $system_path));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


// The path to the "application" folder
if (is_dir($application_folder)) {

	if (($_temp = realpath($application_folder)) !== false) {
		$application_folder = $_temp;
	}

	define('APPPATH', $application_folder . '/');
}
else {

	if (!is_dir(BASEPATH . $application_folder . '/')) {
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		exit('Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF);
	}

	define('APPPATH', BASEPATH . $application_folder . '/');
}


// The path to the "views" folder
if (!is_dir($view_folder)) {

	if (!empty($view_folder) && is_dir(APPPATH . $view_folder . '/')) {
		$view_folder = APPPATH . $view_folder;
	}
	elseif (!is_dir(APPPATH . 'views/')) {
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		exit('Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF);
	}
	else {
		$view_folder = APPPATH . 'views';
	}
}

if (($_temp = realpath($view_folder)) !== false) {
	$view_folder = realpath($view_folder).'/';
}
else {
	$view_folder = rtrim($view_folder, '/').'/';
}

define('VIEWPATH', $view_folder);



// Bootstrap
require_once BASEPATH . 'core/CodeIgniter.php';